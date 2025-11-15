import { Property } from '@/types';

export interface StructuredDataProps {
  property: Property;
  reviews?: any[];
  rating?: number;
}

export function generatePropertyStructuredData({ property, reviews = [], rating }: StructuredDataProps) {
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.international';
  
  // Product Schema for Property
  const productSchema: any = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: property.title,
    description: property.description,
    image: property.image_url ? (Array.isArray(property.image_url) ? property.image_url : [property.image_url]) : [],
    offers: {
      '@type': 'Offer',
      price: property.price_per_night || property.price,
      priceCurrency: 'USD',
      availability: property.status === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
      url: `${baseUrl}/properties/${property.id}`,
    },
    brand: {
      '@type': 'Organization',
      name: 'RentHub',
    },
  };

  // Place/Accommodation Schema
  const placeSchema: any = {
    '@context': 'https://schema.org',
    '@type': 'LodgingBusiness',
    name: property.title,
    description: property.description,
    image: property.image_url ? (Array.isArray(property.image_url) ? property.image_url : [property.image_url]) : [],
    address: {
      '@type': 'PostalAddress',
      addressLocality: property.city || '',
      addressRegion: (property as any).state || '',
      addressCountry: property.country || 'US',
      streetAddress: property.address || '',
    },
    geo: property.latitude && property.longitude ? {
      '@type': 'GeoCoordinates',
      latitude: property.latitude,
      longitude: property.longitude,
    } : undefined,
    priceRange: `$${property.price_per_night || property.price}`,
    starRating: rating ? {
      '@type': 'Rating',
      ratingValue: rating.toFixed(1),
      bestRating: '5',
    } : undefined,
    amenityFeature: (property.amenities || [])
      .map((a: any) => (typeof a === 'string' ? a : a?.name))
      .filter(Boolean)
      .map((amenity: string) => ({
      '@type': 'LocationFeatureSpecification',
      name: amenity,
    })),
  };

  // AggregateRating if reviews exist
  if (reviews.length > 0 && rating) {
    const aggregateRating = {
      '@type': 'AggregateRating',
      ratingValue: rating.toFixed(1),
      reviewCount: reviews.length,
      bestRating: '5',
      worstRating: '1',
    };
    productSchema.aggregateRating = aggregateRating;
    placeSchema.aggregateRating = aggregateRating;
  }

  return {
    product: productSchema,
    place: placeSchema,
  };
}

export function generateReviewStructuredData(reviews: any[], propertyId: number) {
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.international';
  
  return reviews.map((review) => ({
    '@context': 'https://schema.org',
    '@type': 'Review',
    itemReviewed: {
      '@type': 'Product',
      name: `Property #${propertyId}`,
      url: `${baseUrl}/properties/${propertyId}`,
    },
    author: {
      '@type': 'Person',
      name: review.user?.name || 'Anonymous',
    },
    reviewRating: {
      '@type': 'Rating',
      ratingValue: review.rating,
      bestRating: '5',
      worstRating: '1',
    },
    reviewBody: review.comment,
    datePublished: review.created_at,
  }));
}
