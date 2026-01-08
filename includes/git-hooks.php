<?php

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormZone;
use CentralBooking\Admin\Setting\SettingsTexts;
use CentralBooking\Admin\View\TableOperators;
use CentralBooking\Admin\View\TableCoupons;
use CentralBooking\Admin\View\TableLocations;
use CentralBooking\Admin\View\TablePassengers;
use CentralBooking\Admin\View\TableRoutes;
use CentralBooking\Admin\View\TableServices;
use CentralBooking\Admin\View\TableTickets;
use CentralBooking\Admin\View\TableTransports;
use CentralBooking\Admin\View\TableZones;
use CentralBooking\Admin\Setting\SettingsGeneral;
use CentralBooking\Admin\Setting\SettingsTickets;
use CentralBooking\Data\Constants\LogLevel;
use CentralBooking\Data\Constants\LogSource;
use CentralBooking\Data\Date;
use CentralBooking\Data\Operator;
use CentralBooking\Data\Transport;
use CentralBooking\Data\Zone;
use CentralBooking\Data\Services\OperatorService;
use CentralBooking\Data\Services\ErrorService;
use CentralBooking\Data\Services\LocationService;
use CentralBooking\Data\Services\PassengerService;
use CentralBooking\Data\Services\RouteService;
use CentralBooking\Data\Services\ServiceService;
use CentralBooking\Data\Services\TicketService;
use CentralBooking\Data\Services\TransportService;
use CentralBooking\Data\Services\ZoneService;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\UserConstants;
use CentralBooking\Data\Repository\Migration;
use CentralBooking\Placeholders\PlaceholderEngineCheckout;
use CentralBooking\QR\CodeQr;
use CentralBooking\QR\ColorQr;
use CentralBooking\QR\DataQr;
use CentralBooking\QR\ErrorCorrectionCode;
use CentralBooking\QR\DefaultStrategy\EmailData;
use CentralBooking\QR\DefaultStrategy\PhoneData;
use CentralBooking\QR\DefaultStrategy\URLData;
use CentralBooking\QR\DefaultStrategy\WhatsAppData;
use CentralBooking\QR\DefaultStrategy\WiFiData;
use CentralBooking\Utils\ArrayParser\TransportArray;
use CentralBooking\Utils\Actions\DownloadInvoiceInfo;
use CentralBooking\Webhook\Webhook;
use CentralBooking\Webhook\WebhookManager;
use CentralBooking\Webhook\WebhookStatus;
use CentralBooking\Webhook\WebhookTopic;
use CentralBooking\WooCommerce\CreateOrderLineItem;
use CentralBooking\WooCommerce\ProductForm;
use CentralBooking\WooCommerce\ProductItemCart;
use CentralBooking\WooCommerce\Thankyou;
use CentralBooking\WooCommerce\SingleProduct\FormProduct;
use CentralBooking\WooCommerce\SingleProduct\FormProductNotAvailable;
use CentralBooking\WooCommerce\SingleProduct\FormProductTransport;

defined('ABSPATH') || exit;

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $url = AdminRouter::get_url_for_class(SettingsGeneral::class);
    $links[] = '<a href="' . $url . '">Ajustes</a>';
    return $links;
}, 1);

add_action('plugins_loaded', function () {
    // (new Migration())->init();
});

add_action('wp_ajax_nopriv_git_passenger_form_html', function () {
    $passenger_count = $_POST['passengers_count'] ?? 1;
    wp_send_json_success([
        'output' => git_get_passenger_form($passenger_count)
    ]);
});

add_action('wp_ajax_git_passenger_form_html', function () {
    $passenger_count = $_POST['passengers_count'] ?? 1;
    wp_send_json_success([
        'output' => git_get_passenger_form($passenger_count)
    ]);
});

add_action('woocommerce_single_product_summary', function () {
    global $product;
    if ($product->get_type() === 'operator') {
        if ($product->get_meta('enable_bookeable', true) !== 'yes') {
            echo (new FormProductNotAvailable)->compact();
        } else {
            echo $product->get_description();
            echo (new FormProduct($product))->compact();
        }
    }
}, 25);

