/**
 * Portal Guru BIP - Service Worker (WebAPK & PWA Installer Engine)
 * Enables PWA offline capabilities, pre-caching, WebAPK installation compliance, and background sync.
 */

const CACHE_NAME = 'portal-guru-pwa-v3';
const STATIC_ASSETS = [
  './mobile',
  './mobile/absen',
  './mobile/jurnal',
  './mobile/kelas',
  './mobile/murid',
  './mobile/profil',
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

// Activate Event - Clean up obsolete caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => {
            console.log('[SW] Clearing outdated cache:', name);
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
          }).catch(() => {});
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
  } else {
    // Network First with Cache Fallback for dynamic pages & API
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
            if (cachedResponse) {
              return cachedResponse;
            }
            return caches.match('./mobile');
          });
        })
    );
  }
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

