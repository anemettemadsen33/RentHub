<?php

namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    protected SendGrid $client;

    public function __construct()
    {
        $this->client = new SendGrid(config('services.sendgrid.api_key'));
    }

    /**
     * Send verification code email
     */
    public function sendVerificationCode(string $email, string $code, string $userName = null): bool
    {
        try {
            $mail = new Mail();
            $mail->setFrom(
                config('services.sendgrid.from_email'),
                config('services.sendgrid.from_name', 'RentHub')
            );
            $mail->setSubject('Verifică-ți adresa de email - RentHub');
            $mail->addTo($email, $userName);

            $htmlContent = view('emails.verification-code', [
                'code' => $code,
                'userName' => $userName,
                'expiresInMinutes' => 15,
            ])->render();

            $mail->addContent("text/html", $htmlContent);

            $response = $this->client->send($mail);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                Log::info('Verification email sent successfully', [
                    'email' => $email,
                    'status_code' => $response->statusCode(),
                ]);
                return true;
            }

            Log::error('SendGrid API error', [
                'email' => $email,
                'status_code' => $response->statusCode(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send welcome email after verification
     */
    public function sendWelcomeEmail(string $email, string $userName): bool
    {
        try {
            $mail = new Mail();
            $mail->setFrom(
                config('services.sendgrid.from_email'),
                config('services.sendgrid.from_name', 'RentHub')
            );
            $mail->setSubject('Bine ai venit la RentHub! 🎉');
            $mail->addTo($email, $userName);

            $htmlContent = view('emails.welcome', [
                'userName' => $userName,
            ])->render();

            $mail->addContent("text/html", $htmlContent);

            $response = $this->client->send($mail);

            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
