import { Metadata } from 'next';
import { Property } from '@/types';

interface MetaTagsOptions {
  title: string;
  description: string;
  image?: string;
  url?: string;
  type?: 'website' | 'article' | 'product';
  keywords?: string[];
  canonical?: string;
}

/**
 * Generate comprehensive meta tags for SEO
 */
export function generateMetaTags(options: MetaTagsOptions): Metadata {
  const {
    title,
    description,
    image = '/og-image.png',
    url,
    type = 'website',
    keywords = [],
    canonical,
  } = options;

  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.international';
  const fullUrl = url ? `${baseUrl}${url}` : baseUrl;
  const fullImage = image.startsWith('http') ? image : `${baseUrl}${image}`;

  return {
    title,
    description,
    keywords: keywords.join(', '),
    authors: [{ name: 'RentHub' }],
    creator: 'RentHub',
    publisher: 'RentHub',
    formatDetection: {
      telephone: false,
    },
    metadataBase: new URL(baseUrl),
    alternates: {
      canonical: canonical || fullUrl,
    },
    openGraph: {
      title,
      description,
      url: fullUrl,
      siteName: 'RentHub',
      images: [
        {
          url: fullImage,
          width: 1200,
          height: 630,
          alt: title,
        },
      ],
      locale: 'en_US',
      type: type === 'product' ? 'website' : type,
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
      images: [fullImage],
      creator: '@renthub',
    },
    robots: {
      index: true,
      follow: true,
      googleBot: {
        index: true,
        follow: true,
        'max-video-preview': -1,
        'max-image-preview': 'large',
        'max-snippet': -1,
      },
    },
  };
}

/**
 * Generate meta tags specifically for property pages
 */
export function generatePropertyMetaTags(property: Property): Metadata {
  const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.international';
  const image = property.images?.[0] || '/og-image.png';
  const amenityNames = (property.amenities || []).map((a: any) => (typeof a === 'string' ? a : a?.name)).filter(Boolean);
  const amenitiesText = amenityNames.slice(0, 5).join(', ') || '';
  const description = property.description 
    ? `${property.description.slice(0, 150)}...`
    : `${property.bedrooms} bed, ${property.bathrooms} bath property in ${property.city}. ${amenitiesText}`;

  return generateMetaTags({
    title: `${property.title} - ${property.city} | RentHub`,
    description,
    image,
    url: `/properties/${property.id}`,
    type: 'product',
    keywords: [
      'rental property',
      property.city,
      `${property.bedrooms} bedroom`,
      property.type || 'apartment',
      ...amenityNames,
    ],
  });
}

/**
 * Generate meta tags for homepage
 */
export function generateHomeMetaTags(): Metadata {
  return generateMetaTags({
    title: 'RentHub - Find Your Perfect Rental Property',
    description: 'Discover thousands of rental properties with RentHub. Search apartments, houses, and condos with advanced filters, real-time availability, and instant messaging.',
    url: '/',
    keywords: [
      'rental properties',
      'apartments for rent',
      'houses for rent',
      'property rental',
      'find apartments',
      'rental search',
    ],
  });
}

/**
 * Generate meta tags for properties listing page
 */
export function generatePropertiesListMetaTags(filters?: {
  city?: string;
  bedrooms?: number;
  priceMin?: number;
  priceMax?: number;
}): Metadata {
  let title = 'Browse Rental Properties | RentHub';
  let description = 'Browse available rental properties with advanced search filters.';

  if (filters?.city) {
    title = `Rentals in ${filters.city} | RentHub`;
    description = `Find rental properties in ${filters.city}. `;
  }

  if (filters?.bedrooms) {
    description += `${filters.bedrooms} bedroom apartments and houses. `;
  }

  if (filters?.priceMin || filters?.priceMax) {
    const priceRange = filters.priceMin && filters.priceMax
      ? `$${filters.priceMin} - $${filters.priceMax}`
      : filters.priceMin
      ? `From $${filters.priceMin}`
      : `Up to $${filters.priceMax}`;
    description += `${priceRange} per month.`;
  }

  return generateMetaTags({
    title,
    description,
    url: '/properties',
    keywords: [
      'rental listings',
      'property search',
      filters?.city || '',
      'apartments',
      'houses',
    ].filter(Boolean),
  });
}
