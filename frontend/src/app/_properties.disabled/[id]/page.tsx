import type { Metadata } from 'next';
import { JsonLd, generatePropertyJsonLd, generateBreadcrumbJsonLd } from '@/lib/seo';
import { notFound } from 'next/navigation';
import { cache } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { breadcrumbSets } from '@/lib/breadcrumbs';
import { useTranslations } from 'next-intl';
import PropertyDetailClient from '@/app/properties/[id]/property-client';

// --- SEO DATA FETCHING (server side) ---
interface PropertySeoMetadata {
  id: number;
  title: string;
  description: string;
  price: number;
  location: {
    address: string | null;
    city: string | null;
    state: string | null;
    country: string | null;
    postalCode: string | null;
  };
  images: string[];
  bedrooms: number | null;
  bathrooms: number | null;
  area: number | null;
  amenities: string[];
  rating: number | null;
  reviewCount: number;
  updated_at: string;
}

const fetchSeoMetadata = cache(async (id: string): Promise<PropertySeoMetadata | null> => {
  try {
    const base = process.env.NEXT_PUBLIC_API_BASE_URL || process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';
    const res = await fetch(`${base}/properties/${id}`, { next: { revalidate: 600 } });
    if (!res.ok) return null;
    const data = await res.json();
    
    // Transform API response to SEO metadata format
    return {
      id: data.data.id,
      title: data.data.title,
      description: data.data.description,
      price: parseFloat(data.data.price),
      location: {
        address: data.data.street_address,
        city: data.data.city,
        state: data.data.state,
        country: data.data.country,
        postalCode: data.data.postal_code,
      },
      images: data.data.images?.map((img: any) => img.image_url) || [],
      bedrooms: data.data.bedrooms,
      bathrooms: data.data.bathrooms,
      area: data.data.area_sqm,
      amenities: data.data.amenities?.map((a: any) => a.name) || [],
      rating: data.data.average_rating || null,
      reviewCount: data.data.reviews_count || 0,
      updated_at: data.data.updated_at,
    };
  } catch {
    return null;
  }
});

// Using App Router PageProps pattern: params must be awaited in Next.js 15
export async function generateMetadata({ params }: any): Promise<Metadata> {
  const resolvedParams = await params;
  const data = await fetchSeoMetadata(resolvedParams.id);
  const siteUrl = process.env.NEXT_PUBLIC_SITE_URL || process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000';
  if (!data) {
    return {
      title: 'Property Not Found',
      description: 'The requested property could not be found',
      robots: { index: false }
    };
  }
  const primaryImage = data.images?.[0] || '/images/og-default.png';
  const title = `${data.title} | ${data.location.city || ''} ${data.location.country || ''}`.trim();
  const description = data.description?.slice(0, 155) || 'View property details on RentHub';
  const canonical = `${siteUrl}/properties/${data.id}`;
  return {
    title,
    description,
    alternates: { canonical },
    openGraph: {
      type: 'website',
      url: canonical,
      title,
      description,
      images: [
        { url: primaryImage, width: 1200, height: 630, alt: data.title }
      ]
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
      images: [primaryImage]
    }
  };
}

function PropertyJsonLd({ data }: { data: PropertySeoMetadata }) {
  const siteUrl = process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000';
  const canonical = `${siteUrl}/properties/${data.id}`;
  
  // Convert SEO metadata to Property format for utility function
  const propertyData = {
    id: data.id,
    title: data.title,
    description: data.description,
    price: data.price as any, // adapter preserves field but schema expects .price; currency added below
    price_per_night: data.price as any,
    currency: 'USD',
    type: 'apartment',
  address: data.location.address || '',
  city: data.location.city || '',
  country: data.location.country || '',
    latitude: undefined,
    longitude: undefined,
    status: 'available',
    bedrooms: data.bedrooms || 0,
    bathrooms: data.bathrooms || 0,
    max_guests: 0,
    image_url: data.images?.[0],
    images: data.images,
    amenities: data.amenities,
    rating: data.rating ?? undefined,
    review_count: data.reviewCount,
    created_at: data.updated_at,
    updated_at: data.updated_at,
  };
  
  const breadcrumbItems = [
    { name: 'Home', url: siteUrl },
    { name: 'Properties', url: `${siteUrl}/properties` },
    { name: data.title, url: canonical },
  ];
  
  return (
    <JsonLd 
      data={[
        generatePropertyJsonLd(propertyData as any, siteUrl),
        generateBreadcrumbJsonLd(breadcrumbItems, siteUrl),
      ]} 
    />
  );
}

export default async function PropertyDetailPage(props: { params: Promise<{ id: string }> }) {
  const params = await props.params;
  const id = params.id;
  const seo = await fetchSeoMetadata(id);
  if (!seo) return notFound();
  return (
    <MainLayout>
      <div className="container mx-auto px-4 pt-6">
        <Breadcrumbs items={breadcrumbSets.propertyRoot(seo.title)} />
      </div>
      <PropertyJsonLd data={seo} />
      <PropertyDetailClient id={id} />
    </MainLayout>
  );
}
