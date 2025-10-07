<?php
namespace CentralTickets\Profile\Panes;

use CentralTickets\Components\Component;
use CentralTickets\Profile\Forms\FormTripOperator;
use CentralTickets\Profile\Tables\TableTripOperator;

final class ProfilePaneTrip implements Component
{

    public function compact()
    {
        ob_start();
        echo (new FormTripOperator())->compact();
        echo (new TableTripOperator())->compact();
        return ob_get_clean();
    }
}
