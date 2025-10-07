<?php
namespace CentralTickets\Services;

use DateTime;
use CentralTickets\Passenger;
use CentralTickets\Transport;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Persistence\PassengerRepository;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\TransportRepository;

/**
 * @extends parent<Transport>
 */
class TransportService extends BaseService
{
    private RouteRepository $route_repository;
    private PassengerRepository $passenger_repository;
    public function __construct()
    {
        parent::__construct(new TransportRepository);
        $this->route_repository = new RouteRepository;
        $this->passenger_repository = new PassengerRepository;
    }

    public function get_transports_availability(int $id_route, string $date_trip, int $passengers_count = 1)
    {
        $this->error_stack = [];
        $route = $this->route_repository->find($id_route);
        if ($route === null) {
            $this->error_stack[] = "La ruta con el ID $id_route no existe.";
            return [];
        }
        $transports = $route->get_transports();
        if (count($transports) === 0) {
            return [];
        }
        $available_transports = [];
        foreach ($transports as $transport) {
            if ($this->check_availability($transport->id, $id_route, $date_trip, $passengers_count)) {
                $available_transports[] = $transport;
            }
        }
        return $available_transports;
    }

    public function check_availability(int $id_transport, int $id_route, string $date_trip, int $passengers_count = 1)
    {
        if ($passengers_count === 0) {
            return true;
        }
        $this->error_stack = [];
        $transport = $this->repository->find($id_transport);
        if ($transport === null) {
            $this->error_stack[] = "El transporte no existe.";
            return false;
        }
        if (!$transport->is_available($date_trip)) {
            $this->error_stack[] = "El transporte {$transport->nicename} no estará disponible en la fecha {$date_trip} por uno de los siguientes motivos: Está deshabilitado o no cuenta con la operación diaria.";
            return false;
        }
        $route = $this->route_repository->find($id_route);
        if ($route === null) {
            $this->error_stack[] = "La ruta con el ID $id_route no existe.";
            return false;
        }
        if (!$transport->use_route($route)) {
            $this->error_stack[] = "El transporte {$transport->nicename} no tiene asignado la ruta entre {$route->get_origin()->name} y {$route->get_destiny()->name}.";
            return false;
        }
        $passengers = $this->passenger_repository->count([
            'id_route' => $route->id,
            'id_transport' => $transport->id,
            'date_trip' => $date_trip,
            'served' => false,
        ]);
        $capacity = $transport->get_meta('capacity');
        if ($capacity === null) {
            $this->error_stack[] = "El transporte {$transport->nicename} no tiene asignada un capacidad máxima.";
            return false;
        }
        if ($passengers + $passengers_count > $capacity) {
            $this->error_stack[] = "El transporte {$transport->nicename} no tiene capacidad suficiente para $passengers_count pasajero(s).";
            return false;
        }
        return true;
    }

    public function set_maintenance(int $id_transport, string $date_start, string $date_end)
    {
        $this->error_stack = [];
        $transport = $this->repository->find($id_transport);
        if ($transport === null) {
            $this->error_stack[] = 'El transporte no existe.';
            return null;
        }
        if (!$this->validate_date_range($date_start, $date_end)) {
            $this->error_stack[] = 'El rango de fechas no es válido.';
            return null;
        }
        $passengers = $this->passenger_repository->find_by([
            'id_transport' => $transport->id,
            'date_trip_from' => $date_start,
            'date_trip_to' => $date_end,
            'served' => false,
            'approved' => true,
        ]);
        if (count($passengers) > 0) {
            $this->error_stack[] = "Entre " . git_date_format($date_start) . " y " . git_date_format($date_end) . ", existen pasajeros pendientes de viajes. Cantidad de pasajeros: " . count($passengers);
            $this->error_stack[] = array_map(fn(Passenger $passenger) => $passenger->id, $passengers);
            return null;
        }
        $transport->set_maintenance_dates($date_start, $date_end);
        $transport_saved = $this->repository->save($transport);
        if ($transport_saved === null) {
            $this->error_stack[] = $this->repository->error_message;
            return null;
        }
        return $transport_saved;
    }

    private function validate_date_range(string $date_start, string $date_end)
    {
        $start = DateTime::createFromFormat('Y-m-d', $date_start);
        $end = DateTime::createFromFormat('Y-m-d', $date_end);
        if (!$start || !$end) {
            return false;
        }
        return $end >= $start;
    }

    /**
     * @param Transport $transport
     * @return bool
     */
    protected function verify($transport)
    {
        $this->error_stack = [];
        $pass = true;
        if (!user_can($transport->get_operator()->ID, 'operator')) {
            $this->error_stack[] = 'El usuario asignado al transporte no cumple con el rol de operador';
            $pass = false;
        }

        if (!$this->verify_field($transport, 'code')) {
            $this->error_stack[] = 'Ya existe un transporte con el mismo código';
            $pass = false;
        }

        if (!$this->verify_field($transport, 'nicename')) {
            $this->error_stack[] = 'Ya existe un transporte con el mismo nombre';
            $pass = false;
        }

        if (!TransportConstants::is_valid($transport->type)) {
            $this->error_stack[] = 'El tipo de transporte no está disponible.';
            $pass = false;
        }

        return $pass;
    }
}
