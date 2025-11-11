<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Payment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Plată Procesată #'.$this->payment->id)
            ->greeting('Bună ziua!')
            ->line('Plata dumneavoastră a fost procesată cu succes.')
            ->line('Sumă: '.number_format($this->payment->amount, 2).' RON')
            ->line('Metodă: '.ucfirst($this->payment->payment_method))
            ->line('Data: '.$this->payment->created_at->format('d.m.Y H:i'))
            ->action('Vezi Detalii', url('/admin/payments/'.$this->payment->id))
            ->line('Vă mulțumim!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'message' => 'Plată procesată: '.number_format($this->payment->amount, 2).' RON',
        ];
    }
}
