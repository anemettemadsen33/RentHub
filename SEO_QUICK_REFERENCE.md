# SEO Quick Reference Guide

## Quick Commands

### Test SEO Implementation
```bash
# Test sitemap
curl http://localhost:3000/sitemap.xml

# Test robots.txt
curl http://localhost:3000/robots.txt

# Test API endpoints
curl http://localhost:8000/api/v1/seo/locations
curl http://localhost:8000/api/v1/seo/property-urls
curl http://localhost:8000/api/v1/seo/popular-searches
```

## Code Snippets

### 1. Add SEO to a Page

```typescript
// app/your-page/page.tsx
import { generateMetadata } from '@/lib/seo';

export const metadata = generateMetadata({
  title: 'Your Page Title',
  description: 'Your page description (max 160 chars)',
  keywords: ['keyword1', 'keyword2', 'keyword3'],
  canonical: '/your-page',
});

export default function YourPage() {
  return <div>Content</div>;
}
```

### 2. Add Property Schema

```typescript
import JsonLd from '@/components/seo/JsonLd';
import { getPropertySchema } from '@/lib/schema';

<JsonLd data={getPropertySchema({
  id: property.id,
  title: property.title,
  description: property.description,
  price: property.price,
  location: property.location,
  images: property.images,
  bedrooms: property.bedrooms,
  bathrooms: property.bathrooms,
  rating: property.rating,
  reviewCount: property.reviewCount,
})} />
```

### 3. Add Breadcrumbs

```typescript
import BreadcrumbSEO from '@/components/seo/BreadcrumbSEO';

<BreadcrumbSEO items={[
  { name: 'Properties', url: '/properties' },
  { name: 'New York', url: '/properties?location=New+York' },
  { name: property.title, url: `/properties/${property.id}` }
]} />
```

### 4. Generate Search Metadata

```typescript
import { generateSearchMetadata } from '@/lib/seo';

export const metadata = generateSearchMetadata({
  location: 'New York',
  minPrice: 1000,
  maxPrice: 5000,
  bedrooms: 2,
});
```

## File Structure

```
frontend/
├── src/
│   ├── app/
│   │   ├── layout.tsx          # Root layout with org schema
│   │   ├── sitemap.ts          # Dynamic sitemap
│   │   ├── robots.ts           # Robots.txt
│   │   └── page.tsx            # Pages with metadata
│   ├── components/
│   │   └── seo/
│   │       ├── JsonLd.tsx      # Schema component
│   │       └── BreadcrumbSEO.tsx # Breadcrumb component
│   └── lib/
│       ├── seo.ts              # SEO utilities
│       ├── schema.ts           # Schema markup
│       └── canonical.ts        # URL utilities

backend/
└── app/
    └── Http/
        └── Controllers/
            └── Api/
                └── SeoController.php # SEO API endpoints
```

## API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/v1/seo/locations` | GET | All unique locations |
| `/api/v1/seo/property-urls` | GET | Property IDs + dates |
| `/api/v1/seo/popular-searches` | GET | Popular search terms |
| `/api/v1/seo/properties/{id}/metadata` | GET | Property SEO data |
| `/api/v1/seo/organization` | GET | Organization schema |

## Schema Types Available

- `getOrganizationSchema()` - Company info
- `getWebsiteSchema()` - Site search
- `getPropertySchema(property)` - Property listings
- `getBreadcrumbSchema(items)` - Navigation
- `getSearchResultsSchema(properties)` - Search results
- `getFAQSchema(faqs)` - FAQ pages

## Meta Tags Generated

### Standard Meta Tags
- `title` - Page title
- `description` - Page description
- `keywords` - SEO keywords
- `robots` - Indexing instructions
- `canonical` - Canonical URL

### Open Graph (Social Media)
- `og:title` - Social title
- `og:description` - Social description
- `og:image` - Share image
- `og:url` - Page URL
- `og:type` - Content type

### Twitter Cards
- `twitter:card` - Card type
- `twitter:title` - Tweet title
- `twitter:description` - Tweet description
- `twitter:image` - Tweet image

## Environment Variables

```env
# Frontend (.env.local)
NEXT_PUBLIC_SITE_URL=https://renthub.com
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_GOOGLE_VERIFICATION=your-code
NEXT_PUBLIC_FB_VERIFICATION=your-code

# Backend (.env)
APP_URL=https://renthub.com
```

## Testing Checklist

- [ ] Sitemap accessible at `/sitemap.xml`
- [ ] Robots.txt accessible at `/robots.txt`
- [ ] Meta tags in page source
- [ ] Open Graph tags present
- [ ] JSON-LD schema in `<head>`
- [ ] Canonical URLs correct
- [ ] No trailing slashes in URLs
- [ ] Images have alt tags
- [ ] Breadcrumbs display correctly

## Validation Tools

1. **Google Rich Results Test**
   - URL: https://search.google.com/test/rich-results
   - Validates schema markup

2. **Meta Tags Checker**
   - URL: https://metatags.io/
   - Validates social media cards

3. **PageSpeed Insights**
   - URL: https://pagespeed.web.dev/
   - Performance + SEO

4. **Lighthouse (Chrome DevTools)**
   - Open DevTools > Lighthouse
   - Run SEO audit

## Common Issues & Fixes

### Issue: Sitemap not updating
```bash
# Clear Next.js cache
rm -rf .next
npm run build
```

### Issue: Schema validation errors
```typescript
// Ensure all required fields are present
const schema = getPropertySchema({
  id: property.id,
  title: property.title,
  description: property.description,
  price: property.price,
  location: {
    address: property.address,
    city: property.city,
    state: property.state,
    country: property.country,
    postalCode: property.postal_code,
  },
  // ... other fields
});
```

### Issue: Duplicate meta tags
```typescript
// Only set metadata in ONE place per route
// Either in layout.tsx OR page.tsx, not both
```

## Performance Tips

1. **Cache SEO data**: Backend caches locations, URLs for 1 hour
2. **Optimize images**: Use Next.js Image component
3. **Lazy load schemas**: Only load needed schemas per page
4. **Minify content**: Next.js handles this automatically
5. **Use CDN**: For images and static assets

## Priority Settings

### Sitemap Priority Guide
- Homepage: `1.0`
- Main sections: `0.9`
- Search/Category: `0.8`
- Individual properties: `0.7`
- User pages: `0.6`

### Change Frequency Guide
- Homepage: `daily`
- Properties list: `hourly`
- Individual properties: `weekly`
- Static pages: `monthly`

## Next.js Config

```typescript
// next.config.ts
const nextConfig = {
  // Remove trailing slashes
  trailingSlash: false,
  
  // Image optimization
  images: {
    formats: ['image/avif', 'image/webp'],
  },
  
  // Compression
  compress: true,
  
  // Redirects for SEO
  async redirects() {
    return [{
      source: '/:path+/',
      destination: '/:path+',
      permanent: true,
    }];
  },
};
```

## Support & Resources

- Full Guide: `SEO_IMPLEMENTATION_GUIDE.md`
- Schema Reference: https://schema.org/
- Next.js SEO: https://nextjs.org/learn/seo
- Google Guide: https://developers.google.com/search

---

**Quick Reference Version**: 1.0.0  
**Last Updated**: November 3, 2025
