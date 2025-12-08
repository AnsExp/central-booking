<?php

use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\Form\FormZone;
use CentralTickets\Admin\Setting\SettingsGeneral;
use CentralTickets\Admin\Setting\SettingsTickets;
use CentralTickets\Admin\View\TableCoupons;
use CentralTickets\Admin\View\TableOperators;
use CentralTickets\Admin\View\TableRoutes;
use CentralTickets\Admin\View\TableServices;
use CentralTickets\Admin\View\TableTickets;
use CentralTickets\Admin\View\TableTransports;
use CentralTickets\Admin\View\TableZones;
use CentralTickets\ConnectorManager;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Constants\TimeExpirationConstants;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Constants\TransportCustomeFieldConstants;
use CentralTickets\Constants\TypeWayConstants;
use CentralTickets\Constants\UserConstants;
use CentralTickets\Constants\WebhookStatusConstants;
use CentralTickets\Constants\WebhookTopicConstants;
use CentralTickets\MetaManager;
use CentralTickets\Persistence\Migration;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\ZoneRepository;
use CentralTickets\REST\RegisterRoute;
use CentralTickets\Services\Actions\DateTrip;
use CentralTickets\Services\Actions\DownloadInvoiceInfo;
use CentralTickets\Services\Actions\TransferPassengers;
use CentralTickets\Services\ArrayParser\PassengerArray;
use CentralTickets\Services\LocationService;
use CentralTickets\Services\OperatorService;
use CentralTickets\Services\PackageData\LocationData;
use CentralTickets\Services\PackageData\OperatorData;
use CentralTickets\Services\PackageData\RouteData;
use CentralTickets\Services\PackageData\ServiceData;
use CentralTickets\Services\PackageData\TransportData;
use CentralTickets\Services\PassengerService;
use CentralTickets\Services\RouteService;
use CentralTickets\Services\ServiceService;
use CentralTickets\Services\TicketService;
use CentralTickets\Services\TransportService;
use CentralTickets\Webhooks\Webhook;
use CentralTickets\Webhooks\WebhookManager;
use CentralTickets\Zone;

defined('ABSPATH') || exit;

add_action('plugins_loaded', function () {
    (new Migration())->init();
});

add_action('wp_ajax_git_update_coupon_status', function () {

    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;

    $ticket = git_get_ticket_by_id($ticket_id);

    if (!$ticket) {
        wp_send_json(['message' => 'Ticket no encontrado.'], 400);
        return;
    }

    if (!git_current_user_has_role('administrator')) {
        if (!git_current_user_has_role('operator')) {
            wp_send_json(['message' => 'No tienes permiso para realizar esta acción.'], 403);
            return;
        }
        if ($ticket->status !== TicketConstants::PENDING) {
            wp_send_json(['message' => 'Como operador, solo puedes modificar tickets con estado PENDING.'], 403);
            return;
        }
    }

    $status = $_POST['status'] ?? '';
    $approved_passengers = array_map('intval', $_POST['approved_passengers'] ?? []);
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $code = $_POST['code'] ?? '';
    $file = $_FILES['file'] ?? null;

    if (!TicketConstants::is_valid_status($status)) {
        wp_send_json(['message' => 'Estado de ticket no válido.'], 400);
        return;
    }

    $service = new TicketService();
    $ticket = $service->change_ticket_status($ticket_id, $status);

    if ($ticket === null) {
        wp_send_json(['message' => 'Ha ocurrido un error al cambiar el estado del ticket.'], 400);
        return;
    }

    $result = $service->save_proof_payment($ticket_id, $amount, $code, $file);
    if ($result === null) {
        wp_send_json([
            'message' => 'Ha ocurrido un error al guardar el archivo de pago en nuestros servidores.',
            'data' => $service->error_stack,
            'files' => $_FILES,
        ], 400);
        return;
    }

    switch ($status) {
        case TicketConstants::PAYMENT:
            $approved_passengers = array_map(fn($passenger) =>
                $passenger->id, $ticket->get_passengers());
            break;
        case TicketConstants::CANCEL:
            $approved_passengers = [];
            break;
    }

    $result = $service->approve_passengers($ticket_id, $approved_passengers);
    if ($result === null) {
        wp_send_json(['message' => $service->error_stack], 400);
        return;
    }

    WebhookManager::get_instance()->trigger(
        WebhookTopicConstants::INVOICE_UPLOAD,
        $ticket->get_meta('proof_payment') ?? [],
    );

    wp_send_json([
        'message' => 'Operación completada con éxito.',
        'data' => [
            'ticket' => $ticket,
            'passengers' => $ticket->get_passengers(),
            'approved_passengers' => $approved_passengers,
            'proof_payment' => $ticket->get_meta('proof_payment'),
        ],
    ], 200);
});

