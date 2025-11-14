#!/usr/bin/env php
<?php

/**
 * Comprehensive API Testing Script
 * Tests all endpoints and generates detailed reports
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class ComprehensiveApiTester
{
    private string $baseUrl;
    private ?string $token;
    private array $results = [];
    private array $summary = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'auth_required' => 0,
        'by_status' => [],
    ];

    public function __construct(string $baseUrl, ?string $token = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
    }

    public function testAll(): void
    {
        echo "🚀 Starting Comprehensive API Testing...\n";
        echo "Base URL: {$this->baseUrl}\n";
        echo str_repeat('=', 80) . "\n\n";

        $routes = $this->getAllRoutes();
        $this->summary['total'] = count($routes);

        foreach ($routes as $index => $route) {
            $result = $this->testRoute($route);
            $this->results[] = $result;
            $this->updateSummary($result);
            
            $this->printProgress($index + 1, count($routes), $result);
            usleep(100000); // 100ms delay
        }

        $this->printFinalReport();
        $this->saveReport();
    }

    private function getAllRoutes(): array
    {
        $routes = [];
        $collection = Route::getRoutes();

        foreach ($collection as $route) {
            if (str_starts_with($route->uri(), 'api/')) {
                $methods = $route->methods();
                foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
                    if (in_array($method, $methods)) {
                        $routes[] = [
                            'uri' => $route->uri(),
                            'method' => $method,
                            'middleware' => $route->middleware(),
                            'name' => $route->getName(),
                        ];
                    }
                }
            }
        }

        return $routes;
    }

    private function testRoute(array $route): array
    {
        // Skip fallback routes
        if ($route['uri'] === 'api/{fallbackPlaceholder}') {
            return array_merge($route, [
                'status' => 'skipped',
                'message' => 'Fallback route',
            ]);
        }

        // Substitute placeholders
        $uri = $this->substituteBlaceholders($route['uri']);
        $fullUrl = "{$this->baseUrl}/{$uri}";
        
        $needsAuth = $this->needsAuth($route['middleware']);
        $headers = [];
        
        if ($needsAuth && $this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        try {
            $client = Http::timeout(10)->withHeaders($headers);
            
            if (config('app.env') === 'local') {
                $client = $client->withoutVerifying();
            }

            $response = $client->send($route['method'], $fullUrl);
            
            $statusCode = $response->status();
            $isSuccess = $response->successful();
            
            return array_merge($route, [
                'status' => $this->categorizeStatus($statusCode, $needsAuth, $this->token),
                'status_code' => $statusCode,
                'success' => $isSuccess,
                'response_time' => $response->handlerStats()['total_time'] ?? 0,
                'body_size' => strlen($response->body()),
                'has_json' => $this->isJson($response->body()),
            ]);
            
        } catch (\Exception $e) {
            return array_merge($route, [
                'status' => 'error',
                'status_code' => 0,
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function substituteBlaceholders(string $uri): string
    {
        $substitutions = [
            '{version}' => '1',
            '{id}' => '1',
            '{propertyId}' => '1',
            '{userId}' => '1',
            '{bookingId}' => '1',
            '{code}' => 'en',
            '{token}' => 'sample-token',
            '{hash}' => 'sample-hash',
            '{provider}' => 'google',
            '{property}' => '1',
            '{booking}' => '1',
            '{payment}' => '1',
            '{invoice}' => '1',
            '{conversationId}' => '1',
            '{externalCalendar}' => '1',
        ];

        foreach ($substitutions as $placeholder => $value) {
            $uri = str_replace($placeholder, $value, $uri);
        }

        return $uri;
    }

    private function needsAuth(array $middleware): bool
    {
        return collect($middleware)->contains(function ($m) {
            return is_string($m) && (str_contains($m, 'auth') || str_contains($m, 'sanctum'));
        });
    }

    private function categorizeStatus(int $code, bool $needsAuth, ?string $token): string
    {
        if ($code === 200 || $code === 201) {
            return 'success';
        }

        if ($code === 401 || $code === 403) {
            return $needsAuth && !$token ? 'auth_required' : 'unauthorized';
        }

        if ($code === 404) {
            return 'not_found';
        }

        if ($code === 405) {
            return 'method_not_allowed';
        }

        if ($code === 422) {
            return 'validation_error';
        }

        if ($code >= 500) {
            return $needsAuth && !$token ? 'likely_auth_error' : 'server_error';
        }

        return 'unknown';
    }

    private function isJson(string $body): bool
    {
        json_decode($body);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function updateSummary(array $result): void
    {
        if ($result['success']) {
            $this->summary['success']++;
        } else {
            $this->summary['failed']++;
        }

        $status = $result['status'];
        if (!isset($this->summary['by_status'][$status])) {
            $this->summary['by_status'][$status] = 0;
        }
        $this->summary['by_status'][$status]++;
    }

    private function printProgress(int $current, int $total, array $result): void
    {
        $percentage = round(($current / $total) * 100, 1);
        $bar = str_repeat('█', (int)($percentage / 2));
        $space = str_repeat('░', 50 - (int)($percentage / 2));
        
        $statusIcon = $result['success'] ? '✅' : '❌';
        $statusCode = $result['status_code'] ?? 0;
        
        echo "\r[{$bar}{$space}] {$percentage}% | {$statusIcon} {$result['method']} {$result['uri']} ({$statusCode})";
    }

    private function printFinalReport(): void
    {
        echo "\n\n" . str_repeat('=', 80) . "\n";
        echo "📊 FINAL REPORT\n";
        echo str_repeat('=', 80) . "\n\n";

        echo "Total Routes Tested: {$this->summary['total']}\n";
        echo "✅ Successful: {$this->summary['success']}\n";
        echo "❌ Failed: {$this->summary['failed']}\n";
        echo "Success Rate: " . round(($this->summary['success'] / $this->summary['total']) * 100, 2) . "%\n\n";

        echo "Status Breakdown:\n";
        arsort($this->summary['by_status']);
        foreach ($this->summary['by_status'] as $status => $count) {
            $icon = match($status) {
                'success' => '✅',
                'auth_required', 'unauthorized' => '🔒',
                'not_found' => '📭',
                'server_error', 'likely_auth_error' => '🔥',
                'validation_error' => '⚠️',
                default => '❓',
            };
            echo "  {$icon} {$status}: {$count}\n";
        }

        echo "\n" . str_repeat('=', 80) . "\n";
    }

    private function saveReport(): void
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "api-test-report-{$timestamp}.json";
        
        $report = [
            'timestamp' => $timestamp,
            'base_url' => $this->baseUrl,
            'summary' => $this->summary,
            'results' => $this->results,
        ];

        file_put_contents(
            storage_path("logs/{$filename}"),
            json_encode($report, JSON_PRETTY_PRINT)
        );

        echo "\n📄 Report saved to: storage/logs/{$filename}\n";
    }
}

// Parse command line arguments
$baseUrl = $argv[1] ?? 'http://localhost:8000/api';
$token = $argv[2] ?? null;

$tester = new ComprehensiveApiTester($baseUrl, $token);
$tester->testAll();
