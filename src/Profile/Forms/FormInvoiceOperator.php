<?php
namespace CentralTickets\Profile\Forms;

use CentralTickets\Components\Component;
use CentralTickets\Components\Implementation\CouponSelect;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\InputFloatingLabelComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\Implementation\OperatorSelect;
use CentralTickets\Constants\UserConstants;

class FormInvoiceOperator implements Component
{
    private SelectComponent $coupon_select;
    private SelectComponent $operator_select;
    private InputComponent $operator_input;
    private SelectComponent $month_select;
    private SelectComponent $year_select;

    public function __construct()
    {
        if (git_current_user_has_role(UserConstants::ADMINISTRATOR)) {
            $this->coupon_select = (new CouponSelect('coupon'))->create();
        } else {
            $this->coupon_select = (new CouponSelect('coupon', get_current_user_id()))->create();
        }
        $this->operator_select = (new OperatorSelect('operator'))->create();
        $this->operator_input = new InputComponent('operator', 'hidden');
        $this->month_select = $this->create_select_month();
        $this->year_select = $this->create_select_year();

        $this->coupon_select->set_value($_GET['coupon'] ?? '');
        $this->operator_select->set_value($_GET['operator'] ?? '');
        $this->operator_input->set_value(get_current_user_id());

        $this->operator_select->set_required(true);
        $this->month_select->set_required(true);
        $this->year_select->set_required(true);
    }

    public function compact()
    {
        $coupon_floating = new InputFloatingLabelComponent($this->coupon_select, 'Cupón');
        $operator_floating = new InputFloatingLabelComponent($this->operator_select, 'Operador');
        $month_floating = new InputFloatingLabelComponent($this->month_select, 'Mes de facturación');
        $year_floating = new InputFloatingLabelComponent($this->year_select, 'Año de facturación');
        ob_start();
        $this->operator_select->set_value(get_current_user_id());
        ?>
        <form method="get" class="p-3">
            <input type="hidden" name="tab" value="sales">
            <div class="row mb-3">
                <div class="col">
                    <?php
                    if (git_current_user_has_role('administrator'))
                        $operator_floating->display();
                    else
                        $this->operator_input->display();
                    ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <?= $coupon_floating->compact() ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <?= $month_floating->compact(); ?>
                </div>
                <div class="col">
                    <?= $year_floating->compact(); ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
        <?php
        return ob_get_clean();
    }

    private function create_select_month()
    {
        $select = new SelectComponent('invoice_month');
        $select->add_option('Seleccione un mes...', '');
        $months = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];
        foreach ($months as $value => $label) {
            $select->add_option($label, $value);
        }
        if (isset($_GET['invoice_month']) && $_GET['invoice_month'] !== '') {
            $select->set_value($_GET['invoice_month'] ?? '');
        } else {
            $select->set_value(date('m'));
        }
        return $select;
    }

    private function create_select_year()
    {
        $select = new SelectComponent('invoice_year');
        $select->add_option('Seleccione un año...', '');
        $current_year = date('Y');
        $years = range($current_year, $current_year - 5);
        foreach ($years as $year) {
            $select->add_option($year, $year);
        }
        if (isset($_GET['invoice_year']) && $_GET['invoice_year'] !== '') {
            $select->set_value($_GET['invoice_year'] ?? '');
        } else {
            $select->set_value(date('Y'));
        }
        return $select;
    }
}
