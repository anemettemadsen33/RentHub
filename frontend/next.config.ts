import type { NextConfig } from 'next';
import createNextIntlPlugin from 'next-intl/plugin';
import withPWAInit from 'next-pwa';
import { withSentryConfig } from '@sentry/nextjs';

// Re-enable i18n with proper configuration
const withNextIntl = createNextIntlPlugin('./i18n.ts');
const withPWA = withPWAInit({
  dest: 'public',
  register: false,
  skipWaiting: true,
  cacheOnFrontEndNav: true,
  disable: process.env.NODE_ENV === 'development',
  fallbacks: {
    document: '/offline',
    image: '/images/offline.svg',
  },
  runtimeCaching: [
    // Static assets from Next
    {
      urlPattern: /^https?:\/\/.*\/_next\/static\/.*/,
      handler: 'StaleWhileRevalidate',
      options: {
        cacheName: 'next-static',
        expiration: { maxEntries: 256, maxAgeSeconds: 7 * 24 * 60 * 60 },
      },
    },
    // OpenStreetMap tiles (used by Leaflet)
    {
      urlPattern: /^https?:\/\/(?:[abc]\.tile\.openstreetmap\.org)\/\d+\/\d+\/\d+\.png$/,
      handler: 'StaleWhileRevalidate',
      options: {
        cacheName: 'osm-tiles',
        expiration: { maxEntries: 512, maxAgeSeconds: 7 * 24 * 60 * 60 },
        cacheableResponse: { statuses: [0, 200] },
      },
    },
    // Images from allowed remote hosts
    {
      urlPattern: /^https?:\/\/(images\.unsplash\.com|.*\.amazonaws\.com|.*\.cloudfront\.net|.*\.on-forge\.com|api\.renthub\.com|localhost)\/.+\.(png|jpg|jpeg|gif|webp|avif)$/,
      handler: 'CacheFirst',
      options: {
        cacheName: 'image-cache',
        expiration: { maxEntries: 256, maxAgeSeconds: 30 * 24 * 60 * 60 },
        cacheableResponse: { statuses: [0, 200] },
      },
    },
    // Google fonts stylesheets
    {
      urlPattern: /^https?:\/\/fonts\.googleapis\.com\/css.*/,
      handler: 'StaleWhileRevalidate',
      options: { cacheName: 'google-fonts-stylesheets' },
    },
    // Google fonts files
    {
      urlPattern: /^https?:\/\/fonts\.gstatic\.com\/.*/,
      handler: 'CacheFirst',
      options: {
        cacheName: 'google-fonts-webfonts',
        expiration: { maxEntries: 64, maxAgeSeconds: 365 * 24 * 60 * 60 },
      },
    },
    // API GET requests (cache-first with revalidate)
    {
      urlPattern: /\/api\/v1\//,
      handler: 'NetworkFirst',
      method: 'GET',
      options: {
        cacheName: 'api-get',
        networkTimeoutSeconds: 5,
        expiration: { maxEntries: 256, maxAgeSeconds: 10 * 60 },
        cacheableResponse: { statuses: [0, 200] },
      },
    },
    // Background sync for POST
    {
      urlPattern: /\/api\/v1\//,
      handler: 'NetworkOnly',
      method: 'POST',
      options: {
        backgroundSync: {
          name: 'api-post-queue',
          options: { maxRetentionTime: 24 * 60 },
        },
      },
    },
    // Background sync for PUT
    {
      urlPattern: /\/api\/v1\//,
      handler: 'NetworkOnly',
      method: 'PUT',
      options: {
        backgroundSync: {
          name: 'api-put-queue',
          options: { maxRetentionTime: 24 * 60 },
        },
      },
    },
    // Background sync for DELETE
    {
      urlPattern: /\/api\/v1\//,
      handler: 'NetworkOnly',
      method: 'DELETE',
      options: {
        backgroundSync: {
          name: 'api-delete-queue',
          options: { maxRetentionTime: 24 * 60 },
        },
      },
    },
  ],
  customWorkerDir: 'worker',
});

const nextConfig: NextConfig = {
  reactStrictMode: true,
  async redirects() {
    return [
      {
        source: '/login',
        destination: '/auth/login',
        permanent: true,
      },
      {
        source: '/register',
        destination: '/auth/register',
        permanent: true,
      },
    ];
  },
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'images.unsplash.com',
      },
      {
        protocol: 'https',
        hostname: 'api.renthub.com',
      },
      {
        protocol: 'https',
        hostname: 'renthub-tbj7yxj7.on-forge.com',
      },
      {
        protocol: 'https',
        hostname: 'renthub-dji696t0.on-forge.com',
      },
      {
        protocol: 'https',
        hostname: '**.amazonaws.com', // S3 buckets
      },
      {
        protocol: 'https',
        hostname: '**.cloudfront.net', // CloudFront CDN
      },
      {
        protocol: 'http',
        hostname: 'localhost',
      },
      {
        protocol: 'https',
        hostname: 'cdn.simpleicons.org',
      },
      {
        protocol: 'https',
        hostname: 'res.cloudinary.com',
      },
    ],
    formats: ['image/avif', 'image/webp'], // Modern formats for better compression
    deviceSizes: [640, 750, 828, 1080, 1200, 1920, 2048, 3840],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
    minimumCacheTTL: 60, // Cache images for 60 seconds
  },
  env: {
    NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000',
    NEXT_PUBLIC_API_BASE_URL: process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api/v1',
  },
};

export default withSentryConfig(
  withPWA(withNextIntl(nextConfig)),
  {
    silent: true,
  }
);
