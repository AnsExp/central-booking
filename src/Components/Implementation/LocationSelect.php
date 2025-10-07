<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Persistence\LocationRepository;

class LocationSelect
{
    private array $locations;

    public function __construct(private string $name = 'location')
    {
        $repository = new LocationRepository;
        $this->locations = $repository->find_by(order_by: 'name') ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...', '');

        foreach ($this->locations as $service) {
            $selectComponent->add_option(
                $service->name,
                $service->id
            );
        }

        return $selectComponent;
    }
}