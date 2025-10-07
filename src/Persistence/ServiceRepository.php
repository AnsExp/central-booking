<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Service;

/**
 * @extends parent<Service>
 */
class ServiceRepository extends BaseRepository
{
    private string $table_services;
    private string $table_transports_services;

    public function __construct()
    {
        global $wpdb;
        $this->table_services = "{$wpdb->prefix}git_services";
        $this->table_transports_services = "{$wpdb->prefix}git_transports_services";

        $select = "SELECT s.* FROM {$this->table_services} s 
                LEFT JOIN {$this->table_transports_services} ts ON ts.id_service = s.id";

        $filters_allowed = [
            'id' => 's.id = %d',
            'name' => 's.name = %s',
            'id_transport' => 'ts.id_transport = %d',
            'price' => 's.price = %d',
        ];

        $orders_allowed = [
            'id' => 's.id',
            'name' => 's.name',
            'price' => 's.price',
        ];

        parent::__construct(
            $this->table_services,
            $select,
            $orders_allowed,
            $filters_allowed,
            LogSourceConstants::SERVICE,
        );
    }

    protected function build_count_query(): string
    {
        return "SELECT COUNT(DISTINCT s.id)
        FROM {$this->table_services} s 
        LEFT JOIN {$this->table_transports_services} ts ON ts.id_service = s.id";
    }

    protected function create_order(string $order_by, string $order)
    {
        $base_order = parent::create_order($order_by, $order);
        return "GROUP BY s.id $base_order";
    }

    protected function verify($service)
    {
    }

    protected function process_save($service)
    {
        global $wpdb;

        $data = [
            'price' => $service->price,
            'name' => $service->name,
            'icon' => $service->icon
        ];
        $format = ['%d', '%s', '%s'];

        if ($this->exists($service->id)) {
            $wpdb->update(
                $this->table_services,
                $data,
                ['id' => $service->id],
                $format,
                ['%d']
            );
            $wpdb->delete(
                $this->table_transports_services,
                ['id_service' => $service->id],
                ['%d']
            );
        } else {
            $wpdb->insert(
                $this->table_services,
                $data,
                $format
            );
            $service->id = $wpdb->insert_id;
        }

        foreach ($service->get_transports() as $transport) {
            $wpdb->insert(
                $this->table_transports_services,
                [
                    'id_service' => $service->id,
                    'id_transport' => $transport->id
                ],
                ['%d', '%d']
            );
        }

        return $service;
    }

    protected function result_to_entity(mixed $result)
    {
        $service = new Service;
        $service->id = $result->id;
        $service->name = $result->name;
        $service->icon = $result->icon;
        $service->price = $result->price;
        return $service;
    }
}