add_action('wp_ajax_preorder_process', function () {
    git_preorder_process($_POST);
});

add_action('wp_ajax_nopriv_preorder_process', function () {
    git_preorder_process($_POST);
});

add_filter('woocommerce_get_price_html', function ($price_html, $product) {
    $price_html = git_get_price_html_product($product);
    return $price_html;
}, 10, 2);

add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    $product_item = new ProductItemCart();
    $item_data = array_merge($item_data, $product_item->itemCart($cart_item));
    return $item_data;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function ($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        if ($product->get_type() !== 'operator') {
            continue;
        }
        $cart_item['data']->set_price($cart_item['cart_ticket']->calculatePrice());
    }
});

add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    $valid = git_validate_coupon($coupon);
    return $valid;
}, 10, 2);

add_filter('woocommerce_thankyou_order_received_text', function ($thank_you_text, WC_Order $order) {
    $message = git_get_setting('message_checkout', '');
    $engine = new PlaceholderEngineCheckout($order);
    $processed_message = $engine->process($message);
    $thank_you_text = $processed_message;
    return $thank_you_text;
}, 10, 2);

add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values, $order) {
    $create_order_line_item = new CreateOrderLineItem();
    $create_order_line_item->add_line_item($item, $values);
}, 10, 4);

add_action('woocommerce_thankyou', function ($order_id) {
    $thankyou = new Thankyou();
    $thankyou->thankyou($order_id);
}, 10, 1);

add_filter('woocommerce_product_data_tabs', function ($tabs) {
    return array_merge($tabs, ProductForm::get_tabs());
});

add_action('woocommerce_product_data_panels', function () {
    echo ProductForm::get_general_panel()->compact();
    echo ProductForm::get_pricing_panel()->compact();
    echo ProductForm::get_inventory_panel()->compact();
});

add_action('woocommerce_process_product_meta_operator', function ($post_id) {
    ProductForm::process_form($post_id);
});

add_action('wp_ajax_git_product_submit', function () {
    if (!wp_verify_nonce($_POST["nonce"], "git_product_form")) {
        return;
    }
    $_POST['flexible'] = isset($_POST['flexible']) && $_POST['flexible'] === '1';
    $_POST['round_trip'] = isset($_POST['round_trip']) && $_POST['round_trip'] === '1';
    git_proccess_submit_product_form($_POST);
    exit;
});

add_action('wp_ajax_nopriv_git_product_submit', function () {
    if (!wp_verify_nonce($_POST["nonce"], "git_product_form")) {
        return;
    }
    $_POST['flexible'] = isset($_POST['flexible']) && $_POST['flexible'] === '1';
    $_POST['round_trip'] = isset($_POST['round_trip']) && $_POST['round_trip'] === '1';
    git_proccess_submit_product_form($_POST);
    exit;
});

add_action('wp_ajax_git_fetch_transports', function () {
    $response = new TransportArray();
    $_POST['split_alias'] = isset($_POST['split_alias']) && $_POST['split_alias'] === '1' ? true : false;
    wp_send_json_success(array_map(
        fn(Transport $transport) => $response->get_array($transport),
        FormProductTransport::queryTransports($_POST)
    ));
});

add_action('wp_ajax_nopriv_git_fetch_transports', function () {
    $response = new TransportArray;
    $_POST['split_alias'] = isset($_POST['split_alias']) && $_POST['split_alias'] === '1' ? true : false;
    wp_send_json_success(array_map(
        fn($transport) => $response->get_array($transport),
        FormProductTransport::queryTransports($_POST)
    ));
});

