const SW_VERSION = "v1.3.0";
const RUNTIME_CACHE = `runtime-${SW_VERSION}`;
const SHELL_CACHE = `shell-${SW_VERSION}`;
const OFFLINE_URL = "offline.html";

const SHELL_ASSETS = [
  "./",
  OFFLINE_URL,
  "manifest.webmanifest",
  "manifest.json",
  "pwa-init.js",
  "logo192.png",
  "logo512.png",
  "logo.png",
  "script.js",
  "style.css"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(SHELL_CACHE).then((cache) => cache.addAll(SHELL_ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== SHELL_CACHE && key !== RUNTIME_CACHE)
          .map((key) => caches.delete(key))
      )
    ).then(async () => {
      if ("navigationPreload" in self.registration) {
        await self.registration.navigationPreload.enable();
      }
      await self.clients.claim();
    })
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET") return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  if (request.mode === "navigate") {
    event.respondWith(
      (async () => {
        try {
          const preload = await event.preloadResponse;
          if (preload) return preload;

          const response = await fetch(request);
          const copy = response.clone();
          caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy)).catch(() => {});
          return response;
        } catch (_error) {
          const cached = await caches.match(request);
          if (cached) return cached;
          return caches.match(OFFLINE_URL);
        }
      })()
    );
    return;
  }

  if (request.destination === "script" || request.destination === "style" || request.destination === "image" || request.destination === "font") {
    event.respondWith(
      caches.match(request).then((cached) => {
        const networkFetch = fetch(request)
          .then((response) => {
            if (response && response.ok) {
              const copy = response.clone();
              caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy)).catch(() => {});
            }
            return response;
          })
          .catch(async () => {
            return cached;
          });
        return cached || networkFetch;
      })
    );
  }
});

self.addEventListener("push", (event) => {
  if (!event.data) return;

  let payload = {};
  try {
    payload = event.data.json();
  } catch (_e) {
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
  if (data.log_id) {
    target.searchParams.set("notif_log", String(data.log_id));
  }
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
