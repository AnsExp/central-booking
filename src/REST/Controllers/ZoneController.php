<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\REST\Controllers\BaseController;
use CentralTickets\Services\ArrayParser\ZoneArray;
use CentralTickets\Services\PackageData\ZoneData;
use CentralTickets\Services\ZoneService;
use CentralTickets\Zone;

/**
 * @extends parent<Zone>
 */
class ZoneController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            new ZoneService(),
            new ZoneArray()
        );
    }

    protected function parse_payload(array $payload)
    {
        return new ZoneData(
            isset($payload['name']) ? trim($payload['name']) : '',
            $payload['locations'] ?? [],
        );
    }

    /**
     * @param ZoneData $data
     * @return bool
     */
    protected function validate($data)
    {
        $this->issues = [];
        $pass = true;
        if ($data->name==='') {
            $this->issues[]='El nombre de la zona no puede estar en blanco';
            $pass = false;
        }
        return $pass;
    }
}
