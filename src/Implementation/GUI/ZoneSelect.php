<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Services\ZoneService;
use CentralBooking\Data\Zone;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class ZoneSelect
{
    /**
     * @var array<Zone>
     */
    private array $zones;

    public function __construct(private string $name = 'zone')
    {
        $this->zones = git_zones(['order_by' => 'name']) ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->zones as $zone) {
            $selectComponent->addOption($zone->name, $zone->id);
        }

        return $selectComponent;
    }
}
