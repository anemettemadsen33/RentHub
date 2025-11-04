<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FileUploadSecurityMiddleware
{
    private array $config;

    public function __construct()
    {
        $this->config = config('security.file_upload');
    }

    public function handle(Request $request, Closure $next)
    {
        if ($request->hasFile('file') || $request->hasFile('files')) {
            $this->validateFileUploads($request);
        }

        return $next($request);
    }

    private function validateFileUploads(Request $request): void
    {
        $files = $request->allFiles();

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $this->validateSingleFile($singleFile);
                }
            } else {
                $this->validateSingleFile($file);
            }
        }
    }

    private function validateSingleFile($file): void
    {
        // Check file size
        if ($file->getSize() > $this->config['max_size']) {
            abort(413, 'File size exceeds maximum allowed size');
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $this->config['forbidden_extensions'])) {
            abort(400, 'File type not allowed');
        }

        if (! in_array($extension, $this->config['allowed_extensions'])) {
            abort(400, 'File type not allowed');
        }

        // Validate MIME type
        if ($this->config['validate_mime_type']) {
            $this->validateMimeType($file);
        }

        // Scan for viruses if enabled
        if ($this->config['scan_for_viruses']) {
            $this->scanForViruses($file);
        }

        // Check for embedded PHP code
        $this->checkForEmbeddedCode($file);
    }

    private function validateMimeType($file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        $validMimeTypes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
        ];

        if (isset($validMimeTypes[$extension])) {
            if (! in_array($mimeType, $validMimeTypes[$extension])) {
                abort(400, 'File MIME type does not match extension');
            }
        }
    }

    private function scanForViruses($file): void
    {
        // Basic implementation - in production, use ClamAV or similar
        $tempPath = $file->getRealPath();

        // Check file header for common malware signatures
        $handle = fopen($tempPath, 'rb');
        $header = fread($handle, 1024);
        fclose($handle);

        // Check for PHP tags in images
        if (preg_match('/<\?php/i', $header)) {
            \Log::warning('Potential malware detected in upload', [
                'filename' => $file->getClientOriginalName(),
                'ip' => request()->ip(),
            ]);
            abort(400, 'File contains potentially malicious content');
        }
    }

    private function checkForEmbeddedCode($file): void
    {
        $tempPath = $file->getRealPath();
        $content = file_get_contents($tempPath, false, null, 0, 8192);

        $dangerousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/base64_decode/i',
            '/system\s*\(/i',
            '/exec\s*\(/i',
            '/shell_exec/i',
            '/passthru/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                \Log::warning('Embedded code detected in upload', [
                    'filename' => $file->getClientOriginalName(),
                    'pattern' => $pattern,
                    'ip' => request()->ip(),
                ]);
                abort(400, 'File contains embedded code');
            }
        }
    }
}
