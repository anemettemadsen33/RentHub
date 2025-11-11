# SEO & Analytics Implementation Guide

This document explains the SEO, structured data, sitemap, web vitals, PWA & conversion analytics stack in the frontend, and how it integrates with the Laravel backend.

## Overview

Components covered:
- Metadata (Next.js App Router `generateMetadata` + global `metadata` export)
- JSON-LD Structured Data (Organization + Property details)
- Dynamic Sitemap generation
- Web Vitals collection & transport
- PWA events & performance beacons
- Conversion Tracking (bookings, wishlist) with lightweight batching
- User context propagation to analytics

Backend counterparts are primarily in `backend/app/Http/Controllers/Api/SeoController.php` and `PerformanceController.php` plus routes in `backend/routes/api.php`.

---
## 1. Global Metadata
File: `src/app/layout.tsx`

Defines base tags (title, description, OpenGraph, Twitter, icons, manifest, robots). Uses `metadataBase` derived from env (`NEXT_PUBLIC_SITE_URL` fallback). This ensures canonical & OG URLs resolve correctly when Next.js serializes metadata.

### Extending
Add additional global tags (e.g. `keywords`, `verification`) by extending the exported `metadata` object.

---
## 2. Page-Level Metadata (Dynamic)
File: `src/app/properties/[id]/page.tsx`

Uses `export async function generateMetadata({ params })` to fetch property-specific SEO data from backend endpoint:
`GET /api/v1/seo/properties/{id}/metadata`.

Populates:
- Dynamic title template
- Description
- OpenGraph image
- Twitter card image

If backend call fails, falls back to a generic title/description.

### Add Metadata to Another Route
Implement `generateMetadata` in the desired route segment file and return a `Metadata` object. Prefer server components for metadata generation to avoid client bundle bloat.

---
## 3. Structured Data (JSON-LD)
Files:
- `src/components/seo/organization-schema.tsx` (global Organization JSON-LD injected in layout)
- Inline Property JSON-LD inside `properties/[id]/page.tsx` via a small component.

Pattern: Render a `<script type="application/ld+json">{...}</script>` tag with serialized JSON-LD. Keep only necessary fields (avoid leaking PII). Ensure values are stable & properly escaped (`JSON.stringify`).

### Adding New Schema Types
Create a component under `src/components/seo/` returning the `<script>` tag. Example: `event-schema.tsx` for events, `breadcrumb-schema.tsx` for breadcrumb navigation.

---
## 4. Dynamic Sitemap
File: `src/app/sitemap.ts`

Implements Next.js Sitemap route. Fetches property URL list from backend:
`GET /api/v1/seo/property-urls` returning array of slug / id references.

Combines with a static array of core routes. Returns objects with:
- `url`
- `lastModified`

Extend by adding more static routes or by calling additional backend endpoints (e.g., blog posts, categories).

### Validation
Access `/sitemap.xml` after build or with dev server running. Ensure status 200 and that property URLs appear. Search engines will auto-detect if referenced in `robots.txt` (add if not present).

---
## 5. Web Vitals Collection
Files:
- `src/components/analytics/web-vitals.tsx`
- `src/lib/analytics-client.ts` (function `sendWebVital`)

Uses `web-vitals` library to gather FCP, LCP, CLS, FID, TTFB, INP. Metrics are POSTed to:
`POST /api/v1/analytics/web-vitals` (public beacon endpoint).

Backend caches daily metrics and logs poor results. Summaries & recommendations delivered via `PerformanceController` methods.

### Extending
Add custom fields (e.g. `connectionType`) by extending payload composition in the reporter component.

---
## 6. PWA & Generic Events
Function: `sendPwaEvent` and batching queue in `src/lib/analytics-client.ts`.

PWA events (install prompt accepted, SW updated, offline fallback hits, pageview) previously sent individually. We added a lightweight batching layer:
- Queue events (max 20 or 5s interval) -> single `batch` envelope to `POST /api/v1/analytics/pwa`.
- Critical events can still call `sendPwaEvent` directly.

Listeners flush queue on `visibilitychange` (hidden) and `beforeunload`.

---
## 7. Conversion Tracking Hook
File: `src/hooks/use-conversion-tracking.ts`

Provides:
- `trackBookingSubmit({...})` – fires after successful booking creation.
- `trackWishlistToggle(propertyId, added)` – fires when a user toggles favorite status.

