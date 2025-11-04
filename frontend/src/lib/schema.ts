import { Thing, WithContext, Organization, WebSite, BreadcrumbList, Product } from 'schema-dts';

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.com';

export function getOrganizationSchema(): WithContext<Organization> {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'RentHub',
    url: SITE_URL,
    logo: `${SITE_URL}/logo.png`,
    description: 'Leading property rental platform for finding and booking rental properties worldwide',
    contactPoint: {
      '@type': 'ContactPoint',
      telephone: '+1-555-RENTHUB',
      contactType: 'customer service',
      areaServed: 'US',
      availableLanguage: ['en', 'es', 'fr'],
    },
    sameAs: [
      'https://www.facebook.com/renthub',
      'https://www.twitter.com/renthub',
      'https://www.instagram.com/renthub',
      'https://www.linkedin.com/company/renthub',
    ],
  };
}

export function getWebsiteSchema() {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'RentHub',
    url: SITE_URL,
    potentialAction: {
      '@type': 'SearchAction',
      target: {
        '@type': 'EntryPoint',
        urlTemplate: `${SITE_URL}/properties?q={search_term_string}`,
      },
      'query-input': 'required name=search_term_string',
    },
  } as WithContext<WebSite>;
}

export function getPropertySchema(property: {
  id: number;
  title: string;
  description: string;
  price: number;
  location: {
    address: string;
    city: string;
    state: string;
    country: string;
    postalCode: string;
  };
  images?: string[];
  bedrooms?: number;
  bathrooms?: number;
  area?: number;
  amenities?: string[];
  rating?: number;
  reviewCount?: number;
}) {
  const {
    id,
    title,
    description,
    price,
    images = [],
    rating,
    reviewCount,
  } = property;

  const schema = {
    '@context': 'https://schema.org' as const,
    '@type': 'Product' as const,
    name: title,
    description,
    image: images.map((img) => (img.startsWith('http') ? img : `${SITE_URL}${img}`)),
    offers: {
      '@type': 'Offer' as const,
      price: String(price),
      priceCurrency: 'USD',
      availability: 'https://schema.org/InStock',
      url: `${SITE_URL}/properties/${id}`,
      priceValidUntil: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    },
    brand: {
      '@type': 'Brand' as const,
      name: 'RentHub',
    },
    category: 'Real Estate Rental',
  };

  if (rating && reviewCount) {
    (schema as Record<string, unknown>).aggregateRating = {
      '@type': 'AggregateRating',
      ratingValue: String(rating),
      reviewCount: String(reviewCount),
      bestRating: '5',
      worstRating: '1',
    };
  }

  return schema as WithContext<Product>;
}

export function getBreadcrumbSchema(items: Array<{ name: string; url: string }>): WithContext<BreadcrumbList> {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: item.url.startsWith('http') ? item.url : `${SITE_URL}${item.url}`,
    })),
  };
}

export function getSearchResultsSchema(
  properties: Array<{
    id: number;
    title: string;
    price: number;
    location: { city: string };
  }>
) {
  return {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    itemListElement: properties.map((property, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      item: {
        '@type': 'Product',
        name: property.title,
        url: `${SITE_URL}/properties/${property.id}`,
        offers: {
          '@type': 'Offer',
          price: String(property.price),
          priceCurrency: 'USD',
        },
      },
    })),
  };
}

export function getFAQSchema(faqs: Array<{ question: string; answer: string }>) {
  return {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqs.map((faq) => ({
      '@type': 'Question',
      name: faq.question,
      acceptedAnswer: {
        '@type': 'Answer',
        text: faq.answer,
      },
    })),
  };
}

export function renderJsonLd(schema: WithContext<Thing> | unknown) {
  return {
    __html: JSON.stringify(schema),
  };
}
