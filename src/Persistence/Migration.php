<?php
namespace CentralTickets\Persistence;

use CentralTickets\Configurations;
use CentralTickets\Constants\UserConstants;
use CentralTickets\Location;
use CentralTickets\MetaManager;
use CentralTickets\Operator;
use CentralTickets\Route;
use CentralTickets\Service;
use CentralTickets\Transport;
use CentralTickets\Zone;
use WC_Product;
use wpdb;

defined('ABSPATH') || exit;

final class Migration
{
    private function create_tables()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        global $wpdb;
        foreach ($this->tables($wpdb) as $sql) {
            dbDelta($sql);
        }
    }

    public function init()
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}git_tickets";
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $this->create_tables();
        }
    }

    private function tables(wpdb $wpdb)
    {
        $charset_collate = $wpdb->get_charset_collate();
        return [
            "CREATE TABLE {$wpdb->prefix}git_webhooks(  
            id              BIGINT UNSIGNED                     NOT NULL PRIMARY KEY AUTO_INCREMENT,
            name            VARCHAR(255)                        NOT NULL,
            status          VARCHAR(50)                         NOT NULL,
            topic           VARCHAR(255)                        NOT NULL,
            delivery_url    VARCHAR(500)                        NOT NULL,
            secret          VARCHAR(255)                        NOT NULL
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_meta (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            meta_type       VARCHAR(50)                         NOT NULL,
            meta_id         BIGINT UNSIGNED                     NOT NULL,
            meta_key        VARCHAR(255)                        NOT NULL,
            meta_value      LONGTEXT                            NOT NULL
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_settings (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            setting_key     VARCHAR(255)                        NOT NULL,
            setting_value   LONGTEXT                            NOT NULL
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_log (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            timestamp       DATETIME DEFAULT (CURRENT_DATE)     NOT NULL,
            level           VARCHAR(255)                        NOT NULL,
            source          VARCHAR(255)                        NOT NULL,
            id_source       BIGINT UNSIGNED                     DEFAULT NULL,
            message         LONGTEXT                            NOT NULL
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_zones (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            name            VARCHAR(50)                         NOT NULL
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_locations (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            name            VARCHAR(50)                         NOT NULL,
            id_zone         BIGINT UNSIGNED                     NOT NULL,
            FOREIGN KEY (id_zone) REFERENCES {$wpdb->prefix}git_zones(id)
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_services (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            name            VARCHAR(50)                         NOT NULL,
            price           BIGINT UNSIGNED DEFAULT 0           NOT NULL,
            icon            VARCHAR(255)                        NOT NULL
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_routes (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            id_origin       BIGINT UNSIGNED                     NOT NULL,
            id_destiny      BIGINT UNSIGNED                     NOT NULL,
            departure_time  TIME                                NOT NULL,
            duration_trip   TIME                                NOT NULL,
            type            VARCHAR(50)                         NOT NULL,
            distance_km     FLOAT UNSIGNED                      NOT NULL,
            FOREIGN KEY (id_origin) REFERENCES {$wpdb->prefix}git_locations(id),
            FOREIGN KEY (id_destiny) REFERENCES {$wpdb->prefix}git_locations(id)
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_transports (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            id_operator     BIGINT UNSIGNED                     NOT NULL,
            nicename        VARCHAR(255)                        UNIQUE NOT NULL,
            code            VARCHAR(50)                         UNIQUE NOT NULL,
            type            VARCHAR(50)                         NOT NULL,
            FOREIGN KEY (id_operator) REFERENCES {$wpdb->prefix}users(id)
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_routes_transports (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            id_route        BIGINT UNSIGNED                     NOT NULL,
            id_transport    BIGINT UNSIGNED                     NOT NULL,
            FOREIGN KEY (id_route) REFERENCES {$wpdb->prefix}git_routes(id),
            FOREIGN KEY (id_transport) REFERENCES {$wpdb->prefix}git_transports(id)
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_transports_services (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            id_service      BIGINT UNSIGNED                     NOT NULL,
            id_transport    BIGINT UNSIGNED                     NOT NULL,
            FOREIGN KEY (id_service) REFERENCES {$wpdb->prefix}git_services(id),
            FOREIGN KEY (id_transport) REFERENCES {$wpdb->prefix}git_transports(id)
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_tickets (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            id_order        BIGINT UNSIGNED                     NOT NULL,
            id_coupon       BIGINT UNSIGNED                     DEFAULT NULL,
            id_client       BIGINT UNSIGNED                     DEFAULT NULL,
            total_amount    BIGINT UNSIGNED DEFAULT 0           NOT NULL,
            flexible        BOOLEAN DEFAULT FALSE               NOT NULL,
            status          VARCHAR(50) DEFAULT 'pending'       NOT NULL,
            FOREIGN KEY (id_order) REFERENCES {$wpdb->prefix}posts(id),
            FOREIGN KEY (id_coupon) REFERENCES {$wpdb->prefix}posts(id),
            FOREIGN KEY (id_client) REFERENCES {$wpdb->prefix}users(id)
        ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}git_passengers (
            id              BIGINT UNSIGNED                     PRIMARY KEY AUTO_INCREMENT,
            name            VARCHAR(50)                         NOT NULL,
            nationality     VARCHAR(50)                         NOT NULL,
            type_document   VARCHAR(50)                         NOT NULL,
            data_document   VARCHAR(20)                         NOT NULL,
            birthday        DATE                                NOT NULL,
            date_trip       DATE DEFAULT (CURRENT_DATE)         NOT NULL,
            served          BOOLEAN DEFAULT FALSE               NOT NULL,
            approved        BOOLEAN DEFAULT FALSE               NOT NULL,
            type            VARCHAR(50)                         NOT NULL,
            id_ticket       BIGINT UNSIGNED                     NOT NULL,
            id_route        BIGINT UNSIGNED                     NOT NULL,
            id_transport    BIGINT UNSIGNED                     NOT NULL,
            FOREIGN KEY (id_route) REFERENCES {$wpdb->prefix}git_routes(id),
            FOREIGN KEY (id_ticket) REFERENCES {$wpdb->prefix}git_tickets(id),
            FOREIGN KEY (id_transport) REFERENCES {$wpdb->prefix}git_transports(id)
        ) $charset_collate;"
        ];
    }

    public function get_data(bool $settings, bool $entities, bool $products)
    {
        return [
            'settings' => $settings ? Configurations::get_all() : [],
            'entities' => $entities ? [
                'zones' => array_map(fn(Zone $zone) => [
                    'id' => $zone->id,
                    'name' => $zone->name,
                ], git_get_zones()),
                'routes' => array_map(fn(Route $route) => [
                    'id' => $route->id,
                    'type' => $route->type,
                    'origin_id' => $route->get_origin()->id,
                    'destiny_id' => $route->get_destiny()->id,
                    'distance_km' => $route->distance_km,
                    'duration_trip' => $route->duration_trip,
                    'departure_time' => $route->departure_time,
                ], git_get_routes()),
                'services' => array_map(fn(Service $service) => [
                    'id' => $service->id,
                    'icon' => $service->icon,
                    'name' => $service->name,
                    'price' => $service->price,
                ], git_get_services()),
                'locations' => array_map(fn(Location $location) => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'zone_id' => $location->get_zone()->id,
                ], git_get_locations()),
                'transports' => array_map(fn(Transport $transport) => [
                    'id' => $transport->id,
                    'type' => $transport->type,
                    'code' => $transport->code,
                    'nicename' => $transport->nicename,
                    'routes_ids' => array_map(fn(Route $route) => $route->id, $transport->get_routes()),
                    'services_ids' => array_map(fn(Service $service) => $service->id, $transport->get_services()),
                    'meta' => MetaManager::get_metadata(MetaManager::TRANSPORT, $transport->id),
                ], git_get_transports()),
            ] : [],
            'products' => $products ? array_map(fn(WC_Product $p) => [
                'id' => $p->get_id(),
                'name' => $p->get_name(),
                'type' => $p->get_type(),
                'type_way' => $p->get_meta('type_way'),
                'type_transport' => $p->get_meta('type_transport'),
                'type_route' => $p->get_meta('type_route'),
                'status' => method_exists($p, 'get_status') ? $p->get_status() : get_post_status($p->get_id()),
                'prices' => [
                    'price_sale' => $p->get_sale_price(),
                    'price_regular' => $p->get_regular_price(),
                    'price_kid' => $p->get_meta('price_kid'),
                    'price_rpm' => $p->get_meta('price_rpm'),
                    'price_extra' => $p->get_meta('price_extra'),
                    'price_standar' => $p->get_meta('price_standar'),
                    'price_flexible' => $p->get_meta('price_flexible'),
                ],
                'maximums' => [
                    'maximum_extra' => $p->get_meta('maximum_extra'),
                    'maximum_person' => $p->get_meta('maximum_person'),
                ],
                'zones' => [
                    'zone_origin' => $p->get_meta('zone_origin'),
                    'zone_destiny' => $p->get_meta('zone_destiny'),
                ],
                'locations' => [
                    'location_origin' => $p->get_meta('location_origin'),
                    'location_destiny' => $p->get_meta('location_destiny'),
                ],
                'enable_bookeable' => $p->get_meta('enable_bookeable') === 'yes',
                'enable_switch_route' => $p->get_meta('enable_switch_route') === 'yes',
            ], wc_get_products([
                    'limit' => -1,
                    'type' => 'operator',
                    'status' => ['publish', 'pending', 'draft', 'future', 'private', 'trash', 'auto-draft'],
                ])) : [],
        ];
    }

    public function set_data(array $data)
    {
        if (
            !isset($data['settings'], $data['entities'], $data['products']) ||
            !is_array($data['settings']) ||
            !is_array($data['entities']) ||
            !is_array($data['products'])
        ) {
            return;
        }

        foreach ($data['settings'] as $key => $value) {
            Configurations::set($key, $value);
        }

        $products_map = [];

        foreach ($data['products'] as $key => $value) {
            $post_id = wp_insert_post([
                'post_title' => $value['name'] ?? '',
                'post_type' => 'product',
                'post_status' => $value['status'] ?? 'draft',
            ], true);
            wp_set_object_terms($post_id, $value['type'] ?? 'operator', 'product_type', false);

            update_post_meta($post_id, '_sale_price', $value['price_sale'] ?? '');
            update_post_meta($post_id, '_regular_price', $value['price_regular'] ?? '');
            update_post_meta($post_id, 'type_way', $value['type_way'] ?? '');
            update_post_meta($post_id, 'type_route', $value['type_route'] ?? '');
            update_post_meta($post_id, 'type_transport', $value['type_transport'] ?? '');
            update_post_meta($post_id, 'price_kid', $value['price_kid'] ?? '');
            update_post_meta($post_id, 'price_rpm', $value['price_rpm'] ?? '');
            update_post_meta($post_id, 'price_standar', $value['price_standar'] ?? '');
            update_post_meta($post_id, 'price_flexible', $value['price_flexible'] ?? '');
            update_post_meta($post_id, 'maximum_extra', $value['maximum_extra'] ?? '');
            update_post_meta($post_id, 'maximum_person', $value['maximum_person'] ?? '');
            update_post_meta($post_id, 'enable_bookeable', $value['enable_bookeable'] ? 'yes' : 'no');
            update_post_meta($post_id, 'enable_switch_route', $value['enable_switch_route'] ? 'yes' : 'no');
            $products_map[] = [
                'id' => $post_id,
                'zones' => [
                    'origin' => $value['zones']['zone_origin'] ?? -1,
                    'destiny' => $value['zones']['zone_destiny'] ?? -1,
                ],
                'locations' => [
                    'origin' => $value['locations']['location_origin'] ?? -1,
                    'destiny' => $value['locations']['location_destiny'] ?? -1,
                ],
            ];
        }

        if (empty($data['entities']['zones']) || !is_array($data['entities']['zones'])) {
            return;
        }

        $zones_ids_map = [];
        $routes_ids_map = [];
        $services_ids_map = [];
        $locations_ids_map = [];
        $transports_ids_map = [];

        foreach ($data['entities']['zones'] as $key => $value) {
            $zone = new Zone();
            $zone->name = $value['name'] ?? '';
            $repository = git_get_query_persistence()->get_zone_repository();
            $zone_saved = $repository->save($zone);
            $zones_ids_map[$value['id']] = $zone_saved->id;
        }

        foreach ($data['entities']['services'] as $key => $value) {
            $service = new Service();

            $service->name = $value['name'] ?? '';
            $service->icon = $value['icon'] ?? '';
            $service->price = $value['price'] ?? '';

            $repository = git_get_query_persistence()->get_service_repository();
            $service_saved = $repository->save($service);
            $services_ids_map[$value['id']] = $service_saved->id;
        }

        foreach ($data['entities']['locations'] as $key => $value) {
            $zone = new Zone();
            $location = new Location();

            $location->name = $value['name'] ?? '';
            $zone->id = $zones_ids_map[$value['zone_id']] ?? -1;
            $location->set_zone($zone);

            $repository = git_get_query_persistence()->get_location_repository();
            $location_saved = $repository->save($location);
            $locations_ids_map[$value['id']] = $location_saved->id;
        }

        foreach ($data['entities']['routes'] as $key => $value) {
            $route = new Route();
            $origin = new Location();
            $destiny = new Location();

            $origin->id = $locations_ids_map[$value['origin_id']] ?? -1;
            $destiny->id = $locations_ids_map[$value['destiny_id']] ?? -1;
            $route->type = $value['type'] ?? '';
            $route->distance_km = $value['distance_km'] ?? '';
            $route->duration_trip = $value['duration_trip'] ?? '';
            $route->departure_time = $value['departure_time'] ?? '';
            $route->set_origin($origin);
            $route->set_destiny($destiny);

            $repository = git_get_query_persistence()->get_route_repository();
            $route_saved = $repository->save($route);
            $routes_ids_map[$value['id']] = $route_saved->id;
        }

        foreach ($data['entities']['transports'] as $key => $value) {
            $routes = [];
            $services = [];
            $transport = new Transport();

            $transport->type = $value['type'] ?? '';
            $transport->code = $value['code'] ?? '';
            $transport->nicename = $value['nicename'] ?? '';

            foreach ($value['routes_ids'] ?? [] as $id_route) {
                $route = new Route();
                $route->id = $routes_ids_map[$id_route] ?? -1;
                if ($route->id !== -1) {
                    $routes[] = $route;
                }
            }

            foreach ($value['services_ids'] ?? [] as $id_service) {
                $service = new Service();
                $service->id = $services_ids_map[$id_service] ?? -1;
                if ($service->id !== -1) {
                    $services[] = $service;
                }
            }

            $transport->set_routes($routes);
            $transport->set_services($services);
            $operator = new Operator(wp_get_current_user()->ID);
            $transport->set_operator($operator);

            $repository = git_get_query_persistence()->get_transport_repository();
            $transport_saved = $repository->save($transport);
            $transports_ids_map[$value['id']] = [
                'id' => $transport_saved->id,
                'meta' => $value['meta'] ?? [],
            ];
        }

        foreach ($transports_ids_map as $key => $value) {
            MetaManager::set_metadata(
                MetaManager::TRANSPORT,
                $value['id'],
                $value['meta'],
            );
        }

        foreach ($products_map as $key => $value) {
            update_post_meta($value['id'], 'zone_origin', $zones_ids_map[$value['zones']['origin']] ?? -1);
            update_post_meta($value['id'], 'zone_destiny', $zones_ids_map[$value['zones']['destiny']] ?? -1);
            update_post_meta($value['id'], 'location_origin', $locations_ids_map[$value['locations']['origin']] ?? -1);
            update_post_meta($value['id'], 'location_destiny', $locations_ids_map[$value['locations']['destiny']] ?? -1);
        }
    }
}
