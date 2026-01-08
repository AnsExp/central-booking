<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;

class FormTripOperator implements ComponentInterface
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
        $this->schedule_select = $route_selector->get_time_select('time');
        $this->transport_select = $route_selector->get_transport_select('id_transport');

        $this->origin_select->setValue($_GET['id_origin'] ?? '');
        $this->date_to_input->setValue($_GET['date_to'] ?? '');
        $this->destiny_select->setValue($_GET['id_destiny'] ?? '');
        $this->schedule_select->setValue($_GET['time'] ?? '');
        $this->date_from_input->setValue($_GET['date_from'] ?? '');
        $this->transport_select->setValue($_GET['id_transport'] ?? '');

        $this->date_to_input->setRequired(true);
        $this->date_from_input->setRequired(true);

        $this->date_to_input->attributes->set('readonly', '');

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
                        <?= $this->origin_select->getLabel('Origen')->compact(); ?>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->destiny_select->compact(); ?>
                        <?= $this->destiny_select->getLabel('Destino')->compact(); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->schedule_select->compact(); ?>
                        <?= $this->schedule_select->getLabel('Horario')->compact(); ?>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->transport_select->compact(); ?>
                        <?= $this->transport_select->getLabel('Transporte')->compact(); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->date_from_input->compact(); ?>
                        <?= $this->date_from_input->getLabel('Fecha desde')->compact(); ?>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="form-floating">
                        <?= $this->date_to_input->compact(); ?>
                        <?= $this->date_to_input->getLabel('Fecha hasta')->compact(); ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
        <?php
        return ob_get_clean();
    }
}
