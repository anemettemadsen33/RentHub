# Performance SEO Implementation Guide

## Overview
Complete performance optimization implementation for RentHub including Core Web Vitals, mobile-first design, page speed optimization, and AMP pages support.

**Status**: ✅ Complete  
**Last Updated**: November 3, 2025

---

## 🎯 Core Web Vitals Optimization

### What are Core Web Vitals?

Core Web Vitals are Google's metrics for measuring user experience:

1. **LCP (Largest Contentful Paint)** - Loading performance
   - Good: < 2.5s
   - Needs Improvement: 2.5s - 4.0s
   - Poor: > 4.0s

2. **FID (First Input Delay)** / **INP (Interaction to Next Paint)** - Interactivity
   - Good: < 100ms (FID) / < 200ms (INP)
   - Needs Improvement: 100-300ms / 200-500ms
   - Poor: > 300ms / > 500ms

3. **CLS (Cumulative Layout Shift)** - Visual stability
   - Good: < 0.1
   - Needs Improvement: 0.1 - 0.25
   - Poor: > 0.25

### Implementation

#### Web Vitals Monitoring
**File**: `frontend/src/lib/performance.ts`

```typescript
import { reportWebVitals } from '@/lib/performance';

// Automatically reports to Google Analytics and custom endpoint
export function reportWebVitals(metric) {
  // Reports FCP, LCP, FID, CLS, TTFB, INP
  console.log(metric);
}
```

#### Web Vitals Component
**File**: `frontend/src/components/performance/WebVitals.tsx`

Integrated into root layout to track all pages:

```typescript
import WebVitals from '@/components/performance/WebVitals';

<WebVitals />
```

### Optimization Techniques

#### 1. Improve LCP
```typescript
// Preload critical resources
<link rel="preload" as="image" href="/hero-image.jpg" />

// Priority images
<Image src="/hero.jpg" priority alt="Hero" />

// Optimize images
<OptimizedImage 
  src="/image.jpg"
  lowQualitySrc="/image-low.jpg"
  priority
/>
```

#### 2. Improve FID/INP
```typescript
// Defer non-critical JavaScript
import { deferNonCriticalJS } from '@/lib/performance';

deferNonCriticalJS();

// Use requestIdleCallback for low-priority tasks
requestIdleCallback(() => {
  // Non-critical work
});
```

#### 3. Improve CLS
```css
/* Reserve space for images */
.image-container {
  aspect-ratio: 16 / 9;
}

/* Prevent layout shifts */
img, video {
  width: 100%;
  height: auto;
}
```

---

## 📱 Mobile-First Design

### Responsive Breakpoints
**File**: `frontend/src/lib/mobile.ts`

```typescript
export const BREAKPOINTS = {
  xs: 320,   // Mobile portrait
  sm: 640,   // Mobile landscape
  md: 768,   // Tablet portrait
  lg: 1024,  // Tablet landscape
  xl: 1280,  // Desktop
  '2xl': 1536 // Large desktop
};
```

### Device Detection

```typescript
import { device } from '@/lib/mobile';

if (device.isMobile()) {
  // Mobile-specific logic
}

if (device.isIOS()) {
  // iOS-specific optimizations
}

if (device.isTouchDevice()) {
  // Touch-optimized UI
}
```

### Touch Gestures

```typescript
import { TouchHandler } from '@/lib/mobile';

const handler = new TouchHandler(element);

handler.onSwipeLeft = () => {
  // Next slide
};

handler.onSwipeRight = () => {
  // Previous slide
};

handler.enable();
```

### Responsive Utilities

```typescript
import { responsive, getCurrentBreakpoint } from '@/lib/mobile';

const fontSize = responsive({
  xs: '14px',
  md: '16px',
  xl: '18px',
});

const breakpoint = getCurrentBreakpoint();
// Returns: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'
```

### Mobile Optimizations

```typescript
import { 
  applyMobileOptimizations,
  lockBodyScroll,
  unlockBodyScroll 
} from '@/lib/mobile';

// Apply on mount
useEffect(() => {
  applyMobileOptimizations();
}, []);

// Lock scroll for modals
const openModal = () => {
  lockBodyScroll();
  setModalOpen(true);
};

const closeModal = () => {
  unlockBodyScroll();
  setModalOpen(false);
};
```

---

## ⚡ Page Speed Optimization

### Image Optimization

#### Using OptimizedImage Component
**File**: `frontend/src/components/performance/OptimizedImage.tsx`

```typescript
import OptimizedImage from '@/components/performance/OptimizedImage';

<OptimizedImage
  src="/high-quality.jpg"
  lowQualitySrc="/low-quality.jpg"
  width={800}
  height={600}
  alt="Property image"
  priority={false}
/>
```

Features:
- Automatic quality detection
- Progressive loading
- Responsive sizing
- Lazy loading
- AVIF/WebP support

#### Next.js Image Optimization

```typescript
import Image from 'next/image';

<Image
  src="/image.jpg"
  width={800}
  height={600}
  alt="Description"
  loading="lazy"
  placeholder="blur"
  blurDataURL="data:image/..."
/>
```

