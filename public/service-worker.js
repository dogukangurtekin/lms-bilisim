const SW_VERSION = "v1.4.0";
const CACHE_NAMES = {
  shell: `shell-${SW_VERSION}`,
  static: `static-${SW_VERSION}`,
  images: `images-${SW_VERSION}`,
  api: `api-${SW_VERSION}`,
};

const OFFLINE_URL = "/offline.html";
const PRECACHE = [
  "/",
  OFFLINE_URL,
  "/manifest.webmanifest",
  "/manifest.json",
  "/logo192.png",
  "/logo512.png",
  "/logo.png",
  "/favicon.ico",
  "/pwa-init.js",
  "/service-worker.js",
  "/public/css/admin.css",
  "/public/css/student.css",
];

const API_CACHEABLE_PATHS = [
  "/dashboard",
  "/courses",
  "/classes",
  "/odevler",
  "/bildirimler",
  "/ogrenci-verileri",
  "/profilim",
  "/student",
  "/teacher",
  "/app-notifications",
];

function isSameOrigin(requestUrl) {
  return requestUrl.origin === self.location.origin;
}

function isStaticRequest(request) {
  return ["style", "script", "font"].includes(request.destination);
}

function isImageRequest(request) {
  return request.destination === "image";
}

function isNavigationRequest(request) {
  return request.mode === "navigate";
}

function isCacheableApiRequest(url, request) {
  if (request.method !== "GET") return false;
  if (!isSameOrigin(url)) return false;
  return API_CACHEABLE_PATHS.some((path) => url.pathname.startsWith(path));
}

async function cachePut(cacheName, request, response) {
  if (!response || !response.ok) return;
  const cache = await caches.open(cacheName);
  await cache.put(request, response.clone());
}

async function networkFirst(request, cacheName, fallback = null) {
  const cache = await caches.open(cacheName);
  try {
    const response = await fetch(request);
    if (response && response.ok) {
      cache.put(request, response.clone()).catch(() => {});
      return response;
    }
    if (response) return response;
  } catch (_) {}

  const cached = await cache.match(request);
  if (cached) return cached;
  return fallback || offlineResponse();
}

async function cacheFirst(request, cacheName, fallback = null) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  if (cached) {
    fetch(request)
      .then((response) => response && response.ok ? cache.put(request, response.clone()) : null)
      .catch(() => {});
    return cached;
  }
  try {
    const response = await fetch(request);
    if (response && response.ok) {
      cache.put(request, response.clone()).catch(() => {});
      return response;
    }
    if (response) return response;
  } catch (_) {}
  return fallback || offlineResponse();
}

async function staleWhileRevalidate(request, cacheName, fallback = null) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  const update = fetch(request)
    .then((response) => response && response.ok ? cache.put(request, response.clone()) : null)
    .catch(() => {});
  if (cached) {
    update;
    return cached;
  }
  try {
    const response = await fetch(request);
    if (response && response.ok) {
      cache.put(request, response.clone()).catch(() => {});
      return response;
    }
    if (response) return response;
  } catch (_) {}
  return fallback || offlineResponse();
}

function offlineResponse() {
  return new Response("Offline", { status: 503, statusText: "Offline" });
}

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAMES.shell).then(async (cache) => {
      await cache.addAll(PRECACHE.map((asset) => new Request(asset, { cache: "reload" })));
      self.skipWaiting();
    }).catch(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => !Object.values(CACHE_NAMES).includes(key))
          .map((key) => caches.delete(key))
      )
    ).then(async () => {
      if ("navigationPreload" in self.registration) {
        try {
          await self.registration.navigationPreload.enable();
        } catch (_) {}
      }
      await self.clients.claim();
    })
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET") return;
  const url = new URL(request.url);
  if (!isSameOrigin(url)) return;

  if (isNavigationRequest(request)) {
    event.respondWith((async () => {
      try {
        const preload = await event.preloadResponse;
        if (preload instanceof Response) return preload;
        const response = await fetch(request);
        if (response instanceof Response) return response;
      } catch (_) {}
      const path = url.pathname.toLowerCase();
      if (path.includes("/login") || path.includes("/logout")) {
        return offlineResponse();
      }
      const cached = await caches.match(request);
      if (cached) return cached;
      const fallback = await caches.match(OFFLINE_URL);
      return fallback instanceof Response ? fallback : offlineResponse();
    })());
    return;
  }

  if (isStaticRequest(request)) {
    event.respondWith(cacheFirst(request, CACHE_NAMES.static));
    return;
  }

  if (isImageRequest(request)) {
    event.respondWith(staleWhileRevalidate(request, CACHE_NAMES.images));
    return;
  }

  if (isCacheableApiRequest(url, request)) {
    event.respondWith(networkFirst(request, CACHE_NAMES.api));
    return;
  }

  event.respondWith(networkFirst(request, CACHE_NAMES.shell));
});

self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }
});

self.addEventListener("push", (event) => {
  if (!event.data) return;

  let payload = {};
  try {
    payload = event.data.json();
  } catch (_) {
    payload = { body: event.data.text() };
  }

  const title = payload.title || "Yeni Bildirim";
  const options = {
    body: payload.body || "",
    icon: "/logo192.png",
    badge: "/logo192.png",
    data: {
      url: payload.url || "/bildirimler",
      log_id: payload.log_id || null,
      type: payload.type || "system_message",
    },
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const data = (event.notification && event.notification.data) || {};
  const rawTarget = data.url || "/bildirimler";
  const target = new URL(rawTarget, self.location.origin);
  if (data.log_id) target.searchParams.set("notif_log", String(data.log_id));
  if ((data.type || "system_message") === "system_message") {
    target.searchParams.set("notif_mark_read", "1");
  }
  const targetUrl = target.toString();

  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then((windowClients) => {
      for (const client of windowClients) {
        if ("focus" in client) {
          client.navigate(targetUrl);
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
