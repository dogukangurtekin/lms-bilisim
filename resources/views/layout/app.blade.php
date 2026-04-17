<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Okul yonetim sistemi admin paneli">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Egitim">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo192.png') }}">
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
        @if(session('ok'))<div class="card">{{ session('ok') }}</div>@endif
        @yield('content')
        @include('partials.footer')
    </main>
</div>
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

    async function checkActiveQuiz() {
        if (busy) return;
        busy = true;
        try {
            const response = await fetch('{{ route('student.live-quiz.active') }}', {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
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
    setInterval(checkActiveQuiz, 4000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') checkActiveQuiz();
    });
})();
</script>
@endif
@if(auth()->check())
<script>
(() => {
    const feedUrl = @json(route('notifications.feed'));
    const storageKey = `notif:last:id:${@json((int) auth()->id())}`;
    const canUseStorage = typeof window.localStorage !== 'undefined';
    let lastId = canUseStorage ? Number(localStorage.getItem(storageKey) || 0) : 0;
    let bootstrapped = false;
    let busy = false;

    function saveLastId() {
        if (!canUseStorage) return;
        localStorage.setItem(storageKey, String(lastId));
    }

    function ensureToastWrap() {
        let wrap = document.getElementById('system-notification-toast-wrap');
        if (wrap) return wrap;
        wrap = document.createElement('div');
        wrap.id = 'system-notification-toast-wrap';
        wrap.style.position = 'fixed';
        wrap.style.right = '16px';
        wrap.style.bottom = '16px';
        wrap.style.zIndex = '100000';
        wrap.style.display = 'grid';
        wrap.style.gap = '8px';
        document.body.appendChild(wrap);
        return wrap;
    }

    function showToast(title, content) {
        const wrap = ensureToastWrap();
        const item = document.createElement('article');
        item.style.background = '#0f172a';
        item.style.color = '#fff';
        item.style.padding = '12px 14px';
        item.style.borderRadius = '12px';
        item.style.width = 'min(360px, calc(100vw - 32px))';
        item.style.boxShadow = '0 16px 30px rgba(2,6,23,.35)';
        item.style.border = '1px solid rgba(148,163,184,.35)';
        item.innerHTML = `<strong style="display:block;font-size:14px;margin-bottom:4px;">${title}</strong><p style="margin:0;font-size:13px;line-height:1.4;">${content}</p>`;
        wrap.appendChild(item);
        setTimeout(() => item.remove(), 6500);
    }

    async function showDeviceNotification(title, content) {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'default') {
            try { await Notification.requestPermission(); } catch (_) {}
        }
        if (Notification.permission !== 'granted') return;

        try {
            if ('serviceWorker' in navigator) {
                const reg = await navigator.serviceWorker.ready;
                if (reg?.showNotification) {
                    await reg.showNotification(title, {
                        body: content,
                        icon: '{{ asset('logo192.png') }}',
                        badge: '{{ asset('logo192.png') }}',
                        tag: `app-notif-${Date.now()}`,
                    });
                    return;
                }
            }
        } catch (_) {}

        try { new Notification(title, { body: content }); } catch (_) {}
    }

    async function bootstrapLatestId() {
        try {
            const res = await fetch(`${feedUrl}?latest_id_only=1`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const data = await res.json();
            const latest = Number(data.latest_id || 0);
            if (latest > lastId) {
                lastId = latest;
                saveLastId();
            }
        } catch (_) {}
    }

    async function pollFeed() {
        if (busy || !bootstrapped) return;
        busy = true;
        try {
            const res = await fetch(`${feedUrl}?after_id=${lastId}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const data = await res.json();
            const items = Array.isArray(data.items) ? data.items : [];
            if (!items.length) return;

            for (const item of items) {
                const itemId = Number(item.id || 0);
                if (itemId > lastId) {
                    lastId = itemId;
                }
                const title = String(item.title || 'Yeni Bildirim');
                const content = String(item.content || '');
                showToast(title, content);
                showDeviceNotification(title, content);
            }
            saveLastId();
        } catch (_) {
        } finally {
            busy = false;
        }
    }

    (async () => {
        await bootstrapLatestId();
        bootstrapped = true;
        setInterval(pollFeed, 7000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') pollFeed();
        });
    })();
})();
</script>
@endif
@stack('scripts')
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>
