<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\REST\Controllers\BaseController;
use CentralTickets\Service;
use CentralTickets\Services\ArrayParser\ServiceArray;
use CentralTickets\Services\PackageData\ServiceData;
use CentralTickets\Services\ServiceService;

/**
 * @extends parent<Service>
 */
class ServiceController extends BaseController
{
    public function __construct()
    {
        $this->service = new ServiceService();
        parent::__construct(
            new ServiceService,
            new ServiceArray()
        );
    }

    protected function parse_payload(array $payload)
    {
        return new ServiceData(
            price: $payload['price'] ?? 0,
            name: isset($payload['name']) ? trim($payload['name']) : '',
            icon: isset($payload['icon']) ? trim($payload['icon']) : '',
            transports: $payload['transports'] ?? [],
        );
    }

    /**
     * @param ServiceData $service_data
     * @return bool
     */
    protected function validate($service_data)
    {
        $pass = true;
        $this->issues = [];

        if ($service_data->icon === '') {
            $this->issues[] = ['No se a asignado un ícono.'];
            $pass = false;
        }
        if ($service_data->name === '') {
            $this->issues[] = ['El nombre no puede esta vacío.'];
            $pass = false;
        }

        return $pass;
    }
}
