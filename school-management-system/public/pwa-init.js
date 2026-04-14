(function () {
  const INSTALL_BUTTON_ID = "pwa-install-btn";
  const IOS_HINT_ID = "pwa-ios-hint";
  const HINT_DISMISS_KEY = "pwa-ios-hint-dismissed";
  let deferredPrompt = null;

  function createInstallButton() {
    let button = document.getElementById(INSTALL_BUTTON_ID);
    if (button) return button;

    button = document.createElement("button");
    button.id = INSTALL_BUTTON_ID;
    button.type = "button";
    button.textContent = "Uygulamayi Ekle";
    button.setAttribute("aria-label", "Uygulamayi cihaza ekle");
    button.style.position = "fixed";
    button.style.right = "16px";
    button.style.bottom = "16px";
    button.style.zIndex = "9999";
    button.style.display = "none";
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
    window.addEventListener("load", () => {
      navigator.serviceWorker.register("/service-worker.js").catch(() => {});
    });
  }

  function setupInstallPrompt() {
    const button = createInstallButton();
    window.addEventListener("beforeinstallprompt", (event) => {
      deferredPrompt = event;
      button.style.display = "inline-flex";
      button.style.alignItems = "center";
      button.style.gap = "8px";
    });

    window.addEventListener("appinstalled", () => {
      deferredPrompt = null;
      button.style.display = "none";
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
    hint.innerHTML = 'iPhone/iPad icin: Safari menusu > Paylas > "Ana Ekrana Ekle". <button id="pwa-ios-hint-close" style="margin-left:8px;border:0;background:#1d4ed8;color:#fff;border-radius:8px;padding:6px 8px;cursor:pointer;">Tamam</button>';

    document.body.appendChild(hint);
    const closeBtn = document.getElementById("pwa-ios-hint-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        localStorage.setItem(HINT_DISMISS_KEY, "1");
        hint.remove();
      });
    }
  }

  function supportsPwaContext() {
    const standaloneMedia = window.matchMedia && window.matchMedia("(display-mode: standalone)").matches;
    const iosStandalone = window.navigator.standalone === true;
    return standaloneMedia || iosStandalone;
  }

  registerServiceWorker();

  if (!supportsPwaContext()) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", function () {
        setupInstallPrompt();
        showIosInstallHint();
      });
    } else {
      setupInstallPrompt();
      showIosInstallHint();
    }
  }
})();
