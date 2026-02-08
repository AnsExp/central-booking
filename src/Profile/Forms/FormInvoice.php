<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\GUI\DisplayerInterface;

class FormInvoice implements DisplayerInterface
{
    public function render()
    {
        $coupon_select = git_coupon_select_field('coupon');
        $date_start_input = git_input_field([
            'name' => 'date_start',
            'type' => 'date',
            'value' => $_GET['date_start'] ?? null,
            'required' => true,
        ]);
        $date_end_input = git_input_field([
            'name' => 'date_end',
            'type' => 'date',
            'value' => $_GET['date_end'] ?? null,
            'required' => true,
        ]);
        $operator_select = git_operator_select_field('operator');
        $coupon_select->setValue($_GET['coupon'] ?? '');
        $operator_select->setValue($_GET['operator'] ?? '');
        $operator_select->setRequired(true);
        $date_start_input->setRequired(true);
        $date_end_input->setRequired(true);
        $operator_select->setValue(get_current_user_id());
        if (git_current_user_has_role(UserRole::OPERATOR) === true) {
            $coupons = git_operator_by_id(get_current_user_id())->getCoupons();
            foreach ($coupons as $coupon) {
                $coupon_select->removeOption($coupon->ID);
            }
        }
        ?>
        <form method="get" class="p-3">
            <input type="hidden" name="tab" value="<?= $_GET['tab'] ?? '' ?>">
            <?php if (git_current_user_has_role(UserRole::ADMINISTRATOR) === true): ?>
                <div class="row mb-3">
                    <div class="col git-form-section">
                        <div class="git-form-group">
                            <?= $operator_select->getLabel('Operador')->compact(); ?>
                            <?= $operator_select->compact(); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="operator" value="<?= get_current_user_id() ?>">
            <?php endif; ?>
            <div class="row mb-3">
                <div class="git-form-group git-form-section">
                    <?= $coupon_select->getLabel('Cupón')->compact(); ?>
                    <?= $coupon_select->compact(); ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col git-form-section">
                    <div class="git-form-group">
                        <?= $date_start_input->getLabel('Fecha desde')->compact(); ?>
                        <?= $date_start_input->compact(); ?>
                    </div>
                </div>
                <div class="col git-form-section">
                    <div class="git-form-group">
                        <?= $date_end_input->getLabel('Fecha hasta')->compact(); ?>
                        <?= $date_end_input->compact(); ?>
                    </div>
                </div>
            </div>
            <div class="git-form-actions">
                <button type="submit" class="git-btn git-btn-primary">Buscar</button>
            </div>
        </form>
        <?php
    }
}
