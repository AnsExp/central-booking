<?php
namespace CentralTickets\Services\ArrayParser;

use CentralTickets\Route;
use CentralTickets\Transport;

/**
 * @extends parent<Route>
 */
class RouteArray implements ArrayParser
{
    public function get_array($route)
    {
        return [
            'id' => $route->id,
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
            'duration_trip' => $route->duration_trip,
            'departure_time' => $route->departure_time,
            'distance_km' => $route->distance_km,
            'transports' => array_map(fn(Transport $transport) => [
                'id' => $transport->id,
                'nicename' => $transport->nicename,
                'code' => $transport->code,
                'type' => $transport->type
            ], $route->get_transports()),
        ];
    }
}
