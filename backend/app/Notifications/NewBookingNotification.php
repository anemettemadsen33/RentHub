<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'booking',
            'booking_id' => $this->booking->id,
            'property_id' => $this->booking->property_id,
            'message' => 'New booking request received.',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Booking Request')
            ->line('A new booking has been created for your property.')
            ->action('View Booking', url('/dashboard/bookings/'.$this->booking->id));
    }
}
