<?php

namespace App\Notifications;

use App\Models\SavedSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class SavedSearchNewListingsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public SavedSearch $savedSearch;
    public Collection $newProperties;

    public function __construct(SavedSearch $savedSearch, Collection $newProperties)
    {
        $this->savedSearch = $savedSearch;
        $this->newProperties = $newProperties;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->newProperties->count();
        $searchName = $this->savedSearch->name;
        
        $mail = (new MailMessage)
            ->subject("🏠 {$count} New " . ($count === 1 ? 'Property' : 'Properties') . " for '{$searchName}'")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Great news! We found {$count} new " . ($count === 1 ? 'property' : 'properties') . " matching your saved search: **{$searchName}**");

        // Add up to 3 properties preview
        $previewCount = min(3, $count);
        foreach ($this->newProperties->take($previewCount) as $property) {
            $mail->line("**{$property->title}**")
                 ->line("📍 {$property->city}, {$property->country}")
                 ->line("💰 \${$property->price_per_night}/night")
                 ->line("🛏️ {$property->bedrooms} bed · 🛁 {$property->bathrooms} bath")
                 ->line('---');
        }

        if ($count > 3) {
            $mail->line("And " . ($count - 3) . " more...");
        }

        $mail->action('View All Properties', url("/search?saved_search={$this->savedSearch->id}"))
             ->line('Act fast - these properties might get booked quickly!')
             ->line('You can manage your saved searches and alerts in your account settings.');

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'saved_search_new_listings',
            'saved_search_id' => $this->savedSearch->id,
            'saved_search_name' => $this->savedSearch->name,
            'properties_count' => $this->newProperties->count(),
            'properties' => $this->newProperties->take(5)->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'price_per_night' => $property->price_per_night,
                    'city' => $property->city,
                    'image' => $property->images->first()?->url ?? null,
                ];
            }),
            'url' => url("/search?saved_search={$this->savedSearch->id}"),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
