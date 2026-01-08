<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Data\Constants\TransportCustomeFieldConstants;
use CentralBooking\Data\MetaManager;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\GUI\TextareaComponent;
use CentralBooking\GUI\TextComponent;
use CentralBooking\Implementation\GUI\OperatorSelect;
use CentralBooking\Implementation\GUI\RouteSelect;
use CentralBooking\Implementation\GUI\ServiceSelect;
use CentralBooking\Implementation\GUI\TypeSelect;

final class FormTransport implements DisplayerInterface
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
        $this->custom_field = new TextareaComponent('custom_field_content');
        $this->custom_field_topic = new SelectComponent('custom_field_topic');
        $this->input_id->setValue(0);
    }

    public function render()
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
            ]
        );
        $alias = [];
        $working_days = [];
        $this->custom_field->attributes->set('rows', '4');
        $this->custom_field->attributes->set('cols', '60');
        $this->input_code->setRequired(true);
        $this->select_type->setRequired(true);
        $this->input_nicename->setRequired(true);
        $this->input_capacity->setRequired(true);
        $this->select_operator->setRequired(true);
        $this->custom_field_topic->styles->set('margin-bottom', '10px');
        foreach (TransportCustomeFieldConstants::cases() as $field) {
            $this->custom_field_topic->addOption($field->label(), $field->value);
        }
        $transport = git_transport_by_id((int) ($_GET['id'] ?? '0'));
        if ($transport) {
            $working_days = $transport->getWorkingDays();
            $alias = $transport->getMeta('alias') ?? [];
            $this->custom_field->setValue(MetaManager::getMapMeta(MetaManager::TRANSPORT, $transport->id, 'custom_field.content') ?? '');
            $this->custom_field_topic->setValue(MetaManager::getMapMeta(MetaManager::TRANSPORT, $transport->id, 'custom_field.topic') ?? '');
            $this->input_code->setValue($transport->code);
            $this->input_code->setPlaceholder('Código');
            $this->input_photo_url->setValue($transport->getUrlPhoto() ?? '');
            $this->select_operator->setValue($transport->getOperator()->getUser()->ID);
            $this->input_id->setValue($transport->id);
            $this->input_nicename->setValue($transport->nicename);
            $this->input_nicename->setPlaceholder('Nombre');
            $this->input_capacity->setPlaceholder('Capacidad');
            $this->input_capacity->setValue($transport->getMeta('capacity'));
            $this->select_type->setValue($transport->type->value);
            foreach ($transport->getRoutes() as $route) {
                $this->select_routes->setValue($route->id);
            }
            foreach ($transport->getServices() as $service) {
                $this->select_services->setValue($service->id);
            }
        }
        ob_start();
        $action = add_query_arg(
            ['action' => 'git_edit_transport'],
            admin_url('admin-ajax.php')
        );
        ?>
        <div id="form-transport-message-container"></div>
        <form id="form-transport" method="post" action="<?= esc_attr($action) ?>">
            <?php $this->input_id->render() ?>
            <input type="hidden" name="nonce" value="<?= esc_attr(wp_create_nonce('git_transport_form')); ?>">
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
                        <?php $this->input_nicename->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_nicename->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_capacity->getLabel('Capacidad')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_capacity->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_operator->getLabel('Operador')->render() ?>
                    </th>
                    <td>
                        <?php $this->select_operator->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_type->getLabel('Tipo de transporte')->render() ?>
                    </th>
                    <td>
                        <?php $this->select_type->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_code->getLabel('Código')->render() ?>
                    </th>
                    <td>
                        <?php $this->input_code->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_routes->getLabel('Rutas')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_routes->render();
                        $this->select_routes->getOptionsContainer()->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_services->getLabel('Servicios')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_services->render();
                        $this->select_services->getOptionsContainer()->render();
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
                            foreach ($transport->getCrew() as $crew_member) {
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
                        <?php $this->accordion_crew->render(); ?>
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
                        <?= $this->input_photo_url->getLabel('URL de la foto del transporte')->render() ?>
                    </th>
                    <td>
                        <?= $this->input_photo_url->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?= $this->custom_field->getLabel('Campo Personalizado')->render() ?>
                    </th>
                    <td>
                        <?= $this->custom_field_topic->render() ?>
                        <?= $this->custom_field->render() ?>
                    </td>
                </tr>
            </table>
            <button type="submit" class="button-primary">Guardar Transporte</button>
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
                    <input type="text" name="crew[name][]" value="<?php echo esc_attr($member['name']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Rol <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew[role][]" value="<?php echo esc_attr($member['role']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Contacto <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew[contact][]" value="<?php echo esc_attr($member['contact']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Licencia <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew[license][]" value="<?php echo esc_attr($member['license']); ?>" required>
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
