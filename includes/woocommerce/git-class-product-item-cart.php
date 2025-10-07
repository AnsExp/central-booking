<?php
namespace CentralTickets;

use CentralTickets\Constants\PassengerConstants;

class ProductItemCart
{
    public function is_valid(array $cart_item)
    {
        $item_data = [];
        if ($cart_item['data']->get_type() !== 'operator') {
            return $item_data;
        }
        $cart_ticket = $cart_item['cart_ticket'] ?? null;
        if ($cart_ticket === null || !($cart_ticket instanceof CartTicket)) {
            return $item_data;
        }
        $route = $cart_ticket->get_route();
        $transport = $cart_ticket->get_transport();
        $item_data[] = [
            'name' => 'Ruta',
            'value' => "{$route->get_origin()->name} » {$route->get_destiny()->name}",
        ];
        $item_data[] = [
            'name' => 'Viaje',
            'value' => git_date_format($cart_ticket->date_trip) . ' - ' . git_time_format($route->departure_time)
        ];
        $item_data[] = [
            'name' => 'Transporte',
            'value' => $transport->nicename
        ];
        if ($cart_ticket->get_pax(PassengerConstants::STANDARD) > 0) {
            $item_data[] = [
                'name' => 'Pax Estandar',
                'value' => $cart_ticket->get_pax(PassengerConstants::STANDARD)
            ];
        }
        if ($cart_ticket->get_pax(PassengerConstants::KID) > 0) {
            $item_data[] = [
                'name' => 'Pax Menores de Edad',
                'value' => $cart_ticket->get_pax(PassengerConstants::KID)
            ];
        }
        if ($cart_ticket->get_pax(PassengerConstants::RPM) > 0) {
            $item_data[] = [
                'name' => 'Pax Movilidad Reducida',
                'value' => $cart_ticket->get_pax(PassengerConstants::RPM)
            ];
        }
        if ($cart_ticket->get_pax('extra') > 0) {
            $item_data[] = [
                'name' => 'Equipaje extra',
                'value' => $cart_ticket->get_pax('extra')
            ];
        }
        $item_data[] = [
            'name' => 'Ticket flexible',
            'value' => $cart_ticket->flexible ? 'Sí' : 'No'
        ];
        return $item_data;
    }
}