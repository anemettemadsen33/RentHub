'use client';

import { use } from 'react';
import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';
import { notFound } from 'next/navigation';

const PropertyAccessView = dynamic(
  () => import('@/features/property-access/components/PropertyAccessView'),
  { ssr: false, loading: () => <p>Loading...</p> }
);

interface Props { params: Promise<{ id: string }> }

export default function PropertyAccessPage({ params }: Props) {
  const { id } = use(params);
  if (!id) {
    notFound();
  }
  const propertyId = parseInt(id, 10);

  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-4">
        <PropertyAccessView propertyId={propertyId} />
      </main>
    </MainLayout>
  );
}
