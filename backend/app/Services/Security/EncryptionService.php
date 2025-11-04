<?php

namespace App\Services\Security;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class EncryptionService
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
            $decrypted = Crypt::decryptString($encrypted);

            return json_decode($decrypted, true);
        } catch (DecryptException $e) {
            throw new \Exception('Failed to decrypt data: '.$e->getMessage());
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
     * Encrypt file
     */
    public function encryptFile(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        $encrypted = Crypt::encryptString($content);

        return file_put_contents($filePath.'.enc', $encrypted) !== false;
    }

    /**
     * Decrypt file
     */
    public function decryptFile(string $encryptedPath, string $outputPath): bool
    {
        try {
            $encrypted = file_get_contents($encryptedPath);
            $decrypted = Crypt::decryptString($encrypted);

            return file_put_contents($outputPath, $decrypted) !== false;
        } catch (DecryptException $e) {
            return false;
        }
    }

    /**
     * Generate secure random key
     */
    public function generateKey(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
