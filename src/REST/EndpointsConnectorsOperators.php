<?php
namespace CentralTickets\REST;

use CentralTickets\ConnectorManager;
use CentralTickets\Constants\PassengerConstants;
use CentralTickets\Constants\PriceExtraConstants;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\MetaManager;
use CentralTickets\Operator;
use CentralTickets\Route;
use CentralTickets\Services\Actions\DateTrip;
use CentralTickets\Services\ArrayParser\RouteArray;
use CentralTickets\Services\ArrayParser\TransportArray;
use CentralTickets\Services\PackageData\PassengerData;
use CentralTickets\Services\PackageData\TicketData;
use CentralTickets\Services\TicketService;
use CentralTickets\Services\TransportService;
use CentralTickets\Transport;
use DateTime;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

class EndpointsConnectorsOperators
{
    public function init_endpoints()
    {
        RegisterRoute::register(
            'external',
            'POST',
            function (WP_REST_Request $request) {
                $payload = $request->get_json_params();
                if ($payload === null) {
                    return new WP_REST_Response(['message' => 'Solicitud vacía'], 400);
                }
                $action = $payload['action'] ?? 'register';
                if ($action === 'register') {
                    return $this->register_operator($payload);
                }
                $secret_key = $payload['secret_key'] ?? null;
                if ($secret_key === null) {
                    return new WP_REST_Response(['message' => 'Secret key vacío'], 400);
                }
                $username = MetaManager::get_meta(
                    'connector_secret_key',
                    0,
                    $secret_key,
                );
                if ($username === null) {
                    return new WP_REST_Response(['message' => 'Usuario inexistente'], 400);
                }
                $operator = git_get_operator_by_username($username);
                if (!$operator) {
                    return new WP_REST_Response(['message' => 'Operador inexistente'], 400);
                }
                unset($payload['secret_key'], $payload['action']);
                return $this->handler($operator, $action, $payload);
            }
        );
    }

    private function handler(Operator $operator, string $action, array $data)
    {
        if ($action === 'get_transports') {
            return $this->external_transports($operator);
        } else if ($action === 'get_transport') {
            return $this->external_transport($data['transport_id'] ?? 0);
        } else if ($action === 'get_transport_for_booking') {
            return $this->external_transports_for_booking($operator, $data);
        } else if ($action === 'get_routes') {
            return $this->external_routes($operator);
        } else if ($action === 'get_route') {
            return $this->external_route($data['route_id'] ?? 0);
        } else if ($action === 'get_products') {
            return $this->external_products();
        } else if ($action === 'get_product') {
            return $this->external_product($data['product_id'] ?? 0);
        } else if ($action === 'send_ticket_data') {
            return $this->external_ticket($operator, $data);
        } else if ($action === 'check_availability_transport') {
            return $this->external_check_availability_transport($data);
        } else if ($action === 'check_coupon') {
            return $this->external_check_coupon($operator, $data);
        } else {
            return new WP_REST_Response(['message' => 'Acción inválida'], 400);
        }
    }

    private function external_check_coupon(Operator $operator, array $data)
    {
        $code = $data['code'] ?? null;
        if ($code === null) {
            return new WP_REST_Response(['message' => 'Código de cupón vacío'], 400);
        }
        $coupons = $operator->get_coupons();
        if (sizeof($coupons) <= 0) {
            return new WP_REST_Response(['message' => 'El operador no tiene cupones configurados'], 400);
        }
        foreach ($coupons as $coupon) {
            if ($coupon->post_title === $code) {
                return new WP_REST_Response(['message' => 'Cupon aprobado.']);
            }
        }
        return new WP_REST_Response(['message' => 'Cupon no encontrado.'], 404);
    }

    private function external_transports_for_booking(Operator $operator, array $data)
    {
        $transports = $operator->get_transports();
        $date_trip = $data['date_trip'] ?? (new DateTime)->format('Y-m-d');
        $in_morning = isset($data['schedule']) && $data['schedule'] === 'morning';
        $name_zone_origin = $data['name_zone_origin'] ?? null;
        $name_zone_destiny = $data['name_zone_destiny'] ?? null;
        $results = [];

        foreach ($transports as $transport) {
            foreach ($transport->get_routes() as $route) {
                if (
                    $route->get_origin()->get_zone()->name === $name_zone_origin &&
                    $route->get_destiny()->get_zone()->name === $name_zone_destiny
                ) {
                    if ($transport->is_available($date_trip)) {
                        if ($in_morning && $route->departure_time < '12:00:00') {
                            $results[] = $transport;
                        } else if (!$in_morning && $route->departure_time >= '12:00:00') {
                            $results[] = $transport;
                        }
                    }
                }
            }
        }

        $array_parser = new TransportArray();
        return new WP_REST_Response(array_map(
            fn(Transport $transport) => $array_parser->get_array($transport),
            $results
        ), 200);
    }