add_action('wp_ajax_git_approve_passengers_table', function () {
    $passengers = [];
    foreach ($_POST['passengers'] ?? [] as $i) {
        $passenger = git_get_passenger_by_id($i);
        if ($passenger) {
            $passengers[] = $passenger;
        }
    }
    ob_start();
    ?>
    <div style="overflow-x: auto; max-width: 800px;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 200px;">Nombre</th>
                    <th style="width: 100px;">Nacionalidad</th>
                    <th style="width: 100px;">Tipo de Documento</th>
                    <th style="width: 150px;">Número de Documento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($passengers as $passenger): ?>
                    <input type="hidden" name="passengers[]" value="<?= esc_attr($passenger->id); ?>">
                    <tr>
                        <td>
                            <span>
                                <?= esc_html($passenger->name); ?>
                            </span>
                            <div class="row-actions visible">
                                <span>
                                    <a target="_blank"
                                        href="<?= esc_url(admin_url('admin.php?page=git_passengers&id=' . $passenger->id)); ?>">
                                        ID: <?= esc_html($passenger->id); ?>
                                    </a>
                                </span>
                                <span> | </span>
                                <span>
                                    <a target="_blank"
                                        href="<?= esc_url(admin_url('admin.php?page=git_tickets&id=' . $passenger->get_ticket()->id)); ?>">
                                        Ticket: <?= esc_html($passenger->get_ticket()->id); ?>
                                    </a>
                                </span>
                                <span> | </span>
                                <span class="trash">
                                    <a class="link-remove-passenger" data-passenger-id="<?= $passenger->id ?>"
                                        style="cursor: pointer;">
                                        Quitar de la lista
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td><?= esc_html($passenger->nationality); ?></td>
                        <td><?= esc_html($passenger->type_document); ?></td>
                        <td><?= esc_html($passenger->data_document); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    wp_send_json_success([
        'html' => ob_get_clean(),
    ])->set_status(200);
});

add_action('wp_ajax_git_transfer_passengers', function () {
    $transfer = new TransferPassengers();
    $route_repository = new RouteRepository();
    $route = $route_repository->find_first([
        'id_origin' => $_POST['origin'],
        'id_destiny' => $_POST['destiny'],
        'departure_time' => $_POST['time'],
    ]);
    $transport = git_get_transport_by_id($_POST['transport'] ?? -1);
    if ($route === null || $transport === null) {
        wp_send_json_error(['message' => 'Ruta o transporte no válidos.']);
        return;
    }

    $result = $transfer->transfer(
        $route,
        $transport,
        $_POST['date_trip'] ?? '',
        $_POST['passengers'] ? array_map('intval', $_POST['passengers']) : []
    );

    $array_parser = new PassengerArray();
    foreach ($_POST['passengers'] as $passenger_id) {
        $passenger = git_get_passenger_by_id((int) $passenger_id);
        if ($passenger) {
            $payload = $array_parser->get_array($passenger);
            WebhookManager::get_instance()->trigger(
                WebhookTopicConstants::PASSENGER_TRANSFERRED,
                $payload
            );
        }
    }

    if ($result) {
        wp_send_json_success($_POST);
    } else {
        wp_send_json_error(['message' => 'Error al realizar el traslado.']);
    }
});

