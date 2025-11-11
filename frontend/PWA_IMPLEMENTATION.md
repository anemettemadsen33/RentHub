# PWA Implementation (RentHub)

This document outlines the Progressive Web App features added to the RentHub frontend: service worker, offline fallback, install prompt, and push notifications.

## Features Overview
- Manifest (`public/manifest.webmanifest`)
- Icons (placeholder PNGs in `public/icons/` – replace with real assets)
- Service worker (custom `public/sw.js` + next-pwa generated worker)
- Offline fallback page (`src/app/_offline/page.tsx`)
- Install prompt capture (`PWAInstallPrompt` component)
- Automatic service worker registration (`ServiceWorkerRegister` component)
- Push notification subscription utility (`src/lib/push-notifications.ts`)

## Manifest
Located at `public/manifest.webmanifest` with basic metadata (name, theme color, icons). Update icons and colors as needed.

## Service Worker
Two SW layers:
1. `next-pwa` generated (`sw.js` in build output) for precaching & runtime caching.
2. Custom `public/sw.js` for push handling and minimal offline navigate fallback.

> If you need advanced caching strategies, configure `runtimeCaching` in `next.config.ts` under the `withPWA` options.

### Push Handling
The custom worker listens for `push` events and displays notifications using the payload's JSON fields:
```js
{
  title: 'Title',
  body: 'Body',
  data: { url: '/target' },
  actions: [{ action: 'open', title: 'Open App' }]
}
```

## Offline Fallback
`src/app/_offline/page.tsx` is automatically used by next-pwa when network & cache fail for a document request. It provides Retry and Back actions.

## Install Prompt
`PWAInstallPrompt` listens to `beforeinstallprompt`, stores the event, and surfaces an install toast with action. After user chooses, the deferred event is cleared.

## Registration
`ServiceWorkerRegister` registers `/sw.js` (custom layer). `next-pwa` registers its own worker automatically via config. Adjust `register` option in `next.config.ts` if you want manual control.

## Push Subscription Utility
`subscribeToPush()`:
- Requests Notification permission.
- Waits for ready service worker.
- Subscribes using VAPID public key (`NEXT_PUBLIC_VAPID_PUBLIC_KEY`).
- Persists subscription to backend at `/push/subscribe` (implement server endpoint accordingly).

`unsubscribeFromPush()` removes subscription and informs backend at `/push/unsubscribe`.

## Environment Variables
Add to `.env.local` (and example file):
```
NEXT_PUBLIC_VAPID_PUBLIC_KEY=YOUR_VAPID_PUBLIC_KEY_BASE64
```

## Next.js Config
`next.config.ts` wraps existing intl plugin with `withPWA`, enabling caching, skipWaiting, and register options. Adjust `disable` if you want SW active during development.

## Testing
1. `npm run build && npm run start`
2. Open Lighthouse (Chrome DevTools) -> Check PWA scores.
3. Go offline (DevTools Network: Offline) and navigate to uncached route; offline fallback page should appear.
4. Trigger a push (simulate via DevTools Application -> Service Workers -> Push) with JSON payload; notification should display.

## Future Enhancements
- Add runtimeCaching with image/doc/font fallbacks.
- Display in-app UI for new SW version (postMessage lifecycle).
- Advanced notification actions (analytics, deep links).
- Background sync for queued offline mutations.

## Troubleshooting
- If install prompt never appears: ensure served over HTTPS (except localhost) and user has not installed already.
- If push fails: verify VAPID key format and backend endpoint correctness.
- SW updates not applying: clear site data (Application tab) and reload.

---
Maintained as part of ongoing platform enhancements.
