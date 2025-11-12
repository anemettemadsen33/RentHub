'use client';

import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';

const SecurityAuditView = dynamic(
  () => import('@/features/security-audit/components/SecurityAuditView'),
  { ssr: false, loading: () => <p>Loading...</p> }
);

export default function SecurityAuditPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-4">
        <SecurityAuditView />
      </main>
    </MainLayout>
  );
}
