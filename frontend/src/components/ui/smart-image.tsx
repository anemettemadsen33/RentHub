'use client';

import Image, { ImageProps } from 'next/image';
import { useState } from 'react';

interface SmartImageProps extends Omit<ImageProps, 'onError'> {
  /**
   * Fallback image URL if primary fails to load
   */
  fallbackSrc?: string;
  /**
   * Whether this image is above the fold (hero, first card)
   * Auto-sets priority, removes loading prop
   */
  aboveFold?: boolean;
  /**
   * Custom loading strategy - overrides aboveFold
   */
  loadingStrategy?: 'eager' | 'lazy';
  /**
   * Show placeholder shimmer while loading
   */
  showPlaceholder?: boolean;
}

/**
 * SmartImage - Optimized Next.js Image component
 * 
 * Features:
 * - Automatic priority vs loading conflict resolution
 * - Fallback image support
 * - Above-fold detection for LCP optimization
 * - Placeholder shimmer
 * - Error handling
 * 
 * Usage:
 * ```tsx
 * <SmartImage
 *   src="/hero.jpg"
 *   alt="Hero image"
 *   fill
 *   aboveFold // Sets priority, removes loading
 *   showPlaceholder
 * />
 * ```
 */
export function SmartImage({
  fallbackSrc = '/images/placeholder.jpg',
  aboveFold = false,
  loadingStrategy,
  showPlaceholder = true,
  ...props
}: SmartImageProps) {
  const [imgSrc, setImgSrc] = useState(props.src);
  const [isLoading, setIsLoading] = useState(true);
  const [hasError, setHasError] = useState(false);

  // Determine loading behavior
  const shouldPrioritize = aboveFold;
  const loading = loadingStrategy || (aboveFold ? undefined : 'lazy');

  const handleError = () => {
    if (imgSrc !== fallbackSrc) {
      setImgSrc(fallbackSrc);
      setHasError(true);
    }
  };

  const handleLoad = () => {
    setIsLoading(false);
  };

  const imageProps: ImageProps = {
    ...props,
    src: imgSrc,
    onError: handleError,
    onLoad: handleLoad,
    // CRITICAL: Never set both priority and loading
    ...(shouldPrioritize ? { priority: true } : { loading }),
    // Add blur placeholder if not disabled
    ...(showPlaceholder && !shouldPrioritize
      ? {
          placeholder: 'blur',
          blurDataURL:
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAwIiBoZWlnaHQ9IjQ3NSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2ZXJzaW9uPSIxLjEiLz4=',
        }
      : {}),
  };

  return (
    <div className="relative w-full h-full">
      {isLoading && showPlaceholder && (
        <div className="absolute inset-0 bg-muted animate-skeleton-pulse" aria-hidden="true" />
      )}
      <Image {...imageProps} alt={imageProps.alt || ''} />
      {hasError && (
        <span className="sr-only">Image failed to load, showing fallback</span>
      )}
    </div>
  );
}

/**
 * PropertyImage - Specialized for property cards
 */
export function PropertyImage({
  isFirstCard = false,
  ...props
}: SmartImageProps & { isFirstCard?: boolean }) {
  return (
    <SmartImage
      {...props}
      aboveFold={isFirstCard}
      sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
      className="object-cover group-hover:scale-105 transition-transform duration-300"
    />
  );
}

/**
 * HeroImage - For hero sections
 */
export function HeroImage(props: SmartImageProps) {
  return (
    <SmartImage
      {...props}
      aboveFold
      sizes="100vw"
      className="object-cover"
      priority
    />
  );
}
