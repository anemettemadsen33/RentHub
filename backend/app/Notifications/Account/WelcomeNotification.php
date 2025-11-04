<?php

namespace App\Notifications\Account;

use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return NotificationPreference::getEnabledChannels(
            $notifiable->id,
            NotificationPreference::TYPE_ACCOUNT
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to RentHub!')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('Thank you for joining RentHub - your trusted platform for property rentals.')
            ->line('We are excited to have you as part of our community!')
            ->line('**Here is what you can do:**')
            ->line('- Browse thousands of verified properties')
            ->line('- Book your next stay in minutes')
            ->line('- Connect with property owners')
            ->line('- Leave reviews and earn trust')
            ->action('Explore Properties', url('/properties'))
            ->line('If you are a property owner, you can also list your properties and start earning!')
            ->line('Need help? Our support team is here for you 24/7.')
            ->line('Happy renting!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'message' => 'Welcome to RentHub! Start exploring amazing properties.',
            'action_url' => '/properties',
        ];
    }
}
