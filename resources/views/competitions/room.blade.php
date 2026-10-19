@extends('layout.app')
@section('title','Yarışma Odası')
@section('content')
@include('competitions._theme')
<div class="modal" id="compDeleteModal">
    <div class="modal-card">
        <div class="modal-head"><strong>Odayı Sil</strong></div>
        <p style="margin:0 0 14px;color:#475569;">Bu yarışma odasını kalıcı olarak silmek üzeresiniz. Tüm katılımcı kayıtları da birlikte silinecek. Bu işlem geri alınamaz.</p>
        <form method="POST" action="{{ route('competitions.room.destroy', $room) }}" style="margin:0;display:flex;gap:8px;justify-content:flex-end;">
            @csrf
            @method('DELETE')
            <button type="button" class="btn" id="compDeleteCancel">Vazgeç</button>
            <button type="submit" class="btn btn-danger">Evet, Sil</button>
        </form>
    </div>
</div>
<div class="cx">
<div class="cx-hero">
    <div class="cx-hero-icon">🏆</div>
    <div>
        <h1>{{ $room->game_name }}</h1>
        <p>Seviye {{ $room->level_from }}–{{ $room->level_to }} · Süre {{ (int) ($room->duration_seconds / 60) }} dk · Kod <b class="code">{{ $room->join_code }}</b></p>
    </div>
    <div class="cx-chips">
        @if($room->status === 'lobby')<span class="cx-chip lobby" id="compStatusChip">⏳ Lobide</span>
        @elseif($room->status === 'live')<span class="cx-chip live" id="compStatusChip">● Canlı</span>
        @else<span class="cx-chip done" id="compStatusChip">🏁 Bitti</span>@endif
        <span class="cx-chip">👥 <span id="compChipCount">{{ $room->participants()->count() }}</span></span>
    </div>
</div>

@if($room->status === 'lobby')
<div class="cx-panel cx-lobby">
    <p style="margin:0;font-weight:800;letter-spacing:.14em;text-transform:uppercase;font-size:12px">Katılım Kodu</p>
    <div class="comp-lobby-code">{{ $room->join_code }}</div>
    <p style="max-width:640px;margin:0 auto 12px">Öğrenciler <b style="color:#fff">Canlı Yarışmalar</b> bölümünden bu kodu girip lobiye katılır. Herkes hazır olunca butona bas, yarışma <b style="color:#fff">aynı anda</b> başlasın.</p>
    <div class="cx-big"><span id="compLobbyJoined">{{ $room->participants()->count() }}</span></div>
    <p style="margin:4px 0 16px">lobide bekleyen öğrenci</p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <form method="POST" action="{{ route('competitions.room.launch', $room) }}" style="margin:0">
            @csrf
            <button class="btn btn-start" type="submit">▶ Herkese Başlat</button>
        </form>
        <button type="button" class="btn btn-danger comp-delete-trigger">🗑 Odayı Sil</button>
    </div>
</div>
<div class="cx-panel">
    <h3>Katılan Öğrenciler <span id="compJoinedCount" style="color:#94a3b8;font-weight:600">({{ $room->participants()->count() }} katılımcı)</span></h3>
    <div id="compLeaderboard" class="comp-student-grid">
        @forelse($rows as $row)
            <div class="comp-rank-row" data-student="{{ $row['student_user_id'] }}">
                <div class="comp-rank-no">•</div>
                @if($row['avatar_url'])
                    <img class="comp-rank-avatar" src="{{ $row['avatar_url'] }}" alt="{{ $row['avatar_name'] }}">
                @else
                    <div class="comp-rank-avatar empty">?</div>
                @endif
                <div class="comp-rank-name">{{ $row['name'] }}</div>
                <div class="comp-rank-status">Bekliyor</div>
            </div>
        @empty
            <p>Henüz katılan öğrenci yok.</p>
        @endforelse
    </div>
</div>
@else
<div class="cx-panel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap">
        <div>
            <p style="margin:0;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase">Durum: <span id="compStatusText" style="color:#fff">{{ $room->status === 'live' ? 'Canlı' : 'Bitti' }}</span></p>
            @if($room->status === 'live')
                <span class="comp-big-clock"><span id="compCountdown">--</span> sn</span>
            @endif
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if($room->status === 'live')
            <form method="POST" action="{{ route('competitions.room.finish', $room) }}" style="margin:0">@csrf<button class="btn btn-danger" type="submit">⏹ Yarışmayı Bitir</button></form>
            @endif
            <a class="btn" href="{{ route('competitions.index') }}">← Canlı Yarışmalar</a>
            <button type="button" class="btn btn-danger comp-delete-trigger">🗑 Odayı Sil</button>
        </div>
    </div>
    @if($room->status === 'live')
        <div class="comp-timer-bar"><div class="comp-timer-fill" id="compTimerFill"></div></div>
    @endif
