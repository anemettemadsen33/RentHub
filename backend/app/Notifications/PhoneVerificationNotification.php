<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Twilio\Rest\Client as TwilioClient;

class PhoneVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $code;

    /**
     * Create a new notification instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Only send SMS if Twilio is configured
        if (config('services.twilio.sid')) {
            return ['twilio'];
        }
        
        return ['mail']; // Fallback to email
    }

    /**
     * Send the notification via Twilio SMS.
     */
    public function toTwilio($notifiable)
    {
        $twilioSid = config('services.twilio.sid');
        $twilioToken = config('services.twilio.token');
        $twilioFrom = config('services.twilio.from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
            throw new \Exception('Twilio credentials not configured');
        }

        $twilio = new TwilioClient($twilioSid, $twilioToken);

        $message = "Your RentHub verification code is: {$this->code}. This code expires in 10 minutes.";

        return $twilio->messages->create(
            $notifiable->phone,
            [
                'from' => $twilioFrom,
                'body' => $message,
            ]
        );
    }

    /**
     * Fallback to email if Twilio is not configured.
     */
    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Phone Verification Code - RentHub')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your phone verification code is: **' . $this->code . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this code, please ignore this email.')
            ->salutation('Best regards, The RentHub Team');
    }
}
