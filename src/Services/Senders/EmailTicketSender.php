<?php
namespace CentralTickets\Services\Senders;

use CentralTickets\Placeholders\PlaceholderEngineTicket;
use CentralTickets\Ticket;

class EmailTicketSender implements TicketSender
{
    public function send(Ticket $ticket)
    {
        $order = $ticket->get_order();
        return wp_mail(
            $order->get_billing_email(),
            "Gracias por tu compra (Ticket # {$ticket->id})",
            $this->create_message($ticket),
            [
                'Content-Type: text/html; charset=UTF-8',
                'From: Central Reservas <admin@supgalapagos.tours>'
            ]
        );
    }

    private function create_message(Ticket $ticket)
    {
        $placeholder_engine = new PlaceholderEngineTicket($ticket);
        $message = git_get_setting('notification_email', '');
        return $placeholder_engine->process($message);
    }
}