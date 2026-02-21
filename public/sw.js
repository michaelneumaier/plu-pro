const STATIC_CACHE_NAME = 'plupro-static-v8';
const DYNAMIC_CACHE_NAME = 'plupro-dynamic-v8';
const PAGE_CACHE_NAME = 'plupro-pages-v1';

// Files to cache immediately
const STATIC_FILES = [
    '/',
    '/pwa',
    '/offline.html',
    '/manifest.json',
    '/icon-192.png?v=2025.08.07.16.42',
    '/icon-512.png?v=2025.08.07.16.42'
];

// PWA mode flag — set by client via postMessage
let isPWAMode = false;

// Paths eligible for stale-while-revalidate in PWA mode
function isOfflineCachablePage(pathname) {
    return pathname === '/dashboard' ||
        pathname === '/lists' ||
        pathname === '/pwa' ||
        /^\/lists\/\d+$/.test(pathname);
}

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
    const validCaches = [STATIC_CACHE_NAME, DYNAMIC_CACHE_NAME, PAGE_CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => !validCaches.includes(cacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        }).then(() => self.clients.claim())
    );
});

// Message handler for PWA mode flag and eager caching
self.addEventListener('message', event => {
    if (!event.data) return;

    if (event.data.type === 'SET_PWA_MODE') {
        isPWAMode = true;
    }

    if (event.data.type === 'CACHE_PAGES' && Array.isArray(event.data.urls)) {
        event.waitUntil(eagerCachePages(event.data.urls));
    }
});

// Eagerly cache pages in the background (for PWA pre-caching)
async function eagerCachePages(urls) {
    const cache = await caches.open(PAGE_CACHE_NAME);
    for (const url of urls) {
        try {
            const existing = await cache.match(url);
            if (!existing) {
                const response = await fetch(url, { credentials: 'include' });
                if (response.ok) {
                    await cache.put(url, response);
                }
            }
        } catch (e) {
            // Silently skip — not critical
        }
    }
}

// Stale-while-revalidate: serve cache immediately, update in background
async function staleWhileRevalidate(request) {
    const cache = await caches.open(PAGE_CACHE_NAME);
    const cachedResponse = await cache.match(request);

    // Fire off network fetch in the background to update cache
    const fetchPromise = fetch(request)
        .then(response => {
            if (response.ok && request.method === 'GET') {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch(() => null);

    if (cachedResponse) {
        // Serve from cache immediately — background fetch updates for next time
        fetchPromise; // fire-and-forget
        return cachedResponse;
    }

    // Also check the dynamic cache (pages cached before this upgrade)
    const dynamicCached = await caches.match(request);
    if (dynamicCached) {
        fetchPromise;
        return dynamicCached;
    }

    // No cache at all — wait for network
    const networkResponse = await fetchPromise;
    if (networkResponse) return networkResponse;

    // Everything failed — fall back to offline page
    return caches.match('/offline.html');
}

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
                    const responseToCache = response.clone();
                    if (response.status === 200 && request.method === 'GET') {
                        caches.open(DYNAMIC_CACHE_NAME)
                            .then(cache => cache.put(request, responseToCache));
                    }
                    return response;
                })
                .catch(() => {
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

    // Navigation requests
    if (request.mode === 'navigate') {
        // PWA mode: stale-while-revalidate for cacheable pages
        if (isPWAMode && isOfflineCachablePage(url.pathname)) {
            event.respondWith(staleWhileRevalidate(request));
            return;
        }

        // Default: network first with cache fallback
        event.respondWith(
            fetch(request)
                .then(response => {
                    const responseToCache = response.clone();
                    if (request.method === 'GET') {
                        caches.open(PAGE_CACHE_NAME)
                            .then(cache => cache.put(request, responseToCache));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request)
                        .then(response => {
                            if (response) {
                                return response;
                            }
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
                if (response.status === 200 && request.method === 'GET') {
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
    // Notify all clients to flush their dirty inventory
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
        client.postMessage({ type: 'SYNC_INVENTORY' });
    });
}
