<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Route;
use CentralTickets\Transport;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Persistence\TransportRepository;

class SelectorRouteCombine
{
    /**
     * @var array<Transport>
     */
    private array $transports;
    /**
     * @var array<Route>
     */
    private array $routes;
    private string $id;

    public function __construct()
    {
        $this->id = rand();
        wp_enqueue_script(
            'script_route_combine_selector',
            CENTRAL_BOOKING_URL . '/assets/js/route-selector-script.js',
            [],
            time(),
        );
    }

    public function get_transport_select(string $name = 'transport')
    {
        $select_component = new SelectComponent($name);
        $select_component->set_required(true);
        $select_component->set_attribute('target', "selector_route_transport_$this->id");
        $select_component->add_option('Seleccione...', '');
        foreach ($this->get_transport_data() as $transport) {
            $class = array_map(fn($route) => "show_if_origin_{$route['origin']}_destiny_{$route['destiny']}_time_{$route['time']}", $transport['routes']);
            $select_component->add_option(
                $transport['nicename'],
                $transport['id'],
                false,
                ['class' => $class]
            );
        }
        return $select_component;
    }

    public function get_origin_select(string $name = 'origin')
    {
        $select_component = new SelectComponent($name);
        $select_component->set_required(true);
        $select_component->set_attribute('target', "selector_route_origin_$this->id");
        $select_component->add_option('Seleccione...', '');
        foreach ($this->get_origin_data() as $origin) {
            $select_component->add_option($origin['name'], $origin['id']);
        }
        return $select_component;
    }

    public function get_destiny_select(string $name = 'destiny')
    {
        $select_component = new SelectComponent($name);
        $select_component->set_required(true);
        $select_component->set_attribute('target', "selector_route_destiny_$this->id");
        $select_component->add_option('Seleccione...', '');
        foreach ($this->get_destiny_data() as $origin) {
            $class = array_map(fn($destiny) => "show_if_origin_{$destiny}", $origin['origin']);
            $select_component->add_option(
                $origin['name'],
                $origin['id'],
                false,
                ['class' => $class]
            );
        }
        return $select_component;
    }

    public function get_schedule_select(string $name = 'time')
    {
        $select_component = new SelectComponent($name);
        $select_component->set_required(true);
        $select_component->set_attribute('target', "selector_route_time_$this->id");
        $select_component->add_option('Seleccione...', '');
        foreach ($this->get_time_data() as $time) {
            $class = array_map(fn($route) => "show_if_origin_{$route['origin']}_destiny_{$route['destiny']}", $time['routes']);
            $select_component->add_option(
                git_time_format($time['time']),
                $time['time'],
                false,
                ['class' => $class]
            );
        }
        return $select_component;
    }


    private function get_origin_data()
    {
        $origins = [];
        $processed_location_ids = [];
        foreach ($this->get_routes() as $route) {
            if (!isset($processed_location_ids[$route->get_origin()->id])) {
                $origins[] = [
                    'id' => $route->get_origin()->id,
                    'name' => $route->get_origin()->name,
                ];
                $processed_location_ids[$route->get_origin()->id] = true;
            }
        }
        return $origins;
    }

    private function get_destiny_data()
    {
        $origins = [];
        $processed_location_ids = [];
        foreach ($this->get_routes() as $route) {
            $destiny_id = $route->get_destiny()->id;
            $origin_id = $route->get_origin()->id;

            if (!isset($processed_location_ids[$destiny_id])) {
                $origins[] = [
                    'id' => $destiny_id,
                    'name' => $route->get_destiny()->name,
                    'origin' => [$origin_id],
                ];
                $processed_location_ids[$destiny_id] = count($origins) - 1;
            } else {
                $index = $processed_location_ids[$destiny_id];
                $origins[$index]['origin'][] = $origin_id;
            }
        }
        return $origins;
    }

    private function get_time_data()
    {
        $data = [];
        foreach ($this->get_routes() as $route) {
            $add = true;
            for ($i = 0; $i < sizeof($data); $i++) {
                if ($data[$i]['time'] === $route->departure_time) {
                    $data[$i]['routes'][] = [
                        'origin' => $route->get_origin()->id,
                        'destiny' => $route->get_destiny()->id,
                    ];
                    $add = false;
                }
            }
            if ($add) {
                $data[] = [
                    'time' => $route->departure_time,
                    'routes' => [
                        [
                            'origin' => $route->get_origin()->id,
                            'destiny' => $route->get_destiny()->id,
                        ],
                    ]
                ];
            }
        }
        return $data;
    }

    private function get_transport_data()
    {
        $data = [];
        foreach ($this->get_transports() as $transport) {
            $data[] = [
                'id' => $transport->id,
                'nicename' => $transport->nicename,
                'routes' => array_map(fn(Route $route) => [
                    'origin' => $route->get_origin()->id,
                    'destiny' => $route->get_destiny()->id,
                    'time' => $route->departure_time,
                ], $transport->get_routes()),
            ];
        }
        return $data;
    }

    private function get_transports()
    {
        if (!empty($this->transports)) {
            return $this->transports;
        }
        if (git_current_user_has_role('operator')) {
            $this->transports = (new TransportRepository)->find_by(
                args: ['id_operator' => get_current_user_id()],
                order_by: 'nicename',
                limit: -1
            );
        } else {
            $this->transports = (new TransportRepository)->find_by(order_by: 'nicename', limit: -1);
        }
        return $this->transports;
    }

    private function get_routes()
    {
        if (!empty($this->routes)) {
            return $this->routes;
        }

        $this->routes = [];
        $processed_route_ids = [];

        foreach ($this->get_transports() as $transport) {
            foreach ($transport->get_routes() as $route) {
                if (!isset($processed_route_ids[$route->id])) {
                    $this->routes[] = $route;
                    $processed_route_ids[$route->id] = true;
                }
            }
        }
        return $this->routes;
    }
}
