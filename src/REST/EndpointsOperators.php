<?php
namespace CentralTickets\REST;

use CentralTickets\Operator;
use CentralTickets\REST\Controllers\OperatorController;

/**
 * @extends parent<Operator>
 */
class EndpointsOperators extends BaseEndpoints
{
    public function __construct()
    {
        parent::__construct(
            'operators',
            new OperatorController()
        );
    }
}
