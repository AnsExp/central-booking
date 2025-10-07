<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\SelectComponent;
use CentralTickets\Constants\WebhookStatusConstants;
use CentralTickets\Constants\WebhookTopicConstants;
use CentralTickets\Webhooks\WebhookManager;

final class SettingsWebhooks implements Displayer
{
    public function display()
    {
        ?>
        <h2 class="wc-table-list-header">
            Webhooks
            <?php if (!(isset($_GET['action']) && $_GET['action'] === 'edit')): ?>
                <a href="<?= esc_url(add_query_arg(['action' => 'edit'])) ?>" class="page-title-action">Crear webhook</a>
            <?php else: ?>
                <a href="<?= esc_url(add_query_arg(['action' => ''])) ?>" class="page-title-action">Volver</a>
            <?php endif; ?>
        </h2>
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'edit') {
            $this->get_form();
        } else {
            $this->get_table();
        }
    }

    private function get_form()
    {
        $name_input = new InputComponent('name');
        $delivery_url_input = new InputComponent('delivery_url');
        $status_select = new SelectComponent('status');
        $topic_select = new SelectComponent('topic');
        $name_input->set_required(true);
        $topic_select->set_required(true);
        $status_select->set_required(true);
        $delivery_url_input->set_required(true);
        $name_input->styles->set('width', '300px');
        $topic_select->styles->set('width', '300px');
        $status_select->styles->set('width', '300px');
        $delivery_url_input->styles->set('width', '300px');
        $topic_select->set_required(true);
        $status_select->set_required(true);
        $delivery_url_input->set_required(true);
        foreach (WebhookStatusConstants::get_all() as $status) {
            $status_select->add_option(WebhookStatusConstants::get_display_name($status), $status);
        }
        foreach (WebhookTopicConstants::get_all() as $topic) {
            $topic_select->add_option(WebhookTopicConstants::get_display_name($topic), $topic);
        }
        $id = $_GET['id'] ?? '0';
        $webhook = WebhookManager::get_instance()->get(intval($id));
        if ($webhook) {
            $name_input->set_value($webhook->name);
            $topic_select->set_value($webhook->topic);
            $status_select->set_value($webhook->status);
            $delivery_url_input->set_value($webhook->url_delivery);
        }
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <input type="hidden" name="scope" value="webhooks">
            <input type="hidden" name="id" value="<?= esc_attr($id) ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" class="titledesc">
                            <?= $name_input->get_label('Nombre')->compact() ?>
                        </th>
                        <td>
                            <?= $name_input->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $status_select->get_label('Estado')->compact() ?>
                        </th>
                        <td>
                            <?= $status_select->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $topic_select->get_label('Tema')->compact() ?>
                        </th>
                        <td>
                            <?= $topic_select->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $delivery_url_input->get_label('URL de entrega')->compact() ?>
                        </th>
                        <td>
                            <?= $delivery_url_input->compact() ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Guardar</button>
            </p>
        </form>
        <?php
    }

    private function get_table()
    {
        ?>
        <table style="margin-top: 20px;" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Tema</th>
                    <th>URL de entrega</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $webhook_manager = WebhookManager::get_instance();
                $webhooks = $webhook_manager->get_all();
                if (empty($webhooks)) {
                    echo '<tr><td colspan="4">No hay webhooks registrados.</td></tr>';
                } else {
                    foreach ($webhooks as $webhook) {
                        ?>
                        <tr>
                            <td>
                                <span><?= esc_html($webhook->name) ?></span>
                                <div class="row-actions visible">
                                    <span class="edit">
                                        ID: <?= $webhook->id ?>
                                    </span>
                                    <span> | </span>
                                    <span class="edit">
                                        <a href="<?= add_query_arg(['action' => 'edit', 'id' => $webhook->id]) ?>">Editar</a>
                                    </span>
                                </div>
                            </td>
                            <td><?= esc_html(WebhookStatusConstants::get_display_name($webhook->status)) ?></td>
                            <td><?= esc_html(WebhookTopicConstants::get_display_name($webhook->topic)) ?></td>
                            <td><?= esc_html($webhook->url_delivery) ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
        </table>
        <?php
    }
}