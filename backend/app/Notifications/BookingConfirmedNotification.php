<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public string $recipientType = 'guest' // 'guest' or 'owner'
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $propertyTitle = $this->booking->property->title ?? 'Property';
        
        if ($this->recipientType === 'guest') {
            return (new MailMessage)
                ->subject('Booking Confirmed - ' . $propertyTitle)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your booking has been confirmed!')
                ->line('**Property:** ' . $propertyTitle)
                ->line('**Check-in:** ' . $this->booking->check_in->format('M d, Y'))
                ->line('**Check-out:** ' . $this->booking->check_out->format('M d, Y'))
                ->line('**Total Amount:** $' . number_format($this->booking->total_amount, 2))
                ->action('View Booking Details', url('/bookings/' . $this->booking->id))
                ->line('We hope you have a wonderful stay!');
        } else {
            // Owner notification
            $guestName = $this->booking->guest_name ?? $this->booking->user->name ?? 'Guest';
            
            return (new MailMessage)
                ->subject('New Booking Confirmed - ' . $propertyTitle)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('You have a new booking for your property!')
                ->line('**Property:** ' . $propertyTitle)
                ->line('**Guest:** ' . $guestName)
                ->line('**Check-in:** ' . $this->booking->check_in->format('M d, Y'))
                ->line('**Check-out:** ' . $this->booking->check_out->format('M d, Y'))
                ->line('**Revenue:** $' . number_format($this->booking->total_amount, 2))
                ->action('View Booking Details', url('/owner/bookings/' . $this->booking->id))
                ->line('Prepare for your guest\'s arrival!');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'property_id' => $this->booking->property_id,
            'check_in' => $this->booking->check_in->toDateString(),
            'check_out' => $this->booking->check_out->toDateString(),
            'total_amount' => $this->booking->total_amount,
            'recipient_type' => $this->recipientType,
            'message' => $this->recipientType === 'guest' 
                ? 'Your booking has been confirmed!'
                : 'You have a new booking!',
        ];
    }
}
