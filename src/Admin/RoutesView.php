<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormRoute;
use CentralTickets\Admin\View\TableRoutes;
use CentralTickets\Components\Displayer;

final class RoutesView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Rutas</h1>
            <?php if (!isset($_GET['activity']) || $_GET['activity'] !== 'form'): ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_routes&activity=form') ?>">Añadir nueva
                    ruta</a>
            <?php else: ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_routes') ?>">Volver a la lista</a>
            <?php endif; ?>
            <?php if (!isset($_GET['activity']) || $_GET['activity'] === 'table'): ?>
                <?php (new TableRoutes())->display() ?>
            <?php else: ?>
                <?php (new FormRoute($_GET['id'] ?? 0))->display() ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
