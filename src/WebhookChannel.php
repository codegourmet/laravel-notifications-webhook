<?php

namespace NotificationChannels\Webhook;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use NotificationChannels\Webhook\Exceptions\CouldNotSendNotification;

class WebhookChannel
{
    /** @var Client */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return \GuzzleHttp\Psr7\Response
     *
     * @throws \NotificationChannels\Webhook\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $url = $notifiable->routeNotificationFor('webhook', $notification)) {
            return;
        }

        $webhookData = $notification->toWebhook($notifiable)->toArray();

        try {
            $response = $this->client->post($url, [
                'query' => Arr::get($webhookData, 'query'),
                'body' => json_encode(Arr::get($webhookData, 'data')),
                'verify' => Arr::get($webhookData, 'verify'),
                'headers' => Arr::get($webhookData, 'headers'),
                'http_errors' => false
            ]);
        } catch (RequestException $e) {
            throw CouldNotSendNotification::connectionError($e);
        }

        if ($response->getStatusCode() >= 300 || $response->getStatusCode() < 200) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
        }

        return $response;
    }
}
