<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Send SMS verification code
     */
    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        try {
            $message = $this->client->messages->create(
                $phoneNumber,
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Codul tău de verificare RentHub este: {$code}\n\nCodul expiră în 15 minute."
                ]
            );

            if ($message->sid) {
                Log::info('SMS verification code sent successfully', [
                    'phone' => $phoneNumber,
                    'sid' => $message->sid,
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS verification code', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify phone number format
     */
    public function formatPhoneNumber(string $phone): ?string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Add + if not present and ensure it's in E.164 format
        if (strlen($cleaned) === 10) {
            // Assume Romania (+40) if 10 digits
            return '+40' . $cleaned;
        } elseif (strlen($cleaned) > 10 && !str_starts_with($cleaned, '+')) {
            return '+' . $cleaned;
        } elseif (str_starts_with($phone, '+')) {
            return $phone;
        }

        return null;
    }
}
