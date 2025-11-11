<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--compress}';

    protected $description = 'Backup the database';

    public function handle(): int
    {
        $this->info('Starting database backup...');

        $filename = 'backup-'.now()->format('Y-m-d-His').'.sql';
        $path = storage_path('app/backups/'.$filename);

        // Create backups directory if it doesn't exist
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Export database
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            $host,
            $username,
            $password,
            $database,
            $path
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Database backup failed!');

            return self::FAILURE;
        }

        $this->info('Database backed up to: '.$path);

        // Compress if requested
        if ($this->option('compress')) {
            $this->compressBackup($path);
        }

        // Clean old backups (keep last 30 days)
        $this->cleanOldBackups();

        $this->info('Backup completed successfully!');

        return self::SUCCESS;
    }

    protected function compressBackup(string $path): void
    {
        $zip = new ZipArchive;
        $zipPath = $path.'.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $zip->addFile($path, basename($path));
            $zip->close();
            unlink($path);
            $this->info('Backup compressed to: '.$zipPath);
        }
    }

    protected function cleanOldBackups(): void
    {
        $backupPath = storage_path('app/backups');
        $files = glob($backupPath.'/backup-*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 30 * 24 * 3600) { // 30 days
                    unlink($file);
                    $this->info('Deleted old backup: '.basename($file));
                }
            }
        }
    }
}
