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
use WP_Post;
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

    public function get_data(bool $settings, bool $entities, bool $products, bool $operators, bool $coupons)
    {
        $exporter = new ExportData();
        return $exporter->export([
            'coupons' => $coupons,
            'settings' => $settings,
            'entities' => $entities,
            'products' => $products,
            'operators' => $operators,
        ]);
    }

    public function set_data(array $data)
    {
        $importer = new ImportData();
        $importer->import($data);
    }
}
