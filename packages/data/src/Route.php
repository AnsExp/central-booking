<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Constants\TransportConstants;
use CentralBooking\Data\Repository\LazyLoader;

class Route
{
    public int $id = 0;
    public TransportConstants $type = TransportConstants::MARINE;
    private Location $origin;
    private Location $destiny;
    public Time $departureTime;
    public Time $arrivalTime;
    /**
     * @var array<Transport>
     */
    private array $transports;

    public function getOrigin()
    {
        if (!isset($this->origin)) {
            $this->origin = LazyLoader::loadOriginByRoute($this);
        }
        return $this->origin;
    }

    public function setOrigin(Location $origin)
    {
        $this->origin = $origin;
    }

    public function getDestiny()
    {
        if (!isset($this->destiny)) {
            $this->destiny = LazyLoader::loadDestinyByRoute($this);
        }
        return $this->destiny;
    }

    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    public function setDepartureTime(Time $departureTime)
    {
        $this->departureTime = $departureTime;
    }

    public function getArrivalTime()
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(Time $arrivalTime)
    {
        $this->arrivalTime = $arrivalTime;
    }

    public function setDestiny(Location $destiny)
    {
        $this->destiny = $destiny;
    }

    public function getTransports()
    {
        if (!isset($this->transports)) {
            $this->transports = LazyLoader::loadTransportsByRoute($this);
        }
        return $this->transports;
    }

    /**
     * @param array<Transport> $transports
     * @return void
     */
    public function setTransports(array $transports)
    {
        $this->transports = $transports;
    }
}
