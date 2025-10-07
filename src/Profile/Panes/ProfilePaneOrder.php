<?php
namespace CentralTickets\Profile\Panes;

use CentralTickets\Components\Component;
use CentralTickets\Profile\Forms\FormTickets;
use CentralTickets\Profile\Tables\TableOrder;
use CentralTickets\Profile\Tables\TableOrderTickets;

final class ProfilePaneOrder implements Component
{
    public function compact()
    {
        $output = '';
        if (!isset($_GET['action'])) {
            $output .= (new TableOrder())->compact();
        } elseif ($_GET['action'] === 'view_order') {
            $output .= (new TableOrderTickets())->compact();
        } elseif ($_GET['action'] === 'edit_flexible') {
            $output .= (new FormTickets())->compact();
        }
        return $output;
    }
}