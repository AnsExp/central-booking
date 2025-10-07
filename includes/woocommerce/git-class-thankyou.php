<?php
namespace CentralTickets;

use CentralTickets\Services\PackageData\PassengerData;
use CentralTickets\Services\PackageData\TicketData;
use CentralTickets\Services\TicketService;
use CentralTickets\Constants\PassengerConstants;
use CentralTickets\Constants\TicketConstants;

class Thankyou
{
    public function thankyou(int $order_id)
    {
        if (get_post_meta($order_id, '_order_saved', true) === 'yes') {
            return;
        }

        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $data = [];

        foreach ($items as $item) {
            $data[] = unserialize($item->get_meta('_original_data'));
        }

        $coupon_id = -1;

        foreach ($order->get_coupons() as $coupon) {
            $coupon_id = wc_get_coupon_id_by_code($coupon->get_code());
        }

        foreach ($data as $ticket) {
            if (!($ticket instanceof CartTicket)) {
                continue;
            }

            $response = (new TicketService())
                ->save(new TicketData(
                    id_order: $order_id,
                    id_coupon: $coupon_id,
                    flexible: $ticket->flexible,
                    total_amount: $ticket->calculate_price() * 100,
                    status: $coupon_id === -1 ? TicketConstants::PAYMENT : TicketConstants::PENDING,
                    passengers: array_map(
                        fn(CartPassenger $passenger) => (new PassengerData(
                            name: $passenger->name,
                            nationality: $passenger->nationality,
                            birthday: $passenger->birthday,
                            date_trip: $ticket->date_trip,
                            type_document: $passenger->type_document,
                            data_document: $passenger->data_document,
                            type: PassengerConstants::STANDARD,
                            id_route: $ticket->get_route()->id,
                            id_transport: $ticket->get_transport()->id,
                        )),
                        $ticket->get_passengers()
                    )
                ));
            if ($response !== null) {
                $item->get_meta('_ticket_url');
            }
        }

        update_post_meta($order_id, '_order_saved', 'yes');
    }
}