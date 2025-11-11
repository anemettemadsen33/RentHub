# SMS Integration Guide for RentHub

## Overview
This guide covers setting up SMS functionality for phone verification and 2FA codes using Twilio or Vonage (formerly Nexmo).

## Current Implementation Status

### ✅ Completed
- Phone verification code generation and storage
- 2FA code generation and validation
- Database schema for phone verification
- Email-based 2FA (fully implemented)

### ⚠️ Pending
- SMS delivery via Twilio/Vonage
- Phone number validation and formatting
- SMS template configuration

## Setup Options

### Option 1: Twilio (Recommended)

#### Installation
```bash
composer require twilio/sdk
```

#### Configuration
Add to `.env`:
```env
TWILIO_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_PHONE_NUMBER=your_twilio_phone_number
```

Add to `config/services.php`:
```php
'twilio' => [
    'sid' => env('TWILIO_SID'),
    'token' => env('TWILIO_AUTH_TOKEN'),
    'from' => env('TWILIO_PHONE_NUMBER'),
],
```

#### Implementation

Create `app/Services/SmsService.php`:
```php
<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->from = config('services.twilio.from');
    }

    public function send(string $to, string $message): bool
    {
        try {
            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);

            Log::info("SMS sent to {$to}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS: {$e->getMessage()}");
            return false;
        }
    }

    public function sendVerificationCode(string $to, string $code): bool
    {
        $message = "Your RentHub verification code is: {$code}\n\nThis code expires in 10 minutes.\n\nIf you didn't request this, please ignore.";
        return $this->send($to, $message);
    }

    public function send2FACode(string $to, string $code): bool
    {
        $message = "Your RentHub 2FA code is: {$code}\n\nExpires in 10 minutes.";
        return $this->send($to, $message);
    }
}
```

#### Update AuthController

Replace line 226 in `app/Http/Controllers/Api/AuthController.php`:
```php
// Current (line 220-227):
$user->update([
    'phone' => $request->phone,
    'phone_verification_code' => $code,
    'phone_verification_code_expires_at' => now()->addMinutes(10),
]);

// TODO: Send SMS with code using Twilio/Vonage

// New implementation:
$user->update([
    'phone' => $request->phone,
    'phone_verification_code' => $code,
    'phone_verification_code_expires_at' => now()->addMinutes(10),
]);

// Send SMS via Twilio
$smsService = app(SmsService::class);
$smsService->sendVerificationCode($request->phone, $code);
```

### Option 2: Vonage (Nexmo)

#### Installation
```bash
composer require vonage/client
```

#### Configuration
Add to `.env`:
```env
VONAGE_API_KEY=your_api_key
VONAGE_API_SECRET=your_api_secret
VONAGE_SMS_FROM=RentHub
```

Add to `config/services.php`:
```php
'vonage' => [
    'key' => env('VONAGE_API_KEY'),
    'secret' => env('VONAGE_API_SECRET'),
    'sms_from' => env('VONAGE_SMS_FROM', 'RentHub'),
],
```

#### Implementation

Create `app/Services/SmsService.php`:
```php
<?php

namespace App\Services;

use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $basic = new Basic(
            config('services.vonage.key'),
            config('services.vonage.secret')
        );
        $this->client = new Client($basic);
        $this->from = config('services.vonage.sms_from');
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = $this->client->sms()->send(
                new SMS($to, $this->from, $message)
            );

            $message = $response->current();
            if ($message->getStatus() == 0) {
                Log::info("SMS sent to {$to}");
                return true;
            }

            Log::error("SMS failed: " . $message->getStatus());
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS: {$e->getMessage()}");
            return false;
        }
    }

    public function sendVerificationCode(string $to, string $code): bool
    {
        $message = "Your RentHub verification code is: {$code}. Expires in 10 minutes.";
        return $this->send($to, $message);
    }

    public function send2FACode(string $to, string $code): bool
    {
        $message = "Your RentHub 2FA code is: {$code}. Expires in 10 minutes.";
        return $this->send($to, $message);
    }
}
```

## Testing

### Local Testing with Mailtrap (Recommended for Development)

For local development without incurring SMS costs, use Mailtrap's SMS testing:

1. Sign up at https://mailtrap.io/
2. Navigate to SMS API section
3. Get your API token
4. Configure in `.env`:
```env
MAILTRAP_API_TOKEN=your_token
SMS_DRIVER=mailtrap  # or 'twilio', 'vonage'
```

### Test SMS Sending

Create a test route in `routes/api.php`:
```php
Route::get('/test-sms', function () {
    $smsService = app(\App\Services\SmsService::class);
    $result = $smsService->send('+1234567890', 'Test message from RentHub');
    return response()->json(['sent' => $result]);
})->middleware('auth:sanctum');
```

## Phone Number Formatting

Install libphonenumber for proper phone validation:
```bash
composer require propaganistas/laravel-phone
```

Update validation rules:
```php
'phone' => ['required', 'phone:AUTO'],
```

## Code Updates Required

### Files to Update

1. **app/Http/Controllers/Api/AuthController.php** (Line 226)
   - Replace TODO with SmsService call

2. **app/Http/Controllers/Api/UserVerificationController.php** (Line 132)
   - Replace TODO with SmsService call

3. **Create new file: app/Services/SmsService.php**
   - Implement chosen SMS provider

4. **config/services.php**
   - Add SMS provider configuration

5. **.env**
   - Add SMS credentials

## Security Considerations

1. **Rate Limiting**: Implement rate limits for SMS sending to prevent abuse
```php
Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
});
```

2. **Phone Number Validation**: Always validate and sanitize phone numbers

3. **Cost Control**: Set up billing alerts in Twilio/Vonage dashboard

4. **Logging**: Log all SMS attempts for audit purposes

5. **Retry Logic**: Implement exponential backoff for failed sends

## Cost Estimates

- **Twilio**: ~$0.0075 per SMS (US)
- **Vonage**: ~$0.0057 per SMS (US)
- International rates vary by country

## Testing Checklist

- [ ] SMS service configured in .env
- [ ] Test SMS sending to your own number
- [ ] Verify code generation works
- [ ] Test code validation
- [ ] Test code expiration (10 minutes)
- [ ] Test invalid code handling
- [ ] Test rate limiting
- [ ] Check logs for errors
- [ ] Verify database updates
- [ ] Test with international numbers

## Production Deployment

Before going live:

1. Purchase a dedicated phone number from Twilio/Vonage
2. Set up proper error monitoring
3. Configure rate limits
4. Set up billing alerts
5. Test in staging environment first
6. Have fallback to email if SMS fails

## Support

- Twilio Documentation: https://www.twilio.com/docs/sms
- Vonage Documentation: https://developer.vonage.com/messaging/sms/overview
- Laravel Phone Package: https://github.com/Propaganistas/Laravel-Phone
