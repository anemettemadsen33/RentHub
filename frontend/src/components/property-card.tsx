'use client';

import { cn } from '@/lib/utils';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Property } from '@/types';
import { formatCurrency } from '@/lib/utils';
import { MapPin, Users, Bed, Bath, Star, Heart, Share2, Check } from 'lucide-react';
import Link from 'next/link';
import { PropertyImage } from '@/components/ui/smart-image';
import { useState } from 'react';
import { trackMarketingEvent } from '@/lib/analytics-client';
import { notify } from '@/lib/notify';
import { useTranslations } from '@/lib/i18n-temp';

interface PropertyCardProps {
  property: Property;
  onFavorite?: (id: number) => void;
  isFavorite?: boolean;
  className?: string;
}

export function PropertyCard({ property, onFavorite, isFavorite = false, className }: PropertyCardProps) {
  const [imageIndex, setImageIndex] = useState(0);
  const [shared, setShared] = useState(false);
    const images = (property.images && property.images.length > 0) ? property.images : (property.image_url ? [property.image_url] : ['https://images.unsplash.com/photo-1568605114967-8130f3a36994']);
  const tNotify = useTranslations('notify');

  const handlePrevImage = (e: React.MouseEvent) => {
    e.preventDefault();
    setImageIndex((prev) => (prev === 0 ? images.length - 1 : prev - 1));
  };

  const handleNextImage = (e: React.MouseEvent) => {
    e.preventDefault();
    setImageIndex((prev) => (prev === images.length - 1 ? 0 : prev + 1));
  };

  const handleFavorite = (e: React.MouseEvent) => {
    e.preventDefault();
    onFavorite?.(property.id);
  };

  return (
    <Link href={`/properties/${property.id}`}>
      <Card className={cn('overflow-hidden hover:shadow-lg transition-shadow cursor-pointer group', className)}>
        {/* Image Gallery */}
        <div className="relative h-64 overflow-hidden bg-gray-100">
            <PropertyImage
              src={images[imageIndex]}
              alt={`${property.title} - Image ${imageIndex + 1} of ${images.length}`}
              fill
              isFirstCard={imageIndex === 0 && process.env.NEXT_PUBLIC_E2E !== 'true'}
              showPlaceholder={process.env.NEXT_PUBLIC_E2E !== 'true'}
              data-testid={process.env.NEXT_PUBLIC_E2E === 'true' ? 'stub-image' : undefined}
            />
          
          {/* Image Navigation */}
          {images.length > 1 && (
            <>
              <button
                onClick={handlePrevImage}
                aria-label="Previous image"
                className="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button
                onClick={handleNextImage}
                aria-label="Next image"
                className="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </button>
              {/* Image Dots */}
              <div className="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1" role="presentation" aria-hidden="true">
                {images.slice(0, 5).map((_, idx) => (
                  <div
                    key={idx}
                    className={cn(
                      'w-1.5 h-1.5 rounded-full transition-colors',
                      idx === imageIndex ? 'bg-white' : 'bg-white/50'
                    )}
                  />
                ))}
              </div>
            </>
          )}

          {/* Favorite & Share */}
          <div className="absolute top-3 right-3 flex gap-2">
            <button
              onClick={handleFavorite}
              aria-label={isFavorite ? "Remove from favorites" : "Add to favorites"}
              className="bg-white/90 hover:bg-white rounded-full p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-primary"
            >
              <Heart 
                className={cn('w-5 h-5', isFavorite ? 'fill-red-500 text-red-500' : 'text-gray-700')} 
                aria-hidden="true"
              />
            </button>
            <button 
              aria-label="Share property"
              className="bg-white/90 hover:bg-white rounded-full p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-primary"
              onClick={async (e) => {
                e.preventDefault();
                const url = `${window.location.origin}/properties/${property.id}`;
                const title = property.title;
                let succeeded = false;
                try {
                  if (navigator.share) {
                    await navigator.share({ title, url });
                    succeeded = true;
                  } else {
                    await navigator.clipboard.writeText(url);
                    succeeded = true;
                  }
                } catch {
                  succeeded = false;
                }
                if (succeeded) {
                  trackMarketingEvent('share_property', { propertyId: property.id });
                  setShared(true);
                  notify.success({ title: tNotify('shareLinkSuccessTitle'), description: tNotify('shareLinkSuccessDesc') });
                  setTimeout(() => setShared(false), 3000);
                } else {
                  notify.error({ title: tNotify('shareLinkErrorTitle'), description: tNotify('shareLinkErrorDesc') });
                }
              }}
            >
              {shared ? <Check className="w-5 h-5 text-green-600" aria-hidden="true" /> : <Share2 className="w-5 h-5 text-gray-700" aria-hidden="true" />}
            </button>
          </div>

          {/* Status Badge */}
          {property.status === 'available' && (
            <Badge className="absolute top-3 left-3 bg-green-500" aria-label="Property status: Available">
              Available
            </Badge>
          )}
        </div>

        {/* Content */}
        <div className="p-4">
          {/* Title & Rating */}
          <div className="flex items-start justify-between mb-2">
            <h3 className="font-semibold text-lg line-clamp-1 flex-1">{property.title}</h3>
            {property.rating && (
              <div className="flex items-center gap-1 ml-2">
                <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
                <span className="text-sm font-semibold">{property.rating}</span>
              </div>
            )}
          </div>

          {/* Location */}
          <div className="flex items-center text-gray-600 text-sm mb-3">
            <MapPin className="w-4 h-4 mr-1" />
            <span className="line-clamp-1">{property.city}, {property.country}</span>
          </div>

          {/* Property Details */}
          <div className="flex items-center gap-4 text-sm text-gray-600 mb-3">
            <div className="flex items-center gap-1">
              <Users className="w-4 h-4" aria-hidden="true" />
              <span><span className="sr-only">Maximum guests: </span>{property.max_guests}</span>
            </div>
            <div className="flex items-center gap-1">
              <Bed className="w-4 h-4" aria-hidden="true" />
              <span><span className="sr-only">Bedrooms: </span>{property.bedrooms}</span>
            </div>
            <div className="flex items-center gap-1">
              <Bath className="w-4 h-4" aria-hidden="true" />
              <span><span className="sr-only">Bathrooms: </span>{property.bathrooms}</span>
            </div>
          </div>

          {/* Amenities Preview */}
          {property.amenities && property.amenities.length > 0 && (
            <div className="flex gap-1 mb-3 flex-wrap">
              {property.amenities
                .map((a: any) => (typeof a === 'string' ? a : a?.name))
                .filter(Boolean)
                .slice(0, 3)
                .map((label: string, idx: number) => (
                  <Badge key={idx} variant="secondary" className="text-xs capitalize">
                    {label}
                  </Badge>
                ))}
              {property.amenities.length > 3 && (
                <Badge variant="secondary" className="text-xs">
                  +{property.amenities.length - 3}
                </Badge>
              )}
            </div>
          )}

          {/* Price */}
          <div className="flex items-center justify-between pt-3 border-t">
            <div>
              <span className="text-2xl font-bold">{formatCurrency(property.price_per_night || property.price)}</span>
              <span className="text-gray-600 text-sm ml-1">/night</span>
            </div>
            <Button size="sm" variant="outline">View Details</Button>
          </div>
        </div>
      </Card>
    </Link>
  );
}
