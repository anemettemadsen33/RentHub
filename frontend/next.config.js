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
    ],
    // If your backend serves absolute image URLs from e.g. CDN or app domain, add here:
    // domains: ['cdn.renthub.com', 'assets.renthub.com'],
  },
  experimental: {
    optimizeCss: true,
  },
};

// Integrate next-intl plugin so that useTranslations() can resolve the locales
// from next-intl.config.ts during dev, build and test (Playwright) runs.
const withNextIntl = require('next-intl/plugin')();

module.exports = withNextIntl(nextConfig);
