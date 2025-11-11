import * as Sentry from '@sentry/nextjs';

// Server / Edge instrumentation entrypoint for Next.js App Router.
// Next.js calls register() once per process / worker startup.
export async function register() {
  if ((globalThis as any).__sentry_initialized__) return;
  Sentry.init({
    dsn: process.env.SENTRY_DSN || process.env.NEXT_PUBLIC_SENTRY_DSN,
    tracesSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_TRACES_SAMPLE_RATE ?? 0.2),
    profilesSampleRate: Number(process.env.NEXT_PUBLIC_SENTRY_PROFILES_SAMPLE_RATE ?? 0.1),
    environment: process.env.NODE_ENV,
    beforeSend(event, hint) {
      // Filter out low-priority errors if needed
      return event;
    },
  });
  (globalThis as any).__sentry_initialized__ = true;
}
