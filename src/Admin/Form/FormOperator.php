<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\Implementation\GUI\CouponSelect;

final class FormOperator implements DisplayerInterface
{
    private InputComponent $input_id;
    private InputComponent $input_firstname;
    private InputComponent $input_lastname;
    private InputComponent $input_phone;
    private InputComponent $input_brand_media;
    private InputComponent $input_coupons_counter;
    private InputComponent $input_coupons_limit;
    private MultipleSelectComponent $select_coupon;

    public function __construct()
    {
        $this->input_id = new InputComponent('id', 'hidden');
        $this->input_phone = new InputComponent('phone', 'text');
        $this->input_firstname = new InputComponent('firstname', 'text');
        $this->input_lastname = new InputComponent('lastname', 'text');
        $this->input_brand_media = new InputComponent('brand_media');
        $this->input_coupons_counter = new InputComponent('coupons_counter[index]', 'number');
        $this->input_coupons_limit = new InputComponent('coupons_counter[limit]', 'number');
        $this->select_coupon = (new CouponSelect('coupons'))->create(true);
        $this->input_firstname->setRequired(true);
        $this->input_lastname->setRequired(true);
        $this->input_phone->setRequired(true);
        $this->input_coupons_counter->setRequired(true);
        $this->input_coupons_limit->setRequired(true);
        $this->input_id->setValue(-1);
        $this->input_coupons_counter->attributes->set('min', 0);
        $this->input_coupons_limit->attributes->set('min', 0);
    }

    public function render()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $operator = git_operator_by_id($id);
        if ($operator) {
            $this->input_id->setValue($operator->getUser()->ID);
            $this->input_firstname->setValue($operator->getUser()->first_name);
            $this->input_phone->setValue($operator->getPhone());
            $this->input_brand_media->setValue($operator->getBrandMedia());
            $this->input_lastname->setValue($operator->getUser()->last_name);
            $this->input_coupons_counter->setValue($operator->getBusinessPlan()['counter']);
            $this->input_coupons_limit->setValue($operator->getBusinessPlan()['limit']);
            $this->input_coupons_counter->attributes->set('max', $operator->getBusinessPlan()['limit']);
            foreach ($operator->getCoupons() as $coupon) {
                $this->select_coupon->setValue($coupon->ID);
            }
        }
        ob_start();
        $action = add_query_arg(
            ['action' => 'git_edit_operator'],
            admin_url('admin-ajax.php')
        );
        ?>
        <div id="form-operator-message-container"></div>
        <form id="form-operator" method="post" action="<?= $action ?>">
            <?php
            $this->input_id->render();
            wp_nonce_field('git_operator_form_nonce', 'nonce');
            ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th colspan="2">
                        <h3>Información del operador</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_firstname->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_firstname->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_lastname->getLabel('Apellidos')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_lastname->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_phone->getLabel('Teléfono')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_phone->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3>Plan de cupones</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_coupon->getLabel('Seleccionar cupones')->render() ?>
                    </th>
                    <td>
                        <?php $this->select_coupon->render() ?>
                        <?php $this->select_coupon->getOptionsContainer()->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_coupons_counter->getLabel('Contador de cupones')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_coupons_counter->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_coupons_limit->getLabel('Límite de cupones')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_coupons_limit->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3>Marketing</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_brand_media->getLabel('Medio de marca')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_brand_media->render() ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar operador'); ?>
        </form>
        <script>
            jQuery(document).ready(function ($) {
                $('#<?= $this->input_coupons_limit->id ?>').on('input', function (e) {
                    const limit = parseInt($('#<?= $this->input_coupons_limit->id ?>').val());
                    $('#<?= $this->input_coupons_counter->id ?>').attr('max', limit);
                });
            });
        </script>
        <?php
        echo ob_get_clean();
    }
}
