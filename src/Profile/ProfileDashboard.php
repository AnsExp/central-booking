<?php
namespace CentralTickets\Profile;

use CentralTickets\Constants\UserConstants;
use CentralTickets\Components\Component;
use CentralTickets\Profile\Panes\ProfilePaneNotFound;
use CentralTickets\Profile\Panes\ProfilePaneCoupon;
use CentralTickets\Profile\Panes\ProfilePaneInvoice;
use CentralTickets\Profile\Panes\ProfilePaneOrder;
use CentralTickets\Profile\Panes\ProfilePaneProfile;
use CentralTickets\Profile\Panes\ProfilePaneTrip;
use WP_User;

class ProfileDashboard implements Component
{
    private WP_User $current_user;
    private array $user_roles;
    private string $current_tab;

    public function __construct()
    {
        $this->current_user = wp_get_current_user();
        $this->user_roles = (array) $this->current_user->roles;
        $this->current_tab = $_GET['tab'] ?? 'profile';
    }

    public function compact(): string
    {
        ob_start();
        ?>
        <div class="container">
            <div class="dashboard-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <?php foreach ($this->get_available_tabs() as $tab_key => $tab_config): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $this->current_tab === $tab_key ? 'active' : '' ?>"
                                href="<?= $this->get_tab_url($tab_key) ?>" role="tab" data-tab="<?= esc_attr($tab_key) ?>">
                                <?= esc_html($tab_config['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="dashboard-content">
                <div class="tab-content">
                    <?= $this->render_current_tab(); ?>
                </div>
            </div>
        </div>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
            crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <?php
        return ob_get_clean();
    }

    private function get_available_tabs()
    {
        $all_tabs = [
            'profile' => [
                'label' => 'Mi Perfil',
                'roles' => [UserConstants::CUSTOMER, UserConstants::OPERATOR, UserConstants::ADMINISTRATOR],
                'callback' => [new ProfilePaneProfile(), 'compact']
            ],
            'orders' => [
                'label' => 'Mis Pedidos',
                'roles' => [UserConstants::CUSTOMER, UserConstants::OPERATOR, UserConstants::ADMINISTRATOR],
                'callback' => [new ProfilePaneOrder(), 'compact']
            ],
            'coupons' => [
                'label' => 'Cupones',
                'roles' => [UserConstants::OPERATOR, UserConstants::ADMINISTRATOR],
                'callback' => [new ProfilePaneCoupon(), 'compact']
            ],
            'trips' => [
                'label' => 'Bitácora de Viajes',
                'icon' => 'dashicons dashicons-location',
                'roles' => [UserConstants::OPERATOR, UserConstants::ADMINISTRATOR],
                'callback' => [new ProfilePaneTrip(), 'compact']
            ],
            'sales' => [
                'label' => 'Ventas',
                'roles' => [UserConstants::OPERATOR, UserConstants::ADMINISTRATOR],
                'callback' => [new ProfilePaneInvoice(), 'compact']
            ],
        ];

        $available_tabs = [];

        foreach ($all_tabs as $tab_key => $tab_config) {
            if ($this->user_has_access_to_tab($tab_config['roles'])) {
                $available_tabs[$tab_key] = $tab_config;
            }
        }

        return $available_tabs;
    }

    private function user_has_access_to_tab(array $required_roles)
    {
        if (in_array(UserConstants::ADMINISTRATOR, $this->user_roles)) {
            return true;
        }
        return !empty(array_intersect($this->user_roles, $required_roles));
    }

    private function get_tab_url(string $tab_key)
    {
        return add_query_arg(['tab' => $tab_key], remove_query_arg(['tab', 'action']));
    }

    private function render_current_tab()
    {
        $available_tabs = $this->get_available_tabs();

        if (!isset($available_tabs[$this->current_tab])) {
            return (new ProfilePaneNotFound())->compact();
        }

        $tab_config = $available_tabs[$this->current_tab];
        $callback = $tab_config['callback'];

        return $callback();
    }
}