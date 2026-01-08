<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Services\LocationService;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class LocationSelect
{
    private array $locations;

    public function __construct(private string $name = 'location')
    {
        $repository = new LocationService();
        $this->locations = $repository->find(orderBy: 'name')?->getItems() ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->locations as $service) {
            $selectComponent->addOption(
                $service->name,
                $service->id
            );
        }

        return $selectComponent;
    }
}