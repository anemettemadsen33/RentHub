<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmedNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load relationships if needed
            $this->booking->loadMissing(['user', 'property.user']);

            // Notify guest
            if ($this->booking->user) {
                $this->booking->user->notify(
                    new BookingConfirmedNotification($this->booking, 'guest')
                );
            }

            // Notify owner
            if ($this->booking->property && $this->booking->property->user) {
                $this->booking->property->user->notify(
                    new BookingConfirmedNotification($this->booking, 'owner')
                );
            }

            Log::info('Booking confirmation notifications sent', [
                'booking_id' => $this->booking->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send booking confirmation notifications', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Booking confirmation notification job failed permanently', [
            'booking_id' => $this->booking->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
