import type { Metadata, Viewport } from 'next';
// Static message imports
import enMessages from '../../messages/en.json';
import { Inter } from 'next/font/google';
import './globals.css';
import './responsive.css';
// NOTE: Consider extracting global accessibility helpers (skip links, live regions)
import { Providers } from '@/components/providers';
import { ThemeProvider } from '@/components/theme-provider';
import { Toaster } from '@/components/ui/sonner';
import { CommandPalette } from '@/components/command-palette';
import { PWAInstallPrompt } from '@/components/pwa/install-prompt';
import { ServiceWorkerRegister } from '@/components/pwa/sw-register';
import { SWUpdatePrompt } from '@/components/pwa/sw-update-prompt';
import { OrganizationSchema } from '@/components/seo/organization-schema';
import WebVitalsReporter from '@/components/analytics/web-vitals';
import { ConsentBanner } from '@/components/consent-banner';
import { NavigationProvider } from '@/components/navigation/NavigationContainer';
// TEMP DISABLED: next-intl components causing build errors
// import { LocaleAutoDetect } from '@/components/locale-auto-detect';
// import { LocaleDetectionNotification } from '@/components/locale-detection-notification';
import { OfflineIndicator } from '@/components/offline-indicator';
import { AppReady } from '@/components/app-ready';
import { FocusVisibleDetector } from '@/components/accessibility/focus-manager';

const inter = Inter({ subsets: ['latin'] });

export const viewport: Viewport = {
  themeColor: [
    { media: '(prefers-color-scheme: light)', color: '#3b82f6' },
    { media: '(prefers-color-scheme: dark)', color: '#3b82f6' }
  ],
  width: 'device-width',
  initialScale: 1,
  maximumScale: 5,
  userScalable: true,
};

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000'),
  title: {
    default: 'RentHub - Modern Property Rental Platform',
    template: '%s | RentHub',
  },
  description: 'Find and book your perfect rental property',
  manifest: '/api/manifest',
  robots: {
    index: true,
    follow: true,
  },
  alternates: {
    canonical: '/',
  },
  openGraph: {
    type: 'website',
    url: '/',
    title: 'RentHub - Modern Property Rental Platform',
    description: 'Find and book your perfect rental property',
    siteName: 'RentHub',
    images: [
      { url: '/images/og-default.png', width: 1200, height: 630, alt: 'RentHub' },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'RentHub - Modern Property Rental Platform',
    description: 'Find and book your perfect rental property',
    images: ['/images/og-default.png'],
    creator: '@renthub',
  },
  icons: {
    icon: [
      { url: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
      { url: '/icons/icon-256.png', sizes: '256x256', type: 'image/png' },
      { url: '/icons/icon-384.png', sizes: '384x384', type: 'image/png' },
      { url: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
    ],
    apple: [
      { url: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
    ],
  },
};

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  // Simplified - use English by default, no next-intl for now
  const locale = 'en';
  const messages = enMessages;

  return (
  <html lang={locale} suppressHydrationWarning={true}>
      <body
        className={inter.className}
  suppressHydrationWarning={true}
        data-app-ready="false"
        // Respect reduced motion preference globally for any manual JS animations
        data-reduced-motion={typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'true' : 'false'}
      >
        {/* Skip link (paired with id="main-content" inside MainLayout) */}
        <a
          href="#main-content"
          className="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 rounded bg-primary px-3 py-2 text-primary-foreground shadow"
        >
          Skip to main content
        </a>
        {/* Global live region for unobtrusive announcements (can be used by analytics or SW updates) */}
        <div aria-live="polite" aria-atomic="true" className="sr-only" id="global-live-region" />
        <ThemeProvider
          attribute="class"
          defaultTheme="system"
          enableSystem
          disableTransitionOnChange
        >
          {/* Remove NextIntlClientProvider - not needed without i18n */}
          <Providers>
              {/* New Navigation System */}
              <NavigationProvider enableAnalytics={true}>
                {/* Main application layout/content */}
                {children}
              </NavigationProvider>
              {/* Unified toaster already rendered inside Providers; remove duplicate here */}
              <CommandPalette />
              {/* PWA features */}
              <PWAInstallPrompt />
              <ServiceWorkerRegister />
              <SWUpdatePrompt />
              {/* SEO & analytics */}
              <OrganizationSchema />
              <WebVitalsReporter />
              {/* Compliance & locale utilities */}
              <ConsentBanner />
              {/* TEMP DISABLED: next-intl components causing build errors */}
              {/* <LocaleAutoDetect /> */}
              {/* <LocaleDetectionNotification /> */}
              <OfflineIndicator />
              {/* Hydration readiness marker for E2E determinism */}
              <AppReady />
              {/* Global accessibility utilities */}
              <FocusVisibleDetector />
            </Providers>
        </ThemeProvider>
      </body>
    </html>
  );
}
