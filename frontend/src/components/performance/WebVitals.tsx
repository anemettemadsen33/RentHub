'use client';

import { useEffect } from 'react';
import { useReportWebVitals } from 'next/web-vitals';
import { reportWebVitals } from '@/lib/performance';

export default function WebVitals() {
  useReportWebVitals((metric) => {
    reportWebVitals(metric as any);
  });

  useEffect(() => {
    // Monitor performance on page load
    if (typeof window !== 'undefined' && window.performance) {
      const perfData = window.performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
      
      if (perfData) {
        const metrics = {
          dns: perfData.domainLookupEnd - perfData.domainLookupStart,
          tcp: perfData.connectEnd - perfData.connectStart,
          ttfb: perfData.responseStart - perfData.requestStart,
          download: perfData.responseEnd - perfData.responseStart,
          domInteractive: perfData.domInteractive,
          domComplete: perfData.domComplete,
          loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
        };

        // Log performance metrics in development
        if (process.env.NODE_ENV === 'development') {
          console.table(metrics);
        }
      }
    }
  }, []);

  return null;
}
