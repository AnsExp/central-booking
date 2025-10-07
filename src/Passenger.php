<?php
namespace CentralTickets;

use Exception;
use CentralTickets\Constants\PassengerConstants;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\TicketRepository;
use CentralTickets\Persistence\TransportRepository;

class Passenger
{
    public int $id = 0;
    public string $name = '';
    public string $type = PassengerConstants::STANDARD;
    public string $nationality = '';
    public string $type_document = '';
    public string $data_document = '';
    public bool $served = false;
    public bool $approved = false;
    public string $date_trip = '0000-00-00';
    public string $birthday = '0000-00-00';

    private Ticket $ticket;
    private Route $route;
    private Transport $transport;

    public function get_ticket()
    {
        if (!isset($this->ticket)) {
            $repository = new TicketRepository;
            $result = $repository->find_first(['id_passenger' => "{$this->id}"]);
            if ($result === null) {
                throw new Exception("El ticket asignada al pasajero no existe.");
            }
            $this->ticket = $result;
        }
        return $this->ticket;
    }

    public function set_ticket(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function get_route()
    {
        if (!isset($this->route)) {
            $repository = new RouteRepository;
            $result = $repository->find_first(['id_passenger' => $this->id]);
            if ($result === null) {
                throw new Exception("La ruta asignada al pasajero no existe.");
            }
            $this->route = $result;
        }
        return $this->route;
    }

    public function set_route(Route $route)
    {
        $this->route = $route;
    }

    public function get_transport()
    {
        if (!isset($this->transport)) {
            $repository = new TransportRepository;
            $result = $repository->find_first(['id_passenger' => $this->id]);
            if ($result === null) {
                throw new Exception("El transporte asignada al pasajero no existe.");
            }
            $this->transport = $result;
        }
        return $this->transport;
    }

    public function set_transport(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function __clone(){
        $clone = new Passenger();
        $clone->id = $this->id;
        $clone->name = $this->name;
        $clone->type = $this->type;
        $clone->nationality = $this->nationality;
        $clone->type_document = $this->type_document;
        $clone->data_document = $this->data_document;
        $clone->served = $this->served;
        $clone->approved = $this->approved;
        $clone->date_trip = $this->date_trip;
        $clone->birthday = $this->birthday;
        return $clone;
    }
}
