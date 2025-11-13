<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VerificationCode;
use App\Services\SendGridService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailSmsVerificationController extends Controller
{
    public function __construct(
        protected SendGridService $sendGrid,
        protected TwilioService $twilio
    ) {}

    /**
     * Send email verification code
     */
    public function sendEmailCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Date invalide',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $email = $request->email;

        // Check if email already verified
        if ($user->email_verified_at && $user->email === $email) {
            return response()->json([
                'success' => false,
                'message' => 'Email-ul este deja verificat'
            ], 400);
        }

        // Rate limiting: max 3 codes per 5 minutes
        $recentCodes = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        if ($recentCodes >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Prea multe cereri. Te rugăm să aștepți 5 minute.'
            ], 429);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in database
        VerificationCode::create([
            'user_id' => $user->id,
            'type' => 'email',
            'code' => $code,
            'contact' => $email,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send via SendGrid
        $sent = $this->sendGrid->sendVerificationCode($email, $code, $user->name);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Nu s-a putut trimite email-ul. Te rugăm să încerci din nou.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cod de verificare trimis la ' . $email,
            'data' => [
                'expires_in_minutes' => 15,
            ]
        ]);
    }

    /**
     * Verify email code
     */
    public function verifyEmailCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Cod invalid',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $code = $request->code;

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('code', $code)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Cod invalid sau expirat'
            ], 400);
        }

        if ($verification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Codul a expirat. Te rugăm să soliciti unul nou.'
            ], 400);
        }

        if ($verification->hasExceededAttempts()) {
            return response()->json([
                'success' => false,
                'message' => 'Prea multe încercări. Te rugăm să soliciti un cod nou.'
            ], 400);
        }

        $verification->incrementAttempts();

        if ($verification->code !== $code) {
            return response()->json([
                'success' => false,
                'message' => 'Cod incorect',
                'data' => [
                    'attempts_remaining' => 5 - $verification->attempts
                ]
            ], 400);
        }

        // Mark as verified
        $verification->markAsVerified();
        
        // Update user email verification
        $user->update([
            'email' => $verification->contact,
            'email_verified_at' => now(),
        ]);

        // Send welcome email
        $this->sendGrid->sendWelcomeEmail($user->email, $user->name);

        return response()->json([
            'success' => true,
            'message' => 'Email verificat cu succes!',
            'data' => [
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ]
        ]);
    }

    /**
     * Send SMS verification code
     */
    public function sendSmsCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Date invalide',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $phone = $this->twilio->formatPhoneNumber($request->phone);

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Număr de telefon invalid. Folosește formatul: +40XXXXXXXXX'
            ], 422);
        }

        // Rate limiting
        $recentCodes = VerificationCode::where('user_id', $user->id)
            ->where('type', 'sms')
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        if ($recentCodes >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Prea multe cereri. Te rugăm să aștepți 5 minute.'
            ], 429);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in database
        VerificationCode::create([
            'user_id' => $user->id,
            'type' => 'sms',
            'code' => $code,
            'contact' => $phone,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send via Twilio
        $sent = $this->twilio->sendVerificationCode($phone, $code);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Nu s-a putut trimite SMS-ul. Te rugăm să încerci din nou.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cod de verificare trimis la ' . $phone,
            'data' => [
                'expires_in_minutes' => 15,
            ]
        ]);
    }

    /**
     * Verify SMS code
     */
    public function verifySmsCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Cod invalid',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $code = $request->code;

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'sms')
            ->where('code', $code)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Cod invalid sau expirat'
            ], 400);
        }

        if ($verification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Codul a expirat. Te rugăm să soliciti unul nou.'
            ], 400);
        }

        if ($verification->hasExceededAttempts()) {
            return response()->json([
                'success' => false,
                'message' => 'Prea multe încercări. Te rugăm să soliciti un cod nou.'
            ], 400);
        }

        $verification->incrementAttempts();

        if ($verification->code !== $code) {
            return response()->json([
                'success' => false,
                'message' => 'Cod incorect',
                'data' => [
                    'attempts_remaining' => 5 - $verification->attempts
                ]
            ], 400);
        }

        // Mark as verified
        $verification->markAsVerified();
        
        // Update user phone verification
        $user->update([
            'phone' => $verification->contact,
            'phone_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telefon verificat cu succes!',
            'data' => [
                'phone' => $user->phone,
                'phone_verified_at' => $user->phone_verified_at,
            ]
        ]);
    }
}