### Resource Hints

```typescript
import { addResourceHints } from '@/lib/performance';

// Preconnect to external domains
addResourceHints([
  'https://fonts.googleapis.com',
  'https://api.example.com',
  'https://cdn.example.com',
]);
```

### Lazy Loading

```typescript
import { lazyLoadImages } from '@/lib/performance';

useEffect(() => {
  lazyLoadImages('img[data-src]');
}, []);
```

```html
<!-- HTML -->
<img 
  data-src="/actual-image.jpg"
  data-srcset="/image-small.jpg 480w, /image-large.jpg 1200w"
  src="/placeholder.jpg"
  alt="Description"
/>
```

### Third-Party Script Optimization

```typescript
import { optimizeThirdPartyScripts } from '@/lib/performance';

useEffect(() => {
  optimizeThirdPartyScripts();
}, []);
```

```html
<!-- Deferred loading -->
<script 
  data-id="google-analytics"
  data-src="https://www.googletagmanager.com/gtag/js"
  defer
></script>
```

### Code Splitting

```typescript
// Dynamic imports
const HeavyComponent = dynamic(() => import('./HeavyComponent'), {
  loading: () => <LoadingSpinner />,
  ssr: false, // Client-side only
});

// Route-based splitting (automatic in Next.js)
```

### Performance Budget

**File**: `frontend/src/lib/performance.ts`

```typescript
export const PERFORMANCE_BUDGET = {
  js: 300,      // KB
  css: 100,     // KB
  images: 500,  // KB
  fonts: 100,   // KB
  total: 1000,  // KB
};
```

Monitor with backend API:
```bash
GET /api/v1/performance/budget-status
```

---

## 🚀 AMP Pages (Optional)

### What is AMP?

AMP (Accelerated Mobile Pages) provides ultra-fast mobile page loads using restricted HTML/CSS/JS.

### Configuration

**File**: `frontend/src/lib/amp.ts`

```typescript
export const AMP_CONFIG = {
  enabled: process.env.NEXT_PUBLIC_AMP_ENABLED === 'true',
  routes: [
    '/properties/[id]',
    '/properties',
    '/search',
  ],
};
```

### Enable AMP

```env
# .env.local
NEXT_PUBLIC_AMP_ENABLED=true
```

### AMP Page Generation

```typescript
import { generateAMPPage, convertToAMP } from '@/lib/amp';

const ampHtml = generateAMPPage({
  title: 'Property Title',
  description: 'Property description',
  canonicalUrl: 'https://renthub.com/properties/123',
  schemaMarkup: propertySchema,
  content: convertToAMP(htmlContent),
  styles: ampStyles,
});
```

### AMP Components

```typescript
import { generateAMPImage, generateAMPCarousel } from '@/lib/amp';

// Single image
const ampImage = generateAMPImage({
  src: '/image.jpg',
  alt: 'Description',
  width: 1200,
  height: 800,
  layout: 'responsive',
});

// Image carousel
const ampCarousel = generateAMPCarousel([
  { src: '/image1.jpg', alt: 'Image 1' },
  { src: '/image2.jpg', alt: 'Image 2' },
]);
```

### AMP Validation

```typescript
import { validateAMP } from '@/lib/amp';

const { valid, errors } = validateAMP(htmlContent);

if (!valid) {
  console.error('AMP validation errors:', errors);
}
```

### AMP Link Tags

```html
<!-- Regular page -->
<link rel="amphtml" href="https://renthub.com/properties/123?amp=1">

<!-- AMP page -->
<link rel="canonical" href="https://renthub.com/properties/123">
```

---

## 📊 Performance Monitoring

### Backend Analytics

**Controller**: `backend/app/Http/Controllers/Api/PerformanceController.php`

#### Store Web Vitals
```bash
POST /api/v1/analytics/web-vitals
{
  "metric": "LCP",
  "value": 2300,
  "rating": "good",
  "url": "/properties/123",
  "userAgent": "..."
}
```

#### Get Summary
```bash
GET /api/v1/performance/summary?days=7
```

Response:
```json
{
  "2025-11-03": {
    "LCP": {
      "avgValue": 2100,
      "p75Value": 2400,
      "goodPercentage": 85.5
    },
    "FID": { ... },
    "CLS": { ... }
  }
}
```

#### Get Recommendations
```bash
GET /api/v1/performance/recommendations
```

Response:
```json
{
  "recommendations": [
    {
      "metric": "LCP",
      "severity": "medium",
      "message": "LCP is slow...",
      "tips": [
        "Optimize images",
        "Use lazy loading",
        "Minimize CSS"
      ]
    }
  ]
}
```

### Client-Side Monitoring

```typescript
import { monitorLongTasks, WEB_VITALS } from '@/lib/performance';

// Monitor long tasks (> 50ms)
monitorLongTasks();

// Check thresholds
if (lcpValue > WEB_VITALS.LCP.GOOD) {
  console.warn('LCP is slow');
}
```

---

## 🔧 Configuration

### Next.js Config

**File**: `frontend/next.config.ts`

