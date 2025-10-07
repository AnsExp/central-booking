<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\Form\FormOperator;
use CentralTickets\Admin\Form\FormOperatorsExternal;
use CentralTickets\Admin\View\TableOperators;
use CentralTickets\Components\Displayer;

final class OperatorsView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Operadores</h1>
            <?php if (isset($_GET['action'])): ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_operators') ?>">Volver a la lista</a>
            <?php else: ?>
                <a class="page-title-action" href="<?= admin_url('admin.php?page=central_operators&action=operator_link') ?>">Crear
                    vínculo externo</a>
            <?php endif; ?>
            <div id="info-panel"></div>
            <?php if (!isset($_GET['action']) || $_GET['action'] === 'table'): ?>
                <?php (new TableOperators())->display() ?>
            <?php elseif (!isset($_GET['action']) || $_GET['action'] === 'operator_link'): ?>
                <?php (new FormOperatorsExternal())->display() ?>
            <?php elseif ($_GET['action'] === 'edit'): ?>
                <?php (new FormOperator)->display() ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
