"use client";

import { MapView } from '@/components/map-view';
import { MapViewLeaflet } from '@/components/map-view-leaflet';
import { getPreferredMapProvider, subscribeToMapProviderChanges, resolveEnvDefault } from '@/lib/map-provider';
import { useEffect, useState } from 'react';
import type { Property } from '@/types';

interface Props {
  properties: Property[];
  center?: [number, number];
  zoom?: number;
  onPropertyClick?: (property: Property) => void;
  onViewportChange?: (vp: { bounds: [number, number, number, number]; zoom: number }) => void;
}

// Small wrapper to pick a map provider based on env variables
export function MapViewProvider(props: Props) {
  const [provider, setProvider] = useState(resolveEnvDefault());

  useEffect(() => {
    // Initialize from localStorage preference (if any)
    setProvider(getPreferredMapProvider(provider));
    // Subscribe to runtime changes
    const unsub = subscribeToMapProviderChanges(p => setProvider(p));
    return () => {
      unsub();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (provider === 'mapbox') return <MapView {...props} />;
  return <MapViewLeaflet {...props} />;
}
