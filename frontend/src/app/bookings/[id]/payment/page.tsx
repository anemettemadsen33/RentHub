'use client';

import { useEffect, useState } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipProvider, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip';
import { Skeleton } from '@/components/ui/skeleton';
import { notify } from '@/lib/notify';
import { StatusPill } from '@/components/ui/status-pill';
import { generateInvoicePDF, previewInvoicePDF } from '@/lib/invoice-generator';
import { bookingsService, invoicesService } from '@/lib/api-service';
import { formatCurrency } from '@/lib/utils';
import { useTranslations } from 'next-intl';
import {
  Building2,
  Calendar,
  User,
  CreditCard,
  Download,
  CheckCircle,
  AlertCircle,
} from 'lucide-react';

interface InvoiceItem {
  description: string;
  quantity: number;
  price: number;
  total: number;
}

interface BookingApi {
  id: number;
  property_id: number;
  user_id: number;
  check_in: string;
  check_out: string;
  guests: number;
  total_price: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  property?: {
    id: number;
    title: string;
    address: string;
    price_per_night: number;
  };
}

interface InvoiceApi {
  id: number;
  number: string;
  subtotal: number;
  tax: number;
  total: number;
  referral_discount?: number;
  loyalty_discount?: number;
  created_at: string;
}

