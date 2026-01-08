<?php
namespace CentralTickets\Profile\Panes;

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\Profile\Forms\FormTripOperator;
use CentralTickets\Profile\Tables\TableTripOperator;

final class ProfilePaneTrip implements ComponentInterface
{

    public function compact()
    {
        ob_start();
        echo (new FormTripOperator())->compact();
        echo (new TableTripOperator())->compact();
        return ob_get_clean();
    }
}
