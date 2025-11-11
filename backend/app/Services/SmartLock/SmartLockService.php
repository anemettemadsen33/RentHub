<?php

namespace App\Services\SmartLock;

use App\Models\AccessCode;
use App\Models\Booking;
use App\Models\LockActivity;
use App\Models\SmartLock;
use App\Notifications\AccessCodeCreatedNotification;
use App\Notifications\SmartLockLowBatteryNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SmartLockService
{
    protected array $providers = [];

    public function registerProvider(string $name, SmartLockProviderInterface $provider): void
    {
        $this->providers[$name] = $provider;
    }

    public function getProvider(string $name): ?SmartLockProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Create access code for a booking
     */
    public function createAccessCodeForBooking(Booking $booking): ?AccessCode
    {
        $property = $booking->property;
        $smartLock = $property->smartLocks()->where('status', 'active')->first();

        if (! $smartLock) {
            Log::warning("No active smart lock found for property {$property->id}");

            return null;
        }

        if (! $smartLock->auto_generate_codes) {
            Log::info("Auto-generate codes disabled for lock {$smartLock->id}");

            return null;
        }

        // Generate unique code
        $code = AccessCode::generateUniqueCode(6);

        // Create access code
        $accessCode = AccessCode::create([
            'smart_lock_id' => $smartLock->id,
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'code' => $code,
            'type' => 'temporary',
            'valid_from' => Carbon::parse($booking->check_in)->subHours(2), // Allow 2 hours early
            'valid_until' => Carbon::parse($booking->check_out)->addHours(2), // Allow 2 hours late
            'status' => 'pending',
        ]);

        // Try to create on provider
        try {
            $provider = $this->getProvider($smartLock->provider);

            if ($provider) {
                $result = $provider->createAccessCode($smartLock, $accessCode);

                $accessCode->update([
                    'external_code_id' => $result['code_id'] ?? null,
                    'status' => 'active',
                ]);

                // Log activity
                LockActivity::create([
                    'smart_lock_id' => $smartLock->id,
                    'access_code_id' => $accessCode->id,
                    'user_id' => $booking->user_id,
                    'event_type' => 'code_created',
                    'action' => 'code_created',
                    'description' => "Access code created for booking #{$booking->id}",
                ]);

                // Notify guest
                $booking->user->notify(new AccessCodeCreatedNotification($accessCode, $booking));
                $accessCode->update(['notified' => true, 'notified_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to create access code on provider: {$e->getMessage()}");
            // Keep code in pending status for manual sync
        }

        return $accessCode;
    }

    /**
     * Revoke access code
     */
    public function revokeAccessCode(AccessCode $accessCode): bool
    {
        $smartLock = $accessCode->smartLock;

        try {
            $provider = $this->getProvider($smartLock->provider);

            if ($provider && $accessCode->external_code_id) {
                $provider->deleteAccessCode($smartLock, $accessCode);
            }

            $accessCode->update(['status' => 'revoked']);

            // Log activity
            LockActivity::create([
                'smart_lock_id' => $smartLock->id,
                'access_code_id' => $accessCode->id,
                'user_id' => auth()->id(),
                'event_type' => 'code_deleted',
                'action' => 'code_deleted',
                'description' => 'Access code revoked',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to revoke access code: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Sync lock status with provider
     */
    public function syncLockStatus(SmartLock $lock): bool
    {
        try {
            $provider = $this->getProvider($lock->provider);

            if (! $provider) {
                return false;
            }

            $status = $provider->getLockStatus($lock);

            $lock->update([
                'battery_level' => $status['battery_level'] ?? null,
                'status' => $status['status'] ?? 'active',
                'last_synced_at' => now(),
                'error_message' => $status['error'] ?? null,
            ]);

            // Check for low battery
            if ($lock->needsBatteryReplacement()) {
                // Send notification to property owner
                $lock->property->user->notify(new SmartLockLowBatteryNotification($lock));
                Log::warning("Low battery for lock {$lock->id}: {$lock->battery_level}%");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to sync lock status: {$e->getMessage()}");
            $lock->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remote lock operation
     */
    public function remoteLock(SmartLock $lock): bool
    {
        try {
            $provider = $this->getProvider($lock->provider);

            if (! $provider) {
                return false;
            }

            $result = $provider->lock($lock);

            if ($result) {
                LockActivity::create([
                    'smart_lock_id' => $lock->id,
                    'user_id' => auth()->id(),
                    'event_type' => 'lock',
                    'action' => 'lock',
                    'access_method' => 'remote',
                    'description' => 'Lock secured remotely',
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to lock remotely: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Remote unlock operation
     */
    public function remoteUnlock(SmartLock $lock): bool
    {
        try {
            $provider = $this->getProvider($lock->provider);

            if (! $provider) {
                return false;
            }

            $result = $provider->unlock($lock);

            if ($result) {
                LockActivity::create([
                    'smart_lock_id' => $lock->id,
                    'user_id' => auth()->id(),
                    'event_type' => 'unlock',
                    'action' => 'unlock',
                    'access_method' => 'remote',
                    'description' => 'Lock opened remotely',
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to unlock remotely: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Expire old access codes
     */
    public function expireOldAccessCodes(): int
    {
        $expiredCount = 0;

        $expiredCodes = AccessCode::where('status', 'active')
            ->where('valid_until', '<', now())
            ->get();

        foreach ($expiredCodes as $code) {
            $code->update(['status' => 'expired']);
            $expiredCount++;
        }

        return $expiredCount;
    }

    /**
     * Clean up expired codes from providers
     */
    public function cleanupExpiredCodes(): int
    {
        $cleanedCount = 0;

        $expiredCodes = AccessCode::where('status', 'expired')
            ->whereNotNull('external_code_id')
            ->get();

        foreach ($expiredCodes as $code) {
            try {
                $this->revokeAccessCode($code);
                $cleanedCount++;
            } catch (\Exception $e) {
                Log::error("Failed to cleanup code {$code->id}: {$e->getMessage()}");
            }
        }

        return $cleanedCount;
    }
}