export default function PaymentPage() {
  const router = useRouter();
  const params = useParams();
  const tNotify = useTranslations('notify');
  
  const [booking, setBooking] = useState<BookingApi | null>(null);
  const [invoice, setInvoice] = useState<InvoiceApi | null>(null);
  const [loading, setLoading] = useState(true);
  const [processingPayment, setProcessingPayment] = useState(false);

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      try {
        const data = await bookingsService.show(params.id as any);
        setBooking(data);
        // Try invoices
        try {
          const invoices = await bookingsService.getInvoices(params.id as any);
            if (Array.isArray(invoices?.data) && invoices.data.length > 0) {
              setInvoice(invoices.data[0]);
            }
        } catch {}
      } catch (e) {
        notify.error({ title: tNotify('error'), description: tNotify('failedLoadBookingPayment') });
      } finally {
        setLoading(false);
      }
    };
    load();
  }, [params.id, tNotify]);

  const t = useTranslations('invoice');
  const tBooking = useTranslations('bookingDetail');
  const nights = booking ? Math.max(1, Math.ceil((new Date(booking.check_out).getTime() - new Date(booking.check_in).getTime()) / (1000*60*60*24))) : 0;
  const cleaningFee = 0; // Could be dynamic
  const serviceFee = booking ? booking.total_price * 0.05 : 0; // Example 5%
  const subtotal = booking && booking.property ? (booking.property.price_per_night || 0) * nights + cleaningFee + serviceFee : 0;
  const taxRate = 10; // Demo
  const tax = subtotal * (taxRate / 100);
  const total = subtotal + tax;

  const handleGenerateInvoice = async () => {
    if (!booking) return;
    setProcessingPayment(true);
    try {
      generateInvoicePDF({
        invoiceNumber: `INV-${booking.id.toString().padStart(6,'0')}`,
        date: new Date().toLocaleDateString(),
        dueDate: new Date(Date.now() + 3*24*60*60*1000).toLocaleDateString(),
        companyName: 'RentHub Platform',
        companyAddress: '123 Example Street, City, Country',
        companyEmail: 'contact@renthub.com',
        companyPhone: '+1 555 123 4567',
        customerName: '—',
        customerEmail: '—',
        items: [
          { description: `${booking.property?.title} (${nights} ${t('nights',{count:nights})})`, quantity: nights, price: booking.property?.price_per_night || 0, total: (booking.property?.price_per_night||0)*nights },
          { description: t('serviceFee'), quantity: 1, price: serviceFee, total: serviceFee },
        ],
        subtotal: subtotal,
        tax: tax,
        taxRate,
        total,
        paymentMethod: t('bankTransfer'),
        bankDetails: { bankName: 'Demo Bank', accountName: 'RentHub LLC', accountNumber: '000-123', iban: 'RO00 0000 0000', swift: 'DEMOXXX' },
        notes: t('important'),
      });
      notify.success({ title: tNotify('invoice'), description: tNotify('pdfDownloaded') });
    } catch (e) {
      notify.error({ title: tNotify('error'), description: tNotify('failedGenerateInvoice') });
    } finally {
      setProcessingPayment(false);
    }
  };

  const handlePreviewInvoice = async () => {
    if (!booking) return;
    previewInvoicePDF({
      invoiceNumber: `INV-${booking.id.toString().padStart(6,'0')}`,
      date: new Date().toLocaleDateString(),
      dueDate: new Date(Date.now() + 3*24*60*60*1000).toLocaleDateString(),
      companyName: 'RentHub Platform',
      companyAddress: '123 Example Street, City, Country',
      companyEmail: 'contact@renthub.com',
      companyPhone: '+1 555 123 4567',
      customerName: '—',
      customerEmail: '—',
      items: [
        { description: `${booking.property?.title} (${nights} ${t('nights',{count:nights})})`, quantity: nights, price: booking.property?.price_per_night || 0, total: (booking.property?.price_per_night||0)*nights },
        { description: t('serviceFee'), quantity: 1, price: serviceFee, total: serviceFee },
      ],
      subtotal,
      tax,
      taxRate,
      total,
      paymentMethod: t('bankTransfer'),
      bankDetails: { bankName: 'Demo Bank', accountName: 'RentHub LLC', accountNumber: '000-123', iban: 'RO00 0000 0000', swift: 'DEMOXXX' },
      notes: t('important'),
    });
  };

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8 max-w-5xl space-y-6" aria-busy="true" aria-live="polite">
          <Skeleton className="h-10 w-1/3" />
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="space-y-6 lg:col-span-2">
              <Skeleton className="h-48 w-full" />
              <Skeleton className="h-72 w-full" />
              <Skeleton className="h-56 w-full" />
            </div>
            <div className="space-y-6">
              <Skeleton className="h-64 w-full" />
            </div>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!booking) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <Card>
            <CardContent className="py-8 text-center">
              <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
              <h2 className="text-xl font-semibold mb-2">{t('noInvoice')}</h2>
              <Button onClick={() => router.push('/bookings')}>
                {t('cancel')}
              </Button>
            </CardContent>
          </Card>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-5xl">
        <div className="mb-6">
          <h1 className="text-3xl font-bold mb-2">{t('title')}</h1>
          <p className="text-gray-600">{t('invoiceNumber', { number: booking.id.toString().padStart(6,'0') })}</p>
          <span className="sr-only" aria-live="polite">{t('total')}: {formatCurrency(total)}</span>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Payment Section */}
          <div className="lg:col-span-2 space-y-6">
            {/* Property Details */}
            <Card className="animate-fade-in-up">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Building2 className="h-5 w-5" />
                  {t('propertyDetails')}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <h3 className="font-semibold text-lg">
                    {booking.property?.title}
                  </h3>
                  <p className="text-gray-600">{booking.property?.address}</p>
                </div>
                <div className="flex items-center gap-4 text-sm">
                  <div className="flex items-center gap-2">
                    <Calendar className="h-4 w-4 text-gray-400" />
                    <span>
                      {tBooking('checkIn')}:{' '}
                      {new Date(booking.check_in).toLocaleDateString('ro-RO')}
                    </span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Calendar className="h-4 w-4 text-gray-400" />
                    <span>
                      {tBooking('checkOut')}:{' '}
                      {new Date(booking.check_out).toLocaleDateString('ro-RO')}
                    </span>
                  </div>
                </div>
                <div className="text-sm text-gray-600">
                  {nights} {t('nights',{count:nights})} × {booking.property?.price_per_night} RON
                </div>
              </CardContent>
            </Card>

            {/* Payment Method */}
            <Card className="animate-fade-in-up" style={{ animationDelay: '40ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CreditCard className="h-5 w-5" />
                  {t('paymentMethod')}
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="border-2 border-primary rounded-lg p-4 bg-primary/5">
                  <div className="flex items-start gap-3">
                    <CheckCircle className="h-6 w-6 text-primary mt-1" />
                    <div className="flex-1">
                      <h4 className="font-semibold mb-2">{t('bankTransfer')}</h4>
                      <p className="text-sm text-gray-600 mb-4">
                        Efectuați plata prin transfer bancar folosind detaliile
                        de mai jos.
                      </p>
                      <div className="bg-white rounded-lg p-4 space-y-2 text-sm">
                        <div className="flex justify-between">
                          <span className="text-gray-600">Bancă:</span>
                          <span className="font-medium">Banca Transilvania</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-600">Beneficiar:</span>
                          <span className="font-medium">RentHub SRL</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-600">IBAN:</span>
                          <span className="font-mono font-medium">
                            RO49 AAAA 1B31 0075 9384 0000
                          </span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-gray-600">SWIFT:</span>
                          <span className="font-mono font-medium">BTRLRO22</span>
                        </div>
                        <div className="flex justify-between pt-2 border-t">
                          <span className="text-gray-600">Descriere:</span>
                          <span className="font-medium">
                            INV-{booking.id.toString().padStart(6, '0')}
                          </span>
                        </div>
                      </div>
                      <div className="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p className="text-sm text-amber-800">
                          {t('important')}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Guest Information */}
            <Card className="animate-fade-in-up" style={{ animationDelay: '80ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="h-5 w-5" />
                  Client
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Nume:</span>
                  <span className="font-medium">—</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Email:</span>
                  <span className="font-medium">—</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Telefon:</span>
                  <span className="font-medium">—</span>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Summary Sidebar */}
          <div className="lg:col-span-1">
            <Card className="sticky top-6 animate-fade-in-up" style={{ animationDelay: '120ms' }}>
              <CardHeader>
                <CardTitle>{t('summary')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-gray-600">{t('nights', { count: nights })}</span>
                    <span>{formatCurrency((booking.property?.price_per_night||0)*nights)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">{t('cleaningFee')}</span>
                    <span>{formatCurrency(cleaningFee)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">{t('serviceFee')}</span>
                    <span>{formatCurrency(serviceFee)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">{t('tax')} ({taxRate}%)</span>
                    <span>{formatCurrency(tax)}</span>
                  </div>
                  <div className="border-t pt-2 flex justify-between font-semibold text-lg">
                    <span>{t('total')}</span>
                    <span>{formatCurrency(total)}</span>
                  </div>
                </div>

                <div className="pt-4 space-y-3">
                  <TooltipProvider>
                    <div className="flex flex-col gap-2">
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button onClick={handleGenerateInvoice} disabled={processingPayment} className="w-full" aria-busy={processingPayment}>
                            {processingPayment ? t('processing') : <><Download className="h-4 w-4 mr-2" /> {t('confirmAndDownload')}</>}
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>{t('tooltips.generateAndDownload')}</TooltipContent>
                      </Tooltip>
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <Button variant="secondary" onClick={handlePreviewInvoice} className="w-full" disabled={processingPayment}>
                            {t('preview')}
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>{t('tooltips.preview')}</TooltipContent>
                      </Tooltip>
                    </div>
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <Button
                          variant="outline"
                          onClick={() => router.push('/bookings')}
                          className="w-full"
                        >{t('cancel')}</Button>
                      </TooltipTrigger>
                      <TooltipContent>{t('tooltips.goBack')}</TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                </div>

                <div className="pt-4 border-t flex justify-center" aria-live="polite">
                  <StatusPill status={invoice ? 'paid' : 'pending'} />
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
