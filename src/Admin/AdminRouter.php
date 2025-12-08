<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormCoupon;
use CentralTickets\Admin\Form\FormLocation;
use CentralTickets\Admin\Form\FormOperator;
use CentralTickets\Admin\Form\FormOperatorsExternal;
use CentralTickets\Admin\Form\FormQRCode;
use CentralTickets\Admin\Form\FormRoute;
use CentralTickets\Admin\Form\FormService;
use CentralTickets\Admin\Form\FormTransfer;
use CentralTickets\Admin\Form\FormTransport;
use CentralTickets\Admin\Form\FormWebhook;
use CentralTickets\Admin\Form\FormZone;
use CentralTickets\Admin\Setting\SettingsClients;
use CentralTickets\Admin\Setting\SettingsGeneral;
use CentralTickets\Admin\Setting\SettingsNotifications;
use CentralTickets\Admin\Setting\SettingsOperators;
use CentralTickets\Admin\Setting\SettingsPreorder;
use CentralTickets\Admin\Setting\SettingsTexts;
use CentralTickets\Admin\Setting\SettingsTickets;
use CentralTickets\Admin\Setting\SettingsWebhooks;
use CentralTickets\Admin\View\TableCoupons;
use CentralTickets\Admin\View\TableLocations;
use CentralTickets\Admin\View\TableOperators;
use CentralTickets\Admin\View\TablePassengers;
use CentralTickets\Admin\View\TablePassengersLog;
use CentralTickets\Admin\View\TableRoutes;
use CentralTickets\Admin\View\TableServices;
use CentralTickets\Admin\View\TableTickets;
use CentralTickets\Admin\View\TableTicketsLog;
use CentralTickets\Admin\View\TableTransports;
use CentralTickets\Admin\View\TableZones;

final class AdminRouter
{
    public const PAGE_CENTRAL_BOOKING = 'git-central';
    public const PAGE_MARKETING = 'git-marketing';
    public const PAGE_PASSENGERS = 'git-passengers';
    public const PAGE_TICKETS = 'git-tickets';
    public const PAGE_TRANSPORTS = 'git-transports';
    public const PAGE_ROUTES = 'git-routes';
    public const PAGE_SERVICES = 'git-services';
    public const PAGE_LOCATIONS = 'git-locations';
    public const PAGE_OPERATORS = 'git-operators';
    public const PAGE_ACTIVITIES_LOGS = 'git-activity-logs';

