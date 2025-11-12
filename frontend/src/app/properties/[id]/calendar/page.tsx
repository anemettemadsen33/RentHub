'use client';

import React from 'react';
import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';
import { notFound } from 'next/navigation';

const PropertyCalendarView = dynamic(
  () => import('@/features/property-calendar/components/PropertyCalendarView'),
  { ssr: false, loading: () => <p>Loading...</p> }
);

interface Props { params: Promise<{ id: string }> }

async function getParams(params: Promise<{ id: string }>) {
  return await params;
}

export default function PropertyCalendarPage({ params }: Props) {
  const [propertyId, setPropertyId] = React.useState<number | null>(null);
  
  React.useEffect(() => {
    getParams(params).then(({ id }) => {
      if (!id) {
        notFound();
      }
      setPropertyId(parseInt(id, 10));
    });
  }, [params]);
  
  if (propertyId === null) {
    return <p>Loading...</p>;
  }

  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-4">
        <PropertyCalendarView propertyId={propertyId} />
      </main>
    </MainLayout>
  );
}
