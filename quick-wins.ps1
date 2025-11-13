#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Auto-implement Quick Wins - Instant improvements
.DESCRIPTION
    Implementează automat îmbunătățirile rapide care fac diferența
#>

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("all", "loading", "errors", "seo", "pwa", "analytics")]
    [string]$Target = "all",
    
    [switch]$Preview
)

Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  ⚡ QUICK WINS AUTO-IMPLEMENTATION    ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

$completedTasks = @()

# ═══════════════════════════════════════════════════════════════
# 1. ADD GLOBAL LOADING STATES
# ═══════════════════════════════════════════════════════════════

if ($Target -in @("all", "loading")) {
    Write-Host "⚡ Creating Global Loading Component..." -ForegroundColor Yellow
    
    $loadingComponent = @'
'use client';

import { Loader2 } from 'lucide-react';
import { cn } from '@/lib/utils';

interface GlobalLoadingProps {
  fullScreen?: boolean;
  message?: string;
  className?: string;
}

export function GlobalLoading({ 
  fullScreen = false, 
  message = 'Loading...', 
  className 
}: GlobalLoadingProps) {
  if (fullScreen) {
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
        <div className="flex flex-col items-center gap-4">
          <Loader2 className="h-12 w-12 animate-spin text-primary" />
          <p className="text-sm text-muted-foreground">{message}</p>
        </div>
      </div>
    );
  }

  return (
    <div className={cn("flex items-center justify-center p-8", className)}>
      <div className="flex flex-col items-center gap-2">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
        <p className="text-xs text-muted-foreground">{message}</p>
      </div>
    </div>
  );
}

// Page-level skeleton
export function PageSkeleton() {
  return (
    <div className="container mx-auto px-4 py-8 animate-pulse">
      <div className="h-8 w-64 bg-muted rounded mb-4" />
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {[1, 2, 3].map((i) => (
          <div key={i} className="space-y-4">
            <div className="h-48 bg-muted rounded" />
            <div className="h-4 bg-muted rounded w-3/4" />
            <div className="h-4 bg-muted rounded w-1/2" />
          </div>
        ))}
      </div>
    </div>
  );
}
'@
    
    if (-not $Preview) {
        New-Item -ItemType Directory -Path "frontend/src/components/loading" -Force | Out-Null
        $loadingComponent | Out-File "frontend/src/components/loading/global-loading.tsx" -Encoding UTF8
        Write-Host "   ✅ Created frontend/src/components/loading/global-loading.tsx" -ForegroundColor Green
        $completedTasks += "Global Loading Component"
    } else {
        Write-Host "   📋 Would create: global-loading.tsx" -ForegroundColor Gray
    }
}

# ═══════════════════════════════════════════════════════════════
# 2. ADD ERROR BOUNDARIES
# ═══════════════════════════════════════════════════════════════

if ($Target -in @("all", "errors")) {
    Write-Host "⚡ Creating Error Boundary..." -ForegroundColor Yellow
    
    $errorBoundary = @'
'use client';

import { Component, ReactNode } from 'react';
import { AlertTriangle, RefreshCw } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  children: ReactNode;
  fallback?: ReactNode;
}

interface State {
  hasError: boolean;
  error?: Error;
}

export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: any) {
    console.error('Error Boundary caught:', error, errorInfo);
    
    // Send to error tracking service (Sentry, etc.)
    if (typeof window !== 'undefined' && window.navigator.onLine) {
      // Log to your error tracking service
    }
  }

  render() {
    if (this.state.hasError) {
      if (this.props.fallback) {
        return this.props.fallback;
      }

      return (
        <div className="min-h-screen flex items-center justify-center p-4">
          <Card className="max-w-md w-full">
            <CardHeader>
              <div className="flex items-center gap-2 text-destructive">
                <AlertTriangle className="h-5 w-5" />
                <CardTitle>Something went wrong</CardTitle>
              </div>
              <CardDescription>
                We encountered an unexpected error. Please try refreshing the page.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {process.env.NODE_ENV === 'development' && this.state.error && (
                <div className="p-4 bg-muted rounded-md">
                  <p className="text-sm font-mono text-destructive">
                    {this.state.error.message}
                  </p>
                </div>
              )}
              <Button
                onClick={() => window.location.reload()}
                className="w-full"
              >
                <RefreshCw className="mr-2 h-4 w-4" />
                Refresh Page
              </Button>
            </CardContent>
          </Card>
        </div>
      );
    }

    return this.props.children;
  }
}
'@
    
    if (-not $Preview) {
        New-Item -ItemType Directory -Path "frontend/src/components/errors" -Force | Out-Null
        $errorBoundary | Out-File "frontend/src/components/errors/error-boundary.tsx" -Encoding UTF8
        Write-Host "   ✅ Created frontend/src/components/errors/error-boundary.tsx" -ForegroundColor Green
        $completedTasks += "Error Boundary"
    } else {
        Write-Host "   📋 Would create: error-boundary.tsx" -ForegroundColor Gray
    }
}

