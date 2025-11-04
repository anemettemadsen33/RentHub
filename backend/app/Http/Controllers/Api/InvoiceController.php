<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceEmailService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoicePdfService $pdfService,
        private InvoiceEmailService $emailService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $invoices = Invoice::where('user_id', $user->id)
            ->with(['booking.property', 'bankAccount', 'payments'])
            ->orderBy('invoice_date', 'desc')
            ->paginate(10);

        return response()->json($invoices);
    }

    public function show(Request $request, Invoice $invoice)
    {
        $user = $request->user();

        if ($invoice->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($invoice->load([
            'booking.property',
            'bankAccount',
            'payments',
            'user',
        ]));
    }

    public function download(Request $request, Invoice $invoice)
    {
        $user = $request->user();

        if ($invoice->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->pdfService->download($invoice);
    }

    public function resend(Request $request, Invoice $invoice)
    {
        $user = $request->user();

        if ($invoice->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sent = $this->emailService->resend($invoice);

        if ($sent) {
            return response()->json([
                'message' => 'Invoice resent successfully',
                'invoice' => $invoice->fresh(),
            ]);
        }

        return response()->json(['error' => 'Failed to resend invoice'], 500);
    }
}
