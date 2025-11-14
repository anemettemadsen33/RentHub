<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CircuitBreakerService;
use App\Services\OptimizedInvoicePdfService;
use App\Services\InvoiceEmailService;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

class OptimizedPaymentWithCircuitBreakerController extends Controller
{
    private CircuitBreakerService $pdfCircuitBreaker;
    private CircuitBreakerService $emailCircuitBreaker;
    private OptimizedInvoicePdfService $pdfService;
    private InvoiceEmailService $emailService;

    public function __construct(
        OptimizedInvoicePdfService $pdfService,
        InvoiceEmailService $emailService
    ) {
        $this->pdfService = $pdfService;
        $this->emailService = $emailService;
        $this->pdfCircuitBreaker = app('circuit_breaker.pdf');
        $this->emailCircuitBreaker = app('circuit_breaker.email');
    }

    /**
     * Create payment with circuit breaker protection
     */
    public function createPayment(Request $request)
    {
        $startTime = microtime(true);
        $requestId = uniqid('payment_');

        try {
            Log::info('Starting payment creation with circuit breaker', [
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

            // Get booking and bank account with caching
            $booking = $this->getCachedBooking($validated['booking_id']);
            $bankAccount = $this->getCachedBankAccount($validated['bank_account_id']);

            if (!$booking || !$bankAccount) {
                return response()->json([
                    'error' => 'Booking or bank account not found'
                ], 404);
            }

            // Create payment
            $payment = Payment::create([
                'booking_id' => $validated['booking_id'],
                'user_id' => $request->user()->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'bank_account_id' => $validated['bank_account_id'],
                'status' => 'pending',
                'transaction_id' => 'TXN_' . uniqid(),
                'notes' => $validated['notes'] ?? null
            ]);

            // Generate invoice PDF with circuit breaker protection
            $this->generateInvoiceWithCircuitBreaker($payment, $requestId);

            // Send email notification with circuit breaker protection
            $this->sendEmailWithCircuitBreaker($payment, $requestId);

            $totalTime = microtime(true) - $startTime;

            Log::info('Payment creation completed with circuit breaker', [
                'request_id' => $requestId,
                'payment_id' => $payment->id,
                'total_time' => $totalTime
            ]);

            return response()->json([
                'payment' => $payment->load(['booking', 'bankAccount']),
                'processing_time' => $totalTime,
                'message' => 'Payment created successfully. Invoice and email will be processed.'
            ]);

        } catch (\Exception $e) {
            $totalTime = microtime(true) - $startTime;
            
            Log::error('Payment creation failed with circuit breaker', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'total_time' => $totalTime
            ]);

            return response()->json([
                'error' => 'Failed to create payment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate invoice PDF with circuit breaker protection
     */
    private function generateInvoiceWithCircuitBreaker(Payment $payment, string $requestId): void
    {
        try {
            $this->pdfCircuitBreaker->execute(function () use ($payment, $requestId) {
                Log::info('Generating invoice PDF with circuit breaker', [
                    'request_id' => $requestId,
                    'payment_id' => $payment->id
                ]);

                // Generate PDF asynchronously
                Queue::push(function () use ($payment, $requestId) {
                    try {
                        $this->pdfService->generate($payment->id);
                        Log::info('Invoice PDF generated successfully', [
                            'request_id' => $requestId,
                            'payment_id' => $payment->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to generate invoice PDF in queue', [
                            'request_id' => $requestId,
                            'payment_id' => $payment->id,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                });
            }, 'generate_invoice');

        } catch (\Exception $e) {
            Log::warning('Invoice PDF generation failed - circuit breaker is open', [
                'request_id' => $requestId,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            // Still create the payment, but log that PDF generation is unavailable
            $payment->update([
                'notes' => ($payment->notes ?? '') . " [PDF generation unavailable]"
            ]);
        }
    }

    /**
     * Send email notification with circuit breaker protection
     */
    private function sendEmailWithCircuitBreaker(Payment $payment, string $requestId): void
    {
        try {
            $this->emailCircuitBreaker->execute(function () use ($payment, $requestId) {
                Log::info('Sending payment email with circuit breaker', [
                    'request_id' => $requestId,
                    'payment_id' => $payment->id
                ]);

                // Send email asynchronously
                Queue::push(function () use ($payment, $requestId) {
                    try {
                        $this->emailService->sendPaymentEmail($payment);
                        Log::info('Payment email sent successfully', [
                            'request_id' => $requestId,
                            'payment_id' => $payment->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment email in queue', [
                            'request_id' => $requestId,
                            'payment_id' => $payment->id,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                });
            }, 'send_email');

        } catch (\Exception $e) {
            Log::warning('Payment email sending failed - circuit breaker is open', [
                'request_id' => $requestId,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            // Still create the payment, but log that email sending is unavailable
            $payment->update([
                'notes' => ($payment->notes ?? '') . " [Email sending unavailable]"
            ]);
        }
    }

    /**
     * Get cached booking
     */
    private function getCachedBooking(int $bookingId): ?Booking
    {
        return Cache::remember("booking_{$bookingId}", 300, function () use ($bookingId) {
            return Booking::find($bookingId);
        });
    }

    /**
     * Get cached bank account
     */
    private function getCachedBankAccount(int $bankAccountId): ?BankAccount
    {
        return Cache::remember("bank_account_{$bankAccountId}", 300, function () use ($bankAccountId) {
            return BankAccount::find($bankAccountId);
        });
    }

    /**
     * Get circuit breaker statistics
     */
    public function getCircuitBreakerStats(Request $request)
    {
        try {
            $pdfStats = $this->pdfCircuitBreaker->getStats();
            $emailStats = $this->emailCircuitBreaker->getStats();

            return response()->json([
                'pdf_circuit_breaker' => $pdfStats,
                'email_circuit_breaker' => $emailStats,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get circuit breaker statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to retrieve circuit breaker statistics'
            ], 500);
        }
    }

    /**
     * Reset circuit breakers
     */
    public function resetCircuitBreakers(Request $request)
    {
        try {
            $this->pdfCircuitBreaker->reset();
            $this->emailCircuitBreaker->reset();

            Log::info('Circuit breakers manually reset', [
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Circuit breakers reset successfully',
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reset circuit breakers', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to reset circuit breakers'
            ], 500);
        }
    }
}