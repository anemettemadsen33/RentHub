<?php

namespace App\Services\AI;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\FraudAlert;
use App\Models\UserBehavior;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FraudDetectionService
{
    private const FRAUD_THRESHOLD = 70.0;
    private const HIGH_RISK_THRESHOLD = 85.0;

    /**
     * Analyze user behavior for suspicious activity
     */
    public function analyzeUserBehavior(int $userId): ?FraudAlert
    {
        $user = User::with(['bookings', 'properties', 'reviews'])->findOrFail($userId);
        $fraudScore = 0;
        $evidence = [];

        // Check account age
        $accountAgeDays = now()->diffInDays($user->created_at);
        if ($accountAgeDays < 1) {
            $fraudScore += 20;
            $evidence[] = 'Very new account (< 24 hours)';
        }

        // Check for rapid booking attempts
        $recentBookings = Booking::where('user_id', $userId)
            ->where('created_at', '>', now()->subHours(24))
            ->count();
        
        if ($recentBookings > 5) {
            $fraudScore += 25;
            $evidence[] = "Unusual number of booking attempts: {$recentBookings} in 24 hours";
        }

        // Check for incomplete profile
        if (!$user->email_verified_at) {
            $fraudScore += 15;
            $evidence[] = 'Email not verified';
        }

        // Check payment failure rate
        $failedPayments = Payment::where('user_id', $userId)
            ->where('status', 'failed')
            ->where('created_at', '>', now()->subDays(7))
            ->count();
        
        if ($failedPayments > 3) {
            $fraudScore += 30;
            $evidence[] = "Multiple failed payments: {$failedPayments}";
        }

        // Check for bot-like behavior patterns
        $behaviors = UserBehavior::where('user_id', $userId)
            ->where('action_at', '>', now()->subHour())
            ->get();
        
        if ($behaviors->count() > 100) {
            $fraudScore += 35;
            $evidence[] = 'Excessive activity (possible bot)';
        }

        // Check for multiple accounts from same IP
        $sameIpUsers = $this->findUsersWithSameIP($user);
        if ($sameIpUsers > 3) {
            $fraudScore += 20;
            $evidence[] = "Multiple accounts from same IP: {$sameIpUsers}";
        }

        if ($fraudScore >= self::FRAUD_THRESHOLD) {
            return $this->createAlert(
                'bot_behavior',
                $this->determineSeverity($fraudScore),
                $user->id,
                null,
                null,
                null,
                'Suspicious user behavior detected',
                $evidence,
                $fraudScore
            );
        }

        return null;
    }

    /**
     * Analyze property listing for fraud
     */
    public function analyzePropertyListing(int $propertyId): ?FraudAlert
    {
        $property = Property::with(['owner', 'images'])->findOrFail($propertyId);
        $fraudScore = 0;
        $evidence = [];

        // Check for stock images or duplicates
        if ($this->hasStockImages($property)) {
            $fraudScore += 40;
            $evidence[] = 'Property uses stock images';
        }

        // Check if property exists in multiple locations
        $duplicateProperties = Property::where('title', $property->title)
            ->where('id', '!=', $propertyId)
            ->count();
        
        if ($duplicateProperties > 0) {
            $fraudScore += 50;
            $evidence[] = "Property title appears in {$duplicateProperties} other listings";
        }

        // Check for unrealistic pricing
        $avgPrice = Property::where('city', $property->city)
            ->where('type', $property->type)
            ->avg('price_per_night');
        
        if ($avgPrice > 0 && $property->price_per_night < $avgPrice * 0.3) {
            $fraudScore += 45;
            $evidence[] = "Price significantly below market average (70% lower)";
        }

        // Check owner account
        $ownerAge = now()->diffInDays($property->owner->created_at);
        if ($ownerAge < 7) {
            $fraudScore += 25;
            $evidence[] = 'Owner account is very new (< 7 days)';
        }

        // Check for missing verification
        if (!$property->owner->verification || $property->owner->verification->verified === false) {
            $fraudScore += 20;
            $evidence[] = 'Owner not verified';
        }

        // Check description for suspicious patterns
        if ($this->hasS suspiciousContent($property->description)) {
            $fraudScore += 30;
            $evidence[] = 'Description contains suspicious content';
        }

        if ($fraudScore >= self::FRAUD_THRESHOLD) {
            return $this->createAlert(
                'suspicious_listing',
                $this->determineSeverity($fraudScore),
                $property->owner_id,
                $propertyId,
                null,
                null,
                'Suspicious property listing detected',
                $evidence,
                $fraudScore
            );
        }

        return null;
    }

    /**
     * Analyze payment for fraud
     */
    public function analyzePayment(int $paymentId): ?FraudAlert
    {
        $payment = Payment::with(['user', 'booking'])->findOrFail($paymentId);
        $fraudScore = 0;
        $evidence = [];

        // Check for unusual payment amount
        if ($payment->booking) {
            $expectedAmount = $payment->booking->total_price;
            if ($payment->amount != $expectedAmount) {
                $fraudScore += 40;
                $evidence[] = "Payment amount mismatch: Expected {$expectedAmount}, Got {$payment->amount}";
            }
        }

        // Check payment velocity (multiple payments in short time)
        $recentPayments = Payment::where('user_id', $payment->user_id)
            ->where('created_at', '>', now()->subHours(1))
            ->count();
        
        if ($recentPayments > 5) {
            $fraudScore += 35;
            $evidence[] = "High payment velocity: {$recentPayments} payments in 1 hour";
        }

        // Check for payment from high-risk country
        if ($this->isHighRiskCountry($payment->metadata['country'] ?? null)) {
            $fraudScore += 25;
            $evidence[] = 'Payment from high-risk country';
        }

        // Check for mismatched billing info
        if ($this->hasMismatchedBillingInfo($payment)) {
            $fraudScore += 30;
            $evidence[] = 'Billing information mismatch';
        }

        // Check user's payment history
        $failureRate = $this->calculatePaymentFailureRate($payment->user_id);
        if ($failureRate > 50) {
            $fraudScore += 20;
            $evidence[] = "High payment failure rate: {$failureRate}%";
        }

        // Check for card testing patterns
        if ($this->detectsCardTesting($payment->user_id)) {
            $fraudScore += 50;
            $evidence[] = 'Card testing pattern detected';
        }

        if ($fraudScore >= self::FRAUD_THRESHOLD) {
            return $this->createAlert(
                'payment_fraud',
                $this->determineSeverity($fraudScore),
                $payment->user_id,
                null,
                $payment->booking_id,
                $paymentId,
                'Suspicious payment detected',
                $evidence,
                $fraudScore
            );
        }

        return null;
    }

    /**
     * Analyze review for fraud/fake reviews
     */
    public function analyzeReview(int $reviewId): ?FraudAlert
    {
        $review = Review::with(['user', 'property'])->findOrFail($reviewId);
        $fraudScore = 0;
        $evidence = [];

        // Check if user actually booked the property
        $hasBooking = Booking::where('user_id', $review->user_id)
            ->where('property_id', $review->property_id)
            ->where('status', 'completed')
            ->exists();
        
        if (!$hasBooking) {
            $fraudScore += 60;
            $evidence[] = 'Review from user without completed booking';
        }

        // Check for spam patterns
        if ($this->hasSpamContent($review->comment)) {
            $fraudScore += 40;
            $evidence[] = 'Review contains spam patterns';
        }

        // Check user's review patterns
        $userReviews = Review::where('user_id', $review->user_id)
            ->where('created_at', '>', now()->subDay())
            ->count();
        
        if ($userReviews > 10) {
            $fraudScore += 35;
            $evidence[] = "Excessive reviews: {$userReviews} in 24 hours";
        }

        // Check for duplicate content
        $similarReviews = Review::where('user_id', $review->user_id)
            ->where('id', '!=', $reviewId)
            ->where('comment', $review->comment)
            ->count();
        
        if ($similarReviews > 0) {
            $fraudScore += 45;
            $evidence[] = 'Duplicate review content detected';
        }

        // Check rating patterns (always 5 or always 1)
        $userRatings = Review::where('user_id', $review->user_id)
            ->pluck('rating');
        
        if ($userRatings->count() > 5 && $userRatings->unique()->count() === 1) {
            $fraudScore += 25;
            $evidence[] = 'Suspicious rating pattern (all same rating)';
        }

        // Check for review farming (property owner posting fake reviews)
        if ($review->property->owner_id === $review->user_id) {
            $fraudScore += 80;
            $evidence[] = 'Property owner reviewing their own property';
        }

        if ($fraudScore >= self::FRAUD_THRESHOLD) {
            return $this->createAlert(
                'fake_review',
                $this->determineSeverity($fraudScore),
                $review->user_id,
                $review->property_id,
                null,
                null,
                'Suspicious review detected',
                $evidence,
                $fraudScore
            );
        }

        return null;
    }

    /**
     * Analyze booking for suspicious patterns
     */
    public function analyzeBooking(int $bookingId): ?FraudAlert
    {
        $booking = Booking::with(['user', 'property'])->findOrFail($bookingId);
        $fraudScore = 0;
        $evidence = [];

        // Check booking timing (immediate booking after account creation)
        $accountAge = now()->diffInHours($booking->user->created_at);
        if ($accountAge < 1) {
            $fraudScore += 30;
            $evidence[] = 'Booking made within 1 hour of account creation';
        }

        // Check for unrealistic booking duration
        $duration = \Carbon\Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
        if ($duration > 365) {
            $fraudScore += 25;
            $evidence[] = "Unusual booking duration: {$duration} days";
        }

        // Check for price manipulation
        $expectedPrice = $booking->property->price_per_night * $duration;
        $priceDiff = abs($expectedPrice - $booking->total_price) / $expectedPrice;
        if ($priceDiff > 0.5) {
            $fraudScore += 40;
            $evidence[] = 'Significant price discrepancy detected';
        }

        // Check for booking from flagged users
        $userAlerts = FraudAlert::where('user_id', $booking->user_id)
            ->where('status', '!=', 'resolved')
            ->count();
        
        if ($userAlerts > 0) {
            $fraudScore += 35;
            $evidence[] = "User has {$userAlerts} active fraud alerts";
        }

        if ($fraudScore >= self::FRAUD_THRESHOLD) {
            return $this->createAlert(
                'suspicious_booking',
                $this->determineSeverity($fraudScore),
                $booking->user_id,
                $booking->property_id,
                $bookingId,
                null,
                'Suspicious booking detected',
                $evidence,
                $fraudScore
            );
        }

        return null;
    }

    /**
     * Create fraud alert
     */
    private function createAlert(
        string $type,
        string $severity,
        ?int $userId,
        ?int $propertyId,
        ?int $bookingId,
        ?int $paymentId,
        string $description,
        array $evidence,
        float $fraudScore
    ): FraudAlert {
        return FraudAlert::create([
            'alert_type' => $type,
            'severity' => $severity,
            'user_id' => $userId,
            'property_id' => $propertyId,
            'booking_id' => $bookingId,
            'payment_id' => $paymentId,
            'description' => $description,
            'evidence' => $evidence,
            'fraud_score' => $fraudScore,
            'status' => 'pending',
        ]);
    }

    /**
     * Determine severity based on fraud score
     */
    private function determineSeverity(float $fraudScore): string
    {
        if ($fraudScore >= self::HIGH_RISK_THRESHOLD) return 'critical';
        if ($fraudScore >= 80) return 'high';
        if ($fraudScore >= 70) return 'medium';
        return 'low';
    }

    private function hasStockImages(Property $property): bool
    {
        // Placeholder - integrate with image recognition API
        return false;
    }

    private function hasSuspiciousContent(string $content): bool
    {
        $suspiciousPatterns = [
            '/contact.*outside.*platform/i',
            '/wire.*transfer/i',
            '/western.*union/i',
            '/bitcoin/i',
            '/cryptocurrency/i',
            '/urgent.*payment/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    private function findUsersWithSameIP(User $user): int
    {
        // Placeholder - requires IP tracking implementation
        return 0;
    }

    private function isHighRiskCountry(?string $country): bool
    {
        // Simplified high-risk country list
        $highRiskCountries = ['XX', 'YY']; // Replace with actual list
        return in_array($country, $highRiskCountries);
    }

    private function hasMismatchedBillingInfo(Payment $payment): bool
    {
        // Placeholder - compare billing address with user profile
        return false;
    }

    private function calculatePaymentFailureRate(int $userId): float
    {
        $total = Payment::where('user_id', $userId)->count();
        if ($total === 0) return 0;

        $failed = Payment::where('user_id', $userId)
            ->where('status', 'failed')
            ->count();

        return ($failed / $total) * 100;
    }

    private function detectsCardTesting(int $userId): bool
    {
        // Check for multiple small payments in short time (card testing pattern)
        $smallPayments = Payment::where('user_id', $userId)
            ->where('amount', '<', 10)
            ->where('created_at', '>', now()->subHour())
            ->count();

        return $smallPayments > 5;
    }

    private function hasSpamContent(string $content): bool
    {
        $spamPatterns = [
            '/buy.*now/i',
            '/click.*here/i',
            '/limited.*offer/i',
            '/http[s]?:\/\//i', // URLs
            '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', // Emails
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // Check for excessive capitalization
        $capsRatio = strlen(preg_replace('/[^A-Z]/', '', $content)) / max(strlen($content), 1);
        if ($capsRatio > 0.5) {
            return true;
        }

        return false;
    }
}
