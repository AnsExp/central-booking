<?php
namespace CentralTickets\Services\Senders;

use CentralTickets\Configurations;
use CentralTickets\Placeholders\PlaceholderEngineTicket;
use CentralTickets\Ticket;

class EmailTicketSender implements TicketSender
{
    public function send(Ticket $ticket)
    {
        $url = get_site_url();
        $parsed = parse_url($url);
        $subfix = '@' . ($parsed['host'] ?? $url);
        $order = $ticket->get_order();
        $title = Configurations::get_map('notification_email.title', "Central Reservas - Ticket # {$ticket->id}");
        $sender = Configurations::get_map('notification_email.sender', 'admin');
        return wp_mail(
            $order->get_billing_email(),
            $title,
            $this->create_message($ticket),
            [
                'Content-Type: text/html; charset=UTF-8',
                "From: {$title} <{$sender}{$subfix}>"
            ]
        );
    }

    private function create_message(Ticket $ticket)
    {
        $placeholder_engine = new PlaceholderEngineTicket($ticket);
        $content = Configurations::get_map('notification_email.content', "");
        return $placeholder_engine->process($content);
    }
}