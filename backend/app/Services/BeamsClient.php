<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class BeamsClient
{
    protected string $instanceId;

    protected string $secretKey;

    protected HttpClient $http;

    public function __construct(?string $instanceId = null, ?string $secretKey = null)
    {
        $this->instanceId = $instanceId ?? (string) config('services.beams.instance_id');
        $this->secretKey = $secretKey ?? (string) config('services.beams.secret_key');

        $options = [
            'base_uri' => sprintf('https://%s.pushnotifications.pusher.com', $this->instanceId),
            'timeout' => 5.0,
        ];

        // For local development on Windows/Laragon, disable SSL verification
        if (config('app.env') === 'local' && ! file_exists(ini_get('curl.cainfo'))) {
            $options['verify'] = false;
        }

        $this->http = new HttpClient($options);
    }

    public function isConfigured(): bool
    {
        return filled($this->instanceId) && filled($this->secretKey);
    }

    /**
     * Publish a web push notification to one or more interests.
     *
     * @param  array<string>  $interests
     * @param  array<string,mixed>|null  $data
     * @param  array<string,mixed>  $options  Additional notification options (icon, deep_link)
     * @return array{ok: bool, status?: int, error?: string}
     */
    public function publishToInterests(array $interests, string $title, string $body, ?array $data = null, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'error' => 'Pusher Beams is not configured'];
        }

        $payload = [
            'interests' => array_values(array_unique($interests)),
            'web' => [
                'notification' => array_filter([
                    'title' => $title,
                    'body' => $body,
                    'icon' => $options['icon'] ?? null,
                    'deep_link' => $options['deep_link'] ?? null,
                ]),
            ],
        ];

        if ($data) {
            $payload['web']['data'] = $data;
        }

        try {
            $res = $this->http->post(sprintf('/publish_api/v1/instances/%s/publishes/interests', $this->instanceId), [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return ['ok' => $res->getStatusCode() >= 200 && $res->getStatusCode() < 300, 'status' => $res->getStatusCode()];
        } catch (GuzzleException $e) {
            Log::warning('Beams publish failed: '.$e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
