<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\GoogleCalendarToken;
use App\Services\GoogleCalendarService;
use App\Services\InvoiceGenerationService;

class BookingObserver
{
    public function __construct(
        private InvoiceGenerationService $invoiceService,
        private GoogleCalendarService $googleCalendarService,
        private \App\Services\SmartLock\SmartLockService $smartLockService
    ) {}

    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $this->syncToGoogleCalendar($booking);
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Check if status changed to confirmed
        if ($booking->isDirty('status') && $booking->status === 'confirmed') {
            $this->handleBookingConfirmed($booking);
        }

        // Sync to Google Calendar on relevant changes
        if ($booking->isDirty(['check_in', 'check_out', 'status'])) {
            $this->syncToGoogleCalendar($booking);
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        $this->removeFromGoogleCalendar($booking);
    }

    /**
     * Handle booking confirmation
     */
    private function handleBookingConfirmed(Booking $booking): void
    {
        try {
            // Check if we can generate invoice
            if (! $this->invoiceService->canGenerateInvoice($booking)) {
                \Log::info('Skipping invoice generation for booking', [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'has_invoice' => $booking->invoices()->exists(),
                ]);

                return;
            }

            // Generate invoice and send email
            $invoice = $this->invoiceService->createFromBooking($booking, sendEmail: true);

            \Log::info('Auto-generated invoice for confirmed booking', [
                'booking_id' => $booking->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to auto-generate invoice for booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Generate smart lock access code
        try {
            $accessCode = $this->smartLockService->createAccessCodeForBooking($booking);

            if ($accessCode) {
                \Log::info('Auto-generated access code for confirmed booking', [
                    'booking_id' => $booking->id,
                    'access_code_id' => $accessCode->id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to auto-generate access code for booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync booking to Google Calendar
     */
    private function syncToGoogleCalendar(Booking $booking): void
    {
        try {
            // Get Google Calendar tokens for this property
            $tokens = GoogleCalendarToken::where('property_id', $booking->property_id)
                ->where('sync_enabled', true)
                ->get();

            foreach ($tokens as $token) {
                $this->googleCalendarService->syncBookingToGoogle($booking, $token);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync booking to Google Calendar', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove booking from Google Calendar
     */
    private function removeFromGoogleCalendar(Booking $booking): void
    {
        try {
            // Get Google Calendar tokens for this property
            $tokens = GoogleCalendarToken::where('property_id', $booking->property_id)
                ->where('sync_enabled', true)
                ->get();

            foreach ($tokens as $token) {
                $this->googleCalendarService->deleteBookingFromGoogle($booking, $token);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to remove booking from Google Calendar', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
