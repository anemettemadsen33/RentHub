<?php

namespace App\Notifications\Payment;

use App\Models\Payment;
use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return NotificationPreference::getEnabledChannels(
            $notifiable->id,
            NotificationPreference::TYPE_PAYMENT
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->payment->booking;
        $property = $booking->property;
        
        return (new MailMessage)
            ->subject('✅ Payment Confirmed - ' . $property->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your payment has been successfully received and processed.')
            ->line('**Amount Paid:** $' . number_format($this->payment->amount, 2))
            ->line('**Payment Method:** ' . ucfirst($this->payment->payment_method))
            ->line('**Transaction ID:** ' . $this->payment->transaction_id)
            ->line('**Property:** ' . $property->title)
            ->line('**Booking ID:** #' . $booking->id)
            ->action('View Receipt', url('/dashboard/bookings/' . $booking->id))
            ->line('A receipt has been sent to your email.')
            ->line('Thank you for your payment!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_received',
            'payment_id' => $this->payment->id,
            'booking_id' => $this->payment->booking_id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'transaction_id' => $this->payment->transaction_id,
            'message' => 'Payment of $' . number_format($this->payment->amount, 2) . ' received successfully',
            'action_url' => '/dashboard/bookings/' . $this->payment->booking_id,
        ];
    }
}
