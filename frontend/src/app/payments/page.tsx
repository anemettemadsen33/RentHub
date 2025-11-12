'use client';

import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';

const PaymentsView = dynamic(
  () => import('@/features/payments/components/PaymentsView'),
  { ssr: false, loading: () => <p>Loading...</p> }
);

export default function PaymentsPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-4">
        <PaymentsView />
      </main>
    </MainLayout>
  );
}
