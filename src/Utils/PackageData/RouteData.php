<?php
namespace CentralBooking\Utils\PackageData;

use CentralBooking\Data\Constants\TransportConstants;
use CentralBooking\Data\Location;
use CentralBooking\Data\Route;
use CentralBooking\Data\Time;
use CentralBooking\Data\Transport;

/**
 * @extends parent<Route>
 */
class RouteData implements PackageData
{
    /**
     * @param array<string> $schedules
     * @param array<int> $id_transports
     */
    public function __construct(
        public readonly int $id_origin = 0,
        public readonly int $id_destiny = 0,
        public readonly string $type = '',
        public readonly string $departure_time = '00:00:00',
        public readonly string $duration = '00:00:00',
        public readonly float $distance = 0.0,
        public readonly array $id_transports = [],
    ) {
    }

    public function get_data()
    {
        $transports = [];

        foreach ($this->id_transports as $id_transport) {
            $transport = new Transport;
            $transport->id = (int) $id_transport;
            $transports[] = $transport;
        }

        $route = new Route;

        $origin = new Location;
        $destiny = new Location;

        $origin->id = (int) $this->id_origin;
        $destiny->id = (int) $this->id_destiny;

        $route->setOrigin($origin);
        $route->setDestiny($destiny);
        $route->type = TransportConstants::from($this->type);
        $route->setDepartureTime(new Time($this->departure_time));
        $route->setTransports($transports);

        return $route;
    }
}
