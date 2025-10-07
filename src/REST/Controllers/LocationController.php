<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\Location;
use CentralTickets\Services\ArrayParser\LocationArray;
use CentralTickets\Services\LocationService;
use CentralTickets\Services\PackageData\LocationData;

/**
 * @extends parent<Location>
 */
class LocationController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            new LocationService,
            new LocationArray()
        );
    }

    protected function parse_payload(array $payload)
    {
        return new LocationData(
            isset($payload['name']) ? trim($payload['name']) : '',
            $payload['zone'] ?? -1
        );
    }

    /**
     * @param LocationData $data
     * @return bool
     */
    protected function validate($data)
    {
        // echo json_encode($data);
        $pass = true;
        $this->issues = [];
        if ($data->name === '') {
            $this->issues[] = 'El nombre de la ubicación está en blanco.';
            $pass = false;
        }
        if ($data->id_zone < 0) {
            $this->issues[] = 'El ID de la zona no es válido.';
            $pass = false;
        }
        return $pass;
    }
}
