@extends('layout.app')

@section('title', 'Klavye Hız Yarışması')
@section('body_class', 'play-compact')

@section('content')
<style>
.race-shell {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #020617 0%, #0f172a 52%, #111827 100%);
    color: #e2e8f0;
    border: 1px solid rgba(56, 189, 248, 0.25);
    transition: background .2s ease, color .2s ease, border-color .2s ease;
}
.race-bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 16% 16%, rgba(6, 182, 212, 0.25), transparent 45%),
        radial-gradient(circle at 84% 8%, rgba(236, 72, 153, 0.2), transparent 38%),
        radial-gradient(circle at 50% 100%, rgba(59, 130, 246, 0.15), transparent 60%);
    pointer-events: none;
}
.race-content { position: relative; z-index: 1; }
.neon-title {
    color: #e2e8f0;
    text-shadow: 0 0 20px rgba(56, 189, 248, 0.35);
}
.race-input {
    width: 100%;
    margin-bottom: 0;
    border: 1px solid rgba(148, 163, 184, 0.4);
    background: rgba(15, 23, 42, 0.72);
    color: #e2e8f0;
}
.race-input:focus {
    outline: none;
    border-color: rgba(56, 189, 248, 0.8);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
}
.race-input::placeholder { color: #94a3b8; }
.race-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 700;
    border: 1px solid rgba(148, 163, 184, 0.45);
    background: linear-gradient(180deg, #1e293b, #0f172a);
}
.race-btn:hover { filter: brightness(1.08); }
.race-btn:disabled {
    cursor: not-allowed;
    opacity: .55;
    filter: grayscale(.25);
}
.race-btn-primary {
    border-color: rgba(14, 165, 233, 0.8);
    background: linear-gradient(180deg, #0ea5e9, #2563eb);
}
.race-meta {
    border-radius: 10px;
    border: 1px solid rgba(56, 189, 248, 0.3);
    background: rgba(15, 23, 42, 0.75);
    padding: 12px;
}
.typing-box {
    border-radius: 10px;
    background: rgba(15, 23, 42, 0.72);
    border: 1px solid rgba(148, 163, 184, 0.25);
    padding: 12px;
}
.countdown-pop { animation: countdown-pop .35s ease; }
.result-fade { animation: result-fade .4s ease; }
.race-theme-toggle{
    border:1px solid rgba(148,163,184,.45);
    background:linear-gradient(180deg,#f8fafc,#e2e8f0);
    color:#0f172a;
    font-weight:800;
}
.race-shell[data-theme="light"]{
    background:linear-gradient(160deg,#f8fafc 0%, #eef2ff 52%, #e2e8f0 100%);
    color:#0f172a;
    border:1px solid rgba(148, 163, 184, 0.45);
}
.race-shell[data-theme="light"] .neon-title{
    color:#0f172a;
    text-shadow:none;
}
.race-shell[data-theme="light"] .race-input{
    background:#ffffff;
    color:#0f172a;
    border-color:#cbd5e1;
}
.race-shell[data-theme="light"] .race-input::placeholder{color:#64748b}
.race-shell[data-theme="light"] .race-btn{
    background:linear-gradient(180deg,#ffffff,#f1f5f9);
    color:#0f172a;
}
.race-shell[data-theme="light"] .race-meta,
.race-shell[data-theme="light"] .typing-box{
    background:rgba(255,255,255,.85);
    border-color:#cbd5e1;
}
@keyframes countdown-pop {
    0% { transform: scale(.72); opacity: .35; }
    100% { transform: scale(1); opacity: 1; }
}
@keyframes result-fade {
    0% { opacity: 0; transform: translateY(6px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>

<div class="top">
    <h1>Klavye Hız Yarışması</h1>
    <div style="display:flex;gap:8px;align-items:center">
        <button id="themeToggle" class="btn race-theme-toggle">Karanlık Moda Geç</button>
        <a class="btn" href="{{ route('activities.index') }}">Etkinliklere Dön</a>
    </div>
</div>

<div id="raceShell" class="card race-shell" data-theme="light">
    <div class="race-bg"></div>
    <div class="race-content">
        <h2 class="neon-title" style="margin:0;font-size:34px;font-weight:900">Klavye Hız Yarışması</h2>
        <p style="margin:8px 0 0;color:inherit;opacity:.85">Gerçek zamanlı yarış: oda oluştur, katıl, yaz ve zirveye çık.</p>

        <div style="margin-top:18px;display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));align-items:center">
            <input id="userName" type="text" placeholder="Kullanıcı adın" class="race-input" />
            <input id="roomCode" type="text" placeholder="Oda kodu" class="race-input" />
            <div id="roomJoinActions" style="display:flex;gap:8px;flex-wrap:wrap">
                <button id="createRoomBtn" class="btn race-btn race-btn-primary" style="flex:1">Oda Oluştur</button>
                <button id="joinRoomBtn" class="btn race-btn" style="flex:1">Katıl</button>
            </div>
        </div>

        <div id="teacherTextConfig" class="race-meta" style="margin-top:12px;display:none">
            <h3 style="margin:0 0 8px;font-size:16px">Yarış Metni Ayarları</h3>
            <div style="display:grid;gap:8px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));align-items:start">
                <div>
                    <label for="textTemplate" style="display:block;font-size:13px;margin-bottom:6px;color:#cbd5e1">Hazır şablon</label>
                    <select id="textTemplate" class="race-input" style="height:42px"></select>
                </div>
                <div style="grid-column:1 / -1">
                    <label for="customRaceText" style="display:block;font-size:13px;margin-bottom:6px;color:#cbd5e1">Kendi metnin (opsiyonel)</label>
                    <textarea id="customRaceText" rows="4" class="race-input" placeholder="Metni buraya yaz. Boş bırakırsan seçtiğin şablon kullanılır."></textarea>
                </div>
            </div>
        </div>

        <div id="roomMeta" class="race-meta" style="margin-top:12px;display:none"></div>

        <div id="teacherRaceActions" style="margin-top:12px;display:flex;flex-wrap:wrap;align-items:center;gap:8px">
            <button id="startRaceBtn" class="btn race-btn race-btn-primary" disabled>Yarışı Başlat</button>
            <button id="endRaceBtn" class="btn race-btn" disabled>Yarışı Bitir ve Rapor Al</button>
            <div id="countdown" style="font-size:38px;font-weight:900;color:#67e8f9"></div>
            <div id="raceTimer" style="font-size:22px;font-weight:900;color:#fde68a"></div>
            <div id="statusText" style="font-size:13px;color:#cbd5e1"></div>
        </div>

        <div style="margin-top:16px">
            <div id="typingText" class="typing-box" style="line-height:1.8;font-size:18px"></div>
            <textarea id="typingInput" rows="3" class="race-input" style="margin-top:8px" placeholder="Yarış başlayınca buraya yaz..." disabled></textarea>
        </div>

        <div style="margin-top:14px">
            <div style="margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;font-size:13px">
                <span>İlerleme Durumu</span>
                <span id="selfStats">Tamamlanma: 0% | Hız: 0 kelime/dk | Doğruluk: 100%</span>
            </div>
            <div id="selfSeconds" style="font-size:12px;opacity:.9;margin-bottom:6px">Geçen Süre: 0 sn | Kalan: 0 sn</div>
            <div style="height:10px;width:100%;overflow:hidden;border-radius:999px;background:#1e293b">
                <div id="selfBar" style="height:100%;width:0;background:linear-gradient(90deg,#22d3ee,#d946ef);transition:width .25s"></div>
            </div>
        </div>

        <div style="margin-top:12px;display:grid;gap:8px;grid-template-columns:1fr;max-width:520px">
            <div style="display:flex;justify-content:space-between;padding:8px 10px;border-radius:8px;background:rgba(255,255,255,.08)">
                <span>Toplam Yazılan Kelime</span><b id="metricTotalWords">0</b>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 10px;border-radius:8px;background:rgba(255,255,255,.08)">
                <span>Doğru Yazılan Kelime</span><b id="metricCorrectWords" style="color:#22c55e">0</b>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 10px;border-radius:8px;background:rgba(255,255,255,.08)">
                <span>Yanlış Yazılan Kelime</span><b id="metricWrongWords" style="color:#ef4444">0</b>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 10px;border-radius:8px;background:rgba(255,255,255,.08)">
                <span>Hata Oranı</span><b id="metricErrorRate">% 0,00</b>
            </div>
        </div>

        <div style="margin-top:14px">
            <h3 style="margin:0;font-size:20px">Canlı Rakipler</h3>
            <div id="opponents" style="margin-top:8px;display:grid;gap:8px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))"></div>
        </div>

        <div id="leaderboardWrap" style="margin-top:16px;display:none">
            <h3 style="margin:0;font-size:24px;font-weight:900">Sonuçlar</h3>
            <div id="winnerText" style="margin-top:6px;font-size:24px;font-weight:900;color:#6ee7b7"></div>
            <div id="leaderboard" style="margin-top:8px;display:grid;gap:8px"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
(() => {
    const AUTH_USER_ID = {{ (int) (auth()->id() ?? 0) }};
    const AUTH_USER_NAME = @json((string) (auth()->user()?->name ?? ''));
    const AUTH_ACTOR_ROLE = @json(auth()->user()?->hasRole('admin', 'teacher') ? 'teacher' : 'student');

    const API_BASE_CANDIDATES = [
        '{{ url('api/race') }}',
        '{{ url('index.php/api/race') }}',
        '{{ url('public/api/race') }}',
    ];
    let activeApiBase = API_BASE_CANDIDATES[0];
    const SOCKET_URL = '{{ env('SOCKET_SERVER_URL', 'http://localhost:3001') }}';
    const params = new URLSearchParams(window.location.search);
    const actorRole = AUTH_ACTOR_ROLE === 'teacher' ? 'teacher' : 'student';
    const actorUserId = AUTH_USER_ID > 0 ? AUTH_USER_ID : null;

    const state = {
        socket: null,
        socketAvailable: true,
        roomCode: '',
        roomText: '',
        userName: '',
        startedAtMs: null,
        startHandled: false,
        raceDurationSeconds: 120,
        raceEndsAtMs: null,
        raceTimerInterval: null,
        isSpectator: false,
        myProgress: 0,
        opponents: new Map(),
        finished: false,
        roomPollTimer: null,
    };

    const el = {
        userName: document.getElementById('userName'),
        roomCode: document.getElementById('roomCode'),
        createRoomBtn: document.getElementById('createRoomBtn'),
        joinRoomBtn: document.getElementById('joinRoomBtn'),
        roomJoinActions: document.getElementById('roomJoinActions'),
        teacherTextConfig: document.getElementById('teacherTextConfig'),
        textTemplate: document.getElementById('textTemplate'),
        customRaceText: document.getElementById('customRaceText'),
        teacherRaceActions: document.getElementById('teacherRaceActions'),
        startRaceBtn: document.getElementById('startRaceBtn'),
        endRaceBtn: document.getElementById('endRaceBtn'),
        countdown: document.getElementById('countdown'),
        raceTimer: document.getElementById('raceTimer'),
        statusText: document.getElementById('statusText'),
        typingText: document.getElementById('typingText'),
        typingInput: document.getElementById('typingInput'),
        selfStats: document.getElementById('selfStats'),
        selfBar: document.getElementById('selfBar'),
        metricTotalWords: document.getElementById('metricTotalWords'),
        metricCorrectWords: document.getElementById('metricCorrectWords'),
        metricWrongWords: document.getElementById('metricWrongWords'),
        metricErrorRate: document.getElementById('metricErrorRate'),
        opponents: document.getElementById('opponents'),
        leaderboardWrap: document.getElementById('leaderboardWrap'),
        leaderboard: document.getElementById('leaderboard'),
        winnerText: document.getElementById('winnerText'),
        roomMeta: document.getElementById('roomMeta'),
        themeToggle: document.getElementById('themeToggle'),
        raceShell: document.getElementById('raceShell'),
        selfSeconds: document.getElementById('selfSeconds'),
    };

    const raceTextTemplates = [
        'kod yazarken once problemi parcalara ayir sonra her adimi dikkatle uygula ve sonucu test etmeyi unutma',
        'robotik calismalarda sabirli olmak onemlidir cunku kucuk hatalari bulmak buyuk basarilarin kapisini acar',
        'yazilim gelistirirken okunabilir kod yazmak ekip calismasini kolaylastirir ve bakim suresini kisaltir',
        'dogru algoritma secimi ayni isi daha kisa surede yapmayi saglar ve bilgisayar kaynaklarini verimli kullanir',
        'her gun duzenli pratik yapmak klavye hizini artirir dogrulugu yukseltir ve uretkenligi gorulur bicimde gelistirir',
    ];
    const raceSample = raceTextTemplates[0];

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function authHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        };
    }

    function sanitizeRaceText(text) {
        return String(text || '')
            .toLocaleLowerCase('tr-TR')
            .replace(/[^a-zçğıöşü\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function sanitizeTypingInput(text) {
        return String(text || '')
            .toLocaleLowerCase('tr-TR')
            .replace(/[^a-zçğıöşü\s]/g, ' ')
            .replace(/\s+/g, ' ');
    }

    function normalizeAndSanitizeRaceText(text) {
        return sanitizeRaceText(text);
    }

    function getSelectedRaceText() {
        const custom = normalizeAndSanitizeRaceText(el.customRaceText?.value || '');
        if (custom.length >= 30) return custom;
        return normalizeAndSanitizeRaceText(el.textTemplate?.value || raceSample);
    }

    function initializeTeacherTextConfig() {
        if (!el.textTemplate) return;
        el.textTemplate.innerHTML = raceTextTemplates
            .map((text, index) => `<option value="${escapeHtml(text)}">Şablon ${index + 1}</option>`)
            .join('');
        el.textTemplate.value = raceSample;
    }

    async function api(path, options = {}) {
        let lastError = null;
        for (const base of API_BASE_CANDIDATES) {
            const res = await fetch(`${base}${path}`, {
                ...options,
                headers: {
                    ...authHeaders(),
                    ...(options.headers || {}),
                },
            });

            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                activeApiBase = base;
                return data;
            }

            if (res.status !== 404 && res.status < 500) {
                throw new Error(data.message || 'API istegi basarisiz.');
            }

            lastError = data.message || `API istegi basarisiz. (${res.status})`;
        }
        throw new Error(lastError || 'API istegi basarisiz.');
    }

    function connectSocket() {
        if (state.socket || !state.socketAvailable) return;

        state.socket = io(SOCKET_URL, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: 20,
            reconnectionDelay: 500,
            reconnectionDelayMax: 5000,
        });

        state.socket.on('connect_error', () => {
            state.socketAvailable = false;
            try { state.socket?.close(); } catch (_) {}
            state.socket = null;
            setStatus('Canli baglanti sunucusu kapali. Polling modunda devam ediliyor.');
        });

        state.socket.on('connect', () => {
            if (state.roomCode && state.userName) {
                state.socket.emit('join_room', {
                    roomCode: state.roomCode,
                    userName: state.userName,
                });
            }
        });

        state.socket.on('room_joined', (payload) => {
            if (typeof payload.spectator === 'boolean') {
                state.isSpectator = payload.spectator;
                if (state.isSpectator) {
                    setStatus('Yaris baslamis. Spectator modundasin.');
                    el.typingInput.disabled = true;
                }
            }
        });

        state.socket.on('room_presence', (payload) => {
            if (!payload?.userName || payload.userName === state.userName) return;
            upsertOpponent(payload.userName, payload.progress || 0, payload.wpm || 0, payload.accuracy || 100);
        });

        state.socket.on('race_started', async (payload) => {
            await handleRaceStarted({
                text: payload?.text || state.roomText,
                durationSeconds: Number(payload?.durationSeconds || state.raceDurationSeconds || 120),
                endsAt: payload?.endsAt || null,
            });
        });

        state.socket.on('typing_progress', (payload) => {
            if (!payload?.userName || payload.userName === state.userName) return;
            upsertOpponent(payload.userName, payload.progress, payload.wpm, payload.accuracy);
        });

        state.socket.on('race_finished', (payload) => {
            if (Array.isArray(payload?.leaderboard)) {
                renderLeaderboard(payload.leaderboard);
            }
        });
    }

    function startRoomPolling() {
        if (state.roomPollTimer || !state.roomCode) return;
        state.roomPollTimer = setInterval(syncRoomSnapshot, 2000);
        syncRoomSnapshot();
    }

    function stopRoomPolling() {
        if (!state.roomPollTimer) return;
        clearInterval(state.roomPollTimer);
        state.roomPollTimer = null;
    }

    function stopRaceTimer() {
        if (state.raceTimerInterval) {
            clearInterval(state.raceTimerInterval);
            state.raceTimerInterval = null;
        }
    }

    function updateRaceTimerLabel() {
        if (!state.startedAtMs) {
            el.raceTimer.textContent = '';
            if (el.selfSeconds) el.selfSeconds.textContent = 'Geçen Süre: 0 sn | Kalan: 0 sn';
            return;
        }
        const elapsed = Math.max(0, Math.floor((Date.now() - state.startedAtMs) / 1000));
        const remaining = state.raceEndsAtMs ? Math.max(0, Math.floor((state.raceEndsAtMs - Date.now()) / 1000)) : 0;
        el.raceTimer.textContent = `Kalan: ${remaining}s | Suren: ${elapsed}s`;
        if (el.selfSeconds) el.selfSeconds.textContent = `Geçen Süre: ${elapsed} sn | Kalan: ${remaining} sn`;
    }

    function startRaceTimer() {
        stopRaceTimer();
        updateRaceTimerLabel();
        state.raceTimerInterval = setInterval(() => {
            updateRaceTimerLabel();
            if (state.raceEndsAtMs && Date.now() >= state.raceEndsAtMs) {
                stopRaceTimer();
                if (!state.finished && !state.isSpectator) {
                    const input = el.typingInput.value;
                    const stats = computeStats(input);
                    finishRace(stats).catch((err) => setStatus(err.message));
                }
            }
        }, 500);
    }

    function renderOpponentsFromResults(results = []) {
        state.opponents.clear();
        for (const row of results) {
            const name = String(row?.user_name || row?.userName || '').trim();
            if (!name || name === state.userName || row?.is_spectator) continue;
            upsertOpponent(name, Number(row?.progress || 0), Number(row?.wpm || 0), Number(row?.accuracy || 100));
        }
    }

    function renderRoomMeta(roomCode, status) {
        el.roomMeta.style.display = 'block';
        el.roomMeta.innerHTML = `<b>Oda:</b> ${roomCode} | <b>Durum:</b> ${status}`;
    }

    function setStatus(message) {
        el.statusText.textContent = message;
    }

    async function syncRoomSnapshot() {
        if (!state.roomCode) return;
        try {
            const data = await api(`/rooms/${state.roomCode}`);
            if (!data?.room) return;
            const room = data.room;
            if (room.text) {
                state.roomText = room.text;
                renderTypingText(el.typingInput.value, state.roomText);
            }
            renderRoomMeta(room.code || state.roomCode, room.status || '-');
            const liveResults = room.race_results || room.raceResults || data.results || [];
            renderOpponentsFromResults(liveResults);
            if (room.status === 'active' && !state.startHandled) {
                await handleRaceStarted({
                    text: room.text || state.roomText,
                    durationSeconds: Number(room.duration_seconds || state.raceDurationSeconds || 120),
                    endsAt: room.ends_at || null,
                    skipCountdown: true,
                });
            }
            const snapshotResults = room.race_results || room.raceResults || data.results || [];
            if (room.status === 'finished' && Array.isArray(snapshotResults) && !state.finished) {
                state.finished = true;
                el.typingInput.disabled = true;
                stopRaceTimer();
                stopRoomPolling();
                const leaderboard = snapshotResults
                    .map((row) => ({
                        userName: row.user_name,
                        progress: Number(row.progress || 0),
                        wpm: Number(row.wpm || 0),
                        accuracy: Number(row.accuracy || 0),
                    }))
                    .sort((a, b) => (b.progress - a.progress) || (b.wpm - a.wpm) || (b.accuracy - a.accuracy));
                renderLeaderboard(leaderboard);
            }
        } catch (error) {
            setStatus(error.message);
        }
    }

    async function handleRaceStarted(payload) {
        if (state.startHandled) return;
        state.startHandled = true;
        state.finished = false;
        state.roomText = payload.text || state.roomText || raceSample;
        state.raceDurationSeconds = Number(payload.durationSeconds || state.raceDurationSeconds || 120);
        state.raceEndsAtMs = payload.endsAt ? new Date(payload.endsAt).getTime() : (Date.now() + (state.raceDurationSeconds * 1000));
        state.startedAtMs = state.raceEndsAtMs - (state.raceDurationSeconds * 1000);

        if (!payload.skipCountdown) {
            await startCountdown();
        }

        renderTypingText('', state.roomText);
        el.typingInput.value = '';
        el.typingInput.disabled = state.isSpectator;
        if (!state.isSpectator) {
            el.typingInput.focus();
        }

        el.startRaceBtn.disabled = true;
        el.endRaceBtn.disabled = actorRole !== 'teacher';
        setStatus('Yaris basladi. Yazmaya baslayin.');
        startRaceTimer();
    }

    async function createRoom() {
        const userName = el.userName.value.trim();
        if (!userName) return setStatus('Ogretmen ismi gerekli.');
        const selectedText = getSelectedRaceText();
        if (selectedText.length < 30) return setStatus('Yaris metni en az 30 karakter olmali.');

        state.userName = userName;

        const data = await api('/rooms', {
            method: 'POST',
            body: JSON.stringify({
                name: `${userName} Odasi`,
                text: selectedText,
                user_name: userName,
                user_id: actorUserId,
                actor_role: actorRole,
            }),
        });

        state.roomCode = data.room.code;
        state.roomText = data.room.text;
        state.startHandled = false;
        state.finished = false;
        renderRoomMeta(state.roomCode, data.room.status || 'waiting');
        renderTypingText('', state.roomText);
        el.roomCode.value = state.roomCode;

        connectSocket();
        state.socket?.emit('join_room', { roomCode: state.roomCode, userName });
        startRoomPolling();

        el.startRaceBtn.disabled = false;
        el.endRaceBtn.disabled = true;
        setStatus('Oda olusturuldu. Yaris baslatilabilir.');
    }

    async function joinRoom() {
        const userName = el.userName.value.trim();
        const roomCode = el.roomCode.value.trim().toUpperCase();
        if (!userName || !roomCode) return setStatus('Kullanici adi ve oda kodu gerekli.');

        state.userName = userName;
        state.roomCode = roomCode;
        state.startHandled = false;

        const data = await api(`/rooms/${roomCode}/join`, {
            method: 'POST',
            body: JSON.stringify({ user_name: userName, user_id: actorUserId, actor_role: actorRole }),
        });

        state.roomText = data.race_text;
        state.isSpectator = !!data.user.spectator;
        renderRoomMeta(roomCode, data.status);
        renderTypingText('', state.roomText);
        connectSocket();
        state.socket?.emit('join_room', { roomCode, userName });
        startRoomPolling();
        syncRoomSnapshot();
        el.startRaceBtn.disabled = actorRole !== 'teacher' || data.status !== 'waiting';
        setStatus(state.isSpectator ? 'Yarisa izleyici olarak katildin.' : 'Odaya katildin.');
    }

    async function startRace() {
        if (!state.roomCode) return;
        await api(`/rooms/${state.roomCode}/start`, {
            method: 'POST',
            body: JSON.stringify({ user_id: actorUserId }),
        });
        syncRoomSnapshot();
        setStatus('Yaris tetiklendi. Geri sayim bekleniyor...');
    }

    function buildRaceReportHtml(report) {
        const rows = Array.isArray(report?.participants) ? report.participants : [];
        const list = rows.map((r, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td>${r.userName || '-'}</td>
                <td>${Number(r.progress || 0).toFixed(1)}%</td>
                <td>${Number(r.wpm || 0).toFixed(1)}</td>
                <td>${Number(r.accuracy || 0).toFixed(1)}%</td>
                <td>${r.completionSeconds ?? '-'}</td>
                <td>${r.elapsedSeconds ?? 0}</td>
                <td>${r.xpEarned ?? 0}</td>
            </tr>
        `).join('');
        return `
<!doctype html>
<html lang="tr"><head><meta charset="UTF-8"><title>Klavye Yarisi Raporu</title>
<style>body{font-family:Arial,sans-serif;padding:16px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px;font-size:13px}th{background:#f3f4f6;text-align:left}</style>
</head><body>
<h2>Klavye Yarisi Raporu</h2>
<p><b>Oda:</b> ${report?.roomCode || '-'} | <b>Ad:</b> ${report?.roomName || '-'}</p>
<p><b>Baslangic:</b> ${report?.startedAt || '-'} | <b>Bitis:</b> ${report?.finishedAt || '-'} | <b>Toplam Sure:</b> ${report?.durationSeconds ?? '-'} sn</p>
<table><thead><tr><th>#</th><th>Ogrenci</th><th>Ilerleme</th><th>WPM</th><th>Dogruluk</th><th>Bitiris (sn)</th><th>Gecen Sure (sn)</th><th>XP</th></tr></thead><tbody>${list}</tbody></table>
<script>window.onload=()=>window.print();<\/script>
</body></html>`;
    }

    async function endRaceAndDownloadReport() {
        if (!state.roomCode) return;
        const data = await api(`/rooms/${state.roomCode}/end`, {
            method: 'POST',
            body: JSON.stringify({ user_id: actorUserId }),
        });
        stopRaceTimer();
        el.typingInput.disabled = true;
        if (Array.isArray(data?.leaderboard)) renderLeaderboard(data.leaderboard);
        const report = data?.report || null;
        if (report) {
            const win = window.open('', '_blank', 'noopener,noreferrer,width=1100,height=800');
            if (win) {
                win.document.open();
                win.document.write(buildRaceReportHtml(report));
                win.document.close();
            }
        }
        setStatus('Yaris ogretmen tarafindan sonlandirildi.');
    }

    async function startCountdown() {
        const seq = ['3', '2', '1', 'Basla!'];
        for (const tick of seq) {
            el.countdown.textContent = tick;
            el.countdown.classList.remove('countdown-pop');
            void el.countdown.offsetWidth;
            el.countdown.classList.add('countdown-pop');
            await new Promise((r) => setTimeout(r, 750));
        }
        el.countdown.textContent = '';
    }

    function computeStats(inputText) {
        const target = state.roomText;
        const typed = inputText.length;
        const matched = [...inputText].filter((char, i) => char === target[i]).length;
        const progress = Math.min(100, (typed / target.length) * 100);
        const accuracy = typed === 0 ? 100 : (matched / typed) * 100;
        const elapsedMinutes = Math.max((Date.now() - (state.startedAtMs || Date.now())) / 60000, 1 / 60);
        const words = inputText.trim().length ? inputText.trim().split(/\s+/).length : 0;
        const wpm = words / elapsedMinutes;

        return {
            progress: Number(progress.toFixed(2)),
            accuracy: Number(accuracy.toFixed(2)),
            wpm: Number(wpm.toFixed(2)),
        };
    }

    function computeWordMetrics(inputText, targetText) {
        const typedWords = String(inputText || '').trim() === '' ? [] : String(inputText || '').trim().split(/\s+/);
        const targetWords = String(targetText || '').trim() === '' ? [] : String(targetText || '').trim().split(/\s+/);
        let correctWords = 0;

        for (let i = 0; i < typedWords.length; i += 1) {
            if (typedWords[i] === (targetWords[i] || null)) {
                correctWords += 1;
            }
        }

        const totalWords = typedWords.length;
        const wrongWords = Math.max(0, totalWords - correctWords);
        const errorRate = totalWords > 0 ? (wrongWords / totalWords) * 100 : 0;

        return {
            totalWords,
            correctWords,
            wrongWords,
            errorRate,
        };
    }

    function renderTypingText(input, target) {
        const chars = target.split('').map((char, i) => {
            if (i >= input.length) return `<span style="color:#64748b">${escapeHtml(char)}</span>`;
            return input[i] === char
                ? `<span style="color:#34d399">${escapeHtml(char)}</span>`
                : `<span style="color:#fb7185">${escapeHtml(char)}</span>`;
        });
        el.typingText.innerHTML = chars.join('');
    }

    function escapeHtml(value) {
        return value
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function upsertOpponent(userName, progress, wpm, accuracy) {
        state.opponents.set(userName, { progress, wpm, accuracy });
        const entries = [...state.opponents.entries()];
        el.opponents.innerHTML = entries.map(([name, stats]) => `
            <div style="border-radius:10px;border:1px solid rgba(217,70,239,.35);background:rgba(15,23,42,.62);padding:10px">
                <div style="margin-bottom:4px;display:flex;align-items:center;justify-content:space-between;font-size:13px">
                    <span>${name}</span>
                    <span>${Number(stats.progress).toFixed(1)}%</span>
                </div>
                <div style="height:8px;overflow:hidden;border-radius:999px;background:#1e293b">
                    <div style="height:100%;background:linear-gradient(90deg,#d946ef,#22d3ee);transition:width .25s;width:${Math.min(100, Math.max(0, stats.progress))}%"></div>
                </div>
                <div style="margin-top:6px;font-size:12px;color:#cbd5e1">Hız: ${Number(stats.wpm).toFixed(1)} kelime/dk | Doğruluk: ${Number(stats.accuracy).toFixed(1)}%</div>
            </div>
        `).join('');
    }

    async function finishRace(stats) {
        if (state.finished || state.isSpectator) return;
        state.finished = true;
        el.typingInput.disabled = true;

        const result = await api(`/rooms/${state.roomCode}/finish`, {
            method: 'POST',
            body: JSON.stringify({
                user_name: state.userName,
                user_id: actorUserId,
                progress: stats.progress,
                wpm: stats.wpm,
                accuracy: stats.accuracy,
                elapsed_seconds: Math.max(0, Math.floor((Date.now() - (state.startedAtMs || Date.now())) / 1000)),
                completion_seconds: stats.progress >= 100 ? Math.max(0, Math.floor((Date.now() - (state.startedAtMs || Date.now())) / 1000)) : null,
                xp_earned: Math.max(0, Math.round((stats.progress * 0.4) + (stats.wpm * 1.2) + (stats.accuracy * 0.6))),
                is_spectator: false,
            }),
        });

        renderLeaderboard(result.leaderboard || []);
    }

    function renderLeaderboard(leaderboard) {
        el.leaderboardWrap.style.display = 'block';
        el.leaderboard.classList.add('result-fade');
        el.leaderboard.innerHTML = leaderboard.map((row, index) => `
            <div style="border-radius:10px;border:1px solid rgba(56,189,248,.35);background:rgba(15,23,42,.72);padding:10px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
                <div><span style="font-weight:900;color:#67e8f9">#${index + 1}</span> ${row.userName}</div>
                <div style="font-size:13px;color:#cbd5e1">Tamamlanma: ${Number(row.progress).toFixed(1)}% | Hız: ${Number(row.wpm).toFixed(1)} kelime/dk | Doğruluk: ${Number(row.accuracy).toFixed(1)}%</div>
            </div>
        `).join('');

        const winner = leaderboard[0]?.userName;
        if (winner) {
            el.winnerText.textContent = winner === state.userName
                ? 'Tebrikler! En Hizli Klavyesor Sensin!'
                : `Kazanan: ${winner}`;
        }

        if (winner === state.userName && typeof confetti === 'function') {
            confetti({
                particleCount: 180,
                spread: 90,
                origin: { y: 0.6 },
            });
        }
    }

    function onTyping() {
        if (!state.roomText || state.isSpectator || !state.startedAtMs) return;

        const sanitizedInput = sanitizeTypingInput(el.typingInput.value);
        if (sanitizedInput !== el.typingInput.value) {
            el.typingInput.value = sanitizedInput;
        }
        const input = sanitizedInput;
        renderTypingText(input, state.roomText);

        const stats = computeStats(input);
        const wordMetrics = computeWordMetrics(input, state.roomText);
        state.myProgress = stats.progress;

        el.selfBar.style.width = `${stats.progress}%`;
        el.selfStats.textContent = `Tamamlanma: ${stats.progress.toFixed(1)}% | Hız: ${stats.wpm.toFixed(1)} kelime/dk | Doğruluk: ${stats.accuracy.toFixed(1)}%`;
        el.metricTotalWords.textContent = String(wordMetrics.totalWords);
        el.metricCorrectWords.textContent = String(wordMetrics.correctWords);
        el.metricWrongWords.textContent = String(wordMetrics.wrongWords);
        el.metricErrorRate.textContent = `% ${wordMetrics.errorRate.toFixed(2).replace('.', ',')}`;

        if (state.socketAvailable && state.socket?.connected) {
            state.socket.emit('typing_progress', {
                roomCode: state.roomCode,
                userName: state.userName,
                ...stats,
            });
        }

        if (input.length >= state.roomText.length) {
            finishRace(stats).catch((err) => setStatus(err.message));
        }
    }

    el.createRoomBtn.addEventListener('click', () => createRoom().catch((err) => setStatus(err.message)));
    el.joinRoomBtn.addEventListener('click', () => joinRoom().catch((err) => setStatus(err.message)));
    el.startRaceBtn.addEventListener('click', () => startRace().catch((err) => setStatus(err.message)));
    el.endRaceBtn.addEventListener('click', () => endRaceAndDownloadReport().catch((err) => setStatus(err.message)));
    el.typingInput.addEventListener('input', onTyping);
    el.themeToggle?.addEventListener('click', () => {
        const current = el.raceShell?.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        el.raceShell?.setAttribute('data-theme', next);
        localStorage.setItem('keyboardRaceTheme', next);
        el.themeToggle.textContent = next === 'dark' ? 'Aydınlık Moda Geç' : 'Karanlık Moda Geç';
    });

    if (params.get('name')) {
        el.userName.value = params.get('name');
    } else if (AUTH_USER_NAME) {
        el.userName.value = AUTH_USER_NAME;
    }
    if (params.get('room')) {
        el.roomCode.value = params.get('room').toUpperCase();
    }

    if (actorRole === 'teacher') {
        el.teacherTextConfig.style.display = 'block';
        el.teacherRaceActions.style.display = 'flex';
        el.createRoomBtn.style.display = 'inline-flex';
        el.joinRoomBtn.style.display = 'inline-flex';
        el.joinRoomBtn.disabled = true;
        el.joinRoomBtn.title = 'Ogretmen yalnizca oda olusturur.';
        el.endRaceBtn.disabled = true;
        setStatus('Ogretmen modu: oda olusturup yarisi baslatabilirsiniz.');
    } else {
        el.teacherTextConfig.style.display = 'none';
        el.teacherRaceActions.style.display = 'none';
        el.createRoomBtn.style.display = 'none';
        el.joinRoomBtn.style.display = 'inline-flex';
        el.createRoomBtn.disabled = true;
        el.createRoomBtn.title = 'Ogrenci oda olusturamaz.';
        el.startRaceBtn.disabled = true;
        el.endRaceBtn.disabled = true;
        setStatus('Ogrenci modu: oda kodu ile katilin.');
        if (params.get('room')) {
            if (!el.userName.value.trim()) {
                el.userName.value = `Ogrenci-${Math.floor(100 + Math.random() * 900)}`;
            }
            joinRoom().catch((err) => setStatus(err.message));
        }
    }

    window.addEventListener('beforeunload', () => {
        stopRoomPolling();
        stopRaceTimer();
    });
    const savedTheme = localStorage.getItem('keyboardRaceTheme');
    const initialTheme = savedTheme === 'dark' ? 'dark' : 'light';
    el.raceShell?.setAttribute('data-theme', initialTheme);
    if (el.themeToggle) {
        el.themeToggle.textContent = initialTheme === 'dark' ? 'Aydınlık Moda Geç' : 'Karanlık Moda Geç';
    }
    initializeTeacherTextConfig();
    if (actorRole === 'teacher') {
        renderTypingText('', raceSample);
    } else {
        el.typingText.textContent = 'Oda kodu ile katildiktan sonra yaris metni burada gorunecek.';
    }
})();
</script>
@endpush
