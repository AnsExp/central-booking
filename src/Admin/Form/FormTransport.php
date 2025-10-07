<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\TextareaComponent;
use CentralTickets\Components\TextComponent;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Components\AccordionComponent;
use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\Implementation\TypeSelect;
use CentralTickets\Components\Implementation\RouteSelect;
use CentralTickets\Components\Implementation\OperatorSelect;
use CentralTickets\Components\Implementation\ServiceSelect;
use CentralTickets\MetaManager;

final class FormTransport implements Displayer
{
    private InputComponent $input_id;
    private InputComponent $input_nicename;
    private InputComponent $input_photo_url;
    private InputComponent $input_capacity;
    private InputComponent $input_code;
    private SelectComponent $select_operator;
    private SelectComponent $select_type;
    private MultipleSelectComponent $select_routes;
    private MultipleSelectComponent $select_services;
    private AccordionComponent $accordion_crew;
    private TextareaComponent $custom_field;
    private SelectComponent $custom_field_topic;

    public function __construct()
    {
        $this->accordion_crew = new AccordionComponent(false);
        $this->input_id = new InputComponent('id', 'hidden');
        $this->input_photo_url = new InputComponent('photo_url', 'text');
        $this->select_type = (new TypeSelect('type'))->create();
        $this->input_nicename = new InputComponent('nicename', 'text');
        $this->input_capacity = new InputComponent('capacity', 'number');
        $this->input_code = new InputComponent('code', 'text');
        $this->select_operator = (new OperatorSelect('operator'))->create();
        $this->select_routes = (new RouteSelect('routes'))->create(true);
        $this->select_services = (new ServiceSelect('services'))->create(true);
        $this->custom_field = new TextareaComponent('custom_field');
        $this->custom_field_topic = new SelectComponent('custom_field_topic');
        $this->input_id->set_value(0);
    }

