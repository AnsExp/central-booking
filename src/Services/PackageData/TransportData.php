<?php
namespace CentralTickets\Services\PackageData;

use CentralTickets\Operator;
use CentralTickets\Route;
use CentralTickets\Service;
use CentralTickets\Transport;

class TransportData implements PackageData
{
    /**
     * @param array<int> $routes
     * @param array<int> $services
     */
    public function __construct(
        public readonly string $type = '',
        public readonly string $nicename = '',
        public readonly string $code = '',
        public readonly int $capacity = 0,
        public readonly int $operator = 0,
        public readonly bool $flexible = false,
        public readonly array $crew = [],
        public readonly array $working_days = [],
        public readonly array $services = [],
        public readonly array $routes = [],
        public readonly array $alias = [],
    ) { }

    public function get_data()
    {
        $operator = new Operator($this->operator);
        $transport = new Transport;
        $transport->type = $this->type;
        $transport->code = $this->code;
        $transport->nicename = $this->nicename;
        $transport->set_meta('flexible', $this->flexible);
        $transport->set_meta('working_days', $this->working_days);
        $transport->set_meta('crew', $this->crew);
        $transport->set_meta('capacity', $this->capacity);
        $transport->set_meta('alias', $this->alias);
        $transport->set_operator($operator);

        $transport->set_services(
            array_map(
                function (int $id_service) {
                    $service = new Service;
                    $service->id = $id_service;
                    return $service;
                },
                $this->services
            )
        );

        $transport->set_routes(
            array_map(
                function (int $id_route) {
                    $service = new Route;
                    $service->id = $id_route;
                    return $service;
                },
                $this->routes
            )
        );

        return $transport;
    }
}
