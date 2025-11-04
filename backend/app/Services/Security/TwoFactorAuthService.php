<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\TwoFactorAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable 2FA for user
     */
    public function enable(User $user, string $method = 'totp'): array
    {
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactor) {
            $twoFactor = new TwoFactorAuth();
            $twoFactor->user_id = $user->id;
        }

        $result = [];

        switch ($method) {
            case 'totp':
                $secret = $this->google2fa->generateSecretKey();
                $twoFactor->secret = encrypt($secret);
                $twoFactor->method = 'totp';

                $qrCodeUrl = $this->google2fa->getQRCodeUrl(
                    config('app.name'),
                    $user->email,
                    $secret
                );

                $result = [
                    'secret' => $secret,
                    'qr_code_url' => $qrCodeUrl,
                    'backup_codes' => $this->generateBackupCodes($user),
                ];
                break;

            case 'sms':
                $twoFactor->method = 'sms';
                $twoFactor->phone_number = $user->phone;
                $result = [
                    'phone' => $this->maskPhoneNumber($user->phone),
                ];
                break;

            case 'email':
                $twoFactor->method = 'email';
                $result = [
                    'email' => $this->maskEmail($user->email),
                ];
                break;
        }

        $twoFactor->save();

        return $result;
    }

    /**
     * Verify 2FA code
     */
    public function verify(User $user, string $code): bool
    {
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)
            ->where('enabled', true)
            ->first();

        if (!$twoFactor) {
            return false;
        }

        switch ($twoFactor->method) {
            case 'totp':
                return $this->verifyTotp($twoFactor, $code);

            case 'sms':
            case 'email':
                return $this->verifyCode($user, $code);

            default:
                return false;
        }
    }

    /**
     * Verify TOTP code
     */
    protected function verifyTotp(TwoFactorAuth $twoFactor, string $code): bool
    {
        $secret = decrypt($twoFactor->secret);
        
        $valid = $this->google2fa->verifyKey($secret, $code);

        if ($valid) {
            $twoFactor->update([
                'last_used_at' => now(),
            ]);
        }

        return $valid;
    }

    /**
     * Verify SMS/Email code
     */
    protected function verifyCode(User $user, string $code): bool
    {
        $storedCode = Cache::get('2fa_code:' . $user->id);

        if (!$storedCode) {
            return false;
        }

        $valid = hash_equals($storedCode, $code);

        if ($valid) {
            Cache::forget('2fa_code:' . $user->id);
            
            TwoFactorAuth::where('user_id', $user->id)->update([
                'last_used_at' => now(),
            ]);
        }

        return $valid;
    }

    /**
     * Send 2FA code via SMS or Email
     */
    public function sendCode(User $user): bool
    {
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)
            ->where('enabled', true)
            ->first();

        if (!$twoFactor) {
            return false;
        }

        $code = $this->generateCode();
        Cache::put('2fa_code:' . $user->id, $code, now()->addMinutes(10));

        switch ($twoFactor->method) {
            case 'sms':
                return $this->sendSmsCode($user, $code);

            case 'email':
                return $this->sendEmailCode($user, $code);

            default:
                return false;
        }
    }

    /**
     * Generate 6-digit code
     */
    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send SMS code
     */
    protected function sendSmsCode(User $user, string $code): bool
    {
        // Integration with SMS provider (Twilio, etc.)
        // For now, just log it
        \Log::info("2FA SMS Code for {$user->email}: {$code}");
        return true;
    }

    /**
     * Send email code
     */
    protected function sendEmailCode(User $user, string $code): bool
    {
        try {
            Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($code));
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send 2FA email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes(User $user): array
    {
        $codes = [];
        $count = config('security.two_factor.backup_codes_count', 10);

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::random(8) . '-' . Str::random(8);
        }

        // Store hashed backup codes
        $hashedCodes = array_map(fn($code) => hash('sha256', $code), $codes);

        TwoFactorAuth::where('user_id', $user->id)->update([
            'backup_codes' => json_encode($hashedCodes),
        ]);

        return $codes;
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactor || !$twoFactor->backup_codes) {
            return false;
        }

        $backupCodes = json_decode($twoFactor->backup_codes, true);
        $hashedCode = hash('sha256', $code);

        if (in_array($hashedCode, $backupCodes)) {
            // Remove used backup code
            $backupCodes = array_diff($backupCodes, [$hashedCode]);
            $twoFactor->update([
                'backup_codes' => json_encode(array_values($backupCodes)),
                'last_used_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Disable 2FA
     */
    public function disable(User $user): bool
    {
        return TwoFactorAuth::where('user_id', $user->id)->update([
            'enabled' => false,
        ]) > 0;
    }

    /**
     * Check if 2FA is enabled
     */
    public function isEnabled(User $user): bool
    {
        return TwoFactorAuth::where('user_id', $user->id)
            ->where('enabled', true)
            ->exists();
    }

    /**
     * Check if 2FA is enforced for user
     */
    public function isEnforced(User $user): bool
    {
        if (!config('security.two_factor.enabled', false)) {
            return false;
        }

        $enforcedRoles = config('security.two_factor.enforced_for_roles', []);
        return in_array($user->role, $enforcedRoles);
    }

    /**
     * Mask phone number
     */
    protected function maskPhoneNumber(string $phone): string
    {
        if (strlen($phone) < 4) {
            return $phone;
        }

        return str_repeat('*', strlen($phone) - 4) . substr($phone, -4);
    }

    /**
     * Mask email
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        $maskedUsername = substr($username, 0, 2) . str_repeat('*', max(0, strlen($username) - 2));

        return $maskedUsername . '@' . $domain;
    }
}
