<?php

namespace App\Notifications\Review;

use App\Models\NotificationPreference;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Review $review
    ) {}

    public function via(object $notifiable): array
    {
        return NotificationPreference::getEnabledChannels(
            $notifiable->id,
            NotificationPreference::TYPE_REVIEW
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $property = $this->review->property;
        $reviewer = $this->review->user;
        $stars = str_repeat('⭐', $this->review->rating);

        return (new MailMessage)
            ->subject('⭐ New Review - '.$property->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your property has received a new review!')
            ->line('**Property:** '.$property->title)
            ->line('**Guest:** '.$reviewer->name)
            ->line('**Rating:** '.$stars.' ('.$this->review->rating.'/5)')
            ->line('**Review:**')
            ->line('"'.substr($this->review->comment, 0, 200).(strlen($this->review->comment) > 200 ? '...' : '').'"')
            ->action('View & Respond', url('/admin/reviews/'.$this->review->id))
            ->line('Take a moment to respond to this review and thank your guest.')
            ->line('Thank you for being a great host!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_review',
            'review_id' => $this->review->id,
            'property_id' => $this->review->property_id,
            'property_title' => $this->review->property->title,
            'reviewer_name' => $this->review->user->name,
            'rating' => $this->review->rating,
            'comment' => substr($this->review->comment, 0, 100),
            'message' => $this->review->user->name.' left a '.$this->review->rating.'-star review for '.$this->review->property->title,
            'action_url' => '/admin/reviews/'.$this->review->id,
        ];
    }
}
