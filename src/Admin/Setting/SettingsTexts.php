<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Displayer;
use CentralTickets\Constants\TypeWayConstants;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Components\InputComponent;

final class SettingsTexts implements Displayer
{

    public function display()
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

        $one_way_input->set_value(git_get_text_by_way(TypeWayConstants::ONE_WAY));
        $any_way_input->set_value(git_get_text_by_way(TypeWayConstants::ANY_WAY));
        $double_way_input->set_value(git_get_text_by_way(TypeWayConstants::DOUBLE_WAY));
        $type_land_input->set_value(git_get_text_by_type(TransportConstants::LAND));
        $type_aero_input->set_value(git_get_text_by_type(TransportConstants::AERO));
        $type_marine_input->set_value(git_get_text_by_type(TransportConstants::MARINE));
        $status_pending_input->set_value(git_get_text_by_status(TicketConstants::PENDING));
        $status_payment_input->set_value(git_get_text_by_status(TicketConstants::PAYMENT));
        $status_partial_input->set_value(git_get_text_by_status(TicketConstants::PARTIAL));
        $status_cancel_input->set_value(git_get_text_by_status(TicketConstants::CANCEL));

        $type_aero_input->set_required(true);
        $type_marine_input->set_required(true);
        $type_land_input->set_required(true);
        $status_cancel_input->set_required(true);
        $status_pending_input->set_required(true);
        $status_payment_input->set_required(true);
        $status_partial_input->set_required(true);
        $one_way_input->set_required(true);
        $any_way_input->set_required(true);
        $double_way_input->set_required(true);
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
                                <th><?php $type_land_input->get_label('Transporte tipo A')->display() ?></th>
                                <td><?php $type_land_input->display() ?></td>
                            </tr>
                            <tr>
                                <th><?php $type_aero_input->get_label('Transporte tipo B')->display() ?></th>
                                <td><?php $type_aero_input->display() ?></td>
                            </tr>
                            <tr>
                                <th><?php $type_marine_input->get_label('Transporte tipo C')->display() ?></th>
                                <td><?php $type_marine_input->display() ?></td>
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
                                <th><?php $status_pending_input->get_label('Inicial')->display() ?></th>
                                <td><?php $status_pending_input->display() ?></td>
                            </tr>
                            <tr>
                                <th><?php $status_payment_input->get_label('Completado')->display() ?></th>
                                <td><?php $status_payment_input->display() ?></td>
                            </tr>
                            <tr>
                                <th><?php $status_partial_input->get_label('En Proceso')->display() ?></th>
                                <td><?php $status_partial_input->display() ?></td>
                            </tr>
                            <tr>
                                <th><?php $status_cancel_input->get_label('Sin Procesar')->display() ?></th>
                                <td><?php $status_cancel_input->display() ?></td>
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
                                <th><?php $one_way_input->get_label('Una vía')->display() ?></th>
                                <td><?php $one_way_input->display() ?></td>
                            </tr>
                            <tr>
                                <th><?php $double_way_input->get_label('Doble vía')->display() ?></th>
                                <td><?php $double_way_input->display() ?></td>
                            </tr>
                            <tr>
                                <th><?php $any_way_input->get_label('Cualquiera')->display() ?></th>
                                <td><?php $any_way_input->display() ?></td>
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
}