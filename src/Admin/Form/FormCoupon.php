<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\Implementation\CouponSelect;
use CentralTickets\Components\InputComponent;

final class FormCoupon implements Displayer
{
    public function display()
    {
        $id = (int) ($_GET['id'] ?? '0');
        $coupon_input = (new CouponSelect('coupon'))->create();
        $logo_sale_input = new InputComponent('brand_media', 'text');
        $coupon_input->set_required(true);
        $logo_sale_input->set_required(true);
        $coupon = git_get_coupon_by_id($id);
        if ($coupon) {
            $coupon_input->set_value($id);
            $logo_sale_input->set_value(git_get_url_logo_by_coupon($coupon));
        }
        ob_start();
        ?>
        <form id="form-location" method="post" action="<?= esc_url(admin_url('admin-ajax.php?action=git_edit_coupon')) ?>">
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tbody>
                    <tr class="form-field">
                        <th scope="row">
                            <?= $coupon_input->get_label('Comercializador')->compact() ?>
                        </th>
                        <td>
                            <?= $coupon_input->compact() ?>
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
