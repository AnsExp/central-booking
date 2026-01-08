<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;

final class Service
{
    public int $id = 0;
    public string $name = '';
    public string $icon = '';
    public int $price = 0;
    /**
     * @var array<Transport>
     */
    private array $transports;

    public function getTransports()
    {
        if (!isset($this->transports)) {
            $this->transports = LazyLoader::loadTransportsByService($this);
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
