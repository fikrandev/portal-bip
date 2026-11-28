/**
 * Portal BIP Web Push Service Worker (Portal Web: Laptop, Tablet, iPad)
 * Zero fetch caching so dynamic PHP sessions/HTML never go stale.
 */
self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
  const defaultIcon = new URL('/public/images/pwa/icon-192.png', self.location.origin).href;
  let payload = {
    title: 'Portal BIP',
    body: 'Anda memiliki notifikasi baru.',
    url: '/dashboard',
    tag: 'pusat-notif-web',
    pakai_suara: true,
    icon: defaultIcon,
    badge: defaultIcon
  };

  if (event.data) {
    try {
      const parsed = event.data.json();
      if (parsed && typeof parsed === 'object') {
        payload = Object.assign(payload, parsed);
      }
    } catch (e) {
      const txt = event.data.text();
      if (txt) payload.body = txt;
    }
  }

  const options = {
    body: payload.body,
    icon: payload.icon || defaultIcon,
    badge: payload.badge || defaultIcon,
    tag: payload.tag || 'pusat-notif-web',
    renotify: true,
    silent: payload.pakai_suara === false,
    vibrate: [200, 100, 200],
    requireInteraction: false,
    data: {
      url: payload.url || '/dashboard'
    }
  };

  event.waitUntil(
    self.registration.showNotification(payload.title || 'Portal BIP', options)
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const rawUrl = (event.notification && event.notification.data && event.notification.data.url)
    ? event.notification.data.url
    : '/dashboard';
  const targetUrl = new URL(rawUrl, self.location.origin).href;

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url && client.url.startsWith(self.location.origin) && 'focus' in client) {
          return client.focus().then((focused) => {
            if (focused && 'navigate' in focused && focused.url !== targetUrl) {
              return focused.navigate(targetUrl).catch(() => focused);
            }
            return focused;
          });
        }
      }
      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
      return null;
    })
  );
});
