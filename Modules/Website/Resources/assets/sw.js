const CACHE_NAME = 'lighthouse-academy-v1';
const urlsToCache = [
  '/',
  '/css/preset.css',
  '/css/theme.css',
  '/css/responsive.css',
  '/css/animate.css',
  '/js/jquery.js',
  '/js/bootstrap.min.js',
  '/js/theme.js',
  '/images/logo2.png',
  '/images/favicon.png',
  '/manifest.json'
];

// Install event
self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch event
self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        // Return cached response if found
        if (response) {
          return response;
        }

        return fetch(event.request).then(
          function(response) {
            // Check if we received a valid response
            if(!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response
            var responseToCache = response.clone();

            caches.open(CACHE_NAME)
              .then(function(cache) {
                cache.put(event.request, responseToCache);
              });

            return response;
          }
        );
      })
  );
});

// Activate event
self.addEventListener('activate', function(event) {
  var cacheWhitelist = [CACHE_NAME];

  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Background sync for form submissions
self.addEventListener('sync', function(event) {
  if (event.tag === 'contact-form-sync') {
    event.waitUntil(
      syncContactForm()
    );
  }
});

function syncContactForm() {
  return new Promise(function(resolve, reject) {
    // Get stored form data from IndexedDB
    // This would be implemented based on your specific needs
    resolve();
  });
}

// Push notification handling
self.addEventListener('push', function(event) {
  const options = {
    body: event.data ? event.data.text() : 'New update from Lighthouse Academy',
    icon: '/images/favicon.png',
    badge: '/images/favicon.png',
    tag: 'lighthouse-notification',
    requireInteraction: true,
    actions: [
      {
        action: 'view',
        title: 'View',
        icon: '/images/favicon.png'
      },
      {
        action: 'dismiss',
        title: 'Dismiss',
        icon: '/images/favicon.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('Lighthouse Academy', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', function(event) {
  event.notification.close();

  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});