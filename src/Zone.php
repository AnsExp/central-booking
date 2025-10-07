<?php
namespace CentralTickets;

use CentralTickets\Persistence\LocationRepository;
class Zone
{
    /**
     * @var int
     */
    public int $id = 0;
    /**
     * @var string
     */
    public string $name = '';
    /**
     * @var array<Location>
     */
    private array $locations = [];

    /**
     * @return array<Location>
     */
    public function get_locations()
    {
        if (empty($this->locations)) {
            $repository = new LocationRepository;
            $this->locations = $repository->find_by_zone($this);
        }
        return $this->locations;
    }

    /**
     * @param array<Location> $locations
     */
    public function set_locations(array $locations)
    {
        $this->locations = $locations;
    }
}
