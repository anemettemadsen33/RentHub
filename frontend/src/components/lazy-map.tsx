"use client";

import { useEffect, useRef, useState } from 'react';
import dynamic from 'next/dynamic';
import type { Property } from '@/types';

const MapViewProvider = dynamic<any>(() => import('@/components/map-view-provider').then(m => m.MapViewProvider), { ssr: false });

interface Props {
  properties: Property[];
  center?: [number, number];
  zoom?: number;
  onPropertyClick?: (property: Property) => void;
  minHeight?: number;
  onViewportChange?: (vp: { bounds: [number, number, number, number]; zoom: number }) => void;
}

export function LazyMap({ properties, center, zoom = 11, onPropertyClick, minHeight = 400, onViewportChange }: Props) {
  const ref = useRef<HTMLDivElement | null>(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    if (!ref.current || visible) return;
    const el = ref.current;
    const io = new IntersectionObserver((entries) => {
      const entry = entries[0];
      if (entry.isIntersecting) {
        setVisible(true);
        io.disconnect();
      }
    }, { rootMargin: '200px' });
    io.observe(el);
    return () => io.disconnect();
  }, [visible]);

  return (
    <div ref={ref} style={{ minHeight }} className="w-full">
      {visible ? (
        <MapViewProvider properties={properties} center={center} zoom={zoom} onPropertyClick={onPropertyClick} onViewportChange={onViewportChange} />
      ) : (
        <div className="w-full h-full flex items-center justify-center text-muted-foreground">
          Loading map...
        </div>
      )}
    </div>
  );
}
