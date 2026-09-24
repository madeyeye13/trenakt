var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline/',
    '/images/Favicon01.png',
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear old caches, and take control of any tabs that are already open
// (self.clients.claim()) instead of waiting for their next navigation.
// Without this, which requests a given tab's fetches actually go through
// this worker depends on exactly when that tab loaded relative to the
// worker's own install/activate cycle - inconsistent across reloads.
self.addEventListener('activate', event => {
    event.waitUntil(
        Promise.all([
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(cacheName => (cacheName.startsWith("pwa-")))
                        .filter(cacheName => (cacheName !== staticCacheName))
                        .map(cacheName => caches.delete(cacheName))
                );
            }),
            self.clients.claim(),
        ])
    );
});

// Serve from cache only for same-origin GET requests. A POST (logging out,
// switching modes, any form submit) or a cross-origin request must always
// reach the network untouched - answering those from here, even by falling
// through to fetch(), risks interfering with Livewire's own requests
// (wire:navigate, component updates), which this app depends on for every
// page transition.
self.addEventListener("fetch", event => {
    if (event.request.method !== 'GET') {
        return;
    }

    if (new URL(event.request.url).origin !== self.location.origin) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('/offline/');
            })
    )
});