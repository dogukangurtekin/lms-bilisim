@extends('layout.app')
@section('title','Yarışma Odası')
@section('content')
<style>
.comp-timer-bar{height:10px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-top:8px}
.comp-timer-fill{height:100%;background:linear-gradient(90deg,#22c55e,#4f46e5);width:100%;transition:width .25s linear}
.comp-timer-fill.warn{background:linear-gradient(90deg,#f59e0b,#ef4444)}
.comp-big-clock{font-size:34px;font-weight:800;color:#1e293b}
.comp-lobby-code{font-size:44px;font-weight:900;letter-spacing:4px;color:#4f46e5}
.comp-rank-row{display:grid;grid-template-columns:40px 36px 1fr auto;gap:10px;align-items:center;padding:8px 10px;border-radius:10px;background:#f8fafc;margin-bottom:6px;will-change:transform}
.comp-rank-row.leader{background:#fef3c7}
.comp-rank-row .comp-rank-no{font-weight:900;color:#64748b}
.comp-rank-row.leader .comp-rank-no{color:#b45309}
.comp-rank-avatar{width:36px;height:36px;border-radius:8px;object-fit:cover;background:#e2e8f0}
.comp-rank-avatar.empty{display:flex;align-items:center;justify-content:center;font-size:16px;color:#94a3b8}
</style>
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
    <button type="button" class="btn btn-danger comp-delete-trigger" style="display:inline-block;margin-left:8px">Odayı Sil</button>
</div>
<div class="card">
    <h3>Katılan Öğrenciler <span id="compJoinedCount">({{ $room->participants()->count() }} katılımcı)</span></h3>
    <div id="compLeaderboard">
        @forelse($rows as $row)
            <div class="comp-rank-row" data-student="{{ $row['student_user_id'] }}">
                <div class="comp-rank-no">•</div>
                @if($row['avatar_url'])
                    <img class="comp-rank-avatar" src="{{ $row['avatar_url'] }}" alt="{{ $row['avatar_name'] }}">
                @else
                    <div class="comp-rank-avatar empty">?</div>
                @endif
                <div>{{ $row['name'] }}</div>
                <div style="color:#64748b;font-size:13px">Bekliyor</div>
            </div>
        @empty
            <p style="color:#64748b">Henüz katılan öğrenci yok.</p>
        @endforelse
    </div>
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
        <button type="button" class="btn btn-danger comp-delete-trigger">Odayı Sil</button>
    </div>
</div>

<div class="card">
    <h3>Canlı Sıralama <span id="compJoinedCount">({{ $room->participants()->count() }} katılımcı)</span></h3>
    <div id="compLeaderboard">
        @forelse($rows as $i => $row)
            <div class="comp-rank-row {{ $i === 0 ? 'leader' : '' }}" data-student="{{ $row['student_user_id'] }}">
                <div class="comp-rank-no">#{{ $i + 1 }}</div>
                @if($row['avatar_url'])
                    <img class="comp-rank-avatar" src="{{ $row['avatar_url'] }}" alt="{{ $row['avatar_name'] }}">
                @else
                    <div class="comp-rank-avatar empty">?</div>
                @endif
                <div>{{ $row['name'] }}</div>
                <div>%{{ number_format($row['progress_percent'], 0) }} — {{ $row['xp_earned'] }} XP{{ $row['completed_seconds'] !== null ? ' — '.$row['completed_seconds'].' sn' : '' }}{{ $row['finished'] ? ' ✅' : '' }}</div>
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
    const rowElements = new Map(); // student_user_id -> DOM elemani
    const lastXpByStudent = new Map();
    let lastPollAt = Date.now();

    function renderLeaderboard(rows) {
        if (!leaderboardEl) return;
        if (!rows.length) {
            leaderboardEl.innerHTML = '<p style="color:#64748b">Henüz ilerleme verisi yok.</p>';
            rowElements.clear();
            return;
        }

        const now = Date.now();
        const dtSec = Math.max(0.4, (now - lastPollAt) / 1000);

        const oldTops = new Map();
        rowElements.forEach((el, id) => oldTops.set(id, el.getBoundingClientRect().top));

        const seenIds = new Set();
        rows.forEach((row, i) => {
            const id = row.student_user_id;
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
            if (status === 'lobby') {
                el.classList.remove('leader');
                el.innerHTML = `
                    <div class="comp-rank-no">•</div>
                    ${avatarHtml}
                    <div>${row.name}</div>
                    <div style="color:#64748b;font-size:13px">Bekliyor</div>
                `;
            } else {
                el.classList.toggle('leader', i === 0);
                const timeSuffix = row.completed_seconds !== null && row.completed_seconds !== undefined ? ` — ${row.completed_seconds} sn` : '';
                el.innerHTML = `
                    <div class="comp-rank-no">#${i + 1}</div>
                    ${avatarHtml}
                    <div>${row.name}</div>
                    <div>%${Math.round(row.progress_percent)} — ${row.xp_earned} XP${timeSuffix}${row.finished ? ' ✅' : ''}</div>
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
