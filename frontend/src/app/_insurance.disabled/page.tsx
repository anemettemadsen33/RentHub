'use client';

import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';
import { TooltipProvider } from '@/components/ui/tooltip';

const InsuranceView = dynamic(
  () => import('@/features/insurance/components/InsuranceView'),
  { ssr: false, loading: () => <p>Loading...</p> }
);

export default function InsurancePage() {
  return (
  <TooltipProvider>
    <MainLayout>
  <main id="main-content" role="main" className="container mx-auto p-4 animate-fade-in-up" style={{ animationDelay: '0ms' }}>
        <InsuranceView />
      </main>
    </MainLayout>
  </TooltipProvider>
  );
}
