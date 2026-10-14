/*
 * 3D Blok Kodlama (v2) — oyun mantığı, sürükle-bırak blok editörü ve platform entegrasyonu.
 * Bağımlılıklar: engine.js (yürütme), levels.js (200 seviye), render3d.js (izometrik tuval), editor.js (sürükle-bırak blok editörü).
 * engine.js ve editor.js 2B oyunla ortaktır (tools/block-levels/sync-shared.mjs ile eşitlenir).
 *
 * Platform sözleşmesi (eski 3D sürümle uyumlu):
 *   URL: ?role=teacher|admin  &levelStart=&levelEnd= (veya from/to)  &assignmentId=  &assignmentTitle=  &grant=1
 *   Giden mesajlar (source: 'block-3d'): GAME_UPDATE, LEVEL_COMPLETED, ASSIGNMENT_RANGE_COMPLETED, CLOSE_ACTIVITY_MODAL, OPEN_BLOCK_HOMEWORK_ASSIGN
 *   completedLevelIds = tamamlanan seviyelerin SIRA numaraları (1 tabanlı); levels[] = özet liste (id, name, group, order, xp, completed, stars).
 */
(function () {
  'use strict';

  var SET = window.BLOCK_LEVEL_SET;
  var Engine = window.BlockEngine;
  var LEVELS = (SET && SET.levels) || [];
  var VERSION = (SET && SET.version) || 2;
  var $ = function (id) { return document.getElementById(id); };

  if (!LEVELS.length || !Engine) {
    var st = $('status');
    if (st) st.textContent = 'Seviyeler yüklenemedi. Sayfayı yenileyin.';
    return;
  }

  // ------------------------------------------------------------------ rol / ödev parametreleri
  var params = (function () { try { return new URLSearchParams(window.location.search); } catch (e) { return new URLSearchParams(''); } })();
  var role = (function () {
    var r = (params.get('role') || '').toLowerCase();
    if (r !== 'teacher' && r !== 'admin') { try { r = (localStorage.getItem('runner_role') || '').toLowerCase(); } catch (e) { r = ''; } }
    return (r === 'teacher' || r === 'admin') ? r : 'student';
  })();
  var assignmentTitle = String(params.get('assignmentTitle') || '3D Blok Kodlama Ödevi');
  var assignmentId = String(params.get('assignmentId') || '').trim();
  var enforceGrant = params.get('grant') === '1' || params.get('enforceGrant') === '1';
  function queryRange() {
    // URL'de aralık parametresi yoksa aralık yoktur (serbest oyun).
    var raw = params.get('levelStart') || params.get('from');
    if (!raw) return null;
    var start = Math.max(1, Number(raw));
    var end = Math.max(start, Number(params.get('levelEnd') || params.get('to') || start));
    return (Number.isFinite(start) && Number.isFinite(end)) ? { startIdx: start - 1, endIdx: end - 1 } : null;
  }
  var needsGrantCheck = role === 'student' && (!!queryRange() || !!assignmentId || enforceGrant);

  var S = {
    idx: 0, running: false, token: 0, stepIt: null, stepAst: null, lastAst: null,
    speed: 1, sound: true, levelRange: null, assignmentLocked: false, assignmentCompleted: null,
    grantDenied: false, staffFlag: false, attemptStart: Date.now(), sessionStart: Date.now(), loaded: false,
  };
  var isStaff = function () { return role === 'teacher' || role === 'admin' || S.staffFlag; };
  var isAdmin = function () { return role === 'admin'; };
  if (!isStaff() && queryRange()) S.levelRange = queryRange();

  // ------------------------------------------------------------------ ilerleme (localStorage)
  var KEY = 'b3d.v2';
  var prog = (function () {
    try { var p = JSON.parse(localStorage.getItem(KEY) || 'null'); if (p && p.v === VERSION) return p; } catch (e) {}
    return { v: VERSION, done: {}, ws: {}, last: 0 };
  })();
  if (prog.wsv !== 4) { prog.ws = {}; prog.wsv = 4; } // blok editörü değişti (v3): eski kayıtlı blok yerleşimleri geçersiz
  // eski sürümün tarayıcıya özel (başkalarına yansımayan) özel seviye / silinen seviye kayıtlarını temizle
  try { localStorage.removeItem('block3d_custom_levels_v1'); localStorage.removeItem('block3d_deleted_base_levels_v1'); } catch (e) {}
  function saveProg() { try { localStorage.setItem(KEY, JSON.stringify(prog)); } catch (e) {} }
  var isDoneLocal = function (lv) { return !!prog.done[lv.id]; };
  function completedSet() {
    if (!isStaff() && S.assignmentCompleted instanceof Set) return S.assignmentCompleted;
    var set = new Set(); LEVELS.forEach(function (l) { if (prog.done[l.id]) set.add(l.id); }); return set;
  }
  var isCompleted = function (lv) { return completedSet().has(lv.id); };
  function firstIncompleteInRange() {
    if (!S.levelRange) return null;
    var s = Math.max(0, S.levelRange.startIdx), e = Math.min(LEVELS.length - 1, Math.max(s, S.levelRange.endIdx));
    for (var i = s; i <= e; i++) if (!isCompleted(LEVELS[i])) return i;
    return e;
  }
  function highestDoneIdx() { var m = -1; LEVELS.forEach(function (l, i) { if (prog.done[l.id]) m = i; }); return m; }

  function canOpen(idx) {
    if (idx < 0 || idx >= LEVELS.length) return false;
    if (isStaff()) return true;
    if (S.grantDenied || S.assignmentLocked) return false;
    if (S.levelRange) {
      var s = Math.max(0, S.levelRange.startIdx), e = Math.min(LEVELS.length - 1, Math.max(s, S.levelRange.endIdx));
      if (idx < s || idx > e) return false;
      return idx <= firstIncompleteInRange();
    }
    return idx <= highestDoneIdx() + 1;
  }

  // ------------------------------------------------------------------ yardımcılar: arayüz
  var toastTimer = null;
  function toast(msg, kind, ms) {
    var t = $('toast'); if (!t) return;
    t.textContent = msg; t.className = 'toast show' + (kind ? ' ' + kind : '');
    clearTimeout(toastTimer); toastTimer = setTimeout(function () { t.className = 'toast'; }, ms || 2200);
  }
  function setStatus(msg, kind) { var s = $('status'); if (s) { s.textContent = msg; s.className = 'status' + (kind ? ' ' + kind : ''); } }
  function post(msg) { try { if (window.parent && window.parent !== window) window.parent.postMessage(msg, '*'); } catch (e) {} }
  function warnLocked(msg) { msg = msg || 'Bu seviye henüz kilitli.'; setStatus(msg, 'bad'); toast('🔒 ' + msg, 'bad', 2600); post({ type: 'LOCKED_LEVEL_WARNING', message: msg }); }

  // ---- ses (WebAudio, isteğe bağlı) ----
  var audioCtx = null;
  try { var sv = localStorage.getItem('bgr.sound'); if (sv === '0') S.sound = false; } catch (e) {}
  function beep(freq, dur, type, vol, delay) {
    if (!S.sound) return;
    try {
      audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
      var o = audioCtx.createOscillator(), g = audioCtx.createGain(), t0 = audioCtx.currentTime + (delay || 0);
      o.type = type || 'sine'; o.frequency.value = freq;
      g.gain.setValueAtTime(vol || .06, t0); g.gain.exponentialRampToValueAtTime(.0001, t0 + dur);
      o.connect(g); g.connect(audioCtx.destination); o.start(t0); o.stop(t0 + dur);
    } catch (e) {}
  }
  var sfx = {
    move: function () { beep(420, .08, 'triangle', .05); },
    turn: function () { beep(560, .07, 'square', .035); },
    crash: function () { beep(160, .25, 'sawtooth', .07); beep(110, .3, 'square', .05, .08); },
    win: function () { [523, 659, 784, 1047].forEach(function (f, i) { beep(f, .22, 'triangle', .07, i * .11); }); },
    fail: function () { beep(300, .15, 'sine', .05); beep(220, .25, 'sine', .05, .14); },
  };

  // ------------------------------------------------------------------ çizim motoru
  var canvas = $('gridCanvas');
  var R = window.Block3DRender.create(canvas);
  var level = LEVELS[0];

  // ------------------------------------------------------------------ blok editörü (editor.js)
  var editor = null, wsReady = false, loadingWs = false, saveTimer = null;

  // Her seviyede temel bloklar (ileri, sola, sağa, tekrarla) açıktır; ileri bloklar seviyenin kendi listesinden gelir.
  function blockKeysFor(lv) {
    var keys = ['forward', 'left', 'right', 'repeat'];
    ['until', 'if'].forEach(function (k) { if (lv.blocks && lv.blocks.indexOf(k) >= 0) keys.push(k); });
    return keys;
  }
  function buildAst() { return editor ? editor.ast() : []; }
  function highlight(id) { if (editor) editor.highlight(id); }
  function blockLimit() { return Number.isFinite(Number(level.limit)) ? Number(level.limit) : null; }

  // ---- ilk açılış yönlendirmesi (3 adım) ----
  function hideCoach(persist) { var c = $('coach'); if (c) c.classList.add('hidden'); if (persist !== false) { try { localStorage.setItem('bgr.coach', '1'); } catch (e) {} } }
  function showCoachIfNeeded() {
    var c = $('coach'); if (!c) return;
    var seen = false; try { seen = localStorage.getItem('bgr.coach') === '1'; } catch (e) {}
    if (!seen && S.idx === 0 && Engine.countBlocks(buildAst()) === 0) c.classList.remove('hidden');
  }

  function updateCounter() {
    var el = $('blockCounter'); if (!el || !editor) return;
    var used = Engine.countBlocks(buildAst()), lim = blockLimit();
    S.used = used;
    if (lim) { el.textContent = '🧱 ' + used + ' / ' + lim + ' blok'; el.className = 'block-counter' + (used > lim ? ' over' : ''); }
    else { el.textContent = used ? '🧱 ' + used + ' blok' : ''; el.className = 'block-counter'; }
    if (used > 0) hideCoach();
  }

  function wsSerialize() { try { return editor ? editor.serialize() : null; } catch (e) { return null; } }
  function scheduleSaveWs() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(function () {
      var data = wsSerialize(); if (!data) return;
      prog.ws[level.id] = data; prog.last = S.idx; saveProg();
    }, 350);
  }
  function loadWorkspaceFor(lv) {
    loadingWs = true;
    try { var saved = prog.ws[lv.id]; if (saved) editor.load(saved); else editor.clear(); }
    finally { loadingWs = false; }
    updateCounter();
  }

  function initEditor() {
    if (editor) return;
    editor = window.BlockEditor.create({
      workspace: $('editor'), palette: $('palette'),
      onChange: function () { if (loadingWs) return; resetRunVisuals(); updateCounter(); scheduleSaveWs(); },
    });
    wsReady = true;
    var l = $('wsLoading'); if (l) l.classList.add('hidden');
    loadLevel(S.idx, { force: true });
    setTimeout(showCoachIfNeeded, 400);
  }

  // ------------------------------------------------------------------ seviye yükleme / arayüz
  function stars3(n) { return [0, 1, 2].map(function (i) { return '<span class="star' + (i < n ? ' on' : '') + '">★</span>'; }).join(''); }

  function refreshHeader() {
    var done = completedSet();
    var inRange = S.levelRange && !isStaff();
    var total = inRange ? (Math.min(LEVELS.length - 1, S.levelRange.endIdx) - Math.max(0, S.levelRange.startIdx) + 1) : LEVELS.length;
    var cnt = inRange ? LEVELS.slice(S.levelRange.startIdx, S.levelRange.endIdx + 1).filter(function (l) { return done.has(l.id); }).length : done.size;
    $('progressText').textContent = cnt + ' / ' + total;
    $('progressFill').style.width = Math.round(cnt / Math.max(1, total) * 100) + '%';
    var xp = LEVELS.reduce(function (s, l) { return s + (done.has(l.id) ? (l.xp || 0) : 0); }, 0);
    $('xpBadge').textContent = '⭐ ' + xp + ' XP';
    $('levelPrev').disabled = !canOpen(S.idx - 1);
    $('levelNext').disabled = !canOpen(S.idx + 1);
  }

  // Büyük ızgaralarda (10×10 ve üstü) oyun alanı kare yerine geniş ekran olur: izometrik tahta yatay uzandığı için karolar büyür.
  function setStageShape(lv) {
    var wrap = $('canvasWrap'); if (!wrap) return;
    var n = Math.max(lv.w, lv.h);
    wrap.dataset.shape = n >= 10 ? 'wide' : n >= 8 ? 'mid' : 'square';
  }

  function refreshLevelTexts() {
    $('levelName').textContent = level.name;
    $('levelMeta').textContent = level.group + ' · ' + R.THEMES[level.theme].name + ' · ' + level.w + '×' + level.h;
    $('stageTitle').textContent = level.name + ' — ' + level.concept;
    $('stageHint').textContent = level.hint || '';
    var rec = prog.done[level.id];
    $('starRow').innerHTML = stars3(rec ? rec.stars : 0);
  }

  function loadLevel(idx, opts) {
    opts = opts || {};
    if (!opts.force) {
      if (isStaff() === false && S.grantDenied) { warnLocked('Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.'); return false; }
      if (!isStaff() && S.assignmentLocked) { warnLocked('Ödev aralığı tamamlandı.'); return false; }
      if (!canOpen(idx)) {
        if (S.levelRange && !isStaff()) {
          var s = S.levelRange.startIdx, e = S.levelRange.endIdx;
          warnLocked(idx < s || idx > e ? 'Bu seviye ödev aralığında değil.' : 'Önceki seviyeleri tamamlamadan buraya geçemezsin.');
        } else warnLocked('Önce bir önceki seviyeyi tamamlamalısın.');
        return false;
      }
    }
    if (S.levelRange && !isStaff()) {
      var rs = Math.max(0, S.levelRange.startIdx), re = Math.min(LEVELS.length - 1, Math.max(rs, S.levelRange.endIdx));
      idx = Math.max(rs, Math.min(re, idx));
    }
    resetRun(true);
    if (editor && wsReady && level) { var cur = wsSerialize(); if (cur) prog.ws[level.id] = cur; }
    S.idx = idx; level = LEVELS[idx]; S.attemptStart = Date.now();
    setStageShape(level); R.setLevel(level);
    document.body.dataset.theme = level.theme;
    refreshLevelTexts();
    if (editor && wsReady) {
      editor.setKeys(blockKeysFor(level));
      loadWorkspaceFor(level);
    }
    setStatus('Hazır');
    prog.last = idx; saveProg();
    refreshHeader();
    if (level.limit) toast('En fazla ' + level.limit + ' blok kullanabilirsin', null, 2400);
    return true;
  }

  // ------------------------------------------------------------------ çalıştırma
  var DUR = { turn: 260, move: 340, gap: 70 };
  function dur(ms) { return Math.max(40, ms / S.speed); }
  function sleep(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

  function resetRunVisuals() { if (S.running || S.stepIt) return; try { highlight(null); } catch (e) {} }

  function resetRun(silent) {
    S.token++; S.running = false; S.stepIt = null; S.stepAst = null;
    R.cancelTweens(); R.resetRobot(); highlight(null);
    $('runBtn').disabled = false; $('stepBtn').disabled = false;
    if (!silent) setStatus('Hazır');
  }

  function preflight() {
    if (!wsReady) { toast('Blok editörü henüz hazır değil', 'bad'); return null; }
    if (!isStaff() && !canOpen(S.idx)) { warnLocked(); return null; }
    var ast = buildAst();
    if (!ast.length) { setStatus('Önce BAŞLA kutusunun içine blok ekle.', 'bad'); toast('Blokları ▶ BAŞLA kutusunun içine sürükle 🧩', 'bad', 2800); return null; }
    var lim = blockLimit(), used = Engine.countBlocks(ast);
    if (lim && used > lim) { setStatus('Çok fazla blok: en fazla ' + lim + ' blok kullanabilirsin.', 'bad'); toast('En fazla ' + lim + ' blok! Tekrar bloğunu dene 🔁', 'bad', 2800); return null; }
    return ast;
  }

  async function playEvent(e, token) {
    if (token !== S.token) return 'cancel';
    switch (e.kind) {
      case 'turn': highlight(e.id); sfx.turn(); await R.turn(e.dir, dur(DUR.turn)); await sleep(dur(DUR.gap)); break;
      case 'move': highlight(e.id); sfx.move(); await R.move(e.to, dur(DUR.move)); await sleep(dur(DUR.gap)); break;
      case 'crash':
        highlight(e.id); sfx.crash(); await R.crash(e.target, dur(380));
        $('canvasWrap').classList.remove('shake'); void $('canvasWrap').offsetWidth; $('canvasWrap').classList.add('shake');
        setStatus(e.reason === 'edge' ? 'Haritanın kenarına çarptın!' : 'Bir engele çarptın!', 'bad');
        toast(e.reason === 'edge' ? '💥 Haritanın dışına çıkamazsın' : '💥 Engel var! Başka yoldan git', 'bad');
        highlight(null); return 'stop';
      case 'goal': highlight(null); return 'goal';
      case 'end':
        sfx.fail(); highlight(null); setStatus('Program bitti ama yıldıza ulaşamadın. Tekrar dene!', 'bad'); toast('⭐ Yıldıza daha varmadın', 'bad'); return 'stop';
      case 'limit':
        sfx.fail(); highlight(null); setStatus('Program çok uzun sürdü (sonsuz döngü olabilir).', 'bad'); toast('♾ Program bitmiyor!', 'bad'); return 'stop';
    }
    return 'ok';
  }

  async function runProgram() {
    if (S.running) return;
    var ast = preflight(); if (!ast) return;
    resetRun(true);
    S.lastAst = ast; S.running = true;
    var token = ++S.token;
    $('runBtn').disabled = true; $('stepBtn').disabled = true;
    setStatus('Çalışıyor…');
    var it = Engine.run(level, ast);
    await sleep(dur(200));
    var result = 'ok';
    for (var r = it.next(); !r.done; r = it.next()) {
      result = await playEvent(r.value, token);
      if (result !== 'ok') break;
    }
    if (token !== S.token) return;
    S.running = false; $('runBtn').disabled = false; $('stepBtn').disabled = false;
    if (result === 'goal') onGoal();
  }

  async function stepProgram() {
    if (S.running) return;
    if (!S.stepIt) {
      var ast = preflight(); if (!ast) return;
      resetRun(true);
      S.lastAst = S.stepAst = ast; S.stepIt = Engine.run(level, ast);
    }
    var token = S.token;
    S.running = true; $('runBtn').disabled = true; $('stepBtn').disabled = true;
    var r = S.stepIt.next(), result = 'ok';
    if (!r.done) result = await playEvent(r.value, token);
    if (token !== S.token) return;
    S.running = false; $('runBtn').disabled = false; $('stepBtn').disabled = false;
    if (r.done || result !== 'ok') { S.stepIt = null; if (result === 'goal') onGoal(); }
    else setStatus('Adım adım: devam etmek için “Adım”a bas');
  }

  // ------------------------------------------------------------------ tamamlama + platform mesajları (3D sözleşmesi)
  var sessionXp = 0, rangeEmitted = false;
  function elapsedSeconds() { return Math.max(0, Math.round((Date.now() - S.sessionStart) / 1000)); }
  function compactLevels() {
    return LEVELS.map(function (l) { var d = prog.done[l.id]; return { id: l.id, name: l.name, group: l.group, order: l.order, xp: l.xp, completed: !!d, stars: d ? d.stars : 0 }; });
  }
  function completedOrders() { var set = completedSet(); return LEVELS.filter(function (l) { return set.has(l.id); }).map(function (l) { return l.order; }); }
  function rangeInfo() { var r = S.levelRange || queryRange(); return r ? { start: r.startIdx + 1, end: r.endIdx + 1 } : null; }
  function xpInRange() {
    var done = completedSet();
    var s0 = S.levelRange ? Math.max(0, S.levelRange.startIdx) : 0, e0 = S.levelRange ? Math.min(LEVELS.length - 1, S.levelRange.endIdx) : LEVELS.length - 1;
    return LEVELS.slice(s0, e0 + 1).reduce(function (sum, l) { return sum + (done.has(l.id) ? (l.xp || 0) : 0); }, 0);
  }
  function emitGameUpdate() {
    var r = rangeInfo();
    post({
      type: 'GAME_UPDATE', source: 'block-3d', levels: compactLevels(), currentLevelIndex: S.idx, elapsedSeconds: elapsedSeconds(),
      assignmentId: assignmentId, assignmentTitle: assignmentTitle, levelStart: r ? r.start : null, levelEnd: r ? r.end : null, completedLevelIds: completedOrders(),
    });
  }
  // Ödev aralığındaki tüm seviyeler bitince bir kez bildir (assignmentId olmasa da: Laravel ödev sayfası da dinler)
  function maybeEmitRangeComplete() {
    if (role !== 'student' || rangeEmitted) return;
    var r = rangeInfo(); if (!r) return;
    var done = completedSet();
    for (var i = r.start - 1; i <= r.end - 1 && i < LEVELS.length; i++) if (!done.has(LEVELS[i].id)) return;
    rangeEmitted = true;
    post({
      type: 'ASSIGNMENT_RANGE_COMPLETED', source: 'block-3d', assignmentId: assignmentId, assignmentTitle: assignmentTitle,
      levelStart: r.start, levelEnd: r.end, xp: xpInRange(), elapsedSeconds: elapsedSeconds(), completedLevelIds: completedOrders(),
      levels: compactLevels(), currentLevelIndex: S.idx,
    });
  }

  // ---- oturum süresi / XP göstergeleri, Çık ve Ödev Ver düğmeleri
  function updateSessionUI() {
    var t = $('sessionTimer'), x = $('sessionXp');
    if (t) { var sec = elapsedSeconds(); t.textContent = ('0' + Math.floor(sec / 60)).slice(-2) + ':' + ('0' + (sec % 60)).slice(-2); }
    if (x) x.textContent = 'XP: ' + sessionXp;
  }
  function initSessionUI() {
    var inFrame = false; try { inFrame = window.parent && window.parent !== window; } catch (e) {}
    if (role === 'student' && assignmentId) {
      ['sessionTimer', 'sessionXp'].forEach(function (id) { var e = $(id); if (e) e.classList.remove('hidden'); });
      updateSessionUI(); setInterval(updateSessionUI, 1000);
      setInterval(emitGameUpdate, 4000);
    }
    var close = $('btnClose');
    if (close) {
      if (inFrame) close.classList.remove('hidden');
      close.addEventListener('click', function () { post({ type: 'CLOSE_ACTIVITY_MODAL' }); });
    }
    var assign = $('btnAssign');
    if (assign) {
      if (role === 'admin') assign.classList.remove('hidden');
      assign.addEventListener('click', function () {
        if (role !== 'admin') return;
        post({ type: 'OPEN_BLOCK_HOMEWORK_ASSIGN', source: 'block-3d', levelStart: S.idx + 1, levelEnd: S.idx + 1, levelName: level.name, title: '3D Blok Kodlama - ' + level.name });
      });
    }
    window.addEventListener('beforeunload', emitGameUpdate);
  }

  function onGoal() {
    var ast = S.lastAst || buildAst();
    var used = Engine.countBlocks(ast);
    var stars = Engine.rateStars(used, level.par);
    var prev = prog.done[level.id];
    var first = !prev;
    prog.done[level.id] = { stars: Math.max(prev ? prev.stars : 0, stars), blocks: prev ? Math.min(prev.blocks, used) : used, at: Date.now() };
    prog.ws[level.id] = wsSerialize() || prog.ws[level.id];
    saveProg();
    if (S.assignmentCompleted instanceof Set) S.assignmentCompleted.add(level.id);

    // XP yalnızca ilk tamamlamada verilir (eski 3D sürümüyle aynı)
    var xpEarned = first ? (level.xp || 0) : 0;
    if (xpEarned > 0) { sessionXp += xpEarned; updateSessionUI(); }

    R.celebrate(); sfx.win();
    setStatus('Hedefe ulaştın! Seviye tamamlandı 🎉', 'good');
    $('starRow').innerHTML = stars3(prog.done[level.id].stars);
    refreshHeader();

    var r = rangeInfo();
    emitGameUpdate();
    post({
      type: 'LEVEL_COMPLETED', source: 'block-3d', levelId: level.id, levelNo: level.order, stars: stars, xp: xpEarned,
      duration: elapsedSeconds(), percent: 100, levels: compactLevels(), currentLevelIndex: S.idx,
      assignmentId: assignmentId, assignmentTitle: assignmentTitle, levelStart: r ? r.start : null, levelEnd: r ? r.end : null, completedLevelIds: completedOrders(),
    });

    if (!isStaff() && r && S.idx >= Math.min(LEVELS.length - 1, r.end - 1)) S.assignmentLocked = true;
    setTimeout(maybeEmitRangeComplete, 900);
    setTimeout(function () { showResult(stars, used, first); }, 900);
  }

  // ------------------------------------------------------------------ sonuç penceresi + konfeti
  var confettiRAF = 0;
  function startConfetti() {
    var c = $('confettiCanvas'); if (!c) return;
    var rect = c.getBoundingClientRect(); c.width = rect.width; c.height = rect.height;
    var g = c.getContext('2d'), parts = [], colors = ['#fde047', '#fb923c', '#f472b6', '#60a5fa', '#34d399', '#a78bfa'];
    for (var i = 0; i < 90; i++) parts.push({ x: Math.random() * c.width, y: -Math.random() * c.height * .6, vx: (Math.random() - .5) * 2, vy: 2 + Math.random() * 3, s: 4 + Math.random() * 6, r: Math.random() * 6, vr: (Math.random() - .5) * .3, c: colors[i % colors.length] });
    var t0 = performance.now();
    cancelAnimationFrame(confettiRAF);
    (function frame(now) {
      g.clearRect(0, 0, c.width, c.height);
      parts.forEach(function (p) { p.x += p.vx; p.y += p.vy; p.r += p.vr; g.save(); g.translate(p.x, p.y); g.rotate(p.r); g.fillStyle = p.c; g.fillRect(-p.s / 2, -p.s / 4, p.s, p.s / 2); g.restore(); });
      if (now - t0 < 2800) confettiRAF = requestAnimationFrame(frame); else g.clearRect(0, 0, c.width, c.height);
    })(t0);
  }
  function showResult(stars, used, first) {
    var par = level.par, best = prog.done[level.id];
    $('resultTitle').textContent = stars === 3 ? 'Mükemmel! 🏆' : 'Harika! Seviye tamamlandı';
    $('resultText').textContent = stars === 3
      ? 'En iyi çözüme ulaştın!'
      : (!first && best && best.blocks && used >= best.blocks ? 'Bu seviyeyi daha önce de tamamlamıştın.' : par && used > par ? 'Daha az blokla çözebilirsin: ' + par + ' blok yeterli. Tekrar denemek ister misin?' : 'Güzel iş!');
    $('resultXp').textContent = '+' + (first ? (level.xp || 0) : 0);
    $('resultBlocks').textContent = used;
    $('resultPar').textContent = par || '-';
    var spans = $('resultStars').children;
    for (var i = 0; i < 3; i++) spans[i].className = '';
    $('resultModal').classList.remove('hidden');
    [0, 1, 2].forEach(function (i) { setTimeout(function () { if (i < stars) spans[i].className = 'on'; }, 250 + i * 260); });
    $('resultNext').disabled = !canOpen(S.idx + 1);
    startConfetti();
  }
  function hideResult() { $('resultModal').classList.add('hidden'); cancelAnimationFrame(confettiRAF); }

  // ------------------------------------------------------------------ seviye seçici
  function openPicker() {
    var body = $('pickerBody'); body.innerHTML = '';
    var done = completedSet();
    var groups = [];
    LEVELS.forEach(function (l, i) {
      if (!isStaff() && S.levelRange && (i < S.levelRange.startIdx || i > S.levelRange.endIdx)) return;
      var g = groups.find(function (x) { return x.name === l.group; });
      if (!g) { g = { name: l.group, items: [] }; groups.push(g); }
      g.items.push({ l: l, i: i });
    });
    groups.forEach(function (g) {
      var sec = document.createElement('div'); sec.className = 'picker-group';
      var finished = g.items.filter(function (x) { return done.has(x.l.id); }).length;
      sec.innerHTML = '<h4>' + g.name + ' <small>' + finished + ' / ' + g.items.length + ' seviye · ' + (g.items[0].l.xp || 0) + ' XP</small></h4>';
      var grid = document.createElement('div'); grid.className = 'picker-grid';
      g.items.forEach(function (x) {
        var b = document.createElement('button'); b.type = 'button';
        var isDone = done.has(x.l.id), open = canOpen(x.i), rec = prog.done[x.l.id];
        b.className = 'tile' + (isDone ? ' done' : '') + (x.i === S.idx ? ' current' : '');
        b.disabled = !open;
        var starsTxt = rec ? '<span class="t-stars"><b>' + '★'.repeat(rec.stars) + '</b>' + '★'.repeat(3 - rec.stars) + '</span>' : '<span class="t-stars">★★★</span>';
        b.innerHTML = (open ? '' : '<span class="lock">🔒</span>') + '<span class="t-num">' + (x.i + 1) + '</span><span class="t-size">' + x.l.w + '×' + x.l.h + '</span>' + starsTxt;
        b.addEventListener('click', function () { if (loadLevel(x.i)) closePicker(); });
        grid.appendChild(b);
      });
      sec.appendChild(grid); body.appendChild(sec);
    });
    $('pickerModal').classList.remove('hidden');
    var cur = body.querySelector('.tile.current'); if (cur) cur.scrollIntoView({ block: 'center' });
  }
  function closePicker() { $('pickerModal').classList.add('hidden'); }

  // ------------------------------------------------------------------ tema / ses / hız
  function applyTheme(dark) {
    document.body.classList.toggle('dark', dark);
    $('darkToggle').textContent = dark ? '☀️' : '🌙';
    try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
  }
  var SPEEDS = [0.5, 1, 2, 4];
  function cycleSpeed() { S.speed = SPEEDS[(SPEEDS.indexOf(S.speed) + 1) % SPEEDS.length]; $('speedLabel').textContent = S.speed + 'x'; try { localStorage.setItem('bgr.speed', String(S.speed)); } catch (e) {} }

  function initUI() {
    $('runBtn').addEventListener('click', runProgram);
    $('stepBtn').addEventListener('click', stepProgram);
    $('resetBtn').addEventListener('click', function () { resetRun(false); toast('Karakter başa alındı'); });
    var coachClose = $('coachClose'); if (coachClose) coachClose.addEventListener('click', function () { hideCoach(); });
    $('clearBtn').addEventListener('click', function () {
      if (!editor) return;
      resetRun(true);
      loadingWs = true; try { editor.clear(); } finally { loadingWs = false; }
      updateCounter(); scheduleSaveWs(); setStatus('Bloklar temizlendi');
    });
    $('levelPrev').addEventListener('click', function () { loadLevel(S.idx - 1); });
    $('levelNext').addEventListener('click', function () { loadLevel(S.idx + 1); });
    $('levelPicker').addEventListener('click', openPicker);
    document.querySelectorAll('#pickerModal [data-close]').forEach(function (el) { el.addEventListener('click', closePicker); });
    $('resultNext').addEventListener('click', function () { hideResult(); loadLevel(S.idx + 1); });
    $('resultRetry').addEventListener('click', function () { hideResult(); resetRun(false); });
    $('resultClose').addEventListener('click', hideResult);
    $('darkToggle').addEventListener('click', function () { applyTheme(!document.body.classList.contains('dark')); });
    $('soundToggle').addEventListener('click', function () {
      S.sound = !S.sound; $('soundToggle').textContent = S.sound ? '🔊' : '🔇';
      try { localStorage.setItem('bgr.sound', S.sound ? '1' : '0'); } catch (e) {}
      if (S.sound) sfx.move();
    });
    $('speedBtn').addEventListener('click', cycleSpeed);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { closePicker(); hideResult(); }
    });
    $('soundToggle').textContent = S.sound ? '🔊' : '🔇';
    try { var sp = Number(localStorage.getItem('bgr.speed')); if (SPEEDS.indexOf(sp) >= 0) S.speed = sp; } catch (e) {}
    $('speedLabel').textContent = S.speed + 'x';
    var stored = null; try { stored = localStorage.getItem('theme'); } catch (e) {}
    applyTheme(stored ? stored === 'dark' : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));
  }

  // ------------------------------------------------------------------ platform: yetki (grant) ve mesajlar
  async function resolveGrant() {
    if (!needsGrantCheck || isStaff()) return;
    try {
      var base = (window.RUNNER_APP_BASE || '').replace(/\/$/, '');
      var res = await fetch(base + '/runner-grant/block-3d-runner' + window.location.search, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      if (!res.ok) { S.grantDenied = true; setStatus('Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.', 'bad'); refreshHeader(); return; }
      var grant = await res.json();
      if (grant && grant.ok && grant.role === 'staff') { S.staffFlag = true; S.grantDenied = false; S.levelRange = null; S.assignmentLocked = false; refreshHeader(); return; }
      if (!grant || !grant.ok || grant.role !== 'student') { S.grantDenied = true; setStatus('Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.', 'bad'); return; }
      var start = Math.max(1, Number(grant.from || 1)), end = Math.max(start, Number(grant.to || start));
      S.assignmentLocked = false; S.levelRange = { startIdx: start - 1, endIdx: end - 1 };
      var target = firstIncompleteInRange();
      loadLevel(Math.max(S.levelRange.startIdx, Math.min(S.levelRange.endIdx, typeof target === 'number' ? target : S.levelRange.startIdx)), { force: true });
      setStatus('Seviye aralığı: ' + start + '-' + end);
    } catch (e) { S.grantDenied = true; setStatus('Erişim doğrulanamadı. Sayfayı yenileyip tekrar deneyin.', 'bad'); }
  }

  window.addEventListener('message', function (ev) {
    var d = ev && ev.data; if (!d || typeof d !== 'object') return;
    try {
      switch (d.type) {
        case 'SET_LEVEL_RANGE': {
          S.assignmentLocked = false;
          var s = Math.max(1, Number(d.levelStart || 1)), e = Math.max(s, Number(d.levelEnd || s));
          S.levelRange = { startIdx: s - 1, endIdx: e - 1 };
          var target = firstIncompleteInRange();
          loadLevel(typeof target === 'number' ? target : S.levelRange.startIdx, { force: true });
          setStatus('Seviye aralığı: ' + s + '-' + e);
          break;
        }
        case 'SET_ASSIGNMENT_PROGRESS':
          S.assignmentCompleted = new Set((Array.isArray(d.completedLevelIds) ? d.completedLevelIds : []).map(Number).filter(Number.isFinite));
          if (S.levelRange) { var t = firstIncompleteInRange(); if (typeof t === 'number' && S.idx > t) loadLevel(t, { force: true }); }
          refreshHeader();
          break;
        case 'FORCE_ASSIGNMENT_LOCK': S.assignmentLocked = true; setStatus('Ödev aralığı tamamlandı'); break;
        case 'SET_CURRENT_LEVEL': loadLevel(Math.max(0, Math.min(LEVELS.length - 1, Number(d.currentLevelIndex || 0)))); break;
        case 'LOAD_STATE':
          // Yalnızca bu sürümün ilerleme verisi kabul edilir; eski sürümün seviye verisi yok sayılır.
          if (Number(d.version) === VERSION && Array.isArray(d.levels)) {
            d.levels.forEach(function (l) { if (l && l.completed && LEVELS.some(function (x) { return x.id === l.id; })) prog.done[l.id] = prog.done[l.id] || { stars: Number(l.stars) || 1, blocks: 0, at: Date.now() }; });
            saveProg(); refreshHeader();
          }
          break;
        case 'REQUEST_SAVE': emitGameUpdate(); break;
        case 'RESET_LOCAL': prog = { v: VERSION, done: {}, ws: {}, last: 0, wsv: 4 }; saveProg(); loadLevel(0, { force: true }); break;
        case 'RUN': runProgram(); break;
        case 'STEP': stepProgram(); break;
        case 'RESET': resetRun(false); break;
        default: break; // DISABLE_MENU / ENABLE_MENU / tasarımcı mesajları: v2'de menü yok
      }
    } catch (err) { /* bozuk mesajı yok say */ }
  }, false);

  // ------------------------------------------------------------------ açılış
  initUI();
  var startIdx = (function () {
    if (S.levelRange && !isStaff()) { var f = firstIncompleteInRange(); return typeof f === 'number' ? f : S.levelRange.startIdx; }
    var last = Number(prog.last) || 0;
    if (!isStaff() && !canOpen(last)) last = Math.min(highestDoneIdx() + 1, LEVELS.length - 1);
    return Math.max(0, Math.min(LEVELS.length - 1, last));
  })();
  S.idx = startIdx; level = LEVELS[S.idx];
  setStageShape(level); R.setLevel(level); document.body.dataset.theme = level.theme; refreshLevelTexts(); refreshHeader();

  initEditor();
  initSessionUI();
  emitGameUpdate();
  resolveGrant();
  S.loaded = true;

  // dışarıdan test/hata ayıklama için
  window.BlockApp = { state: S, levels: LEVELS, loadLevel: loadLevel, run: runProgram, buildAst: buildAst, editor: function () { return editor; } };
})();
