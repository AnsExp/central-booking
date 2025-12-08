<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Admin\AdminRouter;
use CentralTickets\Admin\View\TableOperators;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\Implementation\OperatorSelect;
use CentralTickets\Constants\TimeExpirationConstants;

final class FormOperatorsExternal implements Displayer
{
    public function display()
    {
        $expiration_select = new SelectComponent('expiration');
        $operator_select = (new OperatorSelect('operator'))->create();
        $operator_select->set_required(true);
        $expiration_select->set_required(true);
        $expiration_select->add_option('Seleccione...', '');
        $expiration_select->add_option('15 minutos', TimeExpirationConstants::MINUTES_15);
        $expiration_select->add_option('30 minutos', TimeExpirationConstants::MINUTES_30);
        $expiration_select->add_option('1 hora', TimeExpirationConstants::ONE_HOUR);
        $expiration_select->add_option('3 horas', TimeExpirationConstants::THREE_HOURS);
        $expiration_select->add_option('6 horas', TimeExpirationConstants::SIX_HOURS);
        $expiration_select->add_option('12 horas', TimeExpirationConstants::TWELVE_HOURS);
        $expiration_select->add_option('1 día', TimeExpirationConstants::ONE_DAY);
        $expiration_select->add_option('3 días', TimeExpirationConstants::THREE_DAYS);
        $expiration_select->add_option('7 días', TimeExpirationConstants::SEVEN_DAYS);
        $expiration_select->add_option('Nunca', TimeExpirationConstants::NEVER);
        $id_form = 'form_operator_link';
        $nonce = wp_create_nonce('operators_link_nonce');
        wp_enqueue_script(
            'operators-link',
            CENTRAL_BOOKING_URL . '/assets/js/admin/operator-link.js',
        );
        wp_localize_script(
            'operators-link',
            'OperatorsLinkData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php?action=git_generate_code_operator_external'),
                'elements' => [
                    'idForm' => $id_form,
                ],
            ]
        );
        ?>
        <form id="<?php echo esc_attr($id_form); ?>">
            <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php $operator_select->get_label('Operador')->display(); ?>
                        </th>
                        <td>
                            <?php $operator_select->display(); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php $expiration_select->get_label('Tiempo de expiración')->display(); ?>
                        </th>
                        <td>
                            <?php $expiration_select->display(); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="2">
                            <button class="button button-primary" type="submit">Generar código</button>
                        </th>
                    </tr>
                    <tr id="row_connector_key" style="display: none;">
                        <td scope="row" colspan="2">
                            <h3>Código de operador</h3>
                            <div id="id_connector_key_container" style="word-break: break-all; width: 100%; margin: 12px 0px;">
                                <!-- El código generado aparecerá aquí -->
                            </div>
                            <button class="button" type="button" id="copy_connector_key">Copiar código</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <?php
    }
}