# ═══════════════════════════════════════════════════════════════
# 3. ADD CUSTOM 404 & 500 PAGES
# ═══════════════════════════════════════════════════════════════

if ($Target -in @("all", "errors")) {
    Write-Host "⚡ Creating Custom Error Pages..." -ForegroundColor Yellow
    
    $notFoundPage = @'
import Link from 'next/link';
import { Home, Search } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function NotFound() {
  return (
    <div className="min-h-screen flex items-center justify-center p-4">
      <div className="text-center space-y-6 max-w-md">
        <div className="text-9xl font-bold text-primary">404</div>
        <h1 className="text-3xl font-bold">Page Not Found</h1>
        <p className="text-muted-foreground">
          Sorry, we couldn't find the page you're looking for. 
          It might have been moved or doesn't exist.
        </p>
        <div className="flex gap-4 justify-center flex-wrap">
          <Button asChild>
            <Link href="/">
              <Home className="mr-2 h-4 w-4" />
              Go Home
            </Link>
          </Button>
          <Button variant="outline" asChild>
            <Link href="/properties">
              <Search className="mr-2 h-4 w-4" />
              Browse Properties
            </Link>
          </Button>
        </div>
      </div>
    </div>
  );
}
'@
    
    if (-not $Preview) {
        $notFoundPage | Out-File "frontend/src/app/not-found.tsx" -Encoding UTF8
        Write-Host "   ✅ Created frontend/src/app/not-found.tsx" -ForegroundColor Green
        $completedTasks += "404 Page"
    } else {
        Write-Host "   📋 Would create: not-found.tsx" -ForegroundColor Gray
    }
}

# ═══════════════════════════════════════════════════════════════
# 4. ENHANCE SEO METADATA
# ═══════════════════════════════════════════════════════════════

if ($Target -in @("all", "seo")) {
    Write-Host "⚡ Creating SEO Utilities..." -ForegroundColor Yellow
    
    $seoUtils = @'
import type { Metadata } from 'next';

const siteConfig = {
  name: 'RentHub',
  description: 'Find and book unique accommodations worldwide. Rent apartments, houses, and villas from local hosts.',
  url: process.env.NEXT_PUBLIC_APP_URL || 'https://rent-ljgrpeajm-madsens-projects.vercel.app',
  ogImage: '/og-image.jpg',
  links: {
    twitter: 'https://twitter.com/renthub',
    facebook: 'https://facebook.com/renthub',
  },
};

export function generateMetadata({
  title,
  description,
  image,
  noIndex = false,
  path = '',
}: {
  title?: string;
  description?: string;
  image?: string;
  noIndex?: boolean;
  path?: string;
}): Metadata {
  const fullTitle = title ? `${title} | ${siteConfig.name}` : siteConfig.name;
  const fullDescription = description || siteConfig.description;
  const fullImage = image || siteConfig.ogImage;
  const url = `${siteConfig.url}${path}`;

  return {
    title: fullTitle,
    description: fullDescription,
    applicationName: siteConfig.name,
    authors: [{ name: 'RentHub Team' }],
    keywords: ['vacation rental', 'accommodation', 'booking', 'travel', 'apartments', 'houses', 'villas'],
    
    ...(noIndex && {
      robots: {
        index: false,
        follow: false,
      },
    }),

    openGraph: {
      type: 'website',
      locale: 'en_US',
      url,
      siteName: siteConfig.name,
      title: fullTitle,
      description: fullDescription,
      images: [
        {
          url: fullImage,
          width: 1200,
          height: 630,
          alt: fullTitle,
        },
      ],
    },

    twitter: {
      card: 'summary_large_image',
      title: fullTitle,
      description: fullDescription,
      images: [fullImage],
      creator: '@renthub',
    },

    alternates: {
      canonical: url,
    },
  };
}

export { siteConfig };
'@
    
    if (-not $Preview) {
        New-Item -ItemType Directory -Path "frontend/src/lib/seo" -Force | Out-Null
        $seoUtils | Out-File "frontend/src/lib/seo/metadata.ts" -Encoding UTF8
        Write-Host "   ✅ Created frontend/src/lib/seo/metadata.ts" -ForegroundColor Green
        $completedTasks += "SEO Utilities"
    } else {
        Write-Host "   📋 Would create: seo/metadata.ts" -ForegroundColor Gray
    }
}

