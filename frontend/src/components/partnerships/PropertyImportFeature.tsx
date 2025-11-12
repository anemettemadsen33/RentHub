'use client';

import { useState } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

export default function PropertyImportFeature() {
  const t = useTranslations('import');
  const [isOpen, setIsOpen] = useState(false);
  const [selectedPlatform, setSelectedPlatform] = useState<'booking' | 'airbnb' | 'vrbo'>('booking');
  const [importUrl, setImportUrl] = useState('');
  const [loading, setLoading] = useState(false);

  const handleImport = async () => {
    if (!importUrl.trim()) return;

    setLoading(true);
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/properties/import`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`, // TODO: Get from auth context
        },
        body: JSON.stringify({
          platform: selectedPlatform,
          url: importUrl.trim(),
        }),
      });

      const data = await response.json();

      if (data.success) {
        // Success
        alert(t('success_message') || 'Property imported successfully!');
        setImportUrl('');
        setIsOpen(false);
      } else {
        // Error from API
        alert(data.message || t('error_message') || 'Import failed.');
      }
    } catch (error) {
      console.error('Import error:', error);
      alert(t('error_message') || 'Import failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <section className="py-12 md:py-16 bg-muted/50 border-y">
      <div className="container mx-auto px-4">
        <div className="max-w-4xl mx-auto">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            {/* Left side - Title and description */}
            <div className="flex-1 text-center md:text-left">
              <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-3 py-1 rounded-full mb-3">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span className="font-semibold text-xs">{t('badge')}</span>
              </div>

              <h2 className="text-2xl md:text-3xl font-bold text-foreground mb-2">
                {t('title')}
              </h2>
              
              <p className="text-sm text-muted-foreground mb-4 md:mb-0">
                {t('description')}
              </p>
            </div>

            {/* Right side - Button only */}
            <div className="flex items-center">
              <Dialog open={isOpen} onOpenChange={setIsOpen}>
                <DialogTrigger asChild>
                  <Button size="lg" className="gap-2">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    {t('import_button')}
                  </Button>
                </DialogTrigger>

            <DialogContent className="sm:max-w-[550px]">
              <DialogHeader>
                <DialogTitle>{t('dialog_title')}</DialogTitle>
                <DialogDescription>{t('dialog_description')}</DialogDescription>
              </DialogHeader>

              <Tabs defaultValue="booking" value={selectedPlatform} onValueChange={(v) => setSelectedPlatform(v as any)} className="w-full">
                  <TabsList className="grid w-full grid-cols-3">
                    <TabsTrigger value="booking">Booking.com</TabsTrigger>
                    <TabsTrigger value="airbnb">Airbnb</TabsTrigger>
                    <TabsTrigger value="vrbo">VRBO</TabsTrigger>
                  </TabsList>

                <TabsContent value="booking" className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="booking-url">{t('url_label')}</Label>
                    <Input
                      id="booking-url"
                      placeholder="https://www.booking.com/hotel/..."
                      value={importUrl}
                      onChange={(e) => setImportUrl(e.target.value)}
                    />
                    <p className="text-xs text-gray-500">{t('booking_hint')}</p>
                  </div>
                </TabsContent>

                <TabsContent value="airbnb" className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="airbnb-url">{t('url_label')}</Label>
                    <Input
                      id="airbnb-url"
                      placeholder="https://www.airbnb.com/rooms/..."
                      value={importUrl}
                      onChange={(e) => setImportUrl(e.target.value)}
                    />
                    <p className="text-xs text-gray-500">{t('airbnb_hint')}</p>
                  </div>
                </TabsContent>

                <TabsContent value="vrbo" className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="vrbo-url">{t('url_label')}</Label>
                    <Input
                      id="vrbo-url"
                      placeholder="https://www.vrbo.com/..."
                      value={importUrl}
                      onChange={(e) => setImportUrl(e.target.value)}
                    />
                    <p className="text-xs text-gray-500">{t('vrbo_hint')}</p>
                  </div>
                </TabsContent>
              </Tabs>

              <div className="bg-primary/5 border border-primary/20 rounded-lg p-4">
                <div className="flex gap-3">
                  <svg className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div className="text-sm">
                    <p className="font-semibold mb-1">{t('info_title')}</p>
                    <p className="text-muted-foreground">{t('info_description')}</p>
                  </div>
                </div>
              </div>

              <div className="flex gap-3 justify-end">
                <Button variant="outline" onClick={() => setIsOpen(false)} disabled={loading}>
                  {t('cancel')}
                </Button>
                <Button onClick={handleImport} disabled={!importUrl || loading}>
                  {loading ? t('importing') : t('import_now')}
                </Button>
              </div>
            </DialogContent>
          </Dialog>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
