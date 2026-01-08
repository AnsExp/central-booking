<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Data\Services\ServiceService;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\Implementation\GUI\TransportSelect;

final class FormService implements DisplayerInterface
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

    public function render()
    {
        $this->input_name->setRequired(true);
        $this->input_price->setRequired(true);
        $this->input_icon->setRequired(true);
        if ($_GET['id'] ?? -1 > 0) {
            $repository = new ServiceService();
            $service = $repository->findById((int) $_GET['id']);
            if ($service !== null) {
                $this->input_id->setValue($service->id);
                $this->input_name->setValue($service->name);
                $this->input_icon->setValue($service->icon);
                $this->input_price->setValue($service->price);
                foreach ($service->getTransports() as $transport) {
                    $this->select_transport->setValue($transport->id);
                }
            }
        }
        ob_start();
        ?>
        <div id="form-message-container"></div>
        <form id="form-service" method="post" action="<?= admin_url('admin-ajax.php?action=git_edit_service') ?>">
            <?php $this->input_id->render() ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $this->input_name->getLabel('Nombre')->render(); ?>
                    </th>
                    <td>
                        <?php $this->input_name->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_price->getLabel('Precio')->render(); ?>
                    </th>
                    <td>
                        <?php $this->input_price->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_icon->getLabel('Icono')->render(); ?>
                    </th>
                    <td>
                        <?php $this->input_icon->render(); ?>
                        <p>Ingrese la direccion URL del ícono.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_transport->getLabel('Transportes')->render(); ?>
                    </th>
                    <td>
                        <?php
                        $this->select_transport->render();
                        $this->select_transport->getOptionsContainer()->render();
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