    private static array $route_mappings = [
        self::PAGE_CENTRAL_BOOKING => [
            'default_action' => 'general',
            'header' => 'Central Reservas',
            'tabpane' => true,
            'actions' => [
                'general' => [
                    'tab_label' => 'General',
                    'target' => SettingsGeneral::class,
                    'is_tab' => true,
                ],
                'booking' => [
                    'tab_label' => 'Reseva',
                    'target' => SettingsClients::class,
                    'is_tab' => true,
                ],
                'tickets' => [
                    'tab_label' => 'Tickets',
                    'target' => SettingsTickets::class,
                    'is_tab' => true,
                ],
                'operators' => [
                    'tab_label' => 'Operadores',
                    'target' => SettingsOperators::class,
                    'is_tab' => true,
                ],
                'labels' => [
                    'tab_label' => 'Etiquetas',
                    'target' => SettingsTexts::class,
                    'is_tab' => true,
                ],
                'preorder' => [
                    'tab_label' => 'Preorder',
                    'target' => SettingsPreorder::class,
                    'is_tab' => true,
                ],
                'webhooks' => [
                    'tab_label' => 'Webhooks',
                    'target' => SettingsWebhooks::class,
                    'is_tab' => true,
                    'redirects' => [
                        [
                            'label' => 'Crear webhook',
                            'to' => FormWebhook::class,
                        ]
                    ],
                ],
                'messenger' => [
                    'tab_label' => 'Notificaciones',
                    'target' => SettingsNotifications::class,
                    'is_tab' => true,
                ],
                'edit_webhook' => [
                    'tab_label' => 'Editar Webhook',
                    'target' => FormWebhook::class,
                    'is_tab' => false,
                ],
            ],
        ],
        self::PAGE_MARKETING => [
            'header' => 'Comercializador',
            'default_action' => 'list_flyers',
            'tabpane' => true,
            'actions' => [
                'list_flyers' => [
                    'is_tab' => true,
                    'tab_label' => 'Flyer de Comercializador',
                    'target' => TableCoupons::class,
                    'redirects' => [
                        [
                            'label' => 'Asignar Flyer',
                            'to' => FormCoupon::class,
                        ]
                    ],
                ],
                'edit_coupon' => [
                    'is_tab' => false,
                    'target' => FormCoupon::class,
                    'redirects' => [
                        [
                            'label' => 'Lista de Flyers',
                            'to' => TableCoupons::class,
                        ]
                    ],
                ],
                'qr_generator' => [
                    'is_tab' => true,
                    'tab_label' => 'Generador QR',
                    'target' => FormQRCode::class,
                ],
            ],
        ],
        self::PAGE_PASSENGERS => [
            'header' => 'Pasajeros',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TablePassengers::class,
                    'redirects' => [
                        [
                            'label' => 'Modo Traslado',
                            'to' => FormTransfer::class,
                        ]
                    ],
                ],
                'transfer' => [
                    'target' => FormTransfer::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TablePassengers::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_TICKETS => [
            'header' => 'Tickets',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableTickets::class,
                ],
            ],
        ],
        self::PAGE_TRANSPORTS => [
            'header' => 'Transportes',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableTransports::class,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormTransport::class,
                        ]
                    ],
                ],
                'edit' => [
                    'target' => FormTransport::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableTransports::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_ROUTES => [
            'header' => 'Rutas',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableRoutes::class,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormRoute::class,
                        ]
                    ],
                ],
                'edit' => [
                    'target' => FormRoute::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableRoutes::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_SERVICES => [
            'header' => 'Servicios',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableServices::class,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormService::class,
                        ]
                    ],
                ],
                'edit' => [
                    'target' => FormService::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableServices::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_LOCATIONS => [
            'header' => 'Ubicaciones',
            'tabpane' => true,
            'default_action' => 'list_locations',
            'actions' => [
                'list_locations' => [
                    'tab_label' => 'Ubicaciones',
                    'target' => TableLocations::class,
                    'is_tab' => true,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormLocation::class,
                        ]
                    ],
                ],
                'edit_location' => [
                    'target' => FormLocation::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableLocations::class,
                        ]
                    ],
                ],
                'list_zones' => [
                    'tab_label' => 'Zonas',
                    'target' => TableZones::class,
                    'is_tab' => true,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormZone::class,
                        ]
                    ],
                ],
                'edit_zone' => [
                    'target' => FormZone::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableZones::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_OPERATORS => [
            'header' => 'Operadores',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableOperators::class,
                    'redirects' => [
                        [
                            'label' => 'Conectar operador externo',
                            'to' => FormOperatorsExternal::class,
                        ]
                    ],
                ],
                'edit' => [
                    'target' => FormOperator::class,
                    'redirects' => [
                        [
                            'label' => 'Listar operadores',
                            'to' => TableOperators::class,
                        ]
                    ],
                ],
                'connector' => [
                    'target' => FormOperatorsExternal::class,
                    'redirects' => [
                        [
                            'label' => 'Listar operadores',
                            'to' => TableOperators::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_ACTIVITIES_LOGS => [
            'header' => 'Log de Actividades',
            'tabpane' => true,
            'default_action' => 'list_tickets',
            'actions' => [
                'list_tickets' => [
                    'tab_label' => 'Tickets',
                    'target' => TableTicketsLog::class,
                    'is_tab' => true,
                ],
                'list_passengers' => [
                    'tab_label' => 'Pasajeros',
                    'target' => TablePassengersLog::class,
                    'is_tab' => true,
                ],
            ],
        ],
    ];

    /**
     * Obtener página y acción para una clase específica
     */
    public static function get_route_for_class(string $classname): ?array
    {
        foreach (self::$route_mappings as $page => $config) {
            foreach ($config['actions'] as $action => $class) {
                if (!isset($class['target'])) {
                    continue;
                }
                if ($class['target'] === $classname) {
                    return [
                        'page' => $page,
                        'action' => $action
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Obtener URL para una clase específica
     */
    public static function get_url_for_class(string $classname, array $additional_params = []): string
    {
        $route = self::get_route_for_class($classname);
        if (!$route) {
            return '';
        }

        $params = array_merge([
            'page' => $route['page'],
            'action' => $route['action']
        ], $additional_params);

        return add_query_arg($params, admin_url('admin.php'));
    }

    /**
     * Obtener contenido/clase para página y acción específica
     */
    public static function get_class_for_route(string $page, ?string $action = null)
    {
        if (!isset(self::$route_mappings[$page])) {
            return null;
        }

        $page_config = self::$route_mappings[$page];
        $action = $action ?: $page_config['default_action'];

        return $page_config['actions'][$action] ?? [];
    }

    public static function get_actions_for_page(string $page): ?array
    {
        if (!isset(self::$route_mappings[$page])) {
            return null;
        }

        return array_keys(self::$route_mappings[$page]['actions']);
    }

    public static function render_page(string $page, ?string $action = null)
    {
        $page_template = self::$route_mappings[$page] ?? null;
        if ($page_template === null) {
            return;
        }
        $class = self::get_class_for_route($page, $action);
        if ($action === null) {
            $action = $page_template['default_action'];
        }
        echo '<div class="wrap">';
        if (isset($page_template['header'])) {
            echo '<h1 class="wp-heading-inline">' . esc_html($page_template['header']) . '</h1>';
        }
        if (!empty($class['redirects'])) {
            foreach ($class['redirects'] as $redirect) {
                echo '<a class="page-title-action" href="' . esc_url(self::get_url_for_class($redirect['to'])) . '" class="page-title">' . esc_html($redirect['label']) . '</a>';
            }
        }
        echo '<hr class="wp-header-end">';
        if (isset($page_template['tabpane']) && $page_template['tabpane'] === true) {
            echo '<nav class="nav-tab-wrapper">';
            $panes = self::$route_mappings[$page]['actions'];
            foreach ($panes as $key => $pane) {
                if (isset($pane['is_tab']) && $pane['is_tab'] === true) {
                    echo '<a href="' . esc_url(self::get_url_for_class($pane['target'])) . '" class="nav-tab ' . ($action === $key ? 'nav-tab-active' : '') . '">' . esc_html($pane['tab_label'] ?? '') . '</a>';
                }
            }
            echo '</nav>';
        }
        if ($class) {
            echo '<div class="wrap">';
            (new $class['target']())->display();
            echo '</div>';
        } else {
            wp_die('Página no encontrada');
        }
        echo '</div>';
    }
}