    private function external_check_availability_transport(array $data)
    {
        $route_id = $data['route_id'] ?? -1;
        $passengers = $data['passengers'] ?? 1;
        $date_trip = $data['date_trip'] ?? (new DateTime)->format('Y-m-d');
        $transport_id = $data['transport_id'] ?? -1;

        $service = new TransportService();

        $result = $service->check_availability(
            $transport_id,
            $route_id,
            $date_trip,
            $passengers,
        );

        return new WP_REST_Response([
            'available' => $result
        ], 200);
    }

    private function external_ticket(Operator $operator, array $data)
    {
        $coupons = $operator->get_coupons();
        if (sizeof($coupons) <= 0) {
            return new WP_REST_Response(['message' => 'El operador no tiene cupones configurados'], 400);
        }
        $coupon = $coupons[0];
        $order = wc_create_order();
        $order->set_billing_phone($data['customer_info']['phone']);
        $order->set_billing_email($data['customer_info']['email']);
        $order->set_billing_last_name($data['customer_info']['lastname']);
        $order->set_billing_first_name($data['customer_info']['firstname']);
        $order->update_meta_data('_wc_order_attribution_utm_source', $data['host_url']);
        wp_update_post([
            'ID' => $order->get_id(),
            'post_title' => 'Central Tickets (External Ticket) - ' . ($data['terminal'] ?? 'No Name'),
        ]);
        $order->save();
        $ticket = new TicketData(
            $order->get_id(),
            $coupon->ID,
            false,
            $data['total_price'] * 100,
            TicketConstants::PENDING,
            array_map(fn(array $passenger_data) => new PassengerData(
                $passenger_data['name'] ?? '',
                false,
                $passenger_data['nationality'] ?? '',
                $passenger_data['birthday'] ?? '',
                $data['date_trip'] ?? (new DateTime('now'))->format('Y-m-d'),
                $passenger_data['type_document'] ?? '',
                $passenger_data['data_document'] ?? '',
                $passenger_data['type'] ?? PassengerConstants::STANDARD,
                $data['route'] ?? -1,
                $data['transport'] ?? -1,
            ), $data['passengers'] ?? [])
        );

        $service = new TicketService();
        $service->save($ticket);
        return new WP_REST_Response(['order_id' => $order->get_id()], 200);
    }

