import type { Metadata } from 'next';

const siteConfig = {
  name: 'RentHub',
  description: 'Find and book unique accommodations worldwide. Rent apartments, houses, and villas from local hosts.',
  url: process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.international',
  ogImage: '/og-image.jpg',
  links: {
    twitter: 'https://twitter.com/renthub',
    facebook: 'https://facebook.com/renthub',
  },
};

export function generateMetadata({
  title,
  description,
  image,
  noIndex = false,
  path = '',
}: {
  title?: string;
  description?: string;
  image?: string;
  noIndex?: boolean;
  path?: string;
}): Metadata {
  const fullTitle = title ? `${title} | ${siteConfig.name}` : siteConfig.name;
  const fullDescription = description || siteConfig.description;
  const fullImage = image || siteConfig.ogImage;
  const url = `${siteConfig.url}${path}`;

  return {
    title: fullTitle,
    description: fullDescription,
    applicationName: siteConfig.name,
    authors: [{ name: 'RentHub Team' }],
    keywords: ['vacation rental', 'accommodation', 'booking', 'travel', 'apartments', 'houses', 'villas'],
    
    ...(noIndex && {
      robots: {
        index: false,
        follow: false,
      },
    }),

    openGraph: {
      type: 'website',
      locale: 'en_US',
      url,
      siteName: siteConfig.name,
      title: fullTitle,
      description: fullDescription,
      images: [
        {
          url: fullImage,
          width: 1200,
          height: 630,
          alt: fullTitle,
        },
      ],
    },

    twitter: {
      card: 'summary_large_image',
      title: fullTitle,
      description: fullDescription,
      images: [fullImage],
      creator: '@renthub',
    },

    alternates: {
      canonical: url,
    },
  };
}

export { siteConfig };
