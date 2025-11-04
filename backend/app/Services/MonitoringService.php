<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MonitoringService
{
    protected $provider;
    protected $config;

    public function __construct()
    {
        $this->provider = config('monitoring.provider');
        $this->config = config('monitoring');
    }

    /**
     * Track custom metric
     */
    public function metric(string $name, float $value, array $tags = []): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        try {
            switch ($this->provider) {
                case 'datadog':
                    $this->sendToDatadog($name, $value, $tags);
                    break;
                case 'newrelic':
                    $this->sendToNewRelic($name, $value, $tags);
                    break;
                case 'prometheus':
                    $this->sendToPrometheus($name, $value, $tags);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send metric', [
                'metric' => $name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Track event
     */
    public function event(string $name, array $data = []): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        try {
            switch ($this->provider) {
                case 'datadog':
                    $this->sendEventToDatadog($name, $data);
                    break;
                case 'newrelic':
                    $this->sendEventToNewRelic($name, $data);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send event', [
                'event' => $name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Start transaction trace
     */
    public function startTransaction(string $name, string $type = 'web'): void
    {
        if ($this->provider === 'newrelic' && function_exists('newrelic_start_transaction')) {
            newrelic_start_transaction(config('newrelic.app_name'));
            newrelic_name_transaction($name);
        }
    }

    /**
     * End transaction
     */
    public function endTransaction(): void
    {
        if ($this->provider === 'newrelic' && function_exists('newrelic_end_transaction')) {
            newrelic_end_transaction();
        }
    }

    /**
     * Record exception
     */
    public function recordException(\Throwable $exception, array $context = []): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Always log locally
        Log::error($exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context,
        ]);

        try {
            switch ($this->provider) {
                case 'datadog':
                    $this->sendExceptionToDatadog($exception, $context);
                    break;
                case 'newrelic':
                    if (function_exists('newrelic_notice_error')) {
                        newrelic_notice_error($exception->getMessage(), $exception);
                    }
                    break;
            }
        } catch (\Exception $e) {
            // Silently fail to avoid cascading errors
        }
    }

    /**
     * Send metric to DataDog
     */
    protected function sendToDatadog(string $name, float $value, array $tags): void
    {
        if (!$this->config['datadog']['enabled']) {
            return;
        }

        $apiKey = $this->config['datadog']['api_key'];
        $host = $this->config['datadog']['host'];

        $data = [
            'series' => [
                [
                    'metric' => $name,
                    'points' => [[time(), $value]],
                    'type' => 'gauge',
                    'tags' => $this->formatTags($tags),
                ],
            ],
        ];

        Http::withHeaders([
            'DD-API-KEY' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("https://{$host}/api/v1/series", $data);
    }

    /**
     * Send event to DataDog
     */
    protected function sendEventToDatadog(string $name, array $data): void
    {
        if (!$this->config['datadog']['enabled']) {
            return;
        }

        $apiKey = $this->config['datadog']['api_key'];
        $host = $this->config['datadog']['host'];

        $eventData = [
            'title' => $name,
            'text' => json_encode($data),
            'tags' => $this->formatTags($data['tags'] ?? []),
            'alert_type' => $data['alert_type'] ?? 'info',
        ];

        Http::withHeaders([
            'DD-API-KEY' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("https://{$host}/api/v1/events", $eventData);
    }

    /**
     * Send exception to DataDog
     */
    protected function sendExceptionToDatadog(\Throwable $exception, array $context): void
    {
        $this->sendEventToDatadog('exception', [
            'message' => $exception->getMessage(),
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
            'alert_type' => 'error',
        ]);
    }

    /**
     * Send metric to New Relic
     */
    protected function sendToNewRelic(string $name, float $value, array $tags): void
    {
        if (!$this->config['newrelic']['enabled']) {
            return;
        }

        if (function_exists('newrelic_custom_metric')) {
            newrelic_custom_metric("Custom/{$name}", $value);
        }
    }

    /**
     * Send event to New Relic
     */
    protected function sendEventToNewRelic(string $name, array $data): void
    {
        if (!$this->config['newrelic']['enabled']) {
            return;
        }

        if (function_exists('newrelic_record_custom_event')) {
            newrelic_record_custom_event($name, $data);
        }
    }

    /**
     * Send to Prometheus
     */
    protected function sendToPrometheus(string $name, float $value, array $tags): void
    {
        // Prometheus uses a pull model, so we store metrics in cache
        $key = "prometheus:{$name}:" . md5(json_encode($tags));
        Cache::put($key, ['value' => $value, 'tags' => $tags], now()->addMinutes(5));
    }

    /**
     * Format tags for monitoring service
     */
    protected function formatTags(array $tags): array
    {
        $formatted = [];
        foreach ($tags as $key => $value) {
            if (is_numeric($key)) {
                $formatted[] = $value;
            } else {
                $formatted[] = "{$key}:{$value}";
            }
        }
        return $formatted;
    }

    /**
     * Check if monitoring is enabled
     */
    protected function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Send alert
     */
    public function sendAlert(string $title, string $message, string $severity = 'warning', array $context = []): void
    {
        $alertConfig = $this->config['alerts'] ?? [];
        
        if (!($alertConfig['enabled'] ?? false)) {
            return;
        }

        // Send to configured channels
        foreach ($alertConfig['channels'] ?? [] as $channel => $config) {
            if (!($config['enabled'] ?? false)) {
                continue;
            }

            try {
                switch ($channel) {
                    case 'slack':
                        $this->sendToSlack($title, $message, $severity, $config);
                        break;
                    case 'email':
                        $this->sendAlertEmail($title, $message, $severity, $config, $context);
                        break;
                    case 'pagerduty':
                        $this->sendToPagerDuty($title, $message, $severity, $config);
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send alert via {$channel}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Send alert to Slack
     */
    protected function sendToSlack(string $title, string $message, string $severity, array $config): void
    {
        $color = match ($severity) {
            'critical' => '#ff0000',
            'warning' => '#ff9900',
            'info' => '#00ff00',
            default => '#999999',
        };

        Http::post($config['webhook_url'], [
            'channel' => $config['channel'],
            'attachments' => [
                [
                    'title' => $title,
                    'text' => $message,
                    'color' => $color,
                    'fields' => [
                        [
                            'title' => 'Severity',
                            'value' => $severity,
                            'short' => true,
                        ],
                        [
                            'title' => 'Environment',
                            'value' => app()->environment(),
                            'short' => true,
                        ],
                    ],
                    'ts' => time(),
                ],
            ],
        ]);
    }

    /**
     * Send alert email
     */
    protected function sendAlertEmail(string $title, string $message, string $severity, array $config, array $context): void
    {
        // Implementation would use Laravel Mail
        // This is a placeholder for the actual email sending logic
    }

    /**
     * Send to PagerDuty
     */
    protected function sendToPagerDuty(string $title, string $message, string $severity, array $config): void
    {
        $severityMap = [
            'critical' => 'critical',
            'warning' => 'warning',
            'info' => 'info',
        ];

        Http::post('https://events.pagerduty.com/v2/enqueue', [
            'routing_key' => $config['integration_key'],
            'event_action' => 'trigger',
            'payload' => [
                'summary' => $title,
                'severity' => $severityMap[$severity] ?? 'warning',
                'source' => config('app.name'),
                'custom_details' => [
                    'message' => $message,
                    'environment' => app()->environment(),
                ],
            ],
        ]);
    }
}
