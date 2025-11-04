<?php

namespace App\Observers;

use App\Models\Property;
use App\Notifications\PriceDropNotification;

class PropertyObserver
{
    public function updated(Property $property): void
    {
        if ($property->isDirty('price_per_night')) {
            $oldPrice = $property->getOriginal('price_per_night');
            $newPrice = $property->price_per_night;

            if ($newPrice < $oldPrice) {
                $this->notifyPriceDrop($property, $oldPrice, $newPrice);
            }
        }
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
}
