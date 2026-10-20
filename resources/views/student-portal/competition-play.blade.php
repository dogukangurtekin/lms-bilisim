@extends('layout.app')
@section('title','Canlı Yarışma')
@section('content')
@include('competitions._theme')

<div class="cx">
<div class="comp-play-bar">
    <div class="cx-hero-icon">🎮</div>
    <div>
        <h1>{{ $gameName }}</h1>
        <p style="margin:2px 0 0;color:rgba(255,255,255,.85);font-size:13px">Canlı Yarışma · Kod <b class="code">{{ $room->join_code }}</b></p>
    </div>
    <div class="comp-play-badges">
        @if($room->status === 'live')
            <span class="comp-play-badge">⏱ <span id="compPlayCountdown">--</span> sn</span>
            <span class="comp-play-badge">🏅 <span id="compPlayRank">-</span>/<span id="compPlayTotal">-</span></span>
            <span class="comp-play-badge">⭐ <span id="compPlayXp">0</span> XP</span>
        @elseif($room->status === 'lobby')
            <span class="cx-chip lobby">⏳ Lobide</span>
        @else
            <span class="cx-chip done">🏁 Bitti</span>
        @endif
    </div>
</div>

@if($room->status === 'live')
    <div class="comp-play-timer-bar"><div class="comp-play-timer-fill" id="compPlayTimerFill"></div></div>
@endif

@if($room->status === 'lobby')
    <div class="comp-wait-stage">
        <div class="comp-wait-box">
            <div class="cx-dots"><i></i><i></i><i></i><i></i><i></i></div>
            <h3 class="comp-wait-title">Öğretmen Başlatmasını Bekliyorsun</h3>
            <p>Lobiye girdin! Öğretmen <b style="color:#fff">"Herkese Başlat"</b> dediği an yarışma herkes için aynı anda başlayacak. Hazır ol, parmaklarını ısıt! 🔥</p>
            <div class="comp-wait-count" id="compLobbyCount">-</div>
            <p style="font-size:13px;margin:0">lobide bekleyen öğrenci</p>
            <div class="comp-joined-grid" id="compLobbyStudents"></div>
        </div>
    </div>
@elseif($room->status === 'live')
    @php($alreadyFinished = $participant->finished_at_ms !== null)
    <div class="comp-play-frame-wrap" id="compPlayFrameWrap" style="{{ $alreadyFinished ? 'display:none;' : '' }}">
        <iframe id="compPlayFrame" src="{{ $iframeSrc }}" title="{{ $gameName }}" allow="fullscreen; autoplay" allowfullscreen></iframe>
    </div>
    <div class="comp-wait-stage" id="compPlayDoneStage" style="{{ $alreadyFinished ? '' : 'display:none;' }}">
        <div class="comp-wait-box">
            <div class="comp-trophy">🏆</div>
            <h3 class="comp-wait-title">Bölümünü Tamamladın!</h3>
            <p>Harika iş çıkardın! Diğer öğrenciler devam ederken süre dolmasını bekle, sıralama canlı olarak güncellenmeye devam ediyor.</p>
            <div class="comp-wait-count" id="compPlayDoneCountdown">-</div>
            <p style="font-size:13px;margin:0">saniye kaldı</p>
        </div>
    </div>
    <canvas id="compConfettiCanvas"></canvas>
@else
    <div class="comp-wait-stage">
        <div class="comp-wait-box">
            <div class="comp-trophy">🏁</div>
            <h3 class="comp-wait-title">Yarışma Sona Erdi</h3>
            <p>Sonuçlar öğretmen ekranında görüntüleniyor.</p>
            <a class="btn btn-primary" href="{{ route('student.competitions.join.form') }}">Yeni Bir Yarışmaya Katıl</a>
        </div>
    </div>
