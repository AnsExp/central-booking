<?php
namespace CentralTickets;

class Location
{
    public int $id = 0;
    public string $name = '';
    private Zone $zone;

    public function get_zone()
    {
        if (!isset($this->zone)) {
            $result = git_get_query_persistence()
                ->get_zone_repository()
                ->find_first(['location_id' => $this->id]);
            $this->zone = $result ?? new Zone();
        }
        return $this->zone;
    }

    public function set_zone(Zone $zone)
    {
        $this->zone = $zone;
    }
}
