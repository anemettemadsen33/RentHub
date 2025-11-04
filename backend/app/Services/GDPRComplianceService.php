<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GDPRComplianceService
{
    public function __construct(
        private DataEncryptionService $encryptionService
    ) {}

    /**
     * Export all user data (GDPR Right to Data Portability)
     */
    public function exportUserData(User $user): array
    {
        return [
            'personal_information' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'profile' => $user->profile,
            'properties' => $user->properties()->get()->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'description' => $property->description,
                    'price' => $property->price,
                    'created_at' => $property->created_at,
                ];
            }),
            'bookings' => $user->bookings()->get()->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'property_id' => $booking->property_id,
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                ];
            }),
            'reviews' => $user->reviews()->get()->map(function ($review) {
                return [
                    'id' => $review->id,
                    'property_id' => $review->property_id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                ];
            }),
            'messages' => $user->messages()->get()->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                ];
            }),
        ];
    }

    /**
     * Delete user data (GDPR Right to be Forgotten)
     */
    public function deleteUserData(User $user, bool $preserveBookingHistory = true): bool
    {
        try {
            DB::beginTransaction();

            // Anonymize instead of deleting if bookings need to be preserved
            if ($preserveBookingHistory) {
                $this->anonymizeUserData($user);
            } else {
                // Delete all related data
                $user->messages()->delete();
                $user->reviews()->delete();
                $user->notifications()->delete();
                $user->wishlists()->delete();
                $user->bookings()->delete();
                
                // Delete properties and their related data
                foreach ($user->properties as $property) {
                    $property->images()->delete();
                    $property->amenities()->detach();
                    $property->bookings()->delete();
                    $property->reviews()->delete();
                    $property->delete();
                }
                
                // Delete user
                $user->delete();
            }

            DB::commit();

            Log::info('User data deleted/anonymized', [
                'user_id' => $user->id,
                'anonymized' => $preserveBookingHistory,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete user data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Anonymize user data while preserving relationships
     */
    private function anonymizeUserData(User $user): void
    {
        $anonymousData = [
            'name' => 'Anonymous User ' . substr(md5($user->id), 0, 8),
            'email' => 'deleted_' . time() . '_' . $user->id . '@anonymized.local',
            'phone' => null,
            'password' => bcrypt(bin2hex(random_bytes(32))),
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'deleted_at' => now(),
        ];

        $user->update($anonymousData);
        
        // Anonymize profile
        if ($user->profile) {
            $user->profile->update([
                'bio' => '[User requested data deletion]',
                'avatar' => null,
                'government_id' => null,
                'address' => '[REDACTED]',
            ]);
        }

        // Delete uploaded files
        if ($user->profile && $user->profile->avatar) {
            Storage::delete($user->profile->avatar);
        }
    }

    /**
     * Check if user data should be deleted (data retention policy)
     */
    public function shouldDeleteInactiveUser(User $user, int $inactiveDays = 730): bool
    {
        if (!$user->last_login_at) {
            return false;
        }

        return $user->last_login_at->diffInDays(now()) > $inactiveDays;
    }

    /**
     * Get user consent status
     */
    public function getUserConsent(User $user): array
    {
        return [
            'marketing_emails' => $user->consent_marketing_emails ?? false,
            'data_processing' => $user->consent_data_processing ?? true,
            'third_party_sharing' => $user->consent_third_party_sharing ?? false,
            'analytics' => $user->consent_analytics ?? false,
        ];
    }

    /**
     * Update user consent
     */
    public function updateUserConsent(User $user, array $consents): bool
    {
        try {
            $user->update([
                'consent_marketing_emails' => $consents['marketing_emails'] ?? false,
                'consent_data_processing' => $consents['data_processing'] ?? true,
                'consent_third_party_sharing' => $consents['third_party_sharing'] ?? false,
                'consent_analytics' => $consents['analytics'] ?? false,
                'consent_updated_at' => now(),
            ]);

            Log::info('User consent updated', [
                'user_id' => $user->id,
                'consents' => $consents,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update user consent', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
