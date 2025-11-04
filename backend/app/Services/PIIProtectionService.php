<?php

namespace App\Services;

class PIIProtectionService
{
    private array $piiFields;
    
    public function __construct()
    {
        $this->piiFields = config('security.data_protection.pii_fields', []);
    }
    
    /**
     * Anonymize PII data
     */
    public function anonymize($data, string $method = 'hash'): mixed
    {
        if (is_array($data)) {
            return $this->anonymizeArray($data, $method);
        }
        
        if (is_object($data)) {
            return $this->anonymizeObject($data, $method);
        }
        
        return $data;
    }
    
    /**
     * Anonymize array
     */
    private function anonymizeArray(array $data, string $method): array
    {
        $anonymized = [];
        
        foreach ($data as $key => $value) {
            if ($this->isPIIField($key)) {
                $anonymized[$key] = $this->applyAnonymization($value, $method);
            } else {
                $anonymized[$key] = is_array($value) 
                    ? $this->anonymizeArray($value, $method)
                    : $value;
            }
        }
        
        return $anonymized;
    }
    
    /**
     * Anonymize object
     */
    private function anonymizeObject(object $data, string $method): object
    {
        $clone = clone $data;
        
        foreach (get_object_vars($clone) as $key => $value) {
            if ($this->isPIIField($key)) {
                $clone->$key = $this->applyAnonymization($value, $method);
            }
        }
        
        return $clone;
    }
    
    /**
     * Apply anonymization method
     */
    private function applyAnonymization($value, string $method)
    {
        if ($value === null) {
            return null;
        }
        
        return match($method) {
            'hash' => $this->hashValue($value),
            'mask' => $this->maskValue($value),
            'redact' => $this->redactValue($value),
            'pseudonymize' => $this->pseudonymizeValue($value),
            default => $value,
        };
    }
    
    /**
     * Hash value
     */
    private function hashValue($value): string
    {
        return hash('sha256', $value);
    }
    
    /**
     * Mask value
     */
    private function maskValue($value): string
    {
        $value = (string) $value;
        $length = strlen($value);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        // Show first 2 and last 2 characters
        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }
    
    /**
     * Redact value
     */
    private function redactValue($value): string
    {
        return '[REDACTED]';
    }
    
    /**
     * Pseudonymize value
     */
    private function pseudonymizeValue($value): string
    {
        return 'pseudo_' . substr(hash('sha256', $value), 0, 16);
    }
    
    /**
     * Check if field is PII
     */
    private function isPIIField(string $field): bool
    {
        return in_array($field, $this->piiFields);
    }
    
    /**
     * Mask email
     */
    public function maskEmail(string $email): string
    {
        [$username, $domain] = explode('@', $email);
        $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
        return $maskedUsername . '@' . $domain;
    }
    
    /**
     * Mask phone number
     */
    public function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        $length = strlen($digits);
        
        if ($length < 4) {
            return str_repeat('*', $length);
        }
        
        return str_repeat('*', $length - 4) . substr($digits, -4);
    }
    
    /**
     * Mask credit card
     */
    public function maskCreditCard(string $cardNumber): string
    {
        $digits = preg_replace('/\D/', '', $cardNumber);
        return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }
    
    /**
     * Validate PII access
     */
    public function canAccessPII($user, string $field): bool
    {
        // Implement your access control logic
        return $user->hasPermissionTo('access.pii') || 
               $user->hasPermissionTo("access.pii.{$field}");
    }
    
    /**
     * Log PII access
     */
    public function logPIIAccess($user, string $field, string $action): void
    {
        \Log::channel('audit')->info('PII access', [
            'user_id' => $user->id,
            'field' => $field,
            'action' => $action,
            'ip' => request()->ip(),
            'timestamp' => now(),
        ]);
    }
}
