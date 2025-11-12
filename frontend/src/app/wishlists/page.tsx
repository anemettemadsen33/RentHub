'use client';

import { MainLayout } from '@/components/layouts/main-layout';
import { useTranslations } from '@/lib/i18n-temp';
import dynamic from 'next/dynamic';
import { TooltipProvider } from '@/components/ui/tooltip';

const WishlistsView = dynamic(() => import('@/features/wishlists/components/WishlistsView'), {
  ssr: false,
  loading: () => <div className="animate-pulse text-sm">Loading wishlists...</div>,
});

export default function WishlistsPage() {
  const t = useTranslations('wishlists');
  return (
  <TooltipProvider>
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-4 space-y-6">
        <header className="space-y-2 animate-fade-in" style={{ animationDelay: '0ms' }}>
          <h1 className="text-2xl font-semibold animate-fade-in" style={{ animationDelay: '0ms' }}>{t('title')}</h1>
          <p className="text-muted-foreground animate-fade-in" style={{ animationDelay: '100ms' }}>{t('subtitle')}</p>
        </header>
        <div className="animate-fade-in-up" style={{ animationDelay: '120ms' }}>
          <WishlistsView />
        </div>
      </main>
    </MainLayout>
  </TooltipProvider>
  );
}
