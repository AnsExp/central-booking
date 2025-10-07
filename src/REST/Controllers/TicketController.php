<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\REST\Controllers\BaseController;
use CentralTickets\REST\Controllers\PassengerController;
use CentralTickets\Services\ArrayParser\TicketArray;
use CentralTickets\Services\PackageData\TicketData;
use CentralTickets\Services\TicketService;
use CentralTickets\Ticket;

/**
 * @extends parent<Ticket>
 */
class TicketController extends BaseController
{
    private PassengerController $passenger_controller;
    public function __construct()
    {
        parent::__construct(
            new TicketService(),
            new TicketArray()
        );
        $this->passenger_controller = new PassengerController();
    }

    protected function parse_payload(array $payload)
    {
        return new TicketData(
            $payload['order'] ?? -1,
            $payload['coupon'] ?? -1,
            $payload['flexible'] ?? false,
            $payload['total_amount'] ?? 0,
            isset($payload['status']) ? trim($payload['status']) : '',
            isset($payload['passengers']) && is_array($payload['passengers']) ?
            array_map(
                [$this->passenger_controller, 'parse_payload'],
                $payload['passengers']
            ) : []
        );
    }

    /**
     * @param TicketData $data
     * @return bool
     */
    protected function validate($data)
    {
        $this->issues = [];
        $pass = true;

        foreach ($data->passengers as $index => $passenger) {
            if (!$this->passenger_controller->validate($passenger)) {
                $this->issues[] = [
                    'passenger_index' => $index + 1,
                    'issues' => $this->passenger_controller->issues
                ];
                $pass = false;
            }
        }

        if ($data->id_order < 0) {
            $this->issues[] = 'El pedido asignado al ticket no es correcto.';
        }

        return $pass;
    }
}
