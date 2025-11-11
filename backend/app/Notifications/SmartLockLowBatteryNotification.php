<?php

namespace App\Notifications;

use App\Models\SmartLock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SmartLockLowBatteryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SmartLock $lock
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $property = $this->lock->property;
        $batteryLevel = $this->lock->battery_level;

        return (new MailMessage)
            ->subject('⚠️ Low Battery Alert - Smart Lock Requires Attention')
            ->greeting('Hello '.$notifiable->name)
            ->line('Your smart lock battery is running low and requires replacement soon.')
            ->line("**Property:** {$property->title}")
            ->line("**Lock Name:** {$this->lock->name}")
            ->line("**Battery Level:** {$batteryLevel}%")
            ->line("**Device ID:** {$this->lock->device_id}")
            ->line('**Action Required:** Please replace the batteries as soon as possible to avoid lockouts.')
            ->action('View Property Details', url("/properties/{$property->id}"))
            ->line('We recommend keeping spare batteries on hand for emergencies.')
            ->salutation('RentHub Smart Lock Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'smart_lock_low_battery',
            'lock_id' => $this->lock->id,
            'property_id' => $this->lock->property_id,
            'battery_level' => $this->lock->battery_level,
            'message' => "Low battery alert for {$this->lock->name} at {$this->lock->property->title}",
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'smart_lock_low_battery',
            'lock_id' => $this->lock->id,
            'property_id' => $this->lock->property_id,
            'battery_level' => $this->lock->battery_level,
        ];
    }
}
