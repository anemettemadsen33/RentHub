<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    private string $algorithm = 'aes-256-gcm';

    /**
     * Encrypt data at rest
     */
    public function encryptAtRest($data): string
    {
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }

        return Crypt::encryptString($data);
    }

    /**
     * Decrypt data at rest
     */
    public function decryptAtRest(string $encrypted)
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);

            $jsonDecoded = json_decode($decrypted, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonDecoded;
            }

            return $decrypted;
        } catch (DecryptException $e) {
            \Log::error('Decryption failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Encrypt field
     */
    public function encryptField($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return base64_encode(openssl_encrypt(
            $value,
            $this->algorithm,
            $this->getEncryptionKey(),
            0,
            $this->getIV(),
            $tag
        ).'::'.base64_encode($tag));
    }

    /**
     * Decrypt field
     */
    public function decryptField(?string $encrypted): ?string
    {
        if ($encrypted === null) {
            return null;
        }

        try {
            [$data, $tag] = explode('::', base64_decode($encrypted), 2);

            return openssl_decrypt(
                $data,
                $this->algorithm,
                $this->getEncryptionKey(),
                0,
                $this->getIV(),
                base64_decode($tag)
            );
        } catch (\Exception $e) {
            \Log::error('Field decryption failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Hash sensitive data
     */
    public function hashSensitiveData(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Encrypt file
     */
    public function encryptFile(string $sourcePath, string $destPath): bool
    {
        try {
            $data = file_get_contents($sourcePath);
            $encrypted = $this->encryptAtRest($data);

            return file_put_contents($destPath, $encrypted) !== false;
        } catch (\Exception $e) {
            \Log::error('File encryption failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Decrypt file
     */
    public function decryptFile(string $sourcePath, string $destPath): bool
    {
        try {
            $encrypted = file_get_contents($sourcePath);
            $decrypted = $this->decryptAtRest($encrypted);

            return file_put_contents($destPath, $decrypted) !== false;
        } catch (\Exception $e) {
            \Log::error('File decryption failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get encryption key
     */
    private function getEncryptionKey(): string
    {
        return hash('sha256', config('app.key'), true);
    }

    /**
     * Get initialization vector
     */
    private function getIV(): string
    {
        return substr(hash('sha256', config('app.key')), 0, 16);
    }

    /**
     * Rotate encryption key
     */
    public function rotateKey(string $oldKey, string $newKey): void
    {
        config(['app.key' => $oldKey]);

        // Re-encrypt all sensitive data
        // This should be implemented based on your data model

        config(['app.key' => $newKey]);
    }
}
