<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\View\TableRoutes;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\Implementation\LocationSelect;
use CentralTickets\Components\Implementation\TransportSelect;
use CentralTickets\Components\Implementation\TypeSelect;

final class FormRoute implements Displayer
{
    private InputComponent $input_id;
    private InputComponent $input_distance;
    private InputComponent $input_duration;
    private SelectComponent $select_origin;
    private SelectComponent $select_destiny;
    private SelectComponent $select_type;
    private InputComponent $input_departure_time;
    private MultipleSelectComponent $select_transport;

    public function __construct()
    {
        $this->input_id = new InputComponent('id', 'hidden');
        $this->input_distance = new InputComponent('distance', 'number');
        $this->input_duration = new InputComponent('duration_trip', 'time');
        $this->input_departure_time = new InputComponent('departure_time', 'time');
        $this->select_type = (new TypeSelect('type'))->create();
        $this->select_origin = (new LocationSelect('origin'))->create();
        $this->select_destiny = (new LocationSelect('destiny'))->create();
        $this->select_transport = (new TransportSelect('transports'))->create(true);
        $this->input_id->set_value(0);
        $this->select_type->set_required(true);
        $this->select_origin->set_required(true);
        $this->input_distance->set_required(true);
        $this->input_duration->set_required(true);
        $this->select_destiny->set_required(true);
        $this->input_departure_time->set_required(true);
        $this->input_distance->set_attribute('min', '0');
    }

    public function display()
    {
        $route = git_get_route_by_id((int) ($_GET['id'] ?? '0'));
        if ($route) {
            $this->input_id->set_value($route->id);
            $this->select_type->set_value($route->type);
            $this->input_distance->set_value($route->distance_km);
            $this->select_origin->set_value($route->get_origin()->id);
            $this->select_destiny->set_value($route->get_destiny()->id);
            $this->input_duration->set_value(esc_attr($route->duration_trip));
            $this->input_departure_time->set_value(esc_attr($route->departure_time));
            foreach ($route->get_transports() as $transport) {
                $this->select_transport->set_value($transport->id);
            }
        }
        ob_start();
        ?>
        <div id="form-route-message-container"></div>
        <form id="form-route" method="post"
            action="<?= add_query_arg(['action' => 'git_edit_route'], admin_url('admin-ajax.php')) ?>">
            <?php
            $this->input_id->display();
            wp_nonce_field('edit_route', 'nonce');
            ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $this->select_origin->get_label('Origen')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_origin->styles->set('width', '100%');
                        $this->select_origin->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_destiny->get_label('Destino')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_destiny->styles->set('width', '100%');
                        $this->select_destiny->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_type->get_label('Tipo de ruta')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_type->styles->set('width', '100%');
                        $this->select_type->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_duration->get_label('Tiempo de viaje')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->input_duration->set_placeholder('Tiempo de viaje');
                        $this->input_duration->styles->set('width', '100%');
                        $this->input_duration->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_departure_time->get_label('Hora de salida')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->input_departure_time->styles->set('width', '100%');
                        $this->input_departure_time->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_distance->get_label('Distancia en km')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->input_distance->styles->set('width', '100%');
                        $this->input_distance->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_transport->get_label('Transportes')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_transport->styles->set('width', '100%');
                        $this->select_transport->display();
                        $this->select_transport->get_options_container()->display();
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
