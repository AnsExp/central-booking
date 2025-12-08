<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\Form\FormCoupon;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\TextComponent;

final class TableCoupons implements Displayer
{
    public function display()
    {
        $coupons = git_get_all_coupons();
        ?>
        <div style="max-width: 500px; margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"> Comercializador </th>
                        <th scope="col"> Medio </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon):
                        $operator = git_get_operator_by_coupon($coupon);
                        ?>
                        <tr style="border-bottom: 1px solid gray;">
                            <td>
                                <span>
                                    <?= esc_html($coupon->post_title) ?>
                                </span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= $coupon->ID ?>
                                    </span>
                                    <span class="edit"> | </span>
                                    <span class="edit"><?= esc_html($operator ? "{$operator->first_name} {$operator->last_name}" : 'N/A') ?></span>
                                    <span class="edit"> | </span>
                                    <span class="edit">
                                        <a href="<?= esc_url(AdminRouter::get_url_for_class(
                                            FormCoupon::class,
                                            ['id' => $coupon->ID]
                                        )) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php
                                $link = new TextComponent('a', 'Ver');
                                $link->set_attribute('href', git_get_url_logo_by_coupon($coupon));
                                $link->set_attribute('target', '_blank');
                                $link->display();
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php

    }
}