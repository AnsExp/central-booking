<?php
namespace CentralTickets\REST;

use CentralTickets\REST\Controllers\LocationController;
use CentralTickets\Location;

/**
 * @extends parent<Location>
 */
class EndpointsLocations extends BaseEndpoints
{
    public function __construct()
    {

        parent::__construct(
            'locations',
            new LocationController
        );
    }
}
