'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
import type { Metadata } from 'next';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Link from 'next/link';
import {
  Calendar,
  MapPin,
  Heart,
  Search,
  MessageSquare,
  Clock,
  Home,
  ArrowRight,
} from 'lucide-react';

interface UpcomingTrip {
  id: number;
  property: string;
  location: string;
  checkIn: string;
  checkOut: string;
  status: 'confirmed' | 'pending';
}

interface SavedSearch {
  id: number;
  query: string;
  filters: string;
  count: number;
}

export default function TenantDashboardPage() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [trips, setTrips] = useState<UpcomingTrip[]>([]);
  const [savedSearches, setSavedSearches] = useState<SavedSearch[]>([]);
  const [favoriteCount, setFavoriteCount] = useState(0);

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push('/auth/login');
    }
  }, [isAuthenticated, isLoading, router]);

  useEffect(() => {
    if (!isAuthenticated) return;
    // Mock data - replace with API calls
    setTrips([
      { id: 1, property: 'Downtown Loft', location: 'Bucharest, RO', checkIn: '2025-11-20', checkOut: '2025-11-23', status: 'confirmed' },
      { id: 2, property: 'Seaside Villa', location: 'Constanța, RO', checkIn: '2025-12-15', checkOut: '2025-12-18', status: 'pending' },
    ]);
    setSavedSearches([
      { id: 1, query: '2 bedrooms in Bucharest', filters: '€50-€100/night', count: 12 },
      { id: 2, query: 'Pet-friendly near beach', filters: 'Any price', count: 8 },
    ]);
    setFavoriteCount(5);
    setLoading(false);
  }, [isAuthenticated]);

  if (isLoading) {
    return (
      <MainLayout>
        <div className="container mx-auto p-4">
          <Skeleton className="h-8 w-64 mb-4" />
          <div className="grid gap-6 md:grid-cols-2">
            {[1, 2, 3].map(i => <Skeleton key={i} className="h-48" />)}
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
              <h1 className="text-3xl font-bold tracking-tight">Tenant Dashboard</h1>
              <p className="text-muted-foreground mt-1">Manage your upcoming stays and favorites.</p>
            </div>
            <Link href="/properties">
              <Button className="gap-2">
                <Search className="h-4 w-4" />
                Find Properties
              </Button>
            </Link>
          </div>
          <p className="sr-only" aria-live="polite">
            {trips.length} upcoming trips, {favoriteCount} favorites
          </p>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {/* Upcoming Trips */}
          <Card className="md:col-span-2 animate-fade-in-up">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="h-5 w-5" />
                Upcoming Trips
              </CardTitle>
              <CardDescription>Your confirmed and pending bookings</CardDescription>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="space-y-3">
                  {[1, 2].map(i => <Skeleton key={i} className="h-20" />)}
                </div>
              ) : trips.length === 0 ? (
                <div className="text-center py-8">
                  <Home className="h-12 w-12 text-muted-foreground mx-auto mb-3" />
                  <p className="text-sm text-muted-foreground">No upcoming trips</p>
                  <Button variant="outline" size="sm" className="mt-3" asChild>
                    <Link href="/properties">Browse Properties</Link>
                  </Button>
                </div>
              ) : (
                <div className="space-y-3">
                  {trips.map((trip, idx) => (
                    <div key={trip.id} className="border rounded-lg p-4 hover:bg-muted/50 transition animate-fade-in-up" style={{ animationDelay: `${idx * 60}ms` }}>
                      <div className="flex items-start justify-between gap-3">
                        <div className="space-y-1 flex-1">
                          <div className="flex items-center gap-2">
                            <h3 className="font-semibold">{trip.property}</h3>
                            <Badge variant={trip.status === 'confirmed' ? 'default' : 'secondary'}>
                              {trip.status}
                            </Badge>
                          </div>
                          <p className="text-sm text-muted-foreground flex items-center gap-1">
                            <MapPin className="h-3 w-3" />
                            {trip.location}
                          </p>
                          <p className="text-xs text-muted-foreground flex items-center gap-1">
                            <Clock className="h-3 w-3" />
                            {new Date(trip.checkIn).toLocaleDateString()} - {new Date(trip.checkOut).toLocaleDateString()}
                          </p>
                        </div>
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={`/bookings/${trip.id}`}>View</Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>View booking details</TooltipContent>
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
          <Card className="animate-fade-in-up" style={{ animationDelay: '120ms' }}>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
              <CardDescription>Common tasks</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/favorites">
                        <span className="flex items-center gap-2">
                          <Heart className="h-4 w-4" />
                          Favorites ({favoriteCount})
                        </span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View saved properties</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/messages">
                        <span className="flex items-center gap-2">
                          <MessageSquare className="h-4 w-4" />
                          Messages
                        </span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Open inbox</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/bookings">
                        <span className="flex items-center gap-2">
                          <Calendar className="h-4 w-4" />
                          All Bookings
                        </span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View booking history</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardContent>
          </Card>

          {/* Saved Searches */}
          <Card className="md:col-span-2 lg:col-span-3 animate-fade-in-up" style={{ animationDelay: '240ms' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Search className="h-5 w-5" />
                Saved Searches
              </CardTitle>
              <CardDescription>Quick access to your search filters</CardDescription>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="grid gap-3 md:grid-cols-2">
                  {[1, 2].map(i => <Skeleton key={i} className="h-16" />)}
                </div>
              ) : savedSearches.length === 0 ? (
                <p className="text-sm text-muted-foreground">No saved searches yet</p>
              ) : (
                <div className="grid gap-3 md:grid-cols-2">
                  {savedSearches.map((search, idx) => (
                    <div key={search.id} className="border rounded-lg p-3 hover:bg-muted/50 transition animate-fade-in-up" style={{ animationDelay: `${idx * 60}ms` }}>
                      <div className="flex items-start justify-between gap-3">
                        <div className="space-y-1">
                          <h4 className="text-sm font-medium">{search.query}</h4>
                          <p className="text-xs text-muted-foreground">{search.filters}</p>
                          <Badge variant="secondary" className="text-xs">{search.count} results</Badge>
                        </div>
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="ghost" size="sm" asChild>
                                <Link href={`/properties?saved=${search.id}`}>Search</Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>Run this search</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </main>
    </MainLayout>
  );
}
