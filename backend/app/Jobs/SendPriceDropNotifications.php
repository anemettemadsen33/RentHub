<?php

namespace App\Jobs;

use App\Models\Property;
use App\Notifications\PriceDropNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPriceDropNotifications implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Property $property,
        public float $oldPrice,
        public float $newPrice
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $items = $this->property->wishlistItems()
                ->with(['wishlist.user'])
                ->where(function ($query) {
                    $query->whereNull('price_alert')
                        ->orWhere('price_alert', '>=', $this->newPrice);
                })
                ->get();

            $notifiedCount = 0;

            foreach ($items as $item) {
                if ($item->wishlist && $item->wishlist->user) {
                    $item->wishlist->user->notify(
                        new PriceDropNotification(
                            $this->property,
                            $item,
                            $this->oldPrice,
                            $this->newPrice
                        )
                    );
                    $notifiedCount++;
                }
            }

            Log::info('Price drop notifications sent', [
                'property_id' => $this->property->id,
                'old_price' => $this->oldPrice,
                'new_price' => $this->newPrice,
                'notified_count' => $notifiedCount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send price drop notifications', [
                'property_id' => $this->property->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Price drop notification job failed permanently', [
            'property_id' => $this->property->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
