"use client";

import Image, { ImageProps } from 'next/image';
import { cn } from '@/lib/utils';
import { useState } from 'react';

// Reusable Image wrapper with sensible defaults
export function BlurImage({
  className,
  alt,
  sizes = '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
  placeholder = 'empty',
  quality = 75,
  onLoadingComplete,
  ...props
}: ImageProps) {
  const [loaded, setLoaded] = useState(false);
  return (
    <Image
      alt={alt}
      sizes={sizes}
      placeholder={placeholder}
      quality={quality}
      onLoadingComplete={(img) => {
        setLoaded(true);
        onLoadingComplete?.(img);
      }}
      className={cn('transition-opacity duration-300', loaded ? 'opacity-100' : 'opacity-0', className)}
      {...props}
    />
  );
}
