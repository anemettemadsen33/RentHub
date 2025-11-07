'use client';

import { useEffect, useState } from 'react';
import { ownerDashboardApi } from '@/lib/api/dashboard';
import Link from 'next/link';
import { Header } from '@/components/layout/Header';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Home, Calendar, DollarSign, Star, TrendingUp, Building2, Users, CheckCircle2 } from 'lucide-react';

interface DashboardStats {
  total_properties?: number;
  active_properties?: number;
  total_bookings?: number;
  active_bookings?: number;
  pending_bookings?: number;
  total_revenue?: number;
  average_rating?: number;
  occupancy_rate?: number;
}

export default function OwnerDashboardPage() {
  const [stats, setStats] = useState<DashboardStats>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    try {
      const response: any = await ownerDashboardApi.getOverview();
      setStats(response.data || {});
    } catch (err: any) {
      setError('Failed to load dashboard data');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-background">
        <Header />
        <div className="container mx-auto px-4 py-8">
          <Skeleton className="h-10 w-80 mb-4" />
          <Skeleton className="h-4 w-96 mb-8" />
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {[1, 2, 3, 4].map((i) => (
              <Card key={i}>
                <CardHeader className="pb-3">
                  <Skeleton className="h-4 w-24 mb-2" />
                  <Skeleton className="h-8 w-16" />
                </CardHeader>
              </Card>
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      <Header />
      
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-8 flex items-center justify-between">
          <div>
            <h1 className="text-4xl font-bold mb-2">Owner Dashboard</h1>
            <p className="text-lg text-muted-foreground">Manage your properties and view performance metrics</p>
          </div>
          <Link href="/owner/properties/create">
            <Button size="lg" className="shadow-lg">
              <Building2 className="h-5 w-5 mr-2" />
              Add Property
            </Button>
          </Link>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card className="border-2 hover:border-primary/50 transition-colors">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">Total Properties</CardTitle>
                <div className="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                  <Building2 className="h-5 w-5 text-blue-600" />
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">{stats.total_properties || 0}</div>
              <p className="text-xs text-muted-foreground mt-1">
                {stats.active_properties || 0} active
              </p>
            </CardContent>
          </Card>

          <Card className="border-2 hover:border-primary/50 transition-colors">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">Active Bookings</CardTitle>
                <div className="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                  <CheckCircle2 className="h-5 w-5 text-green-600" />
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">{stats.active_bookings || 0}</div>
              <p className="text-xs text-muted-foreground mt-1">
                {stats.pending_bookings || 0} pending
              </p>
            </CardContent>
          </Card>

          <Card className="border-2 hover:border-primary/50 transition-colors">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">Total Revenue</CardTitle>
                <div className="h-10 w-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                  <DollarSign className="h-5 w-5 text-yellow-600" />
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">${stats.total_revenue?.toLocaleString() || 0}</div>
              <p className="text-xs text-muted-foreground mt-1 flex items-center">
                <TrendingUp className="h-3 w-3 mr-1" />
                This month
              </p>
            </CardContent>
          </Card>

          <Card className="border-2 hover:border-primary/50 transition-colors">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm font-medium text-muted-foreground">Average Rating</CardTitle>
                <div className="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                  <Star className="h-5 w-5 text-purple-600 fill-purple-600" />
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">{stats.average_rating?.toFixed(1) || '0.0'}</div>
              <p className="text-xs text-muted-foreground mt-1">
                Out of 5.0
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Quick Actions */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>Manage your properties and bookings</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Link href="/owner/properties/new">
                <Card className="cursor-pointer hover:bg-accent transition-colors border-2">
                  <CardContent className="flex items-center p-4">
                    <div className="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                      <Building2 className="h-6 w-6 text-blue-600" />
                    </div>
                    <div className="ml-4">
                      <p className="font-semibold">Add Property</p>
                      <p className="text-sm text-muted-foreground">List a new property</p>
                    </div>
                  </CardContent>
                </Card>
              </Link>

              <Link href="/owner/properties">
                <Card className="cursor-pointer hover:bg-accent transition-colors border-2">
                  <CardContent className="flex items-center p-4">
                    <div className="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                      <Home className="h-6 w-6 text-green-600" />
                    </div>
                    <div className="ml-4">
                      <p className="font-semibold">Manage Properties</p>
                      <p className="text-sm text-muted-foreground">View all properties</p>
                    </div>
                  </CardContent>
                </Card>
              </Link>

              <Link href="/bookings">
                <Card className="cursor-pointer hover:bg-accent transition-colors border-2">
                  <CardContent className="flex items-center p-4">
                    <div className="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                      <Calendar className="h-6 w-6 text-purple-600" />
                    </div>
                    <div className="ml-4">
                      <p className="font-semibold">View Bookings</p>
                      <p className="text-sm text-muted-foreground">Manage reservations</p>
                    </div>
                  </CardContent>
                </Card>
              </Link>
            </div>
          </CardContent>
        </Card>

        {/* Performance Metrics */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Performance Metrics</CardTitle>
              <CardDescription>Your property performance overview</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex justify-between items-center py-2">
                  <span className="text-muted-foreground">Average Rating</span>
                  <span className="font-semibold flex items-center">
                    <Star className="h-4 w-4 text-yellow-400 fill-yellow-400 mr-1" />
                    {stats.average_rating?.toFixed(1) || 'N/A'}
                  </span>
                </div>
                <div className="flex justify-between items-center py-2">
                  <span className="text-muted-foreground">Occupancy Rate</span>
                  <Badge variant="secondary" className="font-semibold">
                    {stats.occupancy_rate?.toFixed(0) || 0}%
                  </Badge>
                </div>
                <div className="flex justify-between items-center py-2">
                  <span className="text-muted-foreground">Active Properties</span>
                  <span className="font-semibold">{stats.active_properties || 0}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Recent Activity</CardTitle>
              <CardDescription>Latest updates and notifications</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col items-center justify-center py-8 text-center">
                <Users className="h-12 w-12 text-muted-foreground mb-4" />
                <p className="text-muted-foreground">No recent activity</p>
                <p className="text-sm text-muted-foreground mt-1">Activity will appear here</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
