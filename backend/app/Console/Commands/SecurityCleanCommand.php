<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Security\APIKeyService;
use App\Services\Security\SecurityAuditService;
use App\Services\Security\GDPRService;

class SecurityCleanCommand extends Command
{
    protected $signature = 'security:clean 
                          {--tokens : Clean expired tokens}
                          {--logs : Clean old audit logs}
                          {--data : Clean old data per retention policy}
                          {--all : Clean everything}';

    protected $description = 'Clean expired security data and old logs';

    public function handle(
        APIKeyService $apiKeyService,
        SecurityAuditService $auditService,
        GDPRService $gdprService
    ): int {
        $this->info('Starting security cleanup...');
        $this->newLine();

        $cleanAll = $this->option('all');
        $cleaned = 0;

        if ($this->option('tokens') || $cleanAll) {
            $this->task('Cleaning expired API keys', function () use ($apiKeyService, &$cleaned) {
                $count = $apiKeyService->cleanExpiredKeys();
                $cleaned += $count;
                $this->info("  Removed {$count} expired API keys");
                return true;
            });
        }

        if ($this->option('logs') || $cleanAll) {
            $this->task('Cleaning old audit logs', function () use ($auditService, &$cleaned) {
                $count = $auditService->cleanOldLogs();
                $cleaned += $count;
                $this->info("  Removed {$count} old audit logs");
                return true;
            });
        }

        if ($this->option('data') || $cleanAll) {
            $this->task('Cleaning old data per retention policy', function () use ($gdprService, &$cleaned) {
                $count = $gdprService->cleanOldData();
                $cleaned += $count;
                $this->info("  Removed {$count} old data records");
                return true;
            });
        }

        if (!$this->option('tokens') && !$this->option('logs') && !$this->option('data') && !$cleanAll) {
            $this->warn('Please specify what to clean: --tokens, --logs, --data, or --all');
            return 1;
        }

        $this->newLine();
        $this->info("✓ Cleanup complete! Total items removed: {$cleaned}");

        return 0;
    }
}
