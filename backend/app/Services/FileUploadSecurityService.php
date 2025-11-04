<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileUploadSecurityService
{
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    protected array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx',
    ];

    protected int $maxFileSize = 10485760; // 10MB

    /**
     * Validate and secure file upload
     */
    public function validateAndSecure(UploadedFile $file): array
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum allowed size of 10MB');
        }

        // Check MIME type
        if (! in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \Exception('Invalid file type');
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $this->allowedExtensions)) {
            throw new \Exception('Invalid file extension');
        }

        // Scan for malware (basic check)
        if ($this->containsMaliciousContent($file)) {
            throw new \Exception('File contains suspicious content');
        }

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);

        return [
            'original_name' => $file->getClientOriginalName(),
            'secure_name' => $filename,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $extension,
        ];
    }

    /**
     * Generate secure random filename
     */
    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        return Str::random(40).'.'.$extension;
    }

    /**
     * Basic malware detection
     */
    protected function containsMaliciousContent(UploadedFile $file): bool
    {
        $content = file_get_contents($file->getRealPath());

        $suspiciousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/onclick=/i',
            '/onerror=/i',
            '/<iframe/i',
            '/eval\(/i',
            '/base64_decode/i',
            '/exec\(/i',
            '/system\(/i',
            '/shell_exec/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store file securely
     */
    public function storeSecurely(UploadedFile $file, string $directory = 'uploads'): string
    {
        $validation = $this->validateAndSecure($file);

        $path = $file->storeAs(
            $directory.'/'.date('Y/m'),
            $validation['secure_name'],
            'private'
        );

        return $path;
    }
}