    private function external_products()
    {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'product_type',
                    'field' => 'slug',
                    'terms' => 'operator',
                    'operator' => 'IN'
                ]
            ]
        ];

        $products_query = new WP_Query($args);
        $products_data = [];

        if ($products_query->have_posts()) {
            while ($products_query->have_posts()) {
                $products_query->the_post();
                if (get_post_meta(get_the_ID(), 'enable_bookeable', true) !== 'yes') {
                    continue;
                }
                $products_data[] = [
                    'id' => get_the_ID(),
                    'permalink' => get_the_permalink(get_the_ID()),
                    'title' => get_the_title()
                ];
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response($products_data);
    }

    private function external_product(int $product_id)
    {
        $product = get_post($product_id);
        if (!$product || $product->post_type !== 'product' || $product->post_status !== 'publish') {
            return new WP_REST_Response(['message' => 'Producto no encontrado'], 404);
        }

        if (get_post_meta($product_id, 'enable_bookeable', true) !== 'yes') {
            return new WP_REST_Response(['message' => 'Producto no habilitado para reserva'], 400);
        }
        $origin_zone = get_post_meta($product_id, 'zone_origin', true);
        $destiny_zone = get_post_meta($product_id, 'zone_destiny', true);
        $origin_zone = git_get_zone_by_id((int) $origin_zone);
        $destiny_zone = git_get_zone_by_id((int) $destiny_zone);
        if (!$origin_zone || !$destiny_zone) {
            return new WP_REST_Response(['message' => 'Zonas de origen o destino inválidas'], 400);
        }
        return new WP_REST_Response([
            'id' => $product->ID,
            'permalink' => get_the_permalink($product->ID),
            'title' => get_the_title($product->ID),
            'info' => [
                'origin' => [
                    'id' => $origin_zone->id,
                    'name' => $origin_zone->name
                ],
                'destiny' => [
                    'id' => $destiny_zone->id,
                    'name' => $destiny_zone->name
                ],
                'date_min' => DateTrip::min_date(),
                'type_way' => get_post_meta($product_id, 'type_way', true),
                'type_transport' => get_post_meta($product_id, 'type_transport', true),
                'enable_switch_route' => (bool) get_post_meta($product_id, 'enable_switch_route', true),
                'split_transport_by_alias' => (bool) get_post_meta($product_id, 'split_transport_by_alias', true),
                'enable_carousel_transports' => (bool) get_post_meta($product_id, 'enable_carousel_transports', true),
                'maximum_extras' => (int) get_post_meta($product_id, 'maximum_extra', true),
                'maximum_persons' => (int) get_post_meta($product_id, 'maximum_person', true),
                'issues' => [
                    PassengerConstants::KID => git_get_setting('form_message_kid', ''),
                    PassengerConstants::RPM => git_get_setting('form_message_rpm', ''),
                    PassengerConstants::STANDARD => git_get_setting('form_message_standard', ''),
                    PriceExtraConstants::EXTRA => git_get_setting('form_message_extra', ''),
                    PriceExtraConstants::FLEXIBLE => git_get_setting('form_message_flexible', ''),
                    PriceExtraConstants::TERMS_CONDITIONS => git_get_setting('form_message_terms_conditions', ''),
                ],
                'prices' => [
                    PassengerConstants::KID => (int) get_post_meta($product_id, 'price_kid', true),
                    PassengerConstants::RPM => (int) get_post_meta($product_id, 'price_rpm', true),
                    PassengerConstants::STANDARD => (int) get_post_meta($product_id, 'price_standar', true),
                    PriceExtraConstants::EXTRA => (int) get_post_meta($product_id, 'price_extra', true),
                    PriceExtraConstants::FLEXIBLE => (int) get_post_meta($product_id, 'price_flexible', true),
                ],
            ],
        ]);
    }

    private function external_transports(Operator $operator)
    {
        $array_parser = new TransportArray();
        $transports = $operator->get_transports();
        return new WP_REST_Response(array_map(
            fn(Transport $transport) => $array_parser->get_array($transport),
            $transports
        ), 200);
    }

    private function external_transport(int $transport_id)
    {
        $array_parser = new TransportArray();
        $transport = git_get_transport_by_id($transport_id);
        if (!$transport) {
            return new WP_REST_Response(['message' => 'Transporte no encontrado'], 404);
        }
        return new WP_REST_Response($array_parser->get_array($transport), 200);
    }

    private function external_routes(Operator $operator)
    {
        $array_parser = new RouteArray();
        $routes = [];
        foreach ($operator->get_transports() as $transport) {
            foreach ($transport->get_routes() as $route) {
                $routes[$route->id] = $route;
            }
        }
        return new WP_REST_Response(array_map(
            fn(Route $route) => $array_parser->get_array($route),
            array_values($routes)
        ), 200);
    }

    private function external_route(int $route_id)
    {
        $array_parser = new RouteArray();
        $route = git_get_route_by_id($route_id);
        if (!$route) {
            return new WP_REST_Response(['message' => 'Ruta no encontrada'], 404);
        }
        return new WP_REST_Response($array_parser->get_array($route), 200);
    }

    private function register_operator(array $data)
    {
        $key = $data['secret_key'] ?? null;
        if ($key === null) {
            return new WP_REST_Response(['message' => 'Token vacío'], 400);
        }
        $result = ConnectorManager::get_instance()->validate_key($key);
        if ($result === false) {
            return new WP_REST_Response(['message' => 'Token inválido'], 400);
        }
        $username = ConnectorManager::get_instance()->get_username_by_key($key);
        ConnectorManager::get_instance()->revoke_key($username);
        $secret_key = hash_hmac('sha256', $username . '-' . time(), git_get_secret_key());
        MetaManager::set_meta(
            'connector_secret_key',
            0,
            $secret_key,
            $username,
        );
        return new WP_REST_Response([
            'secret_key' => $secret_key,
            'delivery_url' => home_url('/wp-json/' . RegisterRoute::prefix . 'external'),
        ], 200);
    }
}
