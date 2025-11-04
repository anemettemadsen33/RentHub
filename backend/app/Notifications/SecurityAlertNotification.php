<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use App\Models\SecurityIncident;

class SecurityAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SecurityIncident $incident
    ) {}

    public function via(object $notifiable): array
    {
        $channels = config('security.monitoring.alert_channels', ['mail']);
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $severity = strtoupper($this->incident->severity);
        $color = match($this->incident->severity) {
            'critical' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#ffc107',
            default => '#0dcaf0',
        };

        return (new MailMessage)
            ->subject("[{$severity}] Security Alert: {$this->incident->type}")
            ->greeting("Security Alert Detected")
            ->line("A security incident has been detected and requires your attention.")
            ->line("**Severity:** {$severity}")
            ->line("**Type:** {$this->incident->type}")
            ->line("**Description:** {$this->incident->description}")
            ->line("**Detected at:** {$this->incident->detected_at->format('Y-m-d H:i:s')}")
            ->action('View Incident Details', url("/admin/security/incidents/{$this->incident->id}"))
            ->line('Please investigate this incident as soon as possible.')
            ->salutation('RentHub Security Team');
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $severity = strtoupper($this->incident->severity);
        
        $emoji = match($this->incident->severity) {
            'critical' => '🚨',
            'high' => '⚠️',
            'medium' => '⚡',
            default => 'ℹ️',
        };

        return (new SlackMessage)
            ->error()
            ->content("{$emoji} *[{$severity}] Security Alert*")
            ->attachment(function ($attachment) {
                $attachment->title($this->incident->type)
                    ->fields([
                        'Severity' => strtoupper($this->incident->severity),
                        'Description' => $this->incident->description,
                        'IP Address' => $this->incident->ip_address ?? 'N/A',
                        'Detected At' => $this->incident->detected_at->format('Y-m-d H:i:s'),
                    ])
                    ->action('View Details', url("/admin/security/incidents/{$this->incident->id}"));
            });
    }

    public function toArray(object $notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'type' => $this->incident->type,
            'severity' => $this->incident->severity,
            'description' => $this->incident->description,
            'detected_at' => $this->incident->detected_at,
        ];
    }
}
