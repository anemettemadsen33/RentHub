<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ApiSmokeTestCommand extends Command
{
    protected $signature = 'api:smoke {--auth= : User ID to authenticate as for protected routes} {--limit=0 : Limit number of routes tested (0 = all)} {--method= : Only test specific HTTP method} {--base= : Override base API URL (Forge/Vercel)} {--token= : Bearer token to use for authenticated routes} {--insecure : Disable TLS certificate verification (local testing only)}';

    protected $description = 'Run a lightweight smoke test against public and authenticated API routes and report status codes & basic schema';

    public function handle(): int
    {
        $override = $this->option('base');
        $baseUrl = rtrim($override ?: (config('app.url') ?: 'http://127.0.0.1'), '/').'/api';
        $authUserId = $this->option('auth');
        $bearerToken = $this->option('token');
        $methodFilter = $this->option('method');
        $limit = (int) $this->option('limit');

        $this->info("API Smoke Test starting: base={$baseUrl}");

        // Collect all routes under /api
        $routes = collect(Route::getRoutes())->filter(function ($route) {
            return Str::startsWith($route->uri(), 'api/');
        })->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ];
        })->values();

        if ($methodFilter) {
            $routes = $routes->filter(fn ($r) => in_array(strtoupper($methodFilter), $r['methods']));
        }

        if ($limit > 0) {
            $routes = $routes->take($limit);
        }

        $results = [];
        $token = $bearerToken ?: null;

        if (! $token && $authUserId) {
            $this->comment("Authenticating as user ID {$authUserId}...");
            $userModel = config('auth.providers.users.model');
            $user = $userModel::find($authUserId);
            if (! $user) {
                $this->error('User not found for auth context');

                return 1;
            }
            // Create a personal access token (Sanctum) if available
            if (method_exists($user, 'createToken')) {
                $token = $user->createToken('smoke-test')->plainTextToken;
                $this->info('Token created for authenticated requests');
            }
        }

        $client = Http::timeout(10);
        if ($this->option('insecure')) {
            $this->warn('TLS verification disabled for this run (insecure)');
            $client = $client->withoutVerifying();
        }
        $headers = $token ? ['Authorization' => 'Bearer '.$token] : [];

        foreach ($routes as $route) {
            // Only test GET & POST for fast smoke unless method filter applied
            $methodsToTest = $methodFilter ? [$methodFilter] : array_intersect($route['methods'], ['GET', 'POST']);
            foreach ($methodsToTest as $method) {
                // Skip CSRF-only or fallback routes
                if ($route['uri'] === 'api/{fallbackPlaceholder}') {
                    continue;
                }

                $uri = $route['uri'];
                // Substitute common placeholders with sample values for realistic testing
                $substitutions = [
                    '{version}' => '1',  // For routes like /v{version}/properties -> /v1/properties
                    '{id}' => '1',
                    '{propertyId}' => '1',
                    '{userId}' => '1',
                    '{bookingId}' => '1',
                    '{code}' => 'en',
                    '{token}' => 'sample-token',
                    '{hash}' => 'sample-hash',
                    '{provider}' => 'google',
                    '{property}' => '1',
                ];
                foreach ($substitutions as $placeholder => $value) {
                    $uri = str_replace($placeholder, $value, $uri);
                }

                $fullUrl = rtrim($baseUrl, '/').'/'.preg_replace('/^api\//', '', $uri);
                $needsAuth = collect($route['middleware'])->contains(function ($m) {
                    return Str::contains($m, ['auth', 'sanctum']);
                });
                $attemptedHeaders = $needsAuth ? $headers : [];

                try {
                    $response = $client->withHeaders($attemptedHeaders)->send($method, $fullUrl);
                    $json = $this->parseJson($response->body());
                    $code = $response->status();
                    $isSuccess = $response->successful();

                    // Detect potential missing auth when 500 returned without token
                    $authIssue = false;
                    if (! $isSuccess && $needsAuth && ! $token && $code === 500) {
                        $authIssue = true;
                    }

                    $results[] = [
                        'uri' => $uri,
                        'method' => $method,
                        'code' => $code,
                        'auth' => $needsAuth ? ($token ? 'ok' : ($authIssue ? 'likely_missing' : 'missing')) : 'public',
                        'ok' => $isSuccess,
                        'json_keys' => $json ? array_slice(array_keys($json), 0, 8) : null,
                    ];
                } catch (\Throwable $e) {
                    $results[] = [
                        'uri' => $uri,
                        'method' => $method,
                        'code' => 0,
                        'auth' => $needsAuth ? ($token ? 'ok' : 'missing') : 'public',
                        'ok' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        // Aggregate summary
        $total = count($results);
        $success = collect($results)->where('ok', true)->count();
        $authMissing = collect($results)->where('auth', 'missing')->count();
        $this->table(['Method', 'URI', 'Code', 'Auth', 'OK', 'Keys'], array_map(function ($r) {
            return [
                $r['method'] ?? '-',
                $r['uri'] ?? '-',
                $r['code'] ?? '-',
                $r['auth'] ?? '-',
                isset($r['ok']) && $r['ok'] ? '✔' : '✖',
                isset($r['json_keys']) && $r['json_keys'] ? implode(',', $r['json_keys']) : ($r['error'] ?? '-'),
            ];
        }, $results));

        $this->info("Summary: {$success}/{$total} successful; {$authMissing} auth missing attempts");

        Cache::put('api_smoke_results', $results, 300);
        Log::info('api_smoke_results', ['summary' => compact('total', 'success', 'authMissing')]);

        return 0;
    }

    protected function parseJson(string $body): ?array
    {
        if ($body === '') {
            return null;
        }
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            return is_array($data) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
