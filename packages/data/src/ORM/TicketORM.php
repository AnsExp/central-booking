<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Ticket;

/**
 * @implements ORMInterface<Ticket>
 */
final class TicketORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $ticket = new Ticket();
        $ticket->id = (int) ($data['id'] ?? 0);
        $ticket->total_amount = (int) ($data['total_amount'] ?? 0);
        $ticket->flexible = $data['flexible'] === '1';
        $ticket->status = TicketStatus::tryFrom($data['status']) ?? TicketStatus::PENDING;
        $ticket->setClient(get_user((int) ($data['id_client'] ?? 0) ?? null));
        if ($data['id_coupon']) {
            $ticket->setCoupon(get_post((int) $data['id_coupon']));
        }
        return $ticket;
    }
}
