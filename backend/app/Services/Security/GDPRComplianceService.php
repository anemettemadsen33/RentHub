<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GDPRComplianceService
{
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Export all user data (GDPR Right to Access)
     */
    public function exportUserData(User $user): array
    {
        return [
            'personal_information' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'properties' => $user->properties()->get()->toArray(),
            'bookings' => $user->bookings()->get()->toArray(),
            'reviews' => $user->reviews()->get()->toArray(),
            'messages' => $user->messages()->get()->toArray(),
            'wishlist' => $user->wishlist()->get()->toArray(),
            'payment_methods' => $user->paymentMethods()->get()->map(function ($pm) {
                return [
                    'type' => $pm->type,
                    'last_four' => $pm->last_four,
                    'created_at' => $pm->created_at,
                ];
            })->toArray(),
        ];
    }

    /**
     * Anonymize user data (GDPR Right to be Forgotten)
     */
    public function anonymizeUser(User $user): bool
    {
        DB::beginTransaction();
        
        try {
            $anonymousId = $this->encryptionService->anonymize($user->email);

            // Anonymize user data
            $user->update([
                'name' => 'Anonymous User',
                'email' => $anonymousId . '@anonymized.local',
                'phone' => null,
                'avatar' => null,
                'bio' => null,
                'deleted_at' => now(),
            ]);

            // Anonymize reviews
            Review::where('user_id', $user->id)->update([
                'user_name' => 'Anonymous',
            ]);

            // Keep bookings but anonymize guest info
            Booking::where('user_id', $user->id)->update([
                'guest_name' => 'Anonymous Guest',
                'guest_email' => $anonymousId . '@anonymized.local',
                'guest_phone' => null,
            ]);

            // Log the anonymization
            Log::info('User anonymized', [
                'user_id' => $user->id,
                'anonymous_id' => $anonymousId,
                'timestamp' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User anonymization failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check data retention policy
     */
    public function enforceDataRetention(): void
    {
        $retentionDays = config('gdpr.data_retention_days', 365);
        $cutoffDate = now()->subDays($retentionDays);

        // Soft delete old data
        User::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->each(function ($user) {
                $this->permanentlyDeleteUser($user);
            });
    }

    /**
     * Permanently delete user data
     */
    protected function permanentlyDeleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Delete related data
            $user->reviews()->delete();
            $user->messages()->delete();
            $user->wishlist()->detach();
            $user->paymentMethods()->delete();
            
            // Force delete user
            $user->forceDelete();

            Log::info('User permanently deleted', [
                'user_id' => $user->id,
                'timestamp' => now(),
            ]);
        });
    }

    /**
     * Generate GDPR compliance report
     */
    public function generateComplianceReport(): array
    {
        return [
            'total_users' => User::count(),
            'anonymized_users' => User::onlyTrashed()->count(),
            'data_export_requests' => $this->getDataExportRequestCount(),
            'deletion_requests' => $this->getDeletionRequestCount(),
            'data_breach_incidents' => $this->getDataBreachIncidentCount(),
            'compliance_score' => $this->calculateComplianceScore(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function getDataExportRequestCount(): int
    {
        return DB::table('audit_logs')
            ->where('action', 'data_export')
            ->where('created_at', '>', now()->subMonth())
            ->count();
    }

    protected function getDeletionRequestCount(): int
    {
        return DB::table('audit_logs')
            ->where('action', 'user_anonymization')
            ->where('created_at', '>', now()->subMonth())
            ->count();
    }

    protected function getDataBreachIncidentCount(): int
    {
        return DB::table('security_incidents')
            ->where('type', 'data_breach')
            ->where('created_at', '>', now()->subMonth())
            ->count();
    }

    protected function calculateComplianceScore(): int
    {
        $score = 100;

        // Deduct points for compliance issues
        if ($this->getDataBreachIncidentCount() > 0) {
            $score -= 20;
        }

        if ($this->hasUnrespondedRequests()) {
            $score -= 10;
        }

        return max(0, $score);
    }

    protected function hasUnrespondedRequests(): bool
    {
        return DB::table('data_requests')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(30))
            ->exists();
    }
}
