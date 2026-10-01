/**
 * Mobile Sarpras PWA - Service Worker
 * Enables PWA offline capabilities, pre-caching, and background sync.
 */

const CACHE_NAME = 'sarpras-mobile-pwa-v1';
const OFFLINE_URL = './mobile-sarpras';

// Install Event - Pre-cache critical core shell
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW-Sarpras] Pre-caching offline fallback');
      return cache.add(OFFLINE_URL).catch(() => {
        console.warn('[SW-Sarpras] Failed to cache offline page (non-fatal)');
      });
    })
  );
});

// Activate Event - Clean up obsolete caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => {
            console.log('[SW-Sarpras] Clearing outdated cache:', name);
            return caches.delete(name);
          })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event - Network First with Cache Fallback
self.addEventListener('fetch', (event) => {
  const request = event.request;
  
  // Skip non-GET requests and cross-origin schemes
  if (request.method !== 'GET' || !request.url.startsWith('http')) {
    return;
  }

  const isStatic = request.destination === 'image' || 
                   request.destination === 'style' || 
                   request.destination === 'script' || 
                   request.destination === 'font';

  if (isStatic) {
    // Stale-While-Revalidate for static assets
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        const fetchPromise = fetch(request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const clone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
          }
          return networkResponse;
        }).catch(() => cachedResponse);

        return cachedResponse || fetchPromise;
      })
    );
  } else {
    // Network First for dynamic pages & API
    event.respondWith(
      fetch(request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return networkResponse;
        })
        .catch(() => {
          return caches.match(request).then((cachedResponse) => {
            return cachedResponse || caches.match(OFFLINE_URL);
          });
        })
    );
  }
});

// Message Event
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
