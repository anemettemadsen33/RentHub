<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\RentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RentPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = RentPayment::with(['longTermRental.property', 'tenant', 'invoice']);

        if ($request->has('long_term_rental_id')) {
            $query->where('long_term_rental_id', $request->long_term_rental_id);
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        if ($request->boolean('upcoming')) {
            $days = $request->get('days', 7);
            $query->upcoming($days);
        }

        $perPage = $request->get('per_page', 15);
        $payments = $query->orderBy('due_date')->paginate($perPage);

        return response()->json($payments);
    }

    public function show($id)
    {
        $payment = RentPayment::with([
            'longTermRental.property',
            'tenant',
            'invoice',
        ])->findOrFail($id);

        return response()->json($payment);
    }

    public function markAsPaid(Request $request, $id)
    {
        $payment = RentPayment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment->markAsPaid(
            $request->amount,
            $request->payment_method,
            $request->get('transaction_id')
        );

        // Generate and send invoice if needed
        if ($request->boolean('generate_invoice', true)) {
            $this->generateInvoice($payment);
        }

        return response()->json([
            'message' => 'Payment marked as paid successfully',
            'payment' => $payment->fresh(['invoice']),
        ]);
    }

    public function updateOverdue()
    {
        $overduePayments = RentPayment::overdue()->get();

        foreach ($overduePayments as $payment) {
            $payment->updateOverdueStatus();
        }

        return response()->json([
            'message' => 'Overdue payments updated',
            'count' => $overduePayments->count(),
        ]);
    }

    public function sendReminder($id)
    {
        $payment = RentPayment::with(['longTermRental.property', 'tenant'])->findOrFail($id);

        // Send reminder logic here
        $payment->increment('reminder_count');
        $payment->update(['reminder_sent_at' => now()]);

        return response()->json([
            'message' => 'Reminder sent successfully',
        ]);
    }

    private function generateInvoice(RentPayment $payment)
    {
        $rental = $payment->longTermRental()->with(['property', 'owner'])->first();

        $invoice = Invoice::create([
            'invoice_number' => 'RENT-'.str_pad($payment->id, 6, '0', STR_PAD_LEFT),
            'user_id' => $payment->tenant_id,
            'booking_id' => null,
            'bank_account_id' => $rental->owner->bankAccounts()->first()?->id,
            'amount' => $payment->getTotalAmount(),
            'status' => 'paid',
            'issued_at' => now(),
            'due_date' => $payment->due_date,
            'paid_at' => $payment->payment_date,
        ]);

        $payment->update(['invoice_id' => $invoice->id]);

        // Send email
        try {
            Mail::to($payment->tenant->email)->send(new InvoiceMail($invoice));
        } catch (\Exception $e) {
            \Log::error('Failed to send rent invoice email: '.$e->getMessage());
        }

        return $invoice;
    }
}
