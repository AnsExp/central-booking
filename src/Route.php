<?php
namespace CentralTickets;

use Exception;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\TransportRepository;

class Route
{
    public int $id = 0;
    public string $type = '';
    public float $distance_km = 0.0;
    private Location $origin;
    private Location $destiny;
    public string $departure_time = '00:00:00';
    public string $duration_trip = '00:00:00';
    /**
     * @var array<Transport>
     */
    private array $transports;

    public function get_origin()
    {
        if (!isset($this->origin)) {
            $repository = new RouteRepository;
            $origin = $repository->get_origin_by_route($this);
            if ($origin === null) {
                throw new Exception("La ruta con el ID $this->id no tiene un origen.");
            }
            $this->origin = $origin;
        }
        return $this->origin;
    }

    public function set_origin(Location $origin)
    {
        $this->origin = $origin;
    }

    public function get_destiny()
    {
        if (!isset($this->destiny)) {
            $repository = new RouteRepository;
            $destiny = $repository->get_destiny_by_route($this);
            if ($destiny === null) {
                throw new Exception("La ruta $this->id no tiene un origen.");
            }
            $this->destiny = $destiny;
        }
        return $this->destiny;
    }

    public function set_destiny(Location $destiny)
    {
        $this->destiny = $destiny;
    }

    public function get_transports()
    {
        if (!isset($this->transports)) {
            $repository = new TransportRepository;
            $this->transports = $repository->find_by(
                args: ['id_route' => $this->id],
                order_by: 'nicename',
                order: 'ASC'
            );
        }
        return $this->transports;
    }

    /**
     * @param array<Transport> $transports
     * @return void
     */
    public function set_transports(array $transports)
    {
        $this->transports = $transports;
    }

    public static function get(int $id_route)
    {
        $repository = new RouteRepository;
        return $repository->find($id_route);
    }
}
