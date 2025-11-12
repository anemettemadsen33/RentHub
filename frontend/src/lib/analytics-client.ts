// Basic Web Vital metric shape
export type WebVitalMetric = {
  metric: 'FCP' | 'LCP' | 'FID' | 'CLS' | 'TTFB' | 'INP';
  value: number;
  rating: 'good' | 'needs-improvement' | 'poor';
  url: string;
  userAgent?: string;
};

// Generic analytics event (conversion / interaction / system)
export interface AnalyticsEvent {
  type: string; // e.g. booking_submitted, wishlist_toggled, pageview
  payload?: Record<string, any>;
  timestamp: string; // ISO string
  user?: { id: string | number; role?: string } | null;
}

const API_BASE = process.env.NEXT_PUBLIC_API_BASE_URL || process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1';

// ---------------------------------------------------------------------------
// Web Vitals (sent immediately - small volume, important for performance)
// ---------------------------------------------------------------------------
export async function sendWebVital(metric: WebVitalMetric) {
  // Respect consent: require overall consent AND performance category
  if (!analyticsConsentGranted || !consentCategories.performance) return;
  try {
    // Note: web-vitals endpoint is outside v1 group on backend
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
    await fetch(`${apiBase}/analytics/web-vitals`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...metric, clientId: getOrCreateClientId() }),
      keepalive: true,
    });
  } catch {
    // swallow errors - non-blocking beacon
  }
}

// ---------------------------------------------------------------------------
// Lightweight batching for generic analytics events
// Backend endpoint only validates basic shape; we wrap batches in a single
// "batch" event payload to avoid changing server code.
// ---------------------------------------------------------------------------
let analyticsUser: { id: string | number; role?: string } | null = null;
let analyticsConsentGranted = false;
let consentCategories: { analytics: boolean; performance: boolean; marketing: boolean } = { analytics: false, performance: false, marketing: false };
let clientId: string | null = null;
let eventQueue: AnalyticsEvent[] = [];
let flushTimer: number | null = null;

const FLUSH_INTERVAL = 5000; // ms
const MAX_BATCH_SIZE = 20;

export function setAnalyticsUserContext(user: { id: string | number; role?: string } | null) {
  analyticsUser = user;
}

export function setAnalyticsConsent(granted: boolean) {
  analyticsConsentGranted = granted;
  try {
    if (typeof document !== 'undefined') {
      document.cookie = `analytics_consent=${granted ? 'granted' : 'denied'}; path=/; max-age=${60 * 60 * 24 * 365}`;
    }
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('analytics_consent', granted ? 'granted' : 'denied');
    }
  } catch {}
}

export function initAnalyticsConsentFromStorage() {
  try {
    if (typeof document !== 'undefined') {
      const match = document.cookie.match(/(?:^|; )analytics_consent=([^;]+)/);
      if (match) {
        analyticsConsentGranted = decodeURIComponent(match[1]) === 'granted';
      }
    }
    if (typeof localStorage !== 'undefined') {
      const v = localStorage.getItem('analytics_consent');
      analyticsConsentGranted = v === 'granted';
      const catsRaw = localStorage.getItem('analytics_consent_categories');
      if (catsRaw) {
        try {
          const parsed = JSON.parse(catsRaw);
          consentCategories = {
            analytics: !!parsed.analytics,
            performance: !!parsed.performance,
            marketing: !!parsed.marketing,
          };
        } catch {}
      }
    }
  } catch {}
  return analyticsConsentGranted;
}

export function setConsentCategories(cats: { analytics?: boolean; performance?: boolean; marketing?: boolean }) {
  consentCategories = { ...consentCategories, ...cats };
  try {
    localStorage.setItem('analytics_consent_categories', JSON.stringify(consentCategories));
  } catch {}
}

function getOrCreateClientId() {
  if (clientId) return clientId;
  try {
    // Try cookie first
    if (typeof document !== 'undefined') {
      const m = document.cookie.match(/(?:^|; )rh_cid=([^;]+)/);
      if (m) {
        clientId = decodeURIComponent(m[1]);
        return clientId;
      }
    }
    // Fallback to localStorage
    if (typeof localStorage !== 'undefined') {
      const stored = localStorage.getItem('rh_cid');
      if (stored) {
        clientId = stored;
      } else {
        const generated = (typeof crypto !== 'undefined' && 'randomUUID' in crypto) ? (crypto as any).randomUUID() : `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
        clientId = generated;
        localStorage.setItem('rh_cid', generated);
      }
    } else {
      clientId = `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
    }
    // Write cookie for server correlation (clientId is non-null here)
    if (typeof document !== 'undefined' && clientId) {
      document.cookie = `rh_cid=${encodeURIComponent(clientId)}; path=/; max-age=${60 * 60 * 24 * 365}`;
    }
  } catch {
    clientId = `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
  }
  return clientId;
}

function scheduleFlush() {
  if (flushTimer !== null) return;
  flushTimer = window.setTimeout(() => {
    flushTimer = null;
    flushEvents();
  }, FLUSH_INTERVAL);
}

// Sampling config for pageviews (default 100%)
const pageviewSampleRate = (() => {
  if (typeof process !== 'undefined' && process.env.NEXT_PUBLIC_PAGEVIEW_SAMPLE_RATE) {
    const n = Number(process.env.NEXT_PUBLIC_PAGEVIEW_SAMPLE_RATE);
    if (!isNaN(n) && n >= 0 && n <= 1) return n;
  }
  return 1; // default
})();

export function enqueueAnalyticsEvent(type: string, payload?: Record<string, any>) {
  // Respect consent categories: analytics events require analytics consent
  if (!analyticsConsentGranted || !consentCategories.analytics) return;
  // Sampling for pageviews
  if (type === 'pageview' && Math.random() > pageviewSampleRate) return;
  const event: AnalyticsEvent = {
    type,
    payload: { ...payload, clientId: getOrCreateClientId() },
    timestamp: new Date().toISOString(),
    user: analyticsUser,
  };
  eventQueue.push(event);
  if (eventQueue.length >= MAX_BATCH_SIZE) {
    flushEvents();
    return;
  }
  scheduleFlush();
}

// Marketing-specific events (require marketing consent category)
export function trackMarketingEvent(type: string, payload?: Record<string, any>) {
  if (!analyticsConsentGranted || !consentCategories.marketing) return;
  const event: AnalyticsEvent = {
    type,
    payload: { ...payload, clientId: getOrCreateClientId() },
    timestamp: new Date().toISOString(),
    user: analyticsUser,
  };
  eventQueue.push(event);
  if (eventQueue.length >= MAX_BATCH_SIZE) {
    flushEvents();
  } else {
    scheduleFlush();
  }
}

export async function flushEvents(force = false) {
  if (!force && eventQueue.length === 0) return;
  const toSend = [...eventQueue];
  eventQueue = [];
  if (flushTimer) {
    clearTimeout(flushTimer);
    flushTimer = null;
  }
  if (toSend.length === 0) return;
  // Wrap events in a single batch envelope so backend logs one entry
  const batchPayload = { events: toSend };
  try {
    await fetch(`${API_BASE}/analytics/pwa`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: 'batch', payload: batchPayload, timestamp: new Date().toISOString() }),
      keepalive: true,
    });
  } catch {
    // On failure push them back (best-effort retry next tick)
    eventQueue.unshift(...toSend);
    if (!flushTimer) scheduleFlush();
  }
}

// Flush before page unload / visibility hidden
if (typeof window !== 'undefined') {
  window.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') flushEvents(true);
  });
  window.addEventListener('beforeunload', () => flushEvents(true));
}

// Convenience immediate send (no batching) for critical events
export async function sendPwaEvent(type: string, payload?: Record<string, any>) {
  // Respect consent categories
  if (!analyticsConsentGranted || !consentCategories.analytics) return; // gate direct sends too
  try {
    await fetch(`${API_BASE}/analytics/pwa`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type, payload: { ...payload, clientId: getOrCreateClientId() }, timestamp: new Date().toISOString(), user: analyticsUser }),
      keepalive: true,
    });
  } catch {
    // Fallback to queue if direct send fails
    enqueueAnalyticsEvent(type, payload);
  }
}

// Page view helper -> batch (low priority)
export function trackPageView(url: string) {
  enqueueAnalyticsEvent('pageview', { url });
}

// Conversion tracking entry point (used by custom hook)
export function trackConversion(event: string, payload?: Record<string, any>) {
  enqueueAnalyticsEvent(event, payload);
}

