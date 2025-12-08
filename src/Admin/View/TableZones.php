<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\Form\FormZone;
use CentralTickets\Components\Displayer;

final class TableZones implements Displayer
{
    public function display()
    {
        ?>
        <div style="max-width: 200px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 200px;" scope="col">Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (git_get_zones() as $zone): ?>
                        <tr>
                            <td>
                                <span><?= esc_html($zone->name) ?></span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= $zone->id ?>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a
                                            href="<?= AdminRouter::get_url_for_class(FormZone::class, ['id' => $zone->id]) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php

    }
}