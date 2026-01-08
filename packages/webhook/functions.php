<?php
/**
 * Webhook Functions for Central Booking Plugin
 * 
 * This file contains utility functions for managing webhooks within the Central Booking
 * system. These functions provide a simplified interface for creating, saving, and 
 * triggering webhooks.
 * 
 * @package CentralBooking\Webhook
 * @since 1.0.0
 * @author Central Booking Team
 */

use CentralBooking\Webhook\Webhook;
use CentralBooking\Webhook\WebhookManager;
use CentralBooking\Webhook\WebhookStatus;
use CentralBooking\Webhook\WebhookTopic;

/**
 * Creates a new webhook instance with the provided data.
 * 
 * This function instantiates a new Webhook object and populates it with the
 * data provided in the array. It provides default values for optional fields
 * to ensure the webhook is properly configured.
 * 
 * @param array{name:string,topic:WebhookTopic,status:WebhookStatus,delivery_url:string} $data 
 *        Array containing webhook configuration data:
 *        - name: The name/identifier for the webhook
 *        - topic: The webhook topic that determines when it triggers
 *        - status: The current status of the webhook (active/inactive)
 *        - delivery_url: The URL where webhook payloads will be sent
 * 
 * @return Webhook A configured webhook instance ready to be saved
 * 
 * @since 1.0.0
 * 
 * @example
 * $webhook_data = [
 *     'name' => 'Booking Created Notification',
 *     'topic' => WebhookTopic::BOOKING_CREATED,
 *     'status' => WebhookStatus::ACTIVE,
 *     'delivery_url' => 'https://example.com/webhook'
 * ];
 * $webhook = git_create_webhook($webhook_data);
 */
function git_webhook_create(array $data)
{
    $webhook = new Webhook();
    $webhook->name = $data['name'] ?? '';
    $webhook->topic = $data['topic'] ?? WebhookTopic::NONE;
    $webhook->status = $data['status'] ?? WebhookStatus::ACTIVE;
    $webhook->url_delivery = $data['delivery_url'] ?? '';
    return $webhook;
}

/**
 * Saves a webhook to persistent storage.
 * 
 * This function delegates to the WebhookManager singleton to handle
 * the actual persistence of the webhook data. The webhook can be either
 * a new webhook (insert) or an existing webhook (update).
 * 
 * @param Webhook $webhook The webhook instance to save
 * 
 * @return mixed The result of the save operation (typically boolean success or ID)
 * 
 * @since 1.0.0
 * 
 * @throws Exception If the webhook data is invalid or save operation fails
 * 
 * @example
 * $webhook = git_create_webhook($webhook_data);
 * $result = git_save_webhook($webhook);
 * if ($result) {
 *     echo "Webhook saved successfully";
 * }
 */
function git_webhook_save(Webhook $webhook)
{
    return WebhookManager::getInstance()->save($webhook);
}

/**
 * Triggers all active webhooks for a specific topic.
 * 
 * This function finds all webhooks registered for the given topic and
 * sends the payload to their respective delivery URLs. Only active webhooks
 * will be triggered.
 * 
 * @param WebhookTopic $topic The topic/event that occurred (e.g., booking created, updated)
 * @param array $payload The data to send to the webhook endpoints
 * 
 * @return void
 * 
 * @since 1.0.0
 * 
 * @example
 * // Trigger webhooks when a new booking is created
 * $booking_data = [
 *     'id' => 123,
 *     'customer_name' => 'John Doe',
 *     'booking_date' => '2026-01-10',
 *     'status' => 'confirmed'
 * ];
 * git_trigger_webhook(WebhookTopic::BOOKING_CREATED, $booking_data);
 */
function git_webhook_trigger(WebhookTopic $topic, array $payload): void
{
    WebhookManager::getInstance()->trigger($topic, $payload);
}