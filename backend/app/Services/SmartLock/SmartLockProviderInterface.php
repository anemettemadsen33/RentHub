<?php

namespace App\Services\SmartLock;

use App\Models\SmartLock;
use App\Models\AccessCode;

interface SmartLockProviderInterface
{
    /**
     * Test connection to the lock provider
     */
    public function testConnection(array $credentials): bool;

    /**
     * Create an access code on the smart lock
     */
    public function createAccessCode(SmartLock $lock, AccessCode $accessCode): array;

    /**
     * Update an existing access code
     */
    public function updateAccessCode(SmartLock $lock, AccessCode $accessCode): array;

    /**
     * Delete an access code from the smart lock
     */
    public function deleteAccessCode(SmartLock $lock, AccessCode $accessCode): bool;

    /**
     * Get lock status (battery, connectivity, etc.)
     */
    public function getLockStatus(SmartLock $lock): array;

    /**
     * Lock the smart lock remotely
     */
    public function lock(SmartLock $lock): bool;

    /**
     * Unlock the smart lock remotely
     */
    public function unlock(SmartLock $lock): bool;

    /**
     * Get activity logs from the provider
     */
    public function getActivityLogs(SmartLock $lock, ?\DateTime $from = null, ?\DateTime $to = null): array;

    /**
     * Sync access codes between our system and provider
     */
    public function syncAccessCodes(SmartLock $lock): array;
}