Both call `trackConversion` (queue) which ends up inside the analytics batching pipeline.

Debounce: booking submissions within 1 second are deduped locally to avoid double clicks creating duplicate conversion events.

### Adding More Conversions
Add a new method in the hook (e.g. `trackSearchPerformed`) that wraps `trackConversion('search_performed', {...})`. Ensure consistent naming (snake_case) and include minimal identifying fields (avoid sensitive data).

---
## 8. User Context Propagation
File changes:
- `src/components/providers.tsx` – sets user context (id, role) into analytics via `setAnalyticsUserContext`.
- Each queued event includes `user` when available. Anonymous users record `user: null`.

Benefit: Attribution for conversions & cohort analysis without adding user logic in every event call.

### Privacy Considerations
Only user id & role are included. Avoid adding email or PII. If more context is needed (e.g., subscription tier), update `setAnalyticsUserContext` payload carefully.

---
## 9. Event Shapes
Queued analytics events structure:
```
{
  type: string;           // e.g. "booking_submitted"
  payload?: object;       // event-specific data
  timestamp: ISOString;
  user?: { id, role } | null;
}
```
Batches are delivered inside a parent event:
```
{
  type: 'batch',
  payload: { events: AnalyticsEvent[] },
  timestamp: ISOString
}
```
If backend later wants explicit batch ingestion, create a new endpoint. For now it just logs the wrapped events.

---
## 10. Failure & Resilience
- If `fetch` for batch fails, events are re-queued and another flush attempt is scheduled.
- On page unload/hidden visibility, a forced flush occurs using `keepalive` to maximize delivery probability.
- Critical single events fallback to enqueue on direct send failure.

---
## 11. Adding New Analytics Dimensions
1. Extend `useConversionTracking` or create a domain-specific hook (e.g., `useSearchTracking`).
2. Call `trackConversion('event_name', { key: value })`.
3. Ensure server logging/parsing if structured analysis required.

---
## 12. Testing Checklist
- Visit a property detail page: confirm metadata & JSON-LD in HTML (view-source).
- Load `/sitemap.xml`: property URLs present.
- Perform an action (add to wishlist, submit booking) and verify network request to `/analytics/pwa` containing corresponding event (batched or single).
- Simulate poor Web Vital (throttle network) and ensure request to `/analytics/web-vitals` appears.
- Log in/out: events after login should include user context.

---
## 13. Future Enhancements
- Server-side aggregation & dashboard endpoint for conversion metrics.
- Consent management integration (filter events unless analytics consent granted).
- Add `clientId` cookie to stitch anonymous + authenticated journeys.
- Introduce sampling for high-volume, low-value events (pageview) under heavy traffic.
- Backend endpoint for raw batch ingestion (avoid wrapping inside `batch`).

---
## 14. Quick Reference
| Concern | File | Key Export |
|---------|------|-----------|
| Global metadata | `src/app/layout.tsx` | `metadata` |
| Property metadata | `src/app/properties/[id]/page.tsx` | `generateMetadata` |
| Organization JSON-LD | `src/components/seo/organization-schema.tsx` | `OrganizationSchema` |
| Property JSON-LD | `src/app/properties/[id]/page.tsx` | Inline component |
| Sitemap | `src/app/sitemap.ts` | default export |
| Web Vitals reporter | `src/components/analytics/web-vitals.tsx` | default component |
| Analytics client | `src/lib/analytics-client.ts` | batching + send APIs |
| Conversion hook | `src/hooks/use-conversion-tracking.ts` | `useConversionTracking` |
| User context injection | `src/components/providers.tsx` | `AnalyticsUserInitializer` |

---
## 15. Environment Variables
Ensure in `.env.local` (frontend):
```
NEXT_PUBLIC_SITE_URL=https://your-domain.com
NEXT_PUBLIC_API_BASE_URL=https://api.your-domain.com/api/v1
```

---
## 16. Security & Privacy Notes
- Do NOT log PII or sensitive booking/payment data in analytics payloads.
- Keep user context minimal (id, role). Hash or pseudonymize if regulatory requirements evolve.
- Consider gating analytics behind user consent for GDPR compliance.

---
## 17. Maintenance
When adding major analytics features, update this doc to keep it a living reference for onboarding and audits.

---
Happy shipping! 🚀
