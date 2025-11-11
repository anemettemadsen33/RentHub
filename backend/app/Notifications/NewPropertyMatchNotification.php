<?php

namespace App\Notifications;

use App\Models\Property;
use App\Models\SavedSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPropertyMatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected SavedSearch $savedSearch;
    protected array $properties;

    /**
     * Create a new notification instance.
     */
    public function __construct(SavedSearch $savedSearch, array $properties)
    {
        $this->savedSearch = $savedSearch;
        $this->properties = $properties;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->savedSearch->email_notifications) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->properties);
        $searchName = $this->savedSearch->name;

        $message = (new MailMessage)
            ->subject("New Properties Match Your Saved Search: {$searchName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("We found {$count} new " . ($count === 1 ? 'property' : 'properties') . " matching your saved search \"{$searchName}\".");

        // Add property details
        foreach (array_slice($this->properties, 0, 5) as $property) {
            $message->line('---')
                ->line("**{$property->title}**")
                ->line("{$property->city}, {$property->country}")
                ->line("Price: \${$property->price_per_night}/night")
                ->line("{$property->bedrooms} bed · {$property->bathrooms} bath · {$property->max_guests} guests")
                ->action('View Property', url("/properties/{$property->id}"));
        }

        if ($count > 5) {
            $remaining = $count - 5;
            $message->line("...and {$remaining} more " . ($remaining === 1 ? 'property' : 'properties'));
        }

        $message->line('Visit your saved searches to manage your preferences.')
            ->action('View All Matches', url('/saved-searches'));

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'saved_search_match',
            'saved_search_id' => $this->savedSearch->id,
            'saved_search_name' => $this->savedSearch->name,
            'property_count' => count($this->properties),
            'properties' => array_map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'city' => $property->city,
                    'country' => $property->country,
                    'price_per_night' => $property->price_per_night,
                    'image_url' => $property->image_url ?? $property->images[0] ?? null,
                ];
            }, array_slice($this->properties, 0, 5)),
        ];
    }
}
