'use client';

import { useAuth } from '@/contexts/auth-context';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
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

export default function HostDashboardPage() {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [properties, setProperties] = useState([]);
  const [stats, setStats] = useState(null);
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
      const [propertiesData, statsData] = await Promise.all([
        apiClient.get('/api/host/properties'),
        apiClient.get('/api/host/stats'),
      ]);

      setProperties(propertiesData.data || []);
      setStats(statsData.data || {});

      interface Property {
        id: number;
        title: string;
        description: string;
        images: string[];
        status: 'active' | 'inactive';
        views: number;
        rating: number;
      }

      interface HostStats {
        totalEarnings: number;
        earningsGrowth: number;
        activeProperties: number;
        totalProperties: number;
        upcomingBookings: number;
        totalBookings: number;
        averageRating: number;
        totalReviews: number;
        recentBookings?: Array<{
          id: number;
          propertyTitle: string;
          guestName: string;
          checkIn: string;
          checkOut: string;
          total: number;
        }>;
      }

    } catch (error) {
        const { user, isLoading: loading } = useAuth();
      toast.error('Failed to load dashboard data');
        const [properties, setProperties] = useState<Property[]>([]);
        const [stats, setStats] = useState<HostStats | null>(null);
    }
  };

  if (loading || loadingData) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-12">
          <div className="animate-pulse space-y-8">
            <div className="h-8 bg-gray-200 rounded w-1/3"></div>
            <div className="grid md:grid-cols-4 gap-6">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="h-32 bg-gray-200 rounded"></div>
              ))}
            </div>
          </div>
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

        {/* Stats Overview */}
        <div className="grid md:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Total Earnings</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                ${stats?.totalEarnings?.toLocaleString() || '0'}
              </div>
              <p className="text-xs text-muted-foreground flex items-center gap-1">
                <TrendingUp className="h-3 w-3 text-green-500" />
                +{stats?.earningsGrowth || 0}% this month
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Active Properties</CardTitle>
              <Home className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats?.activeProperties || 0}</div>
              <p className="text-xs text-muted-foreground">
                {stats?.totalProperties || 0} total listings
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Upcoming Bookings</CardTitle>
              <Calendar className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats?.upcomingBookings || 0}</div>
              <p className="text-xs text-muted-foreground">
                {stats?.totalBookings || 0} total bookings
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">Avg Rating</CardTitle>
              <Star className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold flex items-center gap-1">
                {stats?.averageRating || '0.0'}
                <Star className="h-5 w-5 fill-yellow-400 text-yellow-400" />
              </div>
              <p className="text-xs text-muted-foreground">
                {stats?.totalReviews || 0} reviews
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Main Content */}
        <Tabs defaultValue="properties" className="space-y-6">
          <TabsList>
            <TabsTrigger value="properties">My Properties</TabsTrigger>
            <TabsTrigger value="bookings">Bookings</TabsTrigger>
            <TabsTrigger value="calendar">Calendar</TabsTrigger>
            <TabsTrigger value="earnings">Earnings</TabsTrigger>
          </TabsList>

          <TabsContent value="properties" className="space-y-6">
            {properties.length === 0 ? (
              <Card>
                <CardContent className="flex flex-col items-center justify-center py-12">
                  <Home className="h-12 w-12 text-muted-foreground mb-4" />
                  <h3 className="text-lg font-semibold mb-2">No properties yet</h3>
                  <p className="text-muted-foreground mb-4">Start by adding your first property</p>
                  <Button asChild>
                    <Link href="/host/properties/new">
                      <Plus className="mr-2 h-4 w-4" />
                      Add Your First Property
                    </Link>
                  </Button>
                </CardContent>
              </Card>
            ) : (
              <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                {properties.map((property) => (
                  <Card key={property.id} className="overflow-hidden">
                    <div className="aspect-video bg-gray-200 relative">
                      {property.images?.[0] && (
                        <img
                          src={property.images[0]}
                          alt={property.title}
                          className="w-full h-full object-cover"
                        />
                      )}
                      <div className="absolute top-2 right-2">
                        {property.status === 'active' ? (
                          <span className="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium">
                            Active
                          </span>
                        ) : (
                          <span className="bg-gray-500 text-white px-2 py-1 rounded text-xs font-medium">
                            Inactive
                          </span>
                        )}
                      </div>
                    </div>
                    <CardHeader>
                      <CardTitle className="line-clamp-1">{property.title}</CardTitle>
                      <CardDescription className="line-clamp-2">
                        {property.description}
                      </CardDescription>
                    </CardHeader>
                    <CardContent>
                      <div className="flex items-center justify-between mb-4">
                        <div className="flex items-center gap-1 text-sm">
                          <Eye className="h-4 w-4" />
                          {property.views || 0} views
                        </div>
                        <div className="flex items-center gap-1 text-sm">
                          <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                          {property.rating || '0.0'}
                        </div>
                      </div>
                      <div className="flex gap-2">
                        <Button asChild variant="outline" size="sm" className="flex-1">
                          <Link href={`/host/properties/${property.id}`}>
                            <Settings className="mr-2 h-4 w-4" />
                            Manage
                          </Link>
                        </Button>
                        <Button asChild variant="outline" size="sm" className="flex-1">
                          <Link href={`/properties/${property.id}`}>
                            <Eye className="mr-2 h-4 w-4" />
                            View
                          </Link>
                        </Button>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </TabsContent>

          <TabsContent value="bookings">
            <Card>
              <CardHeader>
                <CardTitle>Recent Bookings</CardTitle>
                <CardDescription>Manage your property bookings</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {stats?.recentBookings?.map((booking) => (
                    <div
                      key={booking.id}
                      className="flex items-center justify-between p-4 border rounded-lg"
                    >
                      <div className="flex-1">
                        <p className="font-medium">{booking.propertyTitle}</p>
                        <p className="text-sm text-muted-foreground">
                          {booking.guestName} • {booking.checkIn} - {booking.checkOut}
                        </p>
                      </div>
                      <div className="flex items-center gap-4">
                        <span className="font-semibold">${booking.total}</span>
                        <Button variant="outline" size="sm">
                          View Details
                        </Button>
                      </div>
                    </div>
                  )) || (
                    <p className="text-center text-muted-foreground py-8">
                      No bookings yet
                    </p>
                  )}
                </div>
              </CardContent>
            </Card>
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