```typescript
const nextConfig = {
  // Image optimization
  images: {
    formats: ['image/avif', 'image/webp'],
    deviceSizes: [640, 750, 828, 1080, 1200, 1920, 2048, 3840],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
  },

  // Compression
  compress: true,

  // Production source maps
  productionBrowserSourceMaps: false,

  // Optimize package imports
  experimental: {
    optimizePackageImports: ['lucide-react', '@headlessui/react'],
  },

  // React compiler
  reactCompiler: true,
};
```

### Environment Variables

```env
# Performance
NEXT_PUBLIC_AMP_ENABLED=false
NEXT_PUBLIC_ENABLE_PERFORMANCE_MONITORING=true

# Analytics
NEXT_PUBLIC_GA_ID=G-XXXXXXXXXX
```

---

## 📈 Best Practices

### 1. Critical CSS
```typescript
// Inline critical CSS in <head>
<style dangerouslySetInnerHTML={{
  __html: criticalCSS
}} />
```

### 2. Font Loading
```typescript
// Preload fonts
<link
  rel="preload"
  href="/fonts/inter-var.woff2"
  as="font"
  type="font/woff2"
  crossOrigin="anonymous"
/>

// Use font-display
@font-face {
  font-family: 'Inter';
  font-display: swap;
  src: url('/fonts/inter-var.woff2');
}
```

### 3. Resource Prioritization
```html
<!-- High priority -->
<link rel="preload" href="/critical.css" as="style">
<link rel="preload" href="/hero.jpg" as="image">

<!-- Preconnect -->
<link rel="preconnect" href="https://api.example.com">

<!-- Prefetch -->
<link rel="prefetch" href="/next-page.js">
```

### 4. Adaptive Loading
```typescript
import { shouldLoadHighQuality, getNetworkInfo } from '@/lib/performance';

const imageQuality = shouldLoadHighQuality() ? 'high' : 'low';

const networkInfo = getNetworkInfo();
if (networkInfo?.saveData) {
  // Reduce data usage
}
```

### 5. Battery Optimization
```typescript
import { shouldOptimizeForBattery } from '@/lib/mobile';

const optimize = await shouldOptimizeForBattery();
if (optimize) {
  // Reduce animations, lower quality
}
```

---

## 🧪 Testing

### Lighthouse
```bash
# Run Lighthouse audit
npx lighthouse https://your-site.com --view

# Specific categories
npx lighthouse https://your-site.com \
  --only-categories=performance,seo \
  --output=html \
  --output-path=./report.html
```

### PageSpeed Insights
https://pagespeed.web.dev/

### WebPageTest
https://www.webpagetest.org/

### Chrome DevTools
1. Open DevTools (F12)
2. Performance tab
3. Record page load
4. Analyze metrics

### Web Vitals Extension
Install: [Chrome Web Store](https://chrome.google.com/webstore/detail/web-vitals)

---

## 📊 Performance Checklist

### Images
- [ ] Use Next.js Image component
- [ ] Implement lazy loading
- [ ] Use modern formats (AVIF, WebP)
- [ ] Optimize image sizes
- [ ] Add explicit dimensions
- [ ] Use responsive images

### JavaScript
- [ ] Code splitting implemented
- [ ] Defer non-critical scripts
- [ ] Remove unused code
- [ ] Minimize bundle size
- [ ] Use dynamic imports
- [ ] Implement tree shaking

### CSS
- [ ] Inline critical CSS
- [ ] Remove unused CSS
- [ ] Minimize CSS files
- [ ] Use CSS containment
- [ ] Optimize animations

### Fonts
- [ ] Preload critical fonts
- [ ] Use font-display: swap
- [ ] Subset fonts
- [ ] Use variable fonts
- [ ] Self-host fonts

### Network
- [ ] Enable compression
- [ ] Use HTTP/2 or HTTP/3
- [ ] Implement caching
- [ ] Use CDN
- [ ] Minimize redirects

### Mobile
- [ ] Mobile-first design
- [ ] Touch-optimized UI
- [ ] Responsive images
- [ ] Fast tap targets
- [ ] Reduced motion support

---

## 🎯 Performance Targets

### Lighthouse Scores
- Performance: > 90
- Accessibility: > 90
- Best Practices: > 95
- SEO: > 95

### Core Web Vitals
- LCP: < 2.5s
- FID/INP: < 100ms / < 200ms
- CLS: < 0.1

### Additional Metrics
- TTFB: < 600ms
- FCP: < 1.8s
- Speed Index: < 3.0s
- Time to Interactive: < 3.8s

---

## 🔗 Resources

- [Web.dev - Core Web Vitals](https://web.dev/vitals/)
- [Next.js Performance](https://nextjs.org/docs/advanced-features/measuring-performance)
- [AMP Project](https://amp.dev/)
- [Chrome DevTools](https://developer.chrome.com/docs/devtools/)
- [Lighthouse CI](https://github.com/GoogleChrome/lighthouse-ci)

---

**Status**: ✅ Complete  
**Version**: 1.0.0  
**Last Updated**: November 3, 2025
