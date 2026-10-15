/* Küçük Nokta — Kod Kartları: arayüz. Motor: engine.js (DotEngine), seviyeler: levels.js. Özgün tasarım. */
(function () {
  'use strict';

  var E = window.DotEngine;
  var ALL = Array.isArray(window.DOT_CARDS_LEVELS) ? window.DOT_CARDS_LEVELS : [];
  var $ = function (id) { return document.getElementById(id); };
  var STORE_KEY = 'dot_cards_progress_v1', SOUND_KEY = 'dot_cards_sound';
  var CAT_LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor', expert: 'Uzman' };
  var COLORS = ['#3b82f6', '#a855f7', '#f59e0b', '#10b981', '#ec4899', '#06b6d4', '#f97316', '#84cc16', '#ef4444', '#8b5cf6'];

  // ------------------------------------------------------------------ ödev / rol parametreleri (diğer runner'larla aynı sözleşme)
  var params = new URLSearchParams(window.location.search);
  var roleParam = (params.get('role') || '').toLowerCase();
  var runnerRole = (roleParam === 'teacher' || roleParam === 'admin') ? roleParam : 'student';
  var isStaff = runnerRole === 'teacher' || runnerRole === 'admin';
  var assignmentId = String(params.get('assignmentId') || '').trim();
  var assignmentTitle = String(params.get('assignmentTitle') || 'Kod Kartları Ödevi').trim();
  var rangeFrom = Math.max(1, Number(params.get('levelStart') || params.get('from') || 0));
  var rangeTo = Math.max(rangeFrom, Number(params.get('levelEnd') || params.get('to') || rangeFrom));
  var hasRange = Number.isFinite(rangeFrom) && rangeFrom > 0;
  var enforceGrant = params.get('grant') === '1' || params.get('enforceGrant') === '1';
  var needsGrantCheck = runnerRole === 'student' && (!!assignmentId || hasRange || enforceGrant);
  var grantDenied = false;
  var levelStart = hasRange ? rangeFrom : 1, levelEndParam = hasRange ? rangeTo : levelStart;
  var isAssignmentMode = !!assignmentId || hasRange;
  if (!isStaff && !isAssignmentMode) { levelStart = 1; levelEndParam = 6; }   // ödevsiz öğrenci: ilk 6 seviye
  if (isStaff) { isAssignmentMode = false; levelStart = 1; levelEndParam = 9999; }
  var sessionStartedAt = Date.now(), rangeCompleteFired = false;

  // ------------------------------------------------------------------ durum
  var levels = [], idx = 0, level = null, world = null;
  var st = null;                                 // seviye çalışma durumu
  var store = { done: {}, last: 0, hintsUsed: {} };
  var soundOn = true, audio = null;
  var cell = 56;
  var MAX_LIVES = 2;                              // her bölümde 2 can: yanlış hamle bir can götürür

  function loadStore() { try { var p = JSON.parse(localStorage.getItem(STORE_KEY) || 'null'); if (p && typeof p === 'object') store = Object.assign(store, p); } catch (e) {} try { soundOn = localStorage.getItem(SOUND_KEY) !== '0'; } catch (e) {} }
  function saveStore() { try { localStorage.setItem(STORE_KEY, JSON.stringify(store)); } catch (e) {} }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function delay(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }
  function elapsed() { return Math.max(0, Math.round((Date.now() - sessionStartedAt) / 1000)); }

  // ------------------------------------------------------------------ ses (özgün, WebAudio ile üretilen kısa bipler)
  function beep(freq, dur, type, vol, when) {
    if (!soundOn) return;
    try {
      audio = audio || new (window.AudioContext || window.webkitAudioContext)();
      var t0 = audio.currentTime + (when || 0), o = audio.createOscillator(), g = audio.createGain();
      o.type = type || 'sine'; o.frequency.value = freq; g.gain.setValueAtTime(vol || 0.06, t0); g.gain.exponentialRampToValueAtTime(0.0001, t0 + dur);
      o.connect(g); g.connect(audio.destination); o.start(t0); o.stop(t0 + dur + 0.02);
    } catch (e) {}
  }
  var SFX = {
    step: function () { beep(420, 0.07, 'triangle', 0.05); },
    turn: function () { beep(300, 0.06, 'sine', 0.04); },
    bump: function () { beep(130, 0.18, 'square', 0.05); },
    fall: function () { beep(380, 0.5, 'sawtooth', 0.04); beep(120, 0.5, 'sawtooth', 0.04, 0.1); },
    win: function () { [523, 659, 784, 1047].forEach(function (f, i) { beep(f, 0.22, 'triangle', 0.07, i * 0.11); }); },
    pick: function () { beep(660, 0.08, 'sine', 0.05); },
  };

  // ------------------------------------------------------------------ mesajlar
  var msgTimer = 0;
  function say(text, kind, ms) { var m = $('msg'); m.textContent = text; m.className = 'dc-msg show' + (kind ? ' ' + kind : ''); clearTimeout(msgTimer); msgTimer = setTimeout(function () { m.className = 'dc-msg'; }, ms || 2200); }
  var toastTimer = 0;
  function toast(text) { var t = $('toast'); t.textContent = text; t.className = 'dc-toast show'; clearTimeout(toastTimer); toastTimer = setTimeout(function () { t.className = 'dc-toast'; }, 2200); }

  // ------------------------------------------------------------------ kod renklendirme (kartlar)
  function esc(s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;'); }
  function colorize(line) {
    return esc(line)
      .replace(/\b(tekrarla|eğer|değilse|iken)\b/g, '<span class="k">$1</span>')
      .replace(/\b(önüm_boş|önüm_güvenli|sağım_boş|solum_boş)\b/g, '<span class="cd">$1</span>')
      .replace(/\b(ileri|sağa|sola|yukarı|aşağı|dön_sağ|dön_sol)\b/g, '<span class="fn">$1</span>')
      .replace(/\b(\d+)\b/g, '<span class="nm">$1</span>');
  }

  // ------------------------------------------------------------------ tahta
  function fitCell() {
    var wrap = $('boardWrap'); if (!level) return;
    var W = wrap.clientWidth - 28, H = wrap.clientHeight - 28;
    cell = clamp(Math.floor(Math.min(W / level.w, H / level.h)), 26, 84);
    document.documentElement.style.setProperty('--cell', cell + 'px');
    $('board').style.width = level.w * cell + 'px'; $('board').style.height = level.h * cell + 'px';
    placeDot(st ? st.pos : level.start, true);
  }
  function buildBoard() {
    var board = $('board'), dot = $('dot');
    Array.prototype.slice.call(board.querySelectorAll('.row')).forEach(function (r) { r.remove(); });
    for (var y = 0; y < level.h; y++) {
      var row = document.createElement('div'); row.className = 'row';
      for (var x = 0; x < level.w; x++) {
        var t = document.createElement('div'), c = level.cells[y][x];
        t.className = 'tile ' + (c === '#' ? 'wall' : c === 'o' ? 'pit' : c === 'G' ? 'goal' : ((x + y) % 2 ? 'f1' : 'f0'));
        row.appendChild(t);
      }
      board.insertBefore(row, dot);
    }
    fitCell();
  }
  function placeDot(pos, instant) {
    var dot = $('dot');
    if (instant) dot.style.transition = 'none';
    dot.style.transform = 'translate(' + pos.x * cell + 'px,' + pos.y * cell + 'px)';
    dot.dataset.d = pos.dir || 'E';
    if (instant) { void dot.offsetWidth; dot.style.transition = ''; }
  }
  function dotAt(x, y) { $('dot').style.transform = 'translate(' + x * cell + 'px,' + y * cell + 'px)'; }

  // ------------------------------------------------------------------ kartlar (el)
  function renderHand() {
    var hand = $('hand'); hand.textContent = '';
    var isRoam = level.mode === 'roam';
    $('roamPad').classList.toggle('hidden', !isRoam);
    $('btnHint').classList.toggle('hidden', false);
    $('handTitle').textContent = isRoam ? 'Serbest Gezinti' : level.mode === 'find' ? 'Doğru kartı seç' : 'Kartların';
    $('handSub').textContent = isRoam ? '' : (st.hand.length + ' kart');
    if (isRoam) { hand.innerHTML = '<p style="font-weight:700;color:#5b7096;margin:4px 2px;line-height:1.6">Ok tuşları, <code>WASD</code> ya da ekrandaki düğmelerle noktayı hedefe götür.</p>'; renderPlayed(); return; }
    st.hand.forEach(function (id, n) {
      var card = level.cards.filter(function (c) { return c.id === id; })[0];
      var b = document.createElement('button'); b.type = 'button'; b.className = 'dc-cardbtn'; b.dataset.id = id; b.style.setProperty('--cc', COLORS[(id - 1) % COLORS.length]);
      var lines = card.lines.map(function (l, i) { return '<span class="ln" data-ln="' + i + '">' + (colorize(l) || ' ') + '</span>'; }).join('');
      b.innerHTML = '<span class="dc-cardnum">' + (n + 1) + '</span><div class="dc-codecard"><pre class="dc-code">' + lines + '</pre></div>';
      b.addEventListener('click', function () { playCard(id); });
      hand.appendChild(b);
    });
    renderPlayed();
  }
  function renderPlayed() {
    var box = $('played'); box.textContent = '';
    if (!st.played.length) { var s = document.createElement('span'); s.className = 'dc-none'; s.textContent = level.mode === 'roam' ? 'Bu bölümde kart yok' : 'Henüz kart oynamadın'; box.appendChild(s); return; }
    st.played.forEach(function (id) { var s = document.createElement('span'); s.style.setProperty('--cc', COLORS[(id - 1) % COLORS.length]); s.textContent = 'Kart ' + id; box.appendChild(s); });
  }
  function cardEl(id) { return $('hand').querySelector('.dc-cardbtn[data-id="' + id + '"]'); }

  function starsFor() { var base = st.wrong === 0 ? 3 : st.wrong === 1 ? 2 : 1; return st.hints > 0 ? Math.min(base, 2) : base; }
  function updateBar() {
    var lv = $('lives'); lv.textContent = '';
    for (var h = 0; h < MAX_LIVES; h++) { var s = document.createElement('i'); s.className = 'heart ' + (h < st.lives ? 'on' : 'off') + (st.justLost === h ? ' lost' : ''); s.textContent = '♥'; lv.appendChild(s); }
    lv.title = 'Kalan can: ' + st.lives + ' / ' + MAX_LIVES; st.justLost = -1;
    var s = starsFor(); $('chipStars').textContent = '★'.repeat(s) + '☆'.repeat(3 - s);
    $('btnHint').textContent = '💡 İpucu' + (st.hints ? ' (' + st.hints + ')' : '');
  }

  // ------------------------------------------------------------------ adımları oynat
  async function play(res, el) {
    var dot = $('dot'), lastLn = -1;
    for (var i = 0; i < res.steps.length; i++) {
      var s = res.steps[i];
      if (el && s.ln !== undefined && s.ln !== lastLn) { Array.prototype.forEach.call(el.querySelectorAll('.ln.on'), function (n) { n.classList.remove('on'); }); var ln = el.querySelector('.ln[data-ln="' + s.ln + '"]'); if (ln) ln.classList.add('on'); lastLn = s.ln; }
      if (s.k === 'move') { dot.dataset.d = s.to.x > s.from.x ? 'E' : s.to.x < s.from.x ? 'W' : s.to.y > s.from.y ? 'S' : 'N'; dotAt(s.to.x, s.to.y); SFX.step(); await delay(270); }
      else if (s.k === 'turn') { dot.dataset.d = s.dir; SFX.turn(); await delay(190); }
      else if (s.k === 'fail' && s.reason === 'wall') { dot.dataset.d = s.toward.x > s.at.x ? 'E' : s.toward.x < s.at.x ? 'W' : s.toward.y > s.at.y ? 'S' : 'N'; dotAt(s.at.x + (s.toward.x - s.at.x) * 0.32, s.at.y + (s.toward.y - s.at.y) * 0.32); dot.classList.add('bump'); SFX.bump(); await delay(190); dotAt(s.at.x, s.at.y); await delay(260); dot.classList.remove('bump'); }
      else if (s.k === 'fail' && s.reason === 'pit') { dot.classList.add('fall'); SFX.fall(); await delay(620); }
      else if (s.k === 'goal') { dot.classList.add('win'); }
    }
    if (el) Array.prototype.forEach.call(el.querySelectorAll('.ln.on'), function (n) { n.classList.remove('on'); });
  }
  function resetDot(pos) { var dot = $('dot'); dot.classList.remove('fall', 'bump', 'win'); placeDot(pos, true); }

  // ------------------------------------------------------------------ oyun akışı
  function loseLife() { st.lives = Math.max(0, st.lives - 1); st.wrong++; st.justLost = st.lives; updateBar(); return st.lives <= 0; }
  async function outOfLives() { say('Canların bitti! Bölüm baştan başlıyor.', 'bad', 2600); await delay(1500); restartLevel('lives'); }
  async function playCard(id) {
    if (!st || st.busy || st.won) return;
    var card = level.cards.filter(function (c) { return c.id === id; })[0]; if (!card) return;
    st.busy = true; clearHint(); SFX.pick();
    var el = cardEl(id), res = E.runCard(world, st.pos, card.ast);
    await play(res, el);
    if (res.goal) { await win(); return; }
    var failed = !res.ok || level.mode === 'find';
    if (failed) {
      var dead = loseLife();
      say((res.failure === 'pit' ? 'Çukura düştü!' : res.failure === 'wall' ? 'Duvara çarptı!' : res.failure === 'loop' ? 'Kart sonsuza dek dönüyor!' : 'Bu kart noktayı hedefe götürmedi.') + (dead ? '' : ' Bir can gitti, kalan: ' + st.lives + '.'), 'bad');
      if (el) { el.classList.add('shake'); setTimeout(function () { el.classList.remove('shake'); }, 450); }
      await delay(450); resetDot(st.pos);
      if (dead) { await outOfLives(); return; }
      st.busy = false; return;
    }
    // başarılı ama hedefe varılmadı: kart harcanır, kontrol noktası güncellenir
    st.pos = res.end; st.hand = st.hand.filter(function (x) { return x !== id; }); st.played.push(id);
    renderHand(); updateBar();
    if (!st.hand.length) { var dead2 = loseLife(); say('Kart kalmadı ama hedefe ulaşamadın. ' + (dead2 ? 'Canların da bitti!' : 'Bir can gitti, kalan: ' + st.lives + '.') + ' Baştan başlıyoruz!', 'bad', 2600); await delay(1700); restartLevel(dead2 ? 'lives' : true); return; }
    st.busy = false;
  }
  // keepStats: undefined = sıfırdan · true = can/hata/ipucu korunur · 'lives' = hata/ipucu korunur, canlar yenilenir
  function restartLevel(keepStats) {
    if (!level) return;
    var old = st;
    st = { pos: { x: level.start.x, y: level.start.y, dir: level.start.dir }, hand: level.cards.map(function (c) { return c.id; }), played: [], busy: false, won: false, wrong: keepStats && old ? old.wrong : 0, hints: keepStats && old ? old.hints : 0, lives: keepStats === true && old ? old.lives : MAX_LIVES, justLost: -1 };
    resetDot(st.pos); renderHand(); updateBar(); clearHint();
  }
  async function roamMove(dir) {
    if (!st || st.busy || st.won || level.mode !== 'roam') return;
    st.busy = true;
    var res = E.runCard(world, st.pos, [{ t: 'move', dir: dir, n: 1, ln: 0 }]);
    await play(res, null);
    if (res.goal) { await win(); return; }
    if (res.failure === 'pit') { var dead3 = loseLife(); say('Çukura düştün! ' + (dead3 ? '' : 'Bir can gitti, kalan: ' + st.lives + '.'), 'bad'); await delay(450); st.pos = { x: level.start.x, y: level.start.y, dir: level.start.dir }; resetDot(st.pos); if (dead3) { await outOfLives(); return; } }
    else if (res.failure === 'wall') { /* sadece çarptı */ }
    else st.pos = res.end;
    st.busy = false;
  }
  function clearHint() { Array.prototype.forEach.call(document.querySelectorAll('.dc-cardbtn.hint'), function (b) { b.classList.remove('hint'); }); }
  function showHint() {
    if (!st || st.busy || st.won) return;
    if (level.mode === 'roam') { st.hints++; updateBar(); say('Turuncu hedefe doğru ilerle; duvarları ve çukurları dolan.', '', 2600); return; }
    var temp = { cells: level.cells, start: st.pos, cards: st.hand.map(function (hid) { return level.cards.filter(function (c) { return c.id === hid; })[0]; }) };
    var sols = E.solve(temp, 1);
    st.hints++; updateBar();
    if (!sols.length) { say('Bu yoldan hedefe varılamıyor. "Baştan" ile yeniden başla.', 'bad', 2800); return; }
    var first = temp.cards[sols[0][0]];
    var el = cardEl(first.id); clearHint(); if (el) { el.classList.add('hint'); if (el.scrollIntoView) el.scrollIntoView({ block: 'nearest' }); setTimeout(function () { el.classList.remove('hint'); }, 5000); }
    say('İpucu: ' + first.id + ' numaralı kartı dene.', '', 2600);
  }
  async function win() {
    st.won = true; SFX.win();
    var stars = starsFor(), rec = store.done[level.id], firstTime = !rec;
    store.done[level.id] = { stars: Math.max(rec ? rec.stars : 0, stars), wrong: rec ? Math.min(rec.wrong, st.wrong) : st.wrong, at: Date.now() };
    store.last = idx; saveStore(); updateHeader();
    emitLevelCompleted(stars); emitGameUpdate(); emitRangeIfNeeded();
    await delay(900);
    $('winStars').textContent = '★'.repeat(stars) + '☆'.repeat(3 - stars);
    $('winText').textContent = stars === 3 ? 'Hiç can kaybetmeden çözdün, harika!' : stars === 2 ? 'Güzel iş! Canını kaybetmeden çözersen 3 yıldız alırsın.' : 'Bölümü bitirdin. Kodları dikkatle okuyarak tekrar dene, daha çok yıldız kazan!';
    $('winXp').textContent = firstTime ? '+' + level.xp + ' XP' : 'Tekrar oynadın ✓';
    var last = idx >= levels.length - 1; $('winNext').classList.toggle('hidden', last);
    $('winModal').classList.remove('hidden');
    st.busy = false;
  }

  // ------------------------------------------------------------------ seviye açma / seçici
  function isDone(l) { return !!store.done[l.id]; }
  function unlockedMax() { if (isStaff) return levels.length - 1; var u = 0; while (u < levels.length - 1 && isDone(levels[u])) u++; return u; }
  function updateHeader() {
    var done = levels.filter(isDone).length;
    $('pillProgress').textContent = done + ' / ' + levels.length;
    $('pillXp').textContent = '⭐ ' + ALL.reduce(function (s, l) { return s + (store.done[l.id] ? l.xp : 0); }, 0) + ' XP';
    $('btnPrev').disabled = idx <= 0; $('btnNext').disabled = idx >= levels.length - 1 || idx + 1 > unlockedMax();
  }
  function prepare(l) {
    l.cards.forEach(function (c) { c.lines = E.codeLines(c.ast); });   // satır numaralarını (ln) AST'ye işler
  }
  function openLevel(i) {
    if (i < 0 || i >= levels.length) return;
    if (i > unlockedMax()) { toast('Önce önceki bölümü tamamla.'); return; }
    idx = i; level = levels[i]; world = E.makeWorld(level);
    $('winModal').classList.add('hidden');
    $('lvName').textContent = level.title; $('lvMeta').textContent = CAT_LABEL[level.cat] + ' · ⭐ ' + level.xp + ' XP · ' + level.w + '×' + level.h;
    $('tip').textContent = level.tip + ' Her bölümde 2 canın var: yanlış hamle bir can götürür, canların bitince bölüm baştan başlar.';
    st = null; buildBoard(); restartLevel(); updateHeader(); emitGameUpdate();
  }
  function openPicker() {
    var body = $('pickerBody'); body.textContent = ''; var max = unlockedMax(), groups = [];
    levels.forEach(function (l, i) { var g = groups.filter(function (x) { return x.cat === l.cat; })[0]; if (!g) { g = { cat: l.cat, items: [] }; groups.push(g); } g.items.push({ l: l, i: i }); });
    groups.forEach(function (g) {
      var sec = document.createElement('div'); sec.className = 'dc-group ' + g.cat;
      var done = g.items.filter(function (x) { return isDone(x.l); }).length;
      var h = document.createElement('h4'); h.innerHTML = CAT_LABEL[g.cat] + ' <small>' + done + ' / ' + g.items.length + ' bölüm · ⭐ ' + g.items[0].l.xp + ' XP</small>'; sec.appendChild(h);
      var grid = document.createElement('div'); grid.className = 'dc-grid';
      g.items.forEach(function (x) {
        var b = document.createElement('button'); b.type = 'button'; var open = x.i <= max;
        b.className = 'dc-tile' + (isDone(x.l) ? ' done' : '') + (x.i === idx ? ' current' : ''); b.disabled = !open;
        var rec = store.done[x.l.id];
        b.innerHTML = (open ? '' : '<span class="lock">🔒</span>') + '<b></b><small></small>';
        b.querySelector('b').textContent = x.l.title.replace(/^[^·]*· /, '') + ' (' + (x.i + 1) + ')';
        b.querySelector('small').textContent = rec ? '★'.repeat(rec.stars) + '☆'.repeat(3 - rec.stars) : x.l.w + '×' + x.l.h + ' · ' + (x.l.mode === 'roam' ? 'gezinti' : x.l.cards.length + ' kart');
        b.addEventListener('click', function () { closeModals(); openLevel(x.i); }); grid.appendChild(b);
      });
      sec.appendChild(grid); body.appendChild(sec);
    });
    $('pickerModal').classList.remove('hidden');
    var cur = body.querySelector('.dc-tile.current'); if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: 'center' });
  }
  function closeModals() { $('pickerModal').classList.add('hidden'); $('helpModal').classList.add('hidden'); }

  // ------------------------------------------------------------------ platform mesajları (lightbot / flowchart ile aynı sözleşme)
  function relIds() { var o = []; levels.forEach(function (l, i) { if (isDone(l)) o.push(i + 1); }); return o; }
  function endEff() { return Math.max(levelStart, Math.min(levelEndParam, levelStart + levels.length - 1)); }
  function pct() { return levels.length ? Math.min(100, Math.round(levels.filter(isDone).length / levels.length * 100)) : 0; }
  function levelsList() { return levels.map(function (l) { return { id: l.id, name: l.title, completed: isDone(l), stars: store.done[l.id] ? store.done[l.id].stars : 0 }; }); }
  function post(m) { try { if (window.parent && window.parent !== window) window.parent.postMessage(m, '*'); } catch (e) {} }
  function emitGameUpdate() { post({ type: 'GAME_UPDATE', source: 'dot-cards', assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: endEff(), levels: levelsList(), currentLevelIndex: idx, progressPercent: pct() }); }
  function emitLevelCompleted(stars) {
    post({ type: 'LEVEL_COMPLETED', source: 'dot-cards', assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelId: String(level.id), levelNo: idx + 1, levelStart: levelStart, levelEnd: endEff(), completedLevelIds: relIds(), currentLevelIndex: idx, percent: pct(), progressPercent: pct(), xp: level.xp, stars: stars, elapsedSeconds: elapsed(), levels: levelsList() });
  }
  function emitRangeIfNeeded() {
    if (!isAssignmentMode || rangeCompleteFired || !levels.length || levels.filter(isDone).length < levels.length) return;
    rangeCompleteFired = true;
    post({ type: 'ASSIGNMENT_RANGE_COMPLETED', source: 'dot-cards', assignmentId: assignmentId, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: endEff(), completedLevelIds: relIds(), xp: levels.reduce(function (s, l) { return s + l.xp; }, 0), elapsedSeconds: elapsed(), currentLevelIndex: idx });
  }
  function resolveGrant() {
    if (isStaff || !needsGrantCheck) return Promise.resolve();
    var url = (window.RUNNER_APP_BASE || '').replace(/\/$/, '') + '/runner-grant/dot-cards-runner' + window.location.search;
    return fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(function (r) { if (!r.ok) { grantDenied = true; return null; } return r.json(); }).then(function (g) {
      if (!g) return; if (!g.ok) { grantDenied = true; return; }
      if (g.role === 'student') { levelStart = Math.max(1, Number(g.from || 1)); levelEndParam = Math.max(levelStart, Number(g.to || levelStart)); isAssignmentMode = true; }
      else if (g.role === 'staff') { levelStart = 1; levelEndParam = 9999; isAssignmentMode = false; }
    }).catch(function () { grantDenied = true; });
  }

  // ------------------------------------------------------------------ olaylar
  var KEYDIR = { ArrowUp: 'N', ArrowDown: 'S', ArrowLeft: 'W', ArrowRight: 'E', w: 'N', s: 'S', a: 'W', d: 'E', W: 'N', S: 'S', A: 'W', D: 'E' };
  function onKey(ev) {
    if (!$('pickerModal').classList.contains('hidden') || !$('helpModal').classList.contains('hidden')) { if (ev.key === 'Escape') closeModals(); return; }
    if (!$('winModal').classList.contains('hidden')) { if (ev.key === 'Enter' && !$('winNext').classList.contains('hidden')) $('winNext').click(); return; }
    if (level && level.mode === 'roam' && KEYDIR[ev.key]) { ev.preventDefault(); roamMove(KEYDIR[ev.key]); return; }
    if (ev.key >= '1' && ev.key <= '9' && st && level.mode !== 'roam') { var id = st.hand[+ev.key - 1]; if (id) playCard(id); return; }
    if (ev.key === 'r' || ev.key === 'R') { restartLevel(true); return; }
    if (ev.key === 'h' || ev.key === 'H') showHint();
  }
  function bind() {
    document.addEventListener('keydown', onKey);
    window.addEventListener('resize', fitCell);
    $('btnPrev').addEventListener('click', function () { openLevel(idx - 1); });
    $('btnNext').addEventListener('click', function () { openLevel(idx + 1); });
    $('btnLevels').addEventListener('click', openPicker);
    $('btnHelp').addEventListener('click', function () { $('helpModal').classList.remove('hidden'); });
    $('btnReset').addEventListener('click', function () { if (st && st.busy) return; restartLevel(true); });
    $('btnHint').addEventListener('click', showHint);
    $('btnSound').addEventListener('click', function () { soundOn = !soundOn; $('btnSound').textContent = soundOn ? '🔊' : '🔇'; try { localStorage.setItem(SOUND_KEY, soundOn ? '1' : '0'); } catch (e) {} });
    Array.prototype.forEach.call(document.querySelectorAll('[data-close]'), function (b) { b.addEventListener('click', closeModals); });
    Array.prototype.forEach.call(document.querySelectorAll('#roamPad button'), function (b) { b.addEventListener('click', function () { roamMove(b.dataset.dir); }); });
    $('winNext').addEventListener('click', function () { $('winModal').classList.add('hidden'); openLevel(idx + 1); });
    $('winRetry').addEventListener('click', function () { $('winModal').classList.add('hidden'); restartLevel(); });
    $('btnClose').addEventListener('click', function () { post({ type: 'REQUEST_CLOSE_ACTIVITY', source: 'dot-cards' }); });
    if (window.parent && window.parent !== window) $('btnClose').classList.remove('hidden');
  }

  function init() {
    loadStore();
    resolveGrant().then(function () {
      if (grantDenied) { document.body.innerHTML = '<div style="padding:40px;max-width:520px;margin:60px auto;text-align:center;font:800 17px Nunito,sans-serif;color:#7f1d1d;background:#fee2e2;border-radius:20px">Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.</div>'; return; }
      levels = ALL.filter(function (l) { return l.id >= levelStart && l.id <= levelEndParam; });
      levels.forEach(prepare);
      $('btnSound').textContent = soundOn ? '🔊' : '🔇';
      bind();
      var start = levels.length ? Math.min(unlockedMax(), levels.length - 1) : 0;
      if (isStaff) start = Math.min(store.last || 0, levels.length - 1);
      openLevel(start);
    });
  }
  init();
})();
