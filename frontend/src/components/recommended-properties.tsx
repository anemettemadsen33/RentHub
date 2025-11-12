"use client";
import { useEffect, useState } from 'react';
import { propertiesService, type Property as ApiProperty } from '@/lib/api-service';
import { Property } from '@/types';
import { MemoizedPropertyCard } from '@/components/memoized-property-card';
import { PropertyCardSkeleton } from '@/components/skeletons';
import { useTranslations } from '@/lib/i18n-temp';
import { Button } from '@/components/ui/button';
import Link from 'next/link';

export default function RecommendedProperties() {
  const t = useTranslations('home');
  const [properties, setProperties] = useState<Property[] | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;
    const normalize = (p: ApiProperty): Property => ({
      id: p.id,
      title: p.title,
      description: p.description || '',
      price: p.price_per_night ?? 0,
      price_per_night: p.price_per_night ?? 0,
      currency: 'USD',
      type: 'apartment',
      bedrooms: (p as any).bedrooms ?? 1,
      bathrooms: (p as any).bathrooms ?? 1,
      max_guests: (p as any).guests ?? 2,
      address: p.address,
      city: p.city,
      country: p.country,
      status: 'available',
      rating: undefined,
      review_count: undefined,
      image_url: p.main_image || (p.images && p.images[0]) || undefined,
      images: p.images ?? (p.main_image ? [p.main_image] : undefined),
      amenities: (p as any).amenities || [],
      created_at: p.created_at,
      updated_at: p.updated_at,
    });
    (async () => {
      try {
        // Attempt to load featured/recommended properties
        const featured = await propertiesService.featured().catch(() => []);
        if (!isMounted) return;
        // Fallback: if API returns empty, try generic list with a limit
        if (featured && featured.length > 0) {
          setProperties(featured.slice(0, 4).map(normalize));
        } else {
          const listResp = await propertiesService.list({ per_page: 4 }).catch(() => ({ data: [] as ApiProperty[] }));
          setProperties((listResp.data?.slice(0, 4) || []).map(normalize));
        }
      } catch (e: any) {
        if (isMounted) setError(e?.message || 'Failed to load');
      } finally {
        if (isMounted) setLoading(false);
      }
    })();
    return () => { isMounted = false; };
  }, []);

  return (
    <section className="py-16 border-t">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-4">
          <div className="space-y-2">
            <h2 className="text-3xl font-bold tracking-tight">{t('recommended.title')}</h2>
            <p className="text-muted-foreground max-w-2xl">{t('recommended.subtitle')}</p>
          </div>
          <Button asChild variant="outline" size="lg">
            <Link href="/properties">{t('recommended.browseAll')}</Link>
          </Button>
        </div>

        {loading && (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6" aria-busy="true">
            {[1,2,3,4].map(i => <PropertyCardSkeleton key={i} />)}
          </div>
        )}

        {!loading && error && (
          <div className="p-4 rounded-lg bg-destructive/10 text-destructive text-sm" role="alert">
            {t('recommended.error')} {error}
          </div>
        )}

        {!loading && !error && properties && properties.length === 0 && (
          <div className="p-4 rounded-lg bg-muted text-muted-foreground text-sm" role="status">
            {t('recommended.empty')}
          </div>
        )}

        {!loading && !error && properties && properties.length > 0 && (
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            {properties.map(p => (
              <div key={p.id} className="animate-fade-in-up" data-testid="recommended-property-card">
                <MemoizedPropertyCard property={p} />
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  );
}
