<?php

namespace App\Services\Security;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class DataEncryptionService
{
    /**
     * Encrypt sensitive data
     */
    public function encrypt(mixed $data): string
    {
        return Crypt::encryptString(json_encode($data));
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt(string $encrypted): mixed
    {
        try {
            return json_decode(Crypt::decryptString($encrypted), true);
        } catch (DecryptException $e) {
            return null;
        }
    }

    /**
     * Hash sensitive data (one-way)
     */
    public function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Anonymize PII data
     */
    public function anonymizePII(array $data): array
    {
        $piiFields = ['email', 'phone', 'ssn', 'passport', 'credit_card'];

        foreach ($piiFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->maskData($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Mask sensitive data
     */
    protected function maskData(string $data): string
    {
        $length = strlen($data);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($data, 0, 2).str_repeat('*', $length - 4).substr($data, -2);
    }

    /**
     * Tokenize sensitive data
     */
    public function tokenize(string $data): string
    {
        $token = bin2hex(random_bytes(16));
        cache()->put("token:{$token}", $this->encrypt($data), now()->addHours(24));

        return $token;
    }

    /**
     * Detokenize data
     */
    public function detokenize(string $token): ?string
    {
        $encrypted = cache()->get("token:{$token}");

        return $encrypted ? $this->decrypt($encrypted) : null;
    }
}
