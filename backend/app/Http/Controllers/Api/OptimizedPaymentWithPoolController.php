<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DatabaseConnectionPoolService;
use App\Services\OptimizedInvoicePdfService;
use App\Services\InvoiceEmailService;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class OptimizedPaymentWithPoolController extends Controller
{
    private DatabaseConnectionPoolService $poolService;
    private OptimizedInvoicePdfService $pdfService;
    private InvoiceEmailService $emailService;

    public function __construct(
        DatabaseConnectionPoolService $poolService,
        OptimizedInvoicePdfService $pdfService,
        InvoiceEmailService $emailService
    ) {
        $this->poolService = $poolService;
        $this->pdfService = $pdfService;
        $this->emailService = $emailService;
    }

    /**
     * Create payment with connection pooling optimization
     */
    public function createPayment(Request $request)
    {
        $startTime = microtime(true);
        $requestId = uniqid('payment_');

        try {
            Log::info('Starting payment creation with connection pooling', [
                'request_id' => $requestId,
                'user_id' => $request->user()->id
            ]);

            // Validate request
            $validated = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string|max:50',
                'bank_account_id' => 'required|exists:bank_accounts,id',
                'notes' => 'nullable|string|max:500'
            ]);

            // Use connection pooling for database operations
            $booking = $this->getBookingWithPool($validated['booking_id']);
            $bankAccount = $this->getBankAccountWithPool($validated['bank_account_id']);

            if (!$booking || !$bankAccount) {
                return response()->json([
                    'error' => 'Booking or bank account not found'
                ], 404);
            }

            // Create payment with pooled connection
            $payment = $this->createPaymentWithPool($validated, $request->user()->id);

            // Generate invoice PDF asynchronously
            Queue::push(function () use ($payment, $requestId) {
                try {
                    Log::info('Generating invoice PDF in queue', [
                        'request_id' => $requestId,
                        'payment_id' => $payment->id
                    ]);

                    $this->pdfService->generate($payment->id);
                } catch (\Exception $e) {
                    Log::error('Failed to generate invoice PDF in queue', [
                        'request_id' => $requestId,
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            });

            // Send email notification asynchronously
            Queue::push(function () use ($payment, $requestId) {
                try {
                    Log::info('Sending payment email in queue', [
                        'request_id' => $requestId,
                        'payment_id' => $payment->id
                    ]);

                    $this->emailService->sendPaymentEmail($payment);
                } catch (\Exception $e) {
                    Log::error('Failed to send payment email in queue', [
                        'request_id' => $requestId,
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            });

            $totalTime = microtime(true) - $startTime;

            Log::info('Payment creation completed with connection pooling', [
                'request_id' => $requestId,
                'payment_id' => $payment->id,
                'total_time' => $totalTime,
                'pool_stats' => $this->poolService->getPoolStats()
            ]);

            return response()->json([
                'payment' => $payment->load(['booking', 'bankAccount']),
                'processing_time' => $totalTime,
                'message' => 'Payment created successfully. Invoice will be generated shortly.'
            ]);

        } catch (\Exception $e) {
            $totalTime = microtime(true) - $startTime;
            
            Log::error('Payment creation failed with connection pooling', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'total_time' => $totalTime,
                'pool_stats' => $this->poolService->getPoolStats()
            ]);

            return response()->json([
                'error' => 'Failed to create payment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking using connection pool
     */
    private function getBookingWithPool(int $bookingId): ?Booking
    {
        $result = $this->poolService->executeWithPool(
            'SELECT * FROM bookings WHERE id = ? AND deleted_at IS NULL',
            [$bookingId]
        );

        if (!empty($result['data'])) {
            $bookingData = $result['data'][0];
            $booking = new Booking((array) $bookingData);
            $booking->exists = true;
            return $booking;
        }

        return null;
    }

    /**
     * Get bank account using connection pool
     */
    private function getBankAccountWithPool(int $bankAccountId): ?BankAccount
    {
        $result = $this->poolService->executeWithPool(
            'SELECT * FROM bank_accounts WHERE id = ? AND deleted_at IS NULL',
            [$bankAccountId]
        );

        if (!empty($result['data'])) {
            $accountData = $result['data'][0];
            $bankAccount = new BankAccount((array) $accountData);
            $bankAccount->exists = true;
            return $bankAccount;
        }

        return null;
    }

    /**
     * Create payment using connection pool
     */
    private function createPaymentWithPool(array $data, int $userId): Payment
    {
        $paymentData = [
            'booking_id' => $data['booking_id'],
            'user_id' => $userId,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'bank_account_id' => $data['bank_account_id'],
            'status' => 'pending',
            'transaction_id' => 'TXN_' . uniqid(),
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $result = $this->poolService->executeWithPool(
            'INSERT INTO payments (booking_id, user_id, amount, payment_method, bank_account_id, status, transaction_id, notes, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array_values($paymentData)
        );

        // Get the created payment ID
        $paymentIdResult = $this->poolService->executeWithPool('SELECT LAST_INSERT_ID() as id');
        $paymentId = $paymentIdResult['data'][0]->id;

        // Create payment model instance
        $paymentData['id'] = $paymentId;
        $payment = new Payment($paymentData);
        $payment->exists = true;

        return $payment;
    }

    /**
     * Get payment statistics with connection pooling
     */
    public function getPaymentStats(Request $request)
    {
        try {
            $userId = $request->user()->id;
            
            // Get payment statistics using connection pool
            $statsResult = $this->poolService->executeWithPool(
                'SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_completed,
                    SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending,
                    AVG(amount) as average_amount,
                    MAX(created_at) as last_payment_date
                 FROM payments 
                 WHERE user_id = ? AND deleted_at IS NULL',
                [$userId]
            );

            $monthlyStatsResult = $this->poolService->executeWithPool(
                'SELECT 
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                 FROM payments 
                 WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) AND deleted_at IS NULL
                 GROUP BY DATE_FORMAT(created_at, "%Y-%m")
                 ORDER BY month DESC',
                [$userId]
            );

            $poolStats = $this->poolService->getPoolStats();

            return response()->json([
                'stats' => $statsResult['data'][0] ?? null,
                'monthly_stats' => $monthlyStatsResult['data'] ?? [],
                'pool_performance' => $poolStats,
                'execution_times' => [
                    'stats_query' => $statsResult['execution_time'] ?? 0,
                    'monthly_query' => $monthlyStatsResult['execution_time'] ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get payment statistics', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'error' => 'Failed to retrieve payment statistics'
            ], 500);
        }
    }

    /**
     * Get pool statistics for monitoring
     */
    public function getPoolStats(Request $request)
    {
        try {
            $stats = $this->poolService->getPoolStats();
            
            return response()->json([
                'pool_stats' => $stats,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get pool statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to retrieve pool statistics'
            ], 500);
        }
    }
}