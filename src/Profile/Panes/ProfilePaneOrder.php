<?php
namespace CentralTickets\Profile\Panes;

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\Profile\Forms\FormTickets;
use CentralBooking\Profile\Tables\TableOrder;
use CentralBooking\Profile\Tables\TableOrderTickets;

final class ProfilePaneOrder implements ComponentInterface
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