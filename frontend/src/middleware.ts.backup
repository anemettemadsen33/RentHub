// Simplified middleware - bypass next-intl for now to fix 404 issues
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export default function middleware(request: NextRequest) {
  // Just pass through - let Next.js handle routing
  return NextResponse.next();
}

export const config = {
  // Match all paths except static files and Next.js internals
  matcher: ['/((?!_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp)).*)', '/'],
};