add_action('wp_ajax_git_generate_code_operator_external', function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'operators_link_nonce')) {
        wp_send_json_error(['message' => 'Código de seguridad inválido.']);
        return;
    }
    $operator = git_get_operator_by_id($_POST['operator'] ?? -1);
    if (!$operator) {
        wp_send_json_error(['message' => 'Operador no válido.']);
        return;
    }
    $coupons = $operator->get_coupons();
    if (sizeof($coupons) <= 0) {
        wp_send_json_error(['message' => 'No se encontraron cupones para el operador.']);
        return;
    }
    $key = ConnectorManager::get_instance()->create_key(
        $operator,
        $_POST['expiration'] ? intval($_POST['expiration']) : TimeExpirationConstants::NEVER
    );
    ConnectorManager::get_instance()->save_key($operator->user_login, $key);
    wp_send_json_success(base64_encode(json_encode([
        'secret_key' => $key,
        'delivery_url' => home_url('/wp-json/' . RegisterRoute::prefix . 'external')
    ])));
});

add_action('wp_ajax_git_toggle_flexible', function () {
    $ticket = git_get_ticket_by_id($_POST['ticket_id'] ?? -1);
    if (!$ticket) {
        wp_send_json_error(['message' => 'Ticket no válido.']);
        return;
    }
    $force = isset($_POST['flexible']) ? (bool) $_POST['flexible'] : !$ticket->flexible;
    $service = new TicketService();
    $service->toggle_flexible($ticket->id, $force);
    wp_safe_redirect(AdminRouter::get_url_for_class(TableTickets::class));
    exit;
});

add_action('wp_ajax_git_transport_form', function () {
    $data = $_POST;
    $service = new TransportService();
    $result = $service->save(
        new TransportData(
            $data['type'] ?? '',
            $data['nicename'] ?? '',
            $data['code'] ?? '',
            (int) ($data['capacity'] ?? 0),
            (int) ($data['operator'] ?? 0),
            (int) ($data['flexible'] ?? false),
            $data['crew'] ?? [],
            $data['days'] ?? [],
            $data['services'] ?? [],
            $data['routes'] ?? [],
            $data['alias'] ?? [],
        ),
        (int) ($data['id'] ?? 0),
    );
    if ($result === null) {
        wp_send_json([
            'message' => 'Error al guardar el transporte.',
        ], 400);
    } else {
        if (TransportCustomeFieldConstants::is_valid($data['custom_field_topic'] ?? '')) {
            MetaManager::set_meta(MetaManager::TRANSPORT, $result->id, 'custom_field', [
                'content' => $data['custom_field_content'] ?? '',
                'topic' => $data['custom_field_topic'] ?? TransportCustomeFieldConstants::TEXT,
            ]);
            MetaManager::set_meta(MetaManager::TRANSPORT, $result->id, 'photo_url', $data['photo_url'] ?? '');
        }
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
        exit;
    }
});

add_action('wp_ajax_git_update_location', function () {
    if (!wp_verify_nonce($_POST['nonce'], 'update_location')) {
        wp_redirect(wp_get_referer());
    } else {
        $service = new LocationService();
        $result = $service->save(
            new LocationData(
                name: isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
                id_zone: isset($_POST['zone']) ? intval($_POST['zone']) : 0
            ),
            isset($_POST['id']) ? intval($_POST['id']) : 0
        );
        if ($result) {
            wp_redirect(remove_query_arg(['action', 'id'], wp_get_referer()));
        } else {
            wp_redirect(wp_get_referer());
        }
    }
    exit;
});

