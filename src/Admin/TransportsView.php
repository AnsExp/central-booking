<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormTransport;
use CentralTickets\Admin\View\TableTransports;
use CentralTickets\Components\Displayer;

final class TransportsView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Transportes</h1>
            <?php if (!isset($_GET['action']) || $_GET['action'] !== 'edit'): ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_transports&action=edit') ?>">Añadir nuevo
                    transporte</a>
            <?php else: ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_transports') ?>">Volver a la lista</a>
            <?php endif; ?>
            <?php if (!isset($_GET['action']) || $_GET['action'] === 'table'): ?>
                <?php (new TableTransports())->display() ?>
            <?php elseif ($_GET['action'] === 'edit'): ?>
                <?php (new FormTransport())->display() ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
