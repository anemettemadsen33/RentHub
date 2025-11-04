<?php

namespace App\Notifications\Booking;

use App\Models\Booking;
use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return NotificationPreference::getEnabledChannels(
            $notifiable->id,
            NotificationPreference::TYPE_BOOKING
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $property = $this->booking->property;
        $checkIn = $this->booking->check_in_date->format('M d, Y');
        $checkOut = $this->booking->check_out_date->format('M d, Y');
        
        return (new MailMessage)
            ->subject('🎉 Booking Confirmed - ' . $property->title)
            ->greeting('Great news, ' . $notifiable->name . '!')
            ->line('Your booking has been confirmed by the property owner.')
            ->line('**Property:** ' . $property->title)
            ->line('**Check-in:** ' . $checkIn)
            ->line('**Check-out:** ' . $checkOut)
            ->line('**Total Amount:** $' . number_format($this->booking->total_price, 2))
            ->line('**Booking ID:** #' . $this->booking->id)
            ->action('View Booking Details', url('/dashboard/bookings/' . $this->booking->id))
            ->line('An invoice has been sent to your email. Please complete the payment before your check-in date.')
            ->line('If you have any questions, feel free to contact the property owner.')
            ->line('Thank you for choosing RentHub!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_confirmed',
            'booking_id' => $this->booking->id,
            'property_id' => $this->booking->property_id,
            'property_title' => $this->booking->property->title,
            'check_in_date' => $this->booking->check_in_date->toDateString(),
            'check_out_date' => $this->booking->check_out_date->toDateString(),
            'total_price' => $this->booking->total_price,
            'message' => 'Your booking for ' . $this->booking->property->title . ' has been confirmed!',
            'action_url' => '/dashboard/bookings/' . $this->booking->id,
        ];
    }
}
