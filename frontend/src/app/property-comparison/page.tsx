"use client";

import { useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { MainLayout } from '@/components/layouts/main-layout';
import { TooltipProvider } from '@/components/ui/tooltip';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { X, ArrowRight, Check, Minus } from 'lucide-react';
import { notify } from '@/lib/notify';
import { useTranslations } from 'next-intl';
import apiClient from '@/lib/api-client';
import { Property } from '@/types';
import { formatCurrency } from '@/lib/utils';
import { mockProperties } from '@/lib/mock-data';

interface ComparisonProperty extends Property {
  area?: number;
  features?: Record<string, any>;
}

export default function PropertyComparisonPage() {
  const router = useRouter();
  const [properties, setProperties] = useState<ComparisonProperty[]>([]);
  const tNotify = useTranslations('notify');
  const tComparison = useTranslations('comparison');
  const tReviews = useTranslations('reviews');
  const tProperties = useTranslations('properties');
  const [loading, setLoading] = useState(true);

  const fetchComparison = useCallback(async () => {
    const useStub = process.env.NEXT_PUBLIC_E2E === 'true';
    try {
      if (useStub) {
        // Populate from localStorage and mock data
        const idsRaw = localStorage.getItem('comparison');
        const ids: number[] = idsRaw ? JSON.parse(idsRaw) : [];
        const all = mockProperties.slice(0, 12);
        setProperties(all.filter(p => ids.includes(p.id)) as any);
      } else {
        const { data } = await apiClient.get('/property-comparison');
        setProperties(data.properties || []);
      }
    } catch (error) {
      // Fallback to stub on error
      const idsRaw = localStorage.getItem('comparison');
      const ids: number[] = idsRaw ? JSON.parse(idsRaw) : [];
      const all = mockProperties.slice(0, 12);
      setProperties(all.filter(p => ids.includes(p.id)) as any);
      notify.error({ title: tNotify('comparisonLoadFallback') });
    } finally {
      setLoading(false);
    }
  }, [tNotify]);

  useEffect(() => {
    fetchComparison();
  }, [fetchComparison]);

  const removeProperty = async (propertyId: number) => {
    try {
      await apiClient.delete(`/property-comparison/remove/${propertyId}`);
      setProperties(prev => prev.filter(p => p.id !== propertyId));
      notify.success({
        title: tNotify('propertyRemovedTitle'),
        description: tNotify('propertyRemoved'),
      });
    } catch (error) {
      notify.error({
        title: tNotify('errorRemoveProperty'),
      });
    }
  };

  const clearAll = async () => {
    try {
      await apiClient.delete('/property-comparison/clear');
      setProperties([]);
      notify.success({
        title: tNotify('comparisonClearedTitle'),
        description: tNotify('comparisonCleared'),
      });
    } catch (error) {
      notify.error({
        title: tNotify('errorClearComparison'),
      });
    }
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="animate-pulse space-y-4">
            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
            <div className="h-64 bg-gray-200 dark:bg-gray-700 rounded"></div>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (properties.length === 0) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-16">
              <h2 className="text-2xl font-bold mb-4">{tComparison('emptyTitle', { fallback: 'No Properties to Compare' })}</h2>
              <p className="text-muted-foreground mb-6">
                {tComparison('emptyDesc', { fallback: 'Add properties from the search results to start comparing' })}
              </p>
              <Button asChild>
                <Link href="/properties">
                  {tProperties('viewDetails', { fallback: 'Browse Properties' })}
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </MainLayout>
    );
  }

  // Collect all unique features across all properties
  const allFeatures = Array.from(
    new Set(
      properties.flatMap(p => [
        ...Object.keys(p.features || {}),
        ...((p.amenities || []).map((a: any) => (typeof a === 'string' ? a : a?.name)).filter(Boolean) as string[]),
      ])
    )
  );

  const comparisonRows = [
    { label: tComparison('pricePerNight', { fallback: 'Price per night' }), key: 'price', render: (p: ComparisonProperty) => formatCurrency(p.price_per_night || p.price) },
    { label: tComparison('bedrooms', { fallback: 'Bedrooms' }), key: 'bedrooms', render: (p: ComparisonProperty) => p.bedrooms || 'N/A' },
    { label: tComparison('bathrooms', { fallback: 'Bathrooms' }), key: 'bathrooms', render: (p: ComparisonProperty) => p.bathrooms || 'N/A' },
    { label: tComparison('maxGuests', { fallback: 'Max Guests' }), key: 'max_guests', render: (p: ComparisonProperty) => p.max_guests || 'N/A' },
    { label: tComparison('area', { fallback: 'Area (sqm)' }), key: 'area', render: (p: ComparisonProperty) => p.area ? `${p.area} m²` : 'N/A' },
    { label: tComparison('location', { fallback: 'Location' }), key: 'location', render: (p: ComparisonProperty) => `${p.city || ''}, ${p.country || ''}`.trim() },
    { label: tComparison('status', { fallback: 'Status' }), key: 'status', render: (p: ComparisonProperty) => (
      <Badge variant={p.status === 'available' ? 'default' : 'secondary'}>
        {p.status}
      </Badge>
    )},
  ];

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-3xl font-bold">{tComparison('pageTitle', { fallback: 'Compare Properties' })}</h1>
          <div className="flex gap-2">
            <Button variant="outline" onClick={clearAll}>
              {tComparison('clearAll', { fallback: 'Clear All' })}
            </Button>
            <Button variant="outline" asChild>
              <Link href="/properties">{tComparison('addMore', { fallback: 'Add More' })}</Link>
            </Button>
          </div>
        </div>

        {/* Desktop View - Side by Side */}
        <div className="hidden md:block overflow-x-auto">
          <table className="w-full border-collapse">
            <thead>
              <tr>
                <th className="p-4 text-left bg-muted sticky left-0 z-10 min-w-[200px]">
                  {tComparison('featureHeader', { fallback: 'Feature' })}
                </th>
                {properties.map(property => (
                  <th key={property.id} className="p-4 min-w-[280px] relative" data-testid="comparison-property">
                    <Card className="relative">
                      <Button
                        variant="ghost"
                        size="icon"
                        className="absolute top-2 right-2 z-10 h-8 w-8"
                        onClick={() => removeProperty(property.id)}
                        aria-label="Remove property from comparison"
                      >
                        <X className="h-4 w-4" />
                      </Button>
                      
                      {property.image_url && (
                        <div className="relative h-48 w-full">
                          <Image
                            src={property.image_url}
                            alt={property.title}
                            fill
                            className="object-cover rounded-t-lg"
                            sizes="280px"
                          />
                        </div>
                      )}
                      
                      <CardContent className="p-4">
                        <h3 className="font-semibold text-lg mb-1 line-clamp-2">
                          {property.title}
                        </h3>
                        <p className="text-sm text-muted-foreground line-clamp-1">
                          {property.address}
                        </p>
                      </CardContent>
                    </Card>
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {comparisonRows.map((row, idx) => (
                <tr key={row.key} className={idx % 2 === 0 ? 'bg-muted/50' : ''}>
                  <td className="p-4 font-medium sticky left-0 bg-background border-r">
                    {row.label}
                  </td>
                  {properties.map(property => (
                    <td key={property.id} className="p-4 text-center">
                      {row.render(property)}
                    </td>
                  ))}
                </tr>
              ))}
              
              <tr className="bg-muted/50">
                <td colSpan={properties.length + 1} className="p-4 font-medium">
                  <h4 className="text-lg">{tComparison('amenitiesHeader', { fallback: 'Amenities & Features' })}</h4>
                </td>
              </tr>
              
              {allFeatures.map((feature: string) => (
                <tr key={feature}>
                  <td className="p-4 sticky left-0 bg-background border-r capitalize">
                    {feature.replace(/_/g, ' ')}
                  </td>
                  {properties.map(property => {
                    const hasFeature = 
                      (property.amenities || []).includes(feature) ||
                      (property.features?.[feature] !== undefined && property.features[feature]);
                    
                    return (
                      <td key={property.id} className="p-4 text-center">
                        {hasFeature ? (
                          <Check className="h-5 w-5 text-green-600 mx-auto" />
                        ) : (
                          <Minus className="h-5 w-5 text-gray-300 mx-auto" />
                        )}
                      </td>
                    );
                  })}
                </tr>
              ))}
              
              <tr>
                <td className="p-4 sticky left-0 bg-background border-r"></td>
                {properties.map(property => (
                  <td key={property.id} className="p-4">
                    <div className="flex flex-col gap-2">
                      <Button asChild className="w-full">
                        <Link href={`/properties/${property.id}`}>
                          {tProperties('viewDetails')}
                        </Link>
                      </Button>
                      <Button variant="outline" className="w-full">
                        {tProperties('book')}
                      </Button>
                    </div>
                  </td>
                ))}
              </tr>
            </tbody>
          </table>
        </div>

        {/* Mobile View - Cards */}
        <div className="md:hidden space-y-6">
          {properties.map(property => (
            <Card key={property.id} className="relative" data-testid="comparison-property">
              <Button
                variant="ghost"
                size="icon"
                className="absolute top-2 right-2 z-10 h-8 w-8 bg-background/80"
                onClick={() => removeProperty(property.id)}
                aria-label="Remove property from comparison"
              >
                <X className="h-4 w-4" />
              </Button>
              
              {property.image_url && (
                <div className="relative h-48 w-full">
                  <Image
                    src={property.image_url}
                    alt={property.title}
                    fill
                    className="object-cover rounded-t-lg"
                    sizes="100vw"
                  />
                </div>
              )}
              
              <CardHeader>
                <CardTitle className="text-xl">{property.title}</CardTitle>
                <p className="text-sm text-muted-foreground">{property.address}</p>
              </CardHeader>
              
              <CardContent className="space-y-4">
                {comparisonRows.map(row => (
                  <div key={row.key} className="flex justify-between items-center">
                    <span className="text-sm font-medium">{row.label}:</span>
                    <span className="text-sm">{row.render(property)}</span>
                  </div>
                ))}
                
                <Separator />
                
                <div>
                  <h4 className="font-medium mb-2">Amenities</h4>
                  <div className="flex flex-wrap gap-2">
                    {(property.amenities || [])
                      .map((a: any) => (typeof a === 'string' ? a : a?.name))
                      .filter(Boolean)
                      .map((amenity: string) => (
                        <Badge key={amenity} variant="secondary">
                          {amenity}
                        </Badge>
                      ))}
                  </div>
                </div>
                
                <div className="flex gap-2 pt-4">
                  <Button asChild className="flex-1">
                    <Link href={`/properties/${property.id}`}>{tProperties('viewDetails')}</Link>
                  </Button>
                  <Button variant="outline" className="flex-1">
                    {tProperties('book')}
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </MainLayout>
  );
}
