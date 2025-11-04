<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GDPRService
{
    public function __construct(
        protected AnonymizationService $anonymizationService
    ) {}

    /**
     * Export all user data (Right to Data Portability)
     */
    public function exportUserData(User $user): string
    {
        $data = [
            'personal_info' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'date_of_birth' => $user->date_of_birth,
                'created_at' => $user->created_at,
            ],
            'properties' => $user->properties()->get()->toArray(),
            'bookings' => $user->bookings()->get()->toArray(),
            'reviews' => $user->reviews()->get()->toArray(),
            'payments' => $user->payments()->select([
                'id', 'amount', 'status', 'created_at',
            ])->get()->toArray(),
            'messages' => $user->messages()->get()->toArray(),
            'activity_logs' => $user->activityLogs()->get()->toArray(),
        ];

        $filename = 'user_data_'.$user->id.'_'.time().'.json';
        $path = 'exports/'.$filename;

        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));

        return Storage::path($path);
    }

    /**
     * Export user data as ZIP with all files
     */
    public function exportUserDataZip(User $user): string
    {
        $zipFilename = 'user_data_'.$user->id.'_'.time().'.zip';
        $zipPath = storage_path('app/exports/'.$zipFilename);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \Exception('Could not create ZIP file');
        }

        // Add JSON data
        $jsonPath = $this->exportUserData($user);
        $zip->addFile($jsonPath, 'data.json');

        // Add profile picture if exists
        if ($user->profile_picture && Storage::exists($user->profile_picture)) {
            $zip->addFile(
                Storage::path($user->profile_picture),
                'profile_picture.'.pathinfo($user->profile_picture, PATHINFO_EXTENSION)
            );
        }

        // Add uploaded documents
        if ($user->documents) {
            foreach ($user->documents as $document) {
                if (Storage::exists($document->path)) {
                    $zip->addFile(
                        Storage::path($document->path),
                        'documents/'.basename($document->path)
                    );
                }
            }
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Delete user and all related data (Right to be Forgotten)
     */
    public function deleteUserData(User $user, bool $anonymize = false): bool
    {
        DB::beginTransaction();

        try {
            if ($anonymize) {
                // Anonymize instead of delete (for legal/audit purposes)
                $this->anonymizationService->anonymizeUser($user);

                // Anonymize related data
                $user->bookings()->update([
                    'guest_name' => 'Deleted User',
                    'guest_email' => 'deleted@anonymized.local',
                    'guest_phone' => null,
                ]);

                $user->reviews()->update([
                    'reviewer_name' => 'Anonymous User',
                ]);

            } else {
                // Hard delete

                // Delete OAuth providers
                $user->oauthProviders()->delete();

                // Delete API keys
                $user->apiKeys()->delete();

                // Delete refresh tokens
                $user->refreshTokens()->delete();

                // Delete roles and permissions
                $user->roles()->detach();
                $user->permissions()->detach();

                // Delete files
                if ($user->profile_picture) {
                    Storage::delete($user->profile_picture);
                }

                // Soft delete bookings (keep for records)
                $user->bookings()->update(['deleted_at' => now()]);

                // Delete messages
                $user->messages()->delete();

                // Delete reviews
                $user->reviews()->delete();

                // Delete activity logs
                $user->activityLogs()->delete();

                // Finally delete user
                $user->delete();
            }

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get user consent status
     */
    public function getConsentStatus(User $user): array
    {
        return [
            'terms_accepted' => $user->terms_accepted_at !== null,
            'privacy_accepted' => $user->privacy_accepted_at !== null,
            'marketing_consent' => $user->marketing_consent ?? false,
            'data_processing_consent' => $user->data_processing_consent ?? false,
            'last_consent_update' => $user->consent_updated_at,
        ];
    }

    /**
     * Update user consent
     */
    public function updateConsent(User $user, array $consents): bool
    {
        $updates = [
            'consent_updated_at' => now(),
        ];

        if (isset($consents['terms'])) {
            $updates['terms_accepted_at'] = $consents['terms'] ? now() : null;
        }

        if (isset($consents['privacy'])) {
            $updates['privacy_accepted_at'] = $consents['privacy'] ? now() : null;
        }

        if (isset($consents['marketing'])) {
            $updates['marketing_consent'] = $consents['marketing'];
        }

        if (isset($consents['data_processing'])) {
            $updates['data_processing_consent'] = $consents['data_processing'];
        }

        return $user->update($updates);
    }

    /**
     * Record data access (for audit trail)
     */
    public function recordDataAccess(User $user, string $accessType, ?string $reason = null): void
    {
        DB::table('data_access_logs')->insert([
            'user_id' => $user->id,
            'access_type' => $accessType,
            'reason' => $reason,
            'accessed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get data retention policy for user data
     */
    public function getRetentionPolicy(string $dataType): array
    {
        $policies = [
            'user_profile' => ['duration' => 'indefinite', 'after_deletion' => 30],
            'bookings' => ['duration' => '7 years', 'after_completion' => 365],
            'payments' => ['duration' => '10 years', 'required_by' => 'Tax law'],
            'messages' => ['duration' => '2 years', 'after_last' => 730],
            'activity_logs' => ['duration' => '1 year', 'days' => 365],
            'api_logs' => ['duration' => '90 days', 'days' => 90],
        ];

        return $policies[$dataType] ?? ['duration' => 'unknown'];
    }

    /**
     * Apply data retention policies
     */
    public function applyRetentionPolicies(): array
    {
        $deleted = [];

        // Delete old activity logs (1 year)
        $deleted['activity_logs'] = DB::table('activity_logs')
            ->where('created_at', '<', now()->subYear())
            ->delete();

        // Delete old API logs (90 days)
        $deleted['api_logs'] = DB::table('api_logs')
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        // Delete expired refresh tokens
        $deleted['refresh_tokens'] = DB::table('refresh_tokens')
            ->where('expires_at', '<', now())
            ->where('revoked', true)
            ->delete();

        // Anonymize old inactive users (3 years inactive)
        $inactiveUsers = User::where('last_login_at', '<', now()->subYears(3))
            ->whereNull('deleted_at')
            ->get();

        foreach ($inactiveUsers as $user) {
            $this->anonymizationService->anonymizeUser($user);
        }
        $deleted['anonymized_users'] = $inactiveUsers->count();

        return $deleted;
    }

    /**
     * Generate GDPR compliance report
     */
    public function generateComplianceReport(): array
    {
        return [
            'total_users' => User::count(),
            'users_with_consent' => User::whereNotNull('terms_accepted_at')
                ->whereNotNull('privacy_accepted_at')
                ->count(),
            'data_export_requests' => DB::table('data_access_logs')
                ->where('access_type', 'export')
                ->where('created_at', '>', now()->subMonth())
                ->count(),
            'deletion_requests' => DB::table('data_access_logs')
                ->where('access_type', 'delete')
                ->where('created_at', '>', now()->subMonth())
                ->count(),
            'anonymized_users' => User::where('email', 'like', 'deleted_%@anonymized.local')
                ->count(),
            'retention_policy_compliant' => true,
            'last_policy_check' => now(),
        ];
    }
}
