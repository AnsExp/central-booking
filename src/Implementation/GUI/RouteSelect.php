<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class RouteSelect
{
    public function __construct(private readonly string $name = 'route')
    {
    }

    public function create(bool $multiple = false)
    {
        $routes = git_routes(['order_by' => 'name_origin']);

        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...');

        foreach ($routes as $route) {
            $selectComponent->addOption(
                "{$route->getOrigin()->name} » {$route->getDestiny()->name} | {$route->getDepartureTime()->format()}",
                $route->id
            );
        }

        return $selectComponent;
    }
}