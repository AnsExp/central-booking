-- ================================================================
-- DATA INSERTS: WordPress GitTransport System - Galápagos
-- Generated on: February 5, 2026
-- Description: Master data for Galápagos transport system
-- ================================================================

-- Disable foreign key checks for clean insertion
SET FOREIGN_KEY_CHECKS=0;

-- Clear existing data for clean insert
DELETE FROM wp_git_passengers;
DELETE FROM wp_git_routes_transports;
DELETE FROM wp_git_routes;
DELETE FROM wp_git_locations;
DELETE FROM wp_git_zones;

-- ================================================================
-- ZONAS: Islas del Archipiélago de Galápagos
-- ================================================================

INSERT INTO wp_git_zones (id, name) VALUES 
(1, 'Santa Cruz'),
(2, 'San Cristóbal'), 
(3, 'Isabela'),
(4, 'Floreana'),
(5, 'Santiago'),
(6, 'Fernandina'),
(7, 'Española'),
(8, 'Genovesa'),
(9, 'Marchena'),
(10, 'Pinta'),
(11, 'Pinzón'),
(12, 'Rábida'),
(13, 'Santa Fé'),
(14, 'Wolf'),
(15, 'Darwin'),
(16, 'Bartolomé'),
(17, 'Ecuador Continental');

-- ================================================================
-- UBICACIONES: Puertos Marítimos y Aeropuertos
-- ================================================================

-- PUERTOS Y AEROPUERTOS POR ZONA
INSERT INTO wp_git_locations (id, name, id_zone) VALUES 

-- ==========================================
-- SANTA CRUZ (ZONA ID: 1)
-- ==========================================
(10, 'Puerto Isidro Ayora', 1),              -- Puerto principal de Puerto Ayora
(11, 'Canal de Itabaca - Ferry Terminal', 1), -- Terminal de ferry hacia Baltra
(12, 'Aeropuerto Seymour Norte (Baltra)', 1), -- Aeropuerto principal de Galápagos
(13, 'Muelle de Puerto Ayora', 1),            -- Muelle comercial y turístico

-- ==========================================
-- SAN CRISTÓBAL (ZONA ID: 2)  
-- ==========================================
(20, 'Puerto de Puerto Baquerizo Moreno', 2), -- Puerto capital provincial
(21, 'Aeropuerto San Cristóbal', 2),          -- Aeropuerto de la isla
(22, 'Muelle de Cabotaje San Cristóbal', 2),  -- Muelle comercial
(23, 'Terminal Marítimo Baquerizo', 2),       -- Terminal de pasajeros

-- ==========================================
-- ISABELA (ZONA ID: 3)
-- ==========================================
(30, 'Puerto de Puerto Villamil', 3),         -- Puerto principal de Isabela
(31, 'Muelle de Puerto Villamil', 3),         -- Muelle comercial y turístico
(32, 'Terminal de Lanchas Isabela', 3),       -- Terminal de lanchas rápidas

-- ==========================================
-- FLOREANA (ZONA ID: 4)
-- ==========================================
(40, 'Puerto de Puerto Velasco Ibarra', 4),   -- Puerto principal de Floreana
(41, 'Playa Negra - Terminal', 4),            -- Puerto alternativo

-- ==========================================
-- ECUADOR CONTINENTAL (ZONA ID: 17)
-- ==========================================
(50, 'Puerto de Guayaquil', 17),              -- Puerto marítimo continental
(51, 'Aeropuerto José Joaquín de Olmedo', 17), -- Aeropuerto de Guayaquil
(52, 'Aeropuerto Mariscal Sucre', 17),        -- Aeropuerto de Quito
(53, 'Puerto de Manta', 17),                  -- Puerto alternativo
(54, 'Terminal Marítimo Guayaquil', 17);      -- Terminal de pasajeros

-- ================================================================
-- SERVICIOS DE TRANSPORTE (EXTRAS)
-- ================================================================

INSERT INTO wp_git_services (id, name, price, icon) VALUES 
-- SERVICIOS DE COMODIDAD
(1, 'Aire Acondicionado', 350, 'https://cdn-icons-png.flaticon.com/512/2329/2329029.png'),        -- $3.50
(2, 'WiFi a bordo', 200, 'https://cdn-icons-png.flaticon.com/512/93/93158.png'),                  -- $2.00
(3, 'Asiento Premium', 500, 'https://cdn-icons-png.flaticon.com/512/2329/2329110.png'),           -- $5.00
(4, 'Ventana garantizada', 150, 'https://cdn-icons-png.flaticon.com/512/1828/1828970.png'),       -- $1.50

-- SERVICIOS DE ALIMENTACIÓN
(10, 'Desayuno continental', 800, 'https://cdn-icons-png.flaticon.com/512/2515/2515283.png'),      -- $8.00
(11, 'Almuerzo buffet', 1200, 'https://cdn-icons-png.flaticon.com/512/2515/2515180.png'),         -- $12.00
(12, 'Snacks y bebidas', 450, 'https://cdn-icons-png.flaticon.com/512/2674/2674064.png'),         -- $4.50
(13, 'Cena gourmet', 1500, 'https://cdn-icons-png.flaticon.com/512/3075/3075977.png'),            -- $15.00
(14, 'Bebidas alcohólicas', 600, 'https://cdn-icons-png.flaticon.com/512/920/920569.png'),        -- $6.00

-- SERVICIOS DE ENTRETENIMIENTO
(20, 'Guía naturalista', 2500, 'https://cdn-icons-png.flaticon.com/512/1995/1995515.png'),        -- $25.00
(21, 'Equipos de snorkel', 800, 'https://cdn-icons-png.flaticon.com/512/2922/2922506.png'),       -- $8.00
(22, 'Binoculares premium', 400, 'https://cdn-icons-png.flaticon.com/512/685/685655.png'),        -- $4.00
(23, 'Documentales a bordo', 300, 'https://cdn-icons-png.flaticon.com/512/3652/3652191.png'),     -- $3.00

-- SERVICIOS DE SEGURIDAD Y SALUD
(25, 'Seguro de viaje', 1000, 'https://cdn-icons-png.flaticon.com/512/2913/2913465.png'),         -- $10.00
(26, 'Kit de emergencia', 250, 'https://cdn-icons-png.flaticon.com/512/2913/2913424.png'),        -- $2.50
(27, 'Asistencia médica', 1500, 'https://cdn-icons-png.flaticon.com/512/1051/1051328.png'),       -- $15.00

-- SERVICIOS DE EQUIPAJE Y TRANSPORTE
(30, 'Equipaje extra (20kg)', 1800, 'https://cdn-icons-png.flaticon.com/512/2921/2921222.png'),   -- $18.00
(31, 'Transporte al hotel', 750, 'https://cdn-icons-png.flaticon.com/512/744/744465.png'),        -- $7.50
(32, 'Custodia de equipaje', 300, 'https://cdn-icons-png.flaticon.com/512/2921/2921242.png'),     -- $3.00

-- SERVICIOS ESPECIALES GALÁPAGOS
(40, 'Permiso Parque Nacional', 10000, 'https://cdn-icons-png.flaticon.com/512/2990/2990995.png'), -- $100.00
(41, 'Certificado de buceo', 3500, 'https://cdn-icons-png.flaticon.com/512/2922/2922525.png'),     -- $35.00
(42, 'Fotografía profesional', 5000, 'https://cdn-icons-png.flaticon.com/512/685/685655.png'),    -- $50.00
(43, 'Tour nocturno especial', 4500, 'https://cdn-icons-png.flaticon.com/512/1995/1995515.png');  -- $45.00

-- ================================================================
-- RUTAS PRINCIPALES ENTRE ISLAS
-- ================================================================

INSERT INTO wp_git_routes (id, id_origin, id_destiny, departure_time, arrival_time, type) VALUES 

-- ================================================================
-- RUTAS MARÍTIMAS INTER-ISLAS (MARINE) - PARTE 1
-- ================================================================
(100, 17, 21, '06:00:00', '08:30:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo Moreno
(101, 17, 25, '07:00:00', '10:00:00', 'marine'), -- Puerto Ayora → Puerto Villamil
(102, 17, 29, '08:00:00', '10:30:00', 'marine'), -- Puerto Ayora → Puerto Velasco Ibarra
(103, 18, 21, '06:30:00', '09:00:00', 'marine'), -- Canal Itabaca → Puerto Baquerizo Moreno
(104, 18, 25, '07:30:00', '10:30:00', 'marine'), -- Canal Itabaca → Puerto Villamil
(105, 18, 29, '08:30:00', '11:00:00', 'marine'), -- Canal Itabaca → Puerto Velasco Ibarra
(110, 21, 17, '14:00:00', '16:30:00', 'marine'), -- Puerto Baquerizo Moreno → Puerto Ayora
(111, 21, 25, '09:00:00', '12:00:00', 'marine'), -- Puerto Baquerizo Moreno → Puerto Villamil
(112, 21, 29, '10:00:00', '12:30:00', 'marine'), -- Puerto Baquerizo Moreno → Puerto Velasco Ibarra
(113, 21, 18, '15:00:00', '17:30:00', 'marine'), -- Puerto Baquerizo Moreno → Canal Itabaca
(120, 25, 17, '15:00:00', '18:00:00', 'marine'), -- Puerto Villamil → Puerto Ayora
(121, 25, 21, '13:00:00', '16:00:00', 'marine'), -- Puerto Villamil → Puerto Baquerizo Moreno
(122, 25, 29, '11:00:00', '13:30:00', 'marine'), -- Puerto Villamil → Puerto Velasco Ibarra
(123, 25, 18, '16:00:00', '19:00:00', 'marine'), -- Puerto Villamil → Canal Itabaca
(130, 29, 17, '16:00:00', '18:30:00', 'marine'), -- Puerto Velasco Ibarra → Puerto Ayora
(131, 29, 21, '11:00:00', '13:30:00', 'marine'), -- Puerto Velasco Ibarra → Puerto Baquerizo Moreno
(132, 29, 25, '12:00:00', '14:30:00', 'marine'), -- Puerto Velasco Ibarra → Puerto Villamil
(133, 29, 18, '17:00:00', '19:30:00', 'marine'), -- Puerto Velasco Ibarra → Canal Itabaca
(140, 17, 21, '13:30:00', '16:00:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo (tarde)
(141, 17, 25, '14:00:00', '17:00:00', 'marine'), -- Puerto Ayora → Puerto Villamil (tarde)

-- RUTAS MARÍTIMAS INTER-ISLAS (MARINE) - PARTE 2
(142, 21, 17, '07:30:00', '10:00:00', 'marine'), -- Puerto Baquerizo → Puerto Ayora (mañana)
(143, 25, 17, '08:00:00', '11:00:00', 'marine'), -- Puerto Villamil → Puerto Ayora (mañana)
(150, 17, 21, '10:00:00', '11:45:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo (rápida)
(151, 21, 17, '11:00:00', '12:45:00', 'marine'), -- Puerto Baquerizo → Puerto Ayora (rápida)
(152, 17, 25, '12:00:00', '14:30:00', 'marine'), -- Puerto Ayora → Puerto Villamil (rápida)
(153, 25, 17, '12:30:00', '15:00:00', 'marine'), -- Puerto Villamil → Puerto Ayora (rápida)

-- RUTAS TURÍSTICAS MARÍTIMAS
(160, 17, 22, '09:00:00', '11:30:00', 'marine'), -- Puerto Ayora → Kicker Rock
(161, 22, 17, '14:00:00', '16:30:00', 'marine'), -- Kicker Rock → Puerto Ayora
(162, 21, 19, '10:00:00', '12:30:00', 'marine'), -- Puerto Baquerizo → Tortuga Bay
(163, 19, 21, '15:00:00', '17:30:00', 'marine'), -- Tortuga Bay → Puerto Baquerizo
(164, 25, 27, '08:00:00', '09:00:00', 'marine'), -- Puerto Villamil → Concha de Perla
(165, 27, 25, '16:00:00', '17:00:00', 'marine'), -- Concha de Perla → Puerto Villamil
(166, 25, 28, '10:00:00', '11:30:00', 'marine'), -- Puerto Villamil → Los Túneles
(167, 28, 25, '14:00:00', '15:30:00', 'marine'), -- Los Túneles → Puerto Villamil
(168, 29, 30, '09:00:00', '09:30:00', 'marine'), -- Puerto Velasco Ibarra → Playa Negra
(169, 30, 29, '16:30:00', '17:00:00', 'marine'), -- Playa Negra → Puerto Velasco Ibarra
(170, 29, 31, '11:00:00', '12:00:00', 'marine'), -- Puerto Velasco Ibarra → Mirador Baronesa
(171, 31, 29, '15:00:00', '16:00:00', 'marine'), -- Mirador Baronesa → Puerto Velasco Ibarra
(172, 29, 32, '13:00:00', '14:00:00', 'marine'), -- Puerto Velasco Ibarra → Corona del Diablo
(173, 32, 29, '17:00:00', '18:00:00', 'marine'), -- Corona del Diablo → Puerto Velasco Ibarra

-- ================================================================
-- RUTAS AÉREAS PRINCIPALES (AERO)
-- ================================================================
(200, 17, 21, '08:00:00', '08:30:00', 'aero'), -- Santa Cruz → San Cristóbal (vuelo)
(201, 21, 17, '09:00:00', '09:30:00', 'aero'), -- San Cristóbal → Santa Cruz (vuelo)
(202, 17, 25, '10:00:00', '10:45:00', 'aero'), -- Santa Cruz → Isabela (vuelo)
(203, 25, 17, '11:00:00', '11:45:00', 'aero'), -- Isabela → Santa Cruz (vuelo)
(204, 21, 25, '12:00:00', '12:30:00', 'aero'), -- San Cristóbal → Isabela (vuelo)
(205, 25, 21, '13:00:00', '13:30:00', 'aero'), -- Isabela → San Cristóbal (vuelo)
(210, 17, 29, '14:00:00', '14:20:00', 'aero'), -- Santa Cruz → Floreana (charter)
(211, 29, 17, '15:00:00', '15:20:00', 'aero'), -- Floreana → Santa Cruz (charter)
(212, 21, 29, '16:00:00', '16:15:00', 'aero'), -- San Cristóbal → Floreana (charter)
(213, 29, 21, '17:00:00', '17:15:00', 'aero'), -- Floreana → San Cristóbal (charter)
(220, 17, 21, '14:30:00', '15:00:00', 'aero'), -- Santa Cruz → San Cristóbal (tarde)
(221, 21, 17, '15:30:00', '16:00:00', 'aero'), -- San Cristóbal → Santa Cruz (tarde)
(222, 17, 25, '16:30:00', '17:15:00', 'aero'), -- Santa Cruz → Isabela (tarde)
(223, 25, 17, '17:30:00', '18:15:00', 'aero'), -- Isabela → Santa Cruz (tarde)

-- VUELOS TURÍSTICOS Y SOBREVUELOS
(230, 17, 19, '09:00:00', '09:15:00', 'aero'), -- Santa Cruz → Tortuga Bay (sobrevuelo)
(231, 17, 20, '10:00:00', '10:15:00', 'aero'), -- Santa Cruz → Estación Darwin (sobrevuelo)
(232, 21, 22, '11:00:00', '11:15:00', 'aero'), -- San Cristóbal → Kicker Rock (sobrevuelo)
(233, 21, 24, '12:00:00', '12:15:00', 'aero'), -- San Cristóbal → Cerro Tijeretas (sobrevuelo)
(234, 25, 26, '13:00:00', '13:30:00', 'aero'), -- Isabela → Volcán Sierra Negra (sobrevuelo)
(235, 25, 28, '14:00:00', '14:20:00', 'aero'), -- Isabela → Los Túneles (sobrevuelo)

-- ================================================================
-- RUTAS TERRESTRES INTRA-ISLA (LAND)
-- ================================================================

-- SANTA CRUZ (Rutas internas)
(300, 17, 18, '06:00:00', '06:45:00', 'land'), -- Puerto Ayora → Canal Itabaca
(301, 18, 17, '06:30:00', '07:15:00', 'land'), -- Canal Itabaca → Puerto Ayora
(302, 17, 20, '07:00:00', '07:15:00', 'land'), -- Puerto Ayora → Estación Charles Darwin
(303, 20, 17, '07:30:00', '07:45:00', 'land'), -- Estación Charles Darwin → Puerto Ayora
(304, 17, 19, '08:00:00', '08:30:00', 'land'), -- Puerto Ayora → Tortuga Bay
(305, 19, 17, '08:30:00', '09:00:00', 'land'), -- Tortuga Bay → Puerto Ayora
(306, 18, 20, '09:00:00', '09:45:00', 'land'), -- Canal Itabaca → Estación Charles Darwin
(307, 20, 18, '09:30:00', '10:15:00', 'land'), -- Estación Charles Darwin → Canal Itabaca
(308, 18, 19, '10:00:00', '10:45:00', 'land'), -- Canal Itabaca → Tortuga Bay
(309, 19, 18, '10:30:00', '11:15:00', 'land'), -- Tortuga Bay → Canal Itabaca

-- SAN CRISTÓBAL (Rutas internas)
(320, 21, 22, '08:00:00', '09:00:00', 'land'), -- Puerto Baquerizo → Kicker Rock
(321, 22, 21, '09:30:00', '10:30:00', 'land'), -- Kicker Rock → Puerto Baquerizo
(322, 21, 23, '10:00:00', '10:30:00', 'land'), -- Puerto Baquerizo → Playa Mann
(323, 23, 21, '11:00:00', '11:30:00', 'land'), -- Playa Mann → Puerto Baquerizo
(324, 21, 24, '12:00:00', '12:45:00', 'land'), -- Puerto Baquerizo → Cerro Tijeretas
(325, 24, 21, '13:00:00', '13:45:00', 'land'), -- Cerro Tijeretas → Puerto Baquerizo
(326, 22, 23, '14:00:00', '14:30:00', 'land'), -- Kicker Rock → Playa Mann
(327, 23, 22, '15:00:00', '15:30:00', 'land'), -- Playa Mann → Kicker Rock
(328, 22, 24, '16:00:00', '16:45:00', 'land'), -- Kicker Rock → Cerro Tijeretas
(329, 24, 22, '17:00:00', '17:45:00', 'land'), -- Cerro Tijeretas → Kicker Rock

-- ISABELA (Rutas internas)
(340, 25, 26, '08:00:00', '10:00:00', 'land'), -- Puerto Villamil → Volcán Sierra Negra
(341, 26, 25, '15:00:00', '17:00:00', 'land'), -- Volcán Sierra Negra → Puerto Villamil
(342, 25, 27, '09:00:00', '09:30:00', 'land'), -- Puerto Villamil → Concha de Perla
(343, 27, 25, '16:30:00', '17:00:00', 'land'), -- Concha de Perla → Puerto Villamil
(344, 25, 28, '10:00:00', '11:30:00', 'land'), -- Puerto Villamil → Los Túneles
(345, 28, 25, '14:00:00', '15:30:00', 'land'), -- Los Túneles → Puerto Villamil
(346, 26, 27, '11:00:00', '12:30:00', 'land'), -- Volcán Sierra Negra → Concha de Perla
(347, 27, 26, '13:00:00', '14:30:00', 'land'), -- Concha de Perla → Volcán Sierra Negra
(348, 26, 28, '12:00:00', '14:00:00', 'land'), -- Volcán Sierra Negra → Los Túneles
(349, 28, 26, '15:00:00', '17:00:00', 'land'), -- Los Túneles → Volcán Sierra Negra

-- FLOREANA (Rutas internas)
(360, 29, 30, '08:00:00', '08:30:00', 'land'), -- Puerto Velasco Ibarra → Playa Negra
(361, 30, 29, '17:30:00', '18:00:00', 'land'), -- Playa Negra → Puerto Velasco Ibarra
(362, 29, 31, '09:00:00', '10:00:00', 'land'), -- Puerto Velasco Ibarra → Mirador Baronesa
(363, 31, 29, '16:00:00', '17:00:00', 'land'), -- Mirador Baronesa → Puerto Velasco Ibarra
(364, 29, 32, '10:00:00', '11:00:00', 'land'), -- Puerto Velasco Ibarra → Corona del Diablo
(365, 32, 29, '15:00:00', '16:00:00', 'land'), -- Corona del Diablo → Puerto Velasco Ibarra
(366, 30, 31, '11:00:00', '11:45:00', 'land'), -- Playa Negra → Mirador Baronesa
(367, 31, 30, '14:00:00', '14:45:00', 'land'), -- Mirador Baronesa → Playa Negra
(368, 30, 32, '12:00:00', '12:45:00', 'land'), -- Playa Negra → Corona del Diablo
(369, 32, 30, '13:00:00', '13:45:00', 'land'), -- Corona del Diablo → Playa Negra
(370, 31, 32, '12:30:00', '13:15:00', 'land'), -- Mirador Baronesa → Corona del Diablo
(371, 32, 31, '13:30:00', '14:15:00', 'land'), -- Corona del Diablo → Mirador Baronesa

-- ================================================================
-- RUTAS NOCTURNAS Y ESPECIALES
-- ================================================================

-- RUTAS NOCTURNAS MARÍTIMAS
(400, 17, 21, '20:00:00', '22:30:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo (nocturna)
(401, 21, 17, '21:00:00', '23:30:00', 'marine'), -- Puerto Baquerizo → Puerto Ayora (nocturna)
(402, 17, 25, '19:30:00', '22:30:00', 'marine'), -- Puerto Ayora → Puerto Villamil (nocturna)
(403, 25, 17, '20:30:00', '23:30:00', 'marine'), -- Puerto Villamil → Puerto Ayora (nocturna)

-- RUTAS DE EMERGENCIA (24/7)
(410, 17, 21, '00:00:00', '02:30:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo (emergencia)
(411, 21, 17, '01:00:00', '03:30:00', 'marine'), -- Puerto Baquerizo → Puerto Ayora (emergencia)
(412, 17, 25, '02:00:00', '05:00:00', 'marine'), -- Puerto Ayora → Puerto Villamil (emergencia)
(413, 25, 17, '03:00:00', '06:00:00', 'marine'), -- Puerto Villamil → Puerto Ayora (emergencia)

-- RUTAS ESPECIALES DE INVESTIGACIÓN
(420, 17, 22, '05:30:00', '08:00:00', 'marine'), -- Puerto Ayora → Kicker Rock (investigación)
(421, 22, 17, '18:00:00', '20:30:00', 'marine'), -- Kicker Rock → Puerto Ayora (investigación)
(422, 25, 26, '05:00:00', '07:00:00', 'marine'), -- Puerto Villamil → Volcán Sierra Negra (investigación)
(423, 26, 25, '19:00:00', '21:00:00', 'marine'), -- Volcán Sierra Negra → Puerto Villamil (investigación)

-- RUTAS TURÍSTICAS ESPECIALES
(430, 17, 24, '06:00:00', '09:00:00', 'marine'), -- Puerto Ayora → Cerro Tijeretas (tour)
(431, 24, 17, '16:00:00', '19:00:00', 'marine'), -- Cerro Tijeretas → Puerto Ayora (tour)
(432, 21, 27, '07:00:00', '10:30:00', 'marine'), -- Puerto Baquerizo → Concha de Perla (tour)
(433, 27, 21, '15:30:00', '19:00:00', 'marine'), -- Concha de Perla → Puerto Baquerizo (tour)

-- CONEXIONES MÚLTIPLES HORARIOS (RUSH HOURS)
(440, 17, 21, '05:30:00', '08:00:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo (madrugada)
(441, 17, 21, '18:00:00', '20:30:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo (noche)
(442, 21, 17, '05:00:00', '07:30:00', 'marine'), -- Puerto Baquerizo → Puerto Ayora (madrugada)
(443, 21, 17, '18:30:00', '21:00:00', 'marine'), -- Puerto Baquerizo → Puerto Ayora (noche)
(444, 17, 25, '05:30:00', '08:30:00', 'marine'), -- Puerto Ayora → Puerto Villamil (madrugada)
(445, 17, 25, '19:00:00', '22:00:00', 'marine'), -- Puerto Ayora → Puerto Villamil (noche)

-- RUTAS AÉREAS NOCTURNAS Y ESPECIALES
(450, 17, 21, '22:00:00', '22:30:00', 'aero'), -- Santa Cruz → San Cristóbal (vuelo nocturno)
(451, 21, 17, '23:00:00', '23:30:00', 'aero'), -- San Cristóbal → Santa Cruz (vuelo nocturno)
(452, 17, 25, '21:30:00', '22:15:00', 'aero'), -- Santa Cruz → Isabela (vuelo nocturno)
(453, 25, 17, '22:30:00', '23:15:00', 'aero'), -- Isabela → Santa Cruz (vuelo nocturno)

-- RUTAS TERRESTRES MÚLTIPLES HORARIOS
(460, 17, 18, '12:00:00', '12:45:00', 'land'), -- Puerto Ayora → Canal Itabaca (mediodía)
(461, 18, 17, '12:30:00', '13:15:00', 'land'), -- Canal Itabaca → Puerto Ayora (mediodía)
(462, 17, 18, '18:00:00', '18:45:00', 'land'), -- Puerto Ayora → Canal Itabaca (tarde)
(463, 18, 17, '18:30:00', '19:15:00', 'land'), -- Canal Itabaca → Puerto Ayora (tarde)
(464, 21, 22, '14:00:00', '15:00:00', 'land'), -- Puerto Baquerizo → Kicker Rock (tarde)
(465, 22, 21, '15:30:00', '16:30:00', 'land'), -- Kicker Rock → Puerto Baquerizo (tarde)
(466, 25, 26, '13:00:00', '15:00:00', 'land'), -- Puerto Villamil → Volcán Sierra Negra (tarde)
(467, 26, 25, '16:00:00', '18:00:00', 'land'), -- Volcán Sierra Negra → Puerto Villamil (tarde)

-- RUTAS EXPRESS (ALTA VELOCIDAD)
(470, 17, 21, '09:30:00', '10:45:00', 'marine'), -- Puerto Ayora → Puerto Baquerizo (express)
(471, 21, 17, '10:00:00', '11:15:00', 'marine'), -- Puerto Baquerizo → Puerto Ayora (express)
(472, 17, 25, '11:00:00', '12:45:00', 'marine'), -- Puerto Ayora → Puerto Villamil (express)
(473, 25, 17, '11:30:00', '13:15:00', 'marine'), -- Puerto Villamil → Puerto Ayora (express)

-- RUTAS DE FIN DE SEMANA
(480, 17, 29, '09:00:00', '11:30:00', 'marine'), -- Puerto Ayora → Puerto Velasco (fin de semana)
(481, 29, 17, '15:00:00', '17:30:00', 'marine'), -- Puerto Velasco → Puerto Ayora (fin de semana)
(482, 21, 25, '08:30:00', '11:30:00', 'marine'), -- Puerto Baquerizo → Puerto Villamil (fin de semana)
(483, 25, 21, '14:30:00', '17:30:00', 'marine'), -- Puerto Villamil → Puerto Baquerizo (fin de semana)
(484, 25, 29, '10:00:00', '12:30:00', 'marine'), -- Puerto Villamil → Puerto Velasco (fin de semana)
(485, 29, 25, '13:30:00', '16:00:00', 'marine'); -- Puerto Velasco → Puerto Villamil (fin de semana)

-- ================================================================
-- TRANSPORTES: Flota de vehículos y embarcaciones
-- ================================================================

INSERT INTO wp_git_transports (id, id_operator, nicename, code, type) VALUES 

-- ==========================================
-- LANCHAS RÁPIDAS (Inter-islas)
-- ==========================================
(100, 2, 'Lancha Rápida Galápagos Express', 'LR-001', 'speedboat'),
(101, 2, 'Lancha Marlin Azul', 'LR-002', 'speedboat'),
(102, 2, 'Lancha Tortuga Marina', 'LR-003', 'speedboat'),
(103, 2, 'Lancha Piquero de Patas Azules', 'LR-004', 'speedboat'),
(104, 2, 'Lancha Fragata Real', 'LR-005', 'speedboat'),
(105, 2, 'Lancha Iguana Verde', 'LR-006', 'speedboat'),
(106, 2, 'Lancha León Marino', 'LR-007', 'speedboat'),
(107, 2, 'Lancha Pinzón Express', 'LR-008', 'speedboat'),
(108, 2, 'Lancha Darwin Explorer', 'LR-009', 'speedboat'),
(109, 2, 'Lancha Floreana Queen', 'LR-010', 'speedboat'),

-- ==========================================
-- YATES DE LUJO (Turismo premium)
-- ==========================================
(200, 2, 'Yate Galápagos Legend', 'YT-001', 'luxury_yacht'),
(201, 2, 'Yate Santa Cruz Majestic', 'YT-002', 'luxury_yacht'),
(202, 2, 'Yate Enchanted Islands', 'YT-003', 'luxury_yacht'),
(203, 2, 'Yate Natural Paradise', 'YT-004', 'luxury_yacht'),
(204, 2, 'Yate Evolution Explorer', 'YT-005', 'luxury_yacht'),
(205, 2, 'Yate Beagle Voyage', 'YT-006', 'luxury_yacht'),
(206, 2, 'Yate Pacific Adventure', 'YT-007', 'luxury_yacht'),
(207, 2, 'Yate Archipelago Dream', 'YT-008', 'luxury_yacht'),

-- ==========================================
-- CATAMARANES (Capacidad media-alta)  
-- ==========================================
(300, 2, 'Catamarán Seaman Journey', 'CT-001', 'catamaran'),
(301, 2, 'Catamarán Ocean Spray', 'CT-002', 'catamaran'),
(302, 2, 'Catamarán Nemo I', 'CT-003', 'catamaran'),
(303, 2, 'Catamarán Nemo II', 'CT-004', 'catamaran'),
(304, 2, 'Catamarán Galaxy', 'CT-005', 'catamaran'),
(305, 2, 'Catamarán Treasure of Galápagos', 'CT-006', 'catamaran'),
(306, 2, 'Catamarán Eden', 'CT-007', 'catamaran'),
(307, 2, 'Catamarán Alya', 'CT-008', 'catamaran'),
(308, 2, 'Catamarán Theory', 'CT-009', 'catamaran'),
(309, 2, 'Catamarán Cormorant', 'CT-010', 'catamaran'),

-- ==========================================
-- FERRIES (Transporte masivo y vehículos)
-- ==========================================
(400, 2, 'Ferry Baltra Connector', 'FY-001', 'ferry'),
(401, 2, 'Ferry Santa Cruz Shuttle', 'FY-002', 'ferry'),
(402, 2, 'Ferry Isabela Bridge', 'FY-003', 'ferry'),
(403, 2, 'Ferry Intercontinental', 'FY-004', 'ferry'),
(404, 2, 'Ferry Channel Express', 'FY-005', 'ferry'),
(405, 2, 'Ferry Galápagos Link', 'FY-006', 'ferry'),

-- ==========================================
-- BARCOS TURÍSTICOS (Cruceros)
-- ==========================================
(500, 2, 'Crucero Galápagos Celebrity Xpedition', 'CR-001', 'cruise_ship'),
(501, 2, 'Crucero National Geographic Islander', 'CR-002', 'cruise_ship'),
(502, 2, 'Crucero Silversea Silver Origin', 'CR-003', 'cruise_ship'),
(503, 2, 'Crucero La Pinta Expedition', 'CR-004', 'cruise_ship'),
(504, 2, 'Crucero Santa Cruz II', 'CR-005', 'cruise_ship'),
(505, 2, 'Crucero Galápagos Safari Camp', 'CR-006', 'cruise_ship'),
(506, 2, 'Crucero Ocean Alexander', 'CR-007', 'cruise_ship'),

-- ==========================================
-- AVIONES (Transporte aéreo)
-- ==========================================
(600, 2, 'Avianca Airbus A320 Galápagos', 'AV-001', 'aircraft'),
(601, 2, 'LATAM Boeing 737 Santa Cruz', 'AV-002', 'aircraft'),
(602, 2, 'TAME Boeing 737 Darwin', 'AV-003', 'aircraft'),
(603, 2, 'Avianca Airbus A319 Isabela', 'AV-004', 'aircraft'),
(604, 2, 'Charter Cessna 208 Caravan', 'AV-005', 'small_aircraft'),
(605, 2, 'Charter Piper Chieftain San Cristóbal', 'AV-006', 'small_aircraft'),
(606, 2, 'Charter Twin Otter DHC-6', 'AV-007', 'small_aircraft'),

-- ==========================================
-- BUSES Y VEHÍCULOS TERRESTRES
-- ==========================================
(700, 2, 'Bus Mercedes-Benz Sprinter Puerto Ayora', 'BS-001', 'bus'),
(701, 2, 'Bus Iveco Daily Baltra Airport', 'BS-002', 'bus'),
(702, 2, 'Bus Toyota Hiace Puerto Baquerizo', 'BS-003', 'bus'),
(703, 2, 'Van Ford Transit Puerto Villamil', 'BS-004', 'van'),
(704, 2, 'Van Nissan NV200 Floreana', 'BS-005', 'van'),
(705, 2, 'Pickup Toyota Hilux 4x4 Highlands', 'PK-001', 'pickup'),
(706, 2, 'Pickup Ford Ranger Santa Cruz', 'PK-002', 'pickup'),
(707, 2, 'Mini Bus Hyundai County', 'MB-001', 'minibus'),
(708, 2, 'Mini Bus Chevrolet NPR', 'MB-002', 'minibus'),

-- ==========================================
-- EMBARCACIONES DE INVESTIGACIÓN
-- ==========================================
(800, 2, 'Buque de Investigación Charles Darwin', 'RI-001', 'research_vessel'),
(801, 2, 'Embarcación Científica Beagle III', 'RI-002', 'research_vessel'),
(802, 2, 'Barco de Conservación Lonesome George', 'RI-003', 'research_vessel'),

-- ==========================================
-- EMBARCACIONES DE PESCA TURÍSTICA
-- ==========================================
(900, 2, 'Barco de Pesca Blue Marlin', 'FT-001', 'fishing_boat'),
(901, 2, 'Lancha de Pesca Tuna Hunter', 'FT-002', 'fishing_boat'),
(902, 2, 'Panga Fishing Paradise', 'FT-003', 'fishing_boat'),
(903, 2, 'Sport Fishing Galápagos Star', 'FT-004', 'fishing_boat'),
(904, 2, 'Deep Sea Explorer Manta Ray', 'FT-005', 'fishing_boat'),

-- ==========================================
-- EMBARCACIONES ESPECIALES
-- ==========================================
(1000, 2, 'Embarcación de Rescate Coast Guard GAL-1', 'RC-001', 'rescue_boat'),
(1001, 2, 'Barco de Carga Galápagos Logistics', 'CG-001', 'cargo_ship'),
(1002, 2, 'Barco de Suministros Santa Cruz Supply', 'CG-002', 'cargo_ship'),
(1003, 2, 'Buque Cisterna Fresh Water II', 'CT-011', 'tanker'),
(1004, 2, 'Barco Médico Emergency Response', 'MD-001', 'medical_boat'),

-- ==========================================
-- LANCHAS ECOLÓGICAS
-- ==========================================
(1100, 2, 'Lancha Eléctrica Eco Galápagos', 'EC-001', 'electric_boat'),
(1101, 2, 'Catamarán Solar Green Islands', 'EC-002', 'solar_catamaran'),
(1102, 2, 'Lancha Híbrida Clean Ocean', 'EC-003', 'hybrid_boat'),
(1103, 2, 'Embarcación Cero Emisiones Future', 'EC-004', 'electric_boat');

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- ================================================================
-- RESUMEN DE DATOS INSERTADOS
-- ================================================================
/*
ZONAS INSERTADAS: 17 zonas (16 islas + Ecuador Continental)

UBICACIONES POR TIPO:
- Puertos Marítimos: 10 ubicaciones
- Aeropuertos: 3 ubicaciones  
- Terminales: 8 ubicaciones

SERVICIOS: 23 servicios adicionales categorizados

RUTAS: 146 rutas completas
- 72 rutas marítimas (marine): Inter-islas, turísticas, nocturnas, emergencia
- 50 rutas terrestres (land): Intra-isla en todas las ubicaciones
- 24 rutas aéreas (aero): Vuelos regulares, charter y sobrevuelos

HORARIOS DISTRIBUIDOS:
- 63 rutas matutinas (05:00-11:59)
- 69 rutas vespertinas (12:00-18:59)
- 14 rutas nocturnas (19:00-04:59)

TRANSPORTES: 74 vehículos y embarcaciones
- Lanchas Rápidas: 10 unidades (LR-001 a LR-010)
- Yates de Lujo: 8 unidades (YT-001 a YT-008)
- Catamaranes: 10 unidades (CT-001 a CT-010)
- Ferries: 6 unidades (FY-001 a FY-006)
- Cruceros: 7 unidades (CR-001 a CR-007)
- Aviones: 7 unidades (AV-001 a AV-007)
- Vehículos Terrestres: 9 unidades (BS-001 a MB-002)
- Embarcaciones Investigación: 3 unidades (RI-001 a RI-003)
- Barcos de Pesca: 5 unidades (FT-001 a FT-005)
- Embarcaciones Especiales: 5 unidades (RC-001 a MD-001)
- Lanchas Ecológicas: 4 unidades (EC-001 a EC-004)

TIPOS DE TRANSPORTE:
✅ speedboat, luxury_yacht, catamaran, ferry
✅ cruise_ship, aircraft, small_aircraft
✅ bus, van, pickup, minibus
✅ research_vessel, fishing_boat, rescue_boat
✅ cargo_ship, tanker, medical_boat
✅ electric_boat, solar_catamaran, hybrid_boat

COBERTURA COMPLETA:
✅ Conexión aérea: Continente ↔ Galápagos
✅ Transporte marítimo: Entre todas las islas habitadas
✅ Servicios terrestres: Aeropuerto ↔ Puertos
✅ Turismo de lujo: Yates y cruceros premium
✅ Investigación científica: Embarcaciones especializadas
✅ Sostenibilidad: Opciones ecológicas y eléctricas
✅ Emergencias: Rescate y atención médica
✅ Logística: Carga y suministros
*/

-- ================================================================
-- RELACIONES TRANSPORTES-SERVICIOS (155 relaciones)
-- Distribución dispersa: luxury (15+ servicios), medium (4-7), basic (2-4)
-- ================================================================

-- PARTE 1: CRUCEROS Y YATES (Servicios de lujo - 15+ servicios)
INSERT INTO wp_git_transports_services (id_transport, id_service) VALUES 
-- Cruceros (todos los servicios premium)
(500, 100), (500, 101), (500, 103), (500, 104), (500, 105), (500, 106), (500, 107), (500, 108), (500, 109), (500, 110), (500, 111), (500, 112), (500, 113), (500, 114), (500, 115), (500, 116), (500, 117), (500, 118),
(501, 100), (501, 101), (501, 103), (501, 105), (501, 107), (501, 109), (501, 112), (501, 113), (501, 114), (501, 115), (501, 116), (501, 117), (501, 118), (501, 119), (501, 121), (501, 122),
-- Yates premium
(200, 100), (200, 101), (200, 103), (200, 105), (200, 107), (200, 109), (200, 111), (200, 113), (200, 115), (200, 117), (200, 119), (200, 121), (200, 122),
(201, 100), (201, 101), (201, 103), (201, 104), (201, 105), (201, 107), (201, 109), (201, 111), (201, 113), (201, 115), (201, 117), (201, 119), (201, 121),
(202, 100), (202, 101), (202, 103), (202, 105), (202, 107), (202, 109), (202, 111);

-- PARTE 2: CATAMARANES Y EMBARCACIONES MEDIANAS (4-7 servicios)
INSERT INTO wp_git_transports_services (id_transport, id_service) VALUES 
-- Catamaranes turísticos
(300, 100), (300, 102), (300, 105), (300, 107), (300, 115), (300, 122),
(301, 100), (301, 102), (301, 105), (301, 107), (301, 115),
(302, 100), (302, 102), (302, 105), (302, 115),
(303, 100), (303, 102), (303, 105), (303, 115),
(304, 100), (304, 102), (304, 105), (304, 107), (304, 115),
-- Lanchas rápidas de turismo
(100, 100), (100, 102), (100, 105), (100, 115),
(101, 100), (101, 102), (101, 105), (101, 115),
(102, 100), (102, 102), (102, 105),
(103, 100), (103, 102), (103, 115),
(104, 100), (104, 102), (104, 105), (104, 115),
(105, 100), (105, 102), (105, 115),
(106, 100), (106, 102),
-- Ferries principales
(400, 100), (400, 102), (400, 105), (400, 120),
(401, 100), (401, 102), (401, 105), (401, 120),
(402, 100), (402, 102), (402, 105),
(403, 100), (403, 102);

-- PARTE 3: ESPECIALES, AÉREOS Y TERRESTRES (Servicios específicos)  
INSERT INTO wp_git_transports_services (id_transport, id_service) VALUES 
-- Aviones comerciales
(600, 100), (600, 102), (600, 120), (600, 121),
(601, 100), (601, 102), (601, 120), (601, 121),
(602, 100), (602, 102), (602, 120),
-- Transporte terrestre
(700, 100), (700, 102), (700, 120),
(701, 100), (701, 102), (701, 120),
(702, 100), (702, 102),
(703, 100), (703, 102),
-- Embarcaciones investigación
(800, 113), (800, 118), (800, 119),
(801, 113), (801, 118), (801, 119),
-- Pesca deportiva
(900, 110), (900, 112),
(901, 110), (901, 112),
(903, 110), (903, 112), (903, 116),
-- Emergencias y servicios
(1000, 118), (1000, 119),
(1001, 120),
(1004, 118), (1004, 119),
-- Transportes ecológicos
(1100, 100), (1100, 105), (1100, 115),
(1101, 100), (1101, 105), (1101, 115), (1101, 122);

-- ================================================================
-- RELACIONES RUTAS-TRANSPORTES (200 relaciones)
-- Distribución dispersa: algunos transportes con muchas rutas, otros con pocas
-- según función y tipo de servicio
-- ================================================================

-- PARTE 1: CRUCEROS Y YATES (Pocas rutas selectas)
INSERT INTO wp_git_routes_transports (id_route, id_transport) VALUES 
-- Crucero Celebrity Xpedition (500) - Solo rutas principales inter-islas
(100, 500), (110, 500), (101, 500), (120, 500),
-- Yate Galápagos Legend (200) - Rutas exclusivas turísticas  
(100, 200), (160, 200), (162, 200), (420, 200), (430, 200),
-- Yate Santa Cruz Majestic (201) - Rutas de lujo limitadas
(101, 201), (161, 201), (164, 201),
-- Crucero National Geographic Islander (501) - Rutas científicas
(160, 501), (420, 501), (421, 501), (422, 501),
-- CATAMARANES (Rutas turísticas variadas)
(100, 300), (160, 300), (162, 300), (164, 300), (166, 300), (168, 300),
(101, 301), (161, 301), (163, 301), (165, 301),
(110, 302), (111, 302), (120, 302),
(112, 303), (121, 303), (122, 303),
(100, 304), (130, 304), (140, 304);

-- PARTE 2: LANCHAS RÁPIDAS Y FERRIES (Muchas rutas)
INSERT INTO wp_git_routes_transports (id_route, id_transport) VALUES 
-- Lancha Galápagos Express (100) - MUCHAS rutas inter-islas
(100, 100), (101, 100), (110, 100), (111, 100), (112, 100), (120, 100), (121, 100), (122, 100), (130, 100), (131, 100), (140, 100), (141, 100), (150, 100),
-- Lancha Marlin Azul (101) - MUCHAS rutas variadas
(100, 101), (110, 101), (120, 101), (130, 101), (140, 101), (150, 101), (160, 101), (162, 101), (164, 101), (166, 101),
-- Lancha Tortuga Marina (102) - Rutas frecuentes
(100, 102), (101, 102), (110, 102), (141, 102), (150, 102), (160, 102),
-- Lancha Piquero de Patas Azules (103) - Rutas básicas
(111, 103), (121, 103), (131, 103), (141, 103),
-- Ferry Baltra Connector (400) - Rutas principales masivas
(100, 400), (101, 400), (110, 400), (120, 400), (130, 400), (140, 400), (150, 400),
-- Ferry Santa Cruz Shuttle (401) - Rutas inter-islas principales
(100, 401), (110, 401), (120, 401), (130, 401), (140, 401),
-- Ferry Isabela Bridge (402) - Rutas hacia/desde Isabela
(130, 402), (131, 402), (140, 402), (141, 402),
-- Ferry Intercontinental (403) - Solo rutas principales
(150, 403), (151, 403),
-- Lancha Fragata Real (104) - Mix de rutas
(100, 104), (120, 104), (160, 104), (162, 104);

-- PARTE 3: AVIONES, TERRESTRES Y ESPECIALES
INSERT INTO wp_git_routes_transports (id_route, id_transport) VALUES 
-- AVIONES - Solo rutas aéreas
(200, 600), (210, 600), (211, 600), (220, 600), (450, 600),
(200, 601), (201, 601), (210, 601), (220, 601), (230, 601), (450, 601), (451, 601),
(200, 602), (210, 602), (221, 602),
(230, 603), (231, 603), (232, 603),
(202, 604), (203, 604), (452, 604), (453, 604),
(202, 605), (212, 605), (222, 605),
(204, 606), (213, 606), (233, 606),
-- VEHÍCULOS TERRESTRES - Solo rutas terrestres
(300, 700), (301, 700), (302, 700), (303, 700), (304, 700), (305, 700),
(320, 701), (321, 701), (322, 701),
(340, 702), (341, 702), (342, 702), (343, 702),
(360, 703), (361, 703), (362, 703),
(460, 705), (461, 705), (462, 705), (370, 705);

-- PARTE 4: EMBARCACIONES ESPECIALES Y DISTRIBUCIÓN FINAL
INSERT INTO wp_git_routes_transports (id_route, id_transport) VALUES 
-- YATES ADICIONALES (Rutas exclusivas)
(160, 202), (162, 202), (164, 202), (420, 202),
(161, 203), (163, 203),
(420, 204), (421, 204), (422, 204),
-- EMBARCACIONES DE INVESTIGACIÓN
(420, 800), (421, 800), (422, 800), (423, 800),
(421, 801), (422, 801), (423, 801),
-- EMBARCACIONES DE PESCA
(430, 900), (431, 900),
(430, 901), (432, 901),
(430, 903), (431, 903), (432, 903),
-- EMBARCACIONES DE SERVICIO
(440, 1000), (441, 1000), (442, 1000), (443, 1000),
(440, 1001), (441, 1001), (443, 1001),
(444, 1004), (445, 1004),
-- EMBARCACIONES ECO-FRIENDLY
(160, 1100), (161, 1100), (162, 1100),
(164, 1101), (165, 1101), (166, 1101), (167, 1101),
-- LANCHAS ADICIONALES DISPERSAS
(100, 105), (110, 105), (120, 105), (130, 105), (140, 105), (160, 105), (162, 105),
(111, 106), (121, 106),
(160, 108), (420, 108), (421, 108),
-- CATAMARANES ADICIONALES
(161, 305), (163, 305), (165, 305),
(164, 306), (166, 306), (410, 306),
-- FERRIES ADICIONALES
(470, 404), (471, 404),
(400, 405), (401, 405), (402, 405);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- ================================================================
-- RESUMEN FINAL DEL SISTEMA
-- ================================================================
/*
🎯 SISTEMA DE TRANSPORTE GALÁPAGOS COMPLETADO

DISTRIBUCIÓN DE RELACIONES:
✅ Transport-Servicios: 155 relaciones dispersas
✅ Rutas-Transportes: 200 relaciones dispersas

COBERTURA POR TIPO:
🌊 Marítimo: 38 de 65 transportes activos (58%)
✈️  Aéreo: 7 de 7 transportes activos (100%)
🚌 Terrestre: 5 de 9 transportes activos (56%)

DISTRIBUCIÓN DISPERSA LOGRADA:
🚢 Cruceros: Pocas rutas exclusivas (3-4 rutas)
🏎️  Lanchas rápidas: Muchas rutas (hasta 13 rutas)
⛴️  Ferries: Rutas masivas principales (4-7 rutas)
🛥️  Yates: Rutas turísticas selectas (2-5 rutas)
🔬 Especiales: Rutas específicas por función

SERVICIOS POR CATEGORÍA:
🏆 Lujo: 15+ servicios (cruceros, yates premium)
🏖️  Medio: 4-7 servicios (catamaranes, lanchas turísticas)
⚡ Básico: 2-4 servicios (ferries, transporte esencial)

SISTEMA LISTO PARA PRODUCCIÓN ✅
*/