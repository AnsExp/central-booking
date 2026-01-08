<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\LocationSelect;
use CentralBooking\Implementation\GUI\TransportSelect;
use CentralBooking\Implementation\GUI\TypeSelect;

final class FormRoute implements DisplayerInterface
{
    private InputComponent $input_id;
    private SelectComponent $select_origin;
    private SelectComponent $select_destiny;
    private SelectComponent $select_type;
    private InputComponent $input_arrival_time;
    private InputComponent $input_departure_time;
    private MultipleSelectComponent $select_transport;

    public function __construct()
    {
        $this->input_id = new InputComponent('id', 'hidden');
        $this->input_departure_time = new InputComponent('departure_time', 'time');
        $this->input_arrival_time = new InputComponent('arrival_time', 'time');
        $this->select_type = (new TypeSelect('type'))->create();
        $this->select_origin = (new LocationSelect('origin'))->create();
        $this->select_destiny = (new LocationSelect('destiny'))->create();
        $this->select_transport = (new TransportSelect('transports'))->create(true);
        $this->input_id->setValue(0);
        $this->select_type->setRequired(true);
        $this->select_origin->setRequired(true);
        $this->select_destiny->setRequired(true);
        $this->input_arrival_time->setRequired(true);
        $this->input_departure_time->setRequired(true);
    }

    public function render()
    {
        $route = git_route_by_id((int) ($_GET['id'] ?? '0'));
        if ($route) {
            $this->input_id->setValue($route->id);
            $this->select_type->setValue($route->type->value);
            $this->select_origin->setValue($route->getOrigin()->id);
            $this->select_destiny->setValue($route->getDestiny()->id);
            $this->input_arrival_time->setValue(esc_attr($route->getArrivalTime()->format()));
            $this->input_departure_time->setValue(esc_attr($route->getDepartureTime()->format()));
            foreach ($route->getTransports() as $transport) {
                $this->select_transport->setValue($transport->id);
            }
        }
        ob_start();
        $action = add_query_arg(
            ['action' => 'git_edit_route'],
            admin_url('admin-ajax.php')
        );
        ?>
        <div id="form-route-message-container"></div>
        <form id="form-route" method="post" action="<?= esc_attr($action) ?>">
            <?php
            $this->input_id->render();
            wp_nonce_field('edit_route', 'nonce');
            ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $this->select_origin->getLabel('Origen')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_origin->styles->set('width', '100%');
                        $this->select_origin->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_destiny->getLabel('Destino')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_destiny->styles->set('width', '100%');
                        $this->select_destiny->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_type->getLabel('Tipo de ruta')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_type->styles->set('width', '100%');
                        $this->select_type->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_departure_time->getLabel('Hora de salida')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->input_departure_time->styles->set('width', '100%');
                        $this->input_departure_time->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_arrival_time->getLabel('Hora de llegada')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->input_arrival_time->setPlaceholder('Hora de llegada');
                        $this->input_arrival_time->styles->set('width', '100%');
                        $this->input_arrival_time->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_transport->getLabel('Transportes')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_transport->styles->set('width', '100%');
                        $this->select_transport->render();
                        $this->select_transport->getOptionsContainer()->render();
                        ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar ruta'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }
}
