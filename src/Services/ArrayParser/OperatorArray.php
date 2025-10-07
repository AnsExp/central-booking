<?php
namespace CentralTickets\Services\ArrayParser;

use CentralTickets\Route;
use CentralTickets\Service;
use CentralTickets\Transport;
use WP_Post;

class OperatorArray implements ArrayParser
{
    public function get_array($operator)
    {
        return [
            'id' => $operator->ID,
            'firstname' => $operator->first_name,
            'lastname' => $operator->last_name,
            'phone' => $operator->phone,
            'username' => $operator->user_login,
            'coupons' => array_map(fn(WP_Post $coupon) => [
                'id' => $coupon->ID,
                'code' => $coupon->post_title,
            ], $operator->get_coupons()),
            'business_plan' => $operator->get_business_plan(),
            'transports' => array_map(fn(Transport $transport) => [
                'id' => $transport->id,
                'code' => $transport->code,
                'capacity' => $transport->get_meta('capacity'),
                'captain' => $transport->get_meta('captain'),
                'nicename' => $transport->nicename,
                'type' => $transport->type,
                'routes' => array_map(fn(Route $route) => [
                    'id' => $route->id,
                    'time' => $route->departure_time,
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
            ], $operator->get_transports()),
        ];
    }
}
