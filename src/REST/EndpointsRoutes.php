<?php
namespace CentralBooking\REST;

use CentralTickets\REST\Controllers\RouteController;
use CentralTickets\Route;

/**
 * @extends parent<Route>
 */
class EndpointsRoutes extends BaseEndpoints
{
    public function __construct()
    {
        parent::__construct(
            'routes',
            new RouteController()
        );
    }
}
