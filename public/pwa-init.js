(function () {
  const INSTALL_BUTTON_ID = "pwa-install-btn";
  const IOS_HINT_ID = "pwa-ios-hint";
  const SPLASH_ID = "pwa-splash-screen";
  const STATUS_ID = "pwa-network-status";
  const HINT_DISMISS_KEY = "pwa-ios-hint-dismissed";
  let deferredPrompt = null;
  let statusTimer = null;
  window.__pwaPromptInstall = null;

  function supportsPwaContext() {
    const standaloneMedia = window.matchMedia && window.matchMedia("(display-mode: standalone)").matches;
    const iosStandalone = window.navigator.standalone === true;
    return standaloneMedia || iosStandalone;
  }

  function setRootState() {
    document.documentElement.classList.toggle("pwa-standalone", supportsPwaContext());
  }

  function createSplashScreen() {
    if (document.getElementById(SPLASH_ID)) return;
    const splash = document.createElement("div");
    splash.id = SPLASH_ID;
    splash.style.position = "fixed";
    splash.style.inset = "0";
    splash.style.zIndex = "1000002";
    splash.style.display = "grid";
    splash.style.placeItems = "center";
    splash.style.background = "linear-gradient(160deg,#eff6ff,#dbeafe 55%,#eef2ff)";
    splash.style.color = "#0f172a";
    splash.style.transition = "opacity .35s ease, transform .35s ease";
    splash.innerHTML = `
      <div style="width:min(360px,calc(100vw - 32px));padding:28px 22px;border-radius:28px;background:rgba(255,255,255,.82);backdrop-filter:blur(20px);box-shadow:0 18px 48px rgba(37,99,235,.18);border:1px solid rgba(37,99,235,.12);text-align:center">
        <img src="/logo192.png" alt="Logo" style="width:92px;height:92px;object-fit:contain;margin:0 auto 14px;display:block;border-radius:22px;background:#fff;padding:10px;box-shadow:0 8px 22px rgba(15,23,42,.08)">
        <div style="font-weight:900;font-size:20px;line-height:1.2">Egitim Portali</div>
        <div style="margin-top:6px;font-size:14px;color:#475569">Yukleniyor...</div>
        <div style="margin-top:18px;height:8px;background:#dbeafe;border-radius:999px;overflow:hidden">
          <div style="width:48%;height:100%;border-radius:999px;background:linear-gradient(90deg,#2563eb,#0ea5e9);animation:pwaPulse 1.3s ease-in-out infinite"></div>
        </div>
      </div>
      <style>
        @keyframes pwaPulse { 0%,100%{transform:translateX(-48%)} 50%{transform:translateX(105%)} }
      </style>
    `;
    document.body.appendChild(splash);
    requestAnimationFrame(() => splash.style.opacity = "1");
    window.addEventListener("load", () => {
      setTimeout(() => {
        splash.style.opacity = "0";
        splash.style.transform = "scale(1.01)";
        setTimeout(() => splash.remove(), 360);
      }, 250);
    }, { once: true });
  }

  function createNetworkStatus() {
    if (document.getElementById(STATUS_ID)) return;
    const box = document.createElement("div");
    box.id = STATUS_ID;
    box.setAttribute("role", "status");
    box.style.position = "fixed";
    box.style.left = "50%";
    box.style.bottom = "14px";
    box.style.transform = "translateX(-50%) translateY(14px)";
    box.style.opacity = "0";
    box.style.zIndex = "1000001";
    box.style.padding = "10px 14px";
    box.style.borderRadius = "999px";
    box.style.background = "#0f172a";
    box.style.color = "#fff";
    box.style.fontSize = "13px";
    box.style.lineHeight = "1.2";
    box.style.boxShadow = "0 12px 30px rgba(2,6,23,.32)";
    box.style.transition = "opacity .25s ease, transform .25s ease";
    box.style.pointerEvents = "none";
    box.textContent = navigator.onLine ? "Baglanti geri geldi. Veriler guncelleniyor..." : "Cevrimdisi calisiyor. Son cache yuklendi.";
    document.body.appendChild(box);
    return box;
  }

  function showNetworkStatus(message, highlight = false) {
    const box = document.getElementById(STATUS_ID) || createNetworkStatus();
    if (!box) return;
    box.textContent = message;
    box.style.background = highlight ? "linear-gradient(135deg,#16a34a,#22c55e)" : "#0f172a";
    box.style.opacity = "1";
    box.style.transform = "translateX(-50%) translateY(0)";
    clearTimeout(statusTimer);
    statusTimer = setTimeout(() => {
      box.style.opacity = "0";
      box.style.transform = "translateX(-50%) translateY(14px)";
    }, 2400);
  }

  function notifyServiceWorker(message) {
    if (!navigator.serviceWorker?.controller) return;
    navigator.serviceWorker.controller.postMessage(message);
  }

  function createInstallButton() {
    let button = document.getElementById(INSTALL_BUTTON_ID);
    if (button) return button;

    button = document.createElement("button");
    button.id = INSTALL_BUTTON_ID;
    button.type = "button";
    button.textContent = "Uygulamayı Ekle";
    button.setAttribute("aria-label", "Uygulamayı cihaza ekle");
    button.style.position = "fixed";
    button.style.right = "16px";
    button.style.bottom = "16px";
    button.style.zIndex = "9999";
    button.style.display = "none";
    button.style.minHeight = "44px";
    button.style.minWidth = "44px";
    button.style.padding = "10px 14px";
    button.style.border = "0";
    button.style.borderRadius = "999px";
    button.style.fontWeight = "700";
    button.style.cursor = "pointer";
    button.style.background = "linear-gradient(135deg,#2563eb,#0ea5e9)";
    button.style.color = "#fff";
    button.style.boxShadow = "0 10px 24px rgba(37,99,235,.35)";
    document.body.appendChild(button);

    button.addEventListener("click", async () => {
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      try {
        await deferredPrompt.userChoice;
      } finally {
        deferredPrompt = null;
        button.style.display = "none";
      }
    });

    return button;
  }

  function registerServiceWorker() {
    if (!("serviceWorker" in navigator)) return;
    const manifestHref = document.querySelector('link[rel="manifest"]')?.getAttribute("href") || "";
    const swUrl = manifestHref
      ? new URL("service-worker.js", new URL(manifestHref, window.location.href)).toString()
      : new URL("service-worker.js", window.location.href).toString();

    window.addEventListener("load", () => {
      navigator.serviceWorker.register(swUrl).catch((error) => {
        console.error("[PWA] Service worker kayıt hatası:", error);
      });
    });
  }

  function setupInstallPrompt() {
    const button = createInstallButton();
    window.addEventListener("beforeinstallprompt", (event) => {
      event.preventDefault();
      deferredPrompt = event;
      window.__pwaPromptInstall = async function () {
        if (!deferredPrompt) return false;
        deferredPrompt.prompt();
        let accepted = false;
        try {
          const choice = await deferredPrompt.userChoice;
          accepted = !!choice && choice.outcome === "accepted";
        } finally {
          deferredPrompt = null;
          window.__pwaPromptInstall = null;
          button.style.display = "none";
        }
        return accepted;
      };
      button.style.display = "inline-flex";
      button.style.alignItems = "center";
      button.style.gap = "8px";
      window.dispatchEvent(new CustomEvent("pwa-install-available"));
    });

    window.addEventListener("appinstalled", () => {
      deferredPrompt = null;
      window.__pwaPromptInstall = null;
      button.style.display = "none";
      window.dispatchEvent(new CustomEvent("pwa-installed"));
    });
  }

  function isIos() {
    return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
  }

  function showIosInstallHint() {
    if (!isIos() || supportsPwaContext()) return;
    if (localStorage.getItem(HINT_DISMISS_KEY) === "1") return;
    if (document.getElementById(IOS_HINT_ID)) return;

    const hint = document.createElement("div");
    hint.id = IOS_HINT_ID;
    hint.style.position = "fixed";
    hint.style.left = "16px";
    hint.style.right = "16px";
    hint.style.bottom = "16px";
    hint.style.zIndex = "9998";
    hint.style.padding = "12px 14px";
    hint.style.borderRadius = "12px";
    hint.style.background = "#0f172a";
    hint.style.color = "#fff";
    hint.style.fontSize = "14px";
    hint.style.lineHeight = "1.35";
    hint.style.boxShadow = "0 12px 30px rgba(2,6,23,.35)";
    hint.innerHTML = 'iPhone/iPad icin: Safari menusu > Paylas > "Ana Ekrana Ekle". <button id="pwa-ios-hint-close" style="margin-left:8px;border:0;background:#1d4ed8;color:#fff;border-radius:8px;padding:6px 8px;cursor:pointer;min-width:44px;min-height:44px;">Tamam</button>';

    document.body.appendChild(hint);
    const closeBtn = document.getElementById("pwa-ios-hint-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        localStorage.setItem(HINT_DISMISS_KEY, "1");
        hint.remove();
      });
    }
  }

  function lockZoomGestures() {
    let lastTouchEnd = 0;

    document.addEventListener("touchstart", function (event) {
      if (event.touches && event.touches.length > 1) event.preventDefault();
    }, { passive: false });

    document.addEventListener("touchmove", function (event) {
      if (event.touches && event.touches.length > 1) event.preventDefault();
    }, { passive: false });

    document.addEventListener("touchend", function (event) {
      const now = Date.now();
      if (now - lastTouchEnd <= 300) event.preventDefault();
      lastTouchEnd = now;
    }, { passive: false });

    document.addEventListener("wheel", function (event) {
      if (event.ctrlKey) event.preventDefault();
    }, { passive: false });

    document.addEventListener("dblclick", function (event) {
      event.preventDefault();
    }, { passive: false });
  }

  function setupNetworkSignals() {
    window.addEventListener("offline", () => {
      document.documentElement.classList.add("is-offline");
      showNetworkStatus("Cevrimdisi calisiyor. Son cache yuklendi.");
    });

    window.addEventListener("online", () => {
      document.documentElement.classList.remove("is-offline");
      showNetworkStatus("Baglanti geri geldi. Veriler guncelleniyor...", true);
      notifyServiceWorker({ type: "REFRESH_CACHE" });
    });

    if (!navigator.onLine) {
      document.documentElement.classList.add("is-offline");
      showNetworkStatus("Cevrimdisi calisiyor. Son cache yuklendi.");
    }
  }

  registerServiceWorker();
  createSplashScreen();
  setRootState();
  lockZoomGestures();
  setupNetworkSignals();

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      setRootState();
      setupInstallPrompt();
      showIosInstallHint();
    });
  } else {
    setRootState();
    setupInstallPrompt();
    showIosInstallHint();
  }
})();
