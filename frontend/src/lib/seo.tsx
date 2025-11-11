import { Property } from '@/types';

export interface JsonLdDataBase {
  '@context': 'https://schema.org';
  '@type': string;
}

export interface JsonLdProperty extends JsonLdDataBase {
  '@type': 'Apartment' | 'House' | 'Residence' | 'LodgingBusiness';
  name: string;
  description: string;
  image?: string[];
  address: {
    '@type': 'PostalAddress';
    streetAddress: string;
    addressLocality: string;
    addressCountry: string;
  };
  geo?: {
    '@type': 'GeoCoordinates';
    latitude: number;
    longitude: number;
  };
  numberOfRooms?: number;
  numberOfBathroomsTotal?: number;
  amenityFeature?: Array<{
    '@type': 'LocationFeatureSpecification';
    name: string;
  }>;
  offers?: {
    '@type': 'Offer';
    price: number;
    priceCurrency: string;
    availability: string;
    validFrom: string;
  };
  url: string;
}

export interface JsonLdOrganization extends JsonLdDataBase {
  '@type': 'Organization';
  name: string;
  url: string;
  logo?: string;
  description?: string;
  sameAs?: string[];
  contactPoint?: {
    '@type': 'ContactPoint';
    contactType: string;
    availableLanguage?: string[];
  };
}

export interface JsonLdWebsite extends JsonLdDataBase {
  '@type': 'WebSite';
  name: string;
  url: string;
  potentialAction?: {
    '@type': 'SearchAction';
    target: {
      '@type': 'EntryPoint';
      urlTemplate: string;
    };
    'query-input': string;
  };
}

export interface JsonLdBreadcrumb extends JsonLdDataBase {
  '@type': 'BreadcrumbList';
  itemListElement: Array<{
    '@type': 'ListItem';
    position: number;
    name: string;
    item: string;
  }>;
}

export type JsonLdData = JsonLdProperty | JsonLdOrganization | JsonLdWebsite | JsonLdBreadcrumb | JsonLdDataBase;

/**
 * Generate JSON-LD structured data for a rental property listing
 */
export function generatePropertyJsonLd(property: Property, baseUrl: string): JsonLdProperty {
  const images = property.images?.map(img => 
    img.startsWith('http') ? img : `${baseUrl}${img}`
  ) || [];

  return {
    '@context': 'https://schema.org',
    '@type': 'Apartment',
    name: property.title,
    description: property.description || property.title,
    image: images,
    address: {
      '@type': 'PostalAddress',
      streetAddress: property.address,
      addressLocality: property.city,
      addressCountry: property.country || 'US',
    },
    geo: property.latitude && property.longitude ? {
      '@type': 'GeoCoordinates',
      latitude: property.latitude,
      longitude: property.longitude,
    } : undefined,
    numberOfRooms: property.bedrooms,
    numberOfBathroomsTotal: property.bathrooms,
    amenityFeature: property.amenities?.map(a => (typeof a === 'string' ? a : a?.name))
      .filter(Boolean)
      .map(amenity => ({
      '@type': 'LocationFeatureSpecification',
      name: amenity,
    })) || [],
    offers: {
      '@type': 'Offer',
      price: property.price,
      priceCurrency: property.currency || 'USD',
      availability: property.status === 'available' 
        ? 'https://schema.org/InStock'
        : 'https://schema.org/OutOfStock',
      validFrom: new Date().toISOString(),
    },
    url: `${baseUrl}/properties/${property.id}`,
  };
}

/**
 * Generate JSON-LD for organization (site-wide)
 */
export function generateOrganizationJsonLd(baseUrl: string): JsonLdOrganization {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'RentHub',
    url: baseUrl,
    logo: `${baseUrl}/logo.png`,
    description: 'Find your perfect rental property with RentHub - the modern property rental platform',
    sameAs: [
      // Add social media URLs when available
    ],
    contactPoint: {
      '@type': 'ContactPoint',
      contactType: 'Customer Service',
      availableLanguage: ['English', 'Romanian'],
    },
  };
}

/**
 * Generate JSON-LD for website
 */
export function generateWebsiteJsonLd(baseUrl: string): JsonLdWebsite {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'RentHub',
    url: baseUrl,
    potentialAction: {
      '@type': 'SearchAction',
      target: {
        '@type': 'EntryPoint',
        urlTemplate: `${baseUrl}/properties?search={search_term_string}`,
      },
      'query-input': 'required name=search_term_string',
    },
  };
}

/**
 * Generate JSON-LD for breadcrumb list
 */
export function generateBreadcrumbJsonLd(
  items: Array<{ name: string; url: string }>,
  baseUrl: string
): JsonLdBreadcrumb {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: item.url.startsWith('http') ? item.url : `${baseUrl}${item.url}`,
    })),
  };
}

/**
 * Component to render JSON-LD script tag
 */
export function JsonLd({ data }: { data: JsonLdData | JsonLdData[] }) {
  const jsonData = Array.isArray(data) ? data : [data];
  
  return (
    <>
      {jsonData.map((item, index) => (
        <script
          key={index}
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify(item, null, 2),
          }}
        />
      ))}
    </>
  );
}
