<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormCoupon;
use CentralTickets\Admin\View\TableCoupons;
use CentralTickets\Admin\View\TablePassengersLog;
use CentralTickets\Admin\View\TableTicketsLog;
use CentralTickets\Components\Displayer;

final class MarketingView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Marketing</h1>
            <?php
            $this->render_tab_navigation();
            $this->render_tab_content();
            ?>
        </div>
        <?php
    }

    private function render_tab_content()
    {
        $tabs = $this->get_tabs();
        $current_tab = $_GET['tab'] ?? 'coupons';

        if (isset($tabs[$current_tab])) {
            $tabs[$current_tab]['callback']();
        } else {
            echo '<p>Tab no encontrado.</p>';
        }
    }

    private function render_tab_navigation()
    {
        echo '<nav class="nav-tab-wrapper">';

        foreach ($this->get_tabs() as $tab_key => $tab_data) {
            $url = add_query_arg([
                'page' => $_GET['page'] ?? 'git_settings',
                'tab' => $tab_key
            ], admin_url('admin.php'));

            $active_class = ($_GET['tab'] ?? 'passengers') === $tab_key ? 'nav-tab-active' : '';

            printf(
                '<a href="%s" class="nav-tab %s">%s</a>',
                esc_url($url),
                esc_attr($active_class),
                esc_html($tab_data['title'])
            );
        }

        echo '</nav>';
    }

    private function get_tabs()
    {
        return [
            'coupons' => [
                'title' => 'Cupones',
                'callback' => function () {
                    $action = $_GET['action'] ?? 'table';
                    if ($action === 'edit') {
                        (new FormCoupon())->display();
                    } else if ($action === 'table') {
                        (new TableCoupons())->display();
                    } else {
                        echo '<p>Acción no encontrada.</p>';
                    }
                }
            ],
            'planes' => [
                'title' => 'Planes',
                'callback' => function () {
                    (new TableTicketsLog())->display();
                }
            ]
        ];
    }
}
