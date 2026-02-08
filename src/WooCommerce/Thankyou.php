<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Constants\TicketStatus;

class Thankyou
{
    public function thankyou(int $order_id)
    {
        if (get_post_meta($order_id, '_order_saved', true) === 'yes') {
            return;
        }

        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $ticket = [];

        foreach ($items as $item) {
            $ticket[] = unserialize($item->get_meta('_original_data'));
        }

        $coupon_id = -1;

        foreach ($order->get_coupons() as $coupon) {
            $coupon_id = wc_get_coupon_id_by_code($coupon->get_code());
        }

        foreach ($ticket as $ticketData) {
            if ($ticketData instanceof CartItem) {
                $ticket = git_ticket_create([
                    'flexible' => $ticketData->isFlexible(),
                    'total_amount' => $ticketData->calculatePrice() * 100,
                    'status' => $coupon_id === -1 ? TicketStatus::PAYMENT : TicketStatus::PENDING,
                ]);

                $ticket->setOrder($order);

                $coupon = null;

                if ($coupon_id !== -1) {
                    $coupon = get_post($coupon_id);
                    $ticket->setCoupon($coupon);
                }

                $ticket->setPassengers(array_map(fn(CartPassenger $passenger) => git_passenger_create([
                    'name' => $passenger->name,
                    'type' => $passenger->type,
                    'served' => false,
                    'approved' => $coupon === null,
                    'birthday' => $passenger->birthday,
                    'date_trip' => $ticketData->getDateTrip()->format('Y-m-d'),
                    'nationality' => $passenger->nationality,
                    'type_document' => $passenger->typeDocument,
                    'data_document' => $passenger->dataDocument,
                    'route_id' => $ticketData->getRoute()->id,
                    'transport_id' => $ticketData->getTransport()->id,
                ]), $ticketData->getPassengers()));

                if ($ticket->save() !== null) {
                    if ($coupon !== null) {
                        $operator = git_operator_by_coupon($coupon);
                        if ($operator !== null) {
                            $business_plan = $operator->getBusinessPlan();
                            $operator->setBusinessPlan($business_plan['limit'], $business_plan['counter'] + 1);
                            $operator->getCoupons();
                            $operator->save();
                        }
                    }
                    $item->delete_meta_data('_original_data');
                    $item->save();
                }
            }
        }

        update_post_meta($order_id, '_order_saved', 'yes');
    }
}