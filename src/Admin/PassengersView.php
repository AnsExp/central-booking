<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormTransfer;
use CentralTickets\Admin\View\TablePassengers;
use CentralTickets\Components\Displayer;

final class PassengersView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Pasajeros</h1>
            <?php if (!isset($_GET['action']) || $_GET['action'] !== 'transfer'): ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_passengers&action=transfer') ?>">Iniciar Traslado</a>
            <?php else: ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_passengers') ?>">Volver a la lista</a>
            <?php endif; ?>
            <?php if (!isset($_GET['action']) || $_GET['action'] === 'table'): ?>
                <?php (new TablePassengers())->display() ?>
            <?php elseif ($_GET['action'] === 'transfer'): ?>
                <?php (new FormTransfer())->display() ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
