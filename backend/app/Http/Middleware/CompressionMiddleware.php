<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressionMiddleware
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only compress if client accepts it
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        if (!$this->shouldCompress($response)) {
            return $response;
        }

        $content = $response->getContent();
        
        // Try Brotli first (better compression)
        if (str_contains($acceptEncoding, 'br') && function_exists('brotli_compress')) {
            $compressed = brotli_compress($content, 11, BROTLI_TEXT);
            if ($compressed !== false) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'br');
                $response->headers->set('Content-Length', strlen($compressed));
                return $response;
            }
        }

        // Fallback to gzip
        if (str_contains($acceptEncoding, 'gzip') && function_exists('gzencode')) {
            $compressed = gzencode($content, 6);
            if ($compressed !== false) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Content-Length', strlen($compressed));
                return $response;
            }
        }

        return $response;
    }

    /**
     * Determine if response should be compressed
     */
    protected function shouldCompress(Response $response): bool
    {
        // Don't compress if already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        // Only compress text-based content
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
            'text/plain',
        ];

        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }
}
