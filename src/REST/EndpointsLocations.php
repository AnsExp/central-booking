<?php
namespace CentralBooking\REST;

use CentralBooking\Data\Location;
use CentralBooking\REST\Controllers\LocationController;

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
