# Vercel Deployment Guide for RentHub Frontend

## Prerequisites

1. **Vercel Account**: Sign up at [vercel.com](https://vercel.com)
2. **GitHub Repository**: Push your code to GitHub
3. **Domain Name**: (Optional) Custom domain for production

## Step 1: Connect Repository

### Via Vercel Dashboard

1. Log in to [vercel.com](https://vercel.com)
2. Click "Add New..." → "Project"
3. Import your GitHub repository
4. Select the `frontend` directory as root

### Via Vercel CLI (Alternative)

```bash
# Install Vercel CLI
npm i -g vercel

# Navigate to frontend
cd frontend

# Login to Vercel
vercel login

# Deploy
vercel
```

## Step 2: Configure Project Settings

### Framework Preset
- **Framework**: Next.js
- **Root Directory**: `frontend/` (if monorepo)
- **Build Command**: `npm run build`
- **Output Directory**: `.next`
- **Install Command**: `npm install`

### Node.js Version
- Set to `18.x` or higher in Project Settings

## Step 3: Environment Variables

### Add via Dashboard

1. Go to Project Settings → Environment Variables
2. Add each variable from `.env.production`:

#### Required Variables

```bash
# API
NEXT_PUBLIC_API_URL=https://api.yourdomain.com

# Authentication
NEXTAUTH_URL=https://yourdomain.com
NEXTAUTH_SECRET=your-secret-here

# Google Maps
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=your-key

# Stripe
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_live_...

# Pusher/Reverb
NEXT_PUBLIC_PUSHER_KEY=your-key
NEXT_PUBLIC_PUSHER_CLUSTER=mt1
```

### Environment Scopes

- **Production**: Live environment
- **Preview**: Pull request previews
- **Development**: Local development

💡 Tip: Use different values for each environment.

### Bulk Import

Alternatively, use Vercel CLI:

```bash
# Import from .env.production
vercel env pull .env.local
vercel env add < .env.production
```

## Step 4: Build Settings

### Optimize Build

Update `next.config.ts`:

```typescript
const config: NextConfig = {
  // Production optimizations
  compress: true,
  poweredByHeader: false,
  generateEtags: true,
  
  // Image optimization
  images: {
    domains: ['yourdomain.com', 's3.amazonaws.com'],
    formats: ['image/avif', 'image/webp'],
    minimumCacheTTL: 60,
  },
  
  // Bundle analyzer (only in development)
  ...(process.env.ANALYZE === 'true' && {
    webpack: (config) => {
      config.plugins.push(
        new (require('@next/bundle-analyzer'))({
          enabled: true,
        })
      );
      return config;
    },
  }),
};
```

### Install Dependencies

```bash
npm install --save-dev @next/bundle-analyzer
```

## Step 5: Configure Custom Domain

### Add Domain

1. Go to Project Settings → Domains
2. Click "Add"
3. Enter your domain: `yourdomain.com`
4. Add `www` subdomain

### DNS Configuration

Add these records to your DNS provider:

```
Type    Name    Value
A       @       76.76.21.21
CNAME   www     cname.vercel-dns.com
```

### SSL Certificate

- Automatically provisioned by Vercel
- Auto-renewal enabled

## Step 6: Configure API Routes

### CORS Configuration

If using Next.js API routes:

```typescript
// middleware.ts
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  const response = NextResponse.next();
  
  // CORS headers
  response.headers.set('Access-Control-Allow-Origin', '*');
  response.headers.set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  response.headers.set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  
  return response;
}

export const config = {
  matcher: '/api/:path*',
};
```

## Step 7: Performance Optimization

### Enable Edge Functions

For API routes that can run on Edge:

```typescript
// app/api/hello/route.ts
export const runtime = 'edge';

export async function GET() {
  return new Response('Hello from Edge!');
}
```

### Enable ISR (Incremental Static Regeneration)

```typescript
// app/properties/page.tsx
export const revalidate = 3600; // Revalidate every hour
```

### Enable Image Optimization

```typescript
// Use Next.js Image component
import Image from 'next/image';

<Image
  src="/property.jpg"
  alt="Property"
  width={800}
  height={600}
  priority
/>
```

## Step 8: Configure Caching

### Add Cache Headers

Update `next.config.ts`:

```typescript
async headers() {
  return [
    {
      source: '/(.*)',
      headers: [
        {
          key: 'Cache-Control',
          value: 'public, max-age=3600, must-revalidate',
        },
      ],
    },
    {
      source: '/static/(.*)',
      headers: [
        {
          key: 'Cache-Control',
          value: 'public, max-age=31536000, immutable',
        },
      ],
    },
  ];
}
```

## Step 9: Setup Monitoring

### Vercel Analytics

```bash
npm install @vercel/analytics
```

```typescript
// app/layout.tsx
import { Analytics } from '@vercel/analytics/react';

export default function RootLayout({ children }) {
  return (
    <html>
      <body>
        {children}
        <Analytics />
      </body>
    </html>
  );
}
```

### Vercel Speed Insights

```bash
npm install @vercel/speed-insights
```

```typescript
import { SpeedInsights } from '@vercel/speed-insights/next';

export default function RootLayout({ children }) {
  return (
    <html>
      <body>
        {children}
        <SpeedInsights />
      </body>
    </html>
  );
}
```

### Sentry Integration

Already configured in `instrumentation.ts`.

## Step 10: Deploy

### Automatic Deployment

1. Push to main branch
2. Vercel automatically deploys
3. Monitor build in Vercel dashboard

### Manual Deployment

```bash
# Production deployment
vercel --prod

# Preview deployment
vercel
```

## Step 11: Setup Preview Deployments

### Automatic PR Previews

- Every pull request gets a unique URL
- Automatically deployed on push
- Perfect for testing before merge

### Configure Preview URLs

```typescript
// Detect preview environment
const isPreview = process.env.VERCEL_ENV === 'preview';
const apiUrl = isPreview 
  ? process.env.NEXT_PUBLIC_PREVIEW_API_URL 
  : process.env.NEXT_PUBLIC_API_URL;
```

## Step 12: Configure Redirects & Rewrites

Already configured in `vercel.json`:

```json
{
  "redirects": [
    {
      "source": "/old-path",
      "destination": "/new-path",
      "permanent": true
    }
  ],
  "rewrites": [
    {
      "source": "/api/:path*",
      "destination": "https://api.yourdomain.com/api/:path*"
    }
  ]
}
```

## Post-Deployment Checklist

### Verify Deployment

```bash
# Check build logs
vercel logs

# Test production URL
curl https://yourdomain.com

# Check API connectivity
curl https://yourdomain.com/api/health
```

### Performance Tests

```bash
# Install Lighthouse CI
npm install -g @lhci/cli

# Run audit
lhci autorun --collect.url=https://yourdomain.com
```

### Security Headers

Verify security headers:

```bash
curl -I https://yourdomain.com
```

Should include:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security`

## Continuous Deployment

### GitHub Integration

Automatic deployment on:
- ✅ Push to `main` → Production
- ✅ Push to `develop` → Preview
- ✅ Pull requests → Preview URL

### Deployment Protection

Enable in Project Settings:
- Require approval for production
- Enable deployment notifications
- Setup deployment hooks

## Rollback Procedure

### Via Dashboard

1. Go to Deployments
2. Find previous successful deployment
3. Click "..." → "Promote to Production"

### Via CLI

```bash
# List deployments
vercel ls

# Rollback to specific deployment
vercel rollback [deployment-url]
```

## Environment-Specific Configuration

### Production

```bash
NEXT_PUBLIC_APP_ENV=production
NEXT_PUBLIC_API_URL=https://api.yourdomain.com
```

### Staging

```bash
NEXT_PUBLIC_APP_ENV=staging
NEXT_PUBLIC_API_URL=https://staging-api.yourdomain.com
```

### Development

```bash
NEXT_PUBLIC_APP_ENV=development
NEXT_PUBLIC_API_URL=http://localhost:8000
```

## Troubleshooting

### Build Failures

Check build logs:
```bash
vercel logs [deployment-url]
```

Common issues:
- Missing environment variables
- TypeScript errors
- Missing dependencies

### Runtime Errors

Enable error tracking:
```typescript
// Sentry already configured
// Check Sentry dashboard for errors
```

### Performance Issues

Use Vercel Analytics to identify:
- Slow pages
- Large bundles
- Slow API calls

## Cost Optimization

### Free Tier Limits

- 100 GB bandwidth
- 100 GB-hours serverless execution
- 6,000 build minutes

### Optimize Bandwidth

```typescript
// Enable compression
module.exports = {
  compress: true,
};

// Optimize images
images: {
  formats: ['image/avif', 'image/webp'],
}
```

### Reduce Build Time

```typescript
// Disable source maps in production
productionBrowserSourceMaps: false,

// Skip linting during build
eslint: {
  ignoreDuringBuilds: true,
},

// Skip type checking during build
typescript: {
  ignoreBuildErrors: false, // Keep for safety
},
```

## Advanced Features

### Edge Middleware

```typescript
// middleware.ts
export function middleware(request: NextRequest) {
  // Geo-location based routing
  const country = request.geo?.country;
  
  if (country === 'RO') {
    return NextResponse.redirect('/ro');
  }
  
  return NextResponse.next();
}
```

### A/B Testing

```typescript
import { NextResponse } from 'next/server';

export function middleware(request: NextRequest) {
  const bucket = Math.random() < 0.5 ? 'a' : 'b';
  request.cookies.set('bucket', bucket);
  
  return NextResponse.next();
}
```

## Monitoring & Alerts

### Setup Alerts

1. Go to Project Settings → Notifications
2. Enable:
   - Deployment failed
   - Deployment succeeded
   - Custom webhook alerts

### Integrations

- Slack notifications
- Email alerts
- Webhook endpoints

## Best Practices

✅ Use environment variables for configuration
✅ Enable automatic deployments
✅ Use preview deployments for testing
✅ Monitor performance with Vercel Analytics
✅ Enable HTTPS only
✅ Use Edge Functions where possible
✅ Implement proper error boundaries
✅ Use ISR for dynamic content
✅ Optimize images with Next.js Image
✅ Enable compression
✅ Setup proper caching headers
✅ Monitor bundle size
✅ Use TypeScript for type safety

## Support & Resources

- **Vercel Documentation**: https://vercel.com/docs
- **Next.js Documentation**: https://nextjs.org/docs
- **Vercel Discord**: https://vercel.com/discord
- **Status Page**: https://vercel-status.com
