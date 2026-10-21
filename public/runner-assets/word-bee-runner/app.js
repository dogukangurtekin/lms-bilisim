/* Kelime Arısı — Harf Avı: arayüz. Motor: engine.js (WordBeeEngine), seviyeler: levels.js. Özgün tasarım. */
(function () {
  'use strict';

  var E = window.WordBeeEngine;
  var ALL = Array.isArray(window.WORD_BEE_LEVELS) ? window.WORD_BEE_LEVELS : [];
  var $ = function (id) { return document.getElementById(id); };
  // Canlı yarışmada ilerleme yarışmaya özeldir: önceki oyunlardan kalan tamamlanmış bölümler kilidi açmaz / aralığı erken bitirmez.
  var COMP_KEY = (function () { try { var a = new URLSearchParams(location.search).get('assignmentId') || ''; return /^competition-\d+$/.test(a) ? ':' + a : ''; } catch (e) { return ''; } })();
  var STORE_KEY = (window.RUNNER_PUBLIC ? 'word_bee_public_progress_v1' : 'word_bee_progress_v1') + COMP_KEY, SOUND_KEY = 'word_bee_sound';
  var CAT_LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor', expert: 'Uzman' };
  var ARROW = { N: '▲', S: '▼', E: '▶', W: '◀' };

  // ------------------------------------------------------------------ ödev / rol parametreleri (diğer runner'larla aynı sözleşme)
  var params = new URLSearchParams(window.location.search);
  var roleParam = (params.get('role') || '').toLowerCase();
  var runnerRole = (roleParam === 'teacher' || roleParam === 'admin') ? roleParam : 'student';
  var isPublic = !!window.RUNNER_PUBLIC;   // anasayfadaki herkese açık sürüm: izin kontrolü yok, 1. bölümden başlar, ilerleme tarayıcıda kalır
  var isStaff = !isPublic && (runnerRole === 'teacher' || runnerRole === 'admin');
  var assignmentId = String(params.get('assignmentId') || '').trim();
  var assignmentTitle = String(params.get('assignmentTitle') || 'Kelime Arısı Ödevi').trim();
  var rangeFrom = Math.max(1, Number(params.get('levelStart') || params.get('from') || 0));
  var rangeTo = Math.max(rangeFrom, Number(params.get('levelEnd') || params.get('to') || rangeFrom));
  var hasRange = Number.isFinite(rangeFrom) && rangeFrom > 0;
  var enforceGrant = params.get('grant') === '1' || params.get('enforceGrant') === '1';
  var needsGrantCheck = !isPublic && runnerRole === 'student' && (!!assignmentId || hasRange || enforceGrant);
  var grantDenied = false;
  var levelStart = (hasRange && !isPublic) ? rangeFrom : 1, levelEndParam = (hasRange && !isPublic) ? rangeTo : levelStart;
  var isAssignmentMode = !isPublic && (!!assignmentId || hasRange);
  if (!isStaff && !isAssignmentMode) { levelStart = 1; levelEndParam = isPublic ? 9999 : 6; }   // ödevsiz öğrenci: ilk 6 seviye; herkese açık sürümde tüm bölümler sırayla açılır
  if (isStaff) { isAssignmentMode = false; levelStart = 1; levelEndParam = 9999; }
  var sessionStartedAt = Date.now(), rangeCompleteFired = false;

  // ------------------------------------------------------------------ durum
  var levels = [], idx = 0, level = null;
  var prog = [], nextId = 1, active = 'root';
  var st = { busy: false, won: false, wrong: 0, hints: 0, token: 0, steps: null, stepI: 0 };
  var store = { done: {}, last: 0 };
  var soundOn = true, audio = null, cell = 56, drag = null;

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
    step: function (n) { beep(380 + (n || 0) * 38, 0.09, 'triangle', 0.05); },
    bump: function () { beep(130, 0.2, 'square', 0.05); },
    add: function () { beep(620, 0.06, 'sine', 0.04); },
    fail: function () { beep(220, 0.25, 'sawtooth', 0.04); beep(160, 0.3, 'sawtooth', 0.04, 0.12); },
    win: function () { [523, 659, 784, 1047].forEach(function (f, i) { beep(f, 0.22, 'triangle', 0.07, i * 0.11); }); },
  };

  // ------------------------------------------------------------------ mesaj / toast
  var msgTimer = 0, toastTimer = 0;
  function say(text, kind, ms) { var m = $('msg'); m.textContent = text; m.className = 'wb-msg show' + (kind ? ' ' + kind : ''); clearTimeout(msgTimer); msgTimer = setTimeout(function () { m.className = 'wb-msg'; }, ms || 3000); }
  function toast(text) { var t = $('toast'); t.textContent = text; t.className = 'wb-toast show'; clearTimeout(toastTimer); toastTimer = setTimeout(function () { t.className = 'wb-toast'; }, 2200); }

  // ------------------------------------------------------------------ arı (SVG)
  function beeSvg() { return document.getElementById('beeTpl').content.firstElementChild.cloneNode(true); }

  // ------------------------------------------------------------------ tahta
  function fitCell() {
    var wrap = $('boardWrap'); if (!level) return;
    var W = wrap.clientWidth - 28, H = wrap.clientHeight - 28;
    cell = clamp(Math.floor(Math.min(W / level.w, H / level.h)), 28, 84);
    document.documentElement.style.setProperty('--cell', cell + 'px');
    var b = $('board'); b.style.gridTemplateColumns = 'repeat(' + level.w + ', ' + cell + 'px)';
    placeBee(st.pos || level.start, true);
  }
  function buildBoard() {
    var board = $('board'); board.textContent = '';
    for (var y = 0; y < level.h; y++) for (var x = 0; x < level.w; x++) {
      var c = document.createElement('div'); c.className = 'wb-cell' + ((x + y) % 2 ? ' alt' : '') + (x === level.start.x && y === level.start.y ? ' start' : '');
      c.dataset.x = x; c.dataset.y = y; c.textContent = level.grid[y][x]; board.appendChild(c);
    }
    var bee = document.createElement('div'); bee.className = 'wb-bee'; bee.id = 'bee'; bee.appendChild(beeSvg()); board.appendChild(bee);
    fitCell();
  }
  function cellEl(x, y) { return $('board').children[y * level.w + x]; }
  function placeBee(pos, instant) {
    var bee = $('bee'); if (!bee) return;
    if (instant) bee.style.transition = 'none';
    bee.style.transform = 'translate(' + pos.x * cell + 'px,' + pos.y * cell + 'px)';
    if (instant) { void bee.offsetWidth; bee.style.transition = ''; }
  }
  function clearMarks() { Array.prototype.forEach.call($('board').querySelectorAll('.wb-cell'), function (c) { c.classList.remove('visited', 'picked', 'bad'); }); }

  // ------------------------------------------------------------------ kelime panelleri
  function renderTarget() {
    var box = $('target'); box.textContent = '';
    Array.prototype.forEach.call(level.word, function (ch) { var s = document.createElement('span'); s.textContent = ch; box.appendChild(s); });
  }
  function renderSpelled(spelled, status) {
    var box = $('spelled'); box.textContent = ''; var w = level.word, n = Math.max(w.length, spelled.length);
    box.className = 'wb-chips wb-spelled' + (status === 'ok' ? ' ok' : (status === 'wrong' || status === 'long') ? ' fail' : '');
    for (var i = 0; i < n; i++) {
      var s = document.createElement('span');
      if (i < spelled.length) { s.textContent = spelled[i]; if (spelled[i] !== w[i]) s.className = 'wrong'; } else { s.textContent = w[i] || '·'; s.className = 'empty'; }
      box.appendChild(s);
    }
  }

  // ------------------------------------------------------------------ program modeli
  function newMove(d) { return { t: 'm', d: d, id: nextId++ }; }
  function newRep() { return { t: 'r', n: 3, body: [], id: nextId++ }; }
  function listOf(slot) { if (slot === 'root') return prog; var n = findNode(prog, +slot); return n && n.t === 'r' ? n.body : prog; }
  function findNode(list, id) { for (var i = 0; i < list.length; i++) { if (list[i].id === id) return list[i]; if (list[i].t === 'r') { var f = findNode(list[i].body, id); if (f) return f; } } return null; }
  function detach(list, id) { for (var i = 0; i < list.length; i++) { if (list[i].id === id) return list.splice(i, 1)[0]; if (list[i].t === 'r') { var f = detach(list[i].body, id); if (f) return f; } } return null; }
  function used() { return E.countBlocks(prog); }
  function limit() { return level.maxBlocks; }
  function room(extra) { return limit() === null || used() + extra <= limit(); }

  function nodeEl(n) {
    var el = document.createElement('div');
    el.dataset.id = n.id;
    if (n.t === 'm') {
      el.className = 'wb-blk wb-move d-' + n.d;
      el.innerHTML = '<span class="wb-let">' + E.LETTER[n.d] + '</span><span class="wb-arrow">' + ARROW[n.d] + '</span><button class="wb-x" type="button" aria-label="Sil">✕</button>';
    } else {
      el.className = 'wb-blk wb-rep' + (String(n.id) === String(active) ? ' active' : '');
      var head = document.createElement('div'); head.className = 'wb-rep-head';
      head.innerHTML = '<span>tekrarla</span>';
      var sel = document.createElement('select'); sel.setAttribute('aria-label', 'Tekrar sayısı');
      for (var i = 2; i <= 9; i++) { var o = document.createElement('option'); o.value = i; o.textContent = i; if (i === n.n) o.selected = true; sel.appendChild(o); }
      sel.addEventListener('change', function () { n.n = +sel.value; onProgramChanged(); });
      head.appendChild(sel); var k = document.createElement('span'); k.textContent = 'kez'; head.appendChild(k);
      var x = document.createElement('button'); x.className = 'wb-x'; x.type = 'button'; x.setAttribute('aria-label', 'Sil'); x.textContent = '✕';
      var slot = document.createElement('div'); slot.className = 'wb-slot'; slot.dataset.slot = n.id;
      n.body.forEach(function (c) { slot.appendChild(nodeEl(c)); });
      var foot = document.createElement('div'); foot.className = 'wb-rep-foot';
      el.appendChild(head); el.appendChild(slot); el.appendChild(foot); el.appendChild(x);
    }
    return el;
  }
  function renderProgram() {
    var root = $('rootSlot'); root.textContent = '';
    prog.forEach(function (n) { root.appendChild(nodeEl(n)); });
    $('emptyNote').classList.toggle('hidden', prog.length > 0);
    updateCount();
  }
  function updateCount() {
    var c = used(), el = $('blockCount'), lim = limit();
    el.textContent = 'Blok: ' + c + (lim === null ? '' : ' / ' + lim);
    el.className = 'wb-bc' + (lim !== null && c === lim ? ' full' : '') + (lim !== null && c > lim ? ' over' : '');
  }
  function renderPalette() {
    var p = $('palette'); p.textContent = '';
    ['N', 'S', 'E', 'W'].forEach(function (d) {
      var b = document.createElement('div'); b.className = 'wb-blk wb-move d-' + d; b.dataset.type = 'm'; b.dataset.d = d;
      b.innerHTML = '<span class="wb-let">' + E.LETTER[d] + '</span><span class="wb-arrow">' + ARROW[d] + '</span>'; p.appendChild(b);
    });
    if (level.loops) {
      var r = document.createElement('div'); r.className = 'wb-blk wb-move'; r.style.background = 'linear-gradient(145deg,#f472b6,#e83e8c)'; r.dataset.type = 'r';
      r.innerHTML = '<span style="font-size:14px">tekrarla</span><span class="wb-arrow" style="font-size:22px">↻</span>'; p.appendChild(r);
    }
  }
  function onProgramChanged() { stopRun(); resetBoardState(); renderProgram(); }

  // ------------------------------------------------------------------ ekleme / sürükleme
  function insertNode(node, slot, index) {
    var list = listOf(slot); if (index < 0 || index > list.length) index = list.length;
    list.splice(index, 0, node);
    if (node.t === 'r') active = node.id;
  }
  function addToActive(proto) {
    var node = proto.t === 'r' ? newRep() : newMove(proto.d);
    if (!room(1)) { toast('Blok sınırına ulaştın (' + limit() + ').'); return; }
    var slot = active === 'root' || !findNode(prog, +active) ? 'root' : active;
    insertNode(node, slot, -1); SFX.add(); onProgramChanged();
  }
  function slotUnder(x, y) {
    var els = document.elementsFromPoint(x, y);
    for (var i = 0; i < els.length; i++) { var s = els[i].classList && els[i].classList.contains('wb-slot') ? els[i] : null; if (s) return s; }
    return null;
  }
  function indexIn(slotEl, y) {
    var kids = Array.prototype.filter.call(slotEl.children, function (c) { return c.classList.contains('wb-blk'); });
    for (var i = 0; i < kids.length; i++) { var r = kids[i].getBoundingClientRect(); if (y < r.top + r.height / 2) return i; }
    return kids.length;
  }
  function onDown(ev) {
    if (st.busy || ev.button > 0) return;
    if (ev.target.closest('select') || ev.target.closest('.wb-x')) return;
    var blk = ev.target.closest('.wb-blk'); if (!blk) return;
    var fromPalette = !!blk.closest('#palette');
    drag = { x0: ev.clientX, y0: ev.clientY, moved: false, fromPalette: fromPalette, blk: blk, id: fromPalette ? null : +blk.dataset.id, proto: fromPalette ? { t: blk.dataset.type, d: blk.dataset.d } : null, ghost: null, node: null, from: null };
    ev.preventDefault();
    window.addEventListener('pointermove', onMove); window.addEventListener('pointerup', onUp); window.addEventListener('pointercancel', onUp);
  }
  function clearDrop() { Array.prototype.forEach.call(document.querySelectorAll('.wb-slot.drop'), function (s) { s.classList.remove('drop'); }); }
  function onMove(ev) {
    if (!drag) return;
    if (!drag.moved) {
      if (Math.abs(ev.clientX - drag.x0) + Math.abs(ev.clientY - drag.y0) < 7) return;
      drag.moved = true;
      var g = drag.blk.cloneNode(true); g.className += ' wb-ghost'; g.style.width = drag.blk.offsetWidth + 'px'; document.body.appendChild(g); drag.ghost = g;
      if (!drag.fromPalette) { drag.node = detach(prog, drag.id); renderProgram(); stopRun(); resetBoardState(); }
    }
    drag.ghost.style.left = ev.clientX - 24 + 'px'; drag.ghost.style.top = ev.clientY - 18 + 'px';
    clearDrop(); var s = slotUnder(ev.clientX, ev.clientY); if (s) s.classList.add('drop');
  }
  function onUp(ev) {
    window.removeEventListener('pointermove', onMove); window.removeEventListener('pointerup', onUp); window.removeEventListener('pointercancel', onUp);
    var d = drag; drag = null; if (!d) return;
    clearDrop();
    if (!d.moved) {                                           // dokunma / tıklama
      if (d.fromPalette) addToActive(d.proto);
      else { var n = findNode(prog, d.id); if (n && n.t === 'r') { active = n.id; renderProgram(); } }
      return;
    }
    d.ghost.remove();
    var slot = slotUnder(ev.clientX, ev.clientY), node = d.node;
    if (!node && d.fromPalette) node = d.proto.t === 'r' ? newRep() : newMove(d.proto.d);
    if (!node) return;
    if (!slot) { if (!d.fromPalette) toast('Blok silindi.'); renderProgram(); updateCount(); return; }
    if (!room(E.countBlocks([node]))) { toast('Blok sınırına ulaştın (' + limit() + ').'); renderProgram(); return; }
    insertNode(node, slot.dataset.slot, indexIn(slot, ev.clientY)); SFX.add(); onProgramChanged();
  }
  function onWorkClick(ev) {
    var x = ev.target.closest('.wb-x');
    if (x) { var b = x.closest('.wb-blk'); detach(prog, +b.dataset.id); if (!findNode(prog, +active)) active = 'root'; onProgramChanged(); return; }
    if (ev.target.closest('.wb-blk')) return;
    if (active !== 'root') { active = 'root'; renderProgram(); }       // boş alana dokunma: ana diziye ekle
  }

  // ------------------------------------------------------------------ çalıştırma
  function stopRun() { st.token++; st.busy = false; st.steps = null; st.stepI = 0; $('btnRun').disabled = false; Array.prototype.forEach.call(document.querySelectorAll('.wb-blk.run'), function (b) { b.classList.remove('run'); }); }
  function resetBoardState() {
    st.pos = { x: level.start.x, y: level.start.y }; placeBee(st.pos, true); clearMarks(); renderSpelled('', ''); st.won = false;
  }
  function blkEl(node) { return $('workBody').querySelector('.wb-blk[data-id="' + node.id + '"]'); }
  function highlight(node) { Array.prototype.forEach.call(document.querySelectorAll('.wb-blk.run'), function (b) { b.classList.remove('run'); }); var el = node && blkEl(node); if (el) { el.classList.add('run'); if (el.scrollIntoView) el.scrollIntoView({ block: 'nearest' }); } }

  function applyStep(res, i, spelledSoFar) {
    var t = res.trace[i];
    highlight(t.node);
    if (t.out) { var bee = $('bee'); bee.classList.remove('bump'); void bee.offsetWidth; bee.classList.add('bump'); SFX.bump(); return spelledSoFar; }
    st.pos = { x: t.x, y: t.y }; placeBee(st.pos); SFX.step(i);
    var c = cellEl(t.x, t.y); c.classList.add('visited');
    var sp = spelledSoFar + t.ch; renderSpelled(sp, '');
    return sp;
  }
  function mismatchIndex(spelled) { for (var i = 0; i < spelled.length; i++) if (spelled[i] !== level.word[i]) return i; return -1; }
  async function finish(res) {
    st.busy = false; $('btnRun').disabled = false; highlight(null); st.steps = null; st.stepI = 0;
    var sp = res.spelled;
    if (res.status === 'ok') { renderSpelled(sp, 'ok'); await win(res); return; }
    renderSpelled(sp, res.status === 'short' ? '' : res.status === 'out' ? '' : res.status);
    if (res.status === 'empty') { say('Önce çalışma alanına blok ekle.', ''); return; }
    st.wrong++; SFX.fail(); updateStars();
    var mi = mismatchIndex(sp);
    if (res.status === 'out') say('Arı ızgaranın dışına çıktı! Blokları kontrol et.', 'bad');
    else if (res.status === 'overflow') say('Çok fazla adım var; döngü sayılarını azalt.', 'bad');
    else if (res.status === 'short') say('Şu ana kadar “' + sp + '” — doğru gidiyorsun ama kelime eksik. Biraz daha ilerle!', '');
    else if (res.status === 'long') say('Kelimeyi tamamladın ama arı fazladan ilerledi: “' + sp + '”.', 'bad');
    else { var t = res.trace[mi]; if (t) cellEl(t.x, t.y).classList.add('bad'); say((mi + 1) + '. harf yanlış: “' + level.word[mi] + '” gerekiyordu, arı “' + sp[mi] + '” topladı.', 'bad'); }
  }
  async function runAll() {
    if (st.busy || st.won) return;
    var res = E.run(level, prog);
    if (res.status === 'empty') { say('Önce çalışma alanına blok ekle.', ''); return; }
    resetBoardState(); var token = ++st.token; st.busy = true; $('btnRun').disabled = true;
    var sp = '';
    for (var i = 0; i < res.trace.length; i++) {
      if (token !== st.token) return;
      sp = applyStep(res, i, sp); await delay(res.trace[i].out ? 450 : 340);
    }
    if (token !== st.token) return;
    await finish(res);
  }
  async function stepOnce() {
    if (st.busy || st.won) return;
    if (!st.steps) {
      var r0 = E.run(level, prog);
      if (r0.status === 'empty') { say('Önce çalışma alanına blok ekle.', ''); return; }
      resetBoardState(); st.steps = r0; st.stepI = 0; st.partial = '';
    }
    var res = st.steps; st.partial = applyStep(res, st.stepI, st.partial || ''); st.stepI++;
    if (st.stepI >= res.trace.length) { st.busy = true; await delay(250); await finish(res); }
  }

  // ------------------------------------------------------------------ yıldız / ipucu / kazanma
  function starsFor(res) {
    var blocks = res ? res.blocks : used(), base = (st.wrong === 0 && blocks <= level.par) ? 3 : (st.wrong <= 1 || blocks <= level.par + 2) ? 2 : 1;
    if (st.hints >= 3) base = 1; else if (st.hints > 0) base = Math.min(base, 2);
    return base;
  }
  function updateStars() { var s = starsFor(null); $('chipStars').textContent = '★'.repeat(s) + '☆'.repeat(3 - s); }
  function showHint() {
    if (st.busy || st.won) return;
    st.hints++; updateStars();
    var steps = E.flatten(level.sol).steps;
    if (st.hints === 1) say('İpucu: arı önce ' + E.NAME[steps[0].d] + ' gitmeli.' + (level.loops ? ' Düz giden yolları “tekrarla” ile kısalt.' : ''), '', 4200);
    else if (st.hints === 2) say('İpucu: ilk iki hareket: ' + steps.slice(0, 2).map(function (s) { return E.NAME[s.d]; }).join(', ') + '.', '', 4600);
    else say('Çözüm: ' + E.describe(level.sol), '', 7000);
  }
  async function win(res) {
    st.won = true; SFX.win(); var bee = $('bee'); bee.classList.add('cheer');
    res.trace.forEach(function (t) { cellEl(t.x, t.y).classList.add('picked'); });
    var stars = starsFor(res), rec = store.done[level.id], firstTime = !rec;
    store.done[level.id] = { stars: Math.max(rec ? rec.stars : 0, stars), blocks: Math.min(rec && rec.blocks ? rec.blocks : 99, res.blocks), at: Date.now() };
    store.last = idx; saveStore(); updateHeader();
    emitLevelCompleted(stars); emitGameUpdate(); emitRangeIfNeeded();
    await delay(1000); bee.classList.remove('cheer');
    $('winWord').textContent = level.word;
    $('winStars').textContent = '★'.repeat(stars) + '☆'.repeat(3 - stars);
    $('winText').textContent = stars === 3 ? 'Yanlış denemesiz ve az blokla çözdün, harika!' : stars === 2 ? 'Güzel iş! Daha az blok ve yanlış denemesiz çözersen 3 yıldız alırsın.' : 'Kelimeyi buldun! Blokları kısaltarak ve ipucu almadan daha çok yıldız kazanabilirsin.';
    $('winXp').textContent = firstTime ? '+' + level.xp + ' XP' : 'Tekrar oynadın ✓';
    $('winNext').classList.toggle('hidden', idx >= levels.length - 1);
    $('winModal').classList.remove('hidden');
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
  function openLevel(i) {
    if (i < 0 || i >= levels.length) return;
    if (i > unlockedMax()) { toast('Önce önceki bölümü tamamla.'); return; }
    idx = i; level = levels[i]; stopRun(); prog = []; nextId = 1; active = 'root'; st.wrong = 0; st.hints = 0; st.pos = null;
    $('winModal').classList.add('hidden');
    $('lvName').textContent = level.title; $('lvMeta').textContent = CAT_LABEL[level.cat] + ' · ⭐ ' + level.xp + ' XP · ' + level.w + '×' + level.h + (level.maxBlocks !== null ? ' · en çok ' + level.maxBlocks + ' blok' : '');
    $('tip').textContent = 'Beni ' + level.word + ' kelimesini oluşturacak şekilde hareket ettir. ' + level.tip;
    buildBoard(); renderTarget(); renderPalette(); resetBoardState(); renderProgram(); updateStars(); updateHeader(); emitGameUpdate();
  }
  function openPicker() {
    var body = $('pickerBody'); body.textContent = ''; var max = unlockedMax(), groups = [];
    levels.forEach(function (l, i) { var g = groups.filter(function (x) { return x.cat === l.cat; })[0]; if (!g) { g = { cat: l.cat, items: [] }; groups.push(g); } g.items.push({ l: l, i: i }); });
    groups.forEach(function (g) {
      var sec = document.createElement('div'); sec.className = 'wb-group ' + g.cat;
      var done = g.items.filter(function (x) { return isDone(x.l); }).length;
      var h = document.createElement('h4'); h.innerHTML = CAT_LABEL[g.cat] + ' <small>' + done + ' / ' + g.items.length + ' bölüm · ⭐ ' + g.items[0].l.xp + ' XP</small>'; sec.appendChild(h);
      var grid = document.createElement('div'); grid.className = 'wb-grid';
      g.items.forEach(function (x) {
        var b = document.createElement('button'); b.type = 'button'; var open = x.i <= max;
        b.className = 'wb-tile' + (isDone(x.l) ? ' done' : '') + (x.i === idx ? ' current' : ''); b.disabled = !open;
        var rec = store.done[x.l.id];
        b.innerHTML = (open ? '' : '<span class="lock">🔒</span>') + '<b></b><small></small>';
        b.querySelector('b').textContent = x.l.title.replace(/^[^·]*· /, '') + ' (' + (x.i + 1) + ')';
        b.querySelector('small').textContent = rec ? '★'.repeat(rec.stars) + '☆'.repeat(3 - rec.stars) : x.l.word.length + ' harf · ' + x.l.w + '×' + x.l.h;
        b.addEventListener('click', function () { closeModals(); openLevel(x.i); }); grid.appendChild(b);
      });
      sec.appendChild(grid); body.appendChild(sec);
    });
    $('pickerModal').classList.remove('hidden');
    var cur = body.querySelector('.wb-tile.current'); if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: 'center' });
  }
  function closeModals() { $('pickerModal').classList.add('hidden'); $('helpModal').classList.add('hidden'); }

  // ------------------------------------------------------------------ platform mesajları (diğer runner'larla aynı sözleşme)
  function relIds() { var o = []; levels.forEach(function (l, i) { if (isDone(l)) o.push(i + 1); }); return o; }
  function endEff() { return Math.max(levelStart, Math.min(levelEndParam, levelStart + levels.length - 1)); }
  function pct() { return levels.length ? Math.min(100, Math.round(levels.filter(isDone).length / levels.length * 100)) : 0; }
  function levelsList() { return levels.map(function (l) { return { id: l.id, name: l.title, completed: isDone(l), stars: store.done[l.id] ? store.done[l.id].stars : 0 }; }); }
  function post(m) { try { if (window.parent && window.parent !== window) window.parent.postMessage(m, '*'); } catch (e) {} }
  function emitGameUpdate() { post({ type: 'GAME_UPDATE', source: 'word-bee', assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: endEff(), levels: levelsList(), currentLevelIndex: idx, progressPercent: pct() }); }
  function emitLevelCompleted(stars) {
    post({ type: 'LEVEL_COMPLETED', source: 'word-bee', assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelId: String(level.id), levelNo: idx + 1, levelStart: levelStart, levelEnd: endEff(), completedLevelIds: relIds(), currentLevelIndex: idx, percent: pct(), progressPercent: pct(), xp: level.xp, stars: stars, elapsedSeconds: elapsed(), levels: levelsList() });
  }
  function emitRangeIfNeeded() {
    if (!isAssignmentMode || rangeCompleteFired || !levels.length || levels.filter(isDone).length < levels.length) return;
    rangeCompleteFired = true;
    post({ type: 'ASSIGNMENT_RANGE_COMPLETED', source: 'word-bee', assignmentId: assignmentId, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: endEff(), completedLevelIds: relIds(), xp: levels.reduce(function (s, l) { return s + l.xp; }, 0), elapsedSeconds: elapsed(), currentLevelIndex: idx });
  }
  function resolveGrant() {
    if (isStaff || !needsGrantCheck) return Promise.resolve();
    var url = (window.RUNNER_APP_BASE || '').replace(/\/$/, '') + '/runner-grant/word-bee-runner' + window.location.search;
    return fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(function (r) { if (!r.ok) { grantDenied = true; return null; } return r.json(); }).then(function (g) {
      if (!g) return; if (!g.ok) { grantDenied = true; return; }
      if (g.role === 'student') { levelStart = Math.max(1, Number(g.from || 1)); levelEndParam = Math.max(levelStart, Number(g.to || levelStart)); isAssignmentMode = true; }
      else if (g.role === 'staff') { levelStart = 1; levelEndParam = 9999; isAssignmentMode = false; }
    }).catch(function () { grantDenied = true; });
  }

  // ------------------------------------------------------------------ olaylar
  function modalOpen() { return !$('pickerModal').classList.contains('hidden') || !$('helpModal').classList.contains('hidden') || !$('winModal').classList.contains('hidden'); }
  function onKey(ev) {
    if (ev.target && /^(SELECT|INPUT|TEXTAREA)$/.test(ev.target.tagName)) return;
    if (!$('pickerModal').classList.contains('hidden') || !$('helpModal').classList.contains('hidden')) { if (ev.key === 'Escape') closeModals(); return; }
    if (!$('winModal').classList.contains('hidden')) { if (ev.key === 'Enter' && !$('winNext').classList.contains('hidden')) $('winNext').click(); return; }
    // Klavye ok tuşlarıyla blok ekleme kapalı: oklar yalnızca fare/dokunma ile seçilir.
    if (ev.key === 'Enter') { ev.preventDefault(); runAll(); return; }
    if (ev.key === 'r' || ev.key === 'R') { stopRun(); resetBoardState(); return; }
    if (ev.key === 'h' || ev.key === 'H') showHint();
  }
  function bind() {
    document.addEventListener('keydown', onKey);
    window.addEventListener('resize', fitCell);
    $('palette').addEventListener('pointerdown', onDown);
    $('workBody').addEventListener('pointerdown', onDown);
    $('workBody').addEventListener('click', onWorkClick);
    $('btnRun').addEventListener('click', runAll);
    $('btnStep').addEventListener('click', stepOnce);
    $('btnReset').addEventListener('click', function () { stopRun(); resetBoardState(); });
    $('btnHint').addEventListener('click', showHint);
    $('btnPrev').addEventListener('click', function () { openLevel(idx - 1); });
    $('btnNext').addEventListener('click', function () { openLevel(idx + 1); });
    $('btnLevels').addEventListener('click', openPicker);
    $('btnHelp').addEventListener('click', function () { $('helpModal').classList.remove('hidden'); });
    $('btnSound').addEventListener('click', function () { soundOn = !soundOn; $('btnSound').textContent = soundOn ? '🔊' : '🔇'; try { localStorage.setItem(SOUND_KEY, soundOn ? '1' : '0'); } catch (e) {} });
    Array.prototype.forEach.call(document.querySelectorAll('[data-close]'), function (b) { b.addEventListener('click', closeModals); });
    $('winNext').addEventListener('click', function () { $('winModal').classList.add('hidden'); openLevel(idx + 1); });
    $('winRetry').addEventListener('click', function () { $('winModal').classList.add('hidden'); stopRun(); resetBoardState(); st.wrong = 0; st.hints = 0; updateStars(); });
    $('btnClose').addEventListener('click', function () { post({ type: 'REQUEST_CLOSE_ACTIVITY', source: 'word-bee' }); });
    if (window.parent && window.parent !== window) $('btnClose').classList.remove('hidden');
  }

  function init() {
    loadStore();
    resolveGrant().then(function () {
      if (grantDenied) { document.body.innerHTML = '<div style="padding:40px;max-width:520px;margin:60px auto;text-align:center;font:800 17px Nunito,sans-serif;color:#7f1d1d;background:#fee2e2;border-radius:20px">Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.</div>'; return; }
      levels = ALL.filter(function (l) { return l.id >= levelStart && l.id <= levelEndParam; });
      $('logoBee').appendChild(beeSvg()); $('bubbleBee').appendChild(beeSvg());
      $('btnSound').textContent = soundOn ? '🔊' : '🔇';
      bind();
      var start = levels.length ? Math.min(unlockedMax(), levels.length - 1) : 0;
      if (isStaff) start = Math.min(store.last || 0, levels.length - 1);
      openLevel(start);
    });
  }
  init();
})();
