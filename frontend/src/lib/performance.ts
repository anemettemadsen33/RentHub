/**
 * Performance optimization utilities for Core Web Vitals
 */

// Core Web Vitals thresholds (in milliseconds)
export const WEB_VITALS = {
  LCP: {
    GOOD: 2500,
    NEEDS_IMPROVEMENT: 4000,
  },
  FID: {
    GOOD: 100,
    NEEDS_IMPROVEMENT: 300,
  },
  CLS: {
    GOOD: 0.1,
    NEEDS_IMPROVEMENT: 0.25,
  },
  FCP: {
    GOOD: 1800,
    NEEDS_IMPROVEMENT: 3000,
  },
  TTFB: {
    GOOD: 800,
    NEEDS_IMPROVEMENT: 1800,
  },
  INP: {
    GOOD: 200,
    NEEDS_IMPROVEMENT: 500,
  },
};

interface WebVitalMetric {
  name: string;
  value: number;
  rating: 'good' | 'needs-improvement' | 'poor';
  delta: number;
  id: string;
}

/**
 * Report Web Vitals to analytics
 */
export function reportWebVitals(metric: WebVitalMetric) {
  if (typeof window === 'undefined') return;

  // Log to console in development
  if (process.env.NODE_ENV === 'development') {
    console.log('Web Vital:', metric);
  }

  // Send to analytics service
  if (window.gtag) {
    window.gtag('event', metric.name, {
      value: Math.round(metric.name === 'CLS' ? metric.value * 1000 : metric.value),
      event_category: 'Web Vitals',
      event_label: metric.id,
      non_interaction: true,
    });
  }

  // Send to custom analytics endpoint
  if (navigator.sendBeacon) {
    const body = JSON.stringify({
      metric: metric.name,
      value: metric.value,
      rating: metric.rating,
      url: window.location.href,
      userAgent: navigator.userAgent,
    });

    navigator.sendBeacon('/api/v1/analytics/web-vitals', body);
  }
}

/**
 * Preload critical resources
 */
export function preloadCriticalAssets() {
  if (typeof window === 'undefined') return;

  // Preload critical fonts
  const fonts = [
    '/fonts/inter-var.woff2',
  ];

  fonts.forEach((font) => {
    const link = document.createElement('link');
    link.rel = 'preload';
    link.as = 'font';
    link.type = 'font/woff2';
    link.crossOrigin = 'anonymous';
    link.href = font;
    document.head.appendChild(link);
  });
}

/**
 * Defer non-critical JavaScript
 */
export function deferNonCriticalJS() {
  if (typeof window === 'undefined') return;

  // Defer third-party scripts
  const scripts = document.querySelectorAll('script[data-defer]');
  scripts.forEach((script) => {
    const newScript = document.createElement('script');
    newScript.src = script.getAttribute('src') || '';
    newScript.defer = true;
    document.body.appendChild(newScript);
  });
}

/**
 * Implement resource hints
 */
export function addResourceHints(domains: string[]) {
  if (typeof window === 'undefined') return;

  domains.forEach((domain) => {
    // DNS prefetch
    const dnsPrefetch = document.createElement('link');
    dnsPrefetch.rel = 'dns-prefetch';
    dnsPrefetch.href = domain;
    document.head.appendChild(dnsPrefetch);

    // Preconnect
    const preconnect = document.createElement('link');
    preconnect.rel = 'preconnect';
    preconnect.href = domain;
    preconnect.crossOrigin = 'anonymous';
    document.head.appendChild(preconnect);
  });
}

/**
 * Lazy load images with intersection observer
 */
export function lazyLoadImages(selector = 'img[data-src]') {
  if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
    return;
  }

  const images = document.querySelectorAll(selector);
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target as HTMLImageElement;
        const src = img.getAttribute('data-src');
        const srcset = img.getAttribute('data-srcset');

        if (src) img.src = src;
        if (srcset) img.srcset = srcset;

        img.removeAttribute('data-src');
        img.removeAttribute('data-srcset');
        observer.unobserve(img);
      }
    });
  }, {
    rootMargin: '50px 0px',
    threshold: 0.01,
  });

  images.forEach((img) => imageObserver.observe(img));
}