add_action('wp_ajax_git_update_operator', function () {
    if (!wp_verify_nonce($_POST['nonce'], 'git_operator_form_nonce')) {
        wp_send_json(
            ['message' => 'Conflictos de seguridad'],
            400
        );
        exit;
    } else {
        $operator_data = new OperatorData(
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['phone'],
            array_map('intval', $_POST['coupons']),
            $_POST['coupons_counter'],
            $_POST['coupons_limit'],
            $_POST['brand_media'],
        );
        $result = (new OperatorService())->save(
            $operator_data,
            (int) $_POST['id'],
        );
        if ($result === null) {
            wp_send_json(
                ['message' => 'Error al guardar el operador'],
                400
            );
        } else {
            wp_safe_redirect(AdminRouter::get_url_for_class(TableOperators::class));
        }
        exit;
    }
});


add_action('wp_ajax_git_edit_route', function () {
    echo json_encode($_POST);
    $service = new RouteService();
    $result = $service->save(
        new RouteData(
            type: $_POST['type'] ?? '',
            id_origin: $_POST['origin'] ?? 0,
            distance: $_POST['distance'] ?? 0,
            id_destiny: $_POST['destiny'] ?? 0,
            duration: $_POST['duration_trip'] ?? '',
            departure_time: $_POST['departure_time'] ?? '',
            id_transports: $_POST['transports'] ?? [],
        ),
        $_POST['id'] ?? 0,
    );
    if ($result === null) {
        wp_send_json_error($service->error_stack);
    }
    wp_safe_redirect(AdminRouter::get_url_for_class(TableRoutes::class));
    exit;
});


add_action('wp_ajax_git_service_form', function () {
    $id = $_POST['id'] ?? '0';
    $icon = $_POST['icon'] ?? '';
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $transports_ids = $_POST['transports'] ?? [];

    $service_service = new ServiceService();

    $result = $service_service->save(new ServiceData(
        $price,
        $name,
        $icon,
        $transports_ids
    ), (int) $id);

    if ($result === null) {
        wp_send_json_error([
            'message' => 'Error a la hora de guardar el servicio.',
        ]);
        return;
    }

    wp_safe_redirect(AdminRouter::get_url_for_class(TableServices::class));
    exit;
});

add_action('wp_ajax_git_edit_coupon', function () {
    $coupon_id = $_POST['coupon'] ?? '0';
    $brand_media = $_POST['brand_media'] ?? '';

    $coupon = git_get_coupon_by_id(intval($coupon_id));

    if (!$coupon) {
        wp_redirect(wp_get_referer());
        exit;
    }

    $operator = git_get_operator_by_coupon($coupon);

    git_get_query_persistence()->get_coupon_repository()->assign_coupon_to_operator(
        $coupon,
        $operator
    );

    update_post_meta($coupon->ID, 'brand_media', $brand_media);
    wp_redirect(AdminRouter::get_url_for_class(TableCoupons::class));
    exit;
});

add_action('wp_ajax_git_update_zone', function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'create_zone')) {
        wp_redirect(wp_get_referer());
        exit;
    }
    $name = $_POST['name'] ?? '';
    $id = $_POST['id'] ?? '0';
    if (empty($name)) {
        wp_redirect(AdminRouter::get_url_for_class(FormZone::class));
        exit;
    }
    if (empty($name) || !is_numeric($id)) {
        wp_redirect(AdminRouter::get_url_for_class(FormZone::class));
        exit;
    }
    $service = new ZoneRepository();
    $zone = git_get_zone_by_id((int) $id);
    if (!$zone) {
        $zone = new Zone();
    }
    $zone->name = $name;
    $service->save($zone);
    wp_redirect(AdminRouter::get_url_for_class(TableZones::class));
    exit;
});

