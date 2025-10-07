<?php
namespace CentralTickets\Profile\Tables;

use CentralTickets\Route;
use CentralTickets\Transport;
use CentralTickets\REST\RegisterRoute;
use CentralTickets\Persistence\PassengerRepository;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\TransportRepository;
use CentralTickets\Components\ButtonComponent;
use CentralTickets\Components\Component;
use CentralTickets\Components\ModalComponent;
use CentralTickets\Components\Constants\ButtonStyleConstants;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;

final class TableTripOperator implements Component
{
    private ModalComponent $modal;

    public function __construct()
    {
        $this->modal = new ModalComponent('Control de Viaje');
    }

    public function compact()
    {
        ob_start();
        if (
            !isset($_GET['time']) ||
            !isset($_GET['date_to']) ||
            !isset($_GET['date_from']) ||
            !isset($_GET['id_origin']) ||
            !isset($_GET['id_destiny']) ||
            !isset($_GET['id_transport'])
        ) {
            return ob_get_clean();
        }

        $route = $this->get_route(
            (int) $_GET['id_origin'],
            (int) $_GET['id_destiny'],
            $_GET['time']
        );

        if ($route === null) {
            return ob_get_clean();
        }

        $transport = $this->get_transport((int) $_GET['id_transport']);

        if ($transport === null) {
            return ob_get_clean();
        }

        if (!git_current_user_has_role('administrator')) {
            if ($transport->get_operator()->ID !== get_current_user_id()) {
                ?>
                <p class="text-center">No tienes permiso para realizar esta consulta.</p>
                <?php
                return ob_get_clean();
            }
        }

        $dates = $this->obtenerFechasEntre(
            $_GET['date_from'],
            $_GET['date_to']
        );

        $this->modal->set_body_component(git_string_to_component(
            $this->modal_table_content($route, $transport)
        ));

        wp_enqueue_script(
            'git-trip-operator',
            CENTRAL_BOOKING_URL . '/assets/js/operator/table-trip-operator.js'
        );

        wp_localize_script(
            'git-trip-operator',
            'gitTripOperator',
            [
                'url' => admin_url('admin-ajax.php'),
                'hook' => 'git_finish_trip',
                'nonce' => wp_create_nonce('git_trip_operator_nonce')
            ]
        );

        echo $this->modal->compact();
        ?>
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <?php foreach ($dates as $date): ?>
                        <th style="text-align: center;"> <?= git_date_format($date, true); ?> </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    foreach ($dates as $date):
                        $passengers = $this->get_passengers($transport->id, $route->id, $date);
                        $total_passengers = count($passengers);
                        $path_pdf_trip = '#';
                        $path_pdf_salling_request = '#';
                        if ($total_passengers > 0) {
                            $path_pdf_trip = get_home_url() . '/wp-json/' . RegisterRoute::prefix . 'pdf_trip?transport=' . $transport->id . '&date=' . $date . '&route=' . $route->id;
                            $path_pdf_salling_request = get_home_url() . '/wp-json/' . RegisterRoute::prefix . 'pdf_salling_request?transport=' . $transport->id . '&date=' . $date . '&route=' . $route->id;
                        }
                        ?>
                        <td>
                            <?php
                            $button = new ButtonComponent($total_passengers . ' / ' . $transport->get_meta('capacity'));
                            $button->set_style(ButtonStyleConstants::BASE);
                            if ($total_passengers > 0) {
                                $button = $this->modal->create_button_launch($total_passengers . ' / ' . $transport->get_meta('capacity'));
                            }
                            $button->set_attribute('data-passenger-counter', $total_passengers . ' / ' . $transport->get_meta('capacity'));
                            $button->class_list->add('button-launch-modal-info', 'w-100');
                            $button->set_attribute('data-path-pdf-trip', $path_pdf_trip);
                            $button->set_attribute('data-path-pdf-salling-request', $path_pdf_salling_request);
                            $button->set_attribute('data-route', $route->id);
                            $button->set_attribute('data-transport', $transport->id);
                            $button->set_attribute('data-date-trip', $date);
                            $button->set_attribute('data-date-trip-display', git_date_format($date));
                            $button->display();
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    private function modal_table_content(Route $route, Transport $transport)
    {
        ob_start();
        ?>
        <table class="table table-bordered table-striped table-hover">
            <tr>
                <th>Origen</th>
                <td><?= $route->get_origin()->name ?></td>
            </tr>
            <tr>
                <th>Destino</th>
                <td><?= $route->get_destiny()->name ?></td>
            </tr>
            <tr>
                <th>Horario</th>
                <td><?= git_time_format($route->departure_time) ?></td>
            </tr>
            <tr>
                <th>Viaje</th>
                <td id="cell-date-trip"></td>
            </tr>
            <tr>
                <th>Transporte</th>
                <td><?= $transport->nicename ?></td>
            </tr>
            <tr>
                <th>Pasajeros</th>
                <td id="cell-passengers-count"></td>
            </tr>
        </table>
        <div class="btn-group">
            <button id="button-finish-trip" class="btn btn-warning">Finalizar Trayecto</button>
            <button id="button-print-trip" class="btn btn-primary">Lista de embarque</button>
            <button id="button-print-salling-request" class="btn btn-success">Solicitud de Zarpe</button>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_passengers(int $transport, int $route, string $date)
    {
        if ($transport <= 0 || $route <= 0) {
            return [];
        }

        $repository = new PassengerRepository();

        return $repository->find_by([
            'id_transport' => $transport,
            'id_route' => $route,
            'date_trip' => $date,
            'approved' => true,
            'served' => false,
        ]);
    }

    private function get_transport(int $transport)
    {
        if ($transport < 0) {
            return null;
        }
        $repository = new TransportRepository();
        return $repository->find_first(['id' => $transport]);
    }

    private function get_route(int $origin, int $destiny, string $schedule)
    {
        if (empty($schedule) || $origin < 0 || $destiny < 0) {
            return null;
        }
        $repository = new RouteRepository();
        return $repository->find_first([
            'id_origin' => $origin,
            'id_destiny' => $destiny,
            'departure_time' => $schedule,
        ]);
    }

    private function obtenerFechasEntre(string $inicio, string $fin)
    {
        try {
            $fechaInicio = new DateTime($inicio);
            $fechaFin = new DateTime($fin);
        } catch (Exception $e) {
            return null;
        }

        $fechaFinInclusiva = clone $fechaFin;
        $fechaFinInclusiva->modify('+1 day');

        $intervalo = new DateInterval('P1D');
        $rango = new DatePeriod($fechaInicio, $intervalo, $fechaFinInclusiva);

        $fechas = [];
        foreach ($rango as $fecha) {
            $fechas[] = $fecha->format('Y-m-d');
        }

        return $fechas;
    }
}
