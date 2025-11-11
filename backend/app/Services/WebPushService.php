<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    protected WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url') ?: 'mailto:admin@localhost',
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ],
        ]);
        $this->webPush->setAutomaticPadding(0);
    }

    public function sendToSubscription(PushSubscription $sub, array $payload): void
    {
        $subscription = Subscription::create([
            'endpoint' => $sub->endpoint,
            'publicKey' => $sub->public_key,
            'authToken' => $sub->auth_token,
            'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
        ]);

        $this->webPush->queueNotification($subscription, json_encode($payload));
        foreach ($this->webPush->flush() as $report) {
            // Swallow errors for now; could log $report
        }
    }
}
