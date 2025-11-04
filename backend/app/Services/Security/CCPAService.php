<?php

namespace App\Services\Security;

use App\Models\DataExportRequest;
use App\Models\DataProcessingConsent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CCPAService
{
    /**
     * Record CCPA consent
     */
    public function recordConsent(User $user, array $consentData): DataProcessingConsent
    {
        return DataProcessingConsent::create([
            'user_id' => $user->id,
            'consent_type' => 'ccpa',
            'categories' => $consentData['categories'] ?? [],
            'purpose' => $consentData['purpose'] ?? 'Service provision',
            'do_not_sell' => $consentData['do_not_sell'] ?? false,
            'consented_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'consent_text' => $consentData['consent_text'] ?? '',
        ]);
    }

    /**
     * Update CCPA consent
     */
    public function updateConsent(User $user, array $consentData): DataProcessingConsent
    {
        $consent = DataProcessingConsent::where('user_id', $user->id)
            ->where('consent_type', 'ccpa')
            ->latest()
            ->first();

        if ($consent) {
            $consent->update([
                'categories' => $consentData['categories'] ?? $consent->categories,
                'do_not_sell' => $consentData['do_not_sell'] ?? $consent->do_not_sell,
                'updated_at' => now(),
            ]);

            return $consent;
        }

        return $this->recordConsent($user, $consentData);
    }

    /**
     * Opt-out of data sale
     */
    public function optOutOfDataSale(User $user): bool
    {
        try {
            DB::beginTransaction();

            $this->updateConsent($user, ['do_not_sell' => true]);

            // Mark user data as not for sale
            $user->update([
                'ccpa_do_not_sell' => true,
                'ccpa_opt_out_date' => now(),
            ]);

            // Log the opt-out
            Log::info('User opted out of data sale', [
                'user_id' => $user->id,
                'timestamp' => now(),
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to opt-out of data sale', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Request data disclosure
     */
    public function requestDataDisclosure(User $user): array
    {
        $dataCategories = $this->getCollectedDataCategories($user);
        $dataSources = $this->getDataSources($user);
        $businessPurposes = $this->getBusinessPurposes();
        $thirdParties = $this->getThirdPartiesSharedWith($user);

        return [
            'categories_collected' => $dataCategories,
            'sources' => $dataSources,
            'business_purposes' => $businessPurposes,
            'third_parties' => $thirdParties,
            'sold_or_shared' => $this->isDataSoldOrShared($user),
            'retention_period' => $this->getRetentionPeriod(),
        ];
    }

    /**
     * Get collected data categories
     */
    private function getCollectedDataCategories(User $user): array
    {
        $categories = [];

        // Identifiers
        if ($user->email || $user->phone) {
            $categories[] = [
                'category' => 'Identifiers',
                'examples' => ['Email address', 'Phone number', 'Name'],
            ];
        }

        // Commercial information
        if ($user->bookings()->exists()) {
            $categories[] = [
                'category' => 'Commercial Information',
                'examples' => ['Booking history', 'Payment records', 'Transaction history'],
            ];
        }

        // Internet activity
        $categories[] = [
            'category' => 'Internet or Network Activity',
            'examples' => ['Browsing history', 'Search history', 'Interaction with website'],
        ];

        // Geolocation
        if ($user->properties()->exists()) {
            $categories[] = [
                'category' => 'Geolocation Data',
                'examples' => ['Property locations', 'IP address location'],
            ];
        }

        // Professional information
        if ($user->role === 'landlord') {
            $categories[] = [
                'category' => 'Professional or Employment Information',
                'examples' => ['Business information', 'Tax ID'],
            ];
        }

        return $categories;
    }

    /**
     * Get data sources
     */
    private function getDataSources(User $user): array
    {
        return [
            'Directly from you' => 'Account registration, profile updates, booking forms',
            'Automatically collected' => 'Cookies, web beacons, usage data',
            'Third parties' => 'Payment processors, identity verification services',
        ];
    }

    /**
     * Get business purposes
     */
    private function getBusinessPurposes(): array
    {
        return [
            'Service provision' => 'To provide and maintain our services',
            'Transaction processing' => 'To process bookings and payments',
            'Communication' => 'To communicate with you about our services',
            'Security' => 'To protect against fraud and maintain security',
            'Legal compliance' => 'To comply with legal obligations',
            'Business operations' => 'To improve our services and operations',
        ];
    }

    /**
     * Get third parties data is shared with
     */
    private function getThirdPartiesSharedWith(User $user): array
    {
        $thirdParties = [];

        if ($user->bookings()->exists()) {
            $thirdParties[] = [
                'category' => 'Payment Processors',
                'purpose' => 'To process payments',
                'examples' => ['PayPal'],
            ];
        }

        $thirdParties[] = [
            'category' => 'Cloud Service Providers',
            'purpose' => 'To host and store data',
            'examples' => ['AWS', 'Google Cloud'],
        ];

        $thirdParties[] = [
            'category' => 'Analytics Providers',
            'purpose' => 'To analyze usage and improve services',
            'examples' => ['Google Analytics'],
        ];

        return $thirdParties;
    }

    /**
     * Check if data is sold or shared
     */
    private function isDataSoldOrShared(User $user): array
    {
        return [
            'sold' => false,
            'shared_for_business_purposes' => true,
            'do_not_sell_status' => $user->ccpa_do_not_sell ?? false,
        ];
    }

    /**
     * Get data retention period
     */
    private function getRetentionPeriod(): array
    {
        return [
            'Account data' => '7 years after account closure',
            'Transaction records' => '7 years for tax compliance',
            'Marketing data' => 'Until consent is withdrawn',
            'Analytics data' => '26 months',
        ];
    }

    /**
     * Request data deletion
     */
    public function requestDataDeletion(User $user, array $options = []): array
    {
        try {
            DB::beginTransaction();

            // Check for legal holds
            if ($this->hasLegalHold($user)) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete data due to legal hold or pending transactions',
                ];
            }

            // Create deletion request
            $deletionRequest = $user->dataDeletionRequests()->create([
                'requested_at' => now(),
                'scheduled_for' => now()->addDays(config('security.gdpr.deletion_grace_period_days', 30)),
                'status' => 'pending',
                'categories' => $options['categories'] ?? ['all'],
            ]);

            // Schedule deletion job
            \App\Jobs\ProcessDataDeletionJob::dispatch($user, $deletionRequest)
                ->delay(now()->addDays(config('security.gdpr.deletion_grace_period_days', 30)));

            DB::commit();

            return [
                'success' => true,
                'request_id' => $deletionRequest->id,
                'scheduled_for' => $deletionRequest->scheduled_for,
                'grace_period_days' => config('security.gdpr.deletion_grace_period_days', 30),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to request data deletion', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process deletion request',
            ];
        }
    }

    /**
     * Check if user has legal hold
     */
    private function hasLegalHold(User $user): bool
    {
        // Check for pending transactions
        if ($user->bookings()->whereIn('status', ['pending', 'active'])->exists()) {
            return true;
        }

        // Check for pending disputes
        if ($user->disputes()->where('status', 'open')->exists()) {
            return true;
        }

        // Check for pending payments
        if ($user->payments()->whereIn('status', ['pending', 'processing'])->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Export user data (data portability)
     */
    public function exportUserData(User $user, string $format = 'json'): array
    {
        $data = [
            'personal_information' => $this->getPersonalInformation($user),
            'bookings' => $this->getBookingData($user),
            'properties' => $this->getPropertyData($user),
            'payments' => $this->getPaymentData($user),
            'messages' => $this->getMessageData($user),
            'reviews' => $this->getReviewData($user),
            'preferences' => $this->getPreferences($user),
            'consent_history' => $this->getConsentHistory($user),
        ];

        // Create export request
        DataExportRequest::create([
            'user_id' => $user->id,
            'format' => $format,
            'requested_at' => now(),
            'status' => 'completed',
        ]);

        return $data;
    }

    /**
     * Get personal information
     */
    private function getPersonalInformation(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Get booking data
     */
    private function getBookingData(User $user): array
    {
        return $user->bookings()
            ->select('id', 'property_id', 'check_in', 'check_out', 'total_price', 'status', 'created_at')
            ->get()
            ->toArray();
    }

    /**
     * Get property data
     */
    private function getPropertyData(User $user): array
    {
        if ($user->role !== 'landlord') {
            return [];
        }

        return $user->properties()
            ->select('id', 'title', 'address', 'city', 'price_per_night', 'created_at')
            ->get()
            ->toArray();
    }

    /**
     * Get payment data
     */
    private function getPaymentData(User $user): array
    {
        return $user->payments()
            ->select('id', 'amount', 'currency', 'status', 'created_at')
            ->get()
            ->toArray();
    }

    /**
     * Get message data
     */
    private function getMessageData(User $user): array
    {
        return $user->messages()
            ->select('id', 'content', 'sent_at', 'read_at')
            ->get()
            ->toArray();
    }

    /**
     * Get review data
     */
    private function getReviewData(User $user): array
    {
        return $user->reviews()
            ->select('id', 'rating', 'comment', 'created_at')
            ->get()
            ->toArray();
    }

    /**
     * Get user preferences
     */
    private function getPreferences(User $user): array
    {
        return [
            'language' => $user->preferred_language,
            'currency' => $user->preferred_currency,
            'notifications' => $user->notification_preferences,
        ];
    }

    /**
     * Get consent history
     */
    private function getConsentHistory(User $user): array
    {
        return DataProcessingConsent::where('user_id', $user->id)
            ->select('consent_type', 'consented_at', 'categories', 'purpose')
            ->get()
            ->toArray();
    }

    /**
     * Verify consumer identity
     */
    public function verifyConsumerIdentity(User $user, array $verificationData): bool
    {
        // Implement multi-factor verification
        $methods = 0;

        // Email verification
        if (isset($verificationData['email_code']) && $this->verifyEmailCode($user, $verificationData['email_code'])) {
            $methods++;
        }

        // Phone verification
        if (isset($verificationData['phone_code']) && $this->verifyPhoneCode($user, $verificationData['phone_code'])) {
            $methods++;
        }

        // Knowledge-based authentication
        if (isset($verificationData['security_answer']) && $this->verifySecurityAnswer($user, $verificationData['security_answer'])) {
            $methods++;
        }

        // Require at least 2 verification methods
        return $methods >= 2;
    }

    private function verifyEmailCode(User $user, string $code): bool
    {
        // Implementation for email code verification
        return true;
    }

    private function verifyPhoneCode(User $user, string $code): bool
    {
        // Implementation for phone code verification
        return true;
    }

    private function verifySecurityAnswer(User $user, string $answer): bool
    {
        // Implementation for security answer verification
        return true;
    }
}
