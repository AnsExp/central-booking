<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\UserConstants;
use CentralBooking\Data\Operator;
use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\TicketStatusSelect;
use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\View\TableTickets;

class FormEditCouponOperator implements ComponentInterface
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
        $this->status_select->setRequired(true);
        $this->code_input->setRequired(true);

        $this->amount_input->attributes->set('min', '0');
        $this->file_input->attributes->set(
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
            $ticket = git_ticket_by_id((int) $_GET['ticket_id']);
            $proof_payment = $ticket->getProofPayment();
            $this->status_select->setValue($ticket->status->value);
            if (!git_current_user_has_role(UserConstants::ADMINISTRATOR) && $ticket->status !== TicketStatus::PENDING) {
                $this->status_select->attributes->set('disabled', '');
                $this->code_input->attributes->set('disabled', '');
            }
            $this->code_input->setValue($proof_payment->code);
            $this->amount_input->setValue(isset($proof_payment->amount) ? floatval($proof_payment->amount) / 100 : 0);
            $this->amount_input->attributes->set('max', $ticket->total_amount / 100);
            $this->amount_input->attributes->set('step', 0.1);

            $action = esc_url(
                add_query_arg(
                    ['action' => 'git_edit_coupon_status'],
                    admin_url('admin-ajax.php')
                )
            );
            ?>
            <a id="link_to_search_pane" class="btn btn-primary mb-3"
                href="<?= esc_url(remove_query_arg(['action', 'ticket_id'])) ?>">
                <i class="bi bi-arrow-left"></i> Regresar al buscador
            </a>
            <table class="table table-bordered">
                <tr>
                    <td><b>Código de cupon:</b></td>
                    <td><?= $ticket->getCoupon()->post_title ?></td>
                </tr>
                <tr>
                    <td><b>Precio:</b></td>
                    <td><?= git_currency_format($ticket->total_amount, true) ?></td>
                </tr>
                <tr>
                    <td><b>Fecha de compra:</b></td>
                    <td><?= git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s')) ?></td>
                </tr>
                <tr>
                    <td><b>Número de ticket:</b></td>
                    <?php if (git_current_user_has_role('administrator')): ?>
                        <td>
                            <a target="_blank" href="<?= esc_url(AdminRouter::get_url_for_class(
                                TableTickets::class,
                                ['id' => $ticket->id]
                            )) ?>">
                                #<?= $ticket->id ?>
                            </a>
                        </td>
                    <?php else: ?>
                        <td>#<?= $ticket->id ?></td>
                    <?php endif; ?>
                </tr>
            </table>
            <?php if (isset($_GET['success']) && $_GET['success'] === 'true'): ?>
                <div class="p-3 bg-success bg-opacity-25 border border-success rounded" role="alert">
                    <p>Se ha actualizado el estado del cupón correctamente. Será redirigido en breve.</p>
                </div>
                <script>
                    setTimeout(() => {
                        location.replace('<?= esc_url(remove_query_arg(['action', 'ticket_id', 'success'])) ?>');
                    }, 2000);
                </script>
            <?php elseif (isset($_GET['success']) && $_GET['success'] === 'false'): ?>
                <div class="p-3 bg-danger bg-opacity-25 border border-danger rounded" role="alert">
                    <p>Hubo un error al actualizar el estado del cupón.</p>
                    <ul>
                        <li>
                            El ticket no existe en la base de datos.
                        </li>
                        <li>
                            Su usuario no tiene permisos para editar el cupón asignado al ticket.
                        </li>
                        <li>
                            Error a la hora de cambiar el estado del ticket.
                        </li>
                        <li>
                            Error guardando el comprobante de pago.
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            <form id="form-coupon-status" method="post" class="p-3" action="<?= $action ?>" enctype="multipart/form-data">
                <?= wp_nonce_field('git_edit_coupon_status', 'nonce') ?>
                <div id="error-container"></div>
                <?php $this->file_input->render() ?>
                <input type="hidden" name="id" value="<?= $ticket->id ?>">
                <input type="hidden" name="has_previous" value="<?= git_serialize(!empty($proof_payment)) ?>">
                <div class="mb-3">
                    <?= $this->status_select->getLabel('Estado')->compact(); ?>
                    <?= $this->status_select->compact(); ?>
                </div>
                <div style="<?= $ticket->status === TicketStatus::PARTIAL ? 'display: block;' : 'display: none;' ?>"
                    id="partial-options-container">
                    <div class="mb-3 p-3 bg-success bg-opacity-25 border border-success rounded">
                        <span class="fs-4 fw-medium">Pasajeros aprobados a viajar:</span>
                        <div id="approved-passengers-container">
                            <?php foreach ($ticket->getPassengers() as $passenger): ?>
                                <div class="form-check">
                                    <input class="form-check-input check-passenger-approved" <?= $passenger->approved ? 'checked' : '' ?>
                                        type="checkbox" id="passenger_<?= $passenger->id ?>"
                                        <?= $ticket->status === TicketStatus::PENDING || git_current_user_has_role('administrator') ? '' : 'disabled' ?> name="approved_passengers[]" value="<?= $passenger->id ?>">
                                    <label class="form-check-label" for="passenger_<?= $passenger->id ?>">
                                        <?= esc_html($passenger->name) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <?= $this->amount_input->getLabel('Monto')->compact(); ?>
                        <?= $this->amount_input->compact(); ?>
                    </div>
                </div>
                <div id="section-payment">
                    <div class="mb-3">
                        <?= $this->code_input->getLabel('Código')->compact(); ?>
                        <?= $this->code_input->compact(); ?>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="m-3">
                            <span class="text-secondary" id="proof_payment_name_display">
                                <?= esc_html(empty($proof_payment->filename) ? 'Subir archivo...' : $proof_payment->filename) ?>
                            </span>
                        </div>
                        <div class="btn-group">
                            <a class="btn btn-outline-success" href="<?= $proof_payment->url ?? '#' ?>" target="_blank">
                                <i class="bi bi-eye"></i> Recupera el comprobante
                            </a>
                            <button id="button_upload_proof_payment" type="button" class="btn btn-outline-primary">
                                <i class="bi bi-upload"></i> Sube el comprobante
                            </button>
                        </div>
                    </div>
                </div>
                <?php if ($ticket->status === TicketStatus::PENDING || git_current_user_has_role('administrator')): ?>
                    <button id="button_submit_form_coupon_status" type="submit" class="btn btn-primary">Guardar</button>
                <?php endif; ?>
            </form>
            <script>
                const selectElement = document.getElementById('<?= $this->status_select->id ?>');
                const submitButton = document.getElementById('button_submit_form_coupon_status');
                selectElement.addEventListener('change', function (event) {
                    const selectedValue = event.target.value;
                    if (['<?= TicketStatus::CANCEL->value ?>', '<?= TicketStatus::PENDING->value ?>'].includes(selectedValue)) {
                        document.getElementById('section-payment').style.display = 'none';
                        document.getElementById('<?= $this->code_input->id ?>').required = false;
                        document.getElementById('partial-options-container').style.display = 'none';
                    } else if (selectedValue === '<?= TicketStatus::PARTIAL->value ?>') {
                        document.getElementById('partial-options-container').style.display = 'block';
                    } else {
                        document.getElementById('partial-options-container').style.display = 'none';
                        document.getElementById('section-payment').style.display = 'block';
                        document.getElementById('<?= $this->code_input->id ?>').required = true;
                    }
                });
                selectElement.dispatchEvent(new Event('change'));
                document.getElementById('button_upload_proof_payment').addEventListener('click', function () {
                    document.getElementById('<?= $this->file_input->id ?>').click();
                });
                if (submitButton) {
                    submitButton.addEventListener('click', function () {
                        submitButton.textContent = 'Guardando...';
                        submitButton.disabled = true;
                    });
                }
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
        $ticket = git_ticket_by_id($ticket_id);

        if ($ticket === null) {
            return false;
        }

        $coupon = $ticket->getCoupon();

        if ($coupon === null) {
            return false;
        }

        if (git_current_user_has_role('administrator')) {
            return true;
        }

        if (!git_current_user_has_role('operator')) {
            return false;
        }

        $operator = new Operator();
        $operator->setUser(wp_get_current_user());
        $coupons = $operator->getCoupons();

        foreach ($coupons as $coupon_operator) {
            if ($coupon_operator->ID === $coupon->ID) {
                return true;
            }
        }

        return false;
    }
}