@endif
</div>

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
        const lobbyStudentsEl = document.getElementById('compLobbyStudents');
        const renderStudents = (students) => {
            if (!lobbyStudentsEl) return;
            lobbyStudentsEl.innerHTML = (students || []).map((s) => `
                <div class="comp-joined-tile">
                    ${s.avatar_url
                        ? `<img src="${s.avatar_url}" alt="">`
                        : `<div class="empty">?</div>`}
                    <span title="${s.name || ''}">${s.name || ''}</span>
                </div>
            `).join('');
        };
        const pollLobby = async () => {
            try {
                const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (lobbyCountEl) lobbyCountEl.textContent = String(data.joined ?? 0);
                renderStudents(data.students);
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
    //  - Yuzde: bu yarisma oturumunda tamamlanan benzersiz seviye sayisi.
    //  - XP: her LEVEL_COMPLETED olayinda gelen levelId'yi bir kez sayarak
    //    (ayni seviye tekrar oynanirsa ikinci kez eklenmesin diye) kendi
    //    biriktirdigimiz toplam; ASSIGNMENT_RANGE_COMPLETED geldiginde ise
    //    oyunun kendi hesapladigi GERCEK kumulatif toplamla senkronluyoruz.
    let lastSentAt = 0;
    let lastSentPct = -1;
    let accumulatedXp = 0;
    const countedLevelIds = new Set();   // ilerleme yüzdesi için (oyunun bildirdiği sıra numaralarıyla)
    const xpCountedIds = new Set();      // XP'nin tekrar eklenmemesi için (levelId ile)

    const computePercent = () => {
        const completedLevels = Math.min(totalLevels, countedLevelIds.size);
        return Math.round((completedLevels / totalLevels) * 100);
    };

    const syncCompletedLevels = (payload) => {
        const completedLevelIds = Array.isArray(payload?.completedLevelIds)
            ? payload.completedLevelIds
            : [];
        completedLevelIds.forEach((levelId) => {
            if (levelId !== undefined && levelId !== null) countedLevelIds.add(String(levelId));
        });
        accumulatedXp = Math.max(
            accumulatedXp,
            Math.max(0, Number(payload?.xpEarned ?? payload?.xp_earned ?? payload?.xp ?? 0))
        );
    };

    const sendProgress = (payload, force, rangeCompleted = false) => {
        const now = Date.now();
        syncCompletedLevels(payload);
        const computedPct = computePercent();
        const completionReported = rangeCompleted || computedPct >= 100;
        const pct = completionReported ? 100 : Math.max(0, Math.min(99, computedPct));
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

    // --- Basit konfeti patlamasi (harici kutuphane yok, hafif canvas) ----
    function burstConfetti() {
        const canvas = document.getElementById('compConfettiCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const resize = () => {
            canvas.width = window.innerWidth * dpr;
            canvas.height = window.innerHeight * dpr;
            canvas.style.width = window.innerWidth + 'px';
            canvas.style.height = window.innerHeight + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        };
        resize();
        const colors = ['#4f46e5', '#22c55e', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899'];
        const pieces = Array.from({ length: 140 }, () => ({
            x: Math.random() * window.innerWidth,
            y: -20 - Math.random() * window.innerHeight * 0.4,
            w: 6 + Math.random() * 6,
            h: 8 + Math.random() * 10,
            color: colors[Math.floor(Math.random() * colors.length)],
            speedY: 2 + Math.random() * 3.5,
            speedX: -1.5 + Math.random() * 3,
            rotation: Math.random() * 360,
            spin: -8 + Math.random() * 16,
        }));
        const startedAt = Date.now();
        const durationMs = 2600;
        let rafId = null;
        const step = () => {
            const elapsed = Date.now() - startedAt;
            ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
            pieces.forEach((p) => {
                p.x += p.speedX;
                p.y += p.speedY;
                p.rotation += p.spin;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate((p.rotation * Math.PI) / 180);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = Math.max(0, 1 - elapsed / durationMs);
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                ctx.restore();
            });
            if (elapsed < durationMs) {
                rafId = requestAnimationFrame(step);
            } else {
                ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
                window.removeEventListener('resize', resize);
            }
        };
        window.addEventListener('resize', resize);
        step();
    }

    const showDoneStage = () => {
        if (myFinished) return;
        myFinished = true;
        const wrap = document.getElementById('compPlayFrameWrap');
        const stage = document.getElementById('compPlayDoneStage');

        // Ani "tak diye" gecis yerine: once oyun ekrani yumusakca solup
        // kuculuyor, ayni anda konfeti patliyor, sonra tebrik ekrani
        // hafif yukselerek/buyuyerek beliriyor.
        burstConfetti();
        if (wrap) {
            wrap.classList.add('comp-celebrate-fadeout');
            setTimeout(() => { wrap.style.display = 'none'; }, 500);
        }
        if (stage) {
            stage.style.display = '';
            stage.classList.add('comp-done-enter');
            requestAnimationFrame(() => {
                stage.classList.add('comp-done-enter-active');
            });
        }
    };

    window.addEventListener('message', (ev) => {
        const data = ev.data;
        if (!data || typeof data !== 'object') return;
        if (data.type === 'GAME_UPDATE') {
            syncCompletedLevels(data);
            sendProgress(data, false);
        } else if (data.type === 'LEVEL_COMPLETED' || data.type === 'LINE_TRACE_LEVEL_COMPLETE') {
            const currentLevelIndex = data.currentLevelIndex ?? (Number.isFinite(Number(data.level)) ? Number(data.level) - 1 : undefined);
            const progressData = { ...data, currentLevelIndex };
            const levelId = data.levelId ?? currentLevelIndex;
            const levelKey = levelId !== undefined && levelId !== null ? String(levelId) : null;
            if (levelKey !== null && !xpCountedIds.has(levelKey)) {
                xpCountedIds.add(levelKey);
                accumulatedXp += Math.max(0, Number(data.xp ?? 0));
            }
            // Yüzde: bazı oyunlar aynı olayda hem mutlak 'levelId' hem de göreli 'completedLevelIds' gönderir.
            // İkisini birlikte saymak aralık 1'den başlamıyorsa her bölümü iki kez sayıp erken %100 gösteriyordu.
            // Oyun tamamlanan bölüm listesi veriyorsa yalnızca onu, vermiyorsa levelId'yi sayıyoruz.
            if (!Array.isArray(data.completedLevelIds) && levelKey !== null) countedLevelIds.add(levelKey);
            sendProgress(progressData, true);
        } else if (data.type === 'ASSIGNMENT_RANGE_COMPLETED') {
            if (xpEl) xpEl.textContent = String(accumulatedXp);
            sendProgress(data, true, true);
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
