'use client';

import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';

const InvoicesView = dynamic(
  () => import('@/features/invoices/components/InvoicesView'),
  { ssr: false, loading: () => <p>Loading...</p> }
);

export default function InvoicesPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-4">
        <InvoicesView />
      </main>
    </MainLayout>
  );
}
