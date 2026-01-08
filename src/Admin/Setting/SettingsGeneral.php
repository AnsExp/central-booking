<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;

final class SettingsGeneral implements DisplayerInterface
{
    public function render()
    {
        ?>
        <div class="wrap">
            <div class="wrap">
                <button type="button" class="button" id="import_data_button">Importar datos</button>
                <button type="button" class="button" id="export_data_button">Exportar datos</button>
                <?php
                $this->import_data_form();
                $this->export_data_form();
                ?>
            </div>
            <hr>
            <?php
            $action = esc_url(add_query_arg(
                ['action' => 'git_settings_secret_key'],
                admin_url('admin-ajax.php')
            ));
            ?>
            <form id="git-settings-form" action="<?= $action ?>" method="post">
                <?php wp_nonce_field('git_secret_key', 'nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="secret_key">Llave secreta</label>
                        </th>
                        <td>
                            <input name="secret_key" type="text" id="secret_key" value="<?= esc_attr(git_get_secret_key()) ?>"
                                class="regular-text" readonly>
                            <button type="button" class="button" id="git-generate-button"> Generar </button>
                            <button type="submit" class="button"> Guardar </button>
                            <p class="description">La llave secreta se utilizará en algunas funcionalidades sensibles del
                                sistema.</p>
                        </td>
                </table>
            </form>
        </div>
        <script>
            function generarClaveSecreta(longitud = 32) {
                const array = new Uint8Array(longitud);
                window.crypto.getRandomValues(array);
                return Array.from(array, b => b.toString(16).padStart(2, '0')).join('');
            }
            document.getElementById('git-generate-button').addEventListener('click', function () {
                const nuevaClave = generarClaveSecreta(16);
                document.getElementById('secret_key').value = nuevaClave;
            });
        </script>
        <?php
    }

    private function import_data_form()
    {
        $file_input = new InputComponent('git_data', 'file');
        $nonce_input = new InputComponent('nonce', 'hidden');
        $nonce_input->setValue(wp_create_nonce('git-import-data'));
        $file_input->attributes->set('accept', '.json');
        ?>
        <div class="git-import-data">
            <form method="post" enctype="multipart/form-data" id="git-import-data-form" class="git-import-data-form"
                action="<?= esc_url(admin_url('admin-ajax.php?action=git_import_data')) ?>">
                <h3>Sube los datos que quieres cargar al sistema GIT</h3>
                <?= $nonce_input->compact(); ?>
                <?= $file_input->compact(); ?>
                <input type="submit" class="button  button-primary" value="Subir" disabled>
            </form>
        </div>
        <?php
    }

    private function export_data_form()
    {
        $nonce_input = new InputComponent('nonce', 'hidden');
        $coupons_input = new InputComponent('coupons_data', 'checkbox');
        $settings_input = new InputComponent('settings_data', 'checkbox');
        $entities_input = new InputComponent('entities_data', 'checkbox');
        $products_input = new InputComponent('products_data', 'checkbox');
        $operators_input = new InputComponent('operators_data', 'checkbox');
        $coupons_input->class_list->add('git-export-settings');
        $settings_input->class_list->add('git-export-settings');
        $entities_input->class_list->add('git-export-settings');
        $products_input->class_list->add('git-export-settings');
        $operators_input->class_list->add('git-export-settings');
        $nonce_input->setValue(wp_create_nonce('git_export_data'));
        ?>
        <div class="git-export-data">
            <form id="git-export-data-form" method="post" class="git-import-data-form"
                action="<?= esc_url(admin_url('admin-ajax.php?action=git_export_data')) ?>">
                <h3>¿Qué deseas exportar?</h3>
                <div class="git-export-options">
                    <p>
                        <?php
                        $nonce_input->render();
                        $settings_input->render();
                        $settings_input->getLabel('Configuraciones')->render();
                        ?>
                    </p>
                    <p>
                        <?php
                        $entities_input->render();
                        $entities_input->getLabel('Entidades de datos')->render();
                        ?>
                    </p>
                    <p>
                        <?php
                        $products_input->render();
                        $products_input->getLabel('Productos')->render();
                        ?>
                    </p>
                    <p>
                        <?php
                        $operators_input->render();
                        $operators_input->getLabel('Operadores')->render();
                        ?>
                    </p>
                    <p>
                        <?php
                        $coupons_input->render();
                        $coupons_input->getLabel('Cupones')->render();
                        ?>
                    </p>
                    <p class="submit inline-edit-save" style="justify-content: center;">
                        <input type="submit" id="export_data_submit" class="button button-primary" value="Descargar" disabled>
                        <span class="spinner"></span>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
}