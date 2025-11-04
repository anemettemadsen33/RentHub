<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;

class InvoiceEmailService
{
    public function __construct(
        private InvoicePdfService $pdfService
    ) {}

    public function send(Invoice $invoice): bool
    {
        try {
            // Generate PDF if it doesn't exist
            if (! $invoice->pdf_path) {
                $this->pdfService->generate($invoice);
                $invoice->refresh();
            }

            // Send email
            Mail::to($invoice->customer_email)->send(new InvoiceMail($invoice));

            // Update invoice
            $invoice->update([
                'sent_at' => now(),
                'send_count' => $invoice->send_count + 1,
                'status' => $invoice->status === 'draft' ? 'sent' : $invoice->status,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function resend(Invoice $invoice): bool
    {
        // Regenerate PDF
        $this->pdfService->regenerate($invoice);
        $invoice->refresh();

        return $this->send($invoice);
    }
}
