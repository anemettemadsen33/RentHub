<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\BankAccount;
use Carbon\Carbon;

class InvoiceGenerationService
{
    public function __construct(
        private BankAccountService $bankAccountService,
        private InvoicePdfService $pdfService,
        private InvoiceEmailService $emailService
    ) {
    }

    /**
     * Create invoice from booking
     */
    public function createFromBooking(Booking $booking, bool $sendEmail = true): Invoice
    {
        // Load relationships
        $booking->load(['property', 'user']);

        // Get appropriate bank account
        $bankAccount = $this->selectBankAccount($booking);

        if (!$bankAccount) {
            throw new \Exception('No valid bank account found for invoice generation');
        }

        // Validate bank account
        if (!$this->bankAccountService->isValidForInvoicing($bankAccount)) {
            $errors = $this->bankAccountService->validateForInvoicing($bankAccount);
            throw new \Exception('Bank account validation failed: ' . implode(', ', $errors));
        }

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'property_id' => $booking->property_id,
            'bank_account_id' => $bankAccount->id,
            'invoice_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(7), // 7 days payment term
            'status' => 'sent',
            
            // Amounts
            'subtotal' => $booking->subtotal,
            'cleaning_fee' => $booking->cleaning_fee ?? 0,
            'security_deposit' => $booking->security_deposit ?? 0,
            'taxes' => $booking->taxes ?? 0,
            'total_amount' => $booking->total_amount,
            'currency' => $booking->property->currency ?? 'EUR',
            
            // Customer information (cached)
            'customer_name' => $booking->guest_name ?? $booking->user->name,
            'customer_email' => $booking->guest_email ?? $booking->user->email,
            'customer_phone' => $booking->guest_phone ?? $booking->user->phone,
            'customer_address' => $booking->user->address ?? null,
            
            // Property information (cached)
            'property_title' => $booking->property->title,
            'property_address' => $booking->property->full_address ?? $booking->property->address,
            
            // Notes
            'notes' => "Booking reference: {$booking->id}. Check-in: {$booking->check_in->format('d M Y')}. Check-out: {$booking->check_out->format('d M Y')}.",
        ]);

        // Generate PDF
        try {
            $this->pdfService->generate($invoice);
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id,
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send email if requested
        if ($sendEmail) {
            try {
                $this->emailService->send($invoice);
            } catch (\Exception $e) {
                \Log::error('Failed to send invoice email', [
                    'invoice_id' => $invoice->id,
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        \Log::info('Invoice created successfully', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'booking_id' => $booking->id,
            'bank_account_id' => $bankAccount->id,
        ]);

        return $invoice->fresh();
    }

    /**
     * Select appropriate bank account for booking
     */
    private function selectBankAccount(Booking $booking): ?BankAccount
    {
        // First priority: Property owner's default account
        if ($booking->property && $booking->property->user_id) {
            $account = $this->bankAccountService->getForProperty($booking->property_id);
            
            if ($account && $this->bankAccountService->isValidForInvoicing($account)) {
                return $account;
            }
        }

        // Second priority: Company default account
        $account = $this->bankAccountService->getCompanyDefault();
        
        if ($account && $this->bankAccountService->isValidForInvoicing($account)) {
            return $account;
        }

        // Last resort: Any active company account
        $account = $this->bankAccountService->getAnyCompanyAccount();
        
        if ($account && $this->bankAccountService->isValidForInvoicing($account)) {
            return $account;
        }

        return null;
    }

    /**
     * Regenerate invoice (useful for updates)
     */
    public function regenerateInvoice(Invoice $invoice, bool $sendEmail = false): Invoice
    {
        // Regenerate PDF
        $this->pdfService->regenerate($invoice);

        // Optionally send email
        if ($sendEmail) {
            $this->emailService->resend($invoice);
        }

        return $invoice->fresh();
    }

    /**
     * Check if booking can have invoice generated
     */
    public function canGenerateInvoice(Booking $booking): bool
    {
        // Don't generate if already has invoice
        if ($booking->invoices()->exists()) {
            return false;
        }

        // Only generate for confirmed or paid bookings
        if (!in_array($booking->status, ['confirmed', 'paid', 'checked_in', 'completed'])) {
            return false;
        }

        // Need valid property and user
        if (!$booking->property_id || !$booking->user_id) {
            return false;
        }

        return true;
    }
}
