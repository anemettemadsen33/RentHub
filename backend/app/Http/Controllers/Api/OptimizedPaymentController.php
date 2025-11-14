<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\OptimizedInvoicePdfService;
use App\Services\InvoiceEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class OptimizedPaymentController extends Controller
{
    public function __construct(
        private OptimizedInvoicePdfService $pdfService,
        private InvoiceEmailService $emailService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $startTime = microtime(true);

        // Use cache for frequently accessed payment data
        $cacheKey = "user_payments_{$user->id}_page_{$request->get('page', 1)}";
        $payments = Cache::remember($cacheKey, 300, function () use ($user) {
            return Payment::where('user_id', $user->id)
                ->with(['booking.property:id,title,address,city', 'invoice:id,invoice_number,status'])
                ->select(['id', 'booking_id', 'invoice_id', 'amount', 'status', 'payment_method', 'type', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        });

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('Optimized payments index completed', [
            'user_id' => $user->id,
            'duration_ms' => $duration,
            'payments_count' => $payments->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $payments,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    public function store(Request $request)
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:bank_transfer,paypal,cash',
            'type' => 'sometimes|in:full,deposit,balance',
            'bank_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        // Use cache for booking validation
        $cacheKey = "booking_validation_{$request->booking_id}";
        $booking = Cache::remember($cacheKey, 300, function () use ($request) {
            return Booking::findOrFail($request->booking_id);
        });

        if ($booking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Enforce amount equals booking total
        if ((float) $request->amount !== (float) ($booking->total_price ?? 0)) {
            return response()->json([
                'errors' => [
                    'amount' => ['Amount must match booking total'],
                ],
            ], 422);
        }

        // Prevent duplicate completed payments for the same booking
        $paymentCacheKey = "booking_payment_status_{$booking->id}";
        $hasCompleted = Cache::remember($paymentCacheKey, 300, function () use ($booking) {
            return Payment::where('booking_id', $booking->id)
                ->where('status', 'completed')
                ->exists();
        });
        
        if ($hasCompleted) {
            return response()->json([
                'errors' => [
                    'booking_id' => ['Payment already completed for this booking'],
                ],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create payment
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(),
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'currency' => 'EUR',
                'type' => $request->input('type', 'full'),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'bank_reference' => $request->bank_reference,
                'notes' => $request->notes,
                'initiated_at' => now(),
            ]);

            // Queue invoice creation and PDF generation
            try {
                if (! $booking->invoices()->exists()) {
                    Queue::push(function () use ($payment, $booking) {
                        $this->createInvoiceForBooking($booking, $payment);
                    });
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to queue invoice creation', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Queue notification to property owner
            try {
                Queue::push(function () use ($payment) {
                    $this->notifyPropertyOwner($payment);
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to queue owner notification', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            // Clear relevant caches
            Cache::forget("user_payments_{$user->id}_page_1");
            Cache::forget($paymentCacheKey);

            $payment->refresh();
            $duration = (microtime(true) - $startTime) * 1000;
            
            Log::info('Optimized payment creation completed', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'duration_ms' => $duration,
                'amount' => $payment->amount
            ]);

            return response()->json(
                $payment->only(['id', 'booking_id', 'amount', 'status', 'payment_method', 'type']) + 
                ['created_at' => $payment->created_at, 'duration_ms' => $duration], 
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Optimized payment creation failed', [
                'user_id' => $user->id,
                'booking_id' => $request->booking_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to create payment: '.$e->getMessage()], 500);
        }
    }

    public function show(Request $request, Payment $payment)
    {
        $startTime = microtime(true);
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Use cache for payment details
        $cacheKey = "payment_details_{$payment->id}";
        $paymentData = Cache::remember($cacheKey, 300, function () use ($payment) {
            return $payment->load([
                'booking.property:id,title,address,city,country',
                'invoice:id,invoice_number,status,pdf_path'
            ]);
        });

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('Optimized payment show completed', [
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'duration_ms' => $duration
        ]);

        return response()->json($paymentData);
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:processing,completed,failed',
            'transaction_id' => 'nullable|string',
            'failure_reason' => 'required_if:status,failed|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if ($payment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            if ($request->status === 'completed') {
                $payment->markAsCompleted([
                    'transaction_id' => $request->transaction_id,
                ]);
            } elseif ($request->status === 'failed') {
                $payment->markAsFailed($request->failure_reason);
            } else {
                $payment->update(['status' => $request->status]);
            }

            // Clear payment cache
            Cache::forget("payment_details_{$payment->id}");
            Cache::forget("user_payments_{$user->id}_page_1");

            $duration = (microtime(true) - $startTime) * 1000;
            Log::info('Optimized payment status update completed', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'new_status' => $request->status,
                'duration_ms' => $duration
            ]);

            return response()->json($payment->fresh()->load([
                'booking.property:id,title,address,city',
                'invoice:id,invoice_number,status'
            ]));
        } catch (\Exception $e) {
            Log::error('Optimized payment status update failed', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to update payment: '.$e->getMessage()], 500);
        }
    }

    public function confirm(Request $request, Payment $payment)
    {
        $startTime = microtime(true);
        $user = $request->user();
        
        if ($payment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payment->markAsCompleted();

        // Clear payment cache
        Cache::forget("payment_details_{$payment->id}");
        Cache::forget("user_payments_{$user->id}_page_1");

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('Optimized payment confirmation completed', [
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'duration_ms' => $duration
        ]);

        return response()->json($payment->fresh());
    }

    public function refund(Request $request, Payment $payment)
    {
        $startTime = microtime(true);
        $user = $request->user();
        
        if ($payment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->filled('reason')) {
            $payment->notes = trim(($payment->notes ? ($payment->notes."\n") : '').'Refund reason: '.$request->reason);
            $payment->save();
        }

        $payment->markAsRefunded();

        // Clear payment cache
        Cache::forget("payment_details_{$payment->id}");
        Cache::forget("user_payments_{$user->id}_page_1");

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('Optimized payment refund completed', [
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'duration_ms' => $duration
        ]);

        return response()->json($payment->fresh());
    }

    private function createInvoiceForBooking(Booking $booking, Payment $payment): Invoice
    {
        $startTime = microtime(true);
        
        $property = $booking->property;
        $user = $booking->user;
        
        // Use cache for bank account lookup
        $cacheKey = "default_bank_account";
        $bankAccount = Cache::remember($cacheKey, 3600, function () use ($property) {
            return \App\Models\BankAccount::where('is_default', true)
                ->whereNull('user_id')
                ->first() ?? 
                \App\Models\BankAccount::where('user_id', $property->user_id)
                    ->where('is_default', true)
                    ->first();
        });

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'property_id' => $property->id,
            'bank_account_id' => $bankAccount?->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'draft',
            'subtotal' => $booking->total_price,
            'cleaning_fee' => $property->cleaning_fee ?? 0,
            'security_deposit' => $property->security_deposit ?? 0,
            'taxes' => 0,
            'total_amount' => $booking->total_price + ($property->cleaning_fee ?? 0) + ($property->security_deposit ?? 0),
            'currency' => 'EUR',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone ?? null,
            'property_title' => $property->title,
            'property_address' => $property->address.', '.$property->city.', '.$property->country,
        ]);

        // Generate PDF and send email
        $this->pdfService->generate($invoice);
        
        // Queue email sending
        Queue::push(function () use ($invoice) {
            $this->emailService->send($invoice);
        });

        // Update payment with invoice ID
        $payment->update(['invoice_id' => $invoice->id]);

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('Optimized invoice creation completed', [
            'invoice_id' => $invoice->id,
            'booking_id' => $booking->id,
            'duration_ms' => $duration
        ]);

        return $invoice;
    }

    private function notifyPropertyOwner(Payment $payment): void
    {
        try {
            $booking = $payment->booking;
            $booking->loadMissing('property');
            
            $ownerId = $booking->property?->owner_id ?? $booking->property?->user_id;
            if ($ownerId) {
                $owner = \App\Models\User::find($ownerId);
                if ($owner) {
                    $owner->notify(new \App\Notifications\PaymentReceivedNotification($payment));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send payment received notification', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}