add_action('wp_ajax_git_edit_coupon_status', function () {
    $redirect = $_POST['_wp_http_referer'] ?? wp_get_referer();
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'git_edit_coupon_status')) {
        $redirect = add_query_arg(
            ['success' => 'false'],
            $redirect
        );
        wp_safe_redirect($redirect);
        exit;
    }
    $ticket_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $ticket = git_ticket_by_id($ticket_id);
    $status = TicketStatus::tryFrom($_POST['status'] ?? '');
    if ($ticket === null) {
        $redirect = add_query_arg(
            ['success' => 'false'],
            $redirect
        );
        wp_safe_redirect($redirect);
        exit;
    }
    if ($status === null) {
        $redirect = add_query_arg(
            ['success' => 'false'],
            $redirect
        );
        wp_safe_redirect($redirect);
        exit;
    }
    if (!git_current_user_has_role(UserConstants::ADMINISTRATOR)) {
        if (!git_current_user_has_role(UserConstants::OPERATOR)) {
            $redirect = add_query_arg(
                ['success' => 'false'],
                $redirect
            );
            wp_safe_redirect($redirect);
            exit;
        }
        if ($ticket->status !== TicketStatus::PENDING) {
            $redirect = add_query_arg(
                ['success' => 'false'],
                $redirect
            );
            wp_safe_redirect($redirect);
            exit;
        }
    }
    $service = new TicketService();

    $approved_passengers = array_map('intval', $_POST['approved_passengers'] ?? []);

    if ($status === TicketStatus::PAYMENT) {
        $amount = $ticket->total_amount;
    } else {
        $amount = 100 * (isset($_POST['amount']) ? floatval($_POST['amount']) : 0);
    }
    $code = $_POST['code'] ?? '';
    $file = $_FILES['file'] ?? null;

    $ticket->status = $status;
    $ticket = git_ticket_save($ticket);

    if ($ticket === null) {
        $redirect = add_query_arg(
            ['success' => 'false'],
            $redirect
        );
        wp_safe_redirect($redirect);
        return;
    }

    $result = $service->saveProofPayment($ticket, $amount, $code, $file);
    if ($result === null) {
        $redirect = add_query_arg(
            ['success' => 'false'],
            $redirect
        );
        wp_safe_redirect($redirect);
        return;
    }

    switch ($status) {
        case TicketStatus::PAYMENT:
            $approved_passengers = array_map(fn($passenger) =>
                $passenger, $ticket->getPassengers());
            break;
        case TicketStatus::PARTIAL:
            $approved_passengers = array_map(fn($passenger_id) =>
                git_passenger_by_id($passenger_id), $approved_passengers);
            break;
        case TicketStatus::CANCEL:
            $approved_passengers = [];
            break;
    }

    $result = $service->approvePassengers($ticket, $approved_passengers);

    if ($result === null) {
        $redirect = add_query_arg(
            ['success' => 'true'],
            $redirect
        );
        wp_safe_redirect($redirect);
        return;
    }

    // WebhookManager::getInstance()->trigger(
    //     WebhookTopic::INVOICE_UPLOAD,
    //     $ticket->getProofPayment(),
    // );

    $redirect = add_query_arg(
        ['success' => 'true'],
        $redirect
    );
    wp_safe_redirect($redirect);
    exit;
});

add_action('wp_ajax_git_approve_passengers_table', function () {
    $passengers = [];
    foreach ($_POST['passengers'] ?? [] as $i) {
        $passenger = git_passenger_by_id($i);
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
                                        href="<?= esc_url(AdminRouter::get_url_for_class(TablePassengers::class, ['id' => $passenger->id])); ?>">
                                        ID: <?= esc_html($passenger->id); ?>
                                    </a>
                                </span>
                                <span> | </span>
                                <span>
                                    <a target="_blank"
                                        href="<?= esc_url(AdminRouter::get_url_for_class(TableTickets::class, ['id' => $passenger->getTicket()->id])); ?>">
                                        Ticket: <?= esc_html($passenger->getTicket()->id); ?>
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
                        <td><?= esc_html($passenger->typeDocument); ?></td>
                        <td><?= esc_html($passenger->dataDocument); ?></td>
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
    $routes = git_routes([
        'id_origin' => $_POST['origin'],
        'id_destiny' => $_POST['destiny'],
        'departure_time' => $_POST['time'],
    ]);
    $transport = git_transport_by_id($_POST['transport'] ?? -1);
    if ($routes === [] || $transport === null) {
        wp_send_json_error(['message' => 'Ruta o transporte no válidos.']);
        return;
    }
    $route = $routes[0];
    $passengers = $_POST['passengers'] ?? [];

    $is_available = git_transport_check_availability(
        $transport->id,
        $route->id,
        new Date($_POST['date_trip'] ?? ''),
        count($passengers)
    );

    if ($is_available !== true) {
        wp_send_json_error(['message' => 'Error al realizar el traslado.']);
        exit;
    }

    foreach ($passengers as $passenger_id) {
        $passenger = git_passenger_by_id((int) $passenger_id);
        if ($passenger === null) {
            continue;
        }
        $result = git_passenger_transfer(
            $passenger,
            $route,
            $transport,
            new Date($_POST['date_trip'] ?? '')
        );
        if ($result === true) {
            git_passenger_save($passenger);
        }
    }

    wp_safe_redirect(AdminRouter::get_url_for_class(TablePassengers::class));
    exit;
});

add_action('wp_ajax_git_toggle_flexible', function () {
    $force = null;
    if (isset($_POST['flexible'])) {
        $force = (bool) $_POST['flexible'];
    }
    $result = git_ticket_toggle_flexible($_POST['ticket_id'] ?? -1, $force);
    if ($result === ErrorService::TICKET_NOT_FOUND) {
        wp_send_json_error(['message' => 'Ticket no válido.']);
        return;
    }
    git_ticket_save($result);
    wp_safe_redirect(AdminRouter::get_url_for_class(TableTickets::class));
    exit;
});

add_action('wp_ajax_git_edit_transport', function () {
    $data = $_POST;
    $transport = git_transport_create([
        'id' => $data['id'] ?? '0',
        'type' => $data['type'] ?? '',
        'nicename' => $data['nicename'] ?? '',
        'code' => $data['code'] ?? '',
        'capacity' => $data['capacity'] ?? '0',
        'operator_id' => $data['operator'] ?? '0',
        'days' => $data['days'] ?? [],
        'alias' => $data['alias'] ?? [],
        'photo_url' => $data['photo_url'] ?? '',
    ]);
    $routes = [];
    $services = [];
    foreach ($data['routes'] as $route_id) {
        $route = git_route_by_id((int) $route_id);
        if ($route === null) {
            continue;
        }
        $routes[] = $route;
    }
    foreach ($data['services'] as $service_id) {
        $service = git_service_by_id((int) $service_id);
        if ($service === null) {
            continue;
        }
        $services[] = $service;
    }
    $transport->setRoutes($routes);
    $transport->setServices($services);
    $service = new TransportService();
    $result = $service->save($transport);
    if ($result === null) {
        wp_redirect($_POST['_wp_http_referer'] ?? wp_get_referer());
    } else {
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
    }
    exit;
});

add_action('wp_ajax_git_edit_location', function () {

    if (!wp_verify_nonce($_POST['nonce'], 'edit_location')) {
        wp_redirect($_POST['_wp_http_referer'] ?? wp_get_referer());
        exit;
    }

    $service = new LocationService();
    $location = git_location_create([
        'id' => $_POST['id'] ?? '',
        'name' => $_POST['name'] ?? '',
        'zone' => $_POST['zone'] ?? '',
    ]);
    $result = $service->save($location);
    if ($result !== null) {
        wp_redirect(AdminRouter::get_url_for_class(TableLocations::class));
    } else {
        wp_redirect($_POST['_wp_http_referer'] ?? wp_get_referer());
    }
    exit;
});

add_action('wp_ajax_git_edit_operator', function () {
    if (!wp_verify_nonce($_POST['nonce'], 'git_operator_form_nonce')) {
        wp_send_json(
            ['message' => 'Conflictos de seguridad'],
            400
        );
        exit;
    } else {
        // echo git_serialize($_POST);
        $user = get_user((int) ($_POST['id'] ?? -1));
        if (!$user) {
            wp_send_json(
                ['message' => 'Usuario no encontrado'],
                400
            );
            exit;
        }
        $user->first_name = $_POST['firstname'];
        $user->last_name = $_POST['lastname'];

        $operator = new Operator();
        $operator->setUser($user);
        $operator->setPhone($_POST['phone']);
        $operator->setBrandMedia($_POST['brand_media']);
        $operator->setBusinessPlan(
            (int) $_POST['coupons_counter']['limit'],
            (int) $_POST['coupons_counter']['index'],
        );
        $coupons = [];
        foreach ($_POST['coupons'] as $coupon_id) {
            $coupon = git_coupon_by_id((int) $coupon_id);
            if ($coupon) {
                $coupons[] = $coupon;
            }
        }
        $operator->setCoupons($coupons);
        $result = (new OperatorService)->save($operator);
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
    $service = new RouteService();
    $route = git_route_create([
        'id' => $_POST['id'] ?? '0',
        'type' => $_POST['type'] ?? '',
        'origin_id' => $_POST['origin'] ?? '',
        'destiny_id' => $_POST['destiny'] ?? '',
        'arrival_time' => $_POST['arrival_time'] ?? '',
        'departure_time' => $_POST['departure_time'] ?? '',
    ]);
    $transports = [];
    foreach ($_POST['transports'] as $transport_id) {
        $transport = git_transport_by_id((int) $transport_id);
        if ($transport === null) {
            continue;
        }
        $transports[] = $transport;
    }
    $route->setTransports($transports);
    $result = $service->save($route);
    if ($result === null) {
        wp_safe_redirect($_POST['_wp_http_referer'] ?? wp_get_referer());
    } else {
        wp_safe_redirect(AdminRouter::get_url_for_class(TableRoutes::class));
    }
    exit;
});

add_action('wp_ajax_git_edit_service', function () {
    $id = $_POST['id'] ?? '0';
    $icon = $_POST['icon'] ?? '';
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;

    $service_service = new ServiceService();

    $service = git_service_create([
        'id' => $id,
        'name' => $name,
        'icon' => $icon,
        'price' => $price,
    ]);
    $transports = [];
    foreach ($_POST['transports'] as $transport_id) {
        $transport = git_transport_by_id((int) $transport_id);
        if ($transport === null) {
            continue;
        }
        $transports[] = $transport;
    }
    $service->setTransports($transports);
    $result = git_service_save($service);
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

    $coupon = git_coupon_by_id(intval($coupon_id));

    if (!$coupon) {
        wp_redirect(wp_get_referer());
        exit;
    }

    $operator = git_operator_by_coupon($coupon);

    git_assign_coupon_to_operator($operator, $coupon);

    update_post_meta($coupon->ID, 'brand_media', $brand_media);
    wp_redirect(AdminRouter::get_url_for_class(TableCoupons::class));
    exit;
});

add_action('wp_ajax_git_edit_zone', function () {
    $url_referer = $_POST['_wp_http_referer'] ?? wp_get_referer();
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'edit_zone')) {
        wp_redirect($url_referer);
        exit;
    }
    $id = (int) ($_POST['id'] ?? '0');
    $name = (string) ($_POST['name'] ?? '');
    if (empty($name)) {
        wp_redirect(AdminRouter::get_url_for_class(FormZone::class));
        exit;
    }
    if (empty($name) || !is_numeric($id)) {
        wp_redirect(AdminRouter::get_url_for_class(FormZone::class));
        exit;
    }
    $service = new ZoneService();
    $zone = git_zone_by_id($id) ?? new Zone();
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

    $passengers = git_passengers([
        'id_route' => $route,
        'id_transport' => $transport,
        'date_trip' => $date_trip,
        'approved' => true,
    ]);

    foreach ($passengers as $passenger) {
        $passenger->served = true;
        git_passenger_save($passenger);
        ob_start();
        $url = AdminRouter::get_url_for_class(TablePassengers::class, ['id' => $passenger->id]);
        ?>
        <p>
            El <a target="_blank" href="<?= esc_url($url) ?>">pasajero<?= $passenger->id ?></a>
            ha sido transportado.<br>
            El responsable del traslado es <code><?= wp_get_current_user()->user_login ?></code>.
        </p>
        <?php
        git_log_create(
            source: LogSource::PASSENGER,
            id_source: $passenger->id,
            message: ob_get_clean(),
            level: LogLevel::INFO,
        );
    }

    wp_send_json_success($_POST, 200);
});

