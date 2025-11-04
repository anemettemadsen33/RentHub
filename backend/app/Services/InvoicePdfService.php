<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function generate(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice->load(['booking', 'user', 'property', 'bankAccount']),
        ]);

        $filename = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::put($filename, $pdf->output());

        $invoice->update(['pdf_path' => $filename]);

        return $filename;
    }

    public function download(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            $this->generate($invoice);
        }

        return Storage::download($invoice->pdf_path, $invoice->invoice_number . '.pdf');
    }

    public function stream(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            $this->generate($invoice);
        }

        return response()->file(Storage::path($invoice->pdf_path));
    }

    public function regenerate(Invoice $invoice): string
    {
        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
            Storage::delete($invoice->pdf_path);
        }

        return $this->generate($invoice);
    }
}
