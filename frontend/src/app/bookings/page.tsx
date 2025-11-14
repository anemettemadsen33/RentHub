'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { Booking } from '@/types';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar, MapPin, Users, DollarSign, Clock, CheckCircle, XCircle, Download, Printer, Filter, Search } from 'lucide-react';
import { formatCurrency, formatDate } from '@/lib/utils';
// TEMP: Using simple wrapper instead of next-intl
import { useTranslations } from '@/lib/i18n-temp';
import { useBookings, useCancelBooking, useExportBookings } from '@/hooks/use-bookings';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';

export const dynamic = 'force-dynamic';

export default function BookingsPage() {
  const t = useTranslations('bookingsPage');
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [filter, setFilter] = useState<'all' | 'upcoming' | 'past' | 'cancelled'>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [page, setPage] = useState(1);
  const [limit] = useState(10);

  // Use React Query hooks
  const { data: bookingsData, isLoading, error } = useBookings({ filter, page, limit });
  const cancelBooking = useCancelBooking();
  const exportBookings = useExportBookings();

  const bookings = bookingsData?.data || [];

  if (!user) {
    router.push('/auth/login');
    return null;
  }

  const handleCancelBooking = async (bookingId: number) => {
    try {
      await cancelBooking.mutateAsync(bookingId);
      toast({
        title: t('toasts.cancelSuccess.title'),
        description: t('toasts.cancelSuccess.description'),
      });
    } catch (error: any) {
      toast({
        title: t('toasts.cancelError.title'),
        description: error.response?.data?.message || t('toasts.cancelError.description'),
        variant: 'destructive',
      });
    }
  };

  const handleExport = async (format: 'pdf' | 'csv' | 'excel') => {
    try {
      await exportBookings.mutateAsync(format);
      toast({
        title: 'Export Successful',
        description: `Bookings exported as ${format.toUpperCase()}`,
      });
    } catch (error) {
      toast({
        title: 'Export Failed',
        description: 'Unable to export bookings. Please try again.',
        variant: 'destructive',
      });
    }
  };

  const handlePrint = () => {
    window.print();
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'confirmed':
        return <CheckCircle className="h-5 w-5 text-green-500" aria-label={t('status.confirmed')} />;
      case 'pending':
        return <Clock className="h-5 w-5 text-yellow-500" aria-label={t('status.pending')} />;
      case 'cancelled':
        return <XCircle className="h-5 w-5 text-red-500" aria-label={t('status.cancelled')} />;
      default:
        return <Calendar className="h-5 w-5 text-gray-500" aria-label={t('status.default')} />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'confirmed':
        return 'bg-green-100 text-green-800';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      case 'completed':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const filteredBookings = bookings.filter((booking: Booking) => {
    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      return (
        booking.property?.title?.toLowerCase().includes(query) ||
        booking.property?.address?.toLowerCase().includes(query) ||
        booking.property?.city?.toLowerCase().includes(query)
      );
    }
    return true;
  });

  if (isLoading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="mb-6">
            <div className="h-9 w-48 bg-gray-200 rounded animate-pulse mb-2" />
            <div className="h-5 w-64 bg-gray-200 rounded animate-pulse" />
          </div>
          <div className="space-y-4">
            {[1, 2, 3, 4].map((i) => (
              <Card key={i} className="overflow-hidden">
                <CardContent className="p-0">
                  <div className="md:flex">
                    <div className="md:w-1/3 h-48 md:h-auto bg-gray-200" />
                    <div className="flex-1 p-6">
                      <div className="space-y-3">
                        <div className="h-6 w-3/4 bg-gray-200 rounded animate-pulse" />
                        <div className="h-4 w-1/2 bg-gray-200 rounded animate-pulse" />
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                          {[1, 2, 3, 4].map((j) => (
                            <div key={j} className="space-y-1">
                              <div className="h-3 w-16 bg-gray-200 rounded animate-pulse" />
                              <div className="h-5 w-20 bg-gray-200 rounded animate-pulse" />
                            </div>
                          ))}
                        </div>
                        <div className="flex gap-2">
                          <div className="h-10 w-24 bg-gray-200 rounded animate-pulse" />
                          <div className="h-10 w-20 bg-gray-200 rounded animate-pulse" />
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </MainLayout>
    );
  }

  if (error) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="text-center py-12">
            <h2 className="text-2xl font-bold mb-4">Error Loading Bookings</h2>
            <p className="text-muted-foreground mb-6">Unable to load your bookings. Please try again later.</p>
            <Button onClick={() => typeof window !== 'undefined' && window.location.reload()}>Retry</Button>
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        {/* Header with Actions */}
        <div className="mb-6">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
            <div>
              <h1 className="text-3xl font-bold mb-2">{t('title')}</h1>
              <p className="text-gray-600">{t('subtitle')}</p>
            </div>
            <div className="flex gap-2 mt-4 md:mt-0">
              <Button variant="outline" size="sm" onClick={() => handleExport('pdf')}>
                <Download className="h-4 w-4 mr-2" />
                Export PDF
              </Button>
              <Button variant="outline" size="sm" onClick={() => handleExport('csv')}>
                <Download className="h-4 w-4 mr-2" />
                Export CSV
              </Button>
              <Button variant="outline" size="sm" onClick={handlePrint}>
                <Printer className="h-4 w-4 mr-2" />
                Print
              </Button>
            </div>
          </div>
          <span className="sr-only" aria-live="polite">{t('aria.showing', { count: filteredBookings.length, filter })}</span>
        </div>

        {/* Search and Filter Controls */}
        <div className="flex flex-col md:flex-row gap-4 mb-6">
          <div className="relative flex-1 max-w-md">
            <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
            <Input
              type="text"
              placeholder="Search bookings..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
          <Select value={filter} onValueChange={(value) => setFilter(value as any)}>
            <SelectTrigger className="w-[180px]">
              <Filter className="h-4 w-4 mr-2" />
              <SelectValue placeholder="Filter bookings" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Bookings</SelectItem>
              <SelectItem value="upcoming">Upcoming</SelectItem>
              <SelectItem value="past">Past</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {/* Confirmation Discount Banner */}
        {typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('confirmed') && (() => {
          try {
            const data = JSON.parse(sessionStorage.getItem('last_booking_discounts') || 'null');
            if (!data) return null;
            return (
              <Card className="mb-6 border-green-500">
                <CardHeader>
                  <CardTitle className="text-lg">Booking Confirmed</CardTitle>
                </CardHeader>
                <CardContent className="text-sm space-y-1">
                  <div className="flex justify-between"><span>Subtotal</span><span>${data.subtotal.toFixed(2)}</span></div>
                  {data.referral > 0 && <div className="flex justify-between text-green-700"><span>Referral Discount</span><span>-${data.referral.toFixed(2)}</span></div>}
                  {data.loyalty > 0 && <div className="flex justify-between text-green-700"><span>Loyalty Discount</span><span>-${data.loyalty.toFixed(2)}</span></div>}
                  <div className="flex justify-between font-semibold border-t pt-2"><span>Total</span><span>${data.total.toFixed(2)}</span></div>
                </CardContent>
              </Card>
            );
          } catch { return null; }
        })()}

        {/* Bookings List */}
        {filteredBookings.length === 0 ? (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-12">
              <Calendar className="h-16 w-16 text-gray-300 mb-4" />
              <h3 className="text-xl font-semibold mb-2">No bookings found</h3>
              <p className="text-gray-600 mb-4">
                {searchQuery ? 'No bookings match your search criteria.' : 'You don\'t have any bookings yet.'}
              </p>
              {!searchQuery && (
                <Button onClick={() => router.push('/properties')}>
                  Browse Properties
                </Button>
              )}
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {filteredBookings.map((booking: Booking, idx: number) => (
              <Card key={booking.id} className="overflow-hidden animate-fade-in-up" style={{ animationDelay: `${Math.min(idx, 8) * 40}ms` }}>
                <CardContent className="p-0">
                  <div className="md:flex">
                    {/* Property Image */}
                    <div className="md:w-1/3 h-48 md:h-auto bg-gray-200 relative">
                      {booking.property?.image_url && (
                        <Image
                          src={booking.property.image_url}
                          alt={t('property.imageAlt', { title: booking.property.title })}
                          fill
                          className="object-cover"
                          sizes="(max-width: 768px) 100vw, 33vw"
                          loading="lazy"
                        />
                      )}
                    </div>

                    {/* Booking Details */}
                    <div className="flex-1 p-6">
                      <div className="flex items-start justify-between mb-4">
                        <div>
                          <h3 className="text-xl font-bold mb-1">
                            {booking.property?.title || t('property.unknown')}
                          </h3>
                          <div className="flex items-center text-gray-600 text-sm">
                            <MapPin className="h-4 w-4 mr-1" />
                            <span>{booking.property?.address || t('property.noAddress')}</span>
                          </div>
                        </div>
                        <div className="flex items-center gap-2">
                          {getStatusIcon(booking.status)}
                          <span
                            className={`px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(
                              booking.status
                            )}`}
                          >
                            {booking.status}
                          </span>
                        </div>
                      </div>

                      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div className="flex items-center">
                          <Calendar className="h-5 w-5 mr-2 text-gray-400" />
                          <div>
                            <p className="text-xs text-gray-600">{t('fields.checkIn')}</p>
                            <p className="font-semibold">{formatDate(booking.check_in)}</p>
                          </div>
                        </div>
                        <div className="flex items-center">
                          <Calendar className="h-5 w-5 mr-2 text-gray-400" />
                          <div>
                            <p className="text-xs text-gray-600">{t('fields.checkOut')}</p>
                            <p className="font-semibold">{formatDate(booking.check_out)}</p>
                          </div>
                        </div>
                        <div className="flex items-center">
                          <Users className="h-5 w-5 mr-2 text-gray-400" />
                          <div>
                            <p className="text-xs text-gray-600">{t('fields.guests')}</p>
                            <p className="font-semibold">{booking.guests}</p>
                          </div>
                        </div>
                        <div className="flex items-center">
                          <DollarSign className="h-5 w-5 mr-2 text-gray-400" />
                          <div>
                            <p className="text-xs text-gray-600">{t('fields.total')}</p>
                            <p className="font-semibold">
                              {formatCurrency(booking.total_price)}
                            </p>
                          </div>
                        </div>
                      </div>

                      <div className="flex gap-2">
                        <Link href={`/bookings/${booking.id}`}>
                          <Button variant="outline">{t('actions.viewDetails')}</Button>
                        </Link>
                        {booking.status === 'pending' || booking.status === 'confirmed' ? (
                          <AlertDialog>
                            <AlertDialogTrigger asChild>
                              <Button
                                variant="destructive"
                                disabled={cancelBooking.isPending}
                              >
                                {cancelBooking.isPending ? (
                                  <span className="inline-flex items-center">
                                    <svg className="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    {t('actions.canceling')}
                                  </span>
                                ) : t('actions.cancel')}
                              </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                              <AlertDialogHeader>
                                <AlertDialogTitle>{t('dialog.cancel.title')}</AlertDialogTitle>
                                <AlertDialogDescription>
                                  {t('dialog.cancel.description', { title: booking.property?.title || t('property.unknown') })}
                                </AlertDialogDescription>
                              </AlertDialogHeader>
                              <AlertDialogFooter>
                                <AlertDialogCancel>{t('dialog.cancel.keep')}</AlertDialogCancel>
                                <AlertDialogAction onClick={() => handleCancelBooking(booking.id)} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                                  {t('dialog.cancel.confirm')}
                                </AlertDialogAction>
                              </AlertDialogFooter>
                            </AlertDialogContent>
                          </AlertDialog>
                        ) : null}
                        {booking.property && (
                          <Link href={`/properties/${booking.property.id}`}>
                            <Button variant="outline">{t('actions.viewProperty')}</Button>
                          </Link>
                        )}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </MainLayout>
  );
}