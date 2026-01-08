<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\GUI\CodeEditorComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;

final class SettingsClients implements DisplayerInterface
{
    private InputComponent $days_without_sale_input;
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
        $this->days_without_sale_input = new InputComponent('days_without_sale', 'number');
        $this->rpm_textarea = new CodeEditorComponent('rpm_message');
        $this->kid_textarea = new CodeEditorComponent('kid_message');
        $this->extra_textarea = new CodeEditorComponent('extra_message');
        $this->local_textarea = new CodeEditorComponent('local_message');
        $this->standard_textarea = new CodeEditorComponent('standard_message');
        $this->flexible_textarea = new CodeEditorComponent('flexible_message');
        $this->request_seats_textarea = new CodeEditorComponent('request_seats');
        $this->terms_conditions_textarea = new CodeEditorComponent('terms_conditions');
        $this->days_without_sale_input->setValue(git_get_setting('days_without_sale', 0));

        $this->rpm_textarea->setValue(git_get_setting('form_message_rpm', ''));
        $this->kid_textarea->setValue(git_get_setting('form_message_kid', ''));
        $this->extra_textarea->setValue(git_get_setting('form_message_extra', ''));
        $this->local_textarea->setValue(git_get_setting('form_message_local', ''));
        $this->standard_textarea->setValue(git_get_setting('form_message_standard', ''));
        $this->flexible_textarea->setValue(git_get_setting('form_message_flexible', ''));
        $this->request_seats_textarea->setValue(git_get_setting('form_message_request_seats', ''));
        $this->terms_conditions_textarea->setValue(git_get_setting('form_message_terms_conditions', ''));
        $this->days_without_sale_input->attributes->set('min', -365);
        $this->days_without_sale_input->attributes->set('max', 365);

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
            $code_editor->attributes->set('rows', 7);
        }
    }

    public function render()
    {
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg(['action' => 'git_settings'], admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="scope" value="clients">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>">
            <table class="form-table" role="presentation">
                <tr>
                    <th colspan="2">
                        <h2>| Configuración de la reserva</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?= $this->days_without_sale_input->getLabel('Días sin venta')->compact() ?>
                    </th>
                    <td>
                        <?= $this->days_without_sale_input->compact() ?>
                        <p class="description">
                            Fecha de viaje >= Fecha actual + Días sin venta.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Regular</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->standard_textarea->getLabel('Mensaje para los clientes regular')->render() ?>
                    </th>
                    <td>
                        <?php $this->standard_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->extra_textarea->getLabel('Mensaje sobre carga extra')->render() ?>
                    </th>
                    <td>
                        <?php $this->extra_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Preferente</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->local_textarea->getLabel('Mensaje para locales')->render() ?>
                    </th>
                    <td>
                        <?php $this->local_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->rpm_textarea->getLabel('Mensaje para los clientes RPM')->render() ?>
                    </th>
                    <td>
                        <?php $this->rpm_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->kid_textarea->getLabel('Mensaje para los clientes de edad preferente')->render() ?>
                    </th>
                    <td>
                        <?php $this->kid_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Extra</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->flexible_textarea->getLabel('Mensaje sobre flexibilidad')->render() ?>
                    </th>
                    <td>
                        <?php $this->flexible_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->terms_conditions_textarea->getLabel('Términos y condiciones')->render() ?>
                    </th>
                    <td>
                        <?php $this->terms_conditions_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->request_seats_textarea->getLabel('Solicitud de más asientos')->render() ?>
                    </th>
                    <td>
                        <?php $this->request_seats_textarea->render() ?>
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