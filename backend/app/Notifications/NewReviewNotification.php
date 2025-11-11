<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Review $review
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $stars = str_repeat('⭐', $this->review->rating);

        return (new MailMessage)
            ->subject('Review Nou pentru '.$this->review->property->name)
            ->greeting('Bună ziua!')
            ->line('Aveți un review nou pentru proprietatea dumneavoastră.')
            ->line('Proprietate: '.$this->review->property->name)
            ->line('Rating: '.$stars.' ('.$this->review->rating.'/5)')
            ->line('De la: '.$this->review->user->name)
            ->line('Comentariu: '.substr($this->review->comment, 0, 100).'...')
            ->action('Vezi Review-ul', url('/admin/reviews/'.$this->review->id))
            ->line('Vă rugăm să răspundeți la review!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'property_name' => $this->review->property->name,
            'rating' => $this->review->rating,
            'reviewer_name' => $this->review->user->name,
            'message' => 'Review nou pentru '.$this->review->property->name,
        ];
    }
}
