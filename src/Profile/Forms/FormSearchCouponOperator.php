<?php
namespace CentralTickets\Profile\Forms;

use CentralTickets\Components\Component;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\Implementation\CouponSelect;

class FormSearchCouponOperator implements Component
{
    private SelectComponent $coupon_select;
    private InputComponent $date_start_input;
    private InputComponent $date_end_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $is_operator = git_current_user_has_role('operator');
        $this->coupon_select = (new CouponSelect('coupon', $is_operator ? get_current_user_id() : null))->create();
        $this->date_end_input = new InputComponent('date_end', 'date');
        $this->date_start_input = new InputComponent('date_start', 'date');

        $this->date_start_input->set_required(true);
        $this->date_end_input->set_required(true);
        $this->coupon_select->set_required(true);

        $coupon = $_GET['coupon'] ?? null;
        $date_end = $_GET['date_end'] ?? null;
        $date_start = $_GET['date_start'] ?? null;

        if ($coupon) {
            $this->coupon_select->set_value($coupon);
        }
        if ($date_end) {
            $this->date_end_input->set_value($date_end);
        }
        if ($date_start) {
            $this->date_start_input->set_value($date_start);
        }
    }

    public function compact()
    {
        ob_start();
        ?>
        <div class="my-3">
            <form method="get">
                <input type="hidden" name="page" value="git_operator_panel">
                <input type="hidden" name="tab" value="coupons">
                <div class="row">
                    <div class="col">
                        <div class="form-floating mb-3">
                            <?php echo $this->coupon_select->compact(); ?>
                            <?php echo $this->coupon_select->get_label('Cupón')->compact(); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-floating mb-3">
                            <?php echo $this->date_start_input->compact(); ?>
                            <?php echo $this->date_start_input->get_label('Fecha de inicio')->compact(); ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-floating mb-3">
                            <?php echo $this->date_end_input->compact(); ?>
                            <?php echo $this->date_end_input->get_label('Fecha de fin')->compact(); ?>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
                <!-- <input class="btn btn-primary" type="submit" value="Buscar"> -->
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
