@extends('layout.app')
@section('title','Canli Quiz Oturumu')
@section('content')
@php
    $questions = $session->quiz?->questions ?? collect();
    $current = $questions->get($session->current_index);
    $left = (array) ($current?->options['left'] ?? []);
    $right = (array) ($current?->options['right'] ?? []);
@endphp
<style>
.lq-timer-bar{height:10px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-top:8px}
.lq-timer-fill{height:100%;background:linear-gradient(90deg,#22c55e,#4f46e5);width:100%;transition:width .25s linear}
.lq-timer-fill.lq-warn{background:linear-gradient(90deg,#f59e0b,#ef4444)}
.lq-big-clock{font-size:34px;font-weight:800;color:#1e293b}
.lq-lobby-code{font-size:44px;font-weight:900;letter-spacing:4px;color:#4f46e5}
</style>
<div class="top"><h1>Canli Quiz Oturumu</h1></div>

@if($session->status === 'lobby')
<div class="card" style="margin-bottom:12px;text-align:center;padding:28px;">
    <p style="margin:0;color:#64748b;font-weight:700;">Katilim Kodu</p>
    <div class="lq-lobby-code">{{ $session->join_code }}</div>
    <p style="margin-top:8px;">Ogrenciler koda girip lobiye katilsin. Herkes hazir oldugunda asagidaki butona basin,
       soru suresi <strong>o an</strong> tum ogrenciler icin ayni anda baslar.</p>
    <p><strong>Lobide bekleyen ogrenci: <span id="lqLobbyJoined">{{ $session->participants()->count() }}</span></strong></p>
    <form method="POST" action="{{ route('live-quiz.session.launch', $session) }}">
        @csrf
        <button class="btn btn-primary" type="submit" style="font-size:18px;padding:12px 28px;">Herkese Baslat</button>
    </form>
</div>
@else
<div class="card" style="margin-bottom:12px;">
    <p><strong>Quiz:</strong> {{ $session->quiz?->title }}</p>
    <p><strong>Katilim Kodu:</strong> <span style="font-size:20px">{{ $session->join_code }}</span></p>
    <p><strong>Durum:</strong> <span id="lqStatusText">{{ $session->status }}</span> |
       <strong>Soru:</strong> <span id="lqQIndex">{{ $session->current_index + 1 }}</span>/<span id="lqQTotal">{{ $questions->count() }}</span></p>

    @if($session->status === 'live')
    <div>
        <span class="lq-big-clock"><span id="lqCountdown">--</span> sn</span>
        <div class="lq-timer-bar"><div class="lq-timer-fill" id="lqTimerFill"></div></div>
    </div>
    @endif

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
        <form method="POST" action="{{ route('live-quiz.session.lock', $session) }}">@csrf<button class="btn" type="submit">{{ $session->is_locked ? 'Kilidi Ac' : 'Kilitle' }}</button></form>
        <form method="POST" action="{{ route('live-quiz.session.next', $session) }}">@csrf<button class="btn btn-primary" type="submit">Sonraki Soru</button></form>
        <form method="POST" action="{{ route('live-quiz.session.finish', $session) }}">@csrf<button class="btn btn-danger" type="submit">Quizi Bitir</button></form>
        <a class="btn" href="{{ route('live-quiz.session.report', $session) }}">Detayli Rapor</a>
    </div>
</div>

<div class="card" style="margin-bottom:12px;">
    <h3>Anlik Durum</h3>
    <div style="display:grid;grid-template-columns:repeat(4,minmax(120px,1fr));gap:8px;">
        <div class="card" style="padding:10px;"><strong>Katilan:</strong> <span id="lqStatJoined">{{ $currentQuestionStats['joined'] }}</span></div>
        <div class="card" style="padding:10px;"><strong>Cevaplayan:</strong> <span id="lqStatAnswered">{{ $currentQuestionStats['answered'] }}</span></div>
        <div class="card" style="padding:10px;"><strong>Dogru:</strong> <span id="lqStatCorrect">{{ $currentQuestionStats['correct'] }}</span></div>
        <div class="card" style="padding:10px;"><strong>Yanlis:</strong> <span id="lqStatWrong">{{ $currentQuestionStats['wrong'] }}</span></div>
    </div>

    @if($current)
        <div style="margin-top:10px;">
            <strong>Aktif Soru ({{ strtoupper($current->type) }})</strong>
            <p>{{ $current->question_text }}</p>
            @if($current->type === 'multiple' || $current->type === 'truefalse')
                @foreach((array) $current->options as $idx => $opt)
                    <div>{{ chr(65 + $idx) }}) {{ $opt }}</div>
                @endforeach
            @elseif($current->type === 'dragdrop')
                @foreach($left as $idx => $l)
                    <div>{{ $l }} -> {{ $right[$idx] ?? '-' }}</div>
                @endforeach
            @endif
        </div>
    @endif
</div>

<div class="card" style="margin-bottom:12px;">
    <h3>Katilan Ogrenciler</h3>
    <table>
        <thead><tr><th>#</th><th>Ogrenci</th><th>Katilim</th></tr></thead>
        <tbody>
        @forelse($session->participants as $i => $participant)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $participant->studentUser?->name ?? ('user_'.$participant->student_user_id) }}</td>
                <td>{{ $participant->created_at?->format('H:i:s') }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Henuz katilan yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Canli Siralama / Rapor</h3>
    <table>
        <thead><tr><th>#</th><th>Ogrenci</th><th>Dogru</th><th>Yanlis</th><th>XP</th></tr></thead>
        <tbody id="lqLeaderboardBody">
        @forelse($rows as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row['student_name'] }}</td>
                <td>{{ $row['correct'] }}</td>
                <td>{{ $row['wrong'] }}</td>
                <td>{{ $row['xp'] }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Henuz cevap yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endif

@push('scripts')
<script>
(() => {
    const sessionStatus = @json($session->status);
    if (sessionStatus === 'finished') return;

    const statusUrl = @json(route('live-quiz.session.status', $session));
    const reportUrlDefault = @json(route('live-quiz.session.report', $session));
    let currentIndex = {{ (int) $session->current_index }};
    let clockOffsetMs = 0; // sunucu saati - tarayici saati

    const countdownEl = document.getElementById('lqCountdown');
    const timerFillEl = document.getElementById('lqTimerFill');
    const lobbyJoinedEl = document.getElementById('lqLobbyJoined');
    const statJoined = document.getElementById('lqStatJoined');
    const statAnswered = document.getElementById('lqStatAnswered');
    const statCorrect = document.getElementById('lqStatCorrect');
    const statWrong = document.getElementById('lqStatWrong');
    const leaderboardBody = document.getElementById('lqLeaderboardBody');

    let endsAtMs = {{ (int) ($session->ends_at_ms ?? 0) }};
    let durationMs = {{ (int) (($current?->duration_sec ?? 30) * 1000) }};

    function tickClock() {
        if (!countdownEl || sessionStatus !== 'live' && !document.getElementById('lqCountdown')) return;
        if (!endsAtMs) return;
        const nowMs = Date.now() + clockOffsetMs;
        const leftMs = Math.max(0, endsAtMs - nowMs);
        const leftSec = Math.ceil(leftMs / 1000);
        if (countdownEl) countdownEl.textContent = String(leftSec);
        if (timerFillEl) {
            const pct = durationMs > 0 ? Math.max(0, Math.min(100, (leftMs / durationMs) * 100)) : 0;
            timerFillEl.style.width = pct + '%';
            timerFillEl.classList.toggle('lq-warn', leftSec <= 5);
        }
    }
    setInterval(tickClock, 250);
    tickClock();

    async function poll() {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            clockOffsetMs = data.server_now_ms - Date.now();

            if (data.status === 'finished') {
                window.location.href = data.report_url || reportUrlDefault;
                return;
            }

            if (lobbyJoinedEl) lobbyJoinedEl.textContent = String(data.stats?.joined ?? 0);

            if (data.current_index !== currentIndex) {
                // Soru degisti: sayfayi yenileyip yeni soru metnini/secenekleri gostermek en guvenlisi.
                window.location.reload();
                return;
            }

            endsAtMs = data.ends_at_ms || endsAtMs;
            if (statJoined) statJoined.textContent = String(data.stats?.joined ?? 0);
            if (statAnswered) statAnswered.textContent = String(data.stats?.answered ?? 0);
            if (statCorrect) statCorrect.textContent = String(data.stats?.correct ?? 0);
            if (statWrong) statWrong.textContent = String(data.stats?.wrong ?? 0);

            if (leaderboardBody && Array.isArray(data.rows)) {
                if (data.rows.length === 0) {
                    leaderboardBody.innerHTML = '<tr><td colspan="5">Henuz cevap yok.</td></tr>';
                } else {
                    leaderboardBody.innerHTML = data.rows.map((row, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${row.student_name}</td>
                            <td>${row.correct}</td>
                            <td>${row.wrong}</td>
                            <td>${row.xp}</td>
                        </tr>
                    `).join('');
                }
            }
        } catch (e) { /* bir sonraki polling denemesinde tekrar denenecek */ }
    }
    poll();
    setInterval(poll, sessionStatus === 'lobby' ? 2000 : 2500);
})();
</script>
@endpush
@endsection
