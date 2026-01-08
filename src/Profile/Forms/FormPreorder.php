<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\GUI\DateTripInput;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;

class FormPreorder implements ComponentInterface
{
    public function compact()
    {
        $combine = new SelectorRouteCombine();
        $passengersInput = new InputComponent('passengers', 'number');
        $timeSelect = $combine->get_time_select('departure_time');
        $originSelect = $combine->get_origin_select();
        $destinySelect = $combine->get_destiny_select();
        $transportSelect = $combine->get_transport_select();
        $dateTripInput = new DateTripInput('date_trip');
        $passengersInput->attributes->set('min', '1');
        $passengersInput->attributes->set('value', '1');
        $passengersInput->setRequired(true);
        ob_start();
        $action = add_query_arg(
            [
                'action' => 'git_create_ticket_operator',
            ],
            admin_url('admin-ajax.php')
        );
        $tickets = git_tickets(
            [
                'status' => TicketStatus::PERORDER->value,
                'id_client' => get_current_user_id(),
            ]
        );
        ?>
        <form method="post" action="<?= esc_url($action); ?>">
            <h2>Crea un ticket</h2>
            <?php wp_nonce_field('create_ticket_operator', 'nonce') ?>
            <table class="table table-bordered">
                <tr>
                    <td>
                        <?= $originSelect->getLabel('Origen')->compact(); ?>
                    </td>
                    <td>
                        <?= $originSelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $destinySelect->getLabel('Destino')->compact(); ?>
                    </td>
                    <td>
                        <?= $destinySelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $timeSelect->getLabel('Hora')->compact(); ?>
                    </td>
                    <td>
                        <?= $timeSelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $transportSelect->getLabel('Transporte')->compact(); ?>
                    </td>
                    <td>
                        <?= $transportSelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $dateTripInput->create()->getLabel('Fecha de viaje')->compact(); ?>
                    </td>
                    <td>
                        <?= $dateTripInput->create()->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $passengersInput->getLabel('Pasajeros')->compact(); ?>
                    </td>
                    <td>
                        <?= $passengersInput->compact(); ?>
                    </td>
                </tr>
            </table>
            <button class="btn btn-primary" type="submit">Crear ticket</button>
        </form>
        <hr>
        <h2>Tus tickets</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>
                        Origen
                    </th>
                    <th>
                        Destino
                    </th>
                    <th>
                        Viaje
                    </th>
                    <th>
                        Pasajeros
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>
                            <?= $ticket->getPassengers()[0]->getRoute()->getOrigin()->name; ?>
                        </td>
                        <td>
                            <?= $ticket->getPassengers()[0]->getRoute()->getDestiny()->name; ?>
                        </td>
                        <td>
                            <?= $ticket->getPassengers()[0]->getDateTrip()->format('d M Y'); ?>,
                            <?= $ticket->getPassengers()[0]->getRoute()->getDepartureTime()->format('H:i'); ?>
                        </td>
                        <td>
                            <?= esc_html(count($ticket->getPassengers())); ?>
                        </td>
                        <td>
                            <a href="#" class="btn btn-warning">
                                <small>
                                    Ver detalles
                                </small>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }
}
