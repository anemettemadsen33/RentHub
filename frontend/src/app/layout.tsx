import type { Metadata, Viewport } from 'next'
import '../styles/globals.css'
import { AuthProvider } from '@/contexts/AuthContext'
import { ComparisonProvider } from '@/contexts/ComparisonContext'
import { Toaster } from 'react-hot-toast'
import ComparisonBar from '@/components/properties/ComparisonBar'
import { DEFAULT_METADATA, DEFAULT_VIEWPORT } from '@/lib/seo'
import { getOrganizationSchema, getWebsiteSchema, renderJsonLd } from '@/lib/schema'
import WebVitals from '@/components/performance/WebVitals'
import { ThemeProvider } from '@/components/ThemeProvider'

export const metadata: Metadata = DEFAULT_METADATA

export const viewport: Viewport = DEFAULT_VIEWPORT

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <head>
        <meta name="theme-color" content="#3b82f6" />
        <meta name="format-detection" content="telephone=no" />
        <meta httpEquiv="x-ua-compatible" content="IE=edge" />
        <link rel="icon" href="/favicon.svg" type="image/svg+xml" />
        <link rel="dns-prefetch" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.googleapis.com" crossOrigin="anonymous" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={renderJsonLd(getOrganizationSchema())}
        />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={renderJsonLd(getWebsiteSchema())}
        />
      </head>
      <body>
        <ThemeProvider defaultTheme="system" storageKey="renthub-ui-theme">
          <WebVitals />
          <AuthProvider>
            <ComparisonProvider>
              {children}
              <ComparisonBar />
              <Toaster position="top-right" />
            </ComparisonProvider>
          </AuthProvider>
        </ThemeProvider>
      </body>
    </html>
  )
}