add_action('wp_ajax_git_finish_trip', function () {
    if (
        !git_current_user_has_role(UserConstants::ADMINISTRATOR) &&
        !git_current_user_has_role(UserConstants::OPERATOR)
    ) {
        wp_send_json_error(['message' => 'No tienes permisos para finalizar el viaje.']);
        return;
    }

    $route = isset($_POST['route']) ? intval($_POST['route']) : 0;
    $transport = isset($_POST['transport']) ? intval($_POST['transport']) : 0;
    $date_trip = isset($_POST['date_trip']) ? sanitize_text_field($_POST['date_trip']) : '';

    $service = new PassengerService();
    $passengers = git_get_passengers([
        'id_route' => $route,
        'id_transport' => $transport,
        'date_trip' => $date_trip,
        'approved' => true,
    ]);

    foreach ($passengers as $passenger) {
        $service->passenger_served($passenger->id, true);
    }

    wp_send_json_success($_POST, 200);
});

add_action('wp_ajax_git_transport_availability', function () {
    $service = new TransportService();
    $result = $service->set_maintenance(
        $_POST['id_transport'],
        $_POST['date_start'],
        $_POST['date_end']
    );
    if (!$result) {
        $message = ['message' => '<p>' . $service->error_stack[0]];
        if (isset($service->error_stack[1])) {
            if (is_array($service->error_stack[1])) {
                $url = admin_url('admin.php?page=git_passengers&served=false&approved=true&date_trip_from=' . $_POST['date_start'] . '&date_trip_to=' . $_POST['date_end'] . '&id_transport=' . $_POST['id_transport']);
                $message['message'] .= '<br><a target="_blank" href="' . $url . '">Ver pasajeros</a>';
            }
        } else {
            $message['message'] .= '</p>';
        }
        wp_send_json($message, 400);
        return;
    }
    wp_send_json(['message' => 'Mantenimiento establecido correctamente'], 200);
});

