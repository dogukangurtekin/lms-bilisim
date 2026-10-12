/*
 * Blok Kodlama (v2) — oyun mantığı, Blockly editörü ve platform entegrasyonu.
 * Bağımlılıklar: engine.js (yürütme), levels.js (82 bölüm), render.js (tuval), Blockly (kendi sunucumuzdan).
 *
 * Platform sözleşmesi (eski sürümle uyumlu):
 *   URL: ?role=teacher|admin  &from=&to= (veya levelStart/levelEnd)  &assignmentId=  &grant=1
 *   Gelen mesajlar: SET_LEVEL_RANGE, SET_ASSIGNMENT_PROGRESS, FORCE_ASSIGNMENT_LOCK, SET_CURRENT_LEVEL, RUN, STEP, RESET,
 *                   LOAD_STATE, REQUEST_SAVE, RESET_LOCAL, DISABLE_MENU, ENABLE_MENU
 *   Giden mesajlar: LEVEL_COMPLETED, ASSIGNMENT_RANGE_COMPLETED, LOCKED_LEVEL_WARNING, GAME_UPDATE
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
    if (st) st.textContent = 'Bölümler yüklenemedi. Sayfayı yenileyin.';
    return;
  }

  // ------------------------------------------------------------------ rol / ödev parametreleri
  var params = (function () { try { return new URLSearchParams(window.location.search); } catch (e) { return new URLSearchParams(''); } })();
  var role = (function () { var r = (params.get('role') || '').toLowerCase(); return (r === 'teacher' || r === 'admin') ? r : 'student'; })();
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
  var KEY = 'bgr.v2';
  var prog = (function () {
    try { var p = JSON.parse(localStorage.getItem(KEY) || 'null'); if (p && p.v === VERSION) return p; } catch (e) {}
    return { v: VERSION, done: {}, ws: {}, last: 0 };
  })();
  try { localStorage.removeItem('levels'); localStorage.removeItem('unlocked'); } catch (e) {} // eski sürümden kalan verileri temizle
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
  function warnLocked(msg) { msg = msg || 'Bu bölüm henüz kilitli.'; setStatus(msg, 'bad'); toast('🔒 ' + msg, 'bad', 2600); post({ type: 'LOCKED_LEVEL_WARNING', message: msg }); }

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
  var R = window.BlockRender.create(canvas);
  var level = LEVELS[0];

  // ------------------------------------------------------------------ Blockly
  var ws = null, wsReady = false, loadingWs = false, saveTimer = null;

  function defineBlocks() {
    Blockly.defineBlocksWithJsonArray([
      { type: 'program_start', message0: '▶ BAŞLA', nextStatement: null, style: 'hat_blocks', tooltip: 'Programın buradan başlar. Blokları altına bağla.' },
      { type: 'move_forward', message0: '⬆ İleri git', previousStatement: null, nextStatement: null, style: 'motion_blocks', tooltip: 'Karakteri bir kare ileri götürür.' },
      { type: 'turn_left', message0: '↺ Sola dön', previousStatement: null, nextStatement: null, style: 'turn_blocks', tooltip: 'Karakteri 90° sola çevirir.' },
      { type: 'turn_right', message0: '↻ Sağa dön', previousStatement: null, nextStatement: null, style: 'turn_blocks', tooltip: 'Karakteri 90° sağa çevirir.' },
      {
        type: 'repeat_times', message0: '🔁 Tekrarla %1 kez', args0: [{ type: 'field_number', name: 'TIMES', value: 2, min: 1, max: 50, precision: 1 }],
        message1: '%1', args1: [{ type: 'input_statement', name: 'DO' }], previousStatement: null, nextStatement: null, style: 'loop_blocks',
        tooltip: 'İçindeki blokları belirtilen sayıda tekrar eder.',
      },
      {
        type: 'repeat_until_goal', message0: '🔁 Yıldıza ulaşana kadar tekrarla', message1: '%1', args1: [{ type: 'input_statement', name: 'DO' }],
        previousStatement: null, nextStatement: null, style: 'loop_blocks', tooltip: 'Yıldıza varınca durur.',
      },
      {
        type: 'if_blocked', message0: '❓ Eğer %1 ise', args0: [{ type: 'field_dropdown', name: 'COND', options: [['önüm kapalı', 'blocked'], ['önüm açık', 'open']] }],
        message1: '%1', args1: [{ type: 'input_statement', name: 'DO' }], message2: 'değilse %1', args2: [{ type: 'input_statement', name: 'ELSE' }],
        previousStatement: null, nextStatement: null, style: 'logic_blocks', tooltip: 'Önündeki kare duvar/kenar mı diye bakar.',
      },
    ]);
  }

  function makeTheme(dark) {
    var comp = dark
      ? { workspaceBackgroundColour: '#232650', toolboxBackgroundColour: '#1b1d3a', flyoutBackgroundColour: '#1b1d3a', flyoutOpacity: 1, scrollbarColour: '#4b4f94', insertionMarkerColour: '#a5b4fc', insertionMarkerOpacity: .35, cursorColour: '#a5b4fc' }
      : { workspaceBackgroundColour: '#f6f7ff', toolboxBackgroundColour: '#eef0ff', flyoutBackgroundColour: '#eef0ff', flyoutOpacity: 1, scrollbarColour: '#c7cbf2', insertionMarkerColour: '#4f46e5', insertionMarkerOpacity: .3, cursorColour: '#4f46e5' };
    return Blockly.Theme.defineTheme(dark ? 'bgrDark' : 'bgrLight', {
      name: dark ? 'bgrDark' : 'bgrLight', base: Blockly.Themes.Classic,
      blockStyles: {
        hat_blocks: { colourPrimary: '#22c55e', colourSecondary: '#4ade80', colourTertiary: '#15803d', hat: 'cap' },
        motion_blocks: { colourPrimary: '#3b82f6', colourSecondary: '#60a5fa', colourTertiary: '#1d4ed8' },
        turn_blocks: { colourPrimary: '#a855f7', colourSecondary: '#c084fc', colourTertiary: '#7e22ce' },
        loop_blocks: { colourPrimary: '#f59e0b', colourSecondary: '#fbbf24', colourTertiary: '#b45309' },
        logic_blocks: { colourPrimary: '#ec4899', colourSecondary: '#f472b6', colourTertiary: '#be185d' },
      },
      componentStyles: comp,
      fontStyle: { family: 'Nunito, "Segoe UI", system-ui, sans-serif', weight: '800', size: 12 },
      startHats: true,
    });
  }

  var BLOCK_NAME = { forward: 'move_forward', left: 'turn_left', right: 'turn_right', repeat: 'repeat_times', until: 'repeat_until_goal', if: 'if_blocked' };
  function toolboxFor(lv) {
    var list = (lv.blocks && lv.blocks.length ? lv.blocks : ['forward', 'left', 'right']).map(function (k) { return { kind: 'block', type: BLOCK_NAME[k] }; });
    return { kind: 'flyoutToolbox', contents: list };
  }

  function startBlock() {
    if (!ws) return null;
    var b = ws.getTopBlocks(false).find(function (x) { return x.type === 'program_start'; });
    return b || null;
  }
  function ensureStartBlock() {
    var b = startBlock();
    if (!b) {
      b = ws.newBlock('program_start'); b.initSvg(); b.render(); b.moveBy(36, 24);
    }
    b.setDeletable(false);
    return b;
  }

  function chain(b) {
    var out = [];
    while (b) {
      if (b.isEnabled && !b.isEnabled()) { b = b.getNextBlock(); continue; }
      var n = null, id = b.id;
      switch (b.type) {
        case 'move_forward': n = { t: 'forward', id: id }; break;
        case 'turn_left': n = { t: 'left', id: id }; break;
        case 'turn_right': n = { t: 'right', id: id }; break;
        case 'repeat_times': n = { t: 'repeat', n: Math.max(1, parseInt(b.getFieldValue('TIMES'), 10) || 1), body: chain(b.getInputTargetBlock('DO')), id: id }; break;
        case 'repeat_until_goal': n = { t: 'until', body: chain(b.getInputTargetBlock('DO')), id: id }; break;
        case 'if_blocked': n = { t: 'if', cond: b.getFieldValue('COND'), then: chain(b.getInputTargetBlock('DO')), else: chain(b.getInputTargetBlock('ELSE')), id: id }; break;
      }
      if (n) out.push(n);
      b = b.getNextBlock();
    }
    return out;
  }
  function buildAst() { var s = startBlock(); return s ? chain(s.getNextBlock()) : []; }

  function orphanCount() {
    if (!ws) return 0;
    return ws.getTopBlocks(false).filter(function (b) { return b.type !== 'program_start'; }).length;
  }

  function blockLimit() { return Number.isFinite(Number(level.limit)) ? Number(level.limit) : null; }
  function updateCounter() {
    var el = $('blockCounter'); if (!el || !ws) return;
    var used = Engine.countBlocks(buildAst()), lim = blockLimit();
    S.used = used;
    if (lim) { el.textContent = '🧱 ' + used + ' / ' + lim + ' blok'; el.className = 'block-counter' + (used > lim ? ' over' : ''); }
    else { el.textContent = used ? '🧱 ' + used + ' blok' : ''; el.className = 'block-counter'; }
    var orphans = orphanCount();
    var help = $('wsHelp');
    if (help) help.textContent = orphans ? '⚠ ' + orphans + ' blok BAŞLA\'ya bağlı değil' : 'Blokları BAŞLA bloğunun altına bağla';
  }

  function wsSerialize() { try { return Blockly.serialization.workspaces.save(ws); } catch (e) { return null; } }
  function scheduleSaveWs() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(function () {
      var s = wsSerialize(); if (!s) return;
      prog.ws[level.id] = s; prog.last = S.idx; saveProg();
    }, 350);
  }
  function loadWorkspaceFor(lv) {
    loadingWs = true;
    try {
      ws.clear();
      var saved = prog.ws[lv.id];
      if (saved) { try { Blockly.serialization.workspaces.load(saved, ws); } catch (e) { ws.clear(); } }
      ensureStartBlock();
      try { ws.scrollCenter(); } catch (e) {}
    } finally { loadingWs = false; }
    updateCounter();
  }

  function initBlockly() {
    if (ws) return;
    defineBlocks();
    var dark = document.body.classList.contains('dark');
    ws = Blockly.inject('blocklyDiv', {
      toolbox: toolboxFor(level), renderer: 'zelos', theme: makeTheme(dark),
      media: window.__BLOCKLY_BASE + 'media/', trashcan: true, sounds: false,
      zoom: { controls: true, wheel: false, startScale: .95, maxScale: 1.6, minScale: .5, scaleSpeed: 1.1 },
      move: { scrollbars: true, drag: true, wheel: true },
      grid: { spacing: 24, length: 3, colour: dark ? 'rgba(165,180,252,.15)' : 'rgba(99,102,241,.14)', snap: true },
    });
    ws.addChangeListener(function (e) {
      if (loadingWs || e.isUiEvent) return;
      updateCounter();
      scheduleSaveWs();
    });
    wsReady = true;
    var l = $('wsLoading'); if (l) l.classList.add('hidden');
    loadLevel(S.idx, { force: true });
    window.addEventListener('resize', function () { try { Blockly.svgResize(ws); } catch (e) {} });
    new ResizeObserver(function () { try { Blockly.svgResize(ws); } catch (e) {} }).observe($('blocklyDiv'));
  }

  // ------------------------------------------------------------------ bölüm yükleme / arayüz
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
          warnLocked(idx < s || idx > e ? 'Bu bölüm ödev aralığında değil.' : 'Önceki bölümleri tamamlamadan buraya geçemezsin.');
        } else warnLocked('Önce bir önceki bölümü tamamlamalısın.');
        return false;
      }
    }
    if (S.levelRange && !isStaff()) {
      var rs = Math.max(0, S.levelRange.startIdx), re = Math.min(LEVELS.length - 1, Math.max(rs, S.levelRange.endIdx));
      idx = Math.max(rs, Math.min(re, idx));
    }
    resetRun(true);
    if (ws && wsReady && level) { var cur = wsSerialize(); if (cur) prog.ws[level.id] = cur; }
    S.idx = idx; level = LEVELS[idx]; S.attemptStart = Date.now();
    R.setLevel(level);
    document.body.dataset.theme = level.theme;
    refreshLevelTexts();
    if (ws && wsReady) {
      try { ws.updateToolbox(toolboxFor(level)); } catch (e) {}
      loadWorkspaceFor(level);
      setTimeout(function () { try { Blockly.svgResize(ws); } catch (e) {} }, 30);
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
  function highlight(id) { try { ws.highlightBlock(id); } catch (e) {} }

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
    if (!ast.length) { setStatus('Önce BAŞLA bloğunun altına blok ekle.', 'bad'); toast('Önce blok ekle 🧩', 'bad'); return null; }
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

  // ------------------------------------------------------------------ tamamlama
  function compactLevels() {
    return LEVELS.map(function (l) { var d = prog.done[l.id]; return { id: l.id, name: l.name, group: l.group, order: l.order, xp: l.xp, completed: !!d, stars: d ? d.stars : 0, v: VERSION }; });
  }
  function xpInRange() {
    var done = completedSet();
    var s = S.levelRange ? Math.max(0, S.levelRange.startIdx) : 0, e = S.levelRange ? Math.min(LEVELS.length - 1, S.levelRange.endIdx) : LEVELS.length - 1;
    return LEVELS.slice(s, e + 1).reduce(function (sum, l) { return sum + (done.has(l.id) ? (l.xp || 0) : 0); }, 0);
  }

  function postAssignmentCompleted() {
    post({
      type: 'ASSIGNMENT_RANGE_COMPLETED', source: 'block-runner', currentLevelIndex: S.idx, levels: compactLevels(),
      xp: xpInRange(), elapsedSeconds: Math.max(0, Math.round((Date.now() - S.sessionStart) / 1000)),
      completedLevelIds: Array.from(completedSet()),
    });
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

    R.celebrate(); sfx.win();
    setStatus('Yıldız bulundu! Bölüm tamamlandı 🎉', 'good');
    $('starRow').innerHTML = stars3(prog.done[level.id].stars);
    refreshHeader();

    var done = completedSet();
    post({
      type: 'LEVEL_COMPLETED', levelId: level.id, xp: level.xp || 0,
      percent: Math.round(done.size / LEVELS.length * 100), levels: compactLevels(), stars: stars,
      duration: Date.now() - S.attemptStart, currentLevelIndex: S.idx, starCollected: true,
    });
    post({ type: 'GAME_UPDATE', levels: compactLevels(), currentLevelIndex: S.idx, version: VERSION });

    var inAssignment = !isStaff() && (S.levelRange || queryRange());
    if (inAssignment) {
      var range = S.levelRange || queryRange();
      var endIdx = Math.min(LEVELS.length - 1, Math.max(0, range.endIdx));
      if (S.idx >= endIdx) { S.assignmentLocked = true; setTimeout(postAssignmentCompleted, 900); toast('Ödevdeki tüm bölümler tamamlandı! 🎉', 'good', 3000); return; }
      toast('+' + (level.xp || 0) + ' XP · Sonraki bölüme geçiliyor…', 'good', 1500);
      setTimeout(function () { loadLevel(S.idx + 1); }, 1500);
      return;
    }
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
    $('resultTitle').textContent = stars === 3 ? 'Mükemmel! 🏆' : 'Harika! Bölüm tamamlandı';
    $('resultText').textContent = stars === 3
      ? 'En iyi çözüme ulaştın!'
      : (par && used > par ? 'Daha az blokla çözebilirsin: ' + par + ' blok yeterli. Tekrar denemek ister misin?' : 'Güzel iş!');
    $('resultXp').textContent = '+' + (level.xp || 0);
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

  // ------------------------------------------------------------------ bölüm seçici
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
      sec.innerHTML = '<h4>' + g.name + ' <small>' + finished + ' / ' + g.items.length + ' bölüm · ' + (g.items[0].l.xp || 0) + ' XP</small></h4>';
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
    if (ws) { try { ws.setTheme(makeTheme(dark)); } catch (e) {} }
  }
  var SPEEDS = [0.5, 1, 2, 4];
  function cycleSpeed() { S.speed = SPEEDS[(SPEEDS.indexOf(S.speed) + 1) % SPEEDS.length]; $('speedLabel').textContent = S.speed + 'x'; try { localStorage.setItem('bgr.speed', String(S.speed)); } catch (e) {} }

  function initUI() {
    $('runBtn').addEventListener('click', runProgram);
    $('stepBtn').addEventListener('click', stepProgram);
    $('resetBtn').addEventListener('click', function () { resetRun(false); toast('Karakter başa alındı'); });
    $('clearBtn').addEventListener('click', function () {
      if (!ws) return;
      resetRun(true);
      loadingWs = true; try { ws.clear(); ensureStartBlock(); } finally { loadingWs = false; }
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
      var res = await fetch(base + '/runner-grant/block-grid-runner' + window.location.search, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      if (!res.ok) { S.grantDenied = true; setStatus('Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.', 'bad'); refreshHeader(); return; }
      var grant = await res.json();
      if (grant && grant.ok && grant.role === 'staff') { S.staffFlag = true; S.grantDenied = false; S.levelRange = null; S.assignmentLocked = false; refreshHeader(); return; }
      if (!grant || !grant.ok || grant.role !== 'student') { S.grantDenied = true; setStatus('Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.', 'bad'); return; }
      var start = Math.max(1, Number(grant.from || 1)), end = Math.max(start, Number(grant.to || start));
      S.assignmentLocked = false; S.levelRange = { startIdx: start - 1, endIdx: end - 1 };
      var target = firstIncompleteInRange();
      loadLevel(Math.max(S.levelRange.startIdx, Math.min(S.levelRange.endIdx, typeof target === 'number' ? target : S.levelRange.startIdx)), { force: true });
      setStatus('Bölüm aralığı: ' + start + '-' + end);
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
          setStatus('Bölüm aralığı: ' + s + '-' + e);
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
          // Yalnızca bu sürümün ilerleme verisi kabul edilir; eski sürümün bölüm verisi yok sayılır.
          if (Number(d.version) === VERSION && Array.isArray(d.levels)) {
            d.levels.forEach(function (l) { if (l && l.completed && LEVELS.some(function (x) { return x.id === l.id; })) prog.done[l.id] = prog.done[l.id] || { stars: Number(l.stars) || 1, blocks: 0, at: Date.now() }; });
            saveProg(); refreshHeader();
          }
          break;
        case 'REQUEST_SAVE': post({ type: 'GAME_UPDATE', levels: compactLevels(), currentLevelIndex: S.idx, version: VERSION }); break;
        case 'RESET_LOCAL': prog = { v: VERSION, done: {}, ws: {}, last: 0 }; saveProg(); loadLevel(0, { force: true }); break;
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
  R.setLevel(level); document.body.dataset.theme = level.theme; refreshLevelTexts(); refreshHeader();

  function boot() {
    if (window.__blocklyReady && window.Blockly) { try { initBlockly(); } catch (e) { console.error(e); failBlockly(); } }
  }
  function failBlockly() {
    var l = $('wsLoading'); if (l) { l.classList.remove('hidden'); l.innerHTML = 'Blok editörü yüklenemedi. <button class="btn btn-ghost btn-compact" onclick="location.reload()">Yenile</button>'; }
  }
  window.addEventListener('blockly-ready', boot);
  window.addEventListener('blockly-failed', failBlockly);
  if (window.__blocklyFailed) failBlockly(); else boot();
  resolveGrant();
  S.loaded = true;

  // dışarıdan test/hata ayıklama için
  window.BlockApp = { state: S, levels: LEVELS, loadLevel: loadLevel, run: runProgram, buildAst: buildAst, workspace: function () { return ws; } };
})();
