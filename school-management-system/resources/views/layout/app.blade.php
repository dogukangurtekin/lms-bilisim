<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Okul yonetim sistemi admin paneli">
    <title>@yield('title', 'School Management')</title>
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
@stack('scripts')
</body>
</html>
