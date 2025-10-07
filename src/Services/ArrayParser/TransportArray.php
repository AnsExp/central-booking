<?php
namespace CentralTickets\Services\ArrayParser;

use CentralTickets\Route;
use CentralTickets\Service;
use CentralTickets\Transport;

/**
 * @extends parent<Transport>
 */
final class TransportArray implements ArrayParser
{
    /**
     * @param Transport $transport
     */
    public function get_array($transport)
    {
        return [
            'id' => $transport->id,
            'capacity' => $transport->get_meta('capacity'),
            'crew' => $transport->get_meta('crew') ?? [],
            'nicename' => $transport->nicename,
            'code' => $transport->code,
            'type' => [
                'slug' => $transport->type,
                'display' => git_get_text_by_type($transport->type)
            ],
            'operator' => [
                'id' => $transport->get_operator()->ID,
                'name' => $transport->get_operator()->user_nicename,
            ],
            'available' => $transport->is_available(),
            'maintenance_dates' => $transport->get_maintenance_dates(),
            'working_days' => $transport->get_working_days(),
            'flexible' => $transport->get_meta('flexible') ?? false,
            'alias' => $transport->get_meta('alias') ?? [],
            'photo' => $transport->get_meta('photo_url') ?? 'https://imageslot.com/v1/1000x200?fg=ffffff&shadow=23272f&filetype=png',
            'custom_field' => $transport->get_meta('custom_field') ?? [
                'topic' => 'text',
                'content' => '',
            ],
            'routes' => array_map(fn(Route $route) => [
                'id' => $route->id,
                'distance_km' => $route->distance_km,
                'duration_trip' => $route->duration_trip,
                'departure_time' => $route->departure_time,
                'type' => $route->type,
                'origin' => [
                    'id' => $route->get_origin()->id,
                    'name' => $route->get_origin()->name,
                    'zone' => [
                        'id' => $route->get_origin()->get_zone()->id,
                        'name' => $route->get_origin()->get_zone()->name,
                    ]
                ],
                'destiny' => [
                    'id' => $route->get_destiny()->id,
                    'name' => $route->get_destiny()->name,
                    'zone' => [
                        'id' => $route->get_destiny()->get_zone()->id,
                        'name' => $route->get_destiny()->get_zone()->name,
                    ]
                ],
            ], $transport->get_routes()),
            'services' => array_map(fn(Service $service) => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'icon' => $service->icon,
            ], $transport->get_services()),
        ];
    }
}
