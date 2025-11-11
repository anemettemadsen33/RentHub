"use client";

import { useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { X, Scale, ArrowRight } from 'lucide-react';
import apiClient from '@/lib/api-client';
import { notify } from '@/lib/notify';
import { useTranslations } from 'next-intl';
import { cn } from '@/lib/utils';

interface ComparisonBarProps {
  className?: string;
}

export function ComparisonBar({ className }: ComparisonBarProps) {
  const router = useRouter();
  const [count, setCount] = useState(0);
  const [isVisible, setIsVisible] = useState(false);
  const tNotify = useTranslations('notify');
  const tComparison = useTranslations('comparison');

  const readLocalComparison = useCallback(() => {
    try {
      const raw = localStorage.getItem('comparison');
      const ids: number[] = raw ? JSON.parse(raw) : [];
      setCount(ids.length);
      setIsVisible(ids.length > 0);
    } catch (e) {
      // ignore parse error
    }
  }, []);

  const fetchCount = useCallback(async () => {
    if (process.env.NEXT_PUBLIC_E2E === 'true') {
      // Avoid network in stub mode
      readLocalComparison();
      return;
    }
    try {
      const { data } = await apiClient.get('/property-comparison', { timeout: 5000 });
      const newCount = data.properties?.length || 0;
      setCount(newCount);
      setIsVisible(newCount > 0);
    } catch (error) {
      // Graceful degradation: use localStorage as fallback
      readLocalComparison();
    }
  }, [readLocalComparison]);

  useEffect(() => {
    // In stub/E2E mode, derive from localStorage immediately and subscribe to storage events
    if (process.env.NEXT_PUBLIC_E2E === 'true') {
      readLocalComparison();
      const onStorage = (e: StorageEvent) => {
        if (e.key === 'comparison') readLocalComparison();
      };
      window.addEventListener('storage', onStorage);
      return () => window.removeEventListener('storage', onStorage);
    }
    
    // Use localStorage as primary source
    readLocalComparison();
    
    // Poll backend less frequently (every 30 seconds instead of 5)
    const interval = setInterval(fetchCount, 30000);
    return () => clearInterval(interval);
  }, [readLocalComparison, fetchCount]);

  const clearAll = useCallback(async () => {
    if (process.env.NEXT_PUBLIC_E2E === 'true') {
      localStorage.removeItem('comparison');
      setCount(0);
      setIsVisible(false);
      return;
    }
    try {
      // Lazy-load CSRF if not already fetched (avoid importing ensureCsrfCookie at top to keep bundle small)
      const { ensureCsrfCookie } = await import('@/lib/api-client');
      await ensureCsrfCookie();
      await apiClient.delete('/property-comparison/clear');
      setCount(0);
      setIsVisible(false);
    } catch (error) {
      console.error('Failed to clear comparison:', error);
      // Fallback to local cleanup so UI doesn't get stuck if backend rejects with 419 or is offline
      try { localStorage.removeItem('comparison'); } catch {}
      setCount(0);
      setIsVisible(false);
      notify.error({ title: tNotify('errorClearComparison') });
    }
  }, [tNotify]);

  if (!isVisible) return null;

  return (
    <div
      className={cn(
        'fixed bottom-6 left-1/2 -translate-x-1/2 z-50 transition-all duration-300',
        className
      )}
    >
      <Card className="shadow-lg border-2 border-primary/20 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
        <div className="flex items-center gap-4 px-6 py-4">
          <div className="flex items-center gap-2">
            <Scale className="h-5 w-5 text-primary" />
            <span className="font-medium">
              {tComparison('itemsCount', { count })}
            </span>
            <Badge variant="secondary">{count}/4</Badge>
          </div>

          <div className="flex items-center gap-2">
            <Button
              variant="default"
              size="sm"
              asChild
              disabled={count < 2}
            >
              <Link href="/property-comparison">
                {tComparison('compareNow')}
                <ArrowRight className="ml-2 h-4 w-4" />
              </Link>
            </Button>

            <Button
              variant="ghost"
              size="icon"
              onClick={clearAll}
              className="h-8 w-8"
              aria-label={tComparison('clearAll')}
            >
              <X className="h-4 w-4" />
            </Button>
          </div>
        </div>
      </Card>
    </div>
  );
}
