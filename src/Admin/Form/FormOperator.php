<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\Implementation\CouponSelect;

final class FormOperator implements Displayer
{
    private InputComponent $input_id;
    private InputComponent $input_firstname;
    private InputComponent $input_lastname;
    private InputComponent $input_phone;
    private InputComponent $input_logo_sale;
    private InputComponent $input_coupons_counter;
    private InputComponent $input_coupons_limit;
    private MultipleSelectComponent $select_coupon;

    public function __construct()
    {
        $this->input_id = new InputComponent('id', 'hidden');
        $this->input_phone = new InputComponent('phone', 'text');
        $this->input_firstname = new InputComponent('firstname', 'text');
        $this->input_lastname = new InputComponent('lastname', 'text');
        $this->input_logo_sale = new InputComponent('logo_sale', 'checkbox');
        $this->input_coupons_counter = new InputComponent('coupons_counter', 'number');
        $this->input_coupons_limit = new InputComponent('coupons_limit', 'number');
        $this->select_coupon = (new CouponSelect('coupons'))->create(true);
        $this->input_firstname->set_required(true);
        $this->input_lastname->set_required(true);
        $this->input_phone->set_required(true);
        $this->input_coupons_counter->set_required(true);
        $this->input_coupons_limit->set_required(true);
        $this->input_id->set_value(-1);
        $this->input_coupons_counter->set_attribute('min', 0);
        $this->input_coupons_limit->set_attribute('min', 0);
    }

    public function display()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $operator = git_get_operator_by_id($id);
        if ($operator) {
            $this->input_id->set_value($operator->ID);
            if ($operator->logo_sale) {
                $this->input_logo_sale->set_attribute('checked', '');
            }
            $this->input_firstname->set_value($operator->first_name);
            $this->input_phone->set_value(get_user_meta($operator->ID, 'phone_number', true));
            $this->input_lastname->set_value($operator->last_name);
            $this->input_coupons_counter->set_value($operator->get_business_plan()['counter']);
            $this->input_coupons_limit->set_value($operator->get_business_plan()['limit']);
            $this->input_coupons_counter->set_attribute('max', $operator->get_business_plan()['limit']);

            foreach ($operator->get_coupons() as $coupon) {
                $this->select_coupon->set_value($coupon->ID);
            }
        }
        wp_enqueue_script(
            'git-operator-form',
            CENTRAL_BOOKING_URL . '/assets/js/admin/operator-form.js',
        );
        wp_localize_script(
            'git-operator-form',
            'gitOperatorForm',
            [
                'url' => admin_url('admin-ajax.php'),
                'action' => 'git_modify_operator',
                'nonce' => wp_create_nonce('git_operator_form_nonce'),
                'successRedirect' => admin_url('admin.php?page=central_operators'),
            ]
        );
        ob_start();
        ?>
        <div id="form-operator-message-container"></div>
        <form id="form-operator" method="post">
            <?php $this->input_id->display() ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th colspan="2">
                        <h3>Información del operador</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_firstname->get_label('Nombre')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_firstname->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_lastname->get_label('Apellidos')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_lastname->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_phone->get_label('Teléfono')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_phone->display() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3>Plan de cupones</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_coupon->get_label('Seleccionar cupones')->display() ?>
                    </th>
                    <td>
                        <?php $this->select_coupon->display() ?>
                        <?php $this->select_coupon->get_options_container()->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_coupons_counter->get_label('Contador de cupones')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_coupons_counter->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_coupons_limit->get_label('Límite de cupones')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_coupons_limit->display() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3>Marketing</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_logo_sale->get_label('Incluir logo de venta')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_logo_sale->display() ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar operador'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }
}
