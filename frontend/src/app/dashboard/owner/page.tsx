'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
// REMOVED: type { Metadata } - not used in client components
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Link from 'next/link';
import {
  Home,
  Calendar,
  DollarSign,
  Users,
  TrendingUp,
  TrendingDown,
  Plus,
  ArrowRight,
  BarChart,
} from 'lucide-react';

interface PropertySummary {
  id: number;
  title: string;
  status: 'active' | 'inactive';
  bookings: number;
  revenue: number;
}

interface RevenueMonth {
  month: string;
  amount: number;
}

export default function OwnerDashboardPage() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [properties, setProperties] = useState<PropertySummary[]>([]);
  const [revenue, setRevenue] = useState<RevenueMonth[]>([]);
  const [stats, setStats] = useState({ totalProperties: 0, totalBookings: 0, totalRevenue: 0, activeGuests: 0 });

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push('/auth/login');
    }
  }, [isAuthenticated, isLoading, router]);

  useEffect(() => {
    if (!isAuthenticated) return;
    // Mock data - replace with API calls
    setProperties([
      { id: 1, title: 'Downtown Apartment', status: 'active', bookings: 12, revenue: 5400 },
      { id: 2, title: 'Lake House', status: 'active', bookings: 8, revenue: 3200 },
      { id: 3, title: 'City Studio', status: 'inactive', bookings: 0, revenue: 0 },
    ]);
    setRevenue([
      { month: 'Jun', amount: 3200 },
      { month: 'Jul', amount: 4100 },
      { month: 'Aug', amount: 3800 },
      { month: 'Sep', amount: 4500 },
      { month: 'Oct', amount: 4000 },
      { month: 'Nov', amount: 4350 },
    ]);
    setStats({ totalProperties: 3, totalBookings: 20, totalRevenue: 8600, activeGuests: 4 });
    setLoading(false);
  }, [isAuthenticated]);

  if (isLoading) {
    return (
      <MainLayout>
        <div className="container mx-auto p-4">
          <Skeleton className="h-8 w-64 mb-4" />
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            {[1, 2, 3, 4].map(i => <Skeleton key={i} className="h-32" />)}
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!isAuthenticated) return null;

  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Owner Dashboard</h1>
              <p className="text-muted-foreground mt-1">Overview of properties, bookings, and revenue.</p>
            </div>
            <Link href="/properties/new">
              <Button className="gap-2">
                <Plus className="h-4 w-4" />
                Add Property
              </Button>
            </Link>
          </div>
          <p className="sr-only" aria-live="polite">
            {stats.totalProperties} properties, {stats.totalBookings} bookings, {stats.totalRevenue} RON revenue
          </p>
        </div>

        {/* Stats */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Properties</CardTitle>
              <Home className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.totalProperties}</div>
              <p className="text-xs text-muted-foreground">Active listings</p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Bookings</CardTitle>
              <Calendar className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.totalBookings}</div>
              <p className="text-xs text-muted-foreground flex items-center gap-1">
                <TrendingUp className="h-3 w-3 text-green-500" /> +8% from last month
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.totalRevenue.toLocaleString()} RON</div>
              <p className="text-xs text-muted-foreground flex items-center gap-1">
                <TrendingUp className="h-3 w-3 text-green-500" /> +15% from last month
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Active Guests</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.activeGuests}</div>
              <p className="text-xs text-muted-foreground">Currently staying</p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 lg:grid-cols-3 mb-8">
          {/* Properties List */}
          <Card className="lg:col-span-2 animate-fade-in-up" style={{ animationDelay: '320ms' }}>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle>Your Properties</CardTitle>
                <CardDescription>Manage your listings</CardDescription>
              </div>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button size="sm" asChild>
                      <Link href="/host/properties/new">
                        <Plus className="h-4 w-4 mr-2" />
                        Add
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Create new property</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="space-y-3">
                  {[1, 2, 3].map(i => <Skeleton key={i} className="h-20" />)}
                </div>
              ) : (
                <div className="space-y-3">
                  {properties.map((prop, idx) => (
                    <div key={prop.id} className="border rounded-lg p-4 hover:bg-muted/50 transition animate-fade-in-up" style={{ animationDelay: `${idx * 60}ms` }}>
                      <div className="flex items-start justify-between gap-3">
                        <div className="space-y-1 flex-1">
                          <div className="flex items-center gap-2">
                            <h3 className="font-semibold">{prop.title}</h3>
                            <Badge variant={prop.status === 'active' ? 'default' : 'secondary'}>
                              {prop.status}
                            </Badge>
                          </div>
                          <div className="flex gap-4 text-xs text-muted-foreground">
                            <span className="flex items-center gap-1">
                              <Calendar className="h-3 w-3" />
                              {prop.bookings} bookings
                            </span>
                            <span className="flex items-center gap-1">
                              <DollarSign className="h-3 w-3" />
                              {prop.revenue.toLocaleString()} RON
                            </span>
                          </div>
                        </div>
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={`/properties/${prop.id}`}>Manage</Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>Edit property</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Quick Actions */}
          <Card className="animate-fade-in-up" style={{ animationDelay: '400ms' }}>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
              <CardDescription>Common tasks</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/host/properties">
                        <span className="flex items-center gap-2">
                          <Home className="h-4 w-4" />
                          Properties
                        </span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Manage all properties</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/bookings">
                        <span className="flex items-center gap-2">
                          <Calendar className="h-4 w-4" />
                          Bookings
                        </span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View all bookings</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/analytics">
                        <span className="flex items-center gap-2">
                          <BarChart className="h-4 w-4" />
                          Analytics
                        </span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View performance metrics</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardContent>
          </Card>
        </div>

        {/* Revenue Chart */}
        <Card className="animate-fade-in-up" style={{ animationDelay: '480ms' }}>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle className="flex items-center gap-2">
                <TrendingUp className="h-5 w-5" />
                Revenue Trend
              </CardTitle>
              <CardDescription>Last 6 months</CardDescription>
            </div>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button variant="outline" size="sm" asChild>
                    <Link href="/payments/history">Details</Link>
                  </Button>
                </TooltipTrigger>
                <TooltipContent>View payment history</TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </CardHeader>
          <CardContent>
            <div className="h-48 w-full flex items-end gap-2">
              {revenue.map((m, i) => {
                const maxHeight = Math.max(...revenue.map(r => r.amount));
                const height = (m.amount / maxHeight) * 120;
                return (
                  <div key={i} className="flex-1 flex flex-col items-center gap-2 animate-fade-in-up" style={{ animationDelay: `${i * 60}ms` }}>
                    <div className="bg-primary/20 hover:bg-primary/30 transition w-full rounded-t" style={{ height: `${height}px` }} />
                    <span className="text-[10px] text-muted-foreground">{m.amount.toFixed(0)}</span>
                  </div>
                );
              })}
            </div>
            <div className="mt-4 text-xs text-muted-foreground flex justify-between">
              {revenue.map(m => <span key={m.month}>{m.month}</span>)}
            </div>
          </CardContent>
        </Card>
      </main>
    </MainLayout>
  );
}
