<?php

namespace App\Services;

use App\Models\ConsentRecord;
use App\Models\DataDeletionRequest;
use App\Models\DataExportRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GDPRService
{
    /**
     * Export user data (Right to Data Portability)
     */
    public function exportUserData(User $user, string $format = 'json'): string
    {
        $data = [
            'user' => $user->toArray(),
            'properties' => $user->properties()->get()->toArray(),
            'bookings' => $user->bookings()->get()->toArray(),
            'reviews' => $user->reviews()->get()->toArray(),
            'messages' => $user->messages()->get()->toArray(),
            'payments' => $user->payments()->get()->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        // Create export request record
        DataExportRequest::create([
            'user_id' => $user->id,
            'format' => $format,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return match ($format) {
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            'csv' => $this->convertToCSV($data),
            'pdf' => $this->convertToPDF($data),
            default => json_encode($data),
        };
    }

    /**
     * Delete user data (Right to be Forgotten)
     */
    public function deleteUserData(User $user, bool $immediate = false): DataDeletionRequest
    {
        $request = DataDeletionRequest::create([
            'user_id' => $user->id,
            'requested_at' => now(),
            'scheduled_for' => $immediate ? now() : now()->addDays(config('security.gdpr.deletion_grace_period_days', 30)),
            'status' => 'pending',
        ]);

        if ($immediate) {
            $this->executeDataDeletion($request);
        }

        return $request;
    }

    /**
     * Execute data deletion
     */
    public function executeDataDeletion(DataDeletionRequest $request): void
    {
        $user = $request->user;

        DB::transaction(function () use ($user, $request) {
            // Anonymize instead of delete (for audit trails)
            $user->update([
                'email' => 'deleted_'.$user->id.'@deleted.local',
                'name' => 'Deleted User',
                'phone' => null,
                'deleted_at' => now(),
            ]);

            // Delete or anonymize related data
            $user->properties()->delete();
            $user->bookings()->update(['user_id' => null]);
            $user->reviews()->update(['user_id' => null]);
            $user->messages()->delete();

            // Delete files
            $this->deleteUserFiles($user);

            $request->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        \Log::channel('audit')->info('User data deleted', [
            'user_id' => $user->id,
            'request_id' => $request->id,
        ]);
    }

    /**
     * Delete user files
     */
    private function deleteUserFiles(User $user): void
    {
        $directories = [
            "users/{$user->id}/profile",
            "users/{$user->id}/documents",
            "users/{$user->id}/properties",
        ];

        foreach ($directories as $directory) {
            if (Storage::exists($directory)) {
                Storage::deleteDirectory($directory);
            }
        }
    }

    /**
     * Record consent
     */
    public function recordConsent(
        User $user,
        string $type,
        bool $granted,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ConsentRecord {
        return ConsentRecord::create([
            'user_id' => $user->id,
            'consent_type' => $type,
            'granted' => $granted,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'granted_at' => $granted ? now() : null,
            'revoked_at' => ! $granted ? now() : null,
        ]);
    }

    /**
     * Check consent
     */
    public function hasConsent(User $user, string $type): bool
    {
        return ConsentRecord::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->where('granted', true)
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Revoke consent
     */
    public function revokeConsent(User $user, string $type): void
    {
        ConsentRecord::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->update([
                'granted' => false,
                'revoked_at' => now(),
            ]);
    }

    /**
     * Get user consent history
     */
    public function getConsentHistory(User $user): array
    {
        return ConsentRecord::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Rectify user data (Right to Rectification)
     */
    public function rectifyData(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $user->update($data);

            \Log::channel('audit')->info('User data rectified', [
                'user_id' => $user->id,
                'fields' => array_keys($data),
            ]);
        });
    }

    /**
     * Restrict processing (Right to Restriction)
     */
    public function restrictProcessing(User $user, bool $restricted = true): void
    {
        $user->update(['processing_restricted' => $restricted]);

        \Log::channel('audit')->info('Processing restriction changed', [
            'user_id' => $user->id,
            'restricted' => $restricted,
        ]);
    }

    /**
     * Object to processing (Right to Object)
     */
    public function objectToProcessing(User $user, string $processingType): void
    {
        $user->processingObjections()->create([
            'processing_type' => $processingType,
            'objected_at' => now(),
        ]);
    }

    /**
     * Clean old data based on retention policy
     */
    public function cleanOldData(): array
    {
        $retentionDays = config('security.gdpr.data_retention_days', 2555);
        $cutoffDate = now()->subDays($retentionDays);

        $results = [];

        // Delete old audit logs
        $results['audit_logs'] = DB::table('audit_logs')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        // Delete old sessions
        $results['sessions'] = DB::table('sessions')
            ->where('last_activity', '<', $cutoffDate->timestamp)
            ->delete();

        return $results;
    }

    /**
     * Convert data to CSV
     */
    private function convertToCSV(array $data): string
    {
        // Implementation for CSV conversion
        return 'CSV export not implemented';
    }

    /**
     * Convert data to PDF
     */
    private function convertToPDF(array $data): string
    {
        // Implementation for PDF conversion
        return 'PDF export not implemented';
    }
}
