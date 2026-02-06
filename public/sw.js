// ZDream Service Worker - Cache First Strategy for Static Assets
const CACHE_NAME = 'zdream-v2';
const STATIC_CACHE = 'zdream-static-v2';

// Assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/manifest.json'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME && name !== STATIC_CACHE)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - Network first for HTML, Cache first for assets
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip external requests
    if (url.origin !== location.origin) return;

    // Skip Livewire/API requests
    if (url.pathname.includes('livewire') ||
        url.pathname.includes('/api/') ||
        url.pathname.includes('/broadcasting/')) {
        return;
    }

    // For navigation requests (HTML) - Network first
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Clone and cache successful responses
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // For static assets (CSS, JS, images) - Cache first
    if (request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font') {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(STATIC_CACHE).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }
});

// Handle push notifications (future use)
self.addEventListener('push', (event) => {
    const options = {
        body: event.data?.text() || 'Có thông báo mới từ ZDream',
        icon: '/images/icon-192.png',
        badge: '/images/icon-192.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        }
    };

    event.waitUntil(
        self.registration.showNotification('ZDream', options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/')
    );
});
