<?php
namespace CentralTickets\Admin;

use CentralTickets\Admin\View\TableTickets;
use CentralTickets\Components\Displayer;

final class TicketsView implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Tickets</h1>
            <?php (new TableTickets())->display() ?>
        </div>
        <?php
    }
}
