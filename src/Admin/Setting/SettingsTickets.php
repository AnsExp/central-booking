<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\InputComponent;
use CentralTickets\Ticket;
use CentralTickets\Passenger;
use CentralTickets\Components\Displayer;
use CentralTickets\Components\AccordionComponent;
use CentralTickets\Components\CodeEditorComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\Implementation\PageSelect;
use CentralTickets\Placeholders\PlaceholderEnginePassenger;
use CentralTickets\Placeholders\PlaceholderEngineTicket;

final class SettingsTickets implements Displayer
{
    private SelectComponent $page_viewer;
    private CodeEditorComponent $viewer_css;
    private CodeEditorComponent $viewer_js;
    private CodeEditorComponent $ticket_viewer_html;
    private CodeEditorComponent $passenger_viewer_html;
    private InputComponent $default_media;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->page_viewer = (new PageSelect('page_viewer'))->create();
        $this->viewer_js = new CodeEditorComponent('viewer_js');
        $this->viewer_css = new CodeEditorComponent('viewer_css');
        $this->ticket_viewer_html = new CodeEditorComponent('ticket_viewer_html');
        $this->passenger_viewer_html = new CodeEditorComponent('passenger_viewer_html');
        $this->default_media = new InputComponent('default_media');

        foreach ([
            $this->viewer_js,
            $this->viewer_css,
            $this->ticket_viewer_html,
            $this->passenger_viewer_html,
            $this->default_media,
        ] as $code_editor) {
            $code_editor->set_attribute('rows', 7);
            $code_editor->styles->set('width', '100%');
        }

        $this->page_viewer->set_value(git_get_map_setting('ticket_viewer.page_viewer'));
        $this->viewer_js->set_value(git_get_map_setting('ticket_viewer.viewer_js'));
        $this->viewer_css->set_value(git_get_map_setting('ticket_viewer.viewer_css'));
        $this->default_media->set_value(git_get_map_setting('ticket_viewer.default_media'));
        $this->ticket_viewer_html->set_value(git_get_map_setting('ticket_viewer.ticket_viewer_html'));
        $this->passenger_viewer_html->set_value(git_get_map_setting('ticket_viewer.passenger_viewer_html'));

        $this->viewer_js->set_language('js');
        $this->viewer_css->set_language('css');
        $this->ticket_viewer_html->set_language('html');
        $this->passenger_viewer_html->set_language('html');
    }

    public function display()
    {
        $accordion = new AccordionComponent();
        $accordion->add_item(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Ticket)'),
            (new PlaceholderEngineTicket(new Ticket()))->get_placeholders_info(),
        );
        $accordion->add_item(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Pasajero)'),
            (new PlaceholderEnginePassenger(new Passenger()))->get_placeholders_info(),
        );
        $accordion->styles->set('margin-top', '20px');
        $accordion->display();
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <input type="hidden" name="scope" value="tickets">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->page_viewer->get_label('Página de visor')->display(); ?>
                    </th>
                    <td>
                        <?php $this->page_viewer->display(); ?>
                        <p class="description">
                            Seleccione la página donde se redirigiran los QR de los tickets generados.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->ticket_viewer_html->get_label('Visor de tickets (html)')->display() ?>
                    </th>
                    <td>
                        <?php $this->ticket_viewer_html->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->viewer_js->get_label('Visor de tickets (js)')->display() ?>
                    </th>
                    <td>
                        <?php $this->viewer_js->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->viewer_css->get_label('Visor de tickets (css)')->display() ?>
                    </th>
                    <td>
                        <?php $this->viewer_css->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->passenger_viewer_html->get_label('Visor de pasajeros (html)')->display() ?>
                    </th>
                    <td>
                        <?php $this->passenger_viewer_html->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->default_media->get_label('Medio por defecto')->display() ?>
                    </th>
                    <td>
                        <?php $this->default_media->display() ?>
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