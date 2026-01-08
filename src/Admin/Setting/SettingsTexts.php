<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\TransportConstants;
use CentralBooking\Data\Constants\TypeWayConstants;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;

final class SettingsTexts implements DisplayerInterface
{

    public function render()
    {
        $one_way_input = new InputComponent('one_way');
        $any_way_input = new InputComponent('any_way');
        $double_way_input = new InputComponent('double_way');

        $type_land_input = new InputComponent('type_land');
        $type_aero_input = new InputComponent('type_aero');
        $type_marine_input = new InputComponent('type_marine');

        $status_cancel_input = new InputComponent('status_cancel');
        $status_pending_input = new InputComponent('status_pending');
        $status_payment_input = new InputComponent('status_payment');
        $status_partial_input = new InputComponent('status_partial');

        $one_way_input->setValue(TypeWayConstants::ONE_WAY->label());
        $any_way_input->setValue(TypeWayConstants::ANY_WAY->label());
        $double_way_input->setValue(TypeWayConstants::DOUBLE_WAY->label());
        $type_land_input->setValue(TransportConstants::LAND->label());
        $type_aero_input->setValue(TransportConstants::AERO->label());
        $type_marine_input->setValue(TransportConstants::MARINE->label());
        $status_pending_input->setValue(TicketStatus::PENDING->label());
        $status_payment_input->setValue(TicketStatus::PAYMENT->label());
        $status_partial_input->setValue(TicketStatus::PARTIAL->label());
        $status_cancel_input->setValue(TicketStatus::CANCEL->label());
        $type_aero_input->setRequired(true);
        $type_marine_input->setRequired(true);
        $type_land_input->setRequired(true);
        $status_cancel_input->setRequired(true);
        $status_pending_input->setRequired(true);
        $status_payment_input->setRequired(true);
        $status_partial_input->setRequired(true);
        $one_way_input->setRequired(true);
        $any_way_input->setRequired(true);
        $double_way_input->setRequired(true);
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <input type="hidden" name="scope" value="texts">
            <table class="form-table" role="presentation">
                <tr>
                    <th>
                        <h2>| Tipos de transporte</h2>
                    </th>
                    <td>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th><?php $type_land_input->getLabel('Transporte tipo A')->render() ?></th>
                                <td><?php $type_land_input->render() ?></td>
                            </tr>
                            <tr>
                                <th><?php $type_aero_input->getLabel('Transporte tipo B')->render() ?></th>
                                <td><?php $type_aero_input->render() ?></td>
                            </tr>
                            <tr>
                                <th><?php $type_marine_input->getLabel('Transporte tipo C')->render() ?></th>
                                <td><?php $type_marine_input->render() ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th>
                        <h2>| Estados de la ruta</h2>
                    </th>
                    <td>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th><?php $status_pending_input->getLabel('Inicial')->render() ?></th>
                                <td><?php $status_pending_input->render() ?></td>
                            </tr>
                            <tr>
                                <th><?php $status_payment_input->getLabel('Completado')->render() ?></th>
                                <td><?php $status_payment_input->render() ?></td>
                            </tr>
                            <tr>
                                <th><?php $status_partial_input->getLabel('En Proceso')->render() ?></th>
                                <td><?php $status_partial_input->render() ?></td>
                            </tr>
                            <tr>
                                <th><?php $status_cancel_input->getLabel('Sin Procesar')->render() ?></th>
                                <td><?php $status_cancel_input->render() ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th>
                        <h2>| Tipos de trayecto</h2>
                    </th>
                    <td>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th><?php $one_way_input->getLabel('Una vía')->render() ?></th>
                                <td><?php $one_way_input->render() ?></td>
                            </tr>
                            <tr>
                                <th><?php $double_way_input->getLabel('Doble vía')->render() ?></th>
                                <td><?php $double_way_input->render() ?></td>
                            </tr>
                            <tr>
                                <th><?php $any_way_input->getLabel('Cualquiera')->render() ?></th>
                                <td><?php $any_way_input->render() ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button-primary" id="git-save-button">
                    Guardar configuraciones
                </button>
            </p>
        </form>
        <?php
    }

    public static function getTextWays(?TypeWayConstants $type = null): string|null
    {
        if ($type !== null) {
            $texts = git_get_map_setting('texts_ways', []);
            return $texts[$type->value] ?? null;
        }
        return null;
    }

    public static function setTextWays(string $one_way, string $any_way, string $double_way)
    {
        $text = [
            TypeWayConstants::ONE_WAY->value => $one_way ?? TypeWayConstants::ONE_WAY->value,
            TypeWayConstants::ANY_WAY->value => $any_way ?? TypeWayConstants::ANY_WAY->value,
            TypeWayConstants::DOUBLE_WAY->value => $double_way ?? TypeWayConstants::DOUBLE_WAY->value,
        ];
        git_set_setting('texts_ways', $text);
    }

    public static function getTextTransport(?TransportConstants $transport = null): string|null
    {
        if ($transport !== null) {
            $texts = git_get_map_setting('texts_transports', []);
            return $texts[$transport->value] ?? null;
        }
        return null;
    }

    public static function setTextTransport(string $land, string $aero, string $marine)
    {
        $text = [
            TransportConstants::LAND->value => $land ?? TransportConstants::LAND->value,
            TransportConstants::AERO->value => $aero ?? TransportConstants::AERO->value,
            TransportConstants::MARINE->value => $marine ?? TransportConstants::MARINE->value,
        ];
        git_set_setting('texts_transports', $text);
    }

    public static function getTextStatus(?TicketStatus $status = null): string|null
    {
        if ($status !== null) {
            $texts = git_get_map_setting('texts_status', []);
            return $texts[$status->value] ?? null;
        }
        return null;
    }

    public static function setTextStatus(string $pending, string $payment, string $partial, string $cancel)
    {
        $text = [
            TicketStatus::PENDING->value => $pending ?? TicketStatus::PENDING->value,
            TicketStatus::PAYMENT->value => $payment ?? TicketStatus::PAYMENT->value,
            TicketStatus::PARTIAL->value => $partial ?? TicketStatus::PARTIAL->value,
            TicketStatus::CANCEL->value => $cancel ?? TicketStatus::CANCEL->value,
        ];
        git_set_setting('texts_status', $text);
    }
}