<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Zone;

/**
 * @extends parent<Zone>
 */
class ZoneRepository extends BaseRepository
{
    private string $table_zones;

    public function __construct()
    {
        global $wpdb;
        $this->table_zones = "{$wpdb->prefix}git_zones";

        $select = "SELECT * FROM {$this->table_zones}";

        $orders = [
            'id'    => 'id',
            'name'  => 'name',
        ];

        $filters = [
            'id'    => 'id = %d',
            'name'  => 'name = %s',
        ];

        parent::__construct(
            $this->table_zones,
            $select,
            $orders,
            $filters,
            LogSourceConstants::ZONE
        );
    }

    protected function verify($entity)
    {
    }

    protected function process_save($zone)
    {
        global $wpdb;
        $data = ['name' => $zone->name];
        $formats = ['%s'];

        if ($this->exists($zone->id)) {
            $wpdb->update($this->table_zones, $data, ['id' => $zone->id], $formats, ['%d']);
        } else {
            $wpdb->insert($this->table_zones, $data, $formats);
            $zone->id = $wpdb->insert_id;
        }

        return $zone;
    }

    protected function result_to_entity(mixed $result): ?Zone
    {
        $zone = new Zone;
        $zone->id = $result->id;
        $zone->name = $result->name;
        return $zone;
    }
}