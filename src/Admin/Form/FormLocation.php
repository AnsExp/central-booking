<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Data\Services\LocationService;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\GUI\ZoneSelect;

final class FormLocation implements DisplayerInterface
{
    private LocationService $locationService;

    public function __construct()
    {
        $this->locationService = new LocationService();
    }
    public function render()
    {
        $input_id = new InputComponent('id', 'hidden');
        $input_name = new InputComponent('name', 'text');
        $select_zone = (new ZoneSelect())->create();

        $input_id->setValue(0);
        $input_name->setPlaceholder('Ubicación');
        $input_name->setRequired(true);
        $select_zone->setRequired(true);
        $input_name->styles->set('width', '100%');
        $select_zone->styles->set('width', '100%');

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $location = git_location_by_id($id);
        if ($location) {
            $input_id->setValue($location->id);
            $input_name->setValue(esc_html($location->name));
            $select_zone->setValue($location->getZone()->id);
        }
        ob_start();
        $action = add_query_arg(
            ['action' => 'git_edit_location'],
            admin_url('admin-ajax.php')
        );
        ?>
        <div id="form-location-message-container"></div>
        <form id="form-location" method="post" action="<?= esc_url($action) ?>">
            <?php $input_id->render() ?>
            <?php wp_nonce_field('edit_location', 'nonce') ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $input_name->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $input_name->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_zone->getLabel('Zona')->render() ?>
                    </th>
                    <td>
                        <?php $select_zone->render() ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }
}
