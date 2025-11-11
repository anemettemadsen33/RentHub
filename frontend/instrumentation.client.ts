import * as Sentry from '@sentry/nextjs';

// Client-side instrumentation (executed once on first load / HMR replace)
export function register() {
  if ((window as any).__sentry_initialized_client__) return;
  Sentry.init({
    dsn: process.env.NEXT_PUBLIC_SENTRY_DSN || process.env.SENTRY_DSN,
    tracesSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_TRACES_SAMPLE_RATE ?? 0.2),
    profilesSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_PROFILES_SAMPLE_RATE ?? 0.1),
    replaysSessionSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_REPLAYS_SESSION_SAMPLE_RATE ?? 0.05),
    replaysOnErrorSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_REPLAYS_ON_ERROR_SAMPLE_RATE ?? 1.0),
    environment: process.env.NODE_ENV,
    integrations: [
      Sentry.browserTracingIntegration(),
      Sentry.replayIntegration(),
    ],
  });
  (window as any).__sentry_initialized_client__ = true;
}
