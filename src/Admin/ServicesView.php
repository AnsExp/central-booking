<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormService;
use CentralTickets\Admin\View\TableServices;
use CentralTickets\Components\Displayer;

final class ServicesView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Servicios</h1>
            <?php if (!isset($_GET['activity']) || $_GET['activity'] !== 'form'): ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_services&activity=form') ?>">Añadir nuevo
                    servicio</a>
            <?php else: ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_services') ?>">Volver a la lista</a>
            <?php endif; ?>
            <?php if (!isset($_GET['activity']) || $_GET['activity'] === 'table'): ?>
                <?php (new TableServices())->display() ?>
            <?php else: ?>
                <?php (new FormService())->display() ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
