<?php
namespace CentralTickets\Services\PackageData;

use CentralTickets\Route;
use CentralTickets\Transport;
use CentralTickets\Passenger;

/**
 * @extends parent<Passenger>
 */
class PassengerData implements PackageData
{
    public function __construct(
        public readonly string $name = '',
        public readonly bool $served = false,
        public readonly string $nationality = '',
        public readonly string $birthday = '',
        public readonly string $date_trip = '',
        public readonly string $type_document = '',
        public readonly string $data_document = '',
        public readonly string $type = '',
        public readonly int $id_route = 0,
        public readonly int $id_transport = 0,
    ) { }

    /**
     * @return Passenger
     */
    public function get_data()
    {
        $route = new Route;
        $passenger = new Passenger;
        $transport = new Transport;

        $passenger->name = $this->name;
        $passenger->type = $this->type;
        $passenger->served = $this->served;
        $passenger->birthday = $this->birthday;
        $passenger->date_trip = $this->date_trip;
        $passenger->nationality = $this->nationality;
        $passenger->data_document = $this->data_document;
        $passenger->type_document = $this->type_document;

        $route->id = $this->id_route;
        
        $transport->id = $this->id_transport;

        $passenger->set_route($route);
        $passenger->set_transport($transport);

        return $passenger;
    }
}
