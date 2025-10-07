<?php
namespace CentralTickets\Services\PackageData;

use CentralTickets\Location;
use CentralTickets\Zone;

class LocationData implements PackageData
{
    public function __construct(
        public readonly string $name = '',
        public readonly int $id_zone = 0,
    ) { }

    public function get_data()
    {
        $location = new Location;
        $location->name = $this->name;

        if ($this->id_zone > 0) {
            $zone = new Zone;
            $zone->id = $this->id_zone;
            $location->set_zone($zone);
        }

        return $location;
    }
}
