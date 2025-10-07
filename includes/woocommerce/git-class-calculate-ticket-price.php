<?php
namespace CentralTickets;

use CentralTickets\Constants\PassengerConstants;
use CentralTickets\Constants\PriceExtraConstants;

use CentralTickets\Persistence\TransportRepository;

use WC_Product;

class CalculateTicketPrice
{
    public function calculate(WC_Product $product, array $pax, int $transport_id, bool $flexible)
    {
        $prices = $this->get_prices($product);
        $price_base = $this->calculate_price_pax($prices, $pax);
        $price_transport = $this->calculate_price_transport($transport_id);
        $price_flexible = $flexible ? $prices[PriceExtraConstants::FLEXIBLE] : 0;
        return $price_base + $price_transport + $price_flexible;
    }

    private function calculate_price_transport(int $transport_id): float
    {
        $transport = (new TransportRepository)->find($transport_id);
        $price = 0;
        if ($transport) {
            foreach ($transport->get_services() as $service) {
                $price += $service->price / 100;
            }
        }
        return $price;
    }

    public function get_prices(WC_Product $product)
    {
        return [
            PassengerConstants::KID => intval(get_post_meta($product->get_id(), 'price_kid', true)),
            PassengerConstants::RPM => intval(get_post_meta($product->get_id(), 'price_rpm', true)),
            PassengerConstants::STANDARD => intval(get_post_meta($product->get_id(), 'price_standar', true)),
            PriceExtraConstants::EXTRA => intval(get_post_meta($product->get_id(), 'price_extra', true)),
            PriceExtraConstants::FLEXIBLE => intval(get_post_meta($product->get_id(), 'price_flexible', true)),
        ];
    }

    private function calculate_price_pax(array $prices, array $pax)
    {
        $kid_count = $pax[PassengerConstants::KID];
        $rpm_count = $pax[PassengerConstants::RPM];
        $extra_count = $pax[PriceExtraConstants::EXTRA];
        $standard_count = $pax[PassengerConstants::STANDARD];

        return
            $prices[PriceExtraConstants::EXTRA] * $extra_count +
            $prices[PassengerConstants::KID] * $kid_count +
            $prices[PassengerConstants::RPM] * $rpm_count +
            $prices[PassengerConstants::STANDARD] * $standard_count;
    }
}