<?php
namespace CentralBooking\REST;

use CentralTickets\REST\Controllers\ZoneController;
use CentralTickets\Zone;

/**
 * @extends parent<Zone>
 */
class EndpointsZone extends BaseEndpoints
{
    public function __construct()
    {
        parent::__construct(
            'zones',
            new ZoneController()
        );
    }
}
