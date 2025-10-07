<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\Passenger;
use CentralTickets\Services\ArrayParser\PassengerArray;
use CentralTickets\Services\PackageData\PassengerData;
use CentralTickets\Services\PassengerService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @extends parent<Passenger>
 */
class PassengerController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            new PassengerService,
            new PassengerArray()
        );
    }

    public function parse_payload(array $payload)
    {
        return new PassengerData(
            isset($payload['name']) ? trim($payload['name']) : '',
            $payload['served'],
            isset($payload['nationality']) ? trim($payload['nationality']) : '',
            isset($payload['birthday']) ? trim($payload['birthday']) : '',
            isset($payload['date_trip']) ? trim($payload['date_trip']) : '',
            isset($payload['type_document']) ? trim($payload['type_document']) : '',
            isset($payload['data_document']) ? trim($payload['data_document']) : '',
            isset($payload['type']) ? trim($payload['type']) : '',
            $payload['route'] ?? -1,
            $payload['transport'] ?? -1,
        );
    }

    public function put_transfer(WP_REST_Request $request)
    {
        $id_route = $request->get_param('route') ?? -1;
        $id_passenger = $request->get_param('id') ?? -1;
        $date_trip = $request->get_param('date_trip') ?? '';
        $id_transport = $request->get_param('transport') ?? -1;

        if ($id_passenger < 0) {
            return new WP_REST_Response(['message' => 'ID inválido.'], 400);
        }

        if (!strtotime($date_trip)) {
            return new WP_REST_Response(['message' => 'Fecha con formato inválido.'], 400);
        }

        if ($this->service instanceof PassengerService) {

            $result = $this->service->transfer_passenger(
                $id_passenger,
                $id_route,
                $id_transport,
                $date_trip
            );

            if ($result === null) {

                return new WP_REST_Response([
                    'message' => 'Petición no ejecutada.',
                    'stack' => $this->service->error_stack,
                ], 400);

            }

            return new WP_REST_Response($this->response_creator->get_array($result));

        } else {

            return new WP_REST_Response(['message' => 'La solicitud no puede ser procesada.'], 400);

        }
    }

    /**
     * @param PassengerData $data
     * @return bool
     */
    public function validate($data)
    {
        $this->issues = [];
        $pass = true;

        if (empty($data->name)) {
            $this->issues[] = 'El nombre del pasajero es obligatorio.';
            $pass = false;
        }
        if (empty($data->nationality)) {
            $this->issues[] = 'La nacionalidad del pasajero es obligatoria.';
            $pass = false;
        }
        if (empty($data->type)) {
            $this->issues[] = 'Falta especificar el tipo de pasajero.';
            $pass = false;
        }
        if (empty($data->type_document)) {
            $this->issues[] = 'El tipo de documento es obligatorio.';
            $pass = false;
        }
        if (empty($data->data_document)) {
            $this->issues[] = 'La información del documento es obligatoria.';
            $pass = false;
        }
        if (empty($data->birthday)) {
            $this->issues[] = 'La fecha de nacimiento es obligatoria.';
            $pass = false;
        }
        if (empty($data->date_trip)) {
            $this->issues[] = 'La fecha de viaje es obligatoria.';
            $pass = false;
        }

        if (!strtotime($data->birthday)) {
            $this->issues[] = 'La fecha de nacimiento debe tener un formato válido (YYYY-MM-DD).';
            $pass = false;
        }
        if (!strtotime($data->date_trip)) {
            $this->issues[] = 'La fecha de viaje debe tener un formato válido (YYYY-MM-DD).';
            $pass = false;
        }

        if ($data->id_route === -1) {
            $this->issues[] = 'El ID de la ruta no es válido.';
            $pass = false;
        }
        if ($data->id_transport === -1) {
            $this->issues[] = 'El ID del transporte no es válido.';
            $pass = false;
        }

        return $pass;
    }
}
