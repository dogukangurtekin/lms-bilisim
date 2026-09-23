@extends('layout.app')
@section('title','Yarışma Odası')
@section('content')
<style>
.comp-timer-bar{height:10px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-top:8px}
.comp-timer-fill{height:100%;background:linear-gradient(90deg,#22c55e,#4f46e5);width:100%;transition:width .25s linear}
.comp-timer-fill.warn{background:linear-gradient(90deg,#f59e0b,#ef4444)}
.comp-big-clock{font-size:34px;font-weight:800;color:#1e293b}
.comp-lobby-code{font-size:44px;font-weight:900;letter-spacing:4px;color:#4f46e5}
.comp-rank-row{display:grid;grid-template-columns:40px 1fr auto;gap:10px;align-items:center;padding:8px 10px;border-radius:10px}
.comp-rank-row:nth-child(odd){background:#f8fafc}
.comp-rank-row.leader{background:#fef3c7}
</style>
<div class="top"><h1>Yarışma Odası — {{ $room->game_name }}</h1></div>

@if($room->status === 'lobby')
<div class="card" style="margin-bottom:12px;text-align:center;padding:28px;">
    <p style="margin:0;color:#64748b;font-weight:700;">Katılım Kodu</p>
    <div class="comp-lobby-code">{{ $room->join_code }}</div>
    <p style="margin-top:8px;">Öğrenciler "Canlı Yarışmalar"dan kodu girip lobiye katılsın. Herkes hazır olduğunda
       aşağıdaki butona basınca yarışma <strong>o an</strong> tüm öğrenciler için aynı anda başlar.</p>
    <p><strong>Lobide bekleyen öğrenci: <span id="compLobbyJoined">{{ $room->participants()->count() }}</span></strong></p>
    <p style="color:#64748b;font-size:13px">Seviye aralığı: {{ $room->level_from }}–{{ $room->level_to }} | Süre: {{ (int) ($room->duration_seconds / 60) }} dk</p>
    <form method="POST" action="{{ route('competitions.room.launch', $room) }}" style="display:inline-block">
        @csrf
        <button class="btn btn-primary" type="submit" style="font-size:18px;padding:12px 28px;">Herkese Başlat</button>
    </form>
    <a class="btn btn-danger" href="{{ route('competitions.room.destroy.confirm', $room) }}" style="display:inline-block;margin-left:8px">Odayı Sil</a>
</div>
@else
<div class="card" style="margin-bottom:12px;">
    <p><strong>Oyun:</strong> {{ $room->game_name }} | <strong>Kod:</strong> {{ $room->join_code }} |
       <strong>Seviye:</strong> {{ $room->level_from }}–{{ $room->level_to }}</p>
    <p><strong>Durum:</strong> <span id="compStatusText">{{ $room->status === 'live' ? 'Canlı' : 'Bitti' }}</span></p>

    @if($room->status === 'live')
    <div>
        <span class="comp-big-clock"><span id="compCountdown">--</span> sn</span>
        <div class="comp-timer-bar"><div class="comp-timer-fill" id="compTimerFill"></div></div>
    </div>
    @endif

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
        @if($room->status === 'live')
        <form method="POST" action="{{ route('competitions.room.finish', $room) }}">@csrf<button class="btn btn-danger" type="submit">Yarışmayı Bitir</button></form>
        @endif
        <a class="btn" href="{{ route('competitions.index') }}">Canlı Yarışmalar'a Dön</a>
        <a class="btn btn-danger" href="{{ route('competitions.room.destroy.confirm', $room) }}">Odayı Sil</a>
    </div>
</div>

<div class="card">
    <h3>Canlı Sıralama <span id="compJoinedCount">({{ $room->participants()->count() }} katılımcı)</span></h3>
    <div id="compLeaderboard">
        @forelse($rows as $i => $row)
            <div class="comp-rank-row {{ $i === 0 ? 'leader' : '' }}">
                <div>#{{ $i + 1 }}</div>
                <div>{{ $row['name'] }}</div>
                <div>%{{ number_format($row['progress_percent'], 0) }} — {{ $row['xp_earned'] }} XP{{ $row['finished'] ? ' ✅' : '' }}</div>
            </div>
        @empty
            <p style="color:#64748b">Henüz ilerleme verisi yok.</p>
        @endforelse
    </div>
</div>
@endif

@push('scripts')
<script>
(() => {
    const status = @json($room->status);
    if (status === 'finished') return;

    const statusUrl = @json(route('competitions.room.status', $room));
    let endsAtMs = {{ (int) ($room->ends_at_ms ?? 0) }};
    let clockOffsetMs = 0;

    const countdownEl = document.getElementById('compCountdown');
    const timerFillEl = document.getElementById('compTimerFill');
    const lobbyJoinedEl = document.getElementById('compLobbyJoined');
    const joinedCountEl = document.getElementById('compJoinedCount');
    const leaderboardEl = document.getElementById('compLeaderboard');
    const statusTextEl = document.getElementById('compStatusText');
    const durationMs = {{ (int) $room->duration_seconds * 1000 }};

    function tickClock() {
        if (!countdownEl || !endsAtMs) return;
        const nowMs = Date.now() + clockOffsetMs;
        const leftMs = Math.max(0, endsAtMs - nowMs);
        const leftSec = Math.ceil(leftMs / 1000);
        countdownEl.textContent = String(leftSec);
        if (timerFillEl) {
            const pct = durationMs > 0 ? Math.max(0, Math.min(100, (leftMs / durationMs) * 100)) : 0;
            timerFillEl.style.width = pct + '%';
            timerFillEl.classList.toggle('warn', leftSec <= 15);
        }
    }
    setInterval(tickClock, 250);
    tickClock();

    function renderLeaderboard(rows) {
        if (!leaderboardEl) return;
        if (!rows.length) {
            leaderboardEl.innerHTML = '<p style="color:#64748b">Henüz ilerleme verisi yok.</p>';
            return;
        }
        leaderboardEl.innerHTML = rows.map((row, i) => `
            <div class="comp-rank-row ${i === 0 ? 'leader' : ''}">
                <div>#${i + 1}</div>
                <div>${row.name}</div>
                <div>%${Math.round(row.progress_percent)} — ${row.xp_earned} XP${row.finished ? ' ✅' : ''}</div>
            </div>
        `).join('');
    }

    async function poll() {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            clockOffsetMs = data.server_now_ms - Date.now();

            if (lobbyJoinedEl) lobbyJoinedEl.textContent = String(data.joined ?? 0);
            if (joinedCountEl) joinedCountEl.textContent = `(${data.joined ?? 0} katılımcı)`;

            if (data.status === 'finished' && status !== 'finished') {
                window.location.reload();
                return;
            }
            endsAtMs = data.ends_at_ms || endsAtMs;
            if (Array.isArray(data.rows)) renderLeaderboard(data.rows);
        } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
    }
    poll();
    setInterval(poll, status === 'lobby' ? 2000 : 2500);
})();
</script>
@endpush
@endsection