/**
 * Optimize third-party scripts
 */
export function optimizeThirdPartyScripts() {
  if (typeof window === 'undefined') return;

  // Delay loading of non-critical third-party scripts
  const delayedScripts = [
    'google-analytics',
    'facebook-pixel',
    'hotjar',
  ];

  let timeout: NodeJS.Timeout;

  const loadScripts = () => {
    clearTimeout(timeout);
    delayedScripts.forEach((scriptId) => {
      const script = document.querySelector(`script[data-id="${scriptId}"]`);
      if (script) {
        const newScript = document.createElement('script');
        newScript.src = script.getAttribute('data-src') || '';
        document.body.appendChild(newScript);
      }
    });
  };

  // Load after user interaction or after 5 seconds
  const events = ['mousedown', 'keydown', 'touchstart', 'scroll'];
  events.forEach((event) => {
    window.addEventListener(event, loadScripts, { once: true, passive: true });
  });

  timeout = setTimeout(loadScripts, 5000);
}

/**
 * Prefetch next pages
 */
export function prefetchPages(urls: string[]) {
  if (typeof window === 'undefined') return;

  urls.forEach((url) => {
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    document.head.appendChild(link);
  });
}

/**
 * Check if device is mobile
 */
export function isMobileDevice(): boolean {
  if (typeof window === 'undefined') return false;
  
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
    navigator.userAgent
  );
}

/**
 * Get network information
 */
export function getNetworkInfo() {
  if (typeof window === 'undefined' || !('connection' in navigator)) {
    return null;
  }

  const connection = (navigator as any).connection;
  return {
    effectiveType: connection.effectiveType,
    downlink: connection.downlink,
    rtt: connection.rtt,
    saveData: connection.saveData,
  };
}

/**
 * Adaptive loading based on network conditions
 */
export function shouldLoadHighQuality(): boolean {
  const networkInfo = getNetworkInfo();
  
  if (!networkInfo) return true;

  // Don't load high quality on slow connections or save data mode
  if (networkInfo.saveData) return false;
  if (networkInfo.effectiveType === 'slow-2g' || networkInfo.effectiveType === '2g') {
    return false;
  }

  return true;
}

/**
 * Performance budget checker
 */
export const PERFORMANCE_BUDGET = {
  // Max size in KB
  js: 300,
  css: 100,
  images: 500,
  fonts: 100,
  total: 1000,
};

/**
 * Monitor long tasks
 */
export function monitorLongTasks() {
  if (typeof window === 'undefined' || !('PerformanceObserver' in window)) {
    return;
  }

  try {
    const observer = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        console.warn('Long task detected:', {
          duration: entry.duration,
          startTime: entry.startTime,
        });
      }
    });

    observer.observe({ entryTypes: ['longtask'] });
  } catch (e) {
    // Long task API not supported
  }
}

/**
 * Optimize images based on device pixel ratio
 */
export function getOptimalImageSize(
  baseWidth: number,
  baseHeight: number
): { width: number; height: number } {
  if (typeof window === 'undefined') {
    return { width: baseWidth, height: baseHeight };
  }

  const dpr = window.devicePixelRatio || 1;
  const maxDpr = shouldLoadHighQuality() ? Math.min(dpr, 2) : 1;

  return {
    width: Math.round(baseWidth * maxDpr),
    height: Math.round(baseHeight * maxDpr),
  };
}

/**
 * Request idle callback polyfill
 */
export const requestIdleCallback =
  typeof window !== 'undefined' && 'requestIdleCallback' in window
    ? window.requestIdleCallback
    : (callback: IdleRequestCallback) => setTimeout(callback, 1);

/**
 * Cancel idle callback polyfill
 */
export const cancelIdleCallback =
  typeof window !== 'undefined' && 'cancelIdleCallback' in window
    ? window.cancelIdleCallback
    : (id: number) => clearTimeout(id);
