<?php

namespace App\Services\SmartLock\Providers;

use App\Services\SmartLock\SmartLockProviderInterface;
use App\Models\SmartLock;
use App\Models\AccessCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic webhook-based provider for smart locks with REST APIs
 */
class GenericWebhookProvider implements SmartLockProviderInterface
{
    protected string $baseUrl;
    protected array $headers;

    public function __construct(string $baseUrl = '', array $headers = [])
    {
        $this->baseUrl = $baseUrl;
        $this->headers = $headers;
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->$method($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Smart lock API error: " . $response->body());
            throw new \Exception("API request failed: " . $response->status());
        } catch (\Exception $e) {
            Log::error("Smart lock request exception: " . $e->getMessage());
            throw $e;
        }
    }

    public function testConnection(array $credentials): bool
    {
        try {
            $this->headers = [
                'Authorization' => 'Bearer ' . ($credentials['api_key'] ?? ''),
                'Accept' => 'application/json',
            ];

            $result = $this->makeRequest('get', '/api/status');
            return isset($result['status']) && $result['status'] === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createAccessCode(SmartLock $lock, AccessCode $accessCode): array
    {
        $this->initializeFromLock($lock);

        return $this->makeRequest('post', "/api/locks/{$lock->lock_id}/codes", [
            'code' => $accessCode->code,
            'valid_from' => $accessCode->valid_from->toIso8601String(),
            'valid_until' => $accessCode->valid_until?->toIso8601String(),
            'type' => $accessCode->type,
        ]);
    }

    public function updateAccessCode(SmartLock $lock, AccessCode $accessCode): array
    {
        $this->initializeFromLock($lock);

        return $this->makeRequest(
            'put',
            "/api/locks/{$lock->lock_id}/codes/{$accessCode->external_code_id}",
            [
                'valid_from' => $accessCode->valid_from->toIso8601String(),
                'valid_until' => $accessCode->valid_until?->toIso8601String(),
            ]
        );
    }

    public function deleteAccessCode(SmartLock $lock, AccessCode $accessCode): bool
    {
        $this->initializeFromLock($lock);

        try {
            $this->makeRequest(
                'delete',
                "/api/locks/{$lock->lock_id}/codes/{$accessCode->external_code_id}"
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getLockStatus(SmartLock $lock): array
    {
        $this->initializeFromLock($lock);

        return $this->makeRequest('get', "/api/locks/{$lock->lock_id}/status");
    }

    public function lock(SmartLock $lock): bool
    {
        $this->initializeFromLock($lock);

        try {
            $this->makeRequest('post', "/api/locks/{$lock->lock_id}/lock");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function unlock(SmartLock $lock): bool
    {
        $this->initializeFromLock($lock);

        try {
            $this->makeRequest('post', "/api/locks/{$lock->lock_id}/unlock");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getActivityLogs(SmartLock $lock, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $this->initializeFromLock($lock);

        $params = [];
        if ($from) {
            $params['from'] = $from->format('Y-m-d H:i:s');
        }
        if ($to) {
            $params['to'] = $to->format('Y-m-d H:i:s');
        }

        $endpoint = "/api/locks/{$lock->lock_id}/activities";
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        return $this->makeRequest('get', $endpoint);
    }

    public function syncAccessCodes(SmartLock $lock): array
    {
        $this->initializeFromLock($lock);

        return $this->makeRequest('post', "/api/locks/{$lock->lock_id}/sync-codes");
    }

    protected function initializeFromLock(SmartLock $lock): void
    {
        $credentials = $lock->credentials ?? [];
        
        $this->baseUrl = $credentials['base_url'] ?? '';
        $this->headers = [
            'Authorization' => 'Bearer ' . ($credentials['api_key'] ?? ''),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
