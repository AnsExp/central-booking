<?php
namespace CentralTickets;

use CentralTickets\Constants\RouteConstants;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Constants\TypeWayConstants;

final class ProductForm
{
    private function __construct()
    {
    }

    public static function get_tabs()
    {
        return [
            'general_operator' => [
                'label' => 'General',
                'target' => 'git_general_product_data',
                'class' => ['show_if_operator'],
                'priority' => 25,
            ],
            'pricing' => [
                'label' => 'Rutas y Capacidad',
                'target' => 'git_routes_product_data',
                'class' => ['show_if_operator', 'advanced_options'],
                'priority' => 26,
            ],
            'inventory' => [
                'label' => 'Precios y Tarifas',
                'target' => 'git_pricing_product_data',
                'class' => ['show_if_operator', 'attribute_options'],
                'priority' => 27,
            ],
        ];
    }

    public static function get_general_panel()
    {
        $types_way = [];
        $types_transport = [];

        foreach (TransportConstants::all() as $type) {
            $types_transport[$type] = git_get_text_by_type($type);
        }

        foreach (TypeWayConstants::all_types() as $type) {
            $types_way[$type] = git_get_text_by_way($type);
        }
        ob_start();
        ?>
        <div id="git_general_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                woocommerce_wp_checkbox([
                    'id' => 'enable_bookeable',
                    'label' => 'Puede reservarse',
                    'description' => 'Permite que los usuarios puedan hacer reservas de esta operación'
                ]);

                woocommerce_wp_checkbox([
                    'id' => 'enable_switch_route',
                    'label' => 'Habilitar switch de ruta',
                    'description' => 'Permite cambiar el sentido de la ruta durante la reserva'
                ]);

                woocommerce_wp_checkbox([
                    'id' => 'split_transport_by_alias',
                    'label' => 'Separar transporte por alias',
                    'description' => 'Crea una opción de transporte por cada alias definido en el mismo'
                ]);

                woocommerce_wp_checkbox([
                    'id' => 'enable_carousel_transports',
                    'label' => 'Habilitar carrusel de transportes',
                    'description' => 'Permite mostrar los transportes en un carrusel'
                ]);

                woocommerce_wp_select([
                    'id' => 'type_transport',
                    'label' => 'Tipo de transporte',
                    'options' => $types_transport,
                    'description' => 'Selecciona el tipo de transporte (lancha, bus, etc.)'
                ]);

                woocommerce_wp_select([
                    'id' => 'type_way',
                    'label' => 'Tipo de trayecto',
                    'options' => $types_way,
                    'description' => 'Define si es ida, vuelta o ida y vuelta'
                ]);
                ?>
            </div>
        </div>
        <?php
        return git_string_to_component(ob_get_clean());
    }

    public static function get_pricing_panel()
    {
        $zones = [];

        foreach (git_get_zones() as $zone) {
            $zones[$zone->id] = $zone->name;
        }

        ob_start();
        ?>
        <div id="git_routes_product_data" class="panel woocommerce_options_panel">
            <?php
            woocommerce_wp_select([
                'id' => 'zone_origin',
                'label' => 'Zona de origen',
                'options' => $zones,
                'description' => 'Punto de partida del transporte'
            ]);

            woocommerce_wp_select([
                'id' => 'zone_destiny',
                'label' => 'Zona de destino',
                'options' => $zones,
                'description' => 'Punto de llegada del transporte'
            ]);

            woocommerce_wp_text_input([
                'id' => 'maximum_person',
                'label' => 'Máximo de personas por operación',
                'placeholder' => '0',
                'type' => 'number',
                'desc_tip' => true,
                'description' => 'Número máximo de pasajeros permitidos'
            ]);

            woocommerce_wp_text_input([
                'id' => 'maximum_extra',
                'label' => 'Máximo de equipaje extra',
                'placeholder' => '0',
                'type' => 'number',
                'desc_tip' => true,
                'description' => 'Cantidad máxima de equipaje adicional permitido'
            ]);
            ?>
        </div>
        <?php
        return git_string_to_component(ob_get_clean());
    }

    public static function get_inventory_panel()
    {
        ?>
        <div id="git_pricing_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                woocommerce_wp_text_input([
                    'id' => 'price_standar',
                    'label' => 'Precio estándar',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio regular para adultos'
                ]);

                woocommerce_wp_text_input([
                    'id' => 'price_kid',
                    'label' => 'Precio para niños',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio especial para menores de edad'
                ]);
                ?>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_text_input([
                    'id' => 'price_rpm',
                    'label' => 'Precio para usuarios RPM',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio con descuento para miembros RPM'
                ]);

                woocommerce_wp_text_input([
                    'id' => 'price_flexible',
                    'label' => 'Precio flexible',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio para reservas con cambios permitidos'
                ]);
                ?>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_text_input([
                    'id' => 'price_extra',
                    'label' => 'Precio cargo extra',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Costo adicional por equipaje extra o servicios especiales'
                ]);
                ?>
            </div>
        </div>
        <?php
        return git_string_to_component(ob_get_clean());
    }

    public static function process_form(int $post_id)
    {
        $fields_standars = [
            'type_way',
            'price_kid',
            'price_rpm',
            'price_extra',
            'zone_origin',
            'zone_destiny',
            'price_standar',
            'maximum_extra',
            'maximum_person',
            'price_flexible',
            'type_transport',
        ];

        $fields_checkboxes = [
            'enable_bookeable',
            'enable_switch_route',
            'split_transport_by_alias',
            'enable_carousel_transports',
        ];

        foreach ($fields_standars as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        foreach ($fields_checkboxes as $field) {
            update_post_meta($post_id, $field, isset($_POST[$field]) ? 'yes' : 'no');
        }

        update_post_meta($post_id, '_sale_price', 0);
        update_post_meta($post_id, '_regular_price', 0);
    }
}