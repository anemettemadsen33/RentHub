import { Metadata } from 'next';

export interface SEOConfig {
  title: string;
  description: string;
  keywords?: string[];
  canonical?: string;
  ogImage?: string;
  ogType?: string;
  noindex?: boolean;
  nofollow?: boolean;
}

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.com';
const SITE_NAME = 'RentHub';
const DEFAULT_OG_IMAGE = '/og-image.jpg';

export function generateMetadata(config: SEOConfig): Metadata {
  const {
    title,
    description,
    keywords = [],
    canonical,
    ogImage = DEFAULT_OG_IMAGE,
    ogType = 'website',
    noindex = false,
    nofollow = false,
  } = config;

  const fullTitle = title.includes(SITE_NAME) ? title : `${title} | ${SITE_NAME}`;
  const canonicalUrl = canonical ? `${SITE_URL}${canonical}` : undefined;
  const imageUrl = ogImage.startsWith('http') ? ogImage : `${SITE_URL}${ogImage}`;

  const robots: string[] = [];
  if (noindex) robots.push('noindex');
  if (nofollow) robots.push('nofollow');
  if (!noindex && !nofollow) robots.push('index', 'follow');

  return {
    title: fullTitle,
    description,
    keywords: keywords.join(', '),
    alternates: canonicalUrl ? { canonical: canonicalUrl } : undefined,
    robots: robots.join(', '),
    openGraph: {
      title: fullTitle,
      description,
      url: canonicalUrl,
      siteName: SITE_NAME,
      images: [
        {
          url: imageUrl,
          width: 1200,
          height: 630,
          alt: title,
        },
      ],
      type: ogType as any,
      locale: 'en_US',
    },
    twitter: {
      card: 'summary_large_image',
      title: fullTitle,
      description,
      images: [imageUrl],
      creator: '@renthub',
      site: '@renthub',
    },
    viewport: {
      width: 'device-width',
      initialScale: 1,
      maximumScale: 5,
    },
    verification: {
      google: process.env.NEXT_PUBLIC_GOOGLE_VERIFICATION,
      other: {
        'facebook-domain-verification': process.env.NEXT_PUBLIC_FB_VERIFICATION || '',
      },
    },
  };
}

export function generatePropertyMetadata(property: {
  id: number;
  title: string;
  description: string;
  location: { city: string; country: string };
  price: number;
  images?: string[];
  bedrooms?: number;
  bathrooms?: number;
}): Metadata {
  const { title, description, location, price, images, bedrooms, bathrooms } = property;
  
  const propertyDescription = `${description.slice(0, 150)}... | ${bedrooms} bed, ${bathrooms} bath | $${price}/month in ${location.city}, ${location.country}`;
  
  return generateMetadata({
    title: `${title} - Property for Rent`,
    description: propertyDescription,
    keywords: [
      'property rental',
      'rent',
      location.city,
      location.country,
      bedrooms ? `${bedrooms} bedroom` : '',
      'apartment',
      'house for rent',
    ],
    canonical: `/properties/${property.id}`,
    ogImage: images && images[0] ? images[0] : DEFAULT_OG_IMAGE,
    ogType: 'article',
  });
}

export function generateSearchMetadata(params: {
  location?: string;
  minPrice?: number;
  maxPrice?: number;
  bedrooms?: number;
}): Metadata {
  const { location, minPrice, maxPrice, bedrooms } = params;
  
  let title = 'Search Properties';
  let description = 'Find your perfect rental property on RentHub';
  const keywords = ['property search', 'rental search', 'find property'];

  if (location) {
    title = `Properties in ${location}`;
    description = `Browse rental properties in ${location}. Find apartments, houses, and more.`;
    keywords.push(location, `rentals in ${location}`);
  }

  if (bedrooms) {
    title = `${bedrooms} Bedroom ${title}`;
    description = `${bedrooms} bedroom rental properties. ${description}`;
    keywords.push(`${bedrooms} bedroom`);
  }

  if (minPrice || maxPrice) {
    const priceRange = `$${minPrice || 0} - $${maxPrice || 'any'}`;
    keywords.push('affordable rentals', 'price range');
  }

  return generateMetadata({
    title,
    description,
    keywords,
    canonical: '/properties',
    noindex: Object.keys(params).length > 3, // Don't index very specific searches
  });
}

export const DEFAULT_METADATA = generateMetadata({
  title: 'RentHub - Find Your Perfect Rental Property',
  description: 'Discover and rent verified properties worldwide. Browse thousands of apartments, houses, and vacation rentals. Easy booking, trusted reviews, secure payments.',
  keywords: [
    'property rental',
    'apartment rental',
    'house for rent',
    'vacation rental',
    'rental platform',
    'property search',
    'rent property online',
    'verified rentals',
  ],
  canonical: '/',
});
