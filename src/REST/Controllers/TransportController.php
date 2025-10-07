<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\REST\Controllers\BaseController;
use CentralTickets\Services\ArrayParser\TransportArray;
use CentralTickets\Services\PackageData\TransportData;
use CentralTickets\Services\TransportService;
use DateTime;
use CentralTickets\Transport;
use CentralTickets\Constants\WeekConstants;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @extends parent<Transport>
 */
class TransportController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            new TransportService(),
            new TransportArray()
        );
    }

    protected function parse_payload(array $payload)
    {
        return new TransportData(
            isset($payload['type']) ? trim($payload['type']) : '',
            isset($payload['nicename']) ? trim($payload['nicename']) : '',
            isset($payload['code']) ? trim($payload['code']) : '',
            $payload['capacity'] ?? -1,
            $payload['operator'] ?? -1,
            $payload['flexible'] ?? false,
            $payload['crew'] ?? [],
            $payload['working_days'] ?? [],
            $payload['services'] ?? [],
            $payload['routes'] ?? [],
        );
    }

    public function get_availability(WP_REST_Request $request)
    {
        $id_route = $request->get_param('route') ?? -1;
        $date_trip = $request->get_param('date_trip') ?? '';
        $pasengers_count = $request->get_param('passengers_count') ?? 0;

        if ($id_route === -1) {
            return new WP_REST_Response(['message' => 'El ID de la ruta no tiene el formato adecuado.'], 400);
        }

        if (!$this->is_date($date_trip)) {
            return new WP_REST_Response(['message' => 'La fecha de viaje no tienen el formato correcto. Formato permitido: Y-m-d.'], 400);
        }

        if ($this->service instanceof TransportService) {
            $result = $this->service->get_transports_availability($id_route, $date_trip, $pasengers_count);
        }

        if (!$result) {
            return new WP_REST_Response(['message' => 'No hay transportes disponibles.'], 400);
        }

        return new WP_REST_Response(array_map(fn($item) => $this->response_creator->get_array($item), $result));
    }

    public function post_check_availability(WP_REST_Request $request)
    {
        $id_route = $request->get_param('route') ?? -1;
        $date_trip = $request->get_param('date_trip') ?? '';
        $id_transport = $request->get_param('transport') ?? -1;
        $pasengers_count = $request->get_param('passengers_count') ?? 0;

        if ($id_transport === -1) {
            return new WP_REST_Response(['message' => 'El ID del transporte no tiene el formato adecuado.'], 400);
        }

        if ($id_route === -1) {
            return new WP_REST_Response(['message' => 'El ID de la ruta no tiene el formato adecuado.'], 400);
        }

        if (!$this->is_date($date_trip)) {
            return new WP_REST_Response(['message' => 'La fecha de viaje no tienen el formato correcto. Formato permitido: Y-m-d.'], 400);
        }

        if ($this->service instanceof TransportService) {
            $result = $this->service->check_availability($id_transport, $id_route, $date_trip, $pasengers_count);
        }

        if (!$result) {
            return new WP_REST_Response([
                'message' => $this->service->error_stack
            ], 400);
        }

        return new WP_REST_Response(['message' => "El transporte cuenta con la disponibilidad para la cantidad de $pasengers_count pasajero(s)."]);
    }

    public function post_maintenance(WP_REST_Request $request)
    {
        $id_transport = $request->get_param('id') ?? -1;
        $date_start = $request->get_param('date_start') ?? '';
        $date_end = $request->get_param('date_end') ?? '';

        if (!$this->is_date($date_start) || !$this->is_date($date_end)) {
            return new WP_REST_Response(['message' => 'Las fechas no tienen el formato correcto. Formato permitido: Y-m-d.'], 400);
        }

        if ($id_transport === -1) {
            return new WP_REST_Response(['message' => 'El ID del transporte no tiene el formato adecuado.'], 400);
        }

        if ($this->service instanceof TransportService) {
            $result = $this->service->set_maintenance($id_transport, $date_start, $date_end);
        }

        if ($result === null) {
            return new WP_REST_Response(['message' => 'Ha ocurrido un conflicto.', 'stack' => $this->service->error_stack], 400);
        }

        return new WP_REST_Response((new TransportArray)->get_array($result));
    }

    private function is_date($texto)
    {
        $fecha = DateTime::createFromFormat('Y-m-d', $texto);
        return $fecha && $fecha->format('Y-m-d') === $texto;
    }

    /**
     * @param TransportData $data
     * @return bool
     */
    protected function validate($data)
    {
        $this->issues = [];
        $pass = true;

        if (empty($data->code)) {
            $this->issues[] = "El código no puede estar vacío.";
            $pass = false;
        }

        if ($data->type === '') {
            $this->issues[] = "No se asignó el tipo de transporte.";
            $pass = false;
        }

        if (empty($data->nicename)) {
            $this->issues[] = "El nombre agradable no puede estar vacío.";
            $pass = false;
        }

        if (!is_array($data->crew)) {
            $this->issues[] = "Crew debe ser un mapa (array asociativo).";
            $pass = false;
        }

        if (!is_array($data->routes) || (!empty($data->routes) && !$this->all_numeric($data->routes))) {
            $this->issues[] = "Routes debe ser un array de números.";
            $pass = false;
        }

        if (!is_array($data->services) || (!empty($data->services) && !$this->all_numeric($data->services))) {
            $this->issues[] = "Services debe ser un array de números.";
            $pass = false;
        }

        if (!is_array($data->working_days)) {
            $this->issues[] = "Los días operables debe ser un array.";
            $pass = false;
        } else {
            foreach ($data->working_days as $working_day) {
                if (!WeekConstants::isValidDay($working_day)) {
                    $this->issues[] = "El día \"$working_day\" no tiene el formato correcto.";
                    $pass = false;
                }
            }
        }

        if ($data->capacity <= 0) {
            $this->issues[] = "Capacity debe ser mayor que 0.";
            $pass = false;
        }

        return $pass;
    }

    private function all_numeric($array)
    {
        return is_array($array) && count($array) > 0 && array_reduce($array, fn($carry, $item) => $carry && is_numeric($item), true);
    }
}
