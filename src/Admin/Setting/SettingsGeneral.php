<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;

final class SettingsGeneral implements Displayer
{
    public function display()
    {
        ?>
        <div class="wrap">
            <button type="button" class="button" id="import_data_button">Importar datos</button>
            <button type="button" class="button" id="export_data_button">Exportar datos</button>
        </div>
        <?php
        $this->import_data_form();
        $this->export_data_form();
    }

    private function import_data_form()
    {
        $file_input = new InputComponent('git_data', 'file');
        $nonce_input = new InputComponent('nonce', 'hidden');
        $nonce_input->set_value(wp_create_nonce('git-import-data'));
        $file_input->set_attribute('accept', '.json');
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
        $nonce_input->set_value(wp_create_nonce('git_export_data'));
        ?>
        <div class="git-export-data">
            <form id="git-export-data-form" method="post" class="git-import-data-form"
                action="<?= esc_url(admin_url('admin-ajax.php?action=git_export_data')) ?>">
                <h3>¿Qué deseas exportar?</h3>
                <div class="git-export-options">
                    <p>
                        <?php
                        $nonce_input->display();
                        $settings_input->display();
                        $settings_input->get_label('Configuraciones')->display();
                        ?>
                    </p>
                    <p>
                        <?php
                        $entities_input->display();
                        $entities_input->get_label('Entidades de datos')->display();
                        ?>
                    </p>
                    <p>
                        <?php
                        $products_input->display();
                        $products_input->get_label('Productos')->display();
                        ?>
                    </p>
                    <p>
                        <?php
                        $operators_input->display();
                        $operators_input->get_label('Operadores')->display();
                        ?>
                    </p>
                    <p>
                        <?php
                        $coupons_input->display();
                        $coupons_input->get_label('Cupones')->display();
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