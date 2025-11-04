<?php

namespace App\Notifications;

use App\Models\AccessCode;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessCodeCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AccessCode $accessCode,
        public Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $property = $this->booking->property;
        $lock = $this->accessCode->smartLock;

        return (new MailMessage)
            ->subject('Your Access Code for ' . $property->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your smart lock access code is ready for your upcoming stay.')
            ->line('**Property:** ' . $property->title)
            ->line('**Check-in:** ' . $this->booking->check_in->format('M d, Y'))
            ->line('**Check-out:** ' . $this->booking->check_out->format('M d, Y'))
            ->line('**Lock Location:** ' . $lock->name)
            ->line('**Access Code:** ' . $this->accessCode->code)
            ->line('**Valid From:** ' . $this->accessCode->valid_from->format('M d, Y H:i'))
            ->line('**Valid Until:** ' . $this->accessCode->valid_until->format('M d, Y H:i'))
            ->line('Please keep this code secure and do not share it with anyone.')
            ->action('View Booking Details', url("/bookings/{$this->booking->id}"))
            ->line('Have a wonderful stay!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'access_code_created',
            'access_code_id' => $this->accessCode->id,
            'booking_id' => $this->booking->id,
            'property_id' => $this->booking->property_id,
            'code' => $this->accessCode->code,
            'valid_from' => $this->accessCode->valid_from,
            'valid_until' => $this->accessCode->valid_until,
            'message' => 'Your access code for ' . $this->booking->property->title . ' is ready',
        ];
    }
}

