<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InputValidationService
{
    /**
     * Sanitize string input
     */
    public function sanitizeString(string $input, bool $stripTags = true): string
    {
        $sanitized = trim($input);
        
        if ($stripTags) {
            $sanitized = strip_tags($sanitized);
        }
        
        // Remove null bytes
        $sanitized = str_replace(chr(0), '', $sanitized);
        
        // Normalize whitespace
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        return $sanitized;
    }

    /**
     * Sanitize HTML input with allowed tags
     */
    public function sanitizeHtml(string $input, array $allowedTags = []): string
    {
        if (empty($allowedTags)) {
            $allowedTags = ['p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li'];
        }
        
        $allowedTagsString = '<' . implode('><', $allowedTags) . '>';
        return strip_tags($input, $allowedTagsString);
    }

    /**
     * Validate and sanitize email
     */
    public function sanitizeEmail(string $email): ?string
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower($email);
        }
        
        return null;
    }

    /**
     * Validate and sanitize URL
     */
    public function sanitizeUrl(string $url): ?string
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        return null;
    }

    /**
     * Sanitize filename
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove path information
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Limit length
        $filename = Str::limit($filename, 100, '');
        
        return $filename;
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload($file, array $options = []): array
    {
        $errors = [];
        $config = config('security.file_upload');
        
        $maxSize = $options['max_size'] ?? $config['max_size'];
        $allowedExtensions = $options['allowed_extensions'] ?? $config['allowed_extensions'];
        $forbiddenExtensions = $config['forbidden_extensions'];
        
        // Check if file exists
        if (!$file || !$file->isValid()) {
            $errors[] = 'Invalid file upload';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, $forbiddenExtensions)) {
            $errors[] = 'File type is forbidden';
        }
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'File type is not allowed';
        }
        
        // Validate MIME type
        if ($config['validate_mime_type']) {
            $mimeType = $file->getMimeType();
            $allowedMimes = $this->getAllowedMimeTypes($allowedExtensions);
            
            if (!in_array($mimeType, $allowedMimes)) {
                $errors[] = 'File MIME type is not allowed';
            }
        }
        
        // Check for double extensions
        if (preg_match('/\.[^.]+\.[^.]+$/', $file->getClientOriginalName())) {
            $errors[] = 'Double file extensions are not allowed';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sanitized_name' => $this->sanitizeFilename($file->getClientOriginalName()),
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Get allowed MIME types for extensions
     */
    private function getAllowedMimeTypes(array $extensions): array
    {
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
        ];
        
        $allowedMimes = [];
        foreach ($extensions as $ext) {
            if (isset($mimeMap[$ext])) {
                $allowedMimes[] = $mimeMap[$ext];
            }
        }
        
        return $allowedMimes;
    }

    /**
     * Prevent SQL injection in raw queries
     */
    public function validateSqlInput(string $input): bool
    {
        $dangerousPatterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(--|#|\/\*|\*\/)/i',
            '/(\bOR\b.*=.*)/i',
            '/(\bAND\b.*=.*)/i',
            '/(\'|\"|\`|;)/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Prevent XSS attacks
     */
    public function preventXss(string $input): string
    {
        // Convert special characters to HTML entities
        $output = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove any JavaScript protocol handlers
        $output = preg_replace('/javascript:/i', '', $output);
        $output = preg_replace('/on\w+=/i', '', $output);
        
        return $output;
    }

    /**
     * Validate JSON input
     */
    public function validateJson(string $input): array
    {
        try {
            $decoded = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
            
            return [
                'valid' => true,
                'data' => $decoded,
            ];
        } catch (\JsonException $e) {
            return [
                'valid' => false,
                'error' => 'Invalid JSON format',
            ];
        }
    }

    /**
     * Validate IP address
     */
    public function validateIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if input contains malicious patterns
     */
    public function containsMaliciousContent(string $input): bool
    {
        $maliciousPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/<object\b[^>]*>(.*?)<\/object>/is',
            '/<embed\b[^>]*>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/eval\s*\(/i',
            '/expression\s*\(/i',
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Sanitize array recursively
     */
    public function sanitizeArray(array $data, bool $stripTags = true): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value, $stripTags);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value, $stripTags);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Validate phone number
     */
    public function validatePhoneNumber(string $phone): ?string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        
        // Basic validation (adjust based on your requirements)
        if (preg_match('/^\+?[1-9]\d{6,14}$/', $cleaned)) {
            return $cleaned;
        }
        
        return null;
    }

    /**
     * Validate credit card number (basic Luhn algorithm)
     */
    public function validateCreditCard(string $number): bool
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }
        
        // Luhn algorithm
        $sum = 0;
        $length = strlen($number);
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            
            if (($length - $i) % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        return $sum % 10 === 0;
    }
}
