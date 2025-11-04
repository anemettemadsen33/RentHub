import { MetadataRoute } from 'next';

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.com';

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const currentDate = new Date();

  // Static routes
  const staticRoutes: MetadataRoute.Sitemap = [
    {
      url: SITE_URL,
      lastModified: currentDate,
      changeFrequency: 'daily',
      priority: 1.0,
    },
    {
      url: `${SITE_URL}/properties`,
      lastModified: currentDate,
      changeFrequency: 'hourly',
      priority: 0.9,
    },
    {
      url: `${SITE_URL}/search`,
      lastModified: currentDate,
      changeFrequency: 'daily',
      priority: 0.8,
    },
    {
      url: `${SITE_URL}/search/map`,
      lastModified: currentDate,
      changeFrequency: 'daily',
      priority: 0.8,
    },
    {
      url: `${SITE_URL}/auth/login`,
      lastModified: currentDate,
      changeFrequency: 'monthly',
      priority: 0.6,
    },
    {
      url: `${SITE_URL}/auth/register`,
      lastModified: currentDate,
      changeFrequency: 'monthly',
      priority: 0.6,
    },
  ];

  // Fetch dynamic property routes
  let propertyRoutes: MetadataRoute.Sitemap = [];
  try {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
    const response = await fetch(`${apiUrl}/api/properties?per_page=1000`, {
      next: { revalidate: 3600 }, // Revalidate every hour
    });
    
    if (response.ok) {
      const data = await response.json();
      const properties = data.data || data;
      
      propertyRoutes = properties.map((property: any) => ({
        url: `${SITE_URL}/properties/${property.id}`,
        lastModified: property.updated_at ? new Date(property.updated_at) : currentDate,
        changeFrequency: 'weekly' as const,
        priority: 0.7,
      }));
    }
  } catch (error) {
    console.error('Error fetching properties for sitemap:', error);
  }

  // Fetch location-based routes
  let locationRoutes: MetadataRoute.Sitemap = [];
  try {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
    const response = await fetch(`${apiUrl}/api/properties/locations`, {
      next: { revalidate: 86400 }, // Revalidate daily
    });
    
    if (response.ok) {
      const locations = await response.json();
      
      locationRoutes = locations.map((location: string) => ({
        url: `${SITE_URL}/properties?location=${encodeURIComponent(location)}`,
        lastModified: currentDate,
        changeFrequency: 'daily' as const,
        priority: 0.8,
      }));
    }
  } catch (error) {
    console.error('Error fetching locations for sitemap:', error);
  }

  return [...staticRoutes, ...propertyRoutes, ...locationRoutes];
}
