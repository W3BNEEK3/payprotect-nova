const CACHE_NAME = 'novatrust-cache-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    OFFLINE_URL,
    '/assets/css/design-tokens.css',
    '/assets/css/buttons.css',
    '/assets/css/forms.css',
    '/assets/css/toast.css',
    '/assets/css/public.css',
    '/assets/css/shell.css',
    '/assets/js/toast.js',
    '/assets/js/modules/validation.js',
    '/assets/js/modules/field-handlers.js',
    '/assets/js/modules/pwa.js',
    '/assets/images/icon-192.png',
    '/assets/images/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // 1. Cache-First Strategy for Static Assets (CSS, JS, Images)
    if (url.pathname.startsWith('/assets/')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) return cachedResponse;
                return fetch(request).then((networkResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, networkResponse.clone());
                        return networkResponse;
                    });
                });
            })
        );
        return;
    }

    // 2. Network-First Strategy for HTML (Navigations) and API requests
    // We never cache API responses or financial data intentionally.
    event.respondWith(
        fetch(request).catch(() => {
            // If network fails and it was a navigation request, show the offline fallback page
            if (request.mode === 'navigate' || (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
                return caches.match(OFFLINE_URL);
            }
            // For failed API requests, we just let it fail so the UI handles the network error properly
            throw new Error('Network unavailable');
        })
    );
});
