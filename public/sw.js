self.addEventListener('push', (event) => {
  const data = event.data?.json() ?? {};
  const options = {
    body: data.body ?? 'You have a notification',
    icon: data.icon ?? '/favicon.ico',
    badge: '/images/badge.png',
    data: { url: data.url ?? '/' },
    actions: [{ action: 'view', title: 'View' }, { action: 'dismiss', title: 'Dismiss' }],
    requireInteraction: false,
    silent: false,
  };

  event.waitUntil(self.registration.showNotification(data.title ?? 'Notification', options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  if (event.action === 'dismiss') return;
  const url = event.notification.data?.url ?? '/';
  event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
    for (const client of windowClients) {
      if (client.url === url && 'focus' in client) return client.focus();
    }
    if (clients.openWindow) return clients.openWindow(url);
    return null;
  }));
});

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open('v1').then((cache) => cache.addAll(['/', '/css/app.css', '/js/app.js', '/offline.html'])));
});

self.addEventListener('fetch', (event) => {
  event.respondWith(fetch(event.request).catch(() => caches.match(event.request).then((cached) => cached || caches.match('/offline.html'))));
});
