<?php

namespace App\Notifications\Booking;

use App\Models\Booking;
use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRequestNotification extends Notification implements ShouldQueue
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
        $tenant = $this->booking->user;
        $checkIn = $this->booking->check_in_date->format('M d, Y');
        $checkOut = $this->booking->check_out_date->format('M d, Y');
        $nights = $this->booking->check_in_date->diffInDays($this->booking->check_out_date);
        
        return (new MailMessage)
            ->subject('🔔 New Booking Request - ' . $property->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have received a new booking request for your property.')
            ->line('**Property:** ' . $property->title)
            ->line('**Guest:** ' . $tenant->name)
            ->line('**Check-in:** ' . $checkIn)
            ->line('**Check-out:** ' . $checkOut)
            ->line('**Duration:** ' . $nights . ' night' . ($nights > 1 ? 's' : ''))
            ->line('**Number of Guests:** ' . $this->booking->guests)
            ->line('**Total Amount:** $' . number_format($this->booking->total_price, 2))
            ->line('**Booking ID:** #' . $this->booking->id)
            ->action('Review & Respond', url('/admin/bookings/' . $this->booking->id))
            ->line('Please review and respond to this booking request promptly.')
            ->line('Thank you for being a valued host on RentHub!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_request',
            'booking_id' => $this->booking->id,
            'property_id' => $this->booking->property_id,
            'property_title' => $this->booking->property->title,
            'guest_name' => $this->booking->user->name,
            'check_in_date' => $this->booking->check_in_date->toDateString(),
            'check_out_date' => $this->booking->check_out_date->toDateString(),
            'guests' => $this->booking->guests,
            'total_price' => $this->booking->total_price,
            'message' => 'New booking request from ' . $this->booking->user->name . ' for ' . $this->booking->property->title,
            'action_url' => '/admin/bookings/' . $this->booking->id,
        ];
    }
}
