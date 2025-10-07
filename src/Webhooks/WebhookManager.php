<?php
namespace CentralTickets\Webhooks;

use CentralTickets\Constants\WebhookStatusConstants;
use CentralTickets\Constants\WebhookTopicConstants;

class WebhookManager
{
    private static ?WebhookManager $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    public function save(Webhook $webhook)
    {
        if (!filter_var($webhook->url_delivery, FILTER_VALIDATE_URL)) {
            return false;
        }
        if (!WebhookStatusConstants::is_valid($webhook->status)) {
            return false;
        }
        if (!WebhookTopicConstants::is_valid($webhook->topic)) {
            return false;
        }
        $data = [
            'name' => $webhook->name,
            'topic' => $webhook->topic,
            'secret' => $webhook->secret,
            'status' => $webhook->status,
            'delivery_url' => $webhook->url_delivery
        ];
        global $wpdb;
        if ($webhook->id > 0) {
            $wpdb->update(
                $wpdb->prefix . 'git_webhooks',
                $data,
                ['id' => $webhook->id]
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'git_webhooks',
                $data
            );
            $webhook->id = $wpdb->insert_id;
        }
        return $webhook;
    }

    public function get(int $id)
    {
        global $wpdb;
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}git_webhooks WHERE id = %d",
            $id
        ), ARRAY_A);
        if ($result === null) {
            return null;
        }
        return $this->parse_webhook($result);
    }

    public function get_all()
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}git_webhooks"
        ), ARRAY_A);
        return array_map([$this, 'parse_webhook'], $results);
    }

    public function get_by_topic(string $topic)
    {
        if (!WebhookTopicConstants::is_valid($topic)) {
            return [];
        }
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}git_webhooks WHERE topic = %s",
            $topic
        ), ARRAY_A);
        return array_map([$this, 'parse_webhook'], $results);
    }

    public function trigger(string $topic, array $payload)
    {
        $webhooks = $this->get_by_topic($topic);
        foreach ($webhooks as $webhook) {
            $webhook->send($payload);
        }
    }

    private function parse_webhook(array $data)
    {
        $webhook = new Webhook();
        $webhook->id = $data['id'];
        $webhook->name = $data['name'];
        $webhook->topic = $data['topic'];
        $webhook->secret = $data['secret'];
        $webhook->status = $data['status'];
        $webhook->url_delivery = $data['delivery_url'];
        return $webhook;
    }
}