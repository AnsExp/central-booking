<?php
namespace CentralTickets\Profile\Forms;

use CentralTickets\Components\Component;
use CentralTickets\Components\Implementation\TicketStatusSelect;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Operator;
use CentralTickets\Persistence\CouponRepository;
use CentralTickets\Persistence\TicketRepository;

class FormEditCouponOperator implements Component
{
    private SelectComponent $status_select;
    private InputComponent $code_input;
    private InputComponent $file_input;
    private InputComponent $amount_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->status_select = (new TicketStatusSelect('status'))->create();
        $this->code_input = new InputComponent('code', 'text');
        $this->file_input = new InputComponent('file', 'file');
        $this->amount_input = new InputComponent('amount', 'number');
        $this->status_select->set_required(true);
        $this->code_input->set_required(true);

        $this->amount_input->set_attribute('min', '0');
        $this->file_input->set_attribute(
            'accept',
            join(',', git_get_setting('operator_file_extensions', []))
        );
        $this->file_input->styles->set('display', 'none');
    }

    public function compact()
    {
        ob_start();
        if (!$this->can_edit()) {
            ?>
            <p>No puedes editar este ticket por una de las siguientes razones:</p>
            <ul>
                <li>No estás logueado.</li>
                <li>El ticket no existe.</li>
                <li>El ticket no tiene un cupón asignado.</li>
                <li>No tienes permisos para editar el cupón asignado al ticket.</li>
            </ul>
            <p>Si crees que esto es un error, por favor contacta con el soporte. Caso contrario, intenta <a
                    href="<?= esc_url(remove_query_arg(['action', 'ticket_id'])) ?>">buscar</a> otro ticket.</p>
            <?php
        } else {
            $ticket = (new TicketRepository())->find((int) $_GET['ticket_id']);
            $proof_payment = $ticket->get_meta('proof_payment');
            $this->status_select->set_value($ticket->status);
            if (!git_current_user_has_role('administrator') && $ticket->status !== TicketConstants::PENDING) {
                $this->status_select->set_attribute('disabled', '');
                $this->code_input->set_attribute('disabled', '');
            }
            $this->code_input->set_value($proof_payment['code'] ?? '');
            $this->amount_input->set_value(isset($proof_payment['amount']) ? floatval($proof_payment['amount']) / 100 : 0);
            $this->amount_input->set_attribute('max', $ticket->total_amount / 100);
            $this->amount_input->set_attribute('step', 0.1);

            wp_enqueue_script(
                'git-operator-form-coupon-status',
                CENTRAL_BOOKING_URL . '/assets/js/operator/form-coupon-status.js',
                ['jquery']
            );
            wp_localize_script(
                'git-operator-form-coupon-status',
                'formCouponStatus',
                [
                    'fileInputId' => $this->file_input->id,
                    'codeInputId' => $this->code_input->id,
                    'amountInputId' => $this->amount_input->id,
                    'statusSelectId' => $this->status_select->id,
                    'checkPassengerClass' => 'check-passenger-approved',
                    'fileRequiredIn' => [TicketConstants::PARTIAL, TicketConstants::PAYMENT],
                    'statusToRemove' => [count($ticket->get_passengers()) === 1 ? TicketConstants::PARTIAL : null],
                ]
            );
            ?>
            <a id="link_to_search_pane" class="btn btn-primary mb-3"
                href="<?= esc_url(remove_query_arg(['action', 'ticket_id'])) ?>">
                <i class="bi bi-arrow-left"></i> Regresar al buscador
            </a>
            <table class="table table-bordered">
                <tr>
                    <td><b>Código de cupon:</b></td>
                    <td><?= $ticket->get_coupon()->post_title ?></td>
                </tr>
                <tr>
                    <td><b>Precio:</b></td>
                    <td><?= git_currency_format($ticket->total_amount, true) ?></td>
                </tr>
                <tr>
                    <td><b>Fecha de compra:</b></td>
                    <td><?= git_datetime_format($ticket->get_order()->get_date_created()->format('Y-m-d H:i:s')) ?></td>
                </tr>
                <tr>
                    <td><b>Número de ticket:</b></td>
                    <?php if (git_current_user_has_role('administrator')): ?>
                        <td>
                            <a target="_blank" href="<?= admin_url('admin.php?page=git_tickets&id=' . $ticket->id) ?>">
                                #<?= $ticket->id ?>
                            </a>
                        </td>
                    <?php else: ?>
                        <td>#<?= $ticket->id ?></td>
                    <?php endif; ?>
                </tr>
            </table>
            <form id="form-coupon-status" method="post" class="p-3"
                action="<?= esc_url(admin_url('admin-ajax.php?action=git_update_coupon_status')) ?>">
                <div id="message-success-container" class="alert alert-success" role="alert" style="display: none;">
                </div>
                <div id="message-danger-container" class="alert alert-danger" role="alert" style="display: none;">
                </div>
                <div id="error-container"></div>
                <?php $this->file_input->display() ?>
                <input type="hidden" name="ticket_id" value="<?= $ticket->id ?>">
                <input type="hidden" name="has_previous" value="<?= git_serialize(!empty($proof_payment)) ?>">
                <div class="mb-3">
                    <?= $this->status_select->get_label('Estado')->compact(); ?>
                    <?= $this->status_select->compact(); ?>
                </div>
                <div style="<?= $ticket->status === TicketConstants::PARTIAL ? 'display: block;' : 'display: none;' ?>"
                    id="partial-options-container">
                    <div class="mb-3 p-3 bg-success bg-opacity-25 border border-success rounded">
                        <span class="fs-4 fw-medium">Pasajeros aprobados a viajar:</span>
                        <div id="approved-passengers-container">
                            <?php foreach ($ticket->get_passengers() as $passenger): ?>
                                <div class="form-check">
                                    <input class="form-check-input check-passenger-approved" <?= $passenger->approved ? 'checked' : '' ?>
                                        type="checkbox" id="passenger_<?= $passenger->id ?>"
                                        <?= $ticket->status === TicketConstants::PENDING || git_current_user_has_role('administrator') ? '' : 'disabled' ?> name="approved_passengers[]" value="<?= $passenger->id ?>">
                                    <label class="form-check-label" for="passenger_<?= $passenger->id ?>">
                                        <?= esc_html($passenger->name) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <?= $this->amount_input->get_label('Monto')->compact(); ?>
                        <?= $this->amount_input->compact(); ?>
                    </div>
                </div>
                <div id="section-payment">
                    <div class="mb-3">
                        <?= $this->code_input->get_label('Código')->compact(); ?>
                        <?= $this->code_input->compact(); ?>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="m-3">
                            <span class="text-secondary" id="proof_payment_name_display">
                                <?= esc_html(empty($proof_payment['name']) ? 'Subir archivo...' : $proof_payment['name']) ?>
                            </span>
                        </div>
                        <div class="btn-group">
                            <a class="btn btn-outline-success" href="<?= $proof_payment['path'] ?>" target="_blank">
                                <i class="bi bi-eye"></i> Recupera el comprobante
                            </a>
                            <button id="button_upload_proof_payment" type="button" class="btn btn-outline-primary">
                                <i class="bi bi-upload"></i> Sube el comprobante
                            </button>
                        </div>
                    </div>
                </div>
                <?php if ($ticket->status === TicketConstants::PENDING || git_current_user_has_role('administrator')): ?>
                    <button id="button-submit-form-coupon-status" type="submit" class="btn btn-primary">Guardar</button>
                <?php endif; ?>
            </form>
            <script>
                selectElement = document.getElementById('<?= $this->status_select->id ?>');
                selectElement.addEventListener('change', function (event) {
                    const selectedValue = event.target.value;
                    if (['<?= TicketConstants::CANCEL ?>', '<?= TicketConstants::PENDING ?>'].includes(selectedValue)) {
                        document.getElementById('section-payment').style.display = 'none';
                        document.getElementById('<?= $this->code_input->id ?>').required = false;
                    } else {
                        document.getElementById('section-payment').style.display = 'block';
                        document.getElementById('<?= $this->code_input->id ?>').required = true;
                    }
                });
                selectElement.dispatchEvent(new Event('change'));
                document.getElementById('button_upload_proof_payment').addEventListener('click', function () {
                    document.getElementById('<?= $this->file_input->id ?>').click();
                    const select = document.getElementById('<?= $this->status_select->id ?>');
                });
            </script>
            <?php
        }
        return ob_get_clean();
    }

    private function can_edit()
    {
        if (!git_user_logged_in()) {
            return false;
        }

        if (!isset($_GET['ticket_id'])) {
            return false;
        }

        $ticket_id = (int) $_GET['ticket_id'];
        $repository = new TicketRepository();
        $ticket = $repository->find($ticket_id);

        if ($ticket === null) {
            return false;
        }

        $coupon = $ticket->get_coupon();

        if ($coupon === null) {
            return false;
        }

        if (git_current_user_has_role('administrator')) {
            return true;
        }

        if (!git_current_user_has_role('operator')) {
            return false;
        }

        $coupon_repository = new CouponRepository();
        $coupons = $coupon_repository->get_coupons_by_operator(new Operator(get_current_user_id()));

        foreach ($coupons as $coupon_operator) {
            if ($coupon_operator->ID === $coupon->ID) {
                return true;
            }
        }

        return false;
    }
}