add_action('wp_ajax_git_transport_availability', function () {
    $service = new TransportService();
    $transport = git_transport_by_id($_POST['id_transport'] ?? -1);
    $date_start = git_date_create($_POST['date_start'] ?? '');
    $date_end = git_date_create($_POST['date_end'] ?? '');
    if ($transport === null || $date_start === false || $date_end === false) {
        wp_send_json(['message' => 'Datos inválidos'], 400);
    }
    try {
        $result = git_transport_set_maintenance(
            $transport,
            $date_start,
            $date_end
        );
        if ($result === true) {
            git_transport_save($transport);
            wp_send_json(['message' => 'Mantenimiento establecido correctamente'], 200);
        } elseif ($result === ErrorService::INVALID_DATE_RANGE) {
            throw new Exception('El transporte ya tiene mantenimiento en las fechas indicadas');
        } elseif ($result === ErrorService::TRANSPORT_NOT_FOUND) {
            throw new Exception('El transporte no existe.');
        } elseif ($result === ErrorService::PASSENGERS_PENDING_TRIPS) {
            throw new Exception('El transporte tiene pasajeros pendientes de viaje.');
        } else {
            throw new Exception('Error al establecer el mantenimiento.');
        }
    } catch (Exception $e) {
        $message = ['message' => '<p>' . $e->getMessage()];
        if ($service->lastError === ErrorService::PASSENGERS_PENDING_TRIPS) {
            $url = add_query_arg(
                [
                    'served' => 'false',
                    'approved' => 'true',
                    'date_trip_from' => $_POST['date_start'],
                    'date_trip_to' => $_POST['date_end'],
                    'id_transport' => $_POST['id_transport'],
                ],
                AdminRouter::get_url_for_class(TablePassengers::class)
            );
            $message['message'] .= '<br><a target="_blank" href="' . $url . '">Ver pasajeros</a>';
        } else {
            $message['message'] .= '</p>';
        }
        wp_send_json($message, 400);
    }
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
    git_set_days_without_sale($days_without_sale);
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

add_action('wp_ajax_git_settings_secret_key', function () {
    $redirect_url = $_POST['_wp_http_referer'] ?? wp_get_referer();
    if (!wp_verify_nonce($_POST['nonce'], 'git_secret_key')) {
        wp_safe_redirect($redirect_url);
        exit;
    }
    $secret_key = $_POST['secret_key'] ?? '';
    git_set_secret_key($secret_key);
    wp_safe_redirect($redirect_url);
    exit;
});

add_action('git_settings_texts', function () {
    SettingsTexts::setTextWays(
        $_POST['one_way'] ?? '',
        $_POST['any_way'] ?? '',
        $_POST['double_way'] ?? ''
    );
    SettingsTexts::setTextTransport(
        $_POST['type_land'] ?? '',
        $_POST['type_aero'] ?? '',
        $_POST['type_marine'] ?? ''
    );
    SettingsTexts::setTextStatus(
        $_POST['status_pending'] ?? '',
        $_POST['status_payment'] ?? '',
        $_POST['status_partial'] ?? '',
        $_POST['status_cancel'] ?? ''
    );
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
    $webhook_manager = WebhookManager::getInstance();
    $webhook = $webhook_manager->get(intval($_POST['id'] ?? '0'));
    if (!$webhook) {
        $webhook = new Webhook();
    }
    $webhook->name = sanitize_text_field($_POST['name'] ?? '');
    $webhook->secret = sanitize_text_field($_POST['secret'] ?? '');
    $webhook->status = WebhookStatus::from(sanitize_text_field($_POST['status'] ?? WebhookStatus::ACTIVE->value));
    $webhook->topic = WebhookTopic::from(sanitize_text_field($_POST['topic'] ?? WebhookTopic::NONE->value));
    $webhook->url_delivery = esc_url_raw($_POST['delivery_url'] ?? '');
    $webhook_manager->save($webhook);
});

add_action('admin_post_download_invoice_csv', function () {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'download_invoice')) {
        wp_die('Token de seguridad inválido');
    }
    $operator = git_operator_by_id($_POST['operator'] ?? -1);
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
    $type = $_POST['type'] ?? 'url';
    if ($type === 'url') {
        $data = new URLData($_POST['url'] ?? '');
    } else if ($type === 'email') {
        $data = new EmailData(
            emailAddress: $_POST['email'] ?? '',
            subject: $_POST['email_subject'] ?? null,
            body: $_POST['email_message'] ?? null
        );
    } elseif ($type === 'phone') {
        $data = new PhoneData($_POST['phone'] ?? '');
    } elseif ($type === 'whatsapp') {
        $data = new WhatsAppData(
            phoneNumber: $_POST['whatsapp_phone'] ?? '',
            message: $_POST['whatsapp_message'] ?? null
        );
    } elseif ($type === 'wifi') {
        $ssid = $_POST['ssid'] ?? '';
        $password = $_POST['password'] ?? '';
        $encryption = $_POST['encryption'] ?? 'WPA';
        $hidden = isset($_POST['hidden']) ? 'true' : 'false';
        $data = new WiFiData(
            ssid: $ssid,
            password: $password,
            encryption: $encryption,
            hidden: $hidden === 'true'
        );
    } else {
        $data = new class implements DataQr {
            public function getData(): string
            {
                return '';
            }
        };
    }
    $size = $_POST['size'] ?? '100';
    $margin = $_POST['margin'] ?? '100';
    $ecc = $_POST['ecc'] ?? ErrorCorrectionCode::LOW;
    $color = $_POST['color'] ?? '#000000';
    $bgColor = $_POST['bgcolor'] ?? '#FFFFFF';
    try {
        $codeQr = git_qr_create($data, [
            'size' => (int) $size,
            'margin' => (int) $margin,
            'color' => (int) $color,
            'bg_color' => (int) $bgColor,
            'error_correction_code' => ErrorCorrectionCode::from($ecc),
        ]);
        wp_send_json_success(['qr_html' => $codeQr->render('Código QR generador por SUP Galápagos')]);
    } catch (Exception $e) {
        git_set_setting('sample', $e->getMessage());
        wp_send_json_error(['message' => 'Error al generar los datos del código QR: ' . $e->getMessage()]);
        return;
    }
});