    public function display()
    {
        wp_enqueue_script(
            'git-form-transport',
            CENTRAL_BOOKING_URL . '/assets/js/admin/transport-form.js',
        );
        wp_localize_script(
            'git-form-transport',
            'formElements',
            [
                'accordionCrew' => $this->accordion_crew->id,
                'containerAliasFields' => 'container-alias-fields',
                'buttonAddAlias' => 'button-add-alias',
                'buttonAddCrewMember' => 'button-add-crew-member',
                'templates' => 'form-transport-template',
                'ajax' => [
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('git_transport_form'),
                    'action' => 'git_transport_form',
                    'successRedirect' => admin_url('admin.php?page=git_transports&action=table'),
                ],
            ]
        );
        $alias = [];
        $working_days = [];
        $this->custom_field->set_attribute('rows', '4');
        $this->custom_field->set_attribute('cols', '60');
        $this->input_code->set_required(true);
        $this->select_type->set_required(true);
        $this->input_nicename->set_required(true);
        $this->input_capacity->set_required(true);
        $this->select_operator->set_required(true);
        $this->custom_field_topic->styles->set('margin-bottom', '10px');
        $this->custom_field_topic->add_option('Texto', 'text');
        $this->custom_field_topic->add_option('Acción', 'action');
        $this->custom_field_topic->add_option('Prompt de IA', 'ia_prompt');
        $this->custom_field_topic->add_option('Logo de la Venta', 'logo_sale');
        $transport = git_get_transport_by_id((int) ($_GET['id'] ?? '0'));
        if ($transport) {
            $working_days = $transport->get_working_days();
            $alias = $transport->get_meta('alias') ?? [];
            $this->custom_field->set_value(MetaManager::get_map_meta(MetaManager::TRANSPORT, $transport->id, 'custom_field.content') ?? '');
            $this->custom_field_topic->set_value(MetaManager::get_map_meta(MetaManager::TRANSPORT, $transport->id, 'custom_field.topic') ?? '');
            $this->input_code->set_value($transport->code);
            $this->input_code->set_placeholder('Código');
            $this->input_photo_url->set_value($transport->get_meta('photo_url') ?? '');
            $this->select_operator->set_value($transport->get_operator()->ID);
            $this->input_id->set_value($transport->id);
            $this->input_nicename->set_value($transport->nicename);
            $this->input_nicename->set_placeholder('Nombre');
            $this->input_capacity->set_placeholder('Capacidad');
            $this->input_capacity->set_value($transport->get_meta('capacity'));
            $this->select_type->set_value($transport->type);
            foreach ($transport->get_routes() as $route) {
                $this->select_routes->set_value($route->id);
            }
            foreach ($transport->get_services() as $service) {
                $this->select_services->set_value($service->id);
            }
        }
        ob_start();
        ?>
        <div id="form-transport-message-container"></div>
        <form id="form-transport" method="post">
            <?php $this->input_id->display() ?>
            <template id="form-transport-template">
                <div id="template-form-crew-member">
                    <?= $this->create_form_crew_member()->compact() ?>
                </div>
                <div id="form-transport-alias-field">
                    <?= $this->create_form_alias()->compact() ?>
                </div>
            </template>
            <table class="form-table" role="presentation" style="max-width: 700px;">
                <tr>
                    <th scope="row">
                        <?php $this->input_nicename->get_label('Nombre')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_nicename->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_capacity->get_label('Capacidad')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_capacity->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_operator->get_label('Operador')->display() ?>
                    </th>
                    <td>
                        <?php $this->select_operator->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_type->get_label('Tipo de transporte')->display() ?>
                    </th>
                    <td>
                        <?php $this->select_type->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_code->get_label('Código')->display() ?>
                    </th>
                    <td>
                        <?php $this->input_code->display() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_routes->get_label('Rutas')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_routes->display();
                        $this->select_routes->get_options_container()->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_services->get_label('Servicios')->display() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_services->display();
                        $this->select_services->get_options_container()->display();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Tripulación</label>
                    </th>
                    <td>
                        <?php
                        $this->accordion_crew->styles->set('margin-bottom', '10px');
                        if ($transport) {
                            foreach ($transport->get_crew() as $crew_member) {
                                $this->accordion_crew->add_item(
                                    TextComponent::create(
                                        'div',
                                        '',
                                        ['class' => 'crew-member-header']
                                    )
                                        ->append(TextComponent::create('span', esc_html($crew_member['name']), ['data-tag-name' => 'bi bi-caret-right']))
                                        ->append(' ')
                                        ->append(TextComponent::create('i', '', ['class' => 'bi bi-caret-right']))
                                        ->append(' ')
                                        ->append(TextComponent::create('span', esc_html($crew_member['role']), ['data-tag-role' => 'bi bi-caret-right'])),
                                    $this->create_form_crew_member($crew_member)
                                );
                            }
                        }
                        ?>
                        <?php $this->accordion_crew->display(); ?>
                        <button id="button-add-crew-member" class="button button-primary" type="button">
                            <i class="bi bi-plus"></i> Añadir tripulante
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Alias</label>
                    </th>
                    <td>
                        <div id="container-alias-fields">
                            <?php
                            foreach ($alias as $al) {
                                echo $this->create_form_alias($al)->compact();
                            }
                            ?>
                        </div>
                        <button id="button-add-alias" class="button button-primary" type="button">
                            <i class="bi bi-plus"></i> Añadir alias
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Disponibilidad diaria</label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">Disponibilidad diaria</legend>
                            <label>
                                <input type="checkbox" name="days[]" value="monday" <?= in_array('monday', $working_days) ? 'checked' : '' ?>>
                                Lunes
                            </label>
                            <label>
                                <input type="checkbox" name="days[]" value="tuesday" <?= in_array('tuesday', $working_days) ? 'checked' : '' ?>>
                                Martes
                            </label>
                            <label>
                                <input type="checkbox" name="days[]" value="wednesday" <?= in_array('wednesday', $working_days) ? 'checked' : '' ?>>
                                Miércoles
                            </label>
                            <label>
                                <input type="checkbox" name="days[]" value="thursday" <?= in_array('thursday', $working_days) ? 'checked' : '' ?>>
                                Jueves
                            </label>
                            <label>
                                <input type="checkbox" name="days[]" value="friday" <?= in_array('friday', $working_days) ? 'checked' : '' ?>>
                                Viernes
                            </label>
                            <label>
                                <input type="checkbox" name="days[]" value="saturday" <?= in_array('saturday', $working_days) ? 'checked' : '' ?>>
                                Sábado
                            </label>
                            <label>
                                <input type="checkbox" name="days[]" value="sunday" <?= in_array('sunday', $working_days) ? 'checked' : '' ?>>
                                Domingo
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?= $this->input_photo_url->get_label('URL de la foto del transporte')->compact() ?>
                    </th>
                    <td>
                        <?= $this->input_photo_url->compact() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?= $this->custom_field->get_label('Campo Personalizado')->compact() ?>
                    </th>
                    <td>
                        <?= $this->custom_field_topic->compact() ?>
                        <?= $this->custom_field->compact() ?>
                    </td>
                </tr>
            </table>
            <?= get_submit_button('Guardar Transporte'); ?>
        </form>
        <?php
        echo ob_get_clean();
    }

    private function create_form_alias(string $alias = '')
    {
        ob_start();
        ?>
        <div style="margin-bottom: 10px;">
            <input type="text" name="alias[]" value="<?php echo esc_attr($alias); ?>" placeholder="Alias del transporte">
            <button type="button" class="button-remove-alias button-secondary">Eliminar alias</button>
        </div>
        <?php
        return git_string_to_component(ob_get_clean());
    }

    private function create_form_crew_member(
        $member = [
            'role' => '',
            'name' => '',
            'contact' => '',
            'license' => '',
        ]
    ) {
        ob_start();
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <td scope="row">
                    <label>Nombre <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew_member_name[]" value="<?php echo esc_attr($member['name']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Rol <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew_member_role[]" value="<?php echo esc_attr($member['role']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Contacto <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew_member_contact[]" value="<?php echo esc_attr($member['contact']); ?>"
                        required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Licencia <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew_member_license[]" value="<?php echo esc_attr($member['license']); ?>"
                        required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <button type="button" class="button button-secondary remove-crew-member">
                        Eliminar tripulante
                    </button>
                </td>
            </tr>
        </table>
        <?php
        return git_string_to_component(ob_get_clean());
    }
}
