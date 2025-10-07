<?php
namespace CentralTickets\Services\Actions;

use CentralTickets\Passenger;
use CentralTickets\Route;
use CentralTickets\Services\PassengerService;
use CentralTickets\Services\TicketService;
use CentralTickets\Services\TransportService;
use CentralTickets\Transport;

class TransferPassengers
{
    /**
     * @var array<Passenger>
     */
    private array $passengers = [];

    /**
     * @param array<int|Passenger> $passenger
     * @return void
     */
    private function set_passengers(array $passenger)
    {
        $this->passengers = [];
        foreach ($passenger as $p) {
            $this->add_passenger($p);
        }
    }

    private function add_passenger(int|Passenger $passenger)
    {
        if ($passenger instanceof Passenger) {
            $this->passengers[$passenger->id] = $passenger;
        } elseif (is_int($passenger)) {
            $found_passenger = git_get_passenger_by_id($passenger);
            if (!$found_passenger) {
                return;
            }
            if ($found_passenger->get_ticket()->flexible) {
                $this->passengers[$found_passenger->id] = $found_passenger;
            }
        }
    }

    public function transfer(Route $route, Transport $transport, string $date_trip, array $passengers)
    {
        $this->set_passengers($passengers);
        $passenger_service = new PassengerService();
        $transport_service = new TransportService();
        $ticket_service = new TicketService();
        if (
            !$transport_service->check_availability(
                $transport->id,
                $route->id,
                $date_trip,
                count($this->passengers)
            )
        ) {
            return false;
        }
        $tickets = [];
        foreach ($this->passengers as $passenger) {
            $tickets[$passenger->get_ticket()->id] = $passenger->get_ticket();
            $passenger_service->transfer_passenger(
                $passenger->id,
                $route->id,
                $transport->id,
                $date_trip
            );
        }

        foreach ($tickets as $value) {
            $ticket_service->toggle_flexible($value->id, false);
        }

        return true;
    }
}
