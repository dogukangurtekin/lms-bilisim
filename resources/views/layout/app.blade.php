<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Okul yonetim sistemi admin paneli">
    <title>@yield('title', 'School Management')</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @if(auth()->user()?->hasRole('student'))
    <link rel="stylesheet" href="{{ asset('css/student.css') }}">
    @endif
</head>
<body class="{{ auth()->user()?->role?->slug ? 'role-'.auth()->user()->role->slug : 'role-guest' }} @yield('body_class')">
@if(auth()->user()?->hasRole('student'))
<style>
.live-quiz-overlay {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: none;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 30% 20%, #8b5cf6 0%, #6d28d9 40%, #4c1d95 100%);
    padding: 20px;
}
.live-quiz-overlay.show {
    display: flex;
}
.live-quiz-overlay-card {
    width: min(640px, 100%);
    background: #ffffff;
    border-radius: 20px;
    border: 2px solid #5b21b6;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.28);
    padding: 24px;
    text-align: center;
}
.live-quiz-overlay-title {
    margin: 0 0 8px;
    color: #312e81;
    font-size: 28px;
    font-weight: 900;
}
.live-quiz-overlay-text {
    margin: 0 0 16px;
    color: #334155;
    font-size: 16px;
}
.live-quiz-overlay-quiz {
    margin: 0 0 18px;
    font-size: 20px;
    font-weight: 800;
    color: #111827;
}
.live-quiz-overlay-actions .btn {
    min-width: 260px;
    font-size: 17px;
    font-weight: 800;
}
</style>
@endif
<div class="layout">
    @include('partials.sidebar')
    <div id="mobile-sidebar-backdrop" class="mobile-sidebar-backdrop"></div>
    <main class="main">
        @include('partials.navbar')
        @yield('content')
        @include('partials.footer')
    </main>
</div>
@include('partials.toast')
@if(auth()->user()?->hasRole('student'))
<div id="liveQuizOverlay" class="live-quiz-overlay" role="dialog" aria-modal="true" aria-label="Canli quiz bildirimi">
    <div class="live-quiz-overlay-card">
        <h2 class="live-quiz-overlay-title">Canli Quiz Basladi</h2>
        <p class="live-quiz-overlay-text">Ogretmenin canli quiz baslatti. Devam etmek icin quize katilman gerekli.</p>
        <p id="liveQuizOverlayTitle" class="live-quiz-overlay-quiz">Canli Quiz</p>
        <div class="live-quiz-overlay-actions">
            <a id="liveQuizOverlayJoinBtn" href="#" class="btn">Canli Quize Katil</a>
        </div>
    </div>
