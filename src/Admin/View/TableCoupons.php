<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Components\Displayer;

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
                        <th scope="col"> Código </th>
                        <th scope="col"> Operador </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon):
                        $operator = git_get_operator_by_coupon($coupon);
                        ?>
                        <tr>
                            <td>
                                <span>
                                    <?= esc_html($coupon->post_title) ?>
                                </span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= $coupon->ID ?>
                                    </span>
                                    <span class="edit"> | </span>
                                    <span class="edit">
                                        <a href="<?= add_query_arg([
                                            'action' => 'edit',
                                            'id' => $coupon->ID,
                                        ]) ?>">
                                            Editar
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td><?= esc_html($operator ? $operator->first_name . ' ' . $operator->last_name : 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php

    }
}