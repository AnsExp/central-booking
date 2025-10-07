<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Persistence\RouteRepository;

class RouteSelect
{
    private RouteRepository $route_repository;

    public function __construct(private readonly string $name = 'route')
    {
        $this->route_repository = new RouteRepository;
    }

    public function create(bool $multiple = false)
    {
        $routes = $this->route_repository->find_by(order_by: 'name_origin');

        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...');

        foreach ($routes as $route) {
            $selectComponent->add_option(
                    "{$route->get_origin()->name} » {$route->get_destiny()->name} | {$route->departure_time}",
                    $route->id
            );
        }

        return $selectComponent;
    }
}