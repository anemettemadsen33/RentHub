'use client';

import { useAuth } from '@/contexts/auth-context';
import { useRouter } from 'next/navigation';
import { useEffect, useState, Suspense } from 'react';
import dynamic from 'next/dynamic';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { AdminLoadingSkeleton } from '@/components/admin/loading-skeleton';

// Dynamic imports for heavy components
const AdminStatsCards = dynamic(
  () => import('@/components/admin/stats-cards').then(mod => ({ default: mod.AdminStatsCards })),
  { 
    loading: () => <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 animate-pulse">
      {[1,2,3,4].map(i => <div key={i} className="bg-gray-200 h-32 rounded-lg" />)}
    </div>,
    ssr: false
  }
);

const AdminManagementTabs = dynamic(
  () => import('@/components/admin/management-tabs').then(mod => ({ default: mod.AdminManagementTabs })),
  { 
    loading: () => <div className="bg-gray-200 h-96 rounded-lg animate-pulse" />,
    ssr: false
  }
);

export default function AdminDashboardPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [stats, setStats] = useState({
    totalUsers: 2543,
    activeUsers: 184,
    totalRevenue: 45231,
    monthlyGrowth: 12.5
  });

  useEffect(() => {
    if (!loading && (!user || user.role !== 'admin')) {
      router.push('/');
    }
  }, [user, loading, router]);

  if (loading) {
    return (
      <MainLayout>
        <AdminLoadingSkeleton />
      </MainLayout>
    );
  }

  if (!user || user.role !== 'admin') {
    return null;
  }

  return (
    <MainLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">Admin Dashboard</h1>
            <p className="text-muted-foreground">Platform management and analytics</p>
          </div>
        </div>

        <Suspense fallback={<AdminLoadingSkeleton />}>
          <AdminStatsCards stats={stats} />
        </Suspense>

        <div className="grid gap-6 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Recent Activity</CardTitle>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>System Health</CardTitle>
            </CardHeader>
          </Card>
        </div>

        <Suspense fallback={<div className="bg-gray-200 h-96 rounded-lg animate-pulse" />}>
          <AdminManagementTabs />
        </Suspense>
      </div>
    </MainLayout>
  );
}
