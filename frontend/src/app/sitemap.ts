import type { MetadataRoute } from 'next';

// Dynamic sitemap leveraging backend SEO endpoints
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const siteUrl = process.env.NEXT_PUBLIC_SITE_URL || process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000';
  const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';

  // Fetch property URLs (id + updated_at)
  let properties: { id: number; updated_at: string }[] = [];
  try {
    const res = await fetch(`${apiBase}/seo/property-urls`, { next: { revalidate: 600 } });
    if (res.ok) {
      properties = await res.json();
    }
  } catch {}

  const staticPaths: MetadataRoute.Sitemap = [
    { url: `${siteUrl}/`, priority: 1.0, lastModified: new Date(), changeFrequency: 'daily' },
    { url: `${siteUrl}/properties`, priority: 0.9, lastModified: new Date(), changeFrequency: 'daily' },
    { url: `${siteUrl}/auth/login`, priority: 0.3, lastModified: new Date(), changeFrequency: 'monthly' },
    { url: `${siteUrl}/bookings`, priority: 0.5, lastModified: new Date(), changeFrequency: 'daily' },
  ];

  const propertyEntries: MetadataRoute.Sitemap = properties.map(p => ({
    url: `${siteUrl}/properties/${p.id}`,
    lastModified: new Date(p.updated_at),
    changeFrequency: 'weekly',
    priority: 0.8,
  }));

  return [...staticPaths, ...propertyEntries];
}
