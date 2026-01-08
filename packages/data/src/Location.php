<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;

class Location
{
    public int $id = 0;
    public string $name = '';
    private Zone $zone;

    public function getZone()
    {
        if (!isset($this->zone)) {
            $this->zone = LazyLoader::loadZoneByLocation($this);
        }
        return $this->zone;
    }

    public function setZone(Zone $zone)
    {
        $this->zone = $zone;
    }
}
