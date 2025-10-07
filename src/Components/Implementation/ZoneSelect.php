<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Zone;
use CentralTickets\Persistence\ZoneRepository;

class ZoneSelect
{
    /**
     * @var array<Zone>
     */
    private array $zones;

    public function __construct(private string $name = 'zone')
    {
        $repository = new ZoneRepository;
        $this->zones = $repository->find_by(order_by: 'name') ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...', '');

        foreach ($this->zones as $zone) {
            $selectComponent->add_option($zone->name, $zone->id);
        }

        return $selectComponent;
    }
}
