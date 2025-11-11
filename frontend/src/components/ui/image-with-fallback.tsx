'use client';

import Image, { ImageProps } from 'next/image';
import { useState } from 'react';

interface ImageWithFallbackProps extends Omit<ImageProps, 'onError'> {
  fallbackSrc?: string;
  onError?: () => void;
}

/**
 * Next.js Image component with automatic fallback support
 * 
 * Features:
 * - Automatic fallback to placeholder on error
 * - All standard Next.js Image optimizations (WebP, AVIF, lazy loading)
 * - Custom error handling callback
 * - Type-safe props
 * 
 * @example
 * ```tsx
 * <ImageWithFallback
 *   src="/user-avatar.jpg"
 *   fallbackSrc="/default-avatar.png"
 *   alt="User avatar"
 *   width={48}
 *   height={48}
 * />
 * ```
 */
export function ImageWithFallback({
  fallbackSrc = 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800',
  onError,
  src,
  alt,
  ...props
}: ImageWithFallbackProps) {
  const [imgSrc, setImgSrc] = useState(src);
  const [hasError, setHasError] = useState(false);

  const handleError = () => {
    if (!hasError) {
      setHasError(true);
      setImgSrc(fallbackSrc);
      onError?.();
    }
  };

  return (
    <Image
      {...props}
      src={imgSrc}
      alt={alt}
      onError={handleError}
    />
  );
}