</div>
@endif
<script src="{{ asset('js/admin.js') }}"></script>
@if(auth()->user()?->hasRole('student'))
<script>
(() => {
    const overlay = document.getElementById('liveQuizOverlay');
    const joinBtn = document.getElementById('liveQuizOverlayJoinBtn');
    const titleEl = document.getElementById('liveQuizOverlayTitle');
    if (!overlay || !joinBtn || !titleEl) return;

    let lastSessionId = null;
    let busy = false;
    let pollingStopped = false;
    let pollTimer = null;

    const stopQuizPolling = () => {
        pollingStopped = true;
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    };

    async function checkActiveQuiz() {
        if (pollingStopped) return;
        if (busy) return;
        busy = true;
        try {
            const response = await fetch('{{ route('student.live-quiz.active') }}', {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.status === 401 || response.status === 419) {
                stopQuizPolling();
                overlay.classList.remove('show');
                return;
            }
            if (!response.ok) {
                overlay.classList.remove('show');
                return;
            }
            const data = await response.json();
            const shouldShow = !!(data && data.active && !data.joined && data.join_url);

            if (shouldShow) {
                const title = data.quiz_title || 'Canli Quiz';
                titleEl.textContent = title;
                joinBtn.href = data.join_url;
                overlay.classList.add('show');
                if (data.session_id && data.session_id !== lastSessionId) {
                    lastSessionId = data.session_id;
                }
            } else {
                overlay.classList.remove('show');
                lastSessionId = null;
            }
        } catch (e) {
            overlay.classList.remove('show');
        } finally {
            busy = false;
        }
    }

    checkActiveQuiz();
    pollTimer = setInterval(checkActiveQuiz, 4000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && !pollingStopped) checkActiveQuiz();
    });
})();
</script>
@endif
@if(auth()->check())
<script>
(() => {
    window.WEBPUSH_ENABLED = false;
    const publicKeyUrl = @json(route('notifications.public-key'));
    const subscribeUrl = @json(route('notifications.subscribe'));
    const unsubscribeUrl = @json(route('notifications.unsubscribe'));
    const deviceStatusUrl = @json(route('notifications.device-status'));
    const serviceWorkerUrl = @json(asset('service-worker.js'));
    const notificationsBaseUrl = @json(url('/app-notifications'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const pwaOnboardDismissKey = 'pwa_onboard_dismissed_v1';
    let onboardEl = null;
    let pushBusy = false;
    let pushConfigMissing = false;
    let pushAuthFailed = false;

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    async function sendSubscriptionToServer(sub) {
        await fetch(subscribeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(sub),
        });
    }

    async function removeSubscriptionFromServer(endpoint) {
        await fetch(unsubscribeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ endpoint }),
        });
    }

    async function syncDeviceStatus(endpoint = '') {
        const platform = navigator.platform || navigator.userAgent || '';
        const isPwa = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
        try {
            await fetch(deviceStatusUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    endpoint,
                    permission: (window.Notification && Notification.permission) ? Notification.permission : 'default',
                    platform,
                    is_pwa: !!isPwa,
                }),
            });
        } catch (_) {}
    }

    async function registerWebPush() {
        if (!window.WEBPUSH_ENABLED) return;
        if (pushConfigMissing) return;
        if (pushAuthFailed) return;
        if (pushBusy) return;
        pushBusy = true;
        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
            console.warn('[WebPush] Tarayici push API desteklemiyor.');
            pushBusy = false;
            return;
        }

        if (!window.isSecureContext) {
            // Web Push sadece HTTPS veya localhost secure context'te calisir.
            console.warn('[WebPush] Guvenli baglam yok. HTTPS uzerinden acin.');
            pushBusy = false;
            return;
        }

        try {
            const keyRes = await fetch(publicKeyUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            if (keyRes.status === 401 || keyRes.status === 419) {
                pushAuthFailed = true;
                return;
            }
            if (!keyRes.ok) {
                throw new Error('Public key alinamadi: HTTP ' + keyRes.status);
            }
            const keyJson = await keyRes.json().catch(() => ({}));
            const vapidPublicKey = String(keyJson.public_key || '');
            if (!vapidPublicKey) {
                pushConfigMissing = true;
                throw new Error('WEBPUSH_VAPID_PUBLIC_KEY bos veya sunucuda tanimsiz.');
            }
            const reg = await navigator.serviceWorker.register(serviceWorkerUrl);
            let sub = await reg.pushManager.getSubscription();

            if (Notification.permission === 'denied') {
                if (sub?.endpoint) await removeSubscriptionFromServer(sub.endpoint);
                if (sub) await sub.unsubscribe();
                await syncDeviceStatus(sub?.endpoint || '');
                return;
            }

            if (Notification.permission === 'default') {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    await syncDeviceStatus(sub?.endpoint || '');
                    return;
                }
            }

            if (!sub && vapidPublicKey) {
                sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });
            }

            if (sub) {
                await sendSubscriptionToServer(sub.toJSON());
                await syncDeviceStatus(sub.endpoint || '');
            } else {
                await syncDeviceStatus('');
            }
        } catch (error) {
            console.error('[WebPush] Kayit/abonelik hatasi:', error);
            if (String(error?.message || '').includes('WEBPUSH_VAPID_PUBLIC_KEY')) {
                pushConfigMissing = true;
            }
        } finally {
            pushBusy = false;
        }
    }

    async function markReadFromQueryIfNeeded() {
        try {
            const url = new URL(window.location.href);
            const logId = String(url.searchParams.get('notif_log') || '').trim();
            const shouldMarkRead = String(url.searchParams.get('notif_mark_read') || '') === '1';
            if (!logId || !shouldMarkRead) return;

            await fetch(`${notificationsBaseUrl}/${encodeURIComponent(logId)}/read`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            url.searchParams.delete('notif_log');
            url.searchParams.delete('notif_mark_read');
            window.history.replaceState({}, '', url.toString());
        } catch (_) {}
    }

    function isPwaInstalled() {
        const standaloneMedia = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
        const iosStandalone = window.navigator.standalone === true;
        return !!(standaloneMedia || iosStandalone);
    }

    function ensureOnboardUi() {
        if (onboardEl) return onboardEl;
        const div = document.createElement('section');
        div.id = 'pwa-onboard-card';
        div.style.position = 'fixed';
        div.style.top = '14px';
        div.style.right = '14px';
        div.style.zIndex = '1000000';
        div.style.maxWidth = '360px';
        div.style.width = 'calc(100vw - 28px)';
        div.style.background = '#0f172a';
        div.style.color = '#fff';
        div.style.borderRadius = '14px';
        div.style.padding = '14px';
        div.style.boxShadow = '0 14px 36px rgba(2,6,23,.45)';
        div.innerHTML = `
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <strong style="font-size:15px;line-height:1.2;">Bildirim Kurulumu</strong>
                <button type="button" id="pwa-onboard-dismiss" style="border:0;background:#1e293b;color:#cbd5e1;border-radius:8px;padding:4px 8px;cursor:pointer;">Kapat</button>
            </div>
            <p id="pwa-onboard-text" style="margin:8px 0 12px;font-size:13px;line-height:1.4;color:#cbd5e1;"></p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" id="pwa-onboard-install" style="border:0;background:#16a34a;color:#fff;border-radius:10px;padding:8px 12px;font-weight:700;cursor:pointer;">Uygulamayi Yukle</button>
                <button type="button" id="pwa-onboard-notify" style="border:0;background:#2563eb;color:#fff;border-radius:10px;padding:8px 12px;font-weight:700;cursor:pointer;">Bildirim Izni Ver</button>
            </div>
        `;
        document.body.appendChild(div);
        onboardEl = div;

        const dismissBtn = div.querySelector('#pwa-onboard-dismiss');
        dismissBtn?.addEventListener('click', () => {
            localStorage.setItem(pwaOnboardDismissKey, '1');
            div.remove();
            onboardEl = null;
        });

        const installBtn = div.querySelector('#pwa-onboard-install');
        installBtn?.addEventListener('click', async () => {
            const fn = window.__pwaPromptInstall;
            if (typeof fn === 'function') {
                await fn();
            } else {
                alert('Tarayiciniz su an dogrudan yukleme penceresi vermiyor. Tarayici menusunden "Uygulamayi Yuke" secenegini kullanin.');
            }
            updateOnboardUi();
        });

        const notifyBtn = div.querySelector('#pwa-onboard-notify');
        notifyBtn?.addEventListener('click', async () => {
            if (!('Notification' in window)) {
                alert('Bu tarayici bildirim izinlerini desteklemiyor.');
                return;
            }
            if (Notification.permission === 'denied') {
                alert('Bildirim izni tarayici tarafinda engellenmis. Adres cubugundaki kilit simgesinden veya tarayici ayarlarindan bu site icin bildirimi tekrar "izin ver" yapin.');
                return;
            }
            notifyBtn.disabled = true;
            notifyBtn.style.opacity = '.7';
            await registerWebPush();
            notifyBtn.disabled = false;
            notifyBtn.style.opacity = '1';
            updateOnboardUi();
        });

        return div;
    }

    function updateOnboardUi() {
        if (localStorage.getItem(pwaOnboardDismissKey) === '1') return;
        if (!('Notification' in window)) return;
        const permission = Notification.permission;
        const installed = isPwaInstalled();

        if (installed && permission === 'granted') {
            if (onboardEl) {
                onboardEl.remove();
                onboardEl = null;
            }
            return;
        }

        const div = ensureOnboardUi();
        const textEl = div.querySelector('#pwa-onboard-text');
        const installBtn = div.querySelector('#pwa-onboard-install');
        const notifyBtn = div.querySelector('#pwa-onboard-notify');
        const hasDirectInstallPrompt = typeof window.__pwaPromptInstall === 'function';

        if (!installed && permission !== 'granted') {
            textEl.textContent = 'Gercek sistem bildirimi icin once uygulamayi yukleyin, sonra bildirim izni verin.';
        } else if (permission === 'denied') {
            textEl.textContent = 'Bildirim izni engellenmis. Tarayici ayarlarindan bu site icin izni tekrar acin.';
        } else if (!installed) {
            textEl.textContent = 'Bildirim acik. Daha stabil deneyim icin uygulamayi cihaza yukleyin.';
        } else {
            textEl.textContent = 'Uygulama yuklu. Simdi bildirim iznini acin.';
        }

        if (installBtn) {
            installBtn.style.display = installed ? 'none' : 'inline-block';
            installBtn.disabled = !hasDirectInstallPrompt;
            installBtn.style.opacity = hasDirectInstallPrompt ? '1' : '.7';
        }
        if (notifyBtn) {
            notifyBtn.style.display = permission === 'granted' ? 'none' : 'inline-block';
            notifyBtn.textContent = permission === 'denied' ? 'Izin Engellendi' : 'Bildirim Izni Ver';
        }
    }

    (async () => {
        await registerWebPush();
        await markReadFromQueryIfNeeded();
        updateOnboardUi();

        window.addEventListener('pwa-install-available', updateOnboardUi);
        window.addEventListener('pwa-installed', updateOnboardUi);
        window.addEventListener('focus', updateOnboardUi);

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                registerWebPush();
                updateOnboardUi();
            }
        });
    })();
})();
</script>
@endif
@stack('scripts')
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>
