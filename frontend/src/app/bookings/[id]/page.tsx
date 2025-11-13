'use client';

import { useEffect, useState, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import apiClient, { ensureCsrfCookie } from '@/lib/api-client';
import { Booking } from '@/types';
import { BookingStatusTimeline } from '@/components/booking/booking-status-timeline';
import { useTranslations } from '@/lib/i18n-temp';
import { useAuth } from '@/contexts/auth-context';
import { notify } from '@/lib/notify';
import { MainLayout } from '@/components/layouts/main-layout';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Calendar,
  MapPin,
  Users,
  DollarSign,
  Home,
  Mail,
  Phone,
  CheckCircle,
  Download,
  Key,
  Copy,
  Eye,
  EyeOff,
} from 'lucide-react';
import { formatCurrency, formatDate } from '@/lib/utils';
import { smartLocksService } from '@/lib/api-service';

interface AccessCode {
  id: number;
  code: string;
  type: 'temporary' | 'permanent' | 'one_time';
  valid_from: string;
  valid_until: string;
  usage_count: number;
  max_uses: number | null;
  is_active: boolean;
  smart_lock: {
    id: number;
    name: string;
    location: string;
  };
}

export default function BookingDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { user } = useAuth();
  const t = useTranslations('bookingDetail');
  const tNotify = useTranslations('notify');
  
  const [booking, setBooking] = useState<Booking | null>(null);
  const [loading, setLoading] = useState(true);
  const [accessCodes, setAccessCodes] = useState<AccessCode[]>([]);
  const [showCodes, setShowCodes] = useState<Set<number>>(new Set());
  const [canceling, setCanceling] = useState(false);

  const fetchBooking = useCallback(async () => {
    try {
      const { data } = await apiClient.get(`/bookings/${params.id}`);
      setBooking(data.data);
    } catch (error) {
      notify.error({
        title: t('actions'),
        description: t('bookingNotFound'),
      });
      router.push('/bookings');
    } finally {
      setLoading(false);
    }
  }, [params.id, t, router]);

  const fetchAccessCodes = useCallback(async () => {
    try {
      const response = await smartLocksService.accessCodes.getByBooking(Number(params.id));
      setAccessCodes(response.data || []);
    } catch (error) {
      console.error('Failed to fetch access codes:', error);
    }
  }, [params.id]);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    fetchBooking();
    fetchAccessCodes();
  }, [user, router, fetchBooking, fetchAccessCodes]);

  const toggleCodeVisibility = useCallback((codeId: number) => {
    setShowCodes((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(codeId)) {
        newSet.delete(codeId);
      } else {
        newSet.add(codeId);
      }
      return newSet;
    });
  }, []);

  const copyCode = useCallback(async (code: string) => {
    try {
      await navigator.clipboard.writeText(code);
      notify.success({
        title: tNotify('copiedTitle'),
        description: tNotify('copiedCode'),
      });
    } catch (error) {
      notify.error({
        title: tNotify('error'),
        description: tNotify('copiedCode'),
      });
    }
  }, [tNotify]);

  const handleCancelBooking = useCallback(async () => {
    setCanceling(true);
    try {
      await ensureCsrfCookie();
      await apiClient.post(`/bookings/${params.id}/cancel`);
      notify.success({
        title: tNotify('success'),
        description: t('cancelBooking'),
      });
      fetchBooking();
    } catch (error: any) {
      notify.error({
        title: tNotify('error'),
        description: error.response?.data?.message || t('cancelBooking'),
      });
    } finally { setCanceling(false); }
  }, [params.id, t, tNotify, fetchBooking]);

  const calculateNights = useCallback(() => {
    if (!booking) return 0;
    const start = new Date(booking.check_in);
    const end = new Date(booking.check_out);
    const diff = end.getTime() - start.getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
  }, [booking]);

  if (!user) {
    return null;
  }

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="space-y-6">
            <div className="h-8 bg-gray-200 rounded w-1/3 animate-pulse" />
            <div className="h-64 bg-gray-200 rounded animate-pulse" />
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!booking) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <p>Booking not found</p>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <div className="mb-6">
          <Link href="/bookings">
            <Button variant="outline" size="sm" className="mb-4">
              ← {t('back')}
            </Button>
          </Link>
          <h1 className="text-3xl font-bold mb-2">{t('title')}</h1>
          <p className="text-gray-600">{t('confirmation', { id: booking.id })}</p>
        </div>

        {/* Status Banner */}
        {booking.status === 'confirmed' && (
          <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-center animate-fade-in" aria-live="polite">
            <CheckCircle className="h-6 w-6 text-green-600 mr-3" />
            <div>
              <p className="font-semibold text-green-900">{t('statusBannerConfirmedTitle')}</p>
              <p className="text-sm text-green-700">{t('statusBannerConfirmedBody')}</p>
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {/* Main Details */}
          <div className="md:col-span-2 space-y-6">
            {/* Property Info */}
            <Card className="animate-fade-in-up">
              <CardHeader>
                <CardTitle>{t('propertyInformation')}</CardTitle>
              </CardHeader>
              <CardContent>
                {booking.property && (
                  <>
                    <div className="aspect-video bg-gray-200 rounded-lg mb-4 overflow-hidden relative">
                      {booking.property.image_url && (
                        <Image
                          src={booking.property.image_url}
                          alt={booking.property.title}
                          fill
                          className="object-cover"
                          sizes="(max-width: 768px) 100vw, 66vw"
                        />
                      )}
                    </div>
                    <h3 className="text-xl font-bold mb-2">{booking.property.title}</h3>
                    <div className="flex items-center text-gray-600 mb-4">
                      <MapPin className="h-5 w-5 mr-2" />
                      <span>{booking.property.address}</span>
                    </div>
                    <Link href={`/properties/${booking.property.id}`}>
                      <Button variant="outline" className="w-full">
                        <Home className="h-4 w-4 mr-2" />
                        {t('viewProperty')}
                      </Button>
                    </Link>
                  </>
                )}
              </CardContent>
            </Card>

            {/* Booking Details */}
            <Card className="animate-fade-in-up" style={{ animationDelay: '40ms' }}>
              <CardHeader>
                <CardTitle>{t('reservationDetails')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div className="flex items-start">
                    <Calendar className="h-5 w-5 mr-3 mt-1 text-gray-400" />
                    <div>
                      <p className="text-sm text-gray-600">{t('checkIn')}</p>
                      <p className="font-semibold">{formatDate(booking.check_in)}</p>
                      <p className="text-xs text-gray-500">{t('afterTime', { time: '3:00 PM' })}</p>
                    </div>
                  </div>
                  <div className="flex items-start">
                    <Calendar className="h-5 w-5 mr-3 mt-1 text-gray-400" />
                    <div>
                      <p className="text-sm text-gray-600">{t('checkOut')}</p>
                      <p className="font-semibold">{formatDate(booking.check_out)}</p>
                      <p className="text-xs text-gray-500">{t('beforeTime', { time: '11:00 AM' })}</p>
                    </div>
                  </div>
                </div>
                <div className="flex items-start">
                  <Users className="h-5 w-5 mr-3 mt-1 text-gray-400" />
                  <div>
                    <p className="text-sm text-gray-600">{t('guests')}</p>
                    <p className="font-semibold">{booking.guests}</p>
                  </div>
                </div>
                <div className="border-t pt-4">
                  <p className="text-sm text-gray-600 mb-1">{t('totalStay')}</p>
                  <p className="text-2xl font-bold">{t('nights', { count: calculateNights() })}</p>
                </div>
              </CardContent>
            </Card>

            {/* Access Codes for Self Check-in */}
            {accessCodes.length > 0 && (
              <Card className="animate-fade-in-up" style={{ animationDelay: '80ms' }}>
                <CardHeader>
                  <CardTitle className="flex items-center">
                    <Key className="h-5 w-5 mr-2" />
                    {t('accessCodes')}
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p className="text-sm text-blue-900">{t('accessCodesImportant')}</p>
                  </div>

                  {accessCodes.map((accessCode) => (
                    <div
                      key={accessCode.id}
                      className="border rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition-colors"
                    >
                      <div className="flex items-start justify-between mb-3">
                        <div>
                          <p className="font-semibold">{accessCode.smart_lock.name}</p>
                          <p className="text-sm text-gray-600">{accessCode.smart_lock.location}</p>
                        </div>
                        <span className={`px-2 py-1 rounded text-xs font-semibold ${
                          accessCode.is_active
                            ? 'bg-green-100 text-green-800'
                            : 'bg-gray-100 text-gray-800'
                        }`}>
                          {accessCode.is_active ? 'Active' : 'Inactive'}
                        </span>
                      </div>

                      <div className="space-y-2">
                        <div className="flex items-center space-x-2">
                          <div className="flex-1 bg-white border rounded-lg p-3 flex items-center justify-between">
                            <span className="font-mono text-lg tracking-wider">
                              {showCodes.has(accessCode.id) ? accessCode.code : '••••••'}
                            </span>
                            <div className="flex space-x-2">
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => toggleCodeVisibility(accessCode.id)}
                              >
                                {showCodes.has(accessCode.id) ? (
                                  <EyeOff className="h-4 w-4" />
                                ) : (
                                  <Eye className="h-4 w-4" />
                                )}
                              </Button>
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => copyCode(accessCode.code)}
                              >
                                <Copy className="h-4 w-4" />
                              </Button>
                            </div>
                          </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2 text-xs text-gray-600">
                          <div>
                            <span className={"font-semibold"}>{t('validFrom')}:</span>
                            <br />
                            {formatDate(accessCode.valid_from)}
                          </div>
                          <div>
                            <span className={"font-semibold"}>{t('validUntil')}:</span>
                            <br />
                            {formatDate(accessCode.valid_until)}
                          </div>
                        </div>

                        {accessCode.type === 'one_time' && (
                          <p className="text-xs text-orange-600">⚠️ {t('oneTimeCodeWarning')}</p>
                        )}
                      </div>
                    </div>
                  ))}
                </CardContent>
              </Card>
            )}

            {/* Host Contact */}
            <Card className="animate-fade-in-up" style={{ animationDelay: '120ms' }}>
              <CardHeader>
                <CardTitle>{t('help')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex items-center">
                  <Mail className="h-5 w-5 mr-3 text-gray-400" />
                  <div>
                    <p className="text-sm text-gray-600">{t('emailSupport')}</p>
                    <a href="mailto:support@renthub.com" className="text-primary hover:underline">
                      support@renthub.com
                    </a>
                  </div>
                </div>
                <div className="flex items-center">
                  <Phone className="h-5 w-5 mr-3 text-gray-400" />
                  <div>
                    <p className="text-sm text-gray-600">{t('phoneSupport')}</p>
                    <a href="tel:+15555555555" className="text-primary hover:underline">
                      +1 (555) 555-5555
                    </a>
                  </div>
                </div>
              </CardContent>
            </Card>
            {/* Status Timeline */}
            <Card className="animate-fade-in-up" style={{ animationDelay: '160ms' }}>
              <CardHeader>
                <CardTitle>Status</CardTitle>
              </CardHeader>
              <CardContent>
                <BookingStatusTimeline status={booking.status as any} />
              </CardContent>
            </Card>
          </div>

          {/* Summary Sidebar */}
          <div className="space-y-6">
            {/* Price Summary */}
            <Card className="animate-fade-in-up">
              <CardHeader>
                <CardTitle>{t('priceSummary')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {booking.property && (
                  <div className="flex justify-between text-sm">
                    <span>
                      ${booking.property.price_per_night || booking.property.price} × {calculateNights()} {t('nights', { count: calculateNights() })}
                    </span>
                    <span>
                      ${(booking.property.price_per_night || booking.property.price) * calculateNights()}
                    </span>
                  </div>
                )}
                <div className="flex justify-between text-sm text-gray-600">
                  <span>{t('serviceFee')}</span>
                  <span>$0</span>
                </div>
                <div className="flex justify-between text-sm text-gray-600">
                  <span>{t('taxes')}</span>
                  <span>$0</span>
                </div>
                <div className="border-t pt-3 flex justify-between font-bold text-lg" aria-live="polite">
                  <span>{t('total')}</span>
                  <span>{formatCurrency(booking.total_price)}</span>
                </div>
              </CardContent>
            </Card>

            {/* Actions */}
            <Card className="animate-fade-in-up" style={{ animationDelay: '40ms' }}>
              <CardHeader>
                <CardTitle>{t('actions')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Button variant="outline" className="w-full">
                  <Download className="h-4 w-4 mr-2" />
                  {t('downloadReceipt')}
                </Button>
                {(booking.status === 'pending' || booking.status === 'confirmed') && (
                  <AlertDialog>
                    <AlertDialogTrigger asChild>
                      <Button
                        variant="destructive"
                        className="w-full"
                        aria-busy={canceling}
                        disabled={canceling}
                      >
                        {canceling ? 'Processing…' : t('cancelBooking')}
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>{t('cancelConfirm')}</AlertDialogTitle>
                        <AlertDialogDescription>
                          {t('cancelIrreversible')}
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>{t('back')}</AlertDialogCancel>
                        <AlertDialogAction onClick={handleCancelBooking} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                          {t('confirmCancel')}
                        </AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}

// metadata is handled in segment layout to keep this page as a Client Component
