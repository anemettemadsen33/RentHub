<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\DB;

class DataRetentionService
{
    /**
     * Data retention policies in days
     */
    protected array $retentionPolicies = [
        'activity_logs' => 365,        // 1 year
        'api_logs' => 90,              // 90 days
        'audit_logs' => 2555,          // 7 years (legal requirement)
        'refresh_tokens' => 30,        // 30 days after expiry
        'failed_login_attempts' => 30, // 30 days
        'email_verification_tokens' => 1, // 1 day
        'password_reset_tokens' => 1,  // 1 day
        'expired_sessions' => 7,       // 7 days
        'temporary_files' => 1,        // 1 day
        'deleted_users' => 30,         // 30 days (soft delete grace period)
    ];

    /**
     * Clean up expired data based on retention policies
     */
    public function cleanupExpiredData(): array
    {
        $results = [];

        // Activity logs
        $results['activity_logs'] = $this->cleanupTable(
            'activity_logs',
            $this->retentionPolicies['activity_logs']
        );

        // API logs
        $results['api_logs'] = $this->cleanupTable(
            'api_logs',
            $this->retentionPolicies['api_logs']
        );

        // Expired refresh tokens
        $results['refresh_tokens'] = DB::table('refresh_tokens')
            ->where('expires_at', '<', now()->subDays($this->retentionPolicies['refresh_tokens']))
            ->orWhere(function ($query) {
                $query->where('revoked', true)
                    ->where('updated_at', '<', now()->subDays(30));
            })
            ->delete();

        // Failed login attempts
        $results['failed_login_attempts'] = $this->cleanupTable(
            'failed_login_attempts',
            $this->retentionPolicies['failed_login_attempts']
        );

        // Expired email verification tokens
        $results['email_verifications'] = DB::table('email_verifications')
            ->where('created_at', '<', now()->subDays($this->retentionPolicies['email_verification_tokens']))
            ->delete();

        // Expired password reset tokens
        $results['password_resets'] = DB::table('password_resets')
            ->where('created_at', '<', now()->subDays($this->retentionPolicies['password_reset_tokens']))
            ->delete();

        // Old temporary files
        $results['temporary_files'] = $this->cleanupTemporaryFiles();

        // Permanently delete soft-deleted users after grace period
        $results['permanent_user_deletions'] = DB::table('users')
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<', now()->subDays($this->retentionPolicies['deleted_users']))
            ->delete();

        return $results;
    }

    /**
     * Clean up specific table
     */
    protected function cleanupTable(string $table, int $days): int
    {
        if (! $this->tableExists($table)) {
            return 0;
        }

        return DB::table($table)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Check if table exists
     */
    protected function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    /**
     * Clean up temporary files
     */
    protected function cleanupTemporaryFiles(): int
    {
        $tempPath = storage_path('app/temp');
        if (! file_exists($tempPath)) {
            return 0;
        }

        $deleted = 0;
        $cutoff = now()->subDays($this->retentionPolicies['temporary_files'])->timestamp;

        $files = scandir($tempPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $tempPath.'/'.$file;
            if (is_file($filePath) && filemtime($filePath) < $cutoff) {
                unlink($filePath);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get retention policy for specific data type
     */
    public function getRetentionPolicy(string $dataType): ?int
    {
        return $this->retentionPolicies[$dataType] ?? null;
    }

    /**
     * Set custom retention policy
     */
    public function setRetentionPolicy(string $dataType, int $days): void
    {
        $this->retentionPolicies[$dataType] = $days;
    }

    /**
     * Archive old data before deletion
     */
    public function archiveData(string $table, int $days): string
    {
        $archiveTable = $table.'_archive';

        // Create archive table if it doesn't exist
        if (! $this->tableExists($archiveTable)) {
            DB::statement("CREATE TABLE {$archiveTable} LIKE {$table}");
        }

        // Move old data to archive
        $moved = DB::statement("
            INSERT INTO {$archiveTable}
            SELECT * FROM {$table}
            WHERE created_at < ?
        ", [now()->subDays($days)]);

        // Delete from original table
        DB::table($table)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        return $archiveTable;
    }

    /**
     * Get data retention statistics
     */
    public function getRetentionStats(): array
    {
        $stats = [];

        foreach ($this->retentionPolicies as $type => $days) {
            if (! $this->tableExists($type)) {
                continue;
            }

            $total = DB::table($type)->count();
            $expired = DB::table($type)
                ->where('created_at', '<', now()->subDays($days))
                ->count();

            $stats[$type] = [
                'total_records' => $total,
                'expired_records' => $expired,
                'retention_days' => $days,
                'expiry_date' => now()->subDays($days)->toDateString(),
            ];
        }

        return $stats;
    }

    /**
     * Estimate storage space that can be freed
     */
    public function estimateStorageRecovery(): array
    {
        $estimates = [];

        foreach ($this->retentionPolicies as $type => $days) {
            if (! $this->tableExists($type)) {
                continue;
            }

            $expiredCount = DB::table($type)
                ->where('created_at', '<', now()->subDays($days))
                ->count();

            // Rough estimate: 1KB per record
            $estimates[$type] = [
                'records' => $expiredCount,
                'estimated_size_kb' => $expiredCount * 1,
                'estimated_size_mb' => round($expiredCount * 1 / 1024, 2),
            ];
        }

        return $estimates;
    }
}
