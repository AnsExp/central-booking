<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Webhook\WebhookManager;
use CentralBooking\Webhook\WebhookStatus;
use CentralBooking\Webhook\WebhookTopic;

final class FormWebhook implements DisplayerInterface
{
    public function render()
    {
        $name_input = new InputComponent('name');
        $delivery_url_input = new InputComponent('delivery_url');
        $status_select = new SelectComponent('status');
        $topic_select = new SelectComponent('topic');
        $name_input->setRequired(true);
        $topic_select->setRequired(true);
        $status_select->setRequired(true);
        $delivery_url_input->setRequired(true);
        $name_input->styles->set('width', '300px');
        $topic_select->styles->set('width', '300px');
        $status_select->styles->set('width', '300px');
        $delivery_url_input->styles->set('width', '300px');
        $topic_select->setRequired(true);
        $status_select->setRequired(true);
        $delivery_url_input->setRequired(true);
        foreach (WebhookStatus::cases() as $status) {
            $status_select->addOption($status->label());
        }
        foreach (WebhookTopic::cases() as $topic) {
            $topic_select->addOption($topic->label());
        }
        $id = $_GET['id'] ?? '0';
        $webhook = WebhookManager::getInstance()->get(intval($id));
        if ($webhook) {
            $name_input->setValue($webhook->name);
            $topic_select->setValue($webhook->topic->value);
            $status_select->setValue($webhook->status->value);
            $delivery_url_input->setValue($webhook->url_delivery);
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
                            <?= $name_input->getLabel('Nombre')->compact() ?>
                        </th>
                        <td>
                            <?= $name_input->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $status_select->getLabel('Estado')->compact() ?>
                        </th>
                        <td>
                            <?= $status_select->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $topic_select->getLabel('Tema')->compact() ?>
                        </th>
                        <td>
                            <?= $topic_select->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $delivery_url_input->getLabel('URL de entrega')->compact() ?>
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
}
