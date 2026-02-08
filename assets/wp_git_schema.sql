-- Active: 1769490781795@@127.0.0.1@3306@wordpress
-- ================================================================
-- SCHEMA EXPORT: WordPress GitTransport System Tables
-- Generated on: February 5, 2026
-- Description: Database schema for the custom transport booking system
-- ================================================================

-- Disable foreign key checks to avoid issues during creation
SET FOREIGN_KEY_CHECKS = 0;

-- ================================================================
-- CORE REFERENCE TABLES
-- ================================================================

-- Zones Table: Stores zones points
CREATE TABLE `wp_git_zones` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 13 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Locations Table: Stores origin and destination points
CREATE TABLE `wp_git_locations` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `id_zone` bigint unsigned DEFAULT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `id_zone` (`id_zone`),
    CONSTRAINT `wp_git_locations_zone_fk` FOREIGN KEY (`id_zone`) REFERENCES `wp_git_zones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 13 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Services Table: Stores available transport services
CREATE TABLE `wp_git_services` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `price` bigint unsigned NOT NULL DEFAULT '0',
    `icon` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- ================================================================
-- TRANSPORT MANAGEMENT TABLES
-- ================================================================

-- Transports Table: Main transport vehicles/units
CREATE TABLE `wp_git_transports` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_operator` bigint unsigned NOT NULL,
    `nicename` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `code` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `nicename` (`nicename`),
    UNIQUE KEY `code` (`code`),
    KEY `id_operator` (`id_operator`),
    CONSTRAINT `wp_git_transports_ibfk_1` FOREIGN KEY (`id_operator`) REFERENCES `wp_users` (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 11 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Routes Table: Available routes between locations
CREATE TABLE `wp_git_routes` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_origin` bigint unsigned NOT NULL,
    `id_destiny` bigint unsigned NOT NULL,
    `departure_time` time NOT NULL,
    `arrival_time` time NOT NULL,
    `type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `id_origin` (`id_origin`),
    KEY `id_destiny` (`id_destiny`),
    CONSTRAINT `wp_git_routes_ibfk_1` FOREIGN KEY (`id_origin`) REFERENCES `wp_git_locations` (`id`),
    CONSTRAINT `wp_git_routes_ibfk_2` FOREIGN KEY (`id_destiny`) REFERENCES `wp_git_locations` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- ================================================================
-- RELATIONSHIP TABLES (Many-to-Many)
-- ================================================================

-- Routes-Transports Relationship: Which transports serve which routes
CREATE TABLE `wp_git_routes_transports` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_route` bigint unsigned NOT NULL,
    `id_transport` bigint unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `id_route` (`id_route`),
    KEY `id_transport` (`id_transport`),
    CONSTRAINT `wp_git_routes_transports_ibfk_1` FOREIGN KEY (`id_route`) REFERENCES `wp_git_routes` (`id`),
    CONSTRAINT `wp_git_routes_transports_ibfk_2` FOREIGN KEY (`id_transport`) REFERENCES `wp_git_transports` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 409 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Transports-Services Relationship: Which services are available on each transport
CREATE TABLE `wp_git_transports_services` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_service` bigint unsigned NOT NULL,
    `id_transport` bigint unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `id_service` (`id_service`),
    KEY `id_transport` (`id_transport`),
    CONSTRAINT `wp_git_transports_services_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `wp_git_services` (`id`),
    CONSTRAINT `wp_git_transports_services_ibfk_2` FOREIGN KEY (`id_transport`) REFERENCES `wp_git_transports` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 265 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- ================================================================
-- BOOKING AND PASSENGER MANAGEMENT
-- ================================================================

-- Tickets Table: Main booking records
CREATE TABLE `wp_git_tickets` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `id_order` bigint unsigned DEFAULT NULL,
    `id_coupon` bigint unsigned DEFAULT NULL,
    `id_client` bigint unsigned DEFAULT NULL,
    `total_amount` bigint unsigned NOT NULL DEFAULT '0',
    `flexible` tinyint(1) NOT NULL DEFAULT '0',
    `status` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id`),
    KEY `id_order` (`id_order`),
    KEY `id_coupon` (`id_coupon`),
    KEY `id_client` (`id_client`),
    CONSTRAINT `wp_git_tickets_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `wp_posts` (`ID`),
    CONSTRAINT `wp_git_tickets_ibfk_2` FOREIGN KEY (`id_coupon`) REFERENCES `wp_posts` (`ID`),
    CONSTRAINT `wp_git_tickets_ibfk_3` FOREIGN KEY (`id_client`) REFERENCES `wp_users` (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 37 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Passengers Table: Individual passenger records
CREATE TABLE `wp_git_passengers` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `nationality` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `type_document` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `data_document` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `birthday` date NOT NULL,
    `date_trip` date NOT NULL DEFAULT(curdate()),
    `served` tinyint(1) NOT NULL DEFAULT '0',
    `approved` tinyint(1) NOT NULL DEFAULT '0',
    `type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `id_ticket` bigint unsigned NOT NULL,
    `id_route` bigint unsigned NOT NULL,
    `id_transport` bigint unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `id_route` (`id_route`),
    KEY `id_ticket` (`id_ticket`),
    KEY `id_transport` (`id_transport`),
    CONSTRAINT `wp_git_passengers_ibfk_1` FOREIGN KEY (`id_route`) REFERENCES `wp_git_routes` (`id`),
    CONSTRAINT `wp_git_passengers_ibfk_2` FOREIGN KEY (`id_ticket`) REFERENCES `wp_git_tickets` (`id`),
    CONSTRAINT `wp_git_passengers_ibfk_3` FOREIGN KEY (`id_transport`) REFERENCES `wp_git_transports` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 81 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- ================================================================
-- SYSTEM ADMINISTRATION TABLES
-- ================================================================

-- Meta Table: Generic metadata storage for extensibility
CREATE TABLE `wp_git_meta` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `meta_type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `meta_id` bigint unsigned NOT NULL,
    `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `meta_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 296 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Log Table: System activity and error logging
CREATE TABLE `wp_git_log` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `timestamp` datetime NOT NULL DEFAULT(curdate()),
    `level` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `source` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `id_source` bigint unsigned DEFAULT NULL,
    `message` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 16 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Webhooks Table: External API integration endpoints
CREATE TABLE `wp_git_webhooks` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `status` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `topic` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `delivery_url` varchar(500) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- SCHEMA SUMMARY
-- ================================================================
/*
Table Relationships:
1. wp_git_zones -> wp_git_locations (zone assignment)
2. wp_git_locations -> wp_git_routes (origin/destiny)
3. wp_git_transports -> wp_git_routes (via wp_git_routes_transports)
4. wp_git_services -> wp_git_transports (via wp_git_transports_services)
5. wp_git_tickets -> wp_users (client), wp_posts (order/coupon)
6. wp_git_passengers -> wp_git_routes, wp_git_tickets, wp_git_transports
7. wp_git_transports -> wp_users (operator)

Core Features:
- Multi-location route management
- Transport fleet management with operators
- Service offerings and pricing
- Ticket booking with passenger details
- Flexible metadata storage
- Comprehensive logging system
- Webhook integration for external APIs
*/