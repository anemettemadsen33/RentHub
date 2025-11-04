<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Str;

class AnonymizationService
{
    /**
     * Anonymize email address
     */
    public function anonymizeEmail(string $email): string
    {
        [$username, $domain] = explode('@', $email);
        $usernameLength = strlen($username);
        
        if ($usernameLength <= 2) {
            $masked = $username[0] . '*';
        } else {
            $visible = min(2, floor($usernameLength / 3));
            $masked = substr($username, 0, $visible) . 
                     str_repeat('*', $usernameLength - $visible);
        }
        
        return $masked . '@' . $domain;
    }

    /**
     * Anonymize phone number
     */
    public function anonymizePhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length < 4) return str_repeat('*', $length);
        
        return str_repeat('*', $length - 4) . substr($phone, -4);
    }

    /**
     * Anonymize name
     */
    public function anonymizeName(string $name): string
    {
        $parts = explode(' ', $name);
        $anonymized = [];
        
        foreach ($parts as $part) {
            if (strlen($part) <= 1) {
                $anonymized[] = $part;
            } else {
                $anonymized[] = $part[0] . str_repeat('*', strlen($part) - 1);
            }
        }
        
        return implode(' ', $anonymized);
    }

    /**
     * Anonymize address
     */
    public function anonymizeAddress(string $address): string
    {
        // Keep only city/country, remove street details
        $parts = array_map('trim', explode(',', $address));
        if (count($parts) > 2) {
            return '[REDACTED], ' . implode(', ', array_slice($parts, -2));
        }
        return '[REDACTED]';
    }

    /**
     * Anonymize IP address
     */
    public function anonymizeIP(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4: Keep first 3 octets
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6: Keep first 4 groups
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 4)) . '::';
        }
        
        return '[REDACTED]';
    }

    /**
     * Fully anonymize user data
     */
    public function anonymizeUser(User $user): bool
    {
        $anonymizedEmail = 'deleted_' . Str::random(16) . '@anonymized.local';
        
        $user->update([
            'name' => 'Deleted User',
            'email' => $anonymizedEmail,
            'phone' => null,
            'address' => null,
            'date_of_birth' => null,
            'profile_picture' => null,
            'bio' => null,
        ]);

        return true;
    }

    /**
     * Generate fake data for testing
     */
    public function generateFakeEmail(): string
    {
        return 'test_' . Str::random(10) . '@test.local';
    }

    /**
     * Pseudonymize user ID
     */
    public function pseudonymizeUserId(int $userId): string
    {
        return hash('sha256', $userId . config('app.key'));
    }

    /**
     * Mask credit card number
     */
    public function maskCreditCard(string $cardNumber): string
    {
        $length = strlen($cardNumber);
        if ($length < 4) return str_repeat('*', $length);
        
        return str_repeat('*', $length - 4) . substr($cardNumber, -4);
    }

    /**
     * Redact sensitive text patterns
     */
    public function redactSensitiveData(string $text): string
    {
        // Email
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL]', $text);
        
        // Phone (US format)
        $text = preg_replace('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', '[PHONE]', $text);
        
        // SSN (US format)
        $text = preg_replace('/\b\d{3}-\d{2}-\d{4}\b/', '[SSN]', $text);
        
        // Credit card
        $text = preg_replace('/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/', '[CARD]', $text);
        
        return $text;
    }
}
