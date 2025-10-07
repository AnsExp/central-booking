<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\Implementation\ZoneSelect;

final class FormLocation implements Displayer
{
    public function display()
    {
        $input_id = new InputComponent('id', 'hidden');
        $input_nonce = new InputComponent('nonce', 'hidden');
        $input_name = new InputComponent('name', 'text');
        $select_zone = (new ZoneSelect)->create();

        $input_id->set_value(0);
        $input_nonce->set_value(wp_create_nonce('update_location'));
        $input_name->set_placeholder('Ubicación');
        $input_name->set_required(true);
        $select_zone->set_required(true);
        $input_name->styles->set('width', '100%');
        $select_zone->styles->set('width', '100%');

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $location = git_get_location_by_id($id);
        if ($location) {
            $input_id->set_value($location->id);
            $input_name->set_value(esc_html($location->name));
            $select_zone->set_value($location->get_zone()->id);
        }
        ob_start();
        ?>
        <div id="form-location-message-container"></div>
        <form id="form-location" method="post" action="<?= esc_url(admin_url('admin-ajax.php?action=git_update_location')) ?>">
            <?php $input_id->display() ?>
            <?php $input_nonce->display() ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $input_name->get_label('Nombre')->display() ?>
                    </th>
                    <td>
                        <?php $input_name->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_zone->get_label('Zona')->display() ?>
                    </th>
                    <td>
                        <?php $select_zone->display() ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar ubicación'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }
}
