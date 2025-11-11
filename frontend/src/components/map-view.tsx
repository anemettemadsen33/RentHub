"use client";

import { useEffect, useRef, useState } from 'react';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import { Property } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { X, MapPin } from 'lucide-react';
import Link from 'next/link';
import Image from 'next/image';
import { formatCurrency } from '@/lib/utils';

mapboxgl.accessToken = process.env.NEXT_PUBLIC_MAPBOX_TOKEN!;

interface MapViewProps {
  properties: Property[];
  center?: [number, number];
  zoom?: number;
  onPropertyClick?: (property: Property) => void;
  onViewportChange?: (vp: { bounds: [number, number, number, number]; zoom: number }) => void;
}

export function MapView({ properties, center, zoom = 11, onPropertyClick, onViewportChange }: MapViewProps) {
  const mapContainer = useRef<HTMLDivElement>(null);
  const map = useRef<mapboxgl.Map | null>(null);
  const markers = useRef<mapboxgl.Marker[]>([]);
  const [selectedProperty, setSelectedProperty] = useState<Property | null>(null);

  // Initialize map
  useEffect(() => {
    if (!mapContainer.current || map.current) return;

    // Calculate center from properties if not provided
    const mapCenter = center || calculateCenter(properties);

    map.current = new mapboxgl.Map({
      container: mapContainer.current,
      style: 'mapbox://styles/mapbox/streets-v12',
      center: mapCenter,
      zoom: zoom,
    });

    map.current.addControl(new mapboxgl.NavigationControl(), 'top-right');
    map.current.addControl(new mapboxgl.FullscreenControl(), 'top-right');

    // Wire viewport change events
    const handleMoveEnd = () => {
      if (!map.current) return;
      const b = map.current.getBounds();
      if (!b) return;
      const sw = b.getSouthWest();
      const ne = b.getNorthEast();
      if (sw && ne) {
        onViewportChange?.({ bounds: [sw.lng, sw.lat, ne.lng, ne.lat], zoom: map.current.getZoom() });
      }
    };
    map.current.on('moveend', handleMoveEnd);

    return () => {
      if (map.current) {
        map.current.off('moveend', handleMoveEnd as any);
        map.current.remove();
      }
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Update markers when properties change
  useEffect(() => {
    if (!map.current) return;

    // Remove existing markers
    markers.current.forEach(marker => marker.remove());
    markers.current = [];

    // Add new markers
    properties.forEach(property => {
      if (!property.latitude || !property.longitude) return;

      // Create marker element
      const el = document.createElement('div');
      el.className = 'map-marker';
      el.innerHTML = `
        <div class="bg-primary text-primary-foreground px-3 py-1 rounded-full shadow-lg font-semibold text-sm hover:scale-110 transition-transform cursor-pointer">
          ${formatCurrency(property.price_per_night || property.price)}
        </div>
      `;

      const marker = new mapboxgl.Marker({ element: el })
        .setLngLat([property.longitude, property.latitude])
        .addTo(map.current!);

      el.addEventListener('click', () => {
        setSelectedProperty(property);
        onPropertyClick?.(property);
      });

      markers.current.push(marker);
    });

    // Fit bounds to show all markers
    if (properties.length > 0 && properties.some(p => p.latitude && p.longitude)) {
      const bounds = new mapboxgl.LngLatBounds();
      properties.forEach(p => {
        if (p.latitude && p.longitude) {
          bounds.extend([p.longitude, p.latitude]);
        }
      });
      map.current.fitBounds(bounds, { padding: 50, maxZoom: 15 });
    }
  }, [properties, onPropertyClick]);

  return (
    <div className="relative w-full h-full">
      <div ref={mapContainer} className="w-full h-full rounded-lg" />

      {/* Property Card Popup */}
      {selectedProperty && (
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 w-full max-w-sm px-4">
          <Card className="shadow-xl">
            <Button
              variant="ghost"
              size="icon"
              className="absolute top-2 right-2 z-10"
              onClick={() => setSelectedProperty(null)}
              aria-label="Close property preview"
            >
              <X className="h-4 w-4" />
            </Button>

            {selectedProperty.image_url && (
              <div className="relative h-48 w-full">
                <Image
                  src={selectedProperty.image_url}
                  alt={selectedProperty.title}
                  fill
                  className="object-cover rounded-t-lg"
                  sizes="400px"
                />
              </div>
            )}

            <CardContent className="p-4">
              <div className="mb-2">
                <h3 className="font-semibold text-lg line-clamp-1">
                  {selectedProperty.title}
                </h3>
                <p className="text-sm text-muted-foreground flex items-center gap-1">
                  <MapPin className="h-3 w-3" />
                  {selectedProperty.city}, {selectedProperty.country}
                </p>
              </div>

              <div className="flex items-center gap-2 mb-3">
                <Badge variant="secondary">
                  {selectedProperty.bedrooms} bed
                </Badge>
                <Badge variant="secondary">
                  {selectedProperty.bathrooms} bath
                </Badge>
                <Badge variant="secondary">
                  {selectedProperty.max_guests} guests
                </Badge>
              </div>

              <div className="flex items-center justify-between">
                <div>
                  <span className="text-2xl font-bold">
                    {formatCurrency(selectedProperty.price_per_night || selectedProperty.price)}
                  </span>
                  <span className="text-sm text-muted-foreground">/night</span>
                </div>
                <Button asChild>
                  <Link href={`/properties/${selectedProperty.id}`}>
                    View Details
                  </Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
}

function calculateCenter(properties: Property[]): [number, number] {
  const validProperties = properties.filter(p => p.latitude && p.longitude);
  
  if (validProperties.length === 0) {
    return [-0.1276, 51.5074]; // Default to London
  }

  const avgLat = validProperties.reduce((sum, p) => sum + p.latitude!, 0) / validProperties.length;
  const avgLng = validProperties.reduce((sum, p) => sum + p.longitude!, 0) / validProperties.length;

  return [avgLng, avgLat];
}
