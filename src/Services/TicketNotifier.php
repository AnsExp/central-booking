<?php
namespace CentralTickets\Services;

use CentralTickets\Services\Senders\EmailTicketSender;
use CentralTickets\Services\Senders\TicketSender;
use CentralTickets\Ticket;

class TicketNotifier
{
    /**
     * @var array<TicketSender>
     */
    private array $senders = [];

    public function __construct()
    {
        $this->senders = [
            new EmailTicketSender(),
            // new WhatsAppTicketSender,
        ];
    }
    public function notify(Ticket $ticket)
    {
        foreach ($this->senders as $sender) {
            $sender->send($ticket);
        }
    }
}
