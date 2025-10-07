<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Persistence\ServiceRepository;

class ServiceSelect
{
    private array $services;

    public function __construct(private readonly string $name = 'service')
    {
        $repository = new ServiceRepository;
        $this->services = $repository->find_by(order_by: 'name') ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...');

        foreach ($this->services as $service) {
            $selectComponent->add_option(
                $service->name,
                $service->id
            );
        }

        return $selectComponent;
    }
}