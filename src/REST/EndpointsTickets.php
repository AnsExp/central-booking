<?php
namespace CentralTickets\REST;

use CentralTickets\REST\Controllers\TicketController;
use CentralTickets\Ticket;

/**
 * @extends parent<Ticket>
 */
class EndpointsTickets extends BaseEndpoints
{
    public function __construct()
    {
        parent::__construct(
            'tickets',
            new TicketController()
        );
    }
}
