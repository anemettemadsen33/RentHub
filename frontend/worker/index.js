/* Custom Workbox worker injected by next-pwa */
/* eslint-disable no-undef */
self.__WB_DISABLE_DEV_LOGS = true;

self.addEventListener('message', (event) => {
  if (!event.data) return;
  const { command } = event.data;
  if (command === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

self.addEventListener('push', (event) => {
  if (!event.data) return;
  let payload = {};
  try { payload = event.data.json(); } catch { payload = { title: 'RentHub', body: event.data.text() }; }
  const title = payload.title || 'RentHub';
  const options = {
    body: payload.body || 'You have a new notification',
    icon: '/icons/icon-192.png',
    badge: '/icons/icon-192.png',
    data: payload.data || {},
    actions: payload.actions || []
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || '/';
  event.waitUntil(
    (async () => {
      // analytics ping
      try { fetch('/api/v1/analytics/pwa', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ type: 'push_notification_clicked', url: targetUrl }) }); } catch {}
      const allClients = await clients.matchAll({ includeUncontrolled: true, type: 'window' });
      for (const client of allClients) {
        if (client.url === targetUrl && 'focus' in client) return client.focus();
      }
      if (clients.openWindow) return clients.openWindow(targetUrl);
    })()
  );
});

// Simple navigation fallback handled by next-pwa fallbacks config

// Custom caching for property list responses (NetworkFirst already defined, but we keep last successful JSON for offline reuse)
self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method === 'GET' && /\/api\/v1\/properties(\?|$)/.test(request.url)) {
    event.respondWith((async () => {
      const cache = await caches.open('properties-last');
      try {
        const networkResp = await fetch(request);
        if (networkResp.ok) {
          // Clone & store
          const clone = networkResp.clone();
          cache.put('latest', clone);
        }
        return networkResp;
      } catch (e) {
        const cached = await cache.match('latest');
        if (cached) return cached;
        // Fallback minimal JSON
        return new Response(JSON.stringify({ data: [], meta: { offline: true } }), { headers: { 'Content-Type': 'application/json' }, status: 200 });
      }
    })());
  }
});
