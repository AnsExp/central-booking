<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;
use CentralTickets\Persistence\ZoneRepository;
use CentralTickets\Components\InputComponent;

final class FormZone implements Displayer
{
    public function __construct()
    {
    }
    
    public function display()
    {
        $input_id = new InputComponent('id', 'hidden');
        $input_name = new InputComponent('name', 'text');
        $input_nonce = new InputComponent('nonce', 'hidden');

        $input_id->set_value('0');
        $input_nonce->set_value(wp_create_nonce('create_zone'));
        if (isset($_GET['id'])) {
            $repository = new ZoneRepository();
            $zone = $repository->find((int) $_GET['id']);
            if ($zone) {
                $input_id->set_value($zone->id);
                $input_name->set_value($zone->name);
            }
        }
        $input_name->set_placeholder('Zona');
        $input_name->set_required(true);
        $input_name->styles->set('width', '100%');
        ob_start();
        ?>
        <form method="post" action="<?= admin_url('admin-ajax.php?action=git_update_zone') ?>">
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
            </table>
            <?= get_submit_button('Guardar Zona'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }
}
