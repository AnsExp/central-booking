<?php
namespace CentralBooking\Utils\PackageData;

use CentralBooking\Data\Constants\TransportConstants;
use CentralBooking\Data\Operator;
use CentralBooking\Data\Route;
use CentralBooking\Data\Service;
use CentralBooking\Data\Transport;

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
    ) {
    }

    public function get_data()
    {
        $operator = new Operator();
        $transport = new Transport();
        $user = get_user($this->operator);
        $operator->setUser($user);
        $transport->type = TransportConstants::from($this->type);
        $transport->code = $this->code;
        $transport->nicename = $this->nicename;
        $transport->setWorkingDays($this->working_days);
        $transport->setCrew($this->crew);
        $transport->setCapacity($this->capacity);
        $transport->setAlias($this->alias);
        $transport->setOperator($operator);

        $transport->setServices(
            array_map(
                function (int $id_service) {
                    $service = new Service();
                    $service->id = $id_service;
                    return $service;
                },
                $this->services
            )
        );

        $transport->setRoutes(
            array_map(
                function (int $id_route) {
                    $service = new Route();
                    $service->id = $id_route;
                    return $service;
                },
                $this->routes
            )
        );

        return $transport;
    }
}
