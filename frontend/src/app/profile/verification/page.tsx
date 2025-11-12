'use client';

import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';

const ProfileVerificationView = dynamic(
  () => import('@/features/profile-verification/components/ProfileVerificationView'),
  { ssr: false, loading: () => <p>Loading...</p> }
);

export default function VerificationPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-4">
        <ProfileVerificationView />
      </main>
    </MainLayout>
  );
}
