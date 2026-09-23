@extends('layout.app')
@section('title','Canlı Yarışma')
@section('content')
<style>
.comp-play-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:8px}
.comp-play-badges{display:flex;gap:8px;flex-wrap:wrap}
.comp-play-badge{background:#eef2ff;border:1px solid #c7d2fe;border-radius:999px;padding:6px 12px;font-weight:800;font-size:13px;color:#3730a3}
.comp-play-timer-bar{height:12px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-bottom:14px}
.comp-play-timer-fill{height:100%;background:linear-gradient(90deg,#22c55e,#4f46e5);width:100%;transition:width .25s linear}
.comp-play-timer-fill.warn{background:linear-gradient(90deg,#f59e0b,#ef4444)}
.comp-play-frame-wrap{border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;background:#000;position:relative}
.comp-play-frame-wrap iframe{width:100%;display:block;border:0}
.comp-wait-stage{min-height:420px;display:grid;place-items:center}
.comp-wait-box{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:26px;min-width:min(560px,92vw);text-align:center}
.comp-wait-title{margin:0 0 8px;font-size:32px;font-weight:900}
.comp-wait-count{font-size:64px;line-height:1;font-weight:900;margin:10px 0;color:#4f46e5}
</style>

<div class="comp-play-bar">
    <h1 style="margin:0">{{ $gameName }} — Canlı Yarışma</h1>
    <div class="comp-play-badges">
        <span class="comp-play-badge">Kod: {{ $room->join_code }}</span>
        @if($room->status === 'live')
            <span class="comp-play-badge">Kalan Süre: <span id="compPlayCountdown">--</span> sn</span>
            <span class="comp-play-badge">Sıralaman: <span id="compPlayRank">-</span>/<span id="compPlayTotal">-</span></span>
            <span class="comp-play-badge">XP: <span id="compPlayXp">0</span></span>
        @endif
    </div>
</div>

@if($room->status === 'live')
    <div class="comp-play-timer-bar"><div class="comp-play-timer-fill" id="compPlayTimerFill"></div></div>
@endif

@if($room->status === 'lobby')
    <div class="comp-wait-stage">
        <div class="comp-wait-box">
            <h3 class="comp-wait-title">Öğretmen Başlatmasını Bekliyorsun</h3>
            <p>Katılım kodu ile lobiye girdin. Öğretmen "Herkese Başlat" dediği an yarışma herkes için aynı anda başlayacak.</p>
            <div class="comp-wait-count" id="compLobbyCount">-</div>
            <p style="color:#64748b;font-size:13px">Lobide bekleyen öğrenci sayısı</p>
        </div>
    </div>
@elseif($room->status === 'live')
    @php($alreadyFinished = $participant->finished_at_ms !== null)
    <div class="comp-play-frame-wrap" id="compPlayFrameWrap" style="{{ $alreadyFinished ? 'display:none;' : '' }}">
        <iframe id="compPlayFrame" src="{{ $iframeSrc }}" title="{{ $gameName }}" allow="fullscreen; autoplay" allowfullscreen></iframe>
    </div>
    <div class="comp-wait-stage" id="compPlayDoneStage" style="{{ $alreadyFinished ? '' : 'display:none;' }}">
        <div class="comp-wait-box">
            <h3 class="comp-wait-title">Bölümünü Tamamladın! 🎉</h3>
            <p>Verilen seviye aralığını bitirdin. Diğer öğrenciler devam ederken süre dolmasını bekle, sıralama canlı olarak güncellenmeye devam ediyor.</p>
            <div class="comp-wait-count" id="compPlayDoneCountdown">-</div>
            <p style="color:#64748b;font-size:13px">saniye kaldı</p>
        </div>
    </div>
@else
    <div class="comp-wait-stage">
        <div class="comp-wait-box">
            <h3 class="comp-wait-title">Yarışma Sona Erdi</h3>
            <p>Sonuçlar öğretmen ekranında görüntüleniyor.</p>
            <a class="btn" href="{{ route('student.portal.dashboard') }}">Anasayfaya Dön</a>
        </div>
    </div>
@endif

@push('scripts')
<script>
(() => {
    const status = @json($room->status);
    const statusUrl = @json(route('student.competitions.status', $room));
    const progressUrl = @json(route('student.competitions.progress', $room));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // --- Lobi bekleme: ogretmen baslatinca otomatik yenile ---------------
    if (status === 'lobby') {
        const lobbyCountEl = document.getElementById('compLobbyCount');
        const pollLobby = async () => {
            try {
                const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (lobbyCountEl) lobbyCountEl.textContent = String(data.joined ?? 0);
                if (data.status === 'live' || data.status === 'finished') {
                    window.location.reload();
                }
            } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
        };
        pollLobby();
        setInterval(pollLobby, 1500);
        return;
    }

    if (status !== 'live') return;

    // --- Canli: sunucu saatiyle senkron sayac + kendi siralamam ----------
    const totalLevels = Math.max(1, {{ (int) ($room->level_to - $room->level_from + 1) }});
    const durationMs = {{ (int) $room->duration_seconds * 1000 }};
    let endsAtMs = {{ (int) ($room->ends_at_ms ?? 0) }};
    let clockOffsetMs = 0;
    let myFinished = @json($participant->finished_at_ms !== null);
    const countdownEl = document.getElementById('compPlayCountdown');
    const rankEl = document.getElementById('compPlayRank');
    const totalEl = document.getElementById('compPlayTotal');
    const xpEl = document.getElementById('compPlayXp');
    const timerFillEl = document.getElementById('compPlayTimerFill');
    const doneCountdownEl = document.getElementById('compPlayDoneCountdown');

    const tick = () => {
        const nowMs = Date.now() + clockOffsetMs;
        const leftMs = Math.max(0, endsAtMs - nowMs);
        const leftSec = Math.ceil(leftMs / 1000);
        if (countdownEl) countdownEl.textContent = String(leftSec);
        if (doneCountdownEl) doneCountdownEl.textContent = String(leftSec);
        if (timerFillEl) {
            const pct = durationMs > 0 ? Math.max(0, Math.min(100, (leftMs / durationMs) * 100)) : 0;
            timerFillEl.style.width = pct + '%';
            timerFillEl.classList.toggle('warn', leftSec <= 15);
        }
        if (leftMs <= 0) {
            window.location.reload();
        }
    };
    setInterval(tick, 500);
    tick();

    const pollStatus = async () => {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            clockOffsetMs = data.server_now_ms - Date.now();
            endsAtMs = data.ends_at_ms || endsAtMs;
            if (rankEl) rankEl.textContent = String(data.my_rank || '-');
            if (totalEl) totalEl.textContent = String(data.total || '-');
            if (data.status === 'finished') {
                window.location.reload();
            }
        } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
    };
    pollStatus();
    setInterval(pollStatus, 2000);

    // --- Oyun iframe'inden gelen ilerleme olaylarini sunucuya ilet -------
    // Oyunlar kendi ilerlemelerini "GAME_UPDATE" / "LEVEL_COMPLETED" /
    // "ASSIGNMENT_RANGE_COMPLETED" tipinde postMessage ile window.parent'a
    // yayinliyor (tum runner oyunlarinda ortak, zaten var olan bir
    // mekanizma). Ama oyunlar arasinda bu mesajlarin icerigi tutarli degil:
    // bazilari "percent"/"progressPercent" gondermiyor, bazilari "xp" alanini
    // TEK bir bolumun odulu olarak gonderiyor (toplam degil). Bu yuzden
    // ilerleme yuzdesini ve toplam XP'yi burada, oyunun ic mantigina
    // guvenmeden kendimiz hesapliyoruz:
    //  - Yuzde: currentLevelIndex / verilen toplam seviye sayisi (her oyun
    //    bu alani tutarli sekilde gonderiyor).
    //  - XP: her LEVEL_COMPLETED olayinda gelen levelId'yi bir kez sayarak
    //    (ayni seviye tekrar oynanirsa ikinci kez eklenmesin diye) kendi
    //    biriktirdigimiz toplam; ASSIGNMENT_RANGE_COMPLETED geldiginde ise
    //    oyunun kendi hesapladigi GERCEK kumulatif toplamla senkronluyoruz.
    let lastSentAt = 0;
    let lastSentPct = -1;
    let accumulatedXp = 0;
    const countedLevelIds = new Set();

    const computePercent = (payload) => {
        const explicit = payload.progressPercent ?? payload.percent;
        if (explicit !== undefined && explicit !== null && Number.isFinite(Number(explicit))) {
            return Number(explicit);
        }
        const idx = Number(payload.currentLevelIndex ?? 0);
        return Math.round(((idx + 1) / totalLevels) * 100);
    };

    const sendProgress = (payload, force) => {
        const now = Date.now();
        const pct = Math.max(0, Math.min(100, computePercent(payload)));
        if (!force && now - lastSentAt < 1500 && Math.abs(pct - lastSentPct) < 1) return;
        lastSentAt = now;
        lastSentPct = pct;
        if (xpEl) xpEl.textContent = String(accumulatedXp);
        fetch(progressUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                progress_percent: pct,
                current_level_index: Number(payload.currentLevelIndex ?? 0),
                xp: accumulatedXp,
            }),
        }).catch(() => {});
    };

    const showDoneStage = () => {
        if (myFinished) return;
        myFinished = true;
        const wrap = document.getElementById('compPlayFrameWrap');
        const stage = document.getElementById('compPlayDoneStage');
        if (wrap) wrap.style.display = 'none';
        if (stage) stage.style.display = '';
    };

    window.addEventListener('message', (ev) => {
        const data = ev.data;
        if (!data || typeof data !== 'object') return;
        if (data.type === 'GAME_UPDATE') {
            sendProgress(data, false);
        } else if (data.type === 'LEVEL_COMPLETED') {
            const levelId = data.levelId ?? data.currentLevelIndex;
            if (levelId !== undefined && levelId !== null && !countedLevelIds.has(levelId)) {
                countedLevelIds.add(levelId);
                accumulatedXp += Math.max(0, Number(data.xp ?? 0));
            }
            sendProgress(data, true);
        } else if (data.type === 'ASSIGNMENT_RANGE_COMPLETED') {
            // Oyunun kendi hesapladigi kumulatif toplam ile senkronla (en guvenilir kaynak).
            if (Number.isFinite(Number(data.xp))) accumulatedXp = Math.max(accumulatedXp, Number(data.xp));
            if (xpEl) xpEl.textContent = String(accumulatedXp);
            sendProgress({ ...data, progressPercent: 100 }, true);
            showDoneStage();
        }
    });

    // Iframe boyutlandirma
    const frame = document.getElementById('compPlayFrame');
    if (frame) {
        const resize = () => {
            const top = frame.getBoundingClientRect().top;
            const footer = document.querySelector('.footer');
            const footerH = footer ? footer.offsetHeight + 20 : 20;
            frame.style.height = Math.max(420, window.innerHeight - top - footerH) + 'px';
        };
        resize();
        window.addEventListener('resize', resize);
    }
})();
</script>
@endpush
@endsection
