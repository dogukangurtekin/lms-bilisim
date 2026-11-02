/* Canlı Quiz ses motoru: dış dosya kullanmaz, tüm müzik ve efektler Web Audio ile üretilir (telif sorunu yok). */
(function () {
    var ctx = null, master = null;
    var cfg = { enabled: true, music: true, effects: true, volume: 0.7 };   // admin ayarı (sunucudan)
    var prefs = { music: true, transition: true, tick: true, results: true, track: 'neseli', volume: null };   // bu cihazın tercihi
    var mode = 'player';
    var muted = false;
    var LS = 'lqSoundMuted', LSP = 'lqSoundPrefs';
    var loop = { name: null, timer: 0, step: 0, next: 0 };
    var kindNow = null;          // sayfanın istediği müzik türü: 'lobby' | 'question'
    var pendingMusic = null;
    var previewTimer = 0;
    var btn = null;

    try { muted = localStorage.getItem(LS) === '1'; } catch (e) {}
    try {
        var saved = JSON.parse(localStorage.getItem(LSP) || 'null');
        if (saved && typeof saved === 'object') for (var k in prefs) if (saved[k] !== undefined) prefs[k] = saved[k];
    } catch (e) {}
    function savePrefs() { try { localStorage.setItem(LSP, JSON.stringify(prefs)); } catch (e) {} }

    function volume() { return prefs.volume == null ? cfg.volume : Math.max(0, Math.min(1, prefs.volume / 100)); }
    function ac() {
        if (ctx) return ctx;
        var C = window.AudioContext || window.webkitAudioContext;
        if (!C) return null;
        ctx = new C();
        master = ctx.createGain();
        master.gain.value = volume();
        master.connect(ctx.destination);
        return ctx;
    }
    function applyVolume() { if (master) master.gain.value = volume(); }
    function sfxCategory(name) {
        if (name === 'start' || name === 'double') return 'transition';
        if (name === 'tick') return 'tick';
        return 'results';
    }
    function allowedMusic() { return cfg.enabled && !muted && cfg.music && prefs.music; }
    function allowedSfx(name) { return cfg.enabled && !muted && cfg.effects && prefs[sfxCategory(name)] !== false; }
    function running() {
        var c = ac();
        if (!c) return false;
        if (c.state === 'suspended') { try { c.resume(); } catch (e) {} }
        return c.state === 'running';
    }
    function hz(m) { return 440 * Math.pow(2, (m - 69) / 12); }

    function tone(f, t, dur, o) {
        o = o || {};
        var c = ctx, osc = c.createOscillator(), g = c.createGain();
        osc.type = o.type || 'sine';
        osc.frequency.setValueAtTime(f, t);
        if (o.to) osc.frequency.exponentialRampToValueAtTime(o.to, t + dur);
        var peak = o.gain == null ? 0.2 : o.gain;
        g.gain.setValueAtTime(0.0001, t);
        g.gain.exponentialRampToValueAtTime(peak, t + Math.min(0.02, dur / 3));
        g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
        osc.connect(g); g.connect(master);
        osc.start(t); osc.stop(t + dur + 0.05);
    }
    function hat(t, gain) {
        var c = ctx, len = Math.floor(c.sampleRate * 0.05), buf = c.createBuffer(1, len, c.sampleRate), d = buf.getChannelData(0);
        for (var i = 0; i < len; i++) d[i] = (Math.random() * 2 - 1) * (1 - i / len);
        var src = c.createBufferSource(), g = c.createGain(), f = c.createBiquadFilter();
        f.type = 'highpass'; f.frequency.value = 6000;
        g.gain.value = gain;
        src.buffer = buf; src.connect(f); f.connect(g); g.connect(master);
        src.start(t);
    }

    /* ---------- Müzik temaları (her biri lobi + soru müziği) ---------- */
    var THEMES = {
        neseli: {
            label: 'Neşeli',
            lobby: { bpm: 124, chords: [[60, 64, 67], [57, 60, 64], [53, 57, 60], [55, 59, 62]], roots: [36, 33, 29, 31], arp: [0, 1, 2, 1, 0, 1, 2, 1], wave: 'triangle', arpGain: 0.12, bass: 'square', hat: 0.05 },
            question: { bpm: 150, chords: [[57, 60, 64], [57, 60, 64], [53, 57, 60], [52, 56, 59]], roots: [33, 33, 29, 28], arp: [0, 2, 1, 2, 0, 2, 1, 2], wave: 'square', arpGain: 0.07, bass: 'sawtooth', hat: 0.09, kick: true }
        },
        arcade: {
            label: 'Arcade (oyun salonu)',
            lobby: { bpm: 140, chords: [[60, 64, 67], [55, 59, 62], [57, 60, 64], [53, 57, 60]], roots: [36, 31, 33, 29], arp: [0, 2, 1, 2, 0, 2, 1, 2], wave: 'square', arpGain: 0.06, bass: 'triangle', hat: 0.04, octave: 12 },
            question: { bpm: 172, chords: [[57, 60, 64], [53, 57, 60], [60, 64, 67], [55, 59, 62]], roots: [33, 29, 36, 31], arp: [0, 1, 2, 1, 2, 1, 0, 1], wave: 'square', arpGain: 0.06, bass: 'square', hat: 0.07, kick: true, octave: 12 }
        },
        sakin: {
            label: 'Sakin',
            lobby: { bpm: 84, chords: [[60, 64, 67], [57, 60, 64], [62, 65, 69], [55, 59, 62]], roots: [36, 33, 38, 31], arp: [0, 1, 2, 1, 2, 1, 0, 1], wave: 'sine', arpGain: 0.15, bass: 'sine', hat: 0.02 },
            question: { bpm: 104, chords: [[57, 60, 64], [62, 65, 69], [60, 64, 67], [55, 59, 62]], roots: [33, 38, 36, 31], arp: [0, 2, 1, 2, 0, 2, 1, 2], wave: 'triangle', arpGain: 0.1, bass: 'sine', hat: 0.03, kick: true }
        },
        macera: {
            label: 'Macera (destansı)',
            lobby: { bpm: 110, chords: [[62, 65, 69], [58, 62, 65], [60, 64, 67], [57, 60, 64]], roots: [38, 34, 36, 33], arp: [0, 1, 2, 1, 0, 1, 2, 1], wave: 'sawtooth', arpGain: 0.045, bass: 'sawtooth', hat: 0.05 },
            question: { bpm: 164, chords: [[62, 65, 69], [62, 65, 69], [58, 62, 65], [57, 61, 64]], roots: [38, 38, 34, 33], arp: [0, 2, 1, 2, 0, 2, 1, 2], wave: 'sawtooth', arpGain: 0.05, bass: 'sawtooth', hat: 0.09, kick: true }
        }
    };
    function songFor(kind) {
        var th = THEMES[prefs.track] || THEMES.neseli;
        return th[kind] || null;
    }
    function scheduleLoop() {
        if (!loop.name || !ctx) return;
        var s = songFor(loop.name);
        if (!s) return;
        var stepDur = 60 / s.bpm / 2, oct = s.octave == null ? 12 : s.octave;
        while (loop.next < ctx.currentTime + 0.4) {
            var bar = Math.floor(loop.step / 8) % s.chords.length, i = loop.step % 8, t = loop.next;
            var ch = s.chords[bar];
            tone(hz(ch[s.arp[i]] + oct), t, stepDur * 0.9, { type: s.wave, gain: s.arpGain });
            if (i % 2 === 0) tone(hz(s.roots[bar]), t, stepDur * 1.6, { type: s.bass, gain: 0.09 });
            if (i % 2 === 1) hat(t, s.hat);
            if (s.kick && i % 4 === 0) tone(80, t, 0.12, { type: 'sine', to: 40, gain: 0.25 });
            loop.step++; loop.next += stepDur;
        }
    }
    function startLoop(name) {
        stopLoop();
        loop.name = name; loop.step = 0; loop.next = ctx.currentTime + 0.05;
        scheduleLoop();
        loop.timer = setInterval(scheduleLoop, 120);
    }
    function stopLoop() {
        if (loop.timer) clearInterval(loop.timer);
        loop.timer = 0; loop.name = null;
    }

    /* ---------- Efektler ---------- */
    var SFX = {
        tick: function (t) { tone(1300, t, 0.05, { type: 'square', gain: 0.1 }); },
        click: function (t) { tone(700, t, 0.06, { type: 'triangle', gain: 0.15 }); tone(1000, t + 0.05, 0.06, { type: 'triangle', gain: 0.12 }); },
        start: function (t) { [60, 67, 72].forEach(function (m, i) { tone(hz(m), t + i * 0.09, 0.22, { type: 'triangle', gain: 0.2 }); }); },
        double: function (t) { [60, 64, 67, 72, 76, 84].forEach(function (m, i) { tone(hz(m), t + i * 0.07, 0.25, { type: 'sawtooth', gain: 0.1 }); }); },
        correct: function (t) { [72, 76, 79, 84].forEach(function (m, i) { tone(hz(m), t + i * 0.08, 0.28, { type: 'triangle', gain: 0.22 }); }); },
        wrong: function (t) { tone(220, t, 0.3, { type: 'sawtooth', to: 110, gain: 0.18 }); tone(165, t + 0.18, 0.4, { type: 'sawtooth', to: 80, gain: 0.16 }); },
        results: function (t) { [67, 72, 76].forEach(function (m, i) { tone(hz(m), t + i * 0.12, 0.35, { type: 'triangle', gain: 0.2 }); }); tone(hz(48), t, 0.6, { type: 'sine', gain: 0.2 }); },
        final: function (t) {
            var mel = [[60, 0], [64, 0.18], [67, 0.36], [72, 0.54], [67, 0.8], [72, 0.98], [76, 1.2], [79, 1.5]];
            mel.forEach(function (n) { tone(hz(n[0]), t + n[1], 0.4, { type: 'triangle', gain: 0.22 }); });
            [[48, 1.5], [55, 1.5], [64, 1.5], [67, 1.5], [72, 1.5]].forEach(function (n) { tone(hz(n[0]), t + n[1], 1.4, { type: 'sawtooth', gain: 0.09 }); });
        }
    };

    function refreshButton() {
        if (!btn) return;
        var off = !cfg.enabled || muted;
        var blocked = ctx && ctx.state === 'suspended' && !off;
        btn.textContent = off ? '🔇' : (blocked ? '🔈 Sesi aç' : '🔊');
        btn.title = off ? 'Ses kapalı (açmak için dokun)' : 'Sesi kapat';
        btn.style.display = cfg.enabled ? '' : 'none';
    }
    function startKind(kind) {
        if (!kind || !allowedMusic() || !songFor(kind)) return;
        if (!running()) { pendingMusic = kind; refreshButton(); return; }
        applyVolume();
        startLoop(kind);
    }
    function unlock() {
        if (!cfg.enabled || muted) return;
        if (running() && pendingMusic && allowedMusic()) {
            var n = pendingMusic; pendingMusic = null; startLoop(n);
        }
        refreshButton();
    }

    /* ---------- Kontrol paneli (öğretmen ekranı) ---------- */
    function mountPanel(el) {
        if (!el) return;
        el.innerHTML = '';
        var box = document.createElement('div');
        box.style.cssText = 'margin-top:12px;padding:12px 14px;border:1px solid #c7d2fe;border-radius:12px;background:#f5f3ff;color:#1e1b4b;font:600 14px system-ui,sans-serif';
        if (!cfg.enabled) {
            box.textContent = '🔇 Canlı Quiz sesleri yönetici tarafından kapatılmış.';
            el.appendChild(box);
            return;
        }
        var title = document.createElement('div');
        title.style.cssText = 'font-weight:800;margin-bottom:8px';
        title.textContent = '🎵 Müzik ve Ses (bu bilgisayar)';
        box.appendChild(title);

        var grid = document.createElement('div');
        grid.style.cssText = 'display:flex;flex-wrap:wrap;gap:8px 18px;align-items:center';
        function check(label, key, adminOk) {
            var l = document.createElement('label');
            l.style.cssText = 'display:flex;gap:6px;align-items:center;margin:0;cursor:pointer' + (adminOk ? '' : ';opacity:.5');
            var c = document.createElement('input');
            c.type = 'checkbox'; c.checked = prefs[key] !== false; c.disabled = !adminOk;
            c.addEventListener('change', function () {
                prefs[key] = c.checked; savePrefs();
                if (key === 'music') { if (c.checked) startKind(kindNow); else { pendingMusic = null; stopLoop(); } }
            });
            l.appendChild(c); l.appendChild(document.createTextNode(label + (adminOk ? '' : ' (yönetici kapattı)')));
            grid.appendChild(l);
        }
        check('Müzik', 'music', cfg.music);
        check('Soru geçiş sesi', 'transition', cfg.effects);
        check('Geri sayım tik sesi', 'tick', cfg.effects);
        check('Sonuç ve final sesleri', 'results', cfg.effects);
        box.appendChild(grid);

        var row = document.createElement('div');
        row.style.cssText = 'display:flex;flex-wrap:wrap;gap:8px 14px;align-items:center;margin-top:10px';
        var sel = document.createElement('select');
        sel.style.cssText = 'padding:6px 8px;border:1px solid #c4b5fd;border-radius:8px;font:inherit;background:#fff;color:#1e1b4b';
        Object.keys(THEMES).forEach(function (id) {
            var o = document.createElement('option'); o.value = id; o.textContent = THEMES[id].label; sel.appendChild(o);
        });
        sel.value = THEMES[prefs.track] ? prefs.track : 'neseli';
        sel.addEventListener('change', function () {
            prefs.track = sel.value; savePrefs();
            if (loop.name) startKind(loop.name);   // çalan müziği yeni temayla devam ettir
        });
        var lm = document.createElement('span'); lm.textContent = 'Müzik seçimi:';
        var prev = document.createElement('button');
        prev.type = 'button'; prev.textContent = '▶ Dinle';
        prev.style.cssText = 'padding:6px 12px;border:0;border-radius:8px;background:#7c3aed;color:#fff;font:700 13px system-ui,sans-serif;cursor:pointer';
        prev.addEventListener('click', function () { API.preview(); });
        var vl = document.createElement('span'); vl.textContent = 'Ses seviyesi:';
        var rng = document.createElement('input');
        rng.type = 'range'; rng.min = 0; rng.max = 100; rng.value = Math.round(volume() * 100);
        var val = document.createElement('span'); val.textContent = rng.value; val.style.minWidth = '28px';
        rng.addEventListener('input', function () { prefs.volume = parseInt(rng.value, 10); val.textContent = rng.value; savePrefs(); applyVolume(); });
        [lm, sel, prev, vl, rng, val].forEach(function (n) { row.appendChild(n); });
        box.appendChild(row);
        el.appendChild(box);
    }

    var API = {
        init: function (c, m) {
            if (c) {
                cfg.enabled = c.enabled !== false;
                cfg.music = c.music !== false;
                cfg.effects = c.effects !== false;
                cfg.volume = Math.max(0, Math.min(1, (c.volume == null ? 70 : c.volume) / 100));
            }
            mode = m || 'player';
            if (!cfg.enabled) return;
            btn = document.createElement('button');
            btn.type = 'button';
            btn.setAttribute('aria-label', 'Ses');
            btn.style.cssText = 'position:fixed;left:14px;bottom:14px;z-index:2000;border:0;border-radius:999px;padding:10px 16px;font:700 15px system-ui,sans-serif;background:#4f46e5;color:#fff;box-shadow:0 6px 18px rgba(15,23,42,.35);cursor:pointer';
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (muted) {
                    muted = false;
                    try { localStorage.setItem(LS, '0'); } catch (x) {}
                    unlock();
                    startKind(kindNow);
                } else if (ctx && ctx.state === 'suspended') {
                    unlock();
                } else {
                    muted = true;
                    try { localStorage.setItem(LS, '1'); } catch (x) {}
                    stopLoop();
                }
                refreshButton();
            });
            document.body.appendChild(btn);
            ['pointerdown', 'keydown', 'touchstart'].forEach(function (ev) { document.addEventListener(ev, unlock, { passive: true }); });
            refreshButton();
        },
        mode: function () { return mode; },
        mountPanel: mountPanel,
        themes: function () { return Object.keys(THEMES).map(function (id) { return { id: id, label: THEMES[id].label }; }); },
        play: function (name) {
            if (!SFX[name] || !allowedSfx(name)) return;
            if (!running()) { refreshButton(); return; }
            applyVolume();
            SFX[name](ctx.currentTime + 0.02);
        },
        music: function (kind) { kindNow = kind; startKind(kind); },
        preview: function () {
            if (!cfg.enabled || muted || !cfg.music) return;
            if (!running()) { refreshButton(); return; }
            applyVolume();
            clearTimeout(previewTimer);
            startLoop('lobby');
            previewTimer = setTimeout(function () {
                stopLoop();
                if (kindNow && prefs.music) startKind(kindNow);
            }, 7000);
        },
        stopMusic: function () { kindNow = null; pendingMusic = null; stopLoop(); },
        muted: function () { return muted || !cfg.enabled; },
        prefs: function () { return JSON.parse(JSON.stringify(prefs)); }
    };
    window.QuizSound = API;
})();
