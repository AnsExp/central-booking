<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Operator;
use CentralTickets\Transport;
use CentralTickets\MetaManager;
use Exception;

/**
 * @extends parent<Transport>
 */
class TransportRepository extends BaseRepository
{
    private string $table_zones;
    private string $table_routes;
    private string $table_locations;
    private string $table_transports;
    private string $table_passengers;
    private string $table_meta;
    private string $table_routes_transports;
    private string $table_transports_services;
    private RouteRepository $route_repository;
    private ServiceRepository $service_repository;

    public function __construct()
    {
        global $wpdb;
        $this->route_repository = new RouteRepository;
        $this->service_repository = new ServiceRepository;

        $this->table_meta = "{$wpdb->prefix}git_meta";
        $this->table_zones = "{$wpdb->prefix}git_zones";
        $this->table_routes = "{$wpdb->prefix}git_routes";
        $this->table_locations = "{$wpdb->prefix}git_locations";
        $this->table_transports = "{$wpdb->prefix}git_transports";
        $this->table_passengers = "{$wpdb->prefix}git_passengers";
        $this->table_routes_transports = "{$wpdb->prefix}git_routes_transports";
        $this->table_transports_services = "{$wpdb->prefix}git_transports_services";

        $select = "SELECT t.*
            FROM {$this->table_transports} t
            LEFT JOIN {$wpdb->prefix}users u ON u.id = t.id_operator
            LEFT JOIN {$this->table_passengers} p ON p.id_transport = t.id
            LEFT JOIN {$this->table_meta} tm ON (tm.meta_id = t.id AND tm.meta_type = '" . MetaManager::TRANSPORT . "')
            LEFT JOIN {$this->table_routes_transports} rt ON rt.id_transport = t.id
            LEFT JOIN {$this->table_routes} r ON r.id = rt.id_route
            LEFT JOIN {$this->table_locations} lo ON lo.id = r.id_origin
            LEFT JOIN {$this->table_locations} ld ON ld.id = r.id_destiny
            LEFT JOIN {$this->table_zones} zo ON zo.id = lo.id_zone
            LEFT JOIN {$this->table_zones} zd ON zd.id = ld.id_zone
            LEFT JOIN {$this->table_transports_services} ts ON ts.id_transport = t.id";

        $filters_allowed = [
            'id' => 't.id = %d',
            'type' => 't.type = %s',
            'code' => 't.code = %s',
            'flexible' => "tm.meta_key = 'flexible' AND tm.meta_value = %s",
            'nicename' => 't.nicename = %s',
            'id_zone_origin' => 'zo.id = %d',
            'id_zone_destiny' => 'zd.id = %d',
            'name_zone_origin' => 'zo.name = %s',
            'name_zone_destiny' => 'zd.name = %s',
            'id_origin' => 'lo.id = %d',
            'id_destiny' => 'ld.id = %d',
            'name_origin' => 'lo.name = %s',
            'working_day' => "tm.meta_key = 'working_days' AND tm.meta_value LIKE %s",
            'name_destiny' => 'ld.name = %s',
            'id_service' => 'ts.id_service = %d',
            'id_route' => 'rt.id_route = %d',
            'id_operator' => 't.id_operator = %d',
            'id_passenger' => 'p.id = %d',
            'username_operator' => 'u.user_login = %s',
        ];

        $orders_allowed = [
            'id' => 't.id',
            'code' => 't.code',
            'nicename' => 't.nicename',
            'id_operator' => 't.id_operator',
            'type' => 't.type',
        ];

        parent::__construct(
            $this->table_transports,
            $select,
            $orders_allowed,
            $filters_allowed,
            LogSourceConstants::TRANSPORT,
        );
    }

    protected function build_count_query(): string
    {
        global $wpdb;
        return "SELECT COUNT(DISTINCT t.id)
        FROM {$this->table_transports} t
            LEFT JOIN {$wpdb->prefix}users o ON o.id = t.id_operator
            LEFT JOIN {$this->table_passengers} p ON p.id_transport = t.id
            LEFT JOIN {$this->table_meta} tm ON (tm.meta_id = t.id AND tm.meta_type = '" . MetaManager::TRANSPORT . "')
            LEFT JOIN {$this->table_routes_transports} rt ON rt.id_transport = t.id
            LEFT JOIN {$this->table_routes} r ON r.id = rt.id_route
            LEFT JOIN {$this->table_locations} lo ON lo.id = r.id_origin
            LEFT JOIN {$this->table_locations} ld ON ld.id = r.id_destiny
            LEFT JOIN {$this->table_zones} zo ON zo.id = lo.id_zone
            LEFT JOIN {$this->table_zones} zd ON zd.id = ld.id_zone
            LEFT JOIN {$this->table_transports_services} ts ON ts.id_transport = t.id";
    }

    protected function create_filter(array $args)
    {
        if (isset($args['working_day'])) {
            $args['working_day'] = "<percent_placeholder>{$args['working_day']}<percent_placeholder>";
        }
        if (isset($args['flexible'])) {
            $args['flexible'] = is_bool($args['flexible']) ? var_export($args['flexible'], true) : $args['flexible'];
        }
        $base = parent::create_filter($args);
        $base = str_replace('<percent_placeholder>', '%', $base);
        return $base;
    }

    protected function create_order(string $order_by, string $order)
    {
        $order_base = parent::create_order($order_by, $order);
        return "GROUP BY t.id $order_base";
    }

    /**
     * @param Transport $transport
     */
    protected function process_save($transport)
    {
        global $wpdb;
        $data = [
            'id_operator' => $transport->get_operator()->ID,
            'type' => $transport->type,
            'nicename' => $transport->nicename,
            'code' => $transport->code,
        ];
        $formats = ['%d', '%s', '%s', '%s'];

        if ($this->exists($transport->id)) {
            $wpdb->update(
                $this->table_transports,
                $data,
                ['id' => $transport->id],
                $formats,
                ['%d']
            );

            $wpdb->delete(
                $this->table_transports_services,
                ['id_transport' => $transport->id],
                ['%d']
            );

            $wpdb->delete(
                $this->table_routes_transports,
                ['id_transport' => $transport->id],
                ['%d']
            );

        } else {
            $wpdb->insert(
                $this->table_transports,
                $data,
                $formats
            );
            $transport->id = $wpdb->insert_id;
        }

        foreach ($transport->get_services() as $service) {
            $wpdb->insert(
                $this->table_transports_services,
                [
                    'id_transport' => $transport->id,
                    'id_service' => $service->id,
                ],
                ['%d', '%d']
            );
        }

        foreach ($transport->get_routes() as $route) {
            $wpdb->insert(
                $this->table_routes_transports,
                [
                    'id_transport' => $transport->id,
                    'id_route' => $route->id,
                ],
                ['%d', '%d']
            );
        }

        MetaManager::set_metadata(
            MetaManager::TRANSPORT,
            $transport->id,
            $transport->metadata
        );

        return $transport;
    }

    /**
     * @param Transport $transport
     * @return void
     */
    protected function verify($transport)
    {
        $operator = $transport->get_operator();
        if (!isset($operator)) {
            throw new Exception("Error al registrar un transporte: operador no válido.");
        }
        $operator = get_user($operator->ID);
        if (!$operator) {
            throw new Exception("El operador no existe.");
        // } elseif (!user_can($transport->get_operator()->ID, UserConstants::OPERATOR)) {
        //     throw new Exception("El usuario no tiene le rol de operador.");
        }

        $routes = [];
        $services = [];
        $operator = new Operator($operator->ID);

        foreach ($transport->get_routes() as $route) {
            $route_found = $this->route_repository->find_first(['id' => $route->id]);
            if ($route_found !== null) {
                $routes[] = $route_found;
            } else {
                throw new Exception("La ruta con el ID $route->id asignado al transporte no existe.");
            }
        }

        foreach ($transport->get_services() as $service) {
            $service_found = $this->service_repository->find_first(['id' => $service->id]);
            if ($service_found !== null) {
                $services[] = $service_found;
            } else {
                throw new Exception("El servicio con el ID $route->id asignado al transporte no existe.");
            }
        }

        $transport->set_routes($routes);
        $transport->set_operator($operator);
        $transport->set_services($services);
    }

    protected function result_to_entity(mixed $result)
    {
        $transport = new Transport;
        $transport->id = $result->id;
        $transport->code = $result->code;
        $transport->type = $result->type;
        $transport->nicename = $result->nicename;
        $transport->set_operator(new Operator($result->id_operator));
        $transport->metadata = MetaManager::get_metadata(MetaManager::TRANSPORT, $transport->id);
        return $transport;
    }
}