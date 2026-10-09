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
.lq-participant-grid{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin:16px auto;max-width:900px}
.lq-participant-chip{padding:9px 13px;border-radius:999px;background:#eef2ff;border:1px solid #c7d2fe;color:#312e81;font-weight:800}
.lq-participant-empty{color:#64748b;font-weight:700}
.lq-results-stage{overflow:hidden;text-align:center;padding:28px;background:linear-gradient(135deg,#312e81,#6d28d9);color:#fff}
.lq-results-stage h2{margin:0 0 8px;font-size:34px}.lq-results-stage p{margin:6px 0 18px}
.lq-top-five{display:flex;flex-wrap:wrap;justify-content:center;align-items:flex-end;gap:12px}
.lq-winner{min-width:150px;padding:14px;border-radius:16px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);animation:lqWinnerIn .55s both;animation-delay:calc(var(--rank) * .12s)}
.lq-winner:first-child{transform-origin:center bottom;background:linear-gradient(145deg,#f59e0b,#f97316);padding:20px;min-width:190px}
.lq-winner-rank{font-size:24px;font-weight:900}.lq-winner-name{font-size:24px;font-weight:900;overflow-wrap:anywhere}.lq-winner-state{font-size:13px;font-weight:800;margin-top:5px}
.lq-double-stage{text-align:center;padding:42px 24px;background:radial-gradient(circle at center,#fbbf24,#f97316 45%,#7c2d12);color:#fff;overflow:hidden}.lq-double-icon{font-size:72px;animation:lqDoublePulse .7s ease-in-out infinite alternate}.lq-double-title{font-size:46px;font-weight:950;margin:8px 0;text-shadow:0 5px 18px rgba(0,0,0,.3)}
@keyframes lqDoublePulse{from{transform:scale(.82) rotate(-5deg)}to{transform:scale(1.12) rotate(5deg)}}
@keyframes lqWinnerIn{from{opacity:0;transform:translateY(45px) scale(.8)}to{opacity:1;transform:translateY(0) scale(1)}}
</style>
<div class="top"><h1>Canli Quiz Oturumu</h1></div>

@if($session->status === 'lobby')
<div class="card" style="margin-bottom:12px;text-align:center;padding:28px;">
    <p style="margin:0;color:#64748b;font-weight:700;">Katilim Kodu</p>
    <div class="lq-lobby-code">{{ $session->join_code }}</div>
    <p style="margin-top:8px;">Ogrenciler koda girip lobiye katilsin. Herkes hazir oldugunda asagidaki butona basin,
       soru suresi <strong>o an</strong> tum ogrenciler icin ayni anda baslar.</p>
    <p><strong>Lobide bekleyen ogrenci: <span id="lqLobbyJoined">{{ $session->participants()->count() }}</span></strong></p>
    <div class="lq-participant-grid" id="lqParticipantList">
        @forelse($participantRows as $participant)
            <span class="lq-participant-chip">{{ $participant['student_name'] }}</span>
        @empty
            <span class="lq-participant-empty">Henüz katılan öğrenci yok.</span>
        @endforelse
    </div>
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
        <span class="lq-big-clock">{{ $session->phase === 'intro' ? 'Soru açılıyor' : ($session->phase === 'results' ? 'Sıradaki soruya' : 'Kalan süre') }}: <span id="lqCountdown">--</span> sn</span>
        <div class="lq-timer-bar"><div class="lq-timer-fill" id="lqTimerFill"></div></div>
    </div>
    @endif

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
        <form method="POST" action="{{ route('live-quiz.session.next', $session) }}">@csrf<button class="btn btn-primary" type="submit" {{ $session->phase !== 'question' ? 'disabled' : '' }}>Cevapları Kapat / Sonraki Soru</button></form>
        <form method="POST" action="{{ route('live-quiz.session.finish', $session) }}">@csrf<button class="btn btn-danger" type="submit">Quizi Bitir</button></form>
        <a class="btn" href="{{ route('live-quiz.session.report', $session) }}">Detayli Rapor</a>
    </div>
</div>

@if($session->status === 'live' && $session->phase === 'intro')
<div class="card lq-double-stage" style="margin-bottom:12px;">
    <div class="lq-double-icon">⚡ 2X</div>
    <div class="lq-double-title">2 Kat Puanlı Soru!</div>
    <p>Bu soruda kazanılan XP iki kat olarak hesaplanacak.</p>
</div>
@elseif($session->status === 'live' && $session->phase === 'results')
<div class="card lq-results-stage" style="margin-bottom:12px;">
    <h2>Soru Sonuçları</h2>
    <p>İlk 5 öğrenci gösteriliyor. Yeni soru tüm ekranlarda aynı anda açılacak.</p>
    <div class="lq-top-five">
        @forelse($topFive as $student)
            <div class="lq-winner" style="--rank:{{ $student['rank'] }}">
                <div class="lq-winner-rank">#{{ $student['rank'] }}</div>
                <div class="lq-winner-name">{{ $student['student_name'] }}</div>
                <div class="lq-winner-state">{{ $student['answered'] ? ($student['is_correct'] ? 'Doğru' : 'Yanlış') : 'Cevaplamadı' }} · {{ $student['xp'] }} XP</div>
            </div>
        @empty
            <strong>Bu soruda katılımcı bulunmuyor.</strong>
        @endforelse
    </div>
</div>
@endif

<div class="card" style="margin-bottom:12px;">
    <h3>Anlik Durum</h3>
    <div style="display:grid;grid-template-columns:repeat(4,minmax(120px,1fr));gap:8px;">
        <div class="card" style="padding:10px;"><strong>Katilan:</strong> <span id="lqStatJoined">{{ $currentQuestionStats['joined'] }}</span></div>
        <div class="card" style="padding:10px;"><strong>Cevaplayan:</strong> <span id="lqStatAnswered">{{ $currentQuestionStats['answered'] }}</span></div>
        <div class="card" style="padding:10px;"><strong>Dogru:</strong> <span id="lqStatCorrect">{{ $currentQuestionStats['correct'] }}</span></div>
        <div class="card" style="padding:10px;"><strong>Yanlis:</strong> <span id="lqStatWrong">{{ $currentQuestionStats['wrong'] }}</span></div>
    </div>

    @if($current && $session->phase !== 'intro')
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
        <tbody id="lqParticipantBody">
        @forelse($participantRows as $i => $participant)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $participant['student_name'] }}</td>
                <td>{{ $participant['joined_at'] ?? '-' }}</td>
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
    const initialPhase = @json((string) $session->phase);
    if (sessionStatus === 'finished') return;

    const statusUrl = @json(route('live-quiz.session.status', $session));
    const reportUrlDefault = @json(route('live-quiz.session.report', $session));
    let currentIndex = {{ (int) $session->current_index }};
    let clockOffsetMs = 0; // sunucu saati - tarayici saati

    const countdownEl = document.getElementById('lqCountdown');
    const timerFillEl = document.getElementById('lqTimerFill');
    const lobbyJoinedEl = document.getElementById('lqLobbyJoined');
    const participantListEl = document.getElementById('lqParticipantList');
    const participantBodyEl = document.getElementById('lqParticipantBody');
    const statJoined = document.getElementById('lqStatJoined');
    const statAnswered = document.getElementById('lqStatAnswered');
    const statCorrect = document.getElementById('lqStatCorrect');
    const statWrong = document.getElementById('lqStatWrong');
    const leaderboardBody = document.getElementById('lqLeaderboardBody');

    let endsAtMs = {{ (int) ($session->ends_at_ms ?? 0) }};
    let durationMs = {{ $session->phase === 'intro' ? 3000 : ($session->phase === 'results' ? 5000 : (int) (($current?->duration_sec ?? 30) * 1000)) }};

    function renderParticipants(participants) {
        if (!Array.isArray(participants)) return;

        if (participantListEl) {
            participantListEl.replaceChildren();
            if (participants.length === 0) {
                const empty = document.createElement('span');
                empty.className = 'lq-participant-empty';
                empty.textContent = 'Henüz katılan öğrenci yok.';
                participantListEl.appendChild(empty);
            } else {
                participants.forEach((participant) => {
                    const chip = document.createElement('span');
                    chip.className = 'lq-participant-chip';
                    chip.textContent = participant.student_name || 'Öğrenci';
                    participantListEl.appendChild(chip);
                });
            }
        }

        if (participantBodyEl) {
            participantBodyEl.replaceChildren();
            if (participants.length === 0) {
                const row = participantBodyEl.insertRow();
                const cell = row.insertCell();
                cell.colSpan = 3;
                cell.textContent = 'Henüz katılan yok.';
            } else {
                participants.forEach((participant, index) => {
                    const row = participantBodyEl.insertRow();
                    row.insertCell().textContent = String(index + 1);
                    row.insertCell().textContent = participant.student_name || 'Öğrenci';
                    row.insertCell().textContent = participant.joined_at || '-';
                });
            }
        }
    }

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
            renderParticipants(data.participants);

            if (data.current_index !== currentIndex) {
                // Soru degisti: sayfayi yenileyip yeni soru metnini/secenekleri gostermek en guvenlisi.
                window.location.reload();
                return;
            }

            if (String(data.phase || '') !== initialPhase) {
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
