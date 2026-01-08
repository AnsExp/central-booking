<?php
namespace CentralBooking\REST;

use CentralTickets\REST\Controllers\TransportController;
use CentralTickets\Transport;

/**
 * @extends parent<Transport>
 */
class EndpointsTransports extends BaseEndpoints
{
    public function __construct()
    {
        parent::__construct(
            'transports',
            new TransportController()
        );
    }

    public function init_endpoints()
    {
        // parent::init_endpoints();
        // RegisterRoute::register(
        //     "{$this->root}/maintenance",
        //     'POST',
        //     [$this->controller, 'post_maintenance']
        // );
        // RegisterRoute::register(
        //     "{$this->root}/availability",
        //     'POST',
        //     [$this->controller, 'post_check_availability']
        // );
        RegisterRoute::register(
            "{$this->root}/availability",
            'GET',
            [$this->controller, 'get_availability']
        );
    }
}
