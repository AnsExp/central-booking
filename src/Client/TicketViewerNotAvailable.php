<?php
namespace CentralTickets\Client;

use CentralTickets\Components\Component;
use CentralTickets\Components\CompositeComponent;

class TicketViewerNotAvailable implements Component
{
    public function compact()
    {
        $container = new CompositeComponent();
        return $container->compact();
    }
}
