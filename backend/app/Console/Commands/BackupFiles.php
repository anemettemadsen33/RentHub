<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupFiles extends Command
{
    protected $signature = 'backup:files';

    protected $description = 'Backup uploaded files';

    public function handle(): int
    {
        $this->info('Starting files backup...');

        $filename = 'files-backup-'.now()->format('Y-m-d-His').'.zip';
        $path = storage_path('app/backups/'.$filename);

        // Create backups directory if it doesn't exist
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Could not create zip file!');

            return self::FAILURE;
        }

        // Backup storage directories
        $directories = [
            storage_path('app/public'),
            storage_path('app/uploads'),
        ];

        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $this->addDirectoryToZip($zip, $directory, basename($directory));
            }
        }

        $zip->close();

        $this->info('Files backed up to: '.$path);
        $this->info('Backup size: '.$this->formatBytes(filesize($path)));

        return self::SUCCESS;
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $directory, string $localPath = ''): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $localPath.'/'.substr($filePath, strlen($directory) + 1);
                $zip->addFile($filePath, $relativePath);
                $this->output->write('.');
            }
        }
        $this->newLine();
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
