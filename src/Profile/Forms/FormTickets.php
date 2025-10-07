<?php
namespace CentralTickets\Profile\Forms;

use CentralTickets\Components\Component;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\InputFloatingLabelComponent;
use CentralTickets\Components\Implementation\DateTripInput;
use CentralTickets\Components\Implementation\SelectorRouteCombine;
use CentralTickets\Persistence\TicketRepository;

class FormTickets implements Component
{
    public function compact()
    {
        $repository = new TicketRepository();
        $ticket = $repository->find($_GET['ticket_number'] ?? -1);
        ob_start();
        if ($ticket === null) {
            ?>
            <div class="alert alert-danger">
                Ticket no encontrado.
            </div>
            <?php
            return ob_get_clean();
        }
        $passengers = $ticket->get_passengers();
        $combine = new SelectorRouteCombine();
        $date_trip_input = (new DateTripInput('date_trip'))->create();
        $select_origin = $combine->get_origin_select('origin');
        $select_destiny = $combine->get_destiny_select('destiny');
        $select_schedule = $combine->get_schedule_select('time');
        $select_transport = $combine->get_transport_select('transport');
        $date_trip_input->set_required(true);
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php?action=git_transfer_passengers')); ?>">
            <?php
            foreach ($passengers as $passenger):
                $checkbox = new InputComponent('passengers[]', 'checkbox');
                $checkbox->class_list->remove('form-control');
                $checkbox->set_value($passenger->id);
                ?>
                <ul>
                    <li>
                        <?= $checkbox->compact(); ?>
                        <?= $checkbox->get_label($passenger->name)->compact(); ?>
                    </li>
                </ul>
            <?php endforeach;
            $select_origin_floating_label = new InputFloatingLabelComponent($select_origin, 'Origen');
            $select_destiny_floating_label = new InputFloatingLabelComponent($select_destiny, 'Destino');
            $select_schedule_floating_label = new InputFloatingLabelComponent($select_schedule, 'Horario');
            $select_transport_floating_label = new InputFloatingLabelComponent($select_transport, 'Transporte');
            $input_date_trip_floating_label = new InputFloatingLabelComponent($date_trip_input, 'Fecha de viaje');
            ?>
            <div class="row">
                <div class="col">
                    <?= $select_origin_floating_label->compact(); ?>
                </div>
                <div class="col">
                    <?= $select_destiny_floating_label->compact(); ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <?= $select_schedule_floating_label->compact(); ?>
                </div>
                <div class="col">
                    <?= $select_transport_floating_label->compact(); ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                </div>
                <div class="col">
                    <?= $input_date_trip_floating_label->compact(); ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                Guardar cambios
            </button>
        </form>
        <?php
        return ob_get_clean();
    }
}
