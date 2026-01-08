<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Data\Configurations;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\CodeEditorComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Placeholders\PlaceholderEngineCheckout;
use CentralBooking\Placeholders\PlaceholderEngineTicket;
use WC_Order;

final class SettingsNotifications implements DisplayerInterface
{
    private CodeEditorComponent $content_email;
    private InputComponent $title_email;
    private InputComponent $sender_email;
    private CodeEditorComponent $message_checkout;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->title_email = new InputComponent('title_notification_email');
        $this->sender_email = new InputComponent('sender_notification_email');
        $this->content_email = new CodeEditorComponent('content_notification_email');
        $this->message_checkout = new CodeEditorComponent('message_checkout');
        $this->sender_email->styles->set('text-align', 'end');
        foreach ([$this->content_email, $this->message_checkout] as $code_editor) {
            $code_editor->set_language('html');
            $code_editor->styles->set('width', '100%');
            $code_editor->attributes->set('rows', 7);
        }
        $this->message_checkout->setValue(git_get_setting('message_checkout', ''));
        $this->title_email->setValue(git_get_map_setting('notification_email.title', ''));
        $this->sender_email->setValue(git_get_map_setting('notification_email.sender', ''));
        $this->content_email->setValue(git_get_map_setting('notification_email.content', ''));
    }

    public function render()
    {
        $accordion = new AccordionComponent();
        $accordion->styles->set('margin-top', '20px');
        $accordion->add_item(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Ticket)'),
            (new PlaceholderEngineTicket(new Ticket()))->get_placeholders_info()
        );
        $accordion->add_item(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Pedido)'),
            (new PlaceholderEngineCheckout(new WC_Order()))->get_placeholders_info(),
        );
        $accordion->render();
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <input type="hidden" name="scope" value="notifications">
            <h3>Mensaje de checkout</h3>
            <p>Este mensaje aparecerá en la pantalla de <i>Thank You</i> de WooCommerce.</p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->message_checkout->getLabel('Mensaje de Thank You')->render(); ?>
                        <br>
                        <small>Placeholders (Pedido)</small>
                    </th>
                    <td>
                        <?php $this->message_checkout->render(); ?>
                    </td>
                </tr>
            </table>
            <hr>
            <h3>Email de confirmación</h3>
            <p>
                Se notificará al cliente dueño del ticket cuando su ticket haya cambiado a uno de los siguientes estados:
                <code><?= TicketStatus::PAYMENT->label() ?></code>,
                <code><?= TicketStatus::PARTIAL->label() ?></code>
            </p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->title_email->getLabel('Título')->render() ?>
                    </th>
                    <td>
                        <?php $this->title_email->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->sender_email->getLabel('Remitente')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->sender_email->render();
                        $url = get_site_url();
                        $parsed = parse_url($url);
                        ?>
                        <code><?= '@' . ($parsed['host'] ?? $url) ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->content_email->getLabel('Contenido')->render() ?>
                        <br>
                        <small>Placeholders (Ticket)</small>
                    </th>
                    <td>
                        <?php $this->content_email->render() ?>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button-primary" id="git-save-button">
                    Guardar configuraciones
                </button>
            </p>
        </form>
        <script>
            document.getElementById('<?= $this->sender_email->id ?>').addEventListener('keydown', function (event) {
                if (event.keyCode === 32 || event.key === ' ') {
                    event.preventDefault();
                    return false;
                }
            });

            document.getElementById('<?= $this->sender_email->id ?>').addEventListener('paste', function (event) {
                event.preventDefault();

                let paste = (event.clipboardData || window.clipboardData).getData('text');
                paste = paste.replace(/\s/g, '');

                this.value = paste;
            });
        </script>
        <?php
    }
}