<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Location;
use CentralTickets\Zone;

/**
 * @extends parent<Location>
 */
class LocationRepository extends BaseRepository
{
    private string $table_zones;
    private string $table_locations;
    private ZoneRepository $zone_repository;

    public function __construct()
    {
        global $wpdb;
        $this->zone_repository = new ZoneRepository;
        $this->table_zones = "{$wpdb->prefix}git_zones";
        $this->table_locations = "{$wpdb->prefix}git_locations";

        $select = "SELECT l.* FROM {$this->table_locations} l 
        LEFT JOIN {$this->table_zones} z ON z.id = l.id_zone";

        $orders = [
            'id' => 'l.id',
            'name' => 'l.name',
            'id_zone' => 'z.id',
            'name_zone' => 'z.name',
        ];

        $filters = [
            'id' => 'l.id = %d',
            'name' => 'l.name = %s',
            'id_zone' => 'z.id = %d',
            'name_zone' => 'z.name = %s',
        ];

        parent::__construct(
            $this->table_locations,
            $select,
            $orders,
            $filters,
            LogSourceConstants::LOCATION
        );
    }

    protected function build_count_query(): string
    {
        return "SELECT COUNT(DISTINCT l.id)
        FROM {$this->table_locations} l 
        LEFT JOIN {$this->table_zones} z ON z.id = l.id_zone";
    }

    protected function verify($location)
    {
    }

    protected function process_save($location)
    {
        global $wpdb;
        $data = [
            'name' => $location->name,
            'id_zone' => $location->get_zone()->id,
        ];
        $formats = ['%s', '%d'];

        $json_status = json_encode($location);

        if ($this->exists($location->id)) {
            $wpdb->update(
                $this->table_locations,
                $data,
                ['id' => $location->id],
                $formats,
                ['%d']
            );
        } else {
            $wpdb->insert(
                $this->table_locations,
                $data,
                $formats
            );
            $location->id = $wpdb->insert_id;
        }

        return $location;
    }

    public function find_by_zone(Zone $zone)
    {
        return $this->find_by(['id_zone' => $zone->id]);
    }

    protected function result_to_entity(mixed $result)
    {
        $location = new Location;
        $location->id = $result->id;
        $location->name = $result->name;
        $location->set_zone($this->get_zone($result->id_zone) ?? new Zone);
        return $location;
    }

    private function get_zone(int $id_zone)
    {
        $match = $this->zone_repository->find_by(['id' => $id_zone]);
        if (empty($match)) {
            return null;
        }
        return $match[0];
    }
}
