'use client';

import { useEffect, useState } from 'react';
import { setAnalyticsConsent, initAnalyticsConsentFromStorage, setConsentCategories } from '@/lib/analytics-client';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

type CategoryState = {
  analytics: boolean;
  performance: boolean;
  marketing: boolean;
};

export function ConsentBanner() {
  const [visible, setVisible] = useState(false);
  const [categories, setCategories] = useState<CategoryState>({ analytics: true, performance: true, marketing: false });

  useEffect(() => {
    // Show if no consent stored
    const granted = initAnalyticsConsentFromStorage();
    try {
      const fromLocal = typeof localStorage !== 'undefined' ? localStorage.getItem('analytics_consent') : null;
      const hasChoice = fromLocal === 'granted' || fromLocal === 'denied';
      setVisible(!granted && !hasChoice);
    } catch {
      setVisible(!granted);
    }
  }, []);

  if (!visible) return null;

  return (
    <div className="fixed inset-x-0 bottom-0 z-50 p-4">
      <Card className="mx-auto max-w-3xl p-4 shadow-lg border bg-white">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div className="text-sm text-gray-700">
            We use cookies to measure performance (Web Vitals), analytics (usage & conversions) and optional marketing. Choose categories or accept all.
            <div className="mt-3 flex flex-col gap-2 text-xs">
              <label className="flex items-center gap-2">
                <input type="checkbox" checked={categories.analytics} onChange={(e) => setCategories(c => ({ ...c, analytics: e.target.checked }))} /> Analytics
              </label>
              <label className="flex items-center gap-2">
                <input type="checkbox" checked={categories.performance} onChange={(e) => setCategories(c => ({ ...c, performance: e.target.checked }))} /> Performance
              </label>
              <label className="flex items-center gap-2">
                <input type="checkbox" checked={categories.marketing} onChange={(e) => setCategories(c => ({ ...c, marketing: e.target.checked }))} /> Marketing (optional)
              </label>
            </div>
          </div>
          <div className="flex gap-2 justify-end">
            <Button
              variant="outline"
              onClick={() => {
                setAnalyticsConsent(false);
                setVisible(false);
              }}
            >Decline</Button>
            <Button
              onClick={() => {
                // For now gating only on overall analytics/performance acceptance
                const overall = categories.analytics || categories.performance || categories.marketing;
                setAnalyticsConsent(overall);
                setConsentCategories(categories);
                setVisible(false);
              }}
            >Accept</Button>
          </div>
        </div>
      </Card>
    </div>
  );
}
