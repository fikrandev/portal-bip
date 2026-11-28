/**
 * Portal Guru BIP - Service Worker (WebAPK & PWA Installer Engine)
 * Enables PWA offline capabilities, pre-caching, WebAPK installation compliance, and background sync.
 */

const CACHE_NAME = 'portal-bip-pwa-v14';
const STATIC_ASSETS = [
  './mobile/manifest.json',
  './manifest.json',
  './public/manifest.json',
  './public/images/pwa/icon-192.png',
  './public/images/pwa/icon-512.png',
  './public/images/pwa/icon-maskable-192.png',
  './public/images/pwa/icon-maskable-512.png',
  './public/images/pwa/apple-touch-icon.png',
  './public/css/mobile/app-animations.css',
  './public/js/mobile/android-ui.js',
  './public/js/mobile/mobile-api.js',
  './public/js/mobile/lazy-load.js',
  './public/js/pwa.js',
  './public/js/mobile/mobile-app.js'
];

// Install Event - Pre-cache critical core shell
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Pre-caching static assets for WebAPK PWA Installer');
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('[SW] Some assets failed to pre-cache (non-fatal):', err);
      });
    })
  );
});

// Activate Event - Clean up obsolete caches (strictly scoped to Portal BIP only)
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name.startsWith('portal-bip-pwa-') && name !== CACHE_NAME)
          .map((name) => {
            console.log('[SW Portal BIP] Clearing outdated portal cache:', name);
            return caches.delete(name);
          })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event - Stale-While-Revalidate for UI / Network-First for dynamic views
self.addEventListener('fetch', (event) => {
  const request = event.request;

  // Skip non-GET requests and cross-origin schemes (e.g. chrome-extension:)
  if (request.method !== 'GET' || !request.url.startsWith('http')) {
    return;
  }

  // Never intercept requests for Sarpras Mobile, Sarpras icons, SW, or live API endpoints
  if (request.url.includes('/mobile-sarpras') || request.url.includes('sarpras-') || request.url.includes('sw-sarpras') || request.url.includes('/api/')) {
    return;
  }

  const isStatic = request.destination === 'image' ||
    request.destination === 'style' ||
    request.destination === 'script' ||
    request.destination === 'font';

  if (isStatic) {
    // Cache First with Network Fallback & update
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          // Fetch update in background (Stale-While-Revalidate)
          fetch(request).then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
            }
          }).catch(() => { });
          return cachedResponse;
        }

        return fetch(request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return networkResponse;
        }).catch(() => {
          // Fallback image for offline missing icons
          if (request.destination === 'image') {
            return caches.match('./public/images/pwa/icon-192.png');
          }
        });
      })
    );
  }
  // Dynamic HTML routes bypass Cache Storage to avoid stale auth/CSRF/data & write overhead
});

// Message Event - Handle client requests for cache status or updates
self.addEventListener('message', (event) => {
  if (!event.data) return;

  if (event.data.action === 'SKIP_WAITING') {
    self.skipWaiting();
  }

  if (event.data.action === 'CLEAR_CACHE') {
    caches.delete(CACHE_NAME).then(() => {
      if (event.ports && event.ports[0]) {
        event.ports[0].postMessage({ success: true, message: 'Cache dibersihkan' });
      }
    });
  }
});

// Push Event - Real-time notification dengan suara default perangkat (silent: false)
self.addEventListener('push', (event) => {
  const defaultIcon = new URL('/public/images/pwa/icon-192.png', self.location.origin).href;
  const defaultBadge = new URL('/public/images/pwa/badge-96.png', self.location.origin).href;
  let payload = {
    title: 'Portal BIP',
    body: 'Anda memiliki pemberitahuan baru.',
    url: '/mobile/jurnal',
    tag: 'portal-bip-push',
    pakai_suara: true,
    icon: defaultIcon,
    badge: defaultBadge
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
    badge: payload.badge || defaultBadge,
    tag: payload.tag || 'portal-bip-push',
    renotify: true,
    silent: payload.pakai_suara === false,
    vibrate: [200, 100, 200],
    requireInteraction: false,
    data: {
      url: payload.url || '/mobile/jurnal'
    }
  };

  event.waitUntil(
    self.registration.showNotification(payload.title || 'Portal BIP', options)
  );
});

// Notification Click Event - Fokuskan atau buka halaman tujuan (misal /mobile/jurnal)
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const rawUrl = (event.notification && event.notification.data && event.notification.data.url)
    ? event.notification.data.url
    : '/mobile/jurnal';

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

