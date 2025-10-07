<?php
namespace CentralTickets;

use CentralTickets\Constants\PassengerConstants;
use CentralTickets\Constants\PriceExtraConstants;
use WC_Order_Item;

class CreateOrderLineItem
{
    public function add_line_item(WC_Order_Item $item, array $values)
    {
        if ($values['data']->product_type !== 'operator') {
            return;
        }
        $cart_ticket = $values['cart_ticket'] ?? null;
        if ($cart_ticket === null || !($cart_ticket instanceof CartTicket)) {
            return;
        }
        $item->add_meta_data('Trayecto', $cart_ticket->get_route()->get_origin()->name . ' » ' . $cart_ticket->get_route()->get_destiny()->name, true);
        $item->add_meta_data('Viaje', git_date_format($cart_ticket->date_trip), true) . ' ' . git_time_format($cart_ticket->get_route()->departure_time);
        $item->add_meta_data('Flexible', $cart_ticket->flexible ? 'Sí' : 'No', true);
        $item->add_meta_data('Transporte', $cart_ticket->get_transport()->nicename, true);
        if ($cart_ticket->get_pax(PassengerConstants::STANDARD) > 0) {
            $item->add_meta_data('Pax Estandar', $cart_ticket->get_pax(PassengerConstants::STANDARD), true);
        }
        if ($cart_ticket->get_pax(PassengerConstants::KID) > 0) {
            $item->add_meta_data('Pax Menor de Edad', $cart_ticket->get_pax(PassengerConstants::KID), true);
        }
        if ($cart_ticket->get_pax(PassengerConstants::RPM) > 0) {
            $item->add_meta_data('Pax Movolidad Reducida', $cart_ticket->get_pax(PassengerConstants::RPM), true);
        }
        if ($cart_ticket->get_pax(PriceExtraConstants::EXTRA) > 0) {
            $item->add_meta_data('Equipaje Extra', $cart_ticket->get_pax(PriceExtraConstants::EXTRA), true);
        }
        $item->add_meta_data('_original_data', serialize($cart_ticket));
    }
}