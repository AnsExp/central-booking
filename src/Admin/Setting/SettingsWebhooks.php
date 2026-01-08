<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Webhook\WebhookManager;

final class SettingsWebhooks implements DisplayerInterface
{
    public function render()
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
                $webhook_manager = WebhookManager::getInstance();
                $webhooks = $webhook_manager->getAll();
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
                            <td><?= esc_html($webhook->status->label()) ?></td>
                            <td><?= esc_html($webhook->topic->label()) ?></td>
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