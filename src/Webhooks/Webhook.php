<?php
namespace CentralTickets\Webhooks;

use CentralTickets\Constants\WebhookStatusConstants;

class Webhook
{
    public int $id = 0;
    public string $name = '';
    public string $secret = '';
    public string $status = WebhookStatusConstants::ACTIVE;
    public string $topic = 'none';
    public string $url_delivery = '';

    public function send(array $payload)
    {
        if ($this->status !== WebhookStatusConstants::ACTIVE) {
            return false;
        }
        $payload['secret'] = $this->secret;
        $args = [
            'method' => 'POST',
            'headers' => ['Content-Type' => 'application/json'],
            'body' => git_serialize($payload),
        ];
        $response = wp_remote_post($this->url_delivery, $args);
        return $response['response']['code'] >= 200 && $response['response']['code'] < 300;
    }
}