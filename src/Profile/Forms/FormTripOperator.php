<?php
namespace CentralTickets\Profile\Forms;

use CentralTickets\Components\Component;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\Implementation\SelectorRouteCombine;

class FormTripOperator implements Component
{
    private SelectComponent $origin_select;
    private SelectComponent $destiny_select;
    private SelectComponent $schedule_select;
    private SelectComponent $transport_select;
    private InputComponent $date_from_input;
    private InputComponent $date_to_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $route_selector = new SelectorRouteCombine();

        $this->date_to_input = new InputComponent('date_to', 'date');
        $this->date_from_input = new InputComponent('date_from', 'date');

        $this->origin_select = $route_selector->get_origin_select('id_origin');
        $this->destiny_select = $route_selector->get_destiny_select('id_destiny');
        $this->schedule_select = $route_selector->get_schedule_select('time');
        $this->transport_select = $route_selector->get_transport_select('id_transport');

        $this->origin_select->set_value($_GET['id_origin'] ?? '');
        $this->date_to_input->set_value($_GET['date_to'] ?? '');
        $this->destiny_select->set_value($_GET['id_destiny'] ?? '');
        $this->schedule_select->set_value($_GET['time'] ?? '');
        $this->date_from_input->set_value($_GET['date_from'] ?? '');
        $this->transport_select->set_value($_GET['id_transport'] ?? '');

        $this->date_to_input->set_required(true);
        $this->date_from_input->set_required(true);

        $this->date_to_input->set_attribute('readonly', '');

        wp_enqueue_script_module(
            'central-tickets-operator-form-trip',
            CENTRAL_BOOKING_URL . '/assets/js/operator/FormRouteOperator.js'
        );
    }

    public function compact()
    {
        ob_start();
        ?>
        <form method="get" class="p-3">
            <input type="hidden" name="tab" value="trips">
            <input type="hidden" name="page" value="git_operator_panel">
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->origin_select->compact(); ?>
                        <?= $this->origin_select->get_label('Origen')->compact(); ?>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->destiny_select->compact(); ?>
                        <?= $this->destiny_select->get_label('Destino')->compact(); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->schedule_select->compact(); ?>
                        <?= $this->schedule_select->get_label('Horario')->compact(); ?>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->transport_select->compact(); ?>
                        <?= $this->transport_select->get_label('Transporte')->compact(); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->date_from_input->compact(); ?>
                        <?= $this->date_from_input->get_label('Fecha desde')->compact(); ?>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->date_to_input->compact(); ?>
                        <?= $this->date_to_input->get_label('Fecha hasta')->compact(); ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
        <?php
        return ob_get_clean();
    }
}
