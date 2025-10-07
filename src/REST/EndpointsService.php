<?php
namespace CentralTickets\REST;

use CentralTickets\REST\Controllers\ServiceController;
use CentralTickets\Service;

/**
 * @extends parent<Service>
 */
class EndpointsService extends BaseEndpoints
{
    public function __construct()
    {
        parent::__construct(
            'services',
            new ServiceController()
        );
    }
}