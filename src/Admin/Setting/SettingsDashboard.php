<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Component;
use CentralTickets\Components\InputComponent;

if (!defined('ABSPATH')) {
    exit;
}

final class SettingsDashboard implements Component
{
    private array $tabs;
    private string $current_tab;

    public function __construct()
    {
        $this->current_tab = $_GET['tab'] ?? 'general';
        $this->tabs = $this->get_available_tabs();
    }

    public function display()
    {
        echo $this->compact();
    }

    public function compact()
    {
        wp_enqueue_script(
            'central-tickets-settings-clients',
            CENTRAL_BOOKING_URL . '/assets/js/admin/settings-form.js',
        );
        ob_start();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Central Reservas</h1>
            <div class="notice notice-info" style="padding:16px; margin-bottom:16px;">
                <h2 style="margin-top:0;">Central Reservas - Versión del Plugin</h2>
                <p>
                    <strong>Versión actual:</strong> 1.0
                </p>
                <p style="color:#666;">
                    Última actualización: <?= git_date_format(date('Y-m-d', filemtime(__FILE__))); ?>
                </p>
            </div>
            <button type="button" class="button" id="import_data_button">Importar datos</button>
            <button type="button" class="button" id="export_data_button">Exportar datos</button>
            <?php $this->import_data_form(); ?>
            <?php $this->export_data_form(); ?>
            <?php $this->render_tab_navigation(); ?>
            <?php $this->render_current_tab_content() ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function import_data_form()
    {
        $file_input = new InputComponent('git_data', 'file');
        $nonce_input = new InputComponent('nonce', 'hidden');
        $nonce_input->set_value(wp_create_nonce('git-import-data'));
        $file_input->set_attribute('accept', '.git');
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
        $settings_input = new InputComponent('settings_data', 'checkbox');
        $entities_input = new InputComponent('entities_data', 'checkbox');
        $products_input = new InputComponent('products_data', 'checkbox');
        $settings_input->class_list->add('git-export-settings');
        $entities_input->class_list->add('git-export-settings');
        $products_input->class_list->add('git-export-settings');
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
                    <p class="submit inline-edit-save" style="justify-content: center;">
                        <input type="submit" id="export_data_submit" class="button button-primary" value="Descargar" disabled>
                        <span class="spinner"></span>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }

    private function get_available_tabs()
    {
        return [
            'general' => [
                'title' => 'General',
                'callback' => fn() => (new SettingsGeneral)->display()
            ],
            'clients' => [
                'title' => 'Reservas',
                'callback' => fn() => (new SettingsClients)->display()
            ],
            'tickets' => [
                'title' => 'Tickets',
                'callback' => fn() => (new SettingsTickets)->display()
            ],
            'operators' => [
                'title' => 'Operadores',
                'callback' => fn() => (new SettingsOperators)->display()
            ],
            'labels' => [
                'title' => 'Etiquetas',
                'callback' => fn() => (new SettingsTexts)->display()
            ],
            'webhooks' => [
                'title' => 'Webhooks',
                'callback' => fn() => (new SettingsWebhooks)->display()
            ],
            'preorder' => [
                'title' => 'Preorden',
                'callback' => fn() => (new SettingsPreorder)->display()
            ],
            'notifications' => [
                'title' => 'Notificaciones',
                'callback' => fn() => (new SettingsNotifications)->display()
            ],
            'interactivity' => [
                'title' => 'Interactividad',
                'callback' => fn() => (new SettingsInteractivity())->display()
            ],
        ];
    }

    private function render_tab_navigation(): void
    {
        echo '<nav class="nav-tab-wrapper">';

        foreach ($this->tabs as $tab_key => $tab_data) {
            $url = add_query_arg([
                'page' => $_GET['page'] ?? 'git_settings',
                'tab' => $tab_key
            ], admin_url('admin.php'));

            $active_class = $this->current_tab === $tab_key ? 'nav-tab-active' : '';

            printf(
                '<a href="%s" class="nav-tab %s">%s</a>',
                esc_url($url),
                esc_attr($active_class),
                esc_html($tab_data['title'])
            );
        }

        echo '</nav>';
    }

    private function render_current_tab_content(): void
    {
        if (isset($this->tabs[$this->current_tab])) {
            $callback = $this->tabs[$this->current_tab]['callback'];

            if (is_callable($callback)) {
                call_user_func($callback);
            }
        } else {
            echo '<div class="git-notice error"><p>' .
                esc_html__('Pestaña no encontrada.', 'central-tickets') .
                '</p></div>';
        }
    }
}
