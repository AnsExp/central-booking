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
        $select_origin_floating_label = new InputFloatingLabelComponent($select_origin, 'Origen');
        $select_destiny_floating_label = new InputFloatingLabelComponent($select_destiny, 'Destino');
        $select_schedule_floating_label = new InputFloatingLabelComponent($select_schedule, 'Horario');
        $select_transport_floating_label = new InputFloatingLabelComponent($select_transport, 'Transporte');
        $input_date_trip_floating_label = new InputFloatingLabelComponent($date_trip_input, 'Fecha de viaje');
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php?action=git_transfer_passengers')); ?>">
            <h2>Modo translado</h2>
            <div class="bg-warning-subtle p-3 my-3 rounded">
                <?php
                $first = true;
                $has_approveds = false;
                foreach ($passengers as $passenger) {
                    if (!$passenger->approved) {
                        continue;
                    }
                    $checkbox = new InputComponent('passengers[]', 'checkbox');
                    $checkbox->class_list->remove('form-control');
                    $checkbox->set_value($passenger->id);
                    $checkbox->class_list->add('me-3');
                    $checkbox->display();
                    $checkbox->get_label($passenger->name)->display();
                    if ($first && count($passengers) > 1) {
                        echo '<br>';
                    }
                    $first = false;
                    $has_approveds = true;
                }
                if (!$has_approveds) {
                    ?>
                    <p>No hay pasajeros aprobados en este ticket. Las razones pueden incluir:</p>
                    <ul>
                        <li>El ticket no fue pagado.</li>
                        <li>El ticket fue anulado.</li>
                    </ul>
                    <?php
                }
                ?>
            </div>
            <?php if ($has_approveds): ?>
                <div class="row mb-3">
                    <div class="col">
                        <?= $select_origin_floating_label->compact(); ?>
                    </div>
                    <div class="col">
                        <?= $select_destiny_floating_label->compact(); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <?= $select_schedule_floating_label->compact(); ?>
                    </div>
                    <div class="col">
                        <?= $select_transport_floating_label->compact(); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <?= $input_date_trip_floating_label->compact(); ?>
                    </div>
                </div>
            <?php endif; ?>
            <a class="btn btn-danger" href="<?= esc_url(add_query_arg([
                'action' => 'view_order',
                'order' => $ticket->get_order()->get_id(),
            ])) ?>">Cancelar</a>
            <?php if ($has_approveds): ?>
                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
            <?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }
}
