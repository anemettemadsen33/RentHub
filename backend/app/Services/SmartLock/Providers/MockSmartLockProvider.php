<?php

namespace App\Services\SmartLock\Providers;

use App\Services\SmartLock\SmartLockProviderInterface;
use App\Models\SmartLock;
use App\Models\AccessCode;
use Illuminate\Support\Str;

/**
 * Mock provider for testing and development
 */
class MockSmartLockProvider implements SmartLockProviderInterface
{
    public function testConnection(array $credentials): bool
    {
        // Always successful for mock
        return true;
    }

    public function createAccessCode(SmartLock $lock, AccessCode $accessCode): array
    {
        // Simulate API call delay
        usleep(100000); // 0.1 second

        return [
            'success' => true,
            'code_id' => 'mock_' . Str::random(10),
            'code' => $accessCode->code,
            'valid_from' => $accessCode->valid_from->toIso8601String(),
            'valid_until' => $accessCode->valid_until?->toIso8601String(),
        ];
    }

    public function updateAccessCode(SmartLock $lock, AccessCode $accessCode): array
    {
        usleep(100000);

        return [
            'success' => true,
            'code_id' => $accessCode->external_code_id,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function deleteAccessCode(SmartLock $lock, AccessCode $accessCode): bool
    {
        usleep(100000);
        return true;
    }

    public function getLockStatus(SmartLock $lock): array
    {
        usleep(50000);

        return [
            'status' => 'active',
            'battery_level' => rand(60, 100),
            'is_locked' => (bool) rand(0, 1),
            'connectivity' => 'online',
            'last_activity' => now()->subMinutes(rand(1, 60))->toIso8601String(),
        ];
    }

    public function lock(SmartLock $lock): bool
    {
        usleep(200000); // 0.2 seconds
        return true;
    }

    public function unlock(SmartLock $lock): bool
    {
        usleep(200000);
        return true;
    }

    public function getActivityLogs(SmartLock $lock, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $activities = [];
        $types = ['unlock', 'lock', 'code_used'];

        for ($i = 0; $i < rand(5, 15); $i++) {
            $activities[] = [
                'event_type' => $types[array_rand($types)],
                'timestamp' => now()->subHours(rand(1, 48))->toIso8601String(),
                'user' => 'Guest ' . rand(1, 5),
                'method' => ['code', 'app', 'remote'][rand(0, 2)],
            ];
        }

        return $activities;
    }

    public function syncAccessCodes(SmartLock $lock): array
    {
        return [
            'success' => true,
            'synced_count' => $lock->accessCodes()->count(),
            'message' => 'Mock sync completed',
        ];
    }
}
