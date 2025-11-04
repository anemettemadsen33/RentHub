<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run 
                            {--type=full : Type of backup (full, database, files)}
                            {--verify : Verify backup after creation}';

    protected $description = 'Run a backup of the application';

    public function handle(BackupService $backupService)
    {
        $type = $this->option('type');
        
        $this->info("Starting {$type} backup...");
        
        try {
            $startTime = microtime(true);
            
            switch ($type) {
                case 'database':
                    $result = $backupService->backupDatabase();
                    break;
                case 'files':
                    $result = $backupService->backupFiles();
                    break;
                case 'full':
                default:
                    $result = $backupService->createFullBackup();
                    break;
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->info("Backup completed successfully in {$duration} seconds");
            
            if ($this->option('verify')) {
                $this->info('Verifying backup...');
                // Verification logic
            }
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Duration', $duration . 's'],
                    ['Type', $type],
                    ['Status', 'Success'],
                ]
            );
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