add_action('wp_ajax_git_settings', function () {
    if (!isset($_POST['nonce']) || !isset($_POST['scope'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['nonce'], 'git_settings_nonce')) {
        return;
    }
    do_action('git_settings_' . $_POST['scope'], $_POST);
    wp_redirect(remove_query_arg(['action'], wp_get_referer()));
    exit;
});

add_action('wp_ajax_git_export_data', function () {
    if (!isset($_POST['nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['nonce'], 'git_export_data')) {
        return;
    }
    $data = (new Migration)->get_data(
        isset($_POST['settings_data']),
        isset($_POST['entities_data']),
        isset($_POST['products_data']),
        isset($_POST['operators_data']),
        isset($_POST['coupons_data']),
    );
    wp_send_json_success(git_serialize($data));
});

add_action('wp_ajax_git_import_data', function () {
    if (!isset($_FILES['git_data'])) {
        wp_redirect(wp_get_referer());
        exit;
    }

    $file = $_FILES['git_data'];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        error_log('[central-tickets] Import file upload error: ' . ($file['error'] ?? 'no_file'));
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
        exit;
    }

    $tmp = $file['tmp_name'];
    if (!is_uploaded_file($tmp) || !is_readable($tmp)) {
        error_log('[central-tickets] Uploaded file not readable: ' . $tmp);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
        exit;
    }

    $content = file_get_contents($tmp);
    if ($content === false) {
        error_log('[central-tickets] Failed reading uploaded file: ' . $tmp);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
        exit;
    }

    $payload = json_decode($content, true);
    if (!is_array($payload)) {
        error_log('[central-tickets] Uploaded file content is not valid JSON: ' . $tmp);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
        exit;
    }

    (new Migration)->set_data($payload);
    wp_redirect(wp_get_referer());
    wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
    exit;
});

add_action('git_settings_clients', function () {
    $kid_message = git_sanitize_html_content(stripslashes($_POST['kid_message']));
    $rpm_message = git_sanitize_html_content(stripslashes($_POST['rpm_message']));
    $extra_message = git_sanitize_html_content(stripslashes($_POST['extra_message']));
    $local_message = git_sanitize_html_content(stripslashes($_POST['local_message']));
    $request_seats = git_sanitize_html_content(stripslashes($_POST['request_seats']));
    $standard_message = git_sanitize_html_content(stripslashes($_POST['standard_message']));
    $flexible_message = git_sanitize_html_content(stripslashes($_POST['flexible_message']));
    $terms_conditions = git_sanitize_html_content(stripslashes($_POST['terms_conditions']));
    $days_without_sale = (int) ($_POST['days_without_sale'] ?? 0);

    git_set_setting('form_message_kid', $kid_message);
    git_set_setting('form_message_rpm', $rpm_message);
    git_set_setting('form_message_extra', $extra_message);
    git_set_setting('form_message_local', $local_message);
    git_set_setting('form_message_standard', $standard_message);
    git_set_setting('form_message_flexible', $flexible_message);
    git_set_setting('form_message_request_seats', $request_seats);
    git_set_setting('form_message_terms_conditions', $terms_conditions);
    DateTrip::set_days_without_sale($days_without_sale);
});

add_action('git_settings_notifications', function () {
    $title = $_POST['title_notification_email'] ?? '';
    $sender = $_POST['sender_notification_email'] ?? '';
    $content = git_sanitize_html_content(stripslashes($_POST['notification_email']));
    $message_checkout = git_sanitize_html_content(stripslashes($_POST['message_checkout'] ?? ''));

    git_set_setting('notification_email', [
        'title' => $title,
        'sender' => $sender,
        'content' => $content,
    ]);
    git_set_setting('message_checkout', $message_checkout);
});

add_action('git_settings_operators', function () {
    $operator_file_size = sanitize_operator_file_size($_POST['operator_file_size'] ?? '');

    $operator_file_extensions = sanitize_operator_file_extensions($_POST['operator_file_extensions'] ?? '');

    if ($operator_file_size !== false) {
        git_set_setting('operator_file_size', $operator_file_size);
    }

    if ($operator_file_extensions !== false) {
        git_set_setting('operator_file_extensions', $operator_file_extensions);
    }
});

add_action('git_settings_preorder', function () {
    $secret_key = $_POST['secret_key'] ?? '';
    git_set_secret_key($secret_key);
});

add_action('git_settings_texts', function () {
    $texts_ways = [
        TypeWayConstants::ONE_WAY => $_POST['one_way'] ?? TypeWayConstants::ONE_WAY,
        TypeWayConstants::ANY_WAY => $_POST['any_way'] ?? TypeWayConstants::ANY_WAY,
        TypeWayConstants::DOUBLE_WAY => $_POST['double_way'] ?? TypeWayConstants::DOUBLE_WAY,
    ];
    $texts_type = [
        TransportConstants::LAND => $_POST['type_land'] ?? TransportConstants::LAND,
        TransportConstants::AERO => $_POST['type_aero'] ?? TransportConstants::AERO,
        TransportConstants::MARINE => $_POST['type_marine'] ?? TransportConstants::MARINE,
    ];
    $texts_status = [
        TicketConstants::PENDING => $_POST['status_pending'] ?? TicketConstants::PENDING,
        TicketConstants::PAYMENT => $_POST['status_payment'] ?? TicketConstants::PAYMENT,
        TicketConstants::PARTIAL => $_POST['status_partial'] ?? TicketConstants::PARTIAL,
        TicketConstants::CANCEL => $_POST['status_cancel'] ?? TicketConstants::CANCEL,
    ];
    $create_categories = isset($_POST['create_categories']);

    if ($create_categories) {
        foreach ($texts_type as $slug => $type) {
            if (!term_exists($slug, 'product_cat')) {
                wp_insert_term(
                    $type,
                    'product_cat',
                    [
                        'slug' => sanitize_title($slug),
                    ]
                );
            } else {
                wp_update_term(
                    get_term_by(
                        'slug',
                        $slug,
                        'product_cat'
                    )->term_id,
                    'product_cat',
                    [
                        'name' => $type,
                        'slug' => sanitize_title($slug),
                    ]
                );
            }
        }
    }

    git_set_setting('texts_ways', $texts_ways);
    git_set_setting('texts_type', $texts_type);
    git_set_setting('texts_status', $texts_status);
});

add_action('git_settings_tickets', function () {
    $page_viewer = $_POST['page_viewer'] ?? -1;
    $viewer_js = git_sanitize_html_content(stripslashes($_POST['viewer_js']));
    $viewer_css = git_sanitize_html_content(stripslashes($_POST['viewer_css']));
    $ticket_viewer_html = git_sanitize_html_content(stripslashes($_POST['ticket_viewer_html']));
    $passenger_viewer_html = git_sanitize_html_content(stripslashes($_POST['passenger_viewer_html']));
    $default_media = sanitize_text_field($_POST['default_media'] ?? '');

    git_set_setting('ticket_viewer', [
        'page_viewer' => $page_viewer,
        'viewer_js' => $viewer_js,
        'viewer_css' => $viewer_css,
        'ticket_viewer_html' => $ticket_viewer_html,
        'passenger_viewer_html' => $passenger_viewer_html,
        'default_media' => $default_media,
    ]);

    wp_safe_redirect(AdminRouter::get_url_for_class(SettingsTickets::class));
    exit;
});


add_action('git_settings_webhooks', function () {
    $webhook_manager = WebhookManager::get_instance();
    $webhook = $webhook_manager->get(intval($_POST['id'] ?? '0'));
    if (!$webhook) {
        $webhook = new Webhook();
    }
    $webhook->name = sanitize_text_field($_POST['name'] ?? '');
    $webhook->secret = sanitize_text_field($_POST['secret'] ?? '');
    $webhook->status = sanitize_text_field($_POST['status'] ?? WebhookStatusConstants::ACTIVE);
    $webhook->topic = sanitize_text_field($_POST['topic'] ?? 'none');
    $webhook->url_delivery = esc_url_raw($_POST['delivery_url'] ?? '');
    $webhook_manager->save($webhook);
});

add_action('admin_post_download_invoice_csv', function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'download_invoice')) {
        wp_die('Token de seguridad inválido');
    }
    $operator = git_get_operator_by_id($_POST['operator'] ?? -1);
    if (!$operator) {
        wp_die('Operador no válido');
    }
    $downloader = new DownloadInvoiceInfo();
    $downloader->download_csv(
        $operator,
        $_POST['date_start'] ?? date('Y-m-01'),
        $_POST['date_end'] ?? date('Y-m-t'),
        get_post($_POST['coupon'] ?? null),
        $_POST['columns'] ?? [],
    );
    wp_safe_redirect($_POST['_wp_http_referer'] ?? admin_url());
    exit;
});

add_action('wp_ajax_git_qr_generator', function () {
    $placeholder = 'https://api.qrserver.com/v1/create-qr-code/?size={width}x{width}&data={data}';
    $data = $_POST['data'] ?? '';
    $type = $_POST['type'] ?? 'text';
    $width = $_POST['width'] ?? '100';
    if ($type === 'text' || $type === 'url') {
        $placeholder = str_replace('{data}', urlencode($data), $placeholder);
    } elseif ($type === 'email') {
        $placeholder = str_replace('{data}', 'mailto:' . urlencode($data), $placeholder);
    } elseif ($type === 'phone') {
        $placeholder = str_replace('{data}', 'tel:' . urlencode($data), $placeholder);
    // } elseif ($type === 'wifi') {
    //     $placeholder = str_replace('{data}', 'WIFI:' . urlencode($data), $placeholder);
    }
    $placeholder = str_replace('{width}', $width, $placeholder);
    wp_send_json_success($placeholder);
});