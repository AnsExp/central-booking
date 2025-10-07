<?php
namespace CentralTickets;

use CentralTickets\Constants\PassengerConstants;
use CentralTickets\Constants\PriceExtraConstants;
use WC_Product;

class CartTicket
{
    private WC_Product $product;
    private Route $route;
    private Transport $transport;
    /**
     * @var array<CartPassenger>
     */
    private array $passengers;
    public string $date_trip;
    public bool $flexible;
    public array $pax = [];

    public function get_route()
    {
        return $this->route;
    }

    public function get_transport()
    {
        return $this->transport;
    }

    public function get_passengers()
    {
        return $this->passengers;
    }

    public function set_route(Route $route  )
    {
        $this->route = $route;
    }

    public function set_transport(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function set_passengers(array $passengers)
    {
        $this->passengers = $passengers;
    }

    public function get_pax(string $key)
    {
        return [
            PassengerConstants::KID => intval($this->pax['kid']),
            PassengerConstants::RPM => intval($this->pax['rpm']),
            PriceExtraConstants::EXTRA => intval($this->pax['extra']),
            PassengerConstants::STANDARD => intval($this->pax['standard']),
        ][$key] ?? 0;
    }

    public function calculate_price()
    {
        $calculate = new CalculateTicketPrice();
        return $calculate->calculate(
            $this->product,
            $this->pax,
            $this->transport->id,
            $this->flexible
        );
    }

    public static function create(array $data)
    {
        $route = git_get_route_by_id($data['trip']['route']);
        $transport = git_get_transport_by_id($data['trip']['transport']);
        if (!$route || !$transport) {
            return false;
        }
        $ticket = new self();
        $ticket->flexible = $data["flexible"];
        $ticket->date_trip = $data['trip']['date_trip'];
        $ticket->set_route($route);
        $ticket->set_transport($transport);
        $ticket->set_passengers(array_map(
            fn($passengerData) =>
            CartPassenger::create($passengerData),
            $data['passengers']
        ));
        $ticket->pax = $data['pax'];
        $ticket->product = wc_get_product($data['product']);
        return $ticket;
    }
}