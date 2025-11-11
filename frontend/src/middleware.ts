// next-intl middleware with safe wrapper and adjusted matcher to exclude root '/'
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import createMiddleware from 'next-intl/middleware';
import { locales } from './i18n/config';

const intlMiddleware = createMiddleware({
  locales,
  defaultLocale: 'en',
  localePrefix: 'never',
  localeDetection: true,
});

export default function middleware(request: NextRequest) {
  // Bypass i18n middleware entirely in development to avoid Turbopack 404s
  if (process.env.NODE_ENV === 'development') {
    return NextResponse.next();
  }
  try {
    return intlMiddleware(request);
  } catch {
    // Fail open to avoid breaking routes
    return NextResponse.next();
  }
}

export const config = {
  // Exclude API, Next internals, static assets, and the root path '/'
  matcher: ['/((?!api|_next|_vercel|.*\\..*).+)'],
};
