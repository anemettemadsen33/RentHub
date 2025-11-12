"use client";
import { useTranslations } from '@/lib/i18n-temp';
import { Button } from '@/components/ui/button';
import Image from 'next/image';

export default function OfflinePage() {
  const t = useTranslations('offline');
  return (
    <main className="min-h-screen flex items-center justify-center p-6">
      <div className="max-w-md w-full text-center space-y-6">
        <div className="flex flex-col items-center gap-4">
          <Image src="/images/offline.png" alt="Offline" width={128} height={128} priority className="opacity-80" />
          <h1 className="text-2xl font-semibold" data-testid="offline-heading">{t('title')}</h1>
          <p className="text-muted-foreground" data-testid="offline-description">{t('description')}</p>
        </div>
        <div className="flex gap-3 justify-center">
          <Button onClick={() => window.location.reload()} data-testid="offline-retry">{t('retry')}</Button>
          <Button variant="outline" onClick={() => history.back()} data-testid="offline-back">{t('goBack')}</Button>
        </div>
      </div>
    </main>
  );
}
