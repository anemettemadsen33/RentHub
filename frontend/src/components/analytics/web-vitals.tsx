"use client";
import { useEffect } from 'react';
import { onCLS, onFID, onLCP, onTTFB, onINP, onFCP, Metric } from 'web-vitals';
import { sendWebVital } from '@/lib/analytics-client';

export default function WebVitalsReporter() {
  useEffect(() => {
    const report = (m: Metric) => {
      const rating: 'good' | 'needs-improvement' | 'poor' = (m.rating as any) ?? 'good';
      sendWebVital({
        metric: m.name as any,
        value: m.value,
        rating,
        url: window.location.href,
        userAgent: navigator.userAgent,
      });
    };
    onFCP(report);
    onLCP(report);
    onFID(report);
    onCLS(report);
    onTTFB(report);
    onINP(report);
  }, []);
  return null;
}