</div>

<div class="cx-panel">
    <h3>Canlı Sıralama <span id="compJoinedCount" style="color:#94a3b8;font-weight:600">({{ $room->participants()->count() }} katılımcı)</span></h3>
    <div id="compLeaderboard" class="comp-student-grid">
        @forelse($rows as $i => $row)
            @php($statusClass = $row['finished'] ? 'status-green' : ($row['progress_percent'] >= 50 ? 'status-orange' : 'status-red'))
            <div class="comp-rank-row {{ $i === 0 ? 'leader' : '' }} {{ $statusClass }}" data-student="{{ $row['student_user_id'] }}">
                <div class="comp-rank-no">{{ $i === 0 ? '👑' : '#'.($i + 1) }}</div>
                @if($row['avatar_url'])
                    <img class="comp-rank-avatar" src="{{ $row['avatar_url'] }}" alt="{{ $row['avatar_name'] }}">
                @else
                    <div class="comp-rank-avatar empty">?</div>
                @endif
                <div class="comp-rank-name">{{ $row['name'] }}</div>
                <div class="comp-rank-status">%{{ number_format($row['progress_percent'], 0) }} — {{ $row['xp_earned'] }} XP{{ $row['completed_seconds'] !== null ? ' — '.$row['completed_seconds'].' sn' : '' }}{{ $row['finished'] ? ' ✅' : '' }}</div>
            </div>
        @empty
            <p>Henüz ilerleme verisi yok.</p>
        @endforelse
    </div>
</div>
@endif
</div>