# ═══════════════════════════════════════════════════════════════
# 5. PWA ENHANCEMENTS
# ═══════════════════════════════════════════════════════════════

if ($Target -in @("all", "pwa")) {
    Write-Host "⚡ Enhancing PWA Manifest..." -ForegroundColor Yellow
    
    $manifest = @'
{
  "name": "RentHub - Vacation Rentals",
  "short_name": "RentHub",
  "description": "Find and book unique accommodations worldwide",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#3b82f6",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/icon-128x128.png",
      "sizes": "128x128",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/icon-152x152.png",
      "sizes": "152x152",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-384x384.png",
      "sizes": "384x384",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ],
  "screenshots": [
    {
      "src": "/screenshots/home.jpg",
      "type": "image/jpeg",
      "sizes": "1280x720"
    },
    {
      "src": "/screenshots/property.jpg",
      "type": "image/jpeg",
      "sizes": "1280x720"
    }
  ],
  "categories": ["travel", "lifestyle"],
  "shortcuts": [
    {
      "name": "Search Properties",
      "short_name": "Search",
      "description": "Search for properties",
      "url": "/properties",
      "icons": [{ "src": "/icons/search-96x96.png", "sizes": "96x96" }]
    },
    {
      "name": "My Bookings",
      "short_name": "Bookings",
      "description": "View your bookings",
      "url": "/dashboard/bookings",
      "icons": [{ "src": "/icons/bookings-96x96.png", "sizes": "96x96" }]
    }
  ]
}
'@
    
    if (-not $Preview) {
        $manifest | Out-File "frontend/public/manifest.json" -Encoding UTF8
        Write-Host "   ✅ Enhanced frontend/public/manifest.json" -ForegroundColor Green
        $completedTasks += "PWA Manifest"
    } else {
        Write-Host "   📋 Would enhance: manifest.json" -ForegroundColor Gray
    }
}

# ═══════════════════════════════════════════════════════════════
# SUMMARY
# ═══════════════════════════════════════════════════════════════

Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  ✅ QUICK WINS SUMMARY                ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

if ($Preview) {
    Write-Host "PREVIEW MODE - No files created" -ForegroundColor Yellow
    Write-Host "Run without -Preview to create files`n" -ForegroundColor Yellow
} else {
    Write-Host "✅ Completed Tasks ($($completedTasks.Count)):" -ForegroundColor Green
    foreach ($task in $completedTasks) {
        Write-Host "   • $task" -ForegroundColor White
    }
    Write-Host ""
}

Write-Host "📋 NEXT STEPS:`n" -ForegroundColor Yellow
Write-Host "1. Import components in your pages:" -ForegroundColor White
Write-Host "   import { GlobalLoading } from '@/components/loading/global-loading'" -ForegroundColor Cyan
Write-Host "   import { ErrorBoundary } from '@/components/errors/error-boundary'`n" -ForegroundColor Cyan

Write-Host "2. Wrap your app with ErrorBoundary:" -ForegroundColor White
Write-Host "   <ErrorBoundary><YourApp /></ErrorBoundary>`n" -ForegroundColor Cyan

Write-Host "3. Use SEO utilities in pages:" -ForegroundColor White
Write-Host "   export const metadata = generateMetadata({ title: 'Page Title' })`n" -ForegroundColor Cyan

Write-Host "4. Test PWA features:" -ForegroundColor White
Write-Host "   - Add to Home Screen" -ForegroundColor Gray
Write-Host "   - Offline mode" -ForegroundColor Gray
Write-Host "   - Push notifications`n" -ForegroundColor Gray
