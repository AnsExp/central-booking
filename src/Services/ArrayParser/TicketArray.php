<?php
namespace CentralTickets\Services\ArrayParser;

use CentralTickets\Service;
use CentralTickets\Ticket;
use CentralTickets\Passenger;

/**
 * @extends parent<Ticket>
 */
class TicketArray implements ArrayParser
{
    public function get_array($ticket)
    {
        return [
            'id' => $ticket->id,
            'order_number' => $ticket->get_order()->get_id(),
            'name_buyer' => "{$ticket->get_order()->get_billing_first_name()} {$ticket->get_order()->get_billing_last_name()}",
            'phone_buyer' => $ticket->get_order()->get_billing_phone(),
            'date_creation' => $ticket->get_order()->get_date_created()->format('Y-m-d H:i:s'),
            'flexible' => $ticket->flexible,
            'total_amount' => $ticket->total_amount,
            'status' => $ticket->status,
            'coupon' => $ticket->get_coupon() ? [
                'id' => $ticket->get_coupon()->ID,
                'code' => $ticket->get_coupon()->post_title,
            ] : null,
            'proof_payment' => $ticket->get_meta('proof_payment'),
            'passengers' => array_map(fn(Passenger $passenger) => [
                'id' => $passenger->id,
                'name' => $passenger->name,
                'birthday' => $passenger->birthday,
                'date_trip' => $passenger->date_trip,
                'nationality' => $passenger->nationality,
                'type_document' => $passenger->type_document,
                'data_document' => $passenger->data_document,
                'served' => $passenger->served,
                'approved' => $passenger->approved,
                'route' => [
                    'id' => $passenger->get_route()->id,
                    'time' => $passenger->get_route()->departure_time,
                    'origin' => [
                        'id' => $passenger->get_route()->get_origin()->id,
                        'name' => $passenger->get_route()->get_origin()->name,
                        'zone' => [
                            'id' => $passenger->get_route()->get_origin()->get_zone()->id,
                            'name' => $passenger->get_route()->get_origin()->get_zone()->name,
                        ]
                    ],
                    'destiny' => [
                        'id' => $passenger->get_route()->get_destiny()->id,
                        'name' => $passenger->get_route()->get_destiny()->name,
                        'zone' => [
                            'id' => $passenger->get_route()->get_origin()->get_zone()->id,
                            'name' => $passenger->get_route()->get_origin()->get_zone()->name,
                        ]
                    ],
                    'type' => $passenger->get_route()->type,
                ],
                'transport' => [
                    'id' => $passenger->get_transport()->id,
                    'nicename' => $passenger->get_transport()->nicename,
                    'type' => $passenger->get_transport()->type,
                    'services' => array_map(fn(Service $service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->price,
                        'icon' => $service->icon,
                    ], $passenger->get_transport()->get_services()),
                ],
            ], $ticket->get_passengers()),
        ];
    }
}
