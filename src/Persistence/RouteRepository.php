<?php
namespace CentralTickets\Persistence;

use CentralTickets\Constants\LogSourceConstants;
use Exception;
use CentralTickets\Route;

/**
 * @extends parent<Route>
 */
class RouteRepository extends BaseRepository
{
    private string $table_zones;
    private string $table_routes;
    private string $table_locations;
    private string $table_passengers;
    private string $table_routes_transports;
    private LocationRepository $location_repository;
    private TransportRepository $transport_repository;

    public function __construct()
    {
        $this->location_repository = new LocationRepository;

        global $wpdb;

        $this->table_zones = "{$wpdb->prefix}git_zones";
        $this->table_routes = "{$wpdb->prefix}git_routes";
        $this->table_locations = "{$wpdb->prefix}git_locations";
        $this->table_passengers = "{$wpdb->prefix}git_passengers";
        $this->table_routes_transports = "{$wpdb->prefix}git_routes_transports";

        $query_select = "SELECT r.* FROM {$this->table_routes} r
            LEFT JOIN {$this->table_locations} lo ON lo.id = r.id_origin
            LEFT JOIN {$this->table_locations} ld ON ld.id = r.id_destiny
            LEFT JOIN {$this->table_zones} zo ON zo.id = lo.id_zone
            LEFT JOIN {$this->table_zones} zd ON zd.id = ld.id_zone
            LEFT JOIN {$this->table_passengers} p ON p.id_route = r.id
            LEFT JOIN {$this->table_routes_transports} rt ON rt.id_route = r.id";

        $filters_allowed = [
            'id' => 'r.id = %d',
            'id_zone_origin' => 'zo.id = %d',
            'id_zone_destiny' => 'zd.id = %d',
            'name_zone_origin' => 'zo.name = %s',
            'name_zone_destiny' => 'zd.name = %s',
            'name_origin' => 'lo.name = %s',
            'name_destiny' => 'ld.name = %s',
            'id_passenger' => 'p.id = %d',
            'id_origin' => 'r.id_origin = %d',
            'id_destiny' => 'r.id_destiny = %d',
            'id_transport' => 'rt.id_transport = %d',
            'type' => 'r.type = %s',
            'departure_time' => "r.departure_time = %s",
            'duration_trip' => "r.duration_trip = %s",
            'distance_km' => "r.distance_km = %.2f",
        ];

        $orders_allowed = [
            'id' => 'r.id',
            'type' => 'r.type',
            'distance' => 'r.distance',
            'name_origin' => 'lo.name',
            'name_destiny' => 'ld.name',
            'distance_km' => "r.distance_km",
            'duration_trip' => "r.duration_trip",
            'departure_time' => "r.departure_time",
        ];

        parent::__construct(
            $this->table_routes,
            $query_select,
            $orders_allowed,
            $filters_allowed,
            LogSourceConstants::ROUTE,
        );
    }

    protected function build_count_query(): string
    {
        global $wpdb;
        return "SELECT COUNT(DISTINCT r.id)
        FROM {$this->table_routes} r
            LEFT JOIN {$this->table_locations} lo ON lo.id = r.id_origin
            LEFT JOIN {$this->table_locations} ld ON ld.id = r.id_destiny
            LEFT JOIN {$this->table_zones} zo ON zo.id = lo.id_zone
            LEFT JOIN {$this->table_zones} zd ON zd.id = ld.id_zone
            LEFT JOIN {$this->table_passengers} p ON p.id_route = r.id
            LEFT JOIN {$this->table_routes_transports} rt ON rt.id_route = r.id";
    }

    protected function create_order(string $order_by, string $order)
    {
        $order_base = parent::create_order($order_by, $order);
        return " GROUP BY r.id $order_base";
    }

    /**
     * @param Route $route
     * @return void
     */
    protected function verify($route)
    {
        $this->transport_repository = new TransportRepository;
        $origin = $route->get_origin();
        $destiny = $route->get_destiny();
        $transports = $route->get_transports();

        if (!isset($origin)) {
            throw new Exception('Se intentó guardar una ruta sin un origen asignado.');
        }
        $origin = $this->location_repository->find_first(['id' => $route->get_origin()->id]);
        if ($origin === null) {
            throw new Exception('Se intentó guardar una ruta con un origen inexistente.');
        }

        if (!isset($destiny)) {
            throw new Exception('Se intentó guardar una ruta sin un destino asignado.');
        }
        $destiny = $this->location_repository->find_first(['id' => $route->get_destiny()->id]);
        if ($destiny === null) {
            throw new Exception('Se intentó guardar una ruta con un destino inexistente.');
        }

        $transports_found = [];

        foreach ($transports as $transport) {
            $transport_found = $this->transport_repository->find($transport->id);
            if ($transport_found === null) {
                throw new Exception('Se intentó asignar un transporte inexistente al registro de una ruta.');
            }
            $transports_found[] = $transport_found;
        }

        $route->set_origin($origin);
        $route->set_destiny($destiny);
        $route->set_transports($transports_found);
    }

    /**
     * @param Route $route
     */
    protected function process_save($route)
    {
        global $wpdb;
        $data = [
            'type' => $route->type,
            'id_origin' => $route->get_origin()->id,
            'id_destiny' => $route->get_destiny()->id,
            'departure_time' => $route->departure_time,
            'duration_trip' => $route->duration_trip,
            'distance_km' => $route->distance_km,
        ];
        $formats = ['%s', '%d', '%d', '%s', '%s', '%.2f'];

        if ($this->exists($route->id)) {
            $wpdb->update(
                $this->table_routes,
                $data,
                ['id' => $route->id],
                $formats,
                ['%d']
            );
            $wpdb->delete(
                $this->table_routes_transports,
                ['id_route' => $route->id],
                ['%d']
            );
        } else {
            $wpdb->insert(
                $this->table_routes,
                $data,
                $formats
            );
            $route->id = $wpdb->insert_id;
        }

        foreach ($route->get_transports() as $transport) {
            $wpdb->insert(
                $this->table_routes_transports,
                [
                    'id_route' => $route->id,
                    'id_transport' => $transport->id
                ],
                ['%d', '%d']
            );
        }

        return $route;
    }

    public function get_origin_by_route(Route $route)
    {
        return $this->get_location_by_route($route, 'id_origin');
    }

    public function get_destiny_by_route(Route $route)
    {
        return $this->get_location_by_route($route, 'id_destiny');
    }

    private function get_location_by_route(Route $route, string $field)
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT {$field} FROM {$this->table_routes} WHERE id = %d",
            $route->id
        ));

        if ($row === null) {
            return null;
        }

        return $this->location_repository->find($row->{$field});
    }

    protected function result_to_entity(mixed $result)
    {
        $route = new Route;
        $route->id = $result->id;
        $route->type = $result->type;
        $route->distance_km = $result->distance_km;
        $route->duration_trip = $result->duration_trip;
        $route->departure_time = $result->departure_time;
        return $route;
    }

    private function get_location(int $id_location)
    {
        return $this->location_repository->find($id_location);
    }
}
