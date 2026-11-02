/* Canlı Quiz ses motoru: dış dosya kullanmaz, tüm müzik ve efektler Web Audio ile üretilir (telif sorunu yok). */
(function () {
    var ctx = null, master = null;
    var cfg = { enabled: true, music: true, effects: true, volume: 0.7 };
    var mode = 'player';
    var muted = false;
    var LS = 'lqSoundMuted';
    var loop = { name: null, timer: 0, step: 0, next: 0 };
    var pendingMusic = null;
    var btn = null;

    try { muted = localStorage.getItem(LS) === '1'; } catch (e) {}

    function ac() {
        if (ctx) return ctx;
        var C = window.AudioContext || window.webkitAudioContext;
        if (!C) return null;
        ctx = new C();
        master = ctx.createGain();
        master.gain.value = cfg.volume;
        master.connect(ctx.destination);
        return ctx;
    }
    function allowed(kind) {
        if (!cfg.enabled || muted) return false;
        return kind === 'music' ? cfg.music : cfg.effects;
    }
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

    /* ---------- Müzik (döngü) ---------- */
    // Her desen bir ölçü = 8 adım; kökler MIDI notası.
    var SONGS = {
        lobby: {   // neşeli bekleme müziği
            bpm: 124, chords: [[60, 64, 67], [57, 60, 64], [53, 57, 60], [55, 59, 62]], roots: [36, 33, 29, 31],
            arp: [0, 1, 2, 1, 0, 1, 2, 1], wave: 'triangle', arpGain: 0.12, bass: 'square'
        },
        question: { // gerilim: hızlı, minör
            bpm: 150, chords: [[57, 60, 64], [57, 60, 64], [53, 57, 60], [52, 56, 59]], roots: [33, 33, 29, 28],
            arp: [0, 2, 1, 2, 0, 2, 1, 2], wave: 'square', arpGain: 0.07, bass: 'sawtooth'
        }
    };
    function scheduleLoop() {
        if (!loop.name || !ctx) return;
        var s = SONGS[loop.name], stepDur = 60 / s.bpm / 2;
        while (loop.next < ctx.currentTime + 0.4) {
            var bar = Math.floor(loop.step / 8) % s.chords.length, i = loop.step % 8, t = loop.next;
            var ch = s.chords[bar];
            tone(hz(ch[s.arp[i]] + 12), t, stepDur * 0.9, { type: s.wave, gain: s.arpGain });
            if (i % 2 === 0) tone(hz(s.roots[bar]), t, stepDur * 1.6, { type: s.bass, gain: 0.09 });
            if (i % 2 === 1) hat(t, loop.name === 'question' ? 0.09 : 0.05);
            if (loop.name === 'question' && i % 4 === 0) tone(80, t, 0.12, { type: 'sine', to: 40, gain: 0.25 });
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

    function applyVolume() { if (master) master.gain.value = cfg.volume; }

    function refreshButton() {
        if (!btn) return;
        var off = !cfg.enabled || muted;
        var blocked = ctx && ctx.state === 'suspended' && !off;
        btn.textContent = off ? '🔇' : (blocked ? '🔈 Sesi aç' : '🔊');
        btn.title = off ? 'Ses kapalı (açmak için dokun)' : 'Sesi kapat';
        btn.style.display = cfg.enabled ? '' : 'none';
    }
    function unlock() {
        if (!cfg.enabled || muted) return;
        if (running() && pendingMusic && allowed('music')) {
            var n = pendingMusic; pendingMusic = null; startLoop(n);
        }
        refreshButton();
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
        play: function (name) {
            if (!allowed('effects') || !SFX[name]) return;
            if (!running()) { refreshButton(); return; }
            applyVolume();
            SFX[name](ctx.currentTime + 0.02);
        },
        music: function (name) {
            if (!allowed('music') || !SONGS[name]) return;
            if (!running()) { pendingMusic = name; refreshButton(); return; }
            applyVolume();
            startLoop(name);
        },
        stopMusic: function () { pendingMusic = null; stopLoop(); },
        muted: function () { return muted || !cfg.enabled; }
    };
    window.QuizSound = API;
})();
