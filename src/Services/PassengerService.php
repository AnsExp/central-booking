<?php
namespace CentralTickets\Services;

use CentralTickets\Constants\LogLevelConstants;
use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Passenger;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\TicketRepository;
use CentralTickets\Persistence\PassengerRepository;
use CentralTickets\Persistence\TransportRepository;
use CentralTickets\Services\LogService;

/**
 * @extends parent<Passenger>
 */
class PassengerService extends BaseService
{
    private RouteRepository $route_repository;
    private TicketRepository $ticket_repository;
    private TransportRepository $transport_repository;

    public function __construct()
    {
        parent::__construct(new PassengerRepository);
        $this->route_repository = new RouteRepository;
        $this->ticket_repository = new TicketRepository;
        $this->transport_repository = new TransportRepository;
    }

    public function passenger_served(int $id, bool $served)
    {
        $this->error_stack = [];
        $passenger = $this->repository->find($id);
        if ($passenger === null) {
            $this->error_stack[] = "El pasajero con el ID: $id no existe.";
            return null;
        }
        $clone = clone $passenger;
        $served_previous = $passenger->served;
        $passenger->served = $served;
        $passenger_saved = $this->repository->save($passenger);
        if ($passenger_saved === null) {
            $this->error_stack[] = $this->repository->error_message;
            return null;
        }
        $this->notify_served($clone, $passenger);
        return $passenger_saved;
    }

    public function transfer_passenger(
        int $id_passenger,
        int $id_route,
        int $id_transport,
        string $date_trip,
    ) {
        $this->error_stack = [];
        $passenger = $this->repository->find($id_passenger);

        if ($passenger === null) {
            $this->error_stack[] = 'El pasajero no existe';
            return null;
        }

        $ticket = $passenger->get_ticket();

        if (!$ticket->flexible) {
            $this->error_stack[] = 'El ticket no es flexible.';
            return null;
        }

        if (
            !($ticket->status === TicketConstants::PAYMENT ||
                ($ticket->status === TicketConstants::PARTIAL && $passenger->approved))
        ) {
            $this->error_stack[] = 'El ticket no está pagado, o el pasajero no está aprobado.';
            return null;
        }

        $route = $this->route_repository->find($id_route);
        $transport = $this->transport_repository->find($id_transport);

        if ($transport === null) {
            $this->error_stack[] = 'El transporte no existe';
            return null;
        }

        if ($route === null) {
            $this->error_stack[] = 'La ruta no existe';
            return null;
        }

        $transport = $this->transport_repository->find($id_transport);

        if (!$transport->use_route($route)) {
            $this->error_stack[] = 'El transporte no recorre la ruta dada.';
            return null;
        }

        if (!$transport->is_available($date_trip)) {
            $this->error_stack[] = 'El transporte no se encuentra disponible en la fecha de traslado. Puede deberse a que está en mantenimiento o no es día laborable';
            return null;
        }

        $clone = clone $passenger;

        $passenger->set_route($route);
        $passenger->set_transport($transport);
        $passenger->date_trip = $date_trip;

        $this->notify_transfer($clone, $passenger);

        $this->repository->save($passenger);
        return $passenger;
    }

    protected function verify($passenger)
    {
        $pass = true;
        $this->error_stack = [];
        if (!$this->ticket_repository->exists($passenger->get_ticket()->id)) {
            $this->error_stack[] = 'El ticket asignado al pasajero no existe.';
            $pass = false;
        }
        $pass = $this->verify_ignore_ticket($passenger);
        return $pass;
    }

    /**
     * @param Passenger $passenger
     * @return bool
     */
    public function verify_ignore_ticket($passenger)
    {
        $pass = true;
        if (!$this->route_repository->exists($passenger->get_route()->id)) {
            $this->error_stack[] = 'La ruta asignada al pasajero no existe.';
            $pass = false;
        }
        if (!$this->transport_repository->exists($passenger->get_transport()->id)) {
            $this->error_stack[] = 'El transporte asignada al pasajero no existe.';
            $pass = false;
        }
        if (!$passenger->get_transport()->is_available($passenger->date_trip)) {
            $this->error_stack[] = 'El transporte asignado al pasajero no estará disponible en la fecha de viaje.';
            $pass = false;
        }
        if ($pass) {
            if (!$passenger->get_transport()->use_route($passenger->get_route())) {
                $this->error_stack[] = 'El transporte no recorre la ruta solicitada
                .';
                $pass = false;
            }
        }
        return $pass;
    }

    private function notify_served(Passenger $original_passenger, Passenger $new_passenger)
    {
        ob_start();
        ?>
        <p>
            El pasajero <b><?= $new_passenger->name ?></b> ha sido transportado.<br>
            El responsable del traslado es <code><?= wp_get_current_user()->user_login ?></code>.
        </p>
        <?php
        LogService::create_git_log(
            source: LogSourceConstants::PASSENGER,
            id_source: $new_passenger->id,
            message: ob_get_clean(),
            level: LogLevelConstants::INFO,
        );
    }

    private function notify_transfer(Passenger $original_passenger, Passenger $new_passenger)
    {
        ob_start();
        ?>
        <table>
            <tbody>
                <tr>
                    <th colspan="2"><strong>Cambio de Ruta</strong></th>
                </tr>
                <tr>
                    <th scope="col"><strong>Pasajero:</strong></th>
                    <td><?= $new_passenger->name ?></td>
                </tr>
                <tr>
                    <th scope="col"><strong>Viaje anterior:</strong></th>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <th>
                                        <strong>Origen:</strong>
                                    </th>
                                    <td>
                                        <s><?= $original_passenger->get_route()->get_origin()->name ?></s>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Destino:</strong>
                                    </th>
                                    <td>
                                        <s><?= $original_passenger->get_route()->get_destiny()->name ?></s>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Fecha:</strong>
                                    </th>
                                    <td>
                                        <s><?= git_date_format($original_passenger->date_trip) ?></s>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Transporte:</strong>
                                    </th>
                                    <td>
                                        <s><?= $original_passenger->get_transport()->nicename ?></s>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th scope="col"><strong>Viaje nuevo:</strong></th>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <th>
                                        <strong>Origen:</strong>
                                    </th>
                                    <td>
                                        <?= $new_passenger->get_route()->get_origin()->name ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Destino:</strong>
                                    </th>
                                    <td>
                                        <?= $new_passenger->get_route()->get_destiny()->name ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Fecha:</strong>
                                    </th>
                                    <td>
                                        <?= git_date_format($new_passenger->date_trip) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Transporte:</strong>
                                    </th>
                                    <td>
                                        <?= $new_passenger->get_transport()->nicename ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th scope="col"><strong>Responsable:</strong></th>
                    <td>
                        <code><?= wp_get_current_user()->user_login ?></code>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
        LogService::create_git_log(
            source: LogSourceConstants::PASSENGER,
            id_source: $new_passenger->id,
            message: ob_get_clean(),
            level: LogLevelConstants::INFO,
        );
    }
}
