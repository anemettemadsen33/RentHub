<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FraudAlert;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FraudDetectionController extends Controller
{
    /**
     * Get fraud alerts
     */
    public function getAlerts(Request $request)
    {
        $query = FraudAlert::with(['user', 'property', 'booking', 'payment']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }

        $alerts = $query->orderBy('fraud_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
        ]);
    }

    /**
     * Get fraud alert details
     */
    public function getAlertDetails($alertId)
    {
        $alert = FraudAlert::with(['user', 'property', 'booking', 'payment', 'reviewer'])
            ->findOrFail($alertId);

        return response()->json([
            'success' => true,
            'alert' => $alert,
        ]);
    }

    /**
     * Check user for fraud indicators
     */
    public function checkUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $fraudScore = $this->calculateUserFraudScore($user);
        $indicators = $this->getUserFraudIndicators($user);

        if ($fraudScore > 70) {
            $this->createFraudAlert(
                'suspicious_user',
                $fraudScore > 85 ? 'high' : 'medium',
                $user->id,
                null,
                null,
                null,
                'Suspicious user activity detected',
                $indicators,
                $fraudScore
            );
        }

        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'fraud_score' => $fraudScore,
            'risk_level' => $this->getRiskLevel($fraudScore),
            'indicators' => $indicators,
        ]);
    }

    /**
     * Check property for fraud indicators
     */
    public function checkProperty(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $fraudScore = $this->calculatePropertyFraudScore($property);
        $indicators = $this->getPropertyFraudIndicators($property);

        if ($fraudScore > 70) {
            $this->createFraudAlert(
                'suspicious_listing',
                $fraudScore > 85 ? 'high' : 'medium',
                $property->user_id,
                $property->id,
                null,
                null,
                'Suspicious property listing detected',
                $indicators,
                $fraudScore
            );
        }

        return response()->json([
            'success' => true,
            'property_id' => $propertyId,
            'fraud_score' => $fraudScore,
            'risk_level' => $this->getRiskLevel($fraudScore),
            'indicators' => $indicators,
        ]);
    }

    /**
     * Check booking for fraud indicators
     */
    public function checkBooking(Request $request, $bookingId)
    {
        $booking = Booking::with(['user', 'property'])->findOrFail($bookingId);

        $fraudScore = $this->calculateBookingFraudScore($booking);
        $indicators = $this->getBookingFraudIndicators($booking);

        if ($fraudScore > 70) {
            $this->createFraudAlert(
                'suspicious_booking',
                $fraudScore > 85 ? 'high' : 'medium',
                $booking->user_id,
                $booking->property_id,
                $booking->id,
                null,
                'Suspicious booking detected',
                $indicators,
                $fraudScore
            );
        }

        return response()->json([
            'success' => true,
            'booking_id' => $bookingId,
            'fraud_score' => $fraudScore,
            'risk_level' => $this->getRiskLevel($fraudScore),
            'indicators' => $indicators,
        ]);
    }

    /**
     * Check payment for fraud indicators
     */
    public function checkPayment(Request $request, $paymentId)
    {
        $payment = Payment::with(['booking', 'user'])->findOrFail($paymentId);

        $fraudScore = $this->calculatePaymentFraudScore($payment);
        $indicators = $this->getPaymentFraudIndicators($payment);

        if ($fraudScore > 70) {
            $this->createFraudAlert(
                'payment_fraud',
                $fraudScore > 85 ? 'critical' : 'high',
                $payment->user_id,
                null,
                $payment->booking_id,
                $payment->id,
                'Suspicious payment detected',
                $indicators,
                $fraudScore
            );
        }

        return response()->json([
            'success' => true,
            'payment_id' => $paymentId,
            'fraud_score' => $fraudScore,
            'risk_level' => $this->getRiskLevel($fraudScore),
            'indicators' => $indicators,
        ]);
    }

    /**
     * Resolve fraud alert
     */
    public function resolveAlert(Request $request, $alertId)
    {
        $request->validate([
            'resolution_notes' => 'required|string',
            'action_type' => 'sometimes|in:account_suspended,property_removed,payment_blocked,review_removed,no_action',
        ]);

        $alert = FraudAlert::findOrFail($alertId);
        $alert->resolve($request->user()->id, $request->resolution_notes, $request->action_type);

        // Execute action if specified
        if ($request->has('action_type') && $request->action_type !== 'no_action') {
            $this->executeAction($alert, $request->action_type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fraud alert resolved successfully',
            'alert' => $alert->fresh(),
        ]);
    }

    /**
     * Mark alert as false positive
     */
    public function markFalsePositive(Request $request, $alertId)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $alert = FraudAlert::findOrFail($alertId);
        $alert->markFalsePositive($request->user()->id, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Marked as false positive',
            'alert' => $alert->fresh(),
        ]);
    }

    /**
     * Get fraud detection stats
     */
    public function getStats(Request $request)
    {
        $stats = [
            'total_alerts' => FraudAlert::count(),
            'pending_alerts' => FraudAlert::where('status', 'pending')->count(),
            'resolved_alerts' => FraudAlert::where('status', 'resolved')->count(),
            'false_positives' => FraudAlert::where('status', 'false_positive')->count(),
            'critical_alerts' => FraudAlert::where('severity', 'critical')->count(),
            'alerts_by_type' => FraudAlert::select('alert_type', DB::raw('count(*) as count'))
                ->groupBy('alert_type')
                ->get()
                ->pluck('count', 'alert_type'),
            'alerts_by_severity' => FraudAlert::select('severity', DB::raw('count(*) as count'))
                ->groupBy('severity')
                ->get()
                ->pluck('count', 'severity'),
            'average_fraud_score' => FraudAlert::avg('fraud_score'),
            'detection_rate' => $this->calculateDetectionRate(),
            'recent_alerts' => FraudAlert::orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Run fraud detection scan
     */
    public function runScan(Request $request)
    {
        $request->validate([
            'scan_type' => 'required|in:users,properties,bookings,payments,all',
        ]);

        $results = [];

        if ($request->scan_type === 'users' || $request->scan_type === 'all') {
            $results['users'] = $this->scanUsers();
        }

        if ($request->scan_type === 'properties' || $request->scan_type === 'all') {
            $results['properties'] = $this->scanProperties();
        }

        if ($request->scan_type === 'bookings' || $request->scan_type === 'all') {
            $results['bookings'] = $this->scanBookings();
        }

        if ($request->scan_type === 'payments' || $request->scan_type === 'all') {
            $results['payments'] = $this->scanPayments();
        }

        return response()->json([
            'success' => true,
            'message' => 'Fraud detection scan completed',
            'results' => $results,
        ]);
    }

    // ========== Private Methods ==========

    /**
     * Calculate user fraud score
     */
    private function calculateUserFraudScore(User $user): float
    {
        $score = 0;

        // New account (created in last 7 days)
        if ($user->created_at->diffInDays(now()) < 7) {
            $score += 20;
        }

        // No verified email
        if (! $user->email_verified_at) {
            $score += 15;
        }

        // No profile picture
        if (! $user->avatar) {
            $score += 10;
        }

        // Multiple accounts from same IP (would need IP tracking)
        // This is a placeholder - implement IP tracking if needed

        // Rapid booking activity
        $recentBookings = Booking::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentBookings > 5) {
            $score += 25;
        }

        // Multiple cancellations
        $cancellationRate = $this->getCancellationRate($user->id);
        if ($cancellationRate > 0.5) {
            $score += 20;
        }

        // Bot-like behavior
        $botScore = $this->detectBotBehavior($user->id);
        $score += $botScore;

        return min(100, $score);
    }

    /**
     * Calculate property fraud score
     */
    private function calculatePropertyFraudScore(Property $property): float
    {
        $score = 0;

        // No photos
        if (! $property->photos || count($property->photos) == 0) {
            $score += 25;
        }

        // Too-good-to-be-true pricing
        $avgPrice = Property::where('city', $property->city)
            ->where('type', $property->type)
            ->avg('price_per_night');

        if ($avgPrice && $property->price_per_night < ($avgPrice * 0.5)) {
            $score += 30;
        }

        // Duplicate listing (similar title/description)
        $duplicateCount = Property::where('id', '!=', $property->id)
            ->where(function ($query) use ($property) {
                $query->where('title', 'LIKE', '%'.substr($property->title, 0, 20).'%')
                    ->orWhere('address', $property->address);
            })
            ->count();

        if ($duplicateCount > 0) {
            $score += 35;
        }

        // New owner with no verification
        $owner = $property->owner;
        if ($owner && ! $owner->email_verified_at) {
            $score += 15;
        }

        // Suspicious description (external links, phone numbers)
        if ($this->hasSuspiciousContent($property->description)) {
            $score += 20;
        }

        return min(100, $score);
    }

    /**
     * Calculate booking fraud score
     */
    private function calculateBookingFraudScore(Booking $booking): float
    {
        $score = 0;

        // Last-minute booking for high-value property
        if ($booking->check_in->diffInHours(now()) < 24 && $booking->total_price > 500) {
            $score += 20;
        }

        // Unusual booking duration
        $duration = $booking->check_in->diffInDays($booking->check_out);
        if ($duration > 180) {
            $score += 15;
        }

        // New user making expensive booking
        $user = $booking->user;
        if ($user->created_at->diffInDays(now()) < 7 && $booking->total_price > 1000) {
            $score += 30;
        }

        // Multiple bookings in short time
        $recentBookings = Booking::where('user_id', $booking->user_id)
            ->where('created_at', '>=', now()->subHours(6))
            ->where('id', '!=', $booking->id)
            ->count();

        if ($recentBookings > 3) {
            $score += 25;
        }

        // Unusual guest count
        if ($booking->number_of_guests > $booking->property->guests) {
            $score += 20;
        }

        return min(100, $score);
    }

    /**
     * Calculate payment fraud score
     */
    private function calculatePaymentFraudScore(Payment $payment): float
    {
        $score = 0;

        // High-value payment from new user
        if ($payment->amount > 1000) {
            $user = $payment->user;
            if ($user && $user->created_at->diffInDays(now()) < 7) {
                $score += 40;
            }
        }

        // Multiple failed payments
        $failedPayments = Payment::where('user_id', $payment->user_id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($failedPayments > 3) {
            $score += 30;
        }

        // Payment from different country (would need IP geolocation)
        // Placeholder for geo-location check

        // Rapid successive payments
        $recentPayments = Payment::where('user_id', $payment->user_id)
            ->where('created_at', '>=', now()->subHours(1))
            ->where('id', '!=', $payment->id)
            ->count();

        if ($recentPayments > 2) {
            $score += 20;
        }

        return min(100, $score);
    }

    /**
     * Get user fraud indicators
     */
    private function getUserFraudIndicators(User $user): array
    {
        $indicators = [];

        if ($user->created_at->diffInDays(now()) < 7) {
            $indicators[] = 'New account';
        }

        if (! $user->email_verified_at) {
            $indicators[] = 'Unverified email';
        }

        if (! $user->avatar) {
            $indicators[] = 'No profile picture';
        }

        $cancellationRate = $this->getCancellationRate($user->id);
        if ($cancellationRate > 0.5) {
            $indicators[] = 'High cancellation rate ('.round($cancellationRate * 100).'%)';
        }

        $recentBookings = Booking::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentBookings > 5) {
            $indicators[] = 'Rapid booking activity ('.$recentBookings.' in 24h)';
        }

        if ($this->detectBotBehavior($user->id) > 20) {
            $indicators[] = 'Bot-like behavior detected';
        }

        return $indicators;
    }

    /**
     * Get property fraud indicators
     */
    private function getPropertyFraudIndicators(Property $property): array
    {
        $indicators = [];

        if (! $property->photos || count($property->photos) == 0) {
            $indicators[] = 'No photos uploaded';
        }

        $avgPrice = Property::where('city', $property->city)
            ->where('type', $property->type)
            ->avg('price_per_night');

        if ($avgPrice && $property->price_per_night < ($avgPrice * 0.5)) {
            $indicators[] = 'Price significantly below market average';
        }

        if ($this->hasSuspiciousContent($property->description)) {
            $indicators[] = 'Suspicious content in description';
        }

        $owner = $property->owner;
        if ($owner && ! $owner->email_verified_at) {
            $indicators[] = 'Owner not verified';
        }

        return $indicators;
    }

    /**
     * Get booking fraud indicators
     */
    private function getBookingFraudIndicators(Booking $booking): array
    {
        $indicators = [];

        if ($booking->check_in->diffInHours(now()) < 24) {
            $indicators[] = 'Last-minute booking';
        }

        $duration = $booking->check_in->diffInDays($booking->check_out);
        if ($duration > 180) {
            $indicators[] = 'Unusually long stay ('.$duration.' days)';
        }

        $user = $booking->user;
        if ($user->created_at->diffInDays(now()) < 7) {
            $indicators[] = 'New user account';
        }

        if ($booking->number_of_guests > $booking->property->guests) {
            $indicators[] = 'Guest count exceeds property capacity';
        }

        return $indicators;
    }

    /**
     * Get payment fraud indicators
     */
    private function getPaymentFraudIndicators(Payment $payment): array
    {
        $indicators = [];

        if ($payment->amount > 1000) {
            $indicators[] = 'High-value transaction';
        }

        $failedPayments = Payment::where('user_id', $payment->user_id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($failedPayments > 3) {
            $indicators[] = 'Multiple failed payment attempts';
        }

        return $indicators;
    }

    /**
     * Detect bot behavior
     */
    private function detectBotBehavior(int $userId): float
    {
        $behaviors = UserBehavior::where('user_id', $userId)
            ->where('action_at', '>=', now()->subHours(1))
            ->orderBy('action_at')
            ->get();

        if ($behaviors->count() < 10) {
            return 0;
        }

        $score = 0;

        // Check for regular intervals (bot-like)
        $intervals = [];
        for ($i = 1; $i < $behaviors->count(); $i++) {
            $interval = $behaviors[$i]->action_at->diffInSeconds($behaviors[$i - 1]->action_at);
            $intervals[] = $interval;
        }

        if (! empty($intervals)) {
            $avgInterval = array_sum($intervals) / count($intervals);
            $variance = 0;
            foreach ($intervals as $interval) {
                $variance += pow($interval - $avgInterval, 2);
            }
            $variance /= count($intervals);

            // Low variance suggests bot behavior
            if ($variance < 10) {
                $score += 30;
            }
        }

        // Check for rapid-fire actions
        if ($behaviors->count() > 50) {
            $score += 20;
        }

        return $score;
    }

    /**
     * Check for suspicious content
     */
    private function hasSuspiciousContent(string $text): bool
    {
        $patterns = [
            '/\b\d{10,}\b/', // Phone numbers
            '/https?:\/\//', // URLs
            '/whatsapp/i',
            '/telegram/i',
            '/email.*@/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cancellation rate
     */
    private function getCancellationRate(int $userId): float
    {
        $totalBookings = Booking::where('user_id', $userId)->count();

        if ($totalBookings == 0) {
            return 0;
        }

        $cancelledBookings = Booking::where('user_id', $userId)
            ->where('status', 'cancelled')
            ->count();

        return $cancelledBookings / $totalBookings;
    }

    /**
     * Get risk level
     */
    private function getRiskLevel(float $fraudScore): string
    {
        if ($fraudScore >= 85) {
            return 'critical';
        }
        if ($fraudScore >= 70) {
            return 'high';
        }
        if ($fraudScore >= 50) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Create fraud alert
     */
    private function createFraudAlert(
        string $alertType,
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
            'alert_type' => $alertType,
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
     * Execute action on fraud alert
     */
    private function executeAction(FraudAlert $alert, string $actionType): void
    {
        switch ($actionType) {
            case 'account_suspended':
                if ($alert->user) {
                    // Suspend user account
                    $alert->user->update(['status' => 'suspended']);
                }
                break;

            case 'property_removed':
                if ($alert->property) {
                    // Remove property
                    $alert->property->update(['status' => 'removed']);
                }
                break;

            case 'payment_blocked':
                if ($alert->payment) {
                    // Block payment
                    $alert->payment->update(['status' => 'blocked']);
                }
                break;

            case 'review_removed':
                // Would need review_id in alert
                break;
        }
    }

    /**
     * Calculate detection rate
     */
    private function calculateDetectionRate(): float
    {
        $total = FraudAlert::count();
        $truePositives = FraudAlert::where('status', 'resolved')
            ->where('action_taken', true)
            ->count();

        return $total > 0 ? round(($truePositives / $total) * 100, 2) : 0;
    }

    /**
     * Scan users for fraud
     */
    private function scanUsers(): array
    {
        $users = User::where('created_at', '>=', now()->subDays(30))->get();
        $flaggedCount = 0;

        foreach ($users as $user) {
            $fraudScore = $this->calculateUserFraudScore($user);

            if ($fraudScore > 70) {
                $indicators = $this->getUserFraudIndicators($user);
                $this->createFraudAlert(
                    'suspicious_user',
                    $fraudScore > 85 ? 'high' : 'medium',
                    $user->id,
                    null,
                    null,
                    null,
                    'Suspicious user activity detected during scan',
                    $indicators,
                    $fraudScore
                );
                $flaggedCount++;
            }
        }

        return [
            'scanned' => $users->count(),
            'flagged' => $flaggedCount,
        ];
    }

    /**
     * Scan properties for fraud
     */
    private function scanProperties(): array
    {
        $properties = Property::where('created_at', '>=', now()->subDays(30))->get();
        $flaggedCount = 0;

        foreach ($properties as $property) {
            $fraudScore = $this->calculatePropertyFraudScore($property);

            if ($fraudScore > 70) {
                $indicators = $this->getPropertyFraudIndicators($property);
                $this->createFraudAlert(
                    'suspicious_listing',
                    $fraudScore > 85 ? 'high' : 'medium',
                    $property->user_id,
                    $property->id,
                    null,
                    null,
                    'Suspicious property listing detected during scan',
                    $indicators,
                    $fraudScore
                );
                $flaggedCount++;
            }
        }

        return [
            'scanned' => $properties->count(),
            'flagged' => $flaggedCount,
        ];
    }

    /**
     * Scan bookings for fraud
     */
    private function scanBookings(): array
    {
        $bookings = Booking::where('created_at', '>=', now()->subDays(7))->get();
        $flaggedCount = 0;

        foreach ($bookings as $booking) {
            $fraudScore = $this->calculateBookingFraudScore($booking);

            if ($fraudScore > 70) {
                $indicators = $this->getBookingFraudIndicators($booking);
                $this->createFraudAlert(
                    'suspicious_booking',
                    $fraudScore > 85 ? 'high' : 'medium',
                    $booking->user_id,
                    $booking->property_id,
                    $booking->id,
                    null,
                    'Suspicious booking detected during scan',
                    $indicators,
                    $fraudScore
                );
                $flaggedCount++;
            }
        }

        return [
            'scanned' => $bookings->count(),
            'flagged' => $flaggedCount,
        ];
    }

    /**
     * Scan payments for fraud
     */
    private function scanPayments(): array
    {
        $payments = Payment::where('created_at', '>=', now()->subDays(7))->get();
        $flaggedCount = 0;

        foreach ($payments as $payment) {
            $fraudScore = $this->calculatePaymentFraudScore($payment);

            if ($fraudScore > 70) {
                $indicators = $this->getPaymentFraudIndicators($payment);
                $this->createFraudAlert(
                    'payment_fraud',
                    $fraudScore > 85 ? 'critical' : 'high',
                    $payment->user_id,
                    null,
                    $payment->booking_id,
                    $payment->id,
                    'Suspicious payment detected during scan',
                    $indicators,
                    $fraudScore
                );
                $flaggedCount++;
            }
        }

        return [
            'scanned' => $payments->count(),
            'flagged' => $flaggedCount,
        ];
    }
}

