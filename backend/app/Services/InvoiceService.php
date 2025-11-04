<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    public function generateInvoice(Booking $booking): string
    {
        $data = [
            'booking' => $booking,
            'property' => $booking->property,
            'user' => $booking->user,
            'invoice_number' => 'INV-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            'invoice_date' => now()->format('Y-m-d'),
        ];

        $pdf = Pdf::loadView('invoices.booking', $data);
        
        $filename = 'invoice_' . $booking->id . '_' . time() . '.pdf';
        $path = storage_path('app/public/invoices/' . $filename);
        
        $pdf->save($path);
        
        return $filename;
    }

    public function sendInvoiceEmail(Booking $booking): void
    {
        $invoiceFilename = $this->generateInvoice($booking);
        
        Mail::to($booking->user->email)->send(
            new \App\Mail\InvoiceMail($booking, $invoiceFilename)
        );
    }

    public function calculateRefund(Booking $booking): float
    {
        $daysUntilCheckIn = now()->diffInDays($booking->check_in_date, false);
        
        // Refund policy
        if ($daysUntilCheckIn >= 30) {
            return $booking->total_amount; // 100% refund
        } elseif ($daysUntilCheckIn >= 14) {
            return $booking->total_amount * 0.5; // 50% refund
        } elseif ($daysUntilCheckIn >= 7) {
            return $booking->total_amount * 0.25; // 25% refund
        }
        
        return 0; // No refund
    }
}
