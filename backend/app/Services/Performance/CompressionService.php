<?php

namespace App\Services\Performance;

class CompressionService
{
    /**
     * Compress response data using gzip
     */
    public function compressGzip(string $data): string
    {
        return gzencode($data, 9);
    }

    /**
     * Compress response data using brotli (if available)
     */
    public function compressBrotli(string $data): string
    {
        if (function_exists('brotli_compress')) {
            return brotli_compress($data, 11);
        }

        // Fallback to gzip
        return $this->compressGzip($data);
    }

    /**
     * Determine best compression method
     */
    public function getBestCompression(string $acceptEncoding): string
    {
        if (str_contains($acceptEncoding, 'br') && function_exists('brotli_compress')) {
            return 'br';
        }

        if (str_contains($acceptEncoding, 'gzip')) {
            return 'gzip';
        }

        return 'none';
    }

    /**
     * Compress response based on client capabilities
     */
    public function compressResponse(string $data, string $acceptEncoding): array
    {
        $method = $this->getBestCompression($acceptEncoding);

        switch ($method) {
            case 'br':
                return [
                    'data' => $this->compressBrotli($data),
                    'encoding' => 'br',
                ];
            case 'gzip':
                return [
                    'data' => $this->compressGzip($data),
                    'encoding' => 'gzip',
                ];
            default:
                return [
                    'data' => $data,
                    'encoding' => 'identity',
                ];
        }
    }

    /**
     * Calculate compression ratio
     */
    public function getCompressionRatio(string $original, string $compressed): float
    {
        $originalSize = strlen($original);
        $compressedSize = strlen($compressed);

        if ($originalSize === 0) {
            return 0;
        }

        return round((1 - ($compressedSize / $originalSize)) * 100, 2);
    }

    /**
     * Compress images
     */
    public function compressImage(string $path, int $quality = 85): bool
    {
        $info = getimagesize($path);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($path);
                return imagejpeg($image, $path, $quality);
            
            case 'image/png':
                $image = imagecreatefrompng($path);
                $pngQuality = round(9 - ($quality / 100 * 9));
                return imagepng($image, $path, $pngQuality);
            
            case 'image/webp':
                $image = imagecreatefromwebp($path);
                return imagewebp($image, $path, $quality);
            
            default:
                return false;
        }
    }

    /**
     * Convert images to WebP format
     */
    public function convertToWebP(string $sourcePath, string $destPath, int $quality = 85): bool
    {
        $info = getimagesize($sourcePath);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            default:
                return false;
        }

        return imagewebp($image, $destPath, $quality);
    }
}
