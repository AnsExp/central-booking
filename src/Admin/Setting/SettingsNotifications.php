<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\AccordionComponent;
use CentralTickets\Components\CodeEditorComponent;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;
use CentralTickets\Configurations;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Placeholders\PlaceholderEngineCheckout;
use CentralTickets\Placeholders\PlaceholderEngineTicket;
use CentralTickets\Ticket;
use WC_Order;

final class SettingsNotifications implements Displayer
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
            $code_editor->set_attribute('rows', 7);
        }
        $this->message_checkout->set_value(git_get_setting('message_checkout', ''));
        $this->title_email->set_value(Configurations::get_map('notification_email.title', ''));
        $this->sender_email->set_value(Configurations::get_map('notification_email.sender', ''));
        $this->content_email->set_value(Configurations::get_map('notification_email.content', ''));
    }

    public function display()
    {
        $accordion = new AccordionComponent();
        $accordion->styles->set('margin-top', '20px');
        $accordion->add_item(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Ticket)'),
            (new PlaceholderEngineTicket(new Ticket))->get_placeholders_info()
        );
        $accordion->add_item(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Pedido)'),
            (new PlaceholderEngineCheckout(new WC_Order()))->get_placeholders_info(),
        );
        $accordion->display();
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
                        <?php $this->message_checkout->get_label('Mensaje de Thank You')->display(); ?>
                        <br>
                        <small>Placeholders (Pedido)</small>
                    </th>
                    <td>
                        <?php $this->message_checkout->display(); ?>
                    </td>
                </tr>
            </table>
            <hr>
            <h3>Email de confirmación</h3>
            <p>
                Se notificará al cliente dueño del ticket cuando su ticket haya cambiado a uno de los siguientes estados:
                <code><?= git_get_text_by_status(TicketConstants::PAYMENT) ?></code>,
                <code><?= git_get_text_by_status(TicketConstants::PARTIAL) ?></code>
            </p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->title_email->get_label('Título')->display() ?>
                    </th>
                    <td>
                        <?php $this->title_email->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->sender_email->get_label('Remitente')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->sender_email->display();
                        $url = get_site_url();
                        $parsed = parse_url($url);
                        ?>
                        <code><?= '@' . ($parsed['host'] ?? $url) ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->content_email->get_label('Contenido')->display() ?>
                        <br>
                        <small>Placeholders (Ticket)</small>
                    </th>
                    <td>
                        <?php $this->content_email->display() ?>
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