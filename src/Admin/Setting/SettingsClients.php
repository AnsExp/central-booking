<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\CodeEditorComponent;
use CentralTickets\Components\Displayer;

final class SettingsClients implements Displayer
{
    private CodeEditorComponent $standard_textarea;
    private CodeEditorComponent $rpm_textarea;
    private CodeEditorComponent $kid_textarea;
    private CodeEditorComponent $extra_textarea;
    private CodeEditorComponent $local_textarea;
    private CodeEditorComponent $flexible_textarea;
    private CodeEditorComponent $terms_conditions_textarea;
    private CodeEditorComponent $request_seats_textarea;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->rpm_textarea = new CodeEditorComponent('rpm_message');
        $this->kid_textarea = new CodeEditorComponent('kid_message');
        $this->extra_textarea = new CodeEditorComponent('extra_message');
        $this->local_textarea = new CodeEditorComponent('local_message');
        $this->standard_textarea = new CodeEditorComponent('standard_message');
        $this->flexible_textarea = new CodeEditorComponent('flexible_message');
        $this->request_seats_textarea = new CodeEditorComponent('request_seats');
        $this->terms_conditions_textarea = new CodeEditorComponent('terms_conditions');

        $this->rpm_textarea->set_value(git_get_setting('form_message_rpm', ''));
        $this->kid_textarea->set_value(git_get_setting('form_message_kid', ''));
        $this->extra_textarea->set_value(git_get_setting('form_message_extra', ''));
        $this->local_textarea->set_value(git_get_setting('form_message_local', ''));
        $this->standard_textarea->set_value(git_get_setting('form_message_standard', ''));
        $this->flexible_textarea->set_value(git_get_setting('form_message_flexible', ''));
        $this->request_seats_textarea->set_value(git_get_setting('form_message_request_seats', ''));
        $this->terms_conditions_textarea->set_value(git_get_setting('form_message_terms_conditions', ''));

        foreach ([
            $this->rpm_textarea,
            $this->kid_textarea,
            $this->extra_textarea,
            $this->local_textarea,
            $this->standard_textarea,
            $this->flexible_textarea,
            $this->request_seats_textarea,
            $this->terms_conditions_textarea,
        ] as $code_editor) {
            $code_editor->set_language('html');
            $code_editor->styles->set('width', '100%');
            $code_editor->set_attribute('rows', 7);
        }
    }

    public function display()
    {
        ?>
            <input type="hidden" name="scope" value="clients">
            <table class="form-table" role="presentation">
                <tr>
                    <th colspan="2">
                        <h2>| Sección Estandar</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->standard_textarea->get_label('Mensaje para los clientes Estandar')->display() ?>
                    </th>
                    <td>
                        <?php $this->standard_textarea->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->extra_textarea->get_label('Mensaje sobre equipaje extra')->display() ?>
                    </th>
                    <td>
                        <?php $this->extra_textarea->display() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Reducido</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->local_textarea->get_label('Mensaje para locales')->display() ?>
                    </th>
                    <td>
                        <?php $this->local_textarea->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->rpm_textarea->get_label('Mensaje para los clientes RPM')->display() ?>
                    </th>
                    <td>
                        <?php $this->rpm_textarea->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->kid_textarea->get_label('Mensaje para los clientes menores de edad')->display() ?>
                    </th>
                    <td>
                        <?php $this->kid_textarea->display() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Extra</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->flexible_textarea->get_label('Mensaje sobre flexibilidad')->display() ?>
                    </th>
                    <td>
                        <?php $this->flexible_textarea->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->terms_conditions_textarea->get_label('Términos y condiciones')->display() ?>
                    </th>
                    <td>
                        <?php $this->terms_conditions_textarea->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->request_seats_textarea->get_label('Solicitud de más asientos')->display() ?>
                    </th>
                    <td>
                        <?php $this->request_seats_textarea->display() ?>
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