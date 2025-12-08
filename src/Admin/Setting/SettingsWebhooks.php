<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Displayer;
use CentralTickets\Constants\WebhookStatusConstants;
use CentralTickets\Constants\WebhookTopicConstants;
use CentralTickets\Webhooks\WebhookManager;

final class SettingsWebhooks implements Displayer
{
    public function display()
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