<?php
namespace CentralTickets;

use CentralTickets\Admin\ActivitiesLogView;
use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\LocationsView;
use CentralTickets\Admin\MarketingView;
use CentralTickets\Admin\OperatorsView;
use CentralTickets\Admin\PassengersView;
use CentralTickets\Admin\RoutesView;
use CentralTickets\Admin\ServicesView;
use CentralTickets\Admin\Setting\SettingsDashboard;
use CentralTickets\Admin\TicketsView;
use CentralTickets\Admin\TransportsView;
use CentralTickets\Admin\View\TableOperators;
use CentralTickets\Admin\View\TablePassengersLog;
use CentralTickets\Admin\View\TableTicketsLog;
use CentralTickets\Client\TicketViewer;
use CentralTickets\Components\CompositeComponent;
use CentralTickets\Preorder\PreorderDashboard;
use CentralTickets\Profile\ProfileDashboard;
use CentralTickets\REST\EndpointsConnectorsOperators;
use CentralTickets\REST\EndpointsPDF;
use CentralTickets\REST\EndpointsPreorder;
use CentralTickets\REST\EndpointsTransports;
use CentralTickets\REST\RegisterRoute;

final class Bootstrap
{
    private static ?self $instance = null;
    private static bool $initialized = false;

    private function __construct()
    {
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function init()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        $instance = self::get_instance();
        $instance->init_rest();
        $instance->init_admin_menu();
        $instance->init_admin_shortcuts();
        $instance->init_woocommerce_extensions();
    }

    private function init_rest()
    {
        add_action('rest_api_init', function () {
            (new EndpointsPDF())->init_endpoints();
            (new EndpointsPreorder())->init_endpoints();
            // (new EndpointsTransports())->init_endpoints();
            (new EndpointsConnectorsOperators())->init_endpoints();
            RegisterRoute::register(
                'verify_signed_pdf',
                'POST',
                function () {
                    $verifier = new Services\Actions\SignedPDF();
                    return $verifier->verifySigned($_FILES['pdf_signed']['tmp_name'] ?? '');
                }
            );
        });
    }

    private function init_woocommerce_extensions(): void
    {
        add_filter('product_type_selector', function ($types) {
            $types['operator'] = 'Producto operable';
            return $types;
        });
        add_action('woocommerce_loaded', function () {
            if (class_exists('WC_Product')) {
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-thankyou.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-cart-ticket.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-hooks-woocommerce.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-product-form.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-passenger-form.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-cart-passenger.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/wc-class-product-operator.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-validate-coupon.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-product-item-cart.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-calculate-ticket-price.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-create-order-line-item.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/git-class-product-single-presentation.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/single-product/git-class-form-product.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/single-product/git-class-form-product-route.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/single-product/git-class-form-product-transport.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/single-product/git-class-form-product-passenger.php';
                require_once CENTRAL_BOOKING_DIR . '/includes/woocommerce/single-product/git-class-form-product-not-available.php';
            }
        });
    }

    private function init_admin_shortcuts()
    {
        add_shortcode('git_ticket_preview', function () {
            wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
            wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
            return (new TicketViewer($_GET['data'] ?? -1))->compact();
        });

        add_shortcode('git_profile', fn() =>
            git_user_logged_in() ?
            (new ProfileDashboard())->compact() :
            wp_login_form(['echo' => false]));

        add_shortcode('git_interactive_map', function ($atts) {
            $attributes = shortcode_atts([
                'width' => '100%',
                'height' => '750px',
                'url' => urlencode(CENTRAL_BOOKING_URL),
                'dir' => CENTRAL_BOOKING_DIR,
            ], $atts);
            $component = new CompositeComponent('iframe');
            $src = add_query_arg([
                'git_url' => $attributes['url'],
                'git_dir' => $attributes['dir'],
            ], CENTRAL_BOOKING_URL . 'includes/git-interactive-map.php');
            $component->set_attribute('src', $src);
            $component->styles->set('width', $attributes['width']);
            $component->styles->set('height', $attributes['height']);
            return $component->compact();
        });

        add_shortcode('git_preorder', function () {
            wp_enqueue_style('bootstrap-icon', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css');
            wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
            wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
            return (new PreorderDashboard())->compact();
        });
    }

    private function init_admin_menu()
    {
        add_role('operator', 'Operador', ['read' => true]);
        add_action('admin_menu', function () {
            wp_enqueue_style(
                'icons-bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css',
                [],
                '5.3.0',
                'all'
            );
            wp_enqueue_style(
                'git-admin-dashboard',
                CENTRAL_BOOKING_URL . '/assets/css/admin-dashboard.css',
            );
            wp_enqueue_script(
                'git-admin-dashboard',
                CENTRAL_BOOKING_URL . '/assets/js/admin-dashboard.js',
                ['jquery'],
            );
            if (current_user_can('manage_options')) {
                add_menu_page(
                    'Central Reservas',
                    'Central Reservas',
                    'manage_options',
                    'central_booking',
                    function () {
                        (new SettingsDashboard())->display();
                    },
                    'dashicons-tickets',
                    6
                );
                add_submenu_page(
                    'central_booking',
                    'Marketing',
                    'Marketing',
                    'manage_options',
                    'central_marketing',
                    function () {
                        (new MarketingView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Pasajeros',
                    'Pasajeros',
                    'manage_options',
                    'central_passengers',
                    function () {
                        (new PassengersView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Tickets',
                    'Tickets',
                    'manage_options',
                    'central_tickets',
                    function () {
                        (new TicketsView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Transportes',
                    'Transportes',
                    'manage_options',
                    'central_transports',
                    function () {
                        (new TransportsView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Rutas',
                    'Rutas',
                    'manage_options',
                    'central_routes',
                    function () {
                        (new RoutesView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Servicios',
                    'Servicios',
                    'manage_options',
                    'central_services',
                    function () {
                        (new ServicesView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Ubicaciones',
                    'Ubicaciones',
                    'manage_options',
                    'central_locations',
                    function () {
                        (new LocationsView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Operadores',
                    'Operadores',
                    'manage_options',
                    'central_operators',
                    function () {
                        (new OperatorsView())->display();
                    }
                );
                add_submenu_page(
                    'central_booking',
                    'Log de Actividades',
                    'Log de Actividades',
                    'manage_options',
                    'central_activity',
                    function () {
                        AdminRouter::add_route(
                            'passengers_log',
                            'Pasajeros',
                            TablePassengersLog::class,
                            'central_activity'
                        );
                        AdminRouter::add_route(
                            'tickets_log',
                            'Tickets',
                            TableTicketsLog::class,
                            'central_activity'
                        );
                        AdminRouter::render_content('central_activity');
                    }
                );
            }
        });
    }
}