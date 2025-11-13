'use client';

import { useAuth } from '@/contexts/auth-context';
import { useRouter } from 'next/navigation';
import { useEffect, useState, Suspense } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import dynamic from 'next/dynamic';
import {
  Home,
  DollarSign,
  Calendar,
  Users,
  Plus,
  TrendingUp,
  Eye,
  MessageSquare,
  Star,
  Settings,
  BarChart3,
  Clock,
  CheckCircle,
  AlertCircle,
} from 'lucide-react';
import Link from 'next/link';
import { apiClient } from '@/lib/api-client';
import { toast } from 'sonner';

import { HostStatsCards, HostStats } from '@/components/host/host-stats-cards';
import { HostPropertiesGrid, PropertyItem } from '@/components/host/host-properties-grid';
import { HostBookingsList, BookingItem } from '@/components/host/host-bookings-list';
import { HostLoadingSkeleton } from '@/components/host/host-loading-skeleton';

// Dynamic imports (split heavy UI sections, no SSR needed for purely client visuals)
const DynamicHostStatsCards = dynamic(() => import('@/components/host/host-stats-cards').then(m => m.HostStatsCards), { ssr: false });
const DynamicHostPropertiesGrid = dynamic(() => import('@/components/host/host-properties-grid').then(m => m.HostPropertiesGrid), { ssr: false });
const DynamicHostBookingsList = dynamic(() => import('@/components/host/host-bookings-list').then(m => m.HostBookingsList), { ssr: false });

// Extend stats locally to ensure optional recentBookings is available for type checking even if build cache is stale
interface ExtendedHostStats extends HostStats {
  recentBookings?: BookingItem[];
}

export default function HostDashboardPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [properties, setProperties] = useState<PropertyItem[]>([]);
  const [stats, setStats] = useState<ExtendedHostStats | null>(null);
  const [loadingData, setLoadingData] = useState(true);

  useEffect(() => {
    if (!loading && !user) {
      router.push('/auth/login');
      return;
    }

    if (user) {
      loadHostData();
    }
  }, [user, loading, router]);

  const loadHostData = async () => {
    try {
      const [propertiesResp, statsResp] = await Promise.all([
        apiClient.get('/api/host/properties'),
        apiClient.get('/api/host/stats'),
      ]);

      setProperties((propertiesResp.data as PropertyItem[]) || []);
  const statsData = statsResp.data as ExtendedHostStats;
      setStats(statsData || null);
    } catch (error) {
      toast.error('Failed to load dashboard data');
    } finally {
      setLoadingData(false);
    }
  };

  if (loading || loadingData) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-12">
          <HostLoadingSkeleton />
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold mb-2">Host Dashboard</h1>
            <p className="text-muted-foreground">
              Manage your properties, bookings, and earnings
            </p>
          </div>
          <Button asChild>
            <Link href="/host/properties/new">
              <Plus className="mr-2 h-4 w-4" />
              Add Property
            </Link>
          </Button>
        </div>

        {/* Stats Overview (dynamically loaded) */}
        <Suspense fallback={<div className="h-32" />}>
          <DynamicHostStatsCards stats={stats} />
        </Suspense>

        {/* Main Content */}
        <Tabs defaultValue="properties" className="space-y-6">
          <TabsList>
            <TabsTrigger value="properties">My Properties</TabsTrigger>
            <TabsTrigger value="bookings">Bookings</TabsTrigger>
            <TabsTrigger value="calendar">Calendar</TabsTrigger>
            <TabsTrigger value="earnings">Earnings</TabsTrigger>
          </TabsList>

          <TabsContent value="properties" className="space-y-6">
            <Suspense fallback={<div className="h-64 bg-muted rounded" />}>
              <DynamicHostPropertiesGrid properties={properties} />
            </Suspense>
          </TabsContent>

          <TabsContent value="bookings">
            <Suspense fallback={<div className="h-64 bg-muted rounded" />}>
              <DynamicHostBookingsList bookings={stats?.recentBookings as BookingItem[] | undefined} />
            </Suspense>
          </TabsContent>

          <TabsContent value="calendar">
            <Card>
              <CardHeader>
                <CardTitle>Booking Calendar</CardTitle>
                <CardDescription>View and manage your availability</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-center text-muted-foreground py-12">
                  Calendar view coming soon...
                </p>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="earnings">
            <Card>
              <CardHeader>
                <CardTitle>Earnings Overview</CardTitle>
                <CardDescription>Track your revenue and payouts</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-center text-muted-foreground py-12">
                  Earnings analytics coming soon...
                </p>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </MainLayout>
  );
}
