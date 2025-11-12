/********
 * Next.js configuration
 */
/** @type {import('next').NextConfig} */
const nextConfig = {
  images: {
    remotePatterns: [
      { protocol: 'https', hostname: 'images.unsplash.com' },
      { protocol: 'https', hostname: 'res.cloudinary.com' },
      { protocol: 'https', hostname: 'cdn.simpleicons.org' },
      { protocol: 'https', hostname: 'renthub-tbj7yxj7.on-forge.com' },
      { protocol: 'https', hostname: '**.amazonaws.com' },
      { protocol: 'https', hostname: '**.cloudfront.net' },
    ],
  },
  env: {
    NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL || 'https://renthub-tbj7yxj7.on-forge.com/api',
    NEXT_PUBLIC_API_BASE_URL: process.env.NEXT_PUBLIC_API_BASE_URL || 'https://renthub-tbj7yxj7.on-forge.com/api/v1',
  },
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'https://renthub-tbj7yxj7.on-forge.com/api/:path*',
      },
    ];
  },
  experimental: {
    optimizeCss: true,
  },
};

// Integrate next-intl plugin so that useTranslations() can resolve the locales
// from next-intl.config.ts during dev, build and test (Playwright) runs.
const withNextIntl = require('next-intl/plugin')();

module.exports = withNextIntl(nextConfig);
