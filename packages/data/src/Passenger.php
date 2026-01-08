<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Repository\LazyLoader;

class Passenger
{
    public int $id = 0;
    public string $name = '';
    public string $nationality = '';
    public string $typeDocument = '';
    public string $dataDocument = '';
    public PassengerConstants $type = PassengerConstants::STANDARD;
    public bool $served = false;
    public bool $approved = false;

    private Date $birthday;
    private Date $dateTrip;
    private Ticket $ticket;
    private Route $route;
    private Transport $transport;

    public function getTicket()
    {
        if (!isset($this->ticket)) {
            $this->ticket = LazyLoader::loadTicketByPassenger($this);
        }
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getRoute()
    {
        if (!isset($this->route)) {
            $this->route = LazyLoader::loadRouteByPassenger($this);
        }
        return $this->route;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    public function getTransport()
    {
        if (!isset($this->transport)) {
            $this->transport = LazyLoader::loadTransportByPassenger($this);
        }
        return $this->transport;
    }

    public function setTransport(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function getBirthday()
    {
        return $this->birthday;
    }

    public function setBirthday(Date $birthday)
    {
        $this->birthday = $birthday;
    }

    public function getDateTrip()
    {
        return $this->dateTrip;
    }

    public function setDateTrip(Date $dateTrip)
    {
        $this->dateTrip = $dateTrip;
    }
}
