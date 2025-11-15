<?php

namespace App\Observers;

use App\Models\Property;
use App\Notifications\PriceDropNotification;
use App\Services\DashboardService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class PropertyObserver
{
    public function created(Property $property): void
    {
        $this->invalidateCaches($property);
    }

    public function updated(Property $property): void
    {
        if ($property->isDirty('price_per_night')) {
            $oldPrice = $property->getOriginal('price_per_night');
            $newPrice = $property->price_per_night;

            if ($newPrice < $oldPrice) {
                // Dispatch job instead of synchronous notification
                \App\Jobs\SendPriceDropNotifications::dispatch($property, $oldPrice, $newPrice);
            }
        }

        $this->invalidateCaches($property);
    }

    public function deleted(Property $property): void
    {
        $this->invalidateCaches($property);
    }

    protected function notifyPriceDrop(Property $property, float $oldPrice, float $newPrice): void
    {
        $property->wishlistItems()
            ->with(['wishlist.user'])
            ->where(function ($query) use ($newPrice) {
                $query->whereNull('price_alert')
                    ->orWhere('price_alert', '>=', $newPrice);
            })
            ->get()
            ->each(function ($item) use ($property, $oldPrice, $newPrice) {
                if ($item->wishlist && $item->wishlist->user) {
                    $item->wishlist->user->notify(
                        new PriceDropNotification($property, $item, $oldPrice, $newPrice)
                    );
                }
            });
    }

    protected function invalidateCaches(Property $property): void
    {
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            // Driver without tag support – ignore
        }
        /** @var DashboardService $dashboard */
        $dashboard = App::make(DashboardService::class);
        $dashboard->invalidateUserStats($property->user_id);
    }
}
