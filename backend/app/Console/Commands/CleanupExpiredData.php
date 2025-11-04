<?php

namespace App\Console\Commands;

use App\Services\Security\DataRetentionService;
use Illuminate\Console\Command;

class CleanupExpiredData extends Command
{
    protected $signature = 'data:cleanup
                          {--dry-run : Preview what would be deleted without actually deleting}';

    protected $description = 'Clean up expired data based on retention policies';

    public function handle(DataRetentionService $retentionService): int
    {
        $this->info('Starting data cleanup process...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No data will be deleted');
            $stats = $retentionService->getRetentionStats();

            $this->table(
                ['Data Type', 'Total Records', 'Expired Records', 'Retention Days'],
                collect($stats)->map(function ($stat, $type) {
                    return [
                        $type,
                        $stat['total_records'],
                        $stat['expired_records'],
                        $stat['retention_days'],
                    ];
                })->toArray()
            );

            return Command::SUCCESS;
        }

        $results = $retentionService->cleanupExpiredData();

        $this->info('Cleanup completed!');
        $this->table(
            ['Data Type', 'Deleted Records'],
            collect($results)->map(function ($count, $type) {
                return [$type, $count];
            })->toArray()
        );

        $totalDeleted = array_sum($results);
        $this->info("Total records deleted: {$totalDeleted}");

        return Command::SUCCESS;
    }
}