add_action('wp_ajax_git_create_ticket_operator', function () {

    $refereder = $_POST['_wp_http_referer'] ?? wp_get_referer();

    if (wp_verify_nonce($_POST['nonce'] ?? '', 'create_ticket_operator') === false) {
        wp_safe_redirect($refereder);
        exit;
    }

    $origin = (int) ($_POST['origin'] ?? '0');
    $destiny = (int) ($_POST['destiny'] ?? '0');
    $departure_time = (string) ($_POST['departure_time'] ?? '');
    $routes = git_routes([
        'id_origin' => $origin,
        'id_destiny' => $destiny,
        'departure_time' => $departure_time,
    ]);

    if (count($routes) === 0) {
        wp_safe_redirect($refereder);
        exit;
    }

    $route = $routes[0];
    $transport = git_transport_by_id((int) ($_POST['transport'] ?? '0'));

    if ($transport === null) {
        wp_safe_redirect($refereder);
        exit;
    }

    $passengers = [];

    for ($i = 0; $i < (int) $_POST['passengers']; $i++) {
        $passenger = git_passenger_create([
            'route' => $route,
            'transport' => $transport,
            'date_trip' => $_POST['date_trip'] ?? '',
        ]);
        $passengers[] = $passenger;
    }

    $ticket = git_ticket_create([
        'passengers' => $passengers,
        'flexible' => false,
        'status' => TicketStatus::PERORDER,
        'meta' => [
            'date_created' => date('Y-m-d H:i:s'),
        ]
    ]);

    $ticket->save();

    wp_safe_redirect($refereder);
    exit;
});