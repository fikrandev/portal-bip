const CACHE_NAME='sarpras-pwa-v6';
self.addEventListener('install',function(e){self.skipWaiting();});
self.addEventListener('activate',function(e){e.waitUntil(caches.keys().then(function(n){return Promise.all(n.filter(function(n){return n!==CACHE_NAME}).map(function(n){return caches.delete(n)}))}).then(function(){return self.clients.claim()}));});
self.addEventListener('fetch',function(e){if(e.request.method!=='GET'||!e.request.url.startsWith('http'))return;if(!e.request.url.includes('/mobile-sarpras'))return;e.respondWith(fetch(e.request).then(function(r){if(r&&r.status===200){var c=r.clone();caches.open(CACHE_NAME).then(function(cache){cache.put(e.request,c)})}return r}).catch(function(){return caches.match(e.request)}));});
