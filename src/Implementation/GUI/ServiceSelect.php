<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Services\ServiceService;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class ServiceSelect
{
    private array $services;

    public function __construct(private readonly string $name = 'service')
    {
        $repository = new ServiceService();
        $this->services = $repository->find(orderBy: 'name')?->getItems() ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...');

        foreach ($this->services as $service) {
            $selectComponent->addOption(
                $service->name,
                $service->id
            );
        }

        return $selectComponent;
    }
}