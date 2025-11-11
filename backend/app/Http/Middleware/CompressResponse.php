<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only compress if enabled in config
        if (! config('cache-strategy.compression.enabled', true)) {
            return $response;
        }

        // Check if client accepts compression
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        // Check if response should be compressed
        if (! $this->shouldCompress($response)) {
            return $response;
        }

        // Determine compression method
        $method = $this->determineCompressionMethod($acceptEncoding);
        
        if (! $method) {
            return $response;
        }

        return $this->compressResponse($response, $method);
    }

    /**
     * Determine if the response should be compressed.
     */
    private function shouldCompress(Response $response): bool
    {
        $content = $response->getContent();
        
        // Don't compress empty responses
        if (empty($content)) {
            return false;
        }

        // Check minimum size threshold
        $minSize = config('cache-strategy.compression.min_size', 1024);
        if (strlen($content) < $minSize) {
            return false;
        }

        // Check if already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        // Check content type
        $contentType = $response->headers->get('Content-Type', '');
        $allowedTypes = config('cache-strategy.compression.mime_types', [
            'application/json',
            'application/xml',
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
        ]);

        foreach ($allowedTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the compression method based on Accept-Encoding header.
     */
    private function determineCompressionMethod(string $acceptEncoding): ?string
    {
        $configMethod = config('cache-strategy.compression.algorithm', 'gzip');

        // Check for brotli support (preferred if configured)
        if ($configMethod === 'brotli' && str_contains($acceptEncoding, 'br') && function_exists('brotli_compress')) {
            return 'br';
        }

        // Fallback to gzip
        if (str_contains($acceptEncoding, 'gzip') && function_exists('gzencode')) {
            return 'gzip';
        }

        // Fallback to deflate
        if (str_contains($acceptEncoding, 'deflate') && function_exists('gzdeflate')) {
            return 'deflate';
        }

        return null;
    }

    /**
     * Compress the response content.
     */
    private function compressResponse(Response $response, string $method): Response
    {
        $content = $response->getContent();
        $level = config('cache-strategy.compression.level', 6);

        try {
            $compressed = match ($method) {
                'br' => brotli_compress($content, $level),
                'gzip' => gzencode($content, $level),
                'deflate' => gzdeflate($content, $level),
                default => null,
            };

            if ($compressed === false || $compressed === null) {
                return $response;
            }

            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', $method);
            $response->headers->set('Content-Length', (string) strlen($compressed));
            $response->headers->set('Vary', 'Accept-Encoding', false);

        } catch (\Throwable $e) {
            // If compression fails, return original response
            \Log::warning('Response compression failed', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }
}