@push('scripts')
<script>
(() => {
    window.__COMPETITION_ROOM_ID__ = {{ $room->id }};
    const deleteModal = document.getElementById('compDeleteModal');
    const deleteCancel = document.getElementById('compDeleteCancel');
    document.querySelectorAll('.comp-delete-trigger').forEach((btn) => {
        btn.addEventListener('click', () => deleteModal?.classList.add('open'));
    });
    deleteCancel?.addEventListener('click', () => deleteModal?.classList.remove('open'));
    deleteModal?.addEventListener('click', (e) => { if (e.target === deleteModal) deleteModal.classList.remove('open'); });

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

    // --- Canli siralama: FLIP animasyonlu yeniden siralama -----------------
    // Satirlar DOM'dan silinip yeniden yazilmiyor; her ogrenci icin sabit bir
    // eleman tutuluyor (data-student anahtari) ve sira degistiginde eski/yeni
    // konum farki bir transform animasyonuyla kapatiliyor (FLIP teknigi).
    // Animasyon hizi, o ogrencinin bir onceki yoklamadan bu yana kazandigi XP
    // miktarina (birim zamanda XP kazanma hizina) gore ayarlaniyor: hizli XP
    // kazanan bir ogrenci sıralamada HIZLI/enerjik bir sicrayisla yukari
    // cikiyor, yavas ilerleyen/duran bir ogrencinin sirasi ise yavas ve
    // yumusak bir gecisle degisiyor.
    const rowElements = new Map(); // student_user_id (string) -> DOM elemani
    const lastXpByStudent = new Map();
    let lastPollAt = Date.now();

    // Sayfa ilk yuklendiginde Blade zaten sunucu tarafinda satirlari
    // basmis oluyor (server-side render). Bu satirlari burada Map'e
    // KAYDETMEZSEK, ilk poll() cagrisinda ayni ogrenciler icin YENI
    // elemanlar olusturulup eskileri DOM'da unutulmus/yetim halde
    // kaliyordu - ekranda ayni ogrenci iki kez (biri eski/durgun, biri
    // guncel) goruluyordu. Mevcut satirlari once burada devralıyoruz.
    if (leaderboardEl) {
        leaderboardEl.querySelectorAll('[data-student]').forEach((el) => {
            rowElements.set(el.dataset.student, el);
        });
    }

    function renderLeaderboard(rows) {
        if (!leaderboardEl) return;
        if (!rows.length) {
            leaderboardEl.innerHTML = '<p style="color:#94a3b8">Henüz ilerleme verisi yok.</p>';
            rowElements.clear();
            return;
        }

        const now = Date.now();
        const dtSec = Math.max(0.4, (now - lastPollAt) / 1000);

        const oldTops = new Map();
        rowElements.forEach((el, id) => oldTops.set(id, el.getBoundingClientRect().top));

        const seenIds = new Set();
        rows.forEach((row, i) => {
            const id = String(row.student_user_id);
            seenIds.add(id);
            const prevXp = lastXpByStudent.has(id) ? lastXpByStudent.get(id) : row.xp_earned;
            const xpRate = Math.max(0, (row.xp_earned - prevXp) / dtSec);
            // Hizli XP kazanma -> kisa (hizli) gecis suresi; yavas/sabit -> uzun (yavas) gecis.
            const durationMs = Math.max(220, Math.min(900, 900 - Math.min(xpRate, 20) * 34));

            let el = rowElements.get(id);
            const isNew = !el;
            if (!el) {
                el = document.createElement('div');
                el.className = 'comp-rank-row';
                el.dataset.student = String(id);
                rowElements.set(id, el);
            }
            const avatarHtml = row.avatar_url
                ? `<img class="comp-rank-avatar" src="${row.avatar_url}" alt="${row.avatar_name || ''}">`
                : `<div class="comp-rank-avatar empty">?</div>`;
            el.classList.remove('status-green', 'status-orange', 'status-red');
            if (status === 'lobby') {
                el.classList.remove('leader');
                el.innerHTML = `
                    <div class="comp-rank-no">•</div>
                    ${avatarHtml}
                    <div class="comp-rank-name">${row.name}</div>
                    <div class="comp-rank-status">Bekliyor</div>
                `;
            } else {
                el.classList.toggle('leader', i === 0);
                const statusClass = row.finished ? 'status-green' : (row.progress_percent >= 50 ? 'status-orange' : 'status-red');
                el.classList.add(statusClass);
                const timeSuffix = row.completed_seconds !== null && row.completed_seconds !== undefined ? ` — ${row.completed_seconds} sn` : '';
                el.innerHTML = `
                    <div class="comp-rank-no">${i === 0 ? '👑' : '#' + (i + 1)}</div>
                    ${avatarHtml}
                    <div class="comp-rank-name">${row.name}</div>
                    <div class="comp-rank-status">%${Math.round(row.progress_percent)} — ${row.xp_earned} XP${timeSuffix}${row.finished ? ' ✅' : ''}</div>
                `;
            }
            el.dataset.duration = String(durationMs);
            leaderboardEl.appendChild(el); // yeni siraya gore konumlandir
            lastXpByStudent.set(id, row.xp_earned);

            if (isNew) {
                el.style.transition = 'none';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-6px)';
            }
        });

        // Odadan ayrilan/artik listede olmayan satirlari kaldir
        Array.from(rowElements.keys()).forEach((id) => {
            if (!seenIds.has(id)) {
                rowElements.get(id)?.remove();
                rowElements.delete(id);
            }
        });

        // FLIP: Invert + Play (eski konumdan yeni konuma anima et)
        rowElements.forEach((el, id) => {
            const durationMs = el.dataset.duration || '400';
            const oldTop = oldTops.get(id);
            if (oldTop == null) {
                requestAnimationFrame(() => {
                    el.style.transition = `transform ${durationMs}ms ease, opacity 300ms ease`;
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                });
                return;
            }
            const newTop = el.getBoundingClientRect().top;
            const delta = oldTop - newTop;
            if (Math.abs(delta) < 0.5) return;
            el.style.transition = 'none';
            el.style.transform = `translateY(${delta}px)`;
            requestAnimationFrame(() => {
                el.style.transition = `transform ${durationMs}ms ease`;
                el.style.transform = 'translateY(0)';
            });
        });

        lastPollAt = now;
    }

    async function poll() {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            clockOffsetMs = data.server_now_ms - Date.now();

            if (lobbyJoinedEl) lobbyJoinedEl.textContent = String(data.joined ?? 0);
            if (joinedCountEl) joinedCountEl.textContent = `(${data.joined ?? 0} katılımcı)`;
            const chipCountEl = document.getElementById('compChipCount');
            if (chipCountEl) chipCountEl.textContent = String(data.joined ?? 0);

            if (data.status === 'finished' && status !== 'finished') {
                window.location.reload();
                return;
            }
            endsAtMs = data.ends_at_ms || endsAtMs;
            if (Array.isArray(data.rows)) renderLeaderboard(data.rows);
        } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
    }
    poll();
    setInterval(poll, status === 'lobby' ? 2000 : 1500);
})();
</script>
@endpush
@endsection
