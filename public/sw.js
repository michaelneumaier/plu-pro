const CACHE_NAME = 'plupro-v7';
const STATIC_CACHE_NAME = 'plupro-static-v7';
const DYNAMIC_CACHE_NAME = 'plupro-dynamic-v7';

// Files to cache immediately
const STATIC_FILES = [
    '/',
    '/pwa',
    '/offline.html',
    '/manifest.json',
    '/icon-192.png?v=2025.08.07.16.42',
    '/icon-512.png?v=2025.08.07.16.42'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then(cache => {
                console.log('Caching static assets');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => {
                        return cacheName !== STATIC_CACHE_NAME && 
                               cacheName !== DYNAMIC_CACHE_NAME;
                    })
                    .map(cacheName => caches.delete(cacheName))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip chrome-extension and non-http(s) requests
    if (url.protocol !== 'http:' && url.protocol !== 'https:') {
        return;
    }
    
    // Network first for API requests
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/livewire/')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Clone the response
                    const responseToCache = response.clone();
                    
                    // Cache successful API responses
                    if (response.status === 200) {
                        caches.open(DYNAMIC_CACHE_NAME)
                            .then(cache => cache.put(request, responseToCache));
                    }
                    
                    return response;
                })
                .catch(() => {
                    // Try to serve from cache if offline
                    return caches.match(request);
                })
        );
        return;
    }
    
    // Cache first for static assets
    if (request.destination === 'image' || 
        request.destination === 'font' ||
        url.pathname.includes('.css') ||
        url.pathname.includes('.js')) {
        
        event.respondWith(
            caches.match(request)
                .then(response => {
                    if (response) {
                        return response;
                    }
                    
                    return fetch(request).then(response => {
                        if (response.status === 200) {
                            const responseToCache = response.clone();
                            caches.open(STATIC_CACHE_NAME)
                                .then(cache => cache.put(request, responseToCache));
                        }
                        return response;
                    });
                })
        );
        return;
    }
    
    // Network first with fallback for navigation requests
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Cache the page
                    const responseToCache = response.clone();
                    caches.open(DYNAMIC_CACHE_NAME)
                        .then(cache => cache.put(request, responseToCache));
                    
                    return response;
                })
                .catch(() => {
                    // Try cache
                    return caches.match(request)
                        .then(response => {
                            if (response) {
                                return response;
                            }
                            // Fall back to offline page
                            return caches.match('/offline.html');
                        });
                })
        );
        return;
    }
    
    // Default: Network first with cache fallback
    event.respondWith(
        fetch(request)
            .then(response => {
                if (response.status === 200) {
                    const responseToCache = response.clone();
                    caches.open(DYNAMIC_CACHE_NAME)
                        .then(cache => cache.put(request, responseToCache));
                }
                return response;
            })
            .catch(() => caches.match(request))
    );
});

// Background sync for inventory updates
self.addEventListener('sync', event => {
    if (event.tag === 'sync-inventory') {
        event.waitUntil(syncInventoryUpdates());
    }
});

async function syncInventoryUpdates() {
    // This would sync pending inventory updates
    // Implementation depends on your specific needs
    console.log('Syncing inventory updates...');
}