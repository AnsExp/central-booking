<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormLocation;
use CentralTickets\Admin\Form\FormZone;
use CentralTickets\Admin\View\TableLocations;
use CentralTickets\Admin\View\TableZones;
use CentralTickets\Components\Displayer;

final class LocationsView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Ubicaciones</h1>
            <?php
            $action = $_GET['action'] ?? 'table';
            if ($action === 'table'): ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_locations&action=form') ?>">Añadir nueva
                    ubicación</a>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_locations&action=zone') ?>">Añadir nueva
                    zona</a>
            <?php else: ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_locations') ?>">Volver a la lista</a>
            <?php endif; ?>

            <?php if ($action === 'form'): ?>
                <?php (new FormLocation())->display() ?>
            <?php elseif ($action === 'zone'): ?>
                <?php (new FormZone())->display() ?>
            <?php else: ?>
                <?php (new TableLocations())->display() ?>
                <hr>
                <h2 class="wp-heading-inline">Zonas</h2>
                <?php (new TableZones())->display() ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
