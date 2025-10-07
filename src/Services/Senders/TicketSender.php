<?php
namespace CentralTickets\Services\Senders;

use CentralTickets\Ticket;
interface TicketSender
{
    public function send(Ticket $ticket);
}