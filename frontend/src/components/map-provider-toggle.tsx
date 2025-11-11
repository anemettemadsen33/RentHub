"use client";

import { useEffect, useState } from 'react';
import { getPreferredMapProvider, setPreferredMapProvider, MapProvider } from '@/lib/map-provider';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface MapProviderToggleProps {
  className?: string;
  size?: 'sm' | 'md';
}

export function MapProviderToggle({ className, size = 'md' }: MapProviderToggleProps) {
  const [provider, setProvider] = useState<MapProvider>(getPreferredMapProvider());

  useEffect(() => {
    // Ensure state matches any external changes
    const unsub = () => {
      // basic inline unsubscribe not needed due to simple pattern
    };
    const sync = () => setProvider(getPreferredMapProvider());
    window.addEventListener('map-provider-change', sync as EventListener);
    window.addEventListener('storage', sync);
    return () => {
      window.removeEventListener('map-provider-change', sync as EventListener);
      window.removeEventListener('storage', sync);
      unsub();
    };
  }, []);

  function onChange(value: string) {
    if (value === 'leaflet' || value === 'mapbox') {
      setPreferredMapProvider(value);
      setProvider(value);
    }
  }

  return (
    <div className={className}>
      <div className="flex items-center gap-2">
        <Label className="text-xs font-medium">Harta</Label>
        <Select value={provider} onValueChange={onChange}>
          <SelectTrigger className={size === 'sm' ? 'h-8 w-36' : 'w-40'} aria-label="Select map provider">
            <SelectValue placeholder="Provider" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="leaflet">Leaflet (Free)</SelectItem>
            <SelectItem value="mapbox">Mapbox</SelectItem>
          </SelectContent>
        </Select>
      </div>
      <p className="mt-1 text-[10px] text-muted-foreground leading-snug">
        Schimbă între furnizorii de hartă. Preferința este salvată local (nu afectează alți utilizatori).
      </p>
    </div>
  );
}
