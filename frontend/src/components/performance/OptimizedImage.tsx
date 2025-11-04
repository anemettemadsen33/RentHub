'use client';

import Image, { ImageProps } from 'next/image';
import { useState } from 'react';
import { getOptimalImageSize, shouldLoadHighQuality } from '@/lib/performance';

interface OptimizedImageProps extends Omit<ImageProps, 'onLoad'> {
  lowQualitySrc?: string;
  priority?: boolean;
}

export default function OptimizedImage({
  src,
  lowQualitySrc,
  priority = false,
  width,
  height,
  alt,
  ...props
}: OptimizedImageProps) {
  const [isLoaded, setIsLoaded] = useState(false);
  const [currentSrc, setCurrentSrc] = useState(
    shouldLoadHighQuality() ? src : (lowQualitySrc || src)
  );

  const handleLoad = () => {
    setIsLoaded(true);
    
    // Upgrade to high quality after load if needed
    if (lowQualitySrc && shouldLoadHighQuality() && currentSrc === lowQualitySrc) {
      setTimeout(() => setCurrentSrc(src), 100);
    }
  };

  // Get optimal dimensions based on device
  const optimalSize = typeof width === 'number' && typeof height === 'number'
    ? getOptimalImageSize(width, height)
    : { width, height };

  return (
    <div className="relative overflow-hidden">
      {!isLoaded && (
        <div className="absolute inset-0 bg-gray-200 animate-pulse" />
      )}
      <Image
        src={currentSrc}
        alt={alt}
        width={optimalSize.width}
        height={optimalSize.height}
        priority={priority}
        loading={priority ? 'eager' : 'lazy'}
        onLoad={handleLoad}
        className={`transition-opacity duration-300 ${
          isLoaded ? 'opacity-100' : 'opacity-0'
        }`}
        {...props}
      />
    </div>
  );
}
