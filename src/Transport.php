<?php
namespace CentralTickets;

use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\ServiceRepository;
use CentralTickets\Persistence\OperatorRepository;

use Exception;
use InvalidArgumentException;

class Transport
{
    public int $id = 0;
    public string $code = '';
    public string $nicename = '';
    public string $type = '';
    public array $metadata = [];
    /**
     * @var array<Service>
     */
    private array $services;
    /**
     * @var array<Route>
     */
    private array $routes;

    private Operator $operator;

    public function get_services()
    {
        if (!isset($this->services)) {
            $repository = new ServiceRepository;
            $this->services = $repository->find_by(['id_transport' => $this->id]);
        }
        return $this->services;
    }

    /**
     * @param array<Service> $services
     * @return void
     */
    public function set_services(array $services)
    {
        $this->services = $services;
    }

    public function get_operator()
    {
        if (!isset($this->operator)) {
            $repository = new OperatorRepository;
            $result = $repository->find_first(['id_transport' => $this->id]);
            if ($result === null) {
                return new Operator();
            }
            $this->operator = $result;
        }
        return $this->operator;
    }

    public function get_crew()
    {
        return $this->get_meta('crew') ?? [];
    }

    /**
     * @param array<Route> $routes
     * @return void
     */
    public function set_routes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @return array<Route>
     */
    public function get_routes()
    {
        if (!isset($this->routes)) {
            $repository = new RouteRepository;
            $this->routes = $repository->find_by(['id_transport' => $this->id]);
        }
        return $this->routes;
    }

    public function get_meta(string $key)
    {
        return $this->metadata[$key] ?? null;
    }

    public function set_meta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    public function set_operator(Operator $operator)
    {
        $this->operator = $operator;
    }

    public function is_available(string $date = ''): bool
    {
        $date = empty($date) ? date('Y-m-d') : $date;
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        if (
            ($this->metadata['maintenance_dates']['date_start'] ?? null) &&
            ($this->metadata['maintenance_dates']['date_end'] ?? null) &&
            $date >= $this->metadata['maintenance_dates']['date_start'] &&
            $date <= $this->metadata['maintenance_dates']['date_end']
        ) {
            return false;
        }

        if (isset($this->metadata['working_days']) && !in_array($dayOfWeek, $this->metadata['working_days'], true)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function get_working_days(): array
    {
        return $this->get_meta('working_days') ?? [];
    }

    public function add_working_day(string $day)
    {
        $this->metadata['working_days'] = $this->metadata['working_days'] ?? [];

        if (!in_array($day, $this->metadata['working_days'], true)) {
            $this->metadata['working_days'][] = $day;
        }
    }

    public function remove_working_day(string $day)
    {
        if (isset($this->metadata['working_days'])) {
            $this->metadata['working_days'] = array_values(array_diff($this->metadata['working_days'], [$day]));
        }
    }

    public function set_maintenance_dates(string $date_start, string $date_end)
    {
        if (strtotime($date_start) === false || strtotime($date_end) === false) {
            throw new InvalidArgumentException("Las fechas ingresadas no son válidas.");
        }

        $this->metadata['maintenance_dates'] = [
            'date_start' => $date_start,
            'date_end' => $date_end
        ];
    }

    /**
     * @return array{date_start: string, date_end: string}
     */
    public function get_maintenance_dates()
    {
        return $this->get_meta('maintenance_dates') ?? [
            'date_start' => '',
            'date_end' => '',
        ];
    }

    public function use_route(Route $route)
    {
        foreach ($this->get_routes() as $rt) {
            if ($rt->id === $route->id) {
                return true;
            }
        }
        return false;
    }
}
