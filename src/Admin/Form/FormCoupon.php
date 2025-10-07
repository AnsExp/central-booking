<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\Implementation\OperatorSelect;
use CentralTickets\Components\InputComponent;

final class FormCoupon implements Displayer
{
    public function display()
    {
        $id = (int) ($_GET['id'] ?? '0');
        $id_input = new InputComponent('id', 'hidden');
        $code_input = new InputComponent('code', 'text');
        $logo_sale_input = new InputComponent('logo_sale', 'text');
        $operator_select = (new OperatorSelect('operator'))->create();
        $code_input->set_required(true);
        $logo_sale_input->set_required(true);
        $operator_select->set_required(true);
        $coupon = git_get_coupon_by_id($id);
        if ($coupon) {
            $id_input->set_value($coupon->ID);
            $code_input->set_value($coupon->post_title);
            $logo_sale_input->set_value(get_post_meta($coupon->ID, 'logo_sale', true));
            $operator_id = git_get_operator_by_coupon($coupon)?->ID ?? null;
            $operator_select->set_value($operator_id);
        }
        ob_start();
        ?>
        <form id="form-location" method="post" action="<?= esc_url(admin_url('admin-ajax.php?action=git_edit_coupon')) ?>">
            <?= $id_input->compact() ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tbody>
                    <tr class="form-field">
                        <th scope="row">
                            <?= $code_input->get_label('Código')->compact() ?>
                        </th>
                        <td>
                            <?= $code_input->compact() ?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <?= $operator_select->get_label('Operador')->compact() ?>
                        </th>
                        <td>
                            <?= $operator_select->compact() ?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <?= $logo_sale_input->get_label('Logo de la venta')->compact() ?>
                        </th>
                        <td>
                            <?= $logo_sale_input->compact() ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="button button-primary">Guardar</button>
        </form>
        <?php
        echo ob_get_clean();
    }
}
