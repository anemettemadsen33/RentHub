<?php

namespace App\Notifications;

use App\Models\SavedSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NewListingsAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SavedSearch $savedSearch,
        public Collection $newProperties
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->newProperties->count();
        $searchName = $this->savedSearch->name;

        $mail = (new MailMessage)
            ->subject("🔔 {$count} New ".str($count)->plural('Property', 'Properties')." Match Your Search: {$searchName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Great news! We found {$count} new ".str($count)->plural('property', 'properties').' that match your saved search:')
            ->line("**{$searchName}**")
            ->line('');

        // Add up to 5 properties to email
        foreach ($this->newProperties->take(5) as $property) {
            $mail->line("🏠 **{$property->title}**")
                ->line("📍 {$property->address}")
                ->line("💰 €{$property->price_per_night}/night")
                ->line("🛏️ {$property->bedrooms} bedrooms • 🛁 {$property->bathrooms} bathrooms")
                ->line('');
        }

        if ($count > 5) {
            $mail->line('...and '.($count - 5).' more!');
        }

        $mail->action('View All Results', url('/saved-searches/'.$this->savedSearch->id.'/execute'))
            ->line('To stop receiving alerts for this search, you can disable notifications in your saved searches settings.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_listings_alert',
            'saved_search_id' => $this->savedSearch->id,
            'saved_search_name' => $this->savedSearch->name,
            'new_properties_count' => $this->newProperties->count(),
            'properties' => $this->newProperties->take(5)->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'price_per_night' => $property->price_per_night,
                    'image' => $property->images[0] ?? null,
                ];
            }),
            'message' => "{$this->newProperties->count()} new properties match your search: {$this->savedSearch->name}",
        ];
    }
}
