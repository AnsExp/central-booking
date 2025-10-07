<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\AccordionComponent;
use CentralTickets\Components\CodeEditorComponent;
use CentralTickets\Components\Displayer;
use CentralTickets\Placeholders\PlaceholderEngineTicket;
use CentralTickets\Ticket;

final class SettingsNotifications implements Displayer
{
    private CodeEditorComponent $email_notification;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->email_notification = new CodeEditorComponent('notification_email');

        $this->email_notification->set_value(git_get_setting('notification_email', ''));

        foreach ([
            $this->email_notification,
        ] as $code_editor) {
            $code_editor->set_language('html');
            $code_editor->styles->set('width', '100%');
            $code_editor->set_attribute('rows', 7);
        }
    }

    public function display()
    {
        $accordion = new AccordionComponent();
        $accordion->styles->set('margin-top', '20px');
        $accordion->add_item(git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Ticket)'), (new PlaceholderEngineTicket(new Ticket))->get_placeholders_info());
        $accordion->display();
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <input type="hidden" name="scope" value="notifications">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->email_notification->get_label('Email de notificación al cliente')->display() ?>
                    </th>
                    <td>
                        <?php $this->email_notification->display() ?>
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