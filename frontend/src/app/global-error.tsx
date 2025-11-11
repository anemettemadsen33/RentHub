'use client';

import * as Sentry from '@sentry/nextjs';
import { useEffect } from 'react';

// Global error boundary for the entire app tree (root-level)
// This complements route-segment error.tsx files and ensures
// unhandled render errors are captured and a user-friendly UI is shown.
export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Report to Sentry (debounced by SDK if needed)
    Sentry.captureException(error);
    // Helpful console in dev
    if (process.env.NODE_ENV === 'development') {
      // eslint-disable-next-line no-console
      console.error('Global error captured:', error);
    }
  }, [error]);

  return (
    <html>
      <body>
        <div className="min-h-screen flex items-center justify-center p-6">
          <div className="w-full max-w-lg rounded-lg border bg-white p-6 shadow-sm">
            <h2 className="text-xl font-semibold mb-2">Something went wrong</h2>
            <p className="text-sm text-gray-600 mb-4">
              An unexpected error occurred. Our team has been notified.
            </p>
            <div className="flex gap-3">
              <button
                onClick={() => reset()}
                className="px-4 py-2 rounded bg-primary text-white hover:bg-primary/90"
              >
                Try again
              </button>
              <button
                onClick={() => (window.location.href = '/')}
                className="px-4 py-2 rounded border hover:bg-gray-50"
              >
                Go home
              </button>
            </div>
          </div>
        </div>
      </body>
    </html>
  );
}
