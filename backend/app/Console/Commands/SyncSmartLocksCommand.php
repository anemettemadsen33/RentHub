<?php

namespace App\Console\Commands;

use App\Models\SmartLock;
use App\Services\SmartLock\SmartLockService;
use Illuminate\Console\Command;

class SyncSmartLocksCommand extends Command
{
    protected $signature = 'smartlocks:sync';

    protected $description = 'Sync all smart locks status and expire old access codes';

    public function __construct(
        private SmartLockService $smartLockService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting smart locks synchronization...');

        // Expire old access codes
        $this->info('Expiring old access codes...');
        $expiredCount = $this->smartLockService->expireOldAccessCodes();
        $this->info("Expired {$expiredCount} access codes");

        // Clean up expired codes from providers
        $this->info('Cleaning up expired codes from providers...');
        $cleanedCount = $this->smartLockService->cleanupExpiredCodes();
        $this->info("Cleaned {$cleanedCount} codes from providers");

        // Sync all active locks
        $this->info('Syncing smart lock status...');
        $locks = SmartLock::where('status', 'active')->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($locks as $lock) {
            $this->info("Syncing lock: {$lock->name} (Property: {$lock->property->title})");

            if ($this->smartLockService->syncLockStatus($lock)) {
                $successCount++;
                $this->line('  ✓ Synced successfully');

                if ($lock->needsBatteryReplacement()) {
                    $this->warn("  ⚠ Low battery: {$lock->battery_level}%");
                }
            } else {
                $errorCount++;
                $this->error("  ✗ Sync failed: {$lock->error_message}");
            }
        }

        $this->info("\nSynchronization complete!");
        $this->info("Success: {$successCount}, Errors: {$errorCount}");

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
