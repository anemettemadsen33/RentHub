import { NextRequest } from 'next/server';

export const runtime = 'edge';

const messages: Record<string, { name: string; short: string; description: string }> = {
  en: {
    name: 'RentHub – Rent Smarter',
    short: 'RentHub',
    description: 'Modern property rental platform',
  },
  ro: {
    name: 'RentHub – Închiriază inteligent',
    short: 'RentHub',
    description: 'Platformă modernă de închirieri',
  },
};

export async function GET(req: NextRequest) {
  const locale = req.headers.get('x-next-intl-locale') || req.headers.get('accept-language')?.split(',')[0].split('-')[0] || 'en';
  const m = messages[locale] || messages.en;
  return new Response(
    JSON.stringify({
      name: m.name,
      short_name: m.short,
      description: m.description,
      start_url: '/',
      display: 'standalone',
      background_color: '#020817',
      theme_color: '#3b82f6',
      lang: locale,
      scope: '/',
      id: '/',
      orientation: 'portrait',
      prefer_related_applications: false,
      icons: [
        { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png', purpose: 'any maskable' },
        { src: '/icons/icon-256.png', sizes: '256x256', type: 'image/png', purpose: 'any maskable' },
        { src: '/icons/icon-384.png', sizes: '384x384', type: 'image/png', purpose: 'any maskable' },
        { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png', purpose: 'any maskable' },
      ],
      shortcuts: [
        {
          name: 'Browse Properties',
          short_name: 'Properties',
          description: 'View available rental properties',
          url: '/properties',
          icons: [{ src: '/icons/icon-192.png', sizes: '192x192' }]
        },
        {
          name: 'My Bookings',
          short_name: 'Bookings',
          description: 'View your bookings',
          url: '/bookings',
          icons: [{ src: '/icons/icon-192.png', sizes: '192x192' }]
        }
      ]
    }),
    {
      headers: {
        'Content-Type': 'application/manifest+json; charset=utf-8',
        'Cache-Control': 'public, max-age=3600',
      },
    }
  );
}
