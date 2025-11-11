/* Pusher Beams Service Worker - handles incoming web push notifications */
/* This worker does not control your app pages; it's only used by Beams. */
importScripts('https://js.pusher.com/beams/service-worker.js');

// Optional: Customize notification click behavior
self.addEventListener('notificationclick', function (event) {
  const deepLink = event?.notification?.data?.deep_link;
  event.notification.close();
  if (deepLink) {
    event.waitUntil(clients.openWindow(deepLink));
  }
});
