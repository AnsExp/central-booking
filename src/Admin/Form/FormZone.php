<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;

final class FormZone implements DisplayerInterface
{
    public function render()
    {
        $input_id = new InputComponent('id', 'hidden');
        $input_name = new InputComponent('name', 'text');

        $input_id->setValue(0);
        if (isset($_GET['id'])) {
            $zone = git_zone_by_id((int) $_GET['id']);
            if ($zone) {
                $input_id->setValue($zone->id);
                $input_name->setValue($zone->name);
            }
        }
        $input_name->setPlaceholder('Zona');
        $input_name->setRequired(true);
        $input_name->styles->set('width', '100%');
        ob_start();
        $action = admin_url('admin-ajax.php?action=git_edit_zone');
        ?>
        <form method="post" action="<?= $action ?>">
            <?php $input_id->render() ?>
            <?php wp_nonce_field('edit_zone', 'nonce') ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $input_name->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $input_name->render() ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }
}
