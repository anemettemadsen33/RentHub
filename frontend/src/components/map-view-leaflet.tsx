"use client";

import dynamic from 'next/dynamic';
import { useEffect, useMemo, useState } from 'react';
import type { Property } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { X, MapPin } from 'lucide-react';
import Link from 'next/link';
import Image from 'next/image';
import { formatCurrency } from '@/lib/utils';

// Lazy load react-leaflet parts to avoid SSR issues
const MapContainer = dynamic(() => import('react-leaflet').then(m => m.MapContainer as any), { ssr: false }) as any;
const TileLayer = dynamic(() => import('react-leaflet').then(m => m.TileLayer as any), { ssr: false }) as any;
const Marker = dynamic(() => import('react-leaflet').then(m => m.Marker as any), { ssr: false }) as any;
const Popup = dynamic(() => import('react-leaflet').then(m => m.Popup as any), { ssr: false }) as any;
const MarkerClusterGroup = dynamic(() => import('react-leaflet-cluster').then(m => (m as any).default), { ssr: false }) as any;

import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix default icon paths (Leaflet expects images in a specific location)
// We create a basic circle marker with price overlays instead of default icon images

interface MapViewLeafletProps {
  properties: Property[];
  center?: [number, number];
  zoom?: number;
  onPropertyClick?: (property: Property) => void;
  onViewportChange?: (vp: { bounds: [number, number, number, number]; zoom: number }) => void;
}

// Simple divIcon generator for price markers
function priceIcon(price: number) {
  return L.divIcon({
    className: 'price-marker',
    html: `<div class="bg-primary text-primary-foreground px-3 py-1 rounded-full shadow-lg font-semibold text-sm border border-white/40">${formatCurrency(price)}</div>`
  });
}

export function MapViewLeaflet({ properties, center, zoom = 11, onPropertyClick, onViewportChange }: MapViewLeafletProps) {
  const valid = properties.filter(p => p.latitude && p.longitude);
  const clusterThreshold = Number(process.env.NEXT_PUBLIC_MAP_CLUSTER_THRESHOLD || 40);

  const computedCenter: [number, number] = useMemo(() => {
    if (center) return center;
    if (valid.length === 0) return [51.5074, -0.1276]; // London fallback (Leaflet uses [lat,lng])
    const avgLat = valid.reduce((s, p) => s + (p.latitude ?? 0), 0) / valid.length;
    const avgLng = valid.reduce((s, p) => s + (p.longitude ?? 0), 0) / valid.length;
    return [avgLat, avgLng];
  }, [center, valid]);

  const [selectedProperty, setSelectedProperty] = useState<Property | null>(null);

  // Fit bounds after mount when there are multiple markers
  const bounds = useMemo(() => {
    if (valid.length === 0) return null;
    const b = L.latLngBounds([]);
    valid.forEach(p => b.extend([p.latitude!, p.longitude!]));
    return b;
  }, [valid]);

  // We need a wrapper because MapContainer can't accept bounds & center simultaneously reliably on first render
  const MapInner = () => {
    const map = (L as any).useMap?.(); // react-leaflet v4 hook (will exist at runtime)
    useEffect(() => {
      if (map && bounds && valid.length > 1) {
        map.fitBounds(bounds, { padding: [50, 50] });
      }
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [map, valid.length]);

    useEffect(() => {
      if (!map || !onViewportChange) return;
      const handler = () => {
        const b = map.getBounds();
        const sw = b.getSouthWest();
        const ne = b.getNorthEast();
        onViewportChange({ bounds: [sw.lng, sw.lat, ne.lng, ne.lat], zoom: map.getZoom() });
      };
      map.on('moveend', handler);
      return () => {
        map.off('moveend', handler);
      };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [map]);
    return null;
  };

  return (
    <div className="relative w-full h-full">
      <MapContainer
        // react-leaflet expects lat,lng order
        center={computedCenter as any}
        zoom={zoom as any}
        className="w-full h-full rounded-lg"
        style={{ minHeight: '400px' }}
        scrollWheelZoom={true as any}
      >
        <TileLayer
          // @ts-ignore runtime prop
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {valid.length > clusterThreshold ? (
          <MarkerClusterGroup chunkedLoading>
            {valid.map(p => (
              <Marker
                key={p.id}
                position={[p.latitude!, p.longitude!] as any}
                // @ts-ignore leaflet icon prop
                icon={priceIcon(p.price_per_night || p.price) as any}
                eventHandlers={{
                  click: () => {
                    setSelectedProperty(p);
                    onPropertyClick?.(p);
                  }
                }}
              >
                <Popup>
                  <div className="space-y-1">
                    <strong>{p.title}</strong>
                    <div className="text-xs text-muted-foreground">
                      {p.city}, {p.country}
                    </div>
                    <div className="text-sm font-semibold">
                      {formatCurrency(p.price_per_night || p.price)} / night
                    </div>
                    <Link href={`/properties/${p.id}`} className="text-primary underline text-xs">View</Link>
                  </div>
                </Popup>
              </Marker>
            ))}
          </MarkerClusterGroup>
        ) : (
          valid.map(p => (
            <Marker
              key={p.id}
              position={[p.latitude!, p.longitude!] as any}
              // @ts-ignore leaflet icon prop
              icon={priceIcon(p.price_per_night || p.price) as any}
              eventHandlers={{
                click: () => {
                  setSelectedProperty(p);
                  onPropertyClick?.(p);
                }
              }}
            >
              <Popup>
                <div className="space-y-1">
                  <strong>{p.title}</strong>
                  <div className="text-xs text-muted-foreground">
                    {p.city}, {p.country}
                  </div>
                  <div className="text-sm font-semibold">
                    {formatCurrency(p.price_per_night || p.price)} / night
                  </div>
                  <Link href={`/properties/${p.id}`} className="text-primary underline text-xs">View</Link>
                </div>
              </Popup>
            </Marker>
          ))
        )}
        <MapInner />
      </MapContainer>

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
                <h3 className="font-semibold text-lg line-clamp-1">{selectedProperty.title}</h3>
                <p className="text-sm text-muted-foreground flex items-center gap-1">
                  <MapPin className="h-3 w-3" />
                  {selectedProperty.city}, {selectedProperty.country}
                </p>
              </div>
              <div className="flex items-center gap-2 mb-3">
                <Badge variant="secondary">{selectedProperty.bedrooms} bed</Badge>
                <Badge variant="secondary">{selectedProperty.bathrooms} bath</Badge>
                <Badge variant="secondary">{selectedProperty.max_guests} guests</Badge>
              </div>
              <div className="flex items-center justify-between">
                <div>
                  <span className="text-2xl font-bold">
                    {formatCurrency(selectedProperty.price_per_night || selectedProperty.price)}
                  </span>
                  <span className="text-sm text-muted-foreground">/night</span>
                </div>
                <Button asChild>
                  <Link href={`/properties/${selectedProperty.id}`}>View Details</Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
}
