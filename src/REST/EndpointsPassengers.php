<?php
namespace CentralBooking\REST;

use CentralTickets\Passenger;
use CentralTickets\REST\Controllers\PassengerController;

/**
 * @extends parent<Passenger>
 */
class EndpointsPassengers extends BaseEndpoints
{
    public function __construct()
    {
        parent::__construct(
            'passengers',
            new PassengerController()
        );
    }

    public function init_endpoints()
    {
        parent::init_endpoints();
        RegisterRoute::register(
            "{$this->root}/transfer/(?P<id>\d+)",
            'PUT',
            [$this->controller, 'put_transfer'],
            [
                'id' => [
                    'validate_callback' => fn($param) => is_numeric($param)
                ]
            ],
        );
    }
}
