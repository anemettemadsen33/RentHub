import * as Sentry from '@sentry/nextjs';

// Next.js will call this during the server/edge bootstrapping phase.
// We initialize Sentry here to align with App Router requirements
// and remove the runtime warning about missing instrumentation.
export function register() {
  const dsn = process.env.SENTRY_DSN || process.env.NEXT_PUBLIC_SENTRY_DSN;

  // Avoid double-initialization if Next reloads in dev
  if ((globalThis as any).__sentry_initialized__) return;

  Sentry.init({
    dsn,
    tracesSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_TRACES_SAMPLE_RATE ?? 0.2),
    profilesSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_PROFILES_SAMPLE_RATE ?? 0.1),
    environment: process.env.NODE_ENV,
  });

  (globalThis as any).__sentry_initialized__ = true;
}
