<?php
namespace CentralTickets\Services\ArrayParser;

use CentralTickets\Service;
use CentralTickets\Transport;

/**
 * @extends parent<Service>
 */
class ServiceArray implements ArrayParser
{
    public function get_array($service)
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'price' => $service->price,
            'icon' => $service->icon,
            'transports' => array_map(fn(Transport $transport) => [
                'id' => $transport->id,
                'nicename' => $transport->nicename,
                'capacity' => $transport->get_meta('capacity'),
                'crew' => $transport->get_meta('crew'),
                'code' => $transport->code,
                'operator' => [
                    'id' => $transport->get_operator()->ID,
                    'name' => $transport->get_operator()->user_nicename,
                ],
                'type' => $transport->type,
            ], $service->get_transports()),
        ];
    }
}
