<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

class DataEncryptionService
{
    /**
     * Encrypt sensitive data at rest
     */
    public function encryptData(mixed $data): ?string
    {
        try {
            return Crypt::encryptString(json_encode($data));
        } catch (\Exception $e) {
            Log::error('Data encryption failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Decrypt sensitive data
     */
    public function decryptData(string $encryptedData): mixed
    {
        try {
            return json_decode(Crypt::decryptString($encryptedData), true);
        } catch (DecryptException $e) {
            Log::error('Data decryption failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Encrypt PII (Personally Identifiable Information)
     */
    public function encryptPII(array $data, array $fieldsToEncrypt): array
    {
        foreach ($fieldsToEncrypt as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->encryptData($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Decrypt PII
     */
    public function decryptPII(array $data, array $fieldsToDecrypt): array
    {
        foreach ($fieldsToDecrypt as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->decryptData($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Anonymize PII for GDPR compliance
     */
    public function anonymizePII(array $data): array
    {
        $anonymized = $data;
        
        // Anonymize email
        if (isset($anonymized['email'])) {
            $anonymized['email'] = $this->anonymizeEmail($anonymized['email']);
        }
        
        // Anonymize phone
        if (isset($anonymized['phone'])) {
            $anonymized['phone'] = $this->anonymizePhone($anonymized['phone']);
        }
        
        // Anonymize name
        if (isset($anonymized['name'])) {
            $anonymized['name'] = 'Anonymous User';
        }
        
        // Anonymize address
        if (isset($anonymized['address'])) {
            $anonymized['address'] = '[REDACTED]';
        }
        
        return $anonymized;
    }

    /**
     * Anonymize email address
     */
    private function anonymizeEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '[REDACTED]@example.com';
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        $anonymizedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
        
        return $anonymizedUsername . '@' . $domain;
    }

    /**
     * Anonymize phone number
     */
    private function anonymizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleaned) < 4) {
            return str_repeat('*', strlen($cleaned));
        }
        
        $lastFour = substr($cleaned, -4);
        $masked = str_repeat('*', strlen($cleaned) - 4) . $lastFour;
        
        return $masked;
    }

    /**
     * Hash sensitive data (one-way)
     */
    public function hashSensitiveData(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Tokenize credit card number (PCI DSS compliance)
     */
    public function tokenizeCreditCard(string $cardNumber): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $cardNumber);
        
        if (strlen($cleaned) < 4) {
            return str_repeat('*', strlen($cleaned));
        }
        
        $lastFour = substr($cleaned, -4);
        return str_repeat('*', strlen($cleaned) - 4) . $lastFour;
    }
}
