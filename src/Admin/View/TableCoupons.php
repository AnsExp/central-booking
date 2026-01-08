<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormCoupon;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\TextComponent;

final class TableCoupons implements DisplayerInterface
{
    public function render()
    {
        $coupons = git_coupons();
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
                        $operator = git_operator_by_coupon($coupon);
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
                                    <span class="edit"><?= esc_html($operator ? "{$operator->getUser()->first_name} {$operator->getUser()->last_name}" : 'N/A') ?></span>
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
                                $link->attributes->set('href', git_get_url_logo_by_coupon($coupon));
                                $link->attributes->set('target', '_blank');
                                $link->render();
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