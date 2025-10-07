<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\Implementation\TransportSelect;
use CentralTickets\Persistence\ServiceRepository;

final class FormService implements Displayer
{
    private InputComponent $input_id;
    private InputComponent $input_name;
    private InputComponent $input_price;
    private InputComponent $input_icon;
    private MultipleSelectComponent $select_transport;

    public function __construct()
    {
        $this->input_id = new InputComponent('id', 'hidden');
        $this->input_icon = new InputComponent('icon', 'text');
        $this->input_name = new InputComponent('name', 'text');
        $this->input_price = new InputComponent('price', 'number');
        $this->select_transport = (new TransportSelect('transport'))->create(true);
    }

    public function display()
    {
        wp_enqueue_script(
            'central-tickets-admin-service-form',
            CENTRAL_BOOKING_URL . '/assets/js/admin/service-form.js',
            ['jquery'],
            null,
            true
        );
        wp_localize_script(
            'central-tickets-admin-service-form',
            'formService',
            [
                'hook' => 'git_service_form',
                'url' => admin_url('admin-ajax.php'),
                'successRedirect' => admin_url('admin.php?page=git_services'),
            ]
        );
        $this->input_name->set_required(true);
        $this->input_price->set_required(true);
        $this->input_icon->set_required(true);
        if ($_GET['id'] ?? -1 > 0) {
            $repository = new ServiceRepository;
            $service = $repository->find((int) $_GET['id']);
            if ($service !== null) {
                $this->input_id->set_value($service->id);
                $this->input_name->set_value($service->name);
                $this->input_icon->set_value($service->icon);
                $this->input_price->set_value($service->price);
                foreach ($service->get_transports() as $transport) {
                    $this->select_transport->set_value($transport->id);
                }
            }
        }
        ob_start();
        ?>
        <div id="form-message-container"></div>
        <form id="form-service" method="post">
            <?php $this->input_id->display() ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $this->input_name->get_label('Nombre')->display(); ?>
                    </th>
                    <td>
                        <?php $this->input_name->display(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_price->get_label('Precio')->display(); ?>
                    </th>
                    <td>
                        <?php $this->input_price->display(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_icon->get_label('Icono')->display(); ?>
                    </th>
                    <td>
                        <?php $this->input_icon->display(); ?>
                        <p>Ingrese la direccion URL del ícono.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_transport->get_label('Transportes')->display(); ?>
                    </th>
                    <td>
                        <?php
                        $this->select_transport->display();
                        $this->select_transport->get_options_container()->display();
                        ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar servicio'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }
}
