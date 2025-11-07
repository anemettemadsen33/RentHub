'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { bookingsApi, Booking } from '@/lib/api/bookings';
import { useAuth } from '@/contexts/AuthContext';
import Link from 'next/link';
import { Header } from '@/components/layout/Header';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Skeleton } from '@/components/ui/skeleton';
import { Calendar, MapPin, Users, DollarSign, AlertCircle, Clock, CheckCircle2 } from 'lucide-react';

export default function MyBookingsPage() {
  const router = useRouter();
  const { user } = useAuth();
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filter, setFilter] = useState<'all' | 'pending' | 'confirmed' | 'cancelled'>('all');

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    fetchBookings();
  }, [filter, user]);

  const fetchBookings = async () => {
    setLoading(true);
    setError('');

    try {
      const params = filter !== 'all' ? { status: filter } : {};
      const response = await bookingsApi.getMy(params);

      if (response.data.success) {
        setBookings(response.data.data);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to load bookings');
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = async (id: number) => {
    if (!confirm('Are you sure you want to cancel this booking?')) return;

    try {
      await bookingsApi.cancel(id);
      fetchBookings();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to cancel booking');
    }
  };

  const getStatusBadge = (status: string) => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
      pending: 'secondary',
      confirmed: 'default',
      cancelled: 'destructive',
      completed: 'outline',
    };

    const icons: Record<string, React.ReactNode> = {
      pending: <Clock className="h-3 w-3 mr-1" />,
      confirmed: <CheckCircle2 className="h-3 w-3 mr-1" />,
      cancelled: <AlertCircle className="h-3 w-3 mr-1" />,
      completed: <CheckCircle2 className="h-3 w-3 mr-1" />,
    };

    return (
      <Badge variant={variants[status] || 'default'} className="flex items-center w-fit">
        {icons[status]}
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  const getPaymentBadge = (status: string) => {
    const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
      unpaid: 'secondary',
      paid: 'default',
      refunded: 'outline',
    };

    return (
      <Badge variant={variants[status] || 'default'}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-background">
        <Header />
        <div className="container mx-auto px-4 py-16">
          <Skeleton className="h-10 w-64 mb-4" />
          <Skeleton className="h-4 w-96 mb-8" />
          <div className="grid gap-4">
            {[1, 2, 3].map((i) => (
              <Card key={i}>
                <CardContent className="p-6">
                  <div className="flex gap-6">
                    <Skeleton className="h-32 w-48 rounded-lg" />
                    <div className="flex-1 space-y-3">
                      <Skeleton className="h-6 w-3/4" />
                      <Skeleton className="h-4 w-1/2" />
                      <Skeleton className="h-4 w-1/3" />
                    </div>
                  </div>
                </CardContent>
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
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold mb-2">My Bookings</h1>
          <p className="text-lg text-muted-foreground">View and manage your property bookings</p>
        </div>

        {/* Filters with Tabs */}
        <Tabs value={filter} onValueChange={(v) => setFilter(v as any)} className="mb-8">
          <TabsList className="grid w-full max-w-md grid-cols-4">
            <TabsTrigger value="all">All</TabsTrigger>
            <TabsTrigger value="pending">Pending</TabsTrigger>
            <TabsTrigger value="confirmed">Confirmed</TabsTrigger>
            <TabsTrigger value="cancelled">Cancelled</TabsTrigger>
          </TabsList>
        </Tabs>

        {/* Error Message */}
        {error && (
          <Alert variant="destructive" className="mb-6">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

        {/* Bookings List */}
        {bookings.length === 0 ? (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-16">
              <Calendar className="h-20 w-20 text-muted-foreground mb-4" />
              <h3 className="text-2xl font-semibold mb-2">No bookings found</h3>
              <p className="text-muted-foreground mb-6 text-center">You haven't made any bookings yet. Start exploring properties!</p>
              <Link href="/properties">
                <Button size="lg">
                  Browse Properties
                </Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {bookings.map((booking) => (
              <div key={booking.id} className="bg-white rounded-lg shadow hover:shadow-md transition p-6">
                <div className="flex flex-col md:flex-row gap-6">
                  {/* Property Image */}
                  <div className="flex-shrink-0">
                    {booking.property?.main_image ? (
                      <img
                        src={booking.property.main_image}
                        alt={booking.property.title}
                        className="w-full md:w-48 h-32 object-cover rounded-lg"
                      />
                    ) : (
                      <div className="w-full md:w-48 h-32 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg className="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                      </div>
                    )}
                  </div>

                  {/* Booking Details */}
                  <div className="flex-1">
                    <div className="flex items-start justify-between mb-2">
                      <div>
                        <h3 className="text-xl font-bold text-gray-900 mb-1">
                          {booking.property?.title || 'Property'}
                        </h3>
                        <p className="text-gray-600 text-sm">
                          {booking.property?.city}, {booking.property?.country}
                        </p>
                      </div>
                      <div className="flex gap-2">
                        {getStatusBadge(booking.status)}
                        {getPaymentBadge(booking.payment_status)}
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                      <div>
                        <p className="text-sm text-gray-600">Check-in</p>
                        <p className="font-semibold">{formatDate(booking.check_in)}</p>
                      </div>
                      <div>
                        <p className="text-sm text-gray-600">Check-out</p>
                        <p className="font-semibold">{formatDate(booking.check_out)}</p>
                      </div>
                      <div>
                        <p className="text-sm text-gray-600">Guests</p>
                        <p className="font-semibold">{booking.guests} {booking.guests === 1 ? 'guest' : 'guests'}</p>
                      </div>
                    </div>

                    <div className="flex items-center justify-between mt-4 pt-4 border-t">
                      <div>
                        <p className="text-sm text-gray-600">Total Amount</p>
                        <p className="text-2xl font-bold text-blue-600">${booking.total_amount.toFixed(2)}</p>
                      </div>

                      <div className="flex gap-2">
                        <Link
                          href={`/bookings/${booking.id}`}
                          className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                        >
                          View Details
                        </Link>

                        {booking.status === 'pending' && (
                          <button
                            onClick={() => handleCancel(booking.id)}
                            className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium"
                          >
                            Cancel
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
