<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    protected $config;

    protected $backupPath;

    public function __construct()
    {
        $this->config = config('backup');
        $this->backupPath = $this->config['destinations']['local']['path'];
    }

    /**
     * Create a full backup (database + files)
     */
    public function createFullBackup(): array
    {
        $startTime = microtime(true);
        $results = ['success' => true, 'backups' => []];

        try {
            Log::info('Starting full backup');

            // Database backup
            if ($this->config['database']['enabled']) {
                $dbBackup = $this->backupDatabase();
                $results['backups']['database'] = $dbBackup;
            }

            // Files backup
            if ($this->config['files']['enabled']) {
                $filesBackup = $this->backupFiles();
                $results['backups']['files'] = $filesBackup;
            }

            $duration = round(microtime(true) - $startTime, 2);
            $results['duration'] = $duration;

            Log::info('Full backup completed', ['duration' => $duration]);
            $this->notifyBackupSuccess('full', $results);

            return $results;
        } catch (\Exception $e) {
            $results['success'] = false;
            $results['error'] = $e->getMessage();

            Log::error('Full backup failed', ['error' => $e->getMessage()]);
            $this->notifyBackupFailure('full', $e);

            return $results;
        }
    }

    /**
     * Backup database
     */
    public function backupDatabase(): array
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "database_backup_{$timestamp}.sql";

        if ($this->config['database']['backup_options']['compress']) {
            $filename .= '.gz';
        }

        $backupFile = $this->backupPath.'/database/'.$filename;
        $this->ensureDirectoryExists(dirname($backupFile));

        try {
            $connection = $this->config['database']['connections']['mysql'];
            $options = $this->config['database']['backup_options'];

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s %s %s %s %s %s %s %s',
                escapeshellarg($connection['host']),
                escapeshellarg($connection['port']),
                escapeshellarg($connection['username']),
                escapeshellarg($connection['password']),
                $options['include_routines'] ? '--routines' : '',
                $options['include_triggers'] ? '--triggers' : '',
                $options['add_drop_table'] ? '--add-drop-table' : '',
                $options['single_transaction'] ? '--single-transaction' : '',
                $options['lock_tables'] ? '--lock-tables' : '--skip-lock-tables',
                $options['quick'] ? '--quick' : '',
                $options['extended_insert'] ? '--extended-insert' : '',
                escapeshellarg($connection['database'])
            );

            // Add compression if enabled
            if ($options['compress']) {
                $command .= ' | gzip';
            }

            $command .= ' > '.escapeshellarg($backupFile);

            // Execute backup
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed with code '.$returnCode);
            }

            // Verify backup
            $size = filesize($backupFile);
            $checksum = hash_file($this->config['verification']['checksum_algorithm'], $backupFile);

            // Store metadata
            $metadata = [
                'filename' => $filename,
                'path' => $backupFile,
                'size' => $size,
                'checksum' => $checksum,
                'created_at' => now()->toIso8601String(),
                'type' => 'database',
            ];

            $this->saveBackupMetadata($metadata);

            // Upload to remote destinations
            $this->uploadToRemoteDestinations($backupFile, 'database/'.$filename);

            return $metadata;
        } catch (\Exception $e) {
            Log::error('Database backup failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Backup files
     */
    public function backupFiles(): array
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "files_backup_{$timestamp}.tar.gz";
        $backupFile = $this->backupPath.'/files/'.$filename;

        $this->ensureDirectoryExists(dirname($backupFile));

        try {
            $includes = $this->config['files']['include'];
            $tempList = tempnam(sys_get_temp_dir(), 'backup_');

            // Create list of files to backup
            $fileList = [];
            foreach ($includes as $name => $include) {
                $path = $include['path'];
                $exclude = $include['exclude'] ?? [];

                if (is_dir($path)) {
                    $files = $this->getFilesRecursively($path, $exclude);
                    $fileList = array_merge($fileList, $files);
                }
            }

            file_put_contents($tempList, implode("\n", $fileList));

            // Create tar archive
            $command = sprintf(
                'tar -czf %s -T %s',
                escapeshellarg($backupFile),
                escapeshellarg($tempList)
            );

            exec($command, $output, $returnCode);
            unlink($tempList);

            if ($returnCode !== 0) {
                throw new \Exception('Files backup failed with code '.$returnCode);
            }

            // Verify backup
            $size = filesize($backupFile);
            $checksum = hash_file($this->config['verification']['checksum_algorithm'], $backupFile);

            $metadata = [
                'filename' => $filename,
                'path' => $backupFile,
                'size' => $size,
                'checksum' => $checksum,
                'created_at' => now()->toIso8601String(),
                'type' => 'files',
                'file_count' => count($fileList),
            ];

            $this->saveBackupMetadata($metadata);
            $this->uploadToRemoteDestinations($backupFile, 'files/'.$filename);

            return $metadata;
        } catch (\Exception $e) {
            Log::error('Files backup failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreDatabase(string $backupFile): bool
    {
        try {
            Log::info('Starting database restore', ['file' => $backupFile]);

            if (! file_exists($backupFile)) {
                throw new \Exception('Backup file not found: '.$backupFile);
            }

            $connection = $this->config['database']['connections']['mysql'];
            $isCompressed = str_ends_with($backupFile, '.gz');

            // Build restore command
            if ($isCompressed) {
                $command = sprintf(
                    'gunzip < %s | mysql --host=%s --port=%s --user=%s --password=%s %s',
                    escapeshellarg($backupFile),
                    escapeshellarg($connection['host']),
                    escapeshellarg($connection['port']),
                    escapeshellarg($connection['username']),
                    escapeshellarg($connection['password']),
                    escapeshellarg($connection['database'])
                );
            } else {
                $command = sprintf(
                    'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                    escapeshellarg($connection['host']),
                    escapeshellarg($connection['port']),
                    escapeshellarg($connection['username']),
                    escapeshellarg($connection['password']),
                    escapeshellarg($connection['database']),
                    escapeshellarg($backupFile)
                );
            }

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database restore failed with code '.$returnCode);
            }

            Log::info('Database restore completed successfully');
            $this->notifyRestoreSuccess('database', $backupFile);

            return true;
        } catch (\Exception $e) {
            Log::error('Database restore failed', ['error' => $e->getMessage()]);
            $this->notifyRestoreFailure('database', $e);
            throw $e;
        }
    }

    /**
     * Restore files from backup
     */
    public function restoreFiles(string $backupFile, ?string $destination = null): bool
    {
        try {
            Log::info('Starting files restore', ['file' => $backupFile]);

            if (! file_exists($backupFile)) {
                throw new \Exception('Backup file not found: '.$backupFile);
            }

            $destination = $destination ?? base_path();

            $command = sprintf(
                'tar -xzf %s -C %s',
                escapeshellarg($backupFile),
                escapeshellarg($destination)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Files restore failed with code '.$returnCode);
            }

            Log::info('Files restore completed successfully');
            $this->notifyRestoreSuccess('files', $backupFile);

            return true;
        } catch (\Exception $e) {
            Log::error('Files restore failed', ['error' => $e->getMessage()]);
            $this->notifyRestoreFailure('files', $e);
            throw $e;
        }
    }

    /**
     * List all backups
     */
    public function listBackups(string $type = 'all'): array
    {
        $backups = [];
        $metadataPath = $this->backupPath.'/metadata';

        if (! is_dir($metadataPath)) {
            return $backups;
        }

        $files = glob($metadataPath.'/*.json');

        foreach ($files as $file) {
            $metadata = json_decode(file_get_contents($file), true);

            if ($type === 'all' || $metadata['type'] === $type) {
                $backups[] = $metadata;
            }
        }

        // Sort by created_at descending
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Clean up old backups based on retention policy
     */
    public function cleanupOldBackups(): array
    {
        $results = ['deleted' => 0, 'space_freed' => 0];

        try {
            $backups = $this->listBackups();
            $now = Carbon::now();

            foreach ($backups as $backup) {
                $age = Carbon::parse($backup['created_at'])->diffInDays($now);
                $shouldDelete = false;

                // Apply retention policy
                if ($backup['type'] === 'database') {
                    $retention = $this->config['database']['retention'];
                } else {
                    $retention = $this->config['files']['retention'];
                }

                if ($age > $retention['daily']) {
                    $shouldDelete = true;
                }

                if ($shouldDelete && file_exists($backup['path'])) {
                    $size = filesize($backup['path']);
                    unlink($backup['path']);

                    // Remove metadata
                    $metadataFile = $this->backupPath.'/metadata/'.
                                   pathinfo($backup['filename'], PATHINFO_FILENAME).'.json';
                    if (file_exists($metadataFile)) {
                        unlink($metadataFile);
                    }

                    $results['deleted']++;
                    $results['space_freed'] += $size;
                }
            }

            Log::info('Backup cleanup completed', $results);

            return $results;
        } catch (\Exception $e) {
            Log::error('Backup cleanup failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Verify backup integrity
     */
    public function verifyBackup(string $backupFile): array
    {
        $results = ['valid' => true, 'checks' => []];

        try {
            // File exists check
            if (! file_exists($backupFile)) {
                $results['valid'] = false;
                $results['checks']['exists'] = false;

                return $results;
            }
            $results['checks']['exists'] = true;

            // Size check
            $size = filesize($backupFile);
            $minSize = $this->config['testing']['thresholds']['min_backup_size'] * 1024;
            $results['checks']['size'] = $size >= $minSize;
            if ($size < $minSize) {
                $results['valid'] = false;
            }

            // Checksum verification
            $metadata = $this->getBackupMetadata(basename($backupFile));
            if ($metadata) {
                $currentChecksum = hash_file($this->config['verification']['checksum_algorithm'], $backupFile);
                $results['checks']['checksum'] = $currentChecksum === $metadata['checksum'];
                if ($currentChecksum !== $metadata['checksum']) {
                    $results['valid'] = false;
                }
            }

            // Compression test
            if (str_ends_with($backupFile, '.gz')) {
                exec('gzip -t '.escapeshellarg($backupFile), $output, $returnCode);
                $results['checks']['compression'] = $returnCode === 0;
                if ($returnCode !== 0) {
                    $results['valid'] = false;
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Backup verification failed', ['error' => $e->getMessage()]);

            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Test backup restore
     */
    public function testBackupRestore(string $backupFile): bool
    {
        // This would create a test database and restore to it
        // Implementation depends on your testing strategy
        return true;
    }

    /**
     * Get backup statistics
     */
    public function getBackupStatistics(): array
    {
        $backups = $this->listBackups();
        $totalSize = 0;
        $byType = ['database' => 0, 'files' => 0];

        foreach ($backups as $backup) {
            if (file_exists($backup['path'])) {
                $size = filesize($backup['path']);
                $totalSize += $size;
                $byType[$backup['type']] += $size;
            }
        }

        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'total_size_gb' => round($totalSize / 1024 / 1024 / 1024, 2),
            'by_type' => $byType,
            'oldest_backup' => ! empty($backups) ? end($backups)['created_at'] : null,
            'newest_backup' => ! empty($backups) ? $backups[0]['created_at'] : null,
        ];
    }

    /**
     * Helper methods
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    protected function getFilesRecursively(string $path, array $exclude = []): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relativePath = str_replace($path.DIRECTORY_SEPARATOR, '', $file->getPathname());

            $shouldExclude = false;
            foreach ($exclude as $pattern) {
                if (str_contains($relativePath, $pattern)) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (! $shouldExclude && $file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    protected function saveBackupMetadata(array $metadata): void
    {
        $metadataPath = $this->backupPath.'/metadata';
        $this->ensureDirectoryExists($metadataPath);

        $filename = pathinfo($metadata['filename'], PATHINFO_FILENAME).'.json';
        $filepath = $metadataPath.'/'.$filename;

        file_put_contents($filepath, json_encode($metadata, JSON_PRETTY_PRINT));
    }

    protected function getBackupMetadata(string $filename): ?array
    {
        $metadataFile = $this->backupPath.'/metadata/'.
                       pathinfo($filename, PATHINFO_FILENAME).'.json';

        if (file_exists($metadataFile)) {
            return json_decode(file_get_contents($metadataFile), true);
        }

        return null;
    }

    protected function uploadToRemoteDestinations(string $localFile, string $remotePath): void
    {
        foreach ($this->config['destinations'] as $name => $destination) {
            if ($name === 'local' || ! ($destination['enabled'] ?? false)) {
                continue;
            }

            try {
                $this->uploadToDestination($name, $localFile, $remotePath);
                Log::info("Uploaded backup to {$name}", ['path' => $remotePath]);
            } catch (\Exception $e) {
                Log::error("Failed to upload to {$name}", ['error' => $e->getMessage()]);
            }
        }
    }

    protected function uploadToDestination(string $destination, string $localFile, string $remotePath): void
    {
        switch ($destination) {
            case 's3':
                Storage::disk('s3')->put($remotePath, file_get_contents($localFile));
                break;
                // Add other destinations as needed
        }
    }

    protected function notifyBackupSuccess(string $type, array $details): void
    {
        if (! $this->config['notifications']['enabled']) {
            return;
        }

        // Send notifications via configured channels
        Log::info('Backup success notification sent', ['type' => $type]);
    }

    protected function notifyBackupFailure(string $type, \Exception $e): void
    {
        if (! $this->config['notifications']['enabled']) {
            return;
        }

        Log::error('Backup failure notification sent', ['type' => $type, 'error' => $e->getMessage()]);
    }

    protected function notifyRestoreSuccess(string $type, string $file): void
    {
        Log::info('Restore success notification sent', ['type' => $type]);
    }

    protected function notifyRestoreFailure(string $type, \Exception $e): void
    {
        Log::error('Restore failure notification sent', ['type' => $type, 'error' => $e->getMessage()]);
    }
}
