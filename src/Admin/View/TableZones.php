<?php
namespace CentralTickets\Admin\View;

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
                                            href="<?= add_query_arg(['action' => 'zone', 'id' => $zone->id], admin_url('admin.php?page=central_locations')) ?>">Editar</a>
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