<?php

namespace App\Notifications;

use App\Models\Property;
use App\Models\WishlistItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceDropNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Property $property,
        public WishlistItem $wishlistItem,
        public float $oldPrice,
        public float $newPrice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $discount = round((($this->oldPrice - $this->newPrice) / $this->oldPrice) * 100);
        $propertyUrl = config('app.frontend_url') . '/properties/' . $this->property->id;

        return (new MailMessage)
            ->subject('Price Drop Alert: ' . $this->property->title)
            ->greeting('Great News!')
            ->line("The price for **{$this->property->title}** has dropped!")
            ->line("**Old Price:** €{$this->oldPrice} per night")
            ->line("**New Price:** €{$this->newPrice} per night")
            ->line("**You Save:** €" . ($this->oldPrice - $this->newPrice) . " ({$discount}% off)")
            ->action('View Property', $propertyUrl)
            ->line('Book now before the price goes back up!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'price_drop',
            'property_id' => $this->property->id,
            'property_title' => $this->property->title,
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
            'savings' => $this->oldPrice - $this->newPrice,
            'wishlist_id' => $this->wishlistItem->wishlist_id,
        ];
    }
}
