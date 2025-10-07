<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Constants\DateTripConstants;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\AccordionComponent;
use CentralTickets\Components\CodeEditorComponent;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Placeholders\PlaceholderEngineCheckout;
use WC_Order;

final class SettingsGeneral implements Displayer
{
    private InputComponent $date_min_buyer_input;
    private SelectComponent $date_min_buyer;
    private CodeEditorComponent $message_checkout_editor;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->date_min_buyer = new SelectComponent('date_min_buyer');
        $this->message_checkout_editor = new CodeEditorComponent('message_checkout');
        $this->date_min_buyer_input = new InputComponent('date_min_buyer_custome', 'number');

        $this->date_min_buyer_input->set_attribute('min', 0);

        $this->date_min_buyer->add_option('Ninguno', DateTripConstants::NONE);
        $this->date_min_buyer->add_option('Fecha actual', DateTripConstants::TODAY);
        $this->date_min_buyer->add_option('Personalizar', DateTripConstants::CUSTOME);

        $this->date_min_buyer->set_value(git_get_setting('date_min_buyer', 'none'));
        $this->date_min_buyer_input->set_value(git_get_setting('date_min_buyer_custome', 0));
        $this->message_checkout_editor->set_value(git_get_setting('message_checkout', ''));

        $this->message_checkout_editor->set_language('html');
    }

    public function display()
    {
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <script>
                jQuery(document).ready(function ($) {
                    $('#<?= $this->date_min_buyer->id ?>').on('change', function () {
                        const selected = $(this).val();
                        $('#row_date_min_buyer_custome').toggle(selected === '<?= DateTripConstants::CUSTOME ?>');
                    });
                });
            </script>
            <input type="hidden" name="scope" value="general">
            <?php wp_nonce_field('git_settings_nonce', 'nonce'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?= $this->date_min_buyer->get_label('Fecha de ida (inicial)')->compact() ?>
                    </th>
                    <td>
                        <?= $this->date_min_buyer->compact() ?>
                        <p class="description">
                            Fecha mínima en que se puede reservar el viaje.
                        </p>
                    </td>
                </tr>
                <tr id="row_date_min_buyer_custome"
                    style="<?= git_get_setting('date_min_buyer', 'none') === 'custome' ? '' : 'display: none;' ?>">
                    <th scope="row">
                        <?= $this->date_min_buyer_input->get_label('Días hasta la ida')->compact() ?>
                    </th>
                    <td>
                        <?= $this->date_min_buyer_input->compact() ?>
                        <p class="description">
                            Fecha actual + Días hasta la ida = Fecha de reserva.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <?php
                        $accordion = new AccordionComponent();
                        $accordion->add_item(
                            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders para el mensaje checkout.'),
                            git_string_to_component($this->get_placeholders()),
                        );
                        echo $accordion->compact();
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php $this->message_checkout_editor->get_label('Mensaje de Checkout')->display(); ?>
                    </th>
                    <td>
                        <?php $this->message_checkout_editor->display(); ?>
                        <p class="description">
                            Mensaje que aparecerá a la hora de hacer Checkout. Puedes usar HTML y subir imágenes directamente.
                        </p>
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

    private function get_placeholders()
    {
        $engine = new PlaceholderEngineCheckout(new WC_Order());
        return $engine->get_placeholders_info()->compact();
    }
}