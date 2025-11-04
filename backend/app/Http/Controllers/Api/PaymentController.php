<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceEmailService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct(
        private InvoicePdfService $pdfService,
        private InvoiceEmailService $emailService
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        $payments = Payment::where('user_id', $user->id)
            ->with(['booking.property', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:bank_transfer,paypal,cash',
            'type' => 'required|in:full,deposit,balance',
            'bank_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
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
                'type' => $request->type,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'bank_reference' => $request->bank_reference,
                'notes' => $request->notes,
                'initiated_at' => now(),
            ]);

            // Create invoice if it doesn't exist
            if (!$booking->invoices()->exists()) {
                $invoice = $this->createInvoiceForBooking($booking);
                $payment->update(['invoice_id' => $invoice->id]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment initiated successfully',
                'payment' => $payment->load(['invoice', 'booking.property']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create payment: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($payment->load(['booking.property', 'invoice']));
    }

    public function updateStatus(Request $request, Payment $payment)
    {
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

            return response()->json([
                'message' => 'Payment status updated successfully',
                'payment' => $payment->fresh()->load(['booking.property', 'invoice']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update payment: ' . $e->getMessage()], 500);
        }
    }

    private function createInvoiceForBooking(Booking $booking): Invoice
    {
        $property = $booking->property;
        $user = $booking->user;
        $bankAccount = \App\Models\BankAccount::where('is_default', true)
            ->whereNull('user_id')
            ->first();

        if (!$bankAccount && $property->user_id) {
            $bankAccount = \App\Models\BankAccount::where('user_id', $property->user_id)
                ->where('is_default', true)
                ->first();
        }

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
            'property_address' => $property->address . ', ' . $property->city . ', ' . $property->country,
        ]);

        // Generate PDF and send email
        $this->pdfService->generate($invoice);
        $this->emailService->send($invoice);

        return $invoice;
    }
}
