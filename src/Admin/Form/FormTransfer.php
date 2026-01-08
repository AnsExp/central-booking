<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;

final class FormTransfer implements DisplayerInterface
{
    private SelectComponent $origin_select;
    private SelectComponent $destiny_select;
    private SelectComponent $schedule_select;
    private SelectComponent $transport_select;
    private InputComponent $date_trip_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $route_combine = new SelectorRouteCombine();
        $this->origin_select = $route_combine->get_origin_select();
        $this->destiny_select = $route_combine->get_destiny_select();
        $this->schedule_select = $route_combine->get_time_select();
        $this->transport_select = $route_combine->get_transport_select();
        $this->date_trip_input = new InputComponent('date_trip', 'date');

        $this->date_trip_input->setRequired(true);
    }

    public function render()
    {
        wp_enqueue_script(
            'central-tickets-passengers-table',
            CENTRAL_BOOKING_URL . '/assets/js/admin/transfer-form.js',
            ['jquery'],
            time(),
            []
        );
        wp_localize_script(
            'central-tickets-passengers-table',
            'gitTransferForm',
            [
                'hook' => admin_url('admin-ajax.php?action=git_approve_passengers_table'),
                'successRedirect' => admin_url('admin.php?page=central_passengers'),
            ]
        );
        ob_start();
        ?>
        <form action="<?= admin_url('admin-ajax.php?action=git_transfer_passengers') ?>" method="post" id="git-transfer-form">
            <div id="container_issues_to_transfer"></div>
            <table class="form-table">
                <tr>
                    <th><?php $this->origin_select->getLabel('Origen')->render(); ?></th>
                    <td><?php $this->origin_select->render(); ?></td>
                    <th><?php $this->destiny_select->getLabel('Destino')->render(); ?></th>
                    <td><?php $this->destiny_select->render(); ?></td>
                </tr>
                <tr>
                    <th><?php $this->schedule_select->getLabel('Horario')->render(); ?></th>
                    <td><?php $this->schedule_select->render(); ?></td>
                    <th><?php $this->transport_select->getLabel('Transporte')->render(); ?></th>
                    <td><?php $this->transport_select->render(); ?></td>
                </tr>
                <tr>
                    <th><?php $this->date_trip_input->getLabel('Fecha del Viaje')->render(); ?></th>
                    <td><?php $this->date_trip_input->render(); ?></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
            <div style="margin: 20px 0;" id="container_passengers_to_transfer"></div>
            <input type="submit" class="button button-primary" value="Trasladar">
        </form>
        <?php
        echo ob_get_clean();
    }
}
