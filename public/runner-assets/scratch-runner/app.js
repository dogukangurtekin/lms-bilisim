(function () {
  'use strict';
  var $ = function (s) { return document.querySelector(s); };
  var KEY = 'scratch-runner:' + (window.RUNNER_USER || 'anon');
  var CUR_KEY = KEY + ':current', LIST_KEY = KEY + ':projects';

  /* Giriş yapmış kullanıcıda projeler sunucuya da kaydedilir (en fazla 10); ziyaretçide yalnızca tarayıcıya. */
  var SERVER = !!window.RUNNER_USER && !window.RUNNER_PUBLIC, API = '/scratch-api/projeler', NOSAVE = false, MAXP = 10;
  var VIEW_ID = parseInt(new URLSearchParams(location.search).get('project'), 10) || 0;
  function xsrf() { var m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/); return m ? decodeURIComponent(m[1]) : ''; }
  function api(method, url, body) {
    return fetch(url, {
      method: method, credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrf(), 'X-Requested-With': 'XMLHttpRequest' },
      body: body ? JSON.stringify(body) : undefined
    }).then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, status: r.status, j: j }; }); })
      .catch(function () { return { ok: false, status: 0, j: {} }; });
  }

  /* ---------- Blok tanımları ---------- */
  var CATS = [
    ['events', 'Olaylar', '#ffbf00'], ['motion', 'Hareket', '#4c97ff'], ['looks', 'Görünüm', '#9966ff'],
    ['sound', 'Ses', '#cf63cf'], ['control', 'Denetim', '#ffab19'], ['sensing', 'Algılama', '#5cb1d6'],
    ['ops', 'İşlemler', '#59c059'], ['vars', 'Değişkenler', '#ff8c1a'], ['pen', 'Kalem', '#0fbd8c']
  ];
  function B(c, t, l, a, d, extra) { var o = { c: c, t: t, l: l, a: a || [], d: d || [] }; for (var k in (extra || {})) o[k] = extra[k]; return o; }
  var BD = {
    whenflag: B('events', 'hat', '🏁 bayrak tıklanınca'),
    whenkey: B('events', 'hat', '%0 tuşuna basılınca', ['key'], ['boşluk']),
    whenclick: B('events', 'hat', 'bu nesneye tıklanınca'),
    whenrecv: B('events', 'hat', '%0 mesajını alınca', ['t'], ['mesaj1']),
    broadcast: B('events', 'stack', '%0 mesajını yayınla', ['t'], ['mesaj1']),
    broadcastwait: B('events', 'stack', '%0 mesajını yayınla ve bekle', ['t'], ['mesaj1']),

    move: B('motion', 'stack', '%0 adım git', ['n'], [10]),
    turnr: B('motion', 'stack', '↻ %0 derece sağa dön', ['n'], [15]),
    turnl: B('motion', 'stack', '↺ %0 derece sola dön', ['n'], [15]),
    goto: B('motion', 'stack', 'x: %0 y: %1 konumuna git', ['n', 'n'], [0, 0]),
    glide: B('motion', 'stack', '%0 saniyede x: %1 y: %2 konumuna kay', ['n', 'n', 'n'], [1, 0, 0]),
    pointdir: B('motion', 'stack', '%0 yönüne dön', ['n'], [90]),
    changex: B('motion', 'stack', "x'i %0 değiştir", ['n'], [10]),
    setx: B('motion', 'stack', "x'i %0 yap", ['n'], [0]),
    changey: B('motion', 'stack', "y'yi %0 değiştir", ['n'], [10]),
    sety: B('motion', 'stack', "y'yi %0 yap", ['n'], [0]),
    bounce: B('motion', 'stack', 'kenara değince geri dön'),
    xpos: B('motion', 'rep', 'x konumu'), ypos: B('motion', 'rep', 'y konumu'), dirv: B('motion', 'rep', 'yön'),

    say: B('looks', 'stack', '%0 de', ['t'], ['Merhaba!']),
    sayfor: B('looks', 'stack', '%0 de, %1 saniye', ['t', 'n'], ['Merhaba!', 2]),
    show: B('looks', 'stack', 'göster'), hide: B('looks', 'stack', 'gizle'),
    setsize: B('looks', 'stack', 'boyutu %0 % yap', ['n'], [100]),
    changesize: B('looks', 'stack', 'boyutu %0 değiştir', ['n'], [10]),
    nextcostume: B('looks', 'stack', 'sonraki kostüm'),
    sizev: B('looks', 'rep', 'boyut'),

    note: B('sound', 'stack', '%0 notasını %1 saniye çal', ['n', 'n'], [60, 0.5]),

    wait: B('control', 'stack', '%0 saniye bekle', ['n'], [1]),
    repeat: B('control', 'c', '%0 kez tekrarla', ['n'], [10], { s: 1 }),
    forever: B('control', 'c', 'sonsuza kadar', [], [], { s: 1, cap: true }),
    'if': B('control', 'c', 'eğer %0 ise', ['b'], [null], { s: 1 }),
    ifelse: B('control', 'c', 'eğer %0 ise', ['b'], [null], { s: 2 }),
    waituntil: B('control', 'stack', 'şunu bekle: %0', ['b'], [null]),
    repeatuntil: B('control', 'c', 'şuna kadar tekrarla: %0', ['b'], [null], { s: 1 }),
    stopall: B('control', 'stack', 'hepsini durdur', [], [], { cap: true }),

    touchedge: B('sensing', 'bool', 'kenara değiyor mu?'),
    touching: B('sensing', 'bool', '%0 nesnesine değiyor mu?', ['spr'], ['']),
    keypressed: B('sensing', 'bool', '%0 tuşuna basılı mı?', ['key'], ['boşluk']),
    mousedown: B('sensing', 'bool', 'fare basılı mı?'),
    mousex: B('sensing', 'rep', 'fare x'), mousey: B('sensing', 'rep', 'fare y'),
    ask: B('sensing', 'stack', '%0 diye sor ve bekle', ['t'], ['Adın ne?']),
    answer: B('sensing', 'rep', 'cevap'),
    timer: B('sensing', 'rep', 'zamanlayıcı'),
    resettimer: B('sensing', 'stack', 'zamanlayıcıyı sıfırla'),

    add: B('ops', 'rep', '%0 + %1', ['n', 'n'], ['', '']),
    sub: B('ops', 'rep', '%0 - %1', ['n', 'n'], ['', '']),
    mul: B('ops', 'rep', '%0 × %1', ['n', 'n'], ['', '']),
    div: B('ops', 'rep', '%0 / %1', ['n', 'n'], ['', '']),
    mod: B('ops', 'rep', '%0 mod %1', ['n', 'n'], ['', '']),
    rand: B('ops', 'rep', '%0 ile %1 arasında rastgele sayı', ['n', 'n'], [1, 10]),
    gt: B('ops', 'bool', '%0 > %1', ['n', 'n'], ['', 50]),
    lt: B('ops', 'bool', '%0 < %1', ['n', 'n'], ['', 50]),
    eq: B('ops', 'bool', '%0 = %1', ['n', 'n'], ['', 50]),
    and: B('ops', 'bool', '%0 ve %1', ['b', 'b'], [null, null]),
    or: B('ops', 'bool', '%0 veya %1', ['b', 'b'], [null, null]),
    not: B('ops', 'bool', '%0 değil', ['b'], [null]),
    join: B('ops', 'rep', '%0 ve %1 birleşimi', ['t', 't'], ['elma', 'muz']),
    round: B('ops', 'rep', '%0 yuvarla', ['n'], ['']),

    setvar: B('vars', 'stack', '%0 değişkenini %1 yap', ['var', 'n'], ['', 0]),
    changevar: B('vars', 'stack', '%0 değişkenini %1 değiştir', ['var', 'n'], ['', 1]),
    getvar: B('vars', 'rep', '%0', ['fixed'], ['']),

    pendown: B('pen', 'stack', 'kalemi indir'), penup: B('pen', 'stack', 'kalemi kaldır'),
    penclear: B('pen', 'stack', 'her şeyi sil'),
    pencolor: B('pen', 'stack', 'kalem rengi %0 yap', ['col'], ['#0fbd8c']),
    pensize: B('pen', 'stack', 'kalem kalınlığı %0 yap', ['n'], [2]),
    stamp: B('pen', 'stack', 'damga')
  };
  var PALETTE = {
    events: ['whenflag', 'whenkey', 'whenclick', 'whenrecv', 'broadcast', 'broadcastwait'],
    motion: ['move', 'turnr', 'turnl', 'goto', 'glide', 'pointdir', 'changex', 'setx', 'changey', 'sety', 'bounce', 'xpos', 'ypos', 'dirv'],
    looks: ['say', 'sayfor', 'show', 'hide', 'setsize', 'changesize', 'nextcostume', 'sizev'],
    sound: ['note'],
    control: ['wait', 'repeat', 'forever', 'if', 'ifelse', 'waituntil', 'repeatuntil', 'stopall'],
    sensing: ['touchedge', 'touching', 'keypressed', 'mousedown', 'mousex', 'mousey', 'ask', 'answer', 'timer', 'resettimer'],
    ops: ['add', 'sub', 'mul', 'div', 'mod', 'rand', 'gt', 'lt', 'eq', 'and', 'or', 'not', 'join', 'round'],
    vars: ['setvar', 'changevar'],
    pen: ['pendown', 'penup', 'penclear', 'pencolor', 'pensize', 'stamp']
  };
  var KEYS = ['boşluk', 'yukarı ok', 'aşağı ok', 'sol ok', 'sağ ok', 'herhangi'].concat('abcdefghijklmnopqrstuvwxyz0123456789'.split(''));
  var LIB = ['🐱', '🐶', '🐸', '🐵', '🦊', '🐼', '🦄', '🐢', '🐟', '🦋', '🐝', '🚀', '🚗', '✈️', '🤖', '👾', '⭐', '❤️', '⚽', '🍎', '🌳', '🏠', '👻', '🎈'];
  var BACKS = ['#ffffff', '#dff3ff', '#e6f7e6', '#fff3d6', '#2b2d42', '#ffe0ec'];

  /* ---------- Proje durumu ---------- */
  var P, cur = 0, uid = 1;
  function newSprite(emoji, name, x, y) {
    return { id: uid++, name: name, x: x || 0, y: y || 0, dir: 90, size: 100, visible: true, costumes: [emoji], ci: 0, pen: { down: false, color: '#0fbd8c', size: 2 }, scripts: [] };
  }
  function defaultProject() {
    return { v: 1, name: 'Projem', backdrop: '#ffffff', vars: {}, sprites: [newSprite('🐱', 'Nesne1', 0, 0)], sel: 0 };
  }
  function sampleProject() {
    var p = defaultProject(); p.name = 'Örnek proje'; p.vars = { puan: 0 };
    var a = p.sprites[0];
    a.scripts = [{ x: 24, y: 24, blocks: [
      { op: 'whenflag', args: [] }, { op: 'penclear', args: [] }, { op: 'goto', args: [-50, -50] }, { op: 'pendown', args: [] },
      { op: 'repeat', args: [4], subs: [[{ op: 'move', args: [100] }, { op: 'turnr', args: [90] }]] },
      { op: 'penup', args: [] }, { op: 'say', args: ['Kare tamam!'] }] }];
    var r = newSprite('🚀', 'Roket', 150, -120); r.size = 70;
    function key(k, blk, i) { return { x: 24, y: 24 + i * 110, blocks: [{ op: 'whenkey', args: [k] }].concat(blk) }; }
    r.scripts = [key('yukarı ok', [{ op: 'changey', args: [15] }, { op: 'changevar', args: ['puan', 1] }], 0), key('aşağı ok', [{ op: 'changey', args: [-15] }], 1),
      key('sol ok', [{ op: 'changex', args: [-15] }], 2), key('sağ ok', [{ op: 'changex', args: [15] }], 3)];
    p.sprites.push(r);
    return p;
  }
  function normalize(p) {
    if (!p || !Array.isArray(p.sprites) || !p.sprites.length) return defaultProject();
    p.vars = p.vars || {}; p.backdrop = p.backdrop || '#ffffff'; p.name = p.name || 'Projem';
    p.sprites.forEach(function (s) {
      s.id = s.id || uid++; uid = Math.max(uid, s.id + 1);
      s.costumes = (s.costumes && s.costumes.length) ? s.costumes : ['🐱']; s.ci = s.ci || 0;
      s.pen = s.pen || { down: false, color: '#0fbd8c', size: 2 }; s.scripts = s.scripts || [];
      if (s.size == null) s.size = 100; if (s.dir == null) s.dir = 90; if (s.visible == null) s.visible = true;
    });
    p.sel = Math.min(p.sel || 0, p.sprites.length - 1);
    return p;
  }
  function ser() { return JSON.stringify(P, function (k, v) { return k.charAt(0) === '_' ? undefined : v; }); }

  /* ---------- Kayıt, geri alma ---------- */
  var undo = [], last = '', saveTimer = 0;
  function status(t) { $('#saveState').textContent = t; }
  function commit() {
    var now = ser();
    if (now === last) return;
    undo.push(last); if (undo.length > 60) undo.shift();
    P.ts = Date.now(); last = ser(); scheduleSave();
  }
  var serverTimer = 0;
  function scheduleSave() {
    if (NOSAVE) return;
    status('Kaydediliyor…'); clearTimeout(saveTimer); saveTimer = setTimeout(flush, 400);
    if (SERVER && P.sid) { clearTimeout(serverTimer); serverTimer = setTimeout(syncServer, 1500); }
  }
  function body() { return { name: P.name, data: ser(), client_ts: P.ts || Date.now() }; }
  function syncServer() {
    if (NOSAVE || !P.sid) return;
    api('PUT', API + '/' + P.sid, body()).then(function (r) {
      if (r.ok) status('Sunucuya kaydedildi');
      else if (r.status === 404) { P.sid = null; status('Sunucu kaydı bulunamadı; "Kaydet" ile yeniden kaydet'); }
      else if (r.status === 422 && r.j.error === 'name_taken') status('Aynı adlı başka proje var; adı değiştir');
      else status('Sunucuya kaydedilemedi');
    });
  }
  function flush() {
    if (NOSAVE) return;
    try { localStorage.setItem(CUR_KEY, ser()); status('Kaydedildi ' + new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })); }
    catch (e) { status('Kaydedilemedi (depolama dolu)'); }
  }
  function loadState(json) { P = normalize(JSON.parse(json)); cur = P.sel; stopAll(); pen.clearRect(0, 0, 480, 360); refreshAll(); }
  function doUndo() { if (!undo.length) return; var prev = undo.pop(); loadState(prev); P.ts = Date.now(); last = ser(); scheduleSave(); }
  function projectsList() { try { return JSON.parse(localStorage.getItem(LIST_KEY) || '{}'); } catch (e) { return {}; } }

  /* ---------- Yardımcılar ---------- */
  function div(c) { var d = document.createElement('div'); if (c) d.className = c; return d; }
  function clone(o) { return JSON.parse(JSON.stringify(o)); }
  function num(x) { var n = parseFloat(x); return isNaN(n) ? 0 : n; }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function sp() { return P.sprites[cur]; }
  function isRep(b) { var t = BD[b.op].t; return t === 'rep' || t === 'bool'; }
  function makeBlock(op) {
    var d = BD[op], b = { op: op, args: clone(d.d) };
    if (d.t === 'c') { b.subs = []; for (var i = 0; i < d.s; i++) b.subs.push([]); }
    var names = Object.keys(P.vars);
    if (op === 'setvar' || op === 'changevar') b.args[0] = names[0] || '';
    return b;
  }

  /* ---------- Blok çizimi ---------- */
  function menuOptions(type) {
    if (type === 'key') return KEYS;
    if (type === 'spr') return [''].concat(P.sprites.filter(function (s) { return s !== sp(); }).map(function (s) { return s.name; }));
    if (type === 'var') return Object.keys(P.vars);
    return [];
  }
  function fitInput(inp) { inp.style.width = Math.max(2, String(inp.value).length + 1) + 'ch'; }
  function fillLabel(row, b, d, pal) {
    d.l.split(/(%\d)/).forEach(function (part) {
      var m = /^%(\d)$/.exec(part);
      if (!m) { if (part) row.appendChild(document.createTextNode(part)); return; }
      var n = +m[1], type = d.a[n], val = b.args[n], slot = document.createElement('span');
      slot.className = 'slot'; slot._slot = { args: b.args, idx: n, type: type };
      if (type === 'fixed') { slot.className = 'fixed'; slot.textContent = val; }
      else if (val && typeof val === 'object') { slot.appendChild(elBlock(val, b.args, n, d.d[n], true)); }
      else if (type === 'b') { slot.className += ' empty b'; }
      else if (type === 'n' || type === 't') {
        var inp = document.createElement('input'); inp.className = 'in'; inp.value = val == null ? '' : val;
        if (type === 'n') inp.setAttribute('inputmode', 'decimal');
        fitInput(inp); inp.disabled = !!pal;
        inp.addEventListener('input', function () { fitInput(inp); b.args[n] = inp.value; });
        inp.addEventListener('change', function () { b.args[n] = inp.value; commit(); });
        slot.appendChild(inp);
      } else if (type === 'col') {
        var c = document.createElement('input'); c.type = 'color'; c.value = val || '#0fbd8c'; c.disabled = !!pal;
        c.addEventListener('change', function () { b.args[n] = c.value; commit(); });
        slot.appendChild(c);
      } else {
        var sel = document.createElement('select'); sel.disabled = !!pal;
        var opts = menuOptions(type); if (val && opts.indexOf(val) < 0) opts = opts.concat([val]);
        if (!opts.length) opts = ['(yok)'];
        opts.forEach(function (o) { var op = document.createElement('option'); op.value = o; op.textContent = o === '' ? '(seç)' : o; sel.appendChild(op); });
        sel.value = val == null ? opts[0] : val;
        if (!pal && (val == null || val === '')) b.args[n] = sel.value === '(yok)' ? '' : sel.value;
        sel.addEventListener('change', function () { b.args[n] = sel.value; commit(); });
        slot.appendChild(sel); slot.className += ' menu';
      }
      row.appendChild(slot);
    });
  }
  function elBlock(b, arr, idx, dv, isArg, pal) {
    var d = BD[b.op], el = div('blk ' + d.c + ' ' + d.t), row = div('row');
    el._ref = { arr: arr, idx: idx, b: b, dv: dv, isArg: !!isArg };
    el.appendChild(row); fillLabel(row, b, d, pal);
    if (d.t === 'c') {
      for (var i = 0; i < d.s; i++) {
        if (i === 1) { var mid = div('row mid'); mid.textContent = 'değilse'; el.appendChild(mid); }
        var sub = div('sub'); sub._sub = { arr: b.subs[i] };
        b.subs[i].forEach(function (cb, k) { sub.appendChild(elBlock(cb, sub._sub.arr, k)); });
        el.appendChild(sub);
      }
      el.appendChild(div('cfoot'));
    }
    return el;
  }
  function renderScripts() {
    var area = $('#scripts'); area.innerHTML = '';
    area.classList.toggle('empty', !sp().scripts.length);
    sp().scripts.forEach(function (s) {
      var d = div('script'); d.style.left = s.x + 'px'; d.style.top = s.y + 'px'; d._script = s;
      s.blocks.forEach(function (b, i) { d.appendChild(elBlock(b, s.blocks, i)); });
      area.appendChild(d);
    });
  }

  /* ---------- Palet ---------- */
  var catSel = 'events';
  function renderCats() {
    var nav = $('#cats'); nav.innerHTML = '';
    CATS.forEach(function (c) {
      var b = document.createElement('button'); b.className = c[0] === catSel ? 'on' : '';
      b.style.borderLeftColor = c[0] === catSel ? c[2] : 'transparent';
      b.innerHTML = '<i style="background:' + c[2] + '"></i>' + c[1];
      b.onclick = function () { catSel = c[0]; renderCats(); renderPalette(); };
      nav.appendChild(b);
    });
  }
  function palBlock(b) { var el = elBlock(b, null, 0, null, false, true); el._ref = { pal: true, op: b.op, name: b.args && b.op === 'getvar' ? b.args[0] : null }; return el; }
  function renderPalette() {
    var pal = $('#palette'); pal.innerHTML = '';
    PALETTE[catSel].forEach(function (op) { pal.appendChild(palBlock(makeBlock(op))); });
    if (catSel === 'vars') {
      var nb = document.createElement('button'); nb.textContent = 'Değişken oluştur';
      nb.onclick = function () {
        var n = (prompt('Değişken adı:') || '').trim(); if (!n) return;
        if (P.vars.hasOwnProperty(n)) { alert('Bu adda bir değişken var.'); return; }
        P.vars[n] = 0; commit(); renderPalette(); renderScripts();
      };
      pal.insertBefore(nb, pal.firstChild);
      Object.keys(P.vars).forEach(function (n) {
        var wrap = div(''); wrap.style.cssText = 'display:flex;gap:6px;align-items:center';
        var b = makeBlock('getvar'); b.args[0] = n; wrap.appendChild(palBlock(b));
        var x = document.createElement('button'); x.textContent = '✕'; x.title = 'Değişkeni sil'; x.style.padding = '0 6px';
        x.onclick = function () { if (confirm(n + ' değişkeni silinsin mi?')) { delete P.vars[n]; commit(); renderPalette(); renderScripts(); } };
        wrap.appendChild(x); pal.appendChild(wrap);
      });
    }
  }

  /* ---------- Sürükle-bırak ---------- */
  var drag = null, hlEl = null;
  function onDown(e) {
    if (e.button > 0) return;
    if (e.target.closest('input,select,button')) return;
    var el = e.target.closest('.blk'); if (!el || !el._ref) return;
    e.preventDefault();
    drag = { el: el, sx: e.clientX, sy: e.clientY, started: false };
    window.addEventListener('pointermove', onMove);
    window.addEventListener('pointerup', onUp, { once: true });
    window.addEventListener('pointercancel', onUp, { once: true });
  }
  function begin(e) {
    var ref = drag.el._ref, r = drag.el.getBoundingClientRect(), stack;
    drag.ox = drag.sx - r.left; drag.oy = drag.sy - r.top; drag.started = true;
    if (ref.pal) {
      stack = [makeBlock(ref.op)]; if (ref.name != null) stack[0].args[0] = ref.name;
    } else if (ref.isArg) {
      stack = [ref.b]; ref.arr[ref.idx] = ref.dv == null ? (BD[parentOpOf(ref)] ? null : '') : ref.dv;
    } else {
      stack = ref.arr.splice(ref.idx);
      var s = sp(); s.scripts = s.scripts.filter(function (x) { return x.blocks.length; });
    }
    drag.stack = stack;
    renderScripts();
    var g = $('#ghost'); g.innerHTML = '';
    var w = div('script'); stack.forEach(function (b, i) { w.appendChild(elBlock(b, stack, i)); }); g.appendChild(w);
    placeGhost(e);
  }
  function parentOpOf() { return ''; }
  function placeGhost(e) { var g = $('#ghost'); g.style.left = (e.clientX - drag.ox) + 'px'; g.style.top = (e.clientY - drag.oy) + 'px'; }
  function onMove(e) {
    if (!drag) return;
    if (!drag.started) { if (Math.hypot(e.clientX - drag.sx, e.clientY - drag.sy) < 5) return; begin(e); }
    placeGhost(e); drag.target = findTarget(e); showTarget(drag.target);
  }
  function inRect(r, x, y) { return x >= r.left && x <= r.right && y >= r.top && y <= r.bottom; }
  function findTarget(e) {
    var stack = drag.stack, first = BD[stack[0].op], px = e.clientX, py = e.clientY;
    var gx = px - drag.ox, gy = py - drag.oy;
    if (inRect($('#palwrap').getBoundingClientRect(), px, py)) return { type: 'delete' };
    var area = $('#scripts'), ar = area.getBoundingClientRect();
    if (!inRect(ar, px, py)) return { type: 'free', x: 20, y: 20 };
    if (first.t === 'rep' || first.t === 'bool') {
      var best = null, bd = 30;
      area.querySelectorAll('.slot').forEach(function (s) {
        var sl = s._slot; if (!sl || sl.type === 'fixed') return;
        if (['n', 't', 'b'].indexOf(sl.type) < 0) return;
        if (sl.type === 'b' && first.t !== 'bool') return;
        var r = s.getBoundingClientRect(), dist = inRect(r, px, py) ? 0 : Math.hypot(r.left - gx, r.top + r.height / 2 - (gy + 10));
        if (dist < bd) { bd = dist; best = s; }
      });
      if (best) return { type: 'slot', el: best };
      return { type: 'free', x: gx - ar.left + area.scrollLeft, y: gy - ar.top + area.scrollTop };
    }
    var lastB = BD[stack[stack.length - 1].op], endsCap = !!lastB.cap, cands = [];
    area.querySelectorAll('.blk').forEach(function (el) {
      var ref = el._ref; if (!ref || ref.isArg) return;
      var t = BD[ref.b.op].t; if (t === 'rep' || t === 'bool') return;
      var r = el.getBoundingClientRect();
      if (first.t !== 'hat' && !BD[ref.b.op].cap) {
        var atEnd = ref.idx === ref.arr.length - 1;
        if (!(endsCap && !atEnd)) cands.push({ x: r.left, y: r.bottom, arr: ref.arr, idx: ref.idx + 1, w: r.width });
      }
      if (ref.idx === 0 && ref.arr === findScriptArr(el) && t !== 'hat' && first.t !== 'rep' && !endsCap) cands.push({ x: r.left, y: r.top, arr: ref.arr, idx: 0, w: r.width });
      if (first.t === 'hat' && ref.idx === 0 && t !== 'hat' && ref.arr === findScriptArr(el)) cands.push({ x: r.left, y: r.top, arr: ref.arr, idx: 0, w: r.width });
    });
    if (first.t !== 'hat') area.querySelectorAll('.sub').forEach(function (s) {
      var r = s.getBoundingClientRect(); if (!(endsCap && s._sub.arr.length)) cands.push({ x: r.left, y: r.top, arr: s._sub.arr, idx: 0, w: r.width });
    });
    var pick = null, pd = 42;
    cands.forEach(function (c) { var dd = Math.hypot(c.x - gx, c.y - gy); if (dd < pd) { pd = dd; pick = c; } });
    if (pick) return { type: 'stack', c: pick };
    return { type: 'free', x: gx - ar.left + area.scrollLeft, y: gy - ar.top + area.scrollTop };
  }
  function findScriptArr(el) { var s = el.closest('.script'); return s && s._script ? s._script.blocks : null; }
  function showTarget(t) {
    var ind = $('#indicator');
    if (hlEl) { hlEl.classList.remove('hl'); hlEl = null; }
    ind.style.display = 'none';
    if (t.type === 'slot') { hlEl = t.el; hlEl.classList.add('hl'); }
    else if (t.type === 'stack') { ind.style.display = 'block'; ind.style.left = t.c.x + 'px'; ind.style.top = (t.c.y - 2) + 'px'; ind.style.width = Math.max(60, t.c.w) + 'px'; }
  }
  function onUp(e) {
    window.removeEventListener('pointermove', onMove);
    var d = drag; drag = null; if (!d) return;
    if (hlEl) { hlEl.classList.remove('hl'); hlEl = null; }
    $('#indicator').style.display = 'none';
    if (!d.started) { clickBlock(d.el); return; }
    $('#ghost').innerHTML = '';
    var t = d.target || { type: 'free', x: 20, y: 20 };
    if (t.type === 'slot') {
      var sl = t.el._slot; sl.args[sl.idx] = d.stack[0];
    } else if (t.type === 'stack') {
      var spl = [t.c.idx, 0].concat(d.stack); Array.prototype.splice.apply(t.c.arr, spl);
    } else if (t.type === 'free') {
      sp().scripts.push({ x: Math.max(4, Math.round(t.x)), y: Math.max(4, Math.round(t.y)), blocks: d.stack });
    }
    commit(); renderScripts();
  }
  function clickBlock(el) {
    var ref = el._ref;
    if (ref.pal) {
      var b = makeBlock(ref.op); if (ref.name != null) b.args[0] = ref.name;
      runOnce(sp(), b); return;
    }
    var scr = findScriptArr(el);
    if (ref.isArg || (scr && isRep(ref.b))) { runOnce(sp(), ref.b); return; }
    if (scr) spawn(sp(), scr, null);
  }
  function runOnce(s, b) {
    if (isRep(b)) { s._say = String(ev(b, s, {})); s._sayUntil = performance.now() + 2500; return; }
    spawn(s, [b], null);
  }

  /* ---------- Yorumlayıcı ---------- */
  var threads = [], pressed = {}, mouse = { x: 0, y: 0, down: false }, answerText = '', t0 = performance.now(), audio = null;
  function spawn(s, blocks, owner) {
    if (owner) threads.forEach(function (t) { if (t.owner === owner) t.dead = true; });
    var th = { sp: s, owner: owner, dead: false, until: 0 };
    th.g = runList(blocks, s, th); threads.push(th); return th;
  }
  function stopAll() { threads.forEach(function (t) { t.dead = true; }); threads = []; P && P.sprites.forEach(function (s) { s._say = null; }); }
  function startHats(pred) {
    P.sprites.forEach(function (s) {
      s.scripts.forEach(function (sc) { var h = sc.blocks[0]; if (h && BD[h.op].t === 'hat' && pred(h, s)) spawn(s, sc.blocks.slice(1), sc); });
    });
  }
  function findSprite(name) { return P.sprites.filter(function (s) { return s.name === name; })[0]; }
  function radius(s) { return 24 * s.size / 100; }
  function truthy(v) { return v === true || (typeof v === 'string' && v !== '' && v !== 'false') || (typeof v === 'number' && v !== 0); }
  function ev(a, s, th) {
    if (!(a && typeof a === 'object')) return a;
    var A = a.args || [];
    function v(i) { return ev(A[i], s, th); }
    switch (a.op) {
      case 'xpos': return Math.round(s.x); case 'ypos': return Math.round(s.y); case 'dirv': return Math.round(s.dir); case 'sizev': return Math.round(s.size);
      case 'add': return num(v(0)) + num(v(1)); case 'sub': return num(v(0)) - num(v(1)); case 'mul': return num(v(0)) * num(v(1));
      case 'div': return num(v(1)) === 0 ? 0 : num(v(0)) / num(v(1));
      case 'mod': var m = num(v(1)); return m === 0 ? 0 : ((num(v(0)) % m) + m) % m;
      case 'rand': var lo = Math.min(num(v(0)), num(v(1))), hi = Math.max(num(v(0)), num(v(1)));
        return (Math.floor(lo) === lo && Math.floor(hi) === hi) ? lo + Math.floor(Math.random() * (hi - lo + 1)) : lo + Math.random() * (hi - lo);
      case 'gt': return num(v(0)) > num(v(1)); case 'lt': return num(v(0)) < num(v(1));
      case 'eq': var x = v(0), y = v(1); return (x !== '' && y !== '' && !isNaN(x) && !isNaN(y)) ? num(x) === num(y) : String(x).toLowerCase() === String(y).toLowerCase();
      case 'and': return truthy(v(0)) && truthy(v(1)); case 'or': return truthy(v(0)) || truthy(v(1)); case 'not': return !truthy(v(0));
      case 'join': return String(v(0)) + String(v(1));
      case 'round': return Math.round(num(v(0)));
      case 'getvar': return P.vars.hasOwnProperty(A[0]) ? P.vars[A[0]] : 0;
      case 'touchedge': var r = radius(s); return s.x > 240 - r || s.x < -240 + r || s.y > 180 - r || s.y < -180 + r;
      case 'touching': var o = findSprite(A[0]); return !!(o && o !== s && o.visible && s.visible && Math.hypot(o.x - s.x, o.y - s.y) < radius(o) + radius(s));
      case 'keypressed': return A[0] === 'herhangi' ? Object.keys(pressed).some(function (k) { return pressed[k]; }) : !!pressed[A[0]];
      case 'mousedown': return mouse.down; case 'mousex': return Math.round(mouse.x); case 'mousey': return Math.round(mouse.y);
      case 'answer': return answerText; case 'timer': return Math.round((performance.now() - t0) / 100) / 10;
    }
    return 0;
  }
  function norm(d) { d = ((d + 180) % 360 + 360) % 360 - 180; return d; }
  function setPos(s, x, y) {
    var ox = s.x, oy = s.y; s.x = clamp(x, -300, 300); s.y = clamp(y, -240, 240);
    if (s.pen.down) { pen.strokeStyle = s.pen.color; pen.lineWidth = s.pen.size; pen.lineCap = 'round'; pen.beginPath(); pen.moveTo(240 + ox, 180 - oy); pen.lineTo(240 + s.x, 180 - s.y); pen.stroke(); }
  }
  function beep(note, secs) {
    try {
      audio = audio || new (window.AudioContext || window.webkitAudioContext)();
      var o = audio.createOscillator(), g = audio.createGain();
      o.frequency.value = 440 * Math.pow(2, (note - 69) / 12); g.gain.value = 0.15;
      o.connect(g); g.connect(audio.destination); o.start(); o.stop(audio.currentTime + secs);
    } catch (e) {}
  }
  function* runList(list, s, th) {
    for (var i = 0; i < list.length; i++) { if (th.dead) return; yield* exec(list[i], s, th); }
  }
  function* exec(b, s, th) {
    var A = b.args || [];
    function v(i) { return ev(A[i], s, th); }
    switch (b.op) {
      case 'move': var st = num(v(0)), rd = s.dir * Math.PI / 180; setPos(s, s.x + st * Math.sin(rd), s.y + st * Math.cos(rd)); break;
      case 'turnr': s.dir = norm(s.dir + num(v(0))); break;
      case 'turnl': s.dir = norm(s.dir - num(v(0))); break;
      case 'goto': setPos(s, num(v(0)), num(v(1))); break;
      case 'glide':
        var secs = Math.max(0, num(v(0))), sx = s.x, sy = s.y, tx = num(v(1)), ty = num(v(2)), st0 = performance.now();
        while (!th.dead) { var f = secs ? Math.min(1, (performance.now() - st0) / (secs * 1000)) : 1; setPos(s, sx + (tx - sx) * f, sy + (ty - sy) * f); if (f >= 1) break; yield 0; }
        break;
      case 'pointdir': s.dir = norm(num(v(0))); break;
      case 'changex': setPos(s, s.x + num(v(0)), s.y); break;
      case 'setx': setPos(s, num(v(0)), s.y); break;
      case 'changey': setPos(s, s.x, s.y + num(v(0))); break;
      case 'sety': setPos(s, s.x, num(v(0))); break;
      case 'bounce':
        var r = radius(s);
        if (s.x > 240 - r || s.x < -240 + r) s.dir = norm(-s.dir);
        if (s.y > 180 - r || s.y < -180 + r) s.dir = norm(180 - s.dir);
        s.x = clamp(s.x, -240 + r, 240 - r); s.y = clamp(s.y, -180 + r, 180 - r); break;
      case 'say': s._say = String(v(0)); s._sayUntil = 0; break;
      case 'sayfor': s._say = String(v(0)); s._sayUntil = 0; yield { w: num(v(1)) * 1000 }; s._say = null; break;
      case 'show': s.visible = true; break; case 'hide': s.visible = false; break;
      case 'setsize': s.size = clamp(num(v(0)), 5, 500); break;
      case 'changesize': s.size = clamp(s.size + num(v(0)), 5, 500); break;
      case 'nextcostume': s.ci = (s.ci + 1) % s.costumes.length; break;
      case 'note': var nt = num(v(0)), dur = Math.max(0.05, num(v(1))); beep(nt, dur); yield { w: dur * 1000 }; break;
      case 'whenflag': case 'whenkey': case 'whenclick': case 'whenrecv': break;
      case 'broadcast': startHats(function (h) { return h.op === 'whenrecv' && String(h.args[0]).toLowerCase() === String(v(0)).toLowerCase(); }); break;
      case 'broadcastwait':
        var msg = String(v(0)).toLowerCase(), before = threads.length;
        startHats(function (h) { return h.op === 'whenrecv' && String(h.args[0]).toLowerCase() === msg; });
        var started = threads.slice(before);
        while (!th.dead && started.some(function (t) { return !t.dead; })) yield 0;
        break;
      case 'wait': yield { w: num(v(0)) * 1000 }; break;
      case 'repeat':
        var n = Math.floor(num(v(0)));
        for (var i = 0; i < n && !th.dead; i++) { yield* runList(b.subs[0], s, th); yield 0; }
        break;
      case 'forever': while (!th.dead) { yield* runList(b.subs[0], s, th); yield 0; } break;
      case 'if': if (truthy(v(0))) yield* runList(b.subs[0], s, th); break;
      case 'ifelse': yield* runList(truthy(v(0)) ? b.subs[0] : b.subs[1], s, th); break;
      case 'waituntil': while (!th.dead && !truthy(v(0))) yield 0; break;
      case 'repeatuntil': while (!th.dead && !truthy(v(0))) { yield* runList(b.subs[0], s, th); yield 0; } break;
      case 'stopall': stopAll(); th.dead = true; return;
      case 'ask': var ans = window.prompt(String(v(0)), ''); answerText = ans == null ? '' : ans; break;
      case 'resettimer': t0 = performance.now(); break;
      case 'setvar': if (A[0] !== '') P.vars[A[0]] = ev(A[1], s, th); break;
      case 'changevar': if (A[0] !== '') P.vars[A[0]] = num(P.vars[A[0]]) + num(v(1)); break;
      case 'pendown': s.pen.down = true; break; case 'penup': s.pen.down = false; break;
      case 'penclear': pen.clearRect(0, 0, 480, 360); break;
      case 'pencolor': s.pen.color = String(v(0)) || '#0fbd8c'; break;
      case 'pensize': s.pen.size = clamp(num(v(0)), 1, 60); break;
      case 'stamp': drawSprite(pen, s, true); break;
    }
  }

  /* ---------- Sahne ---------- */
  var cv = $('#stage'), ctx = cv.getContext('2d');
  var penCv = document.createElement('canvas'); penCv.width = 480; penCv.height = 360; var pen = penCv.getContext('2d');
  function drawSprite(c, s, force) {
    if (!s.visible && !force) return;
    c.save(); c.translate(240 + s.x, 180 - s.y); c.rotate((s.dir - 90) * Math.PI / 180);
    c.font = (48 * s.size / 100) + 'px "Segoe UI Emoji","Apple Color Emoji",sans-serif'; c.textAlign = 'center'; c.textBaseline = 'middle';
    c.fillText(s.costumes[s.ci % s.costumes.length], 0, 2); c.restore();
  }
  function drawBubble(s) {
    var t = s._say; if (!t || !s.visible) return;
    ctx.font = '14px system-ui,sans-serif'; var w = Math.min(220, ctx.measureText(t).width + 18), x = 240 + s.x + radius(s) * .4, y = 180 - s.y - radius(s) - 30;
    x = clamp(x, 4, 476 - w); y = Math.max(4, y);
    ctx.fillStyle = '#fff'; ctx.strokeStyle = '#8a93a6'; ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.roundRect ? ctx.roundRect(x, y, w, 26, 10) : ctx.rect(x, y, w, 26); ctx.fill(); ctx.stroke();
    ctx.fillStyle = '#2b2d42'; ctx.textAlign = 'left'; ctx.textBaseline = 'middle'; ctx.fillText(t, x + 9, y + 13, w - 14);
  }
  function drawStage() {
    ctx.fillStyle = P.backdrop; ctx.fillRect(0, 0, 480, 360); ctx.drawImage(penCv, 0, 0);
    P.sprites.forEach(function (s) { drawSprite(ctx, s); });
    P.sprites.forEach(drawBubble);
    var mon = $('#monitors'), keys = Object.keys(P.vars), html = keys.map(function (k) { return '<span>' + esc(k) + ': ' + esc(P.vars[k]) + '</span>'; }).join('');
    if (mon._h !== html) { mon.innerHTML = html; mon._h = html; }
  }
  function esc(t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function stagePos(e) { var r = cv.getBoundingClientRect(); return { x: (e.clientX - r.left) / r.width * 480 - 240, y: 180 - (e.clientY - r.top) / r.height * 360 }; }
  cv.addEventListener('pointermove', function (e) { var p = stagePos(e); mouse.x = p.x; mouse.y = p.y; });
  cv.addEventListener('pointerdown', function (e) {
    mouse.down = true; var p = stagePos(e); mouse.x = p.x; mouse.y = p.y;
    for (var i = P.sprites.length - 1; i >= 0; i--) {
      var s = P.sprites[i]; if (s.visible && Math.hypot(s.x - p.x, s.y - p.y) < radius(s) + 4) {
        startHats(function (h, sx) { return h.op === 'whenclick' && sx === s; }); break;
      }
    }
  });
  window.addEventListener('pointerup', function () { mouse.down = false; });

  var lastTick = 0, acc = 0;
  function frame(ts) {
    var dt = ts - (lastTick || ts); lastTick = ts; acc += dt;
    var steps = 0;
    while (acc >= 33 && steps < 3) { acc -= 33; steps++; tick(); }
    if (steps === 3) acc = 0;
    var now = performance.now();
    P.sprites.forEach(function (s) { if (s._sayUntil && now > s._sayUntil) { s._say = null; s._sayUntil = 0; } });
    drawStage(); syncProps();
    requestAnimationFrame(frame);
  }
  function tick() {
    var now = performance.now(), list = threads.slice();
    list.forEach(function (th) {
      if (th.dead || (th.until && now < th.until)) return; th.until = 0;
      var r; try { r = th.g.next(); } catch (e) { th.dead = true; return; }
      if (r.done) th.dead = true; else if (r.value && r.value.w) th.until = now + r.value.w;
    });
    threads = threads.filter(function (t) { return !t.dead; });
  }

  /* ---------- Klavye ---------- */
  function keyName(e) {
    if (e.key === ' ') return 'boşluk';
    var m = { ArrowUp: 'yukarı ok', ArrowDown: 'aşağı ok', ArrowLeft: 'sol ok', ArrowRight: 'sağ ok' }; if (m[e.key]) return m[e.key];
    var k = e.key.toLowerCase(); return /^[a-z0-9]$/.test(k) ? k : null;
  }
  window.addEventListener('keydown', function (e) {
    if (e.target.closest && e.target.closest('input,select,textarea')) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') { e.preventDefault(); doUndo(); return; }
    var k = keyName(e); if (!k) return;
    if (['boşluk', 'yukarı ok', 'aşağı ok', 'sol ok', 'sağ ok'].indexOf(k) >= 0) e.preventDefault();
    var first = !pressed[k]; pressed[k] = true;
    if (first) startHats(function (h) { return h.op === 'whenkey' && (h.args[0] === k || h.args[0] === 'herhangi'); });
    else startHats(function (h) { return h.op === 'whenkey' && h.args[0] === k && !threads.some(function (t) { return t.owner && t.owner.blocks[0] === h; }); });
  });
  window.addEventListener('keyup', function (e) { var k = keyName(e); if (k) pressed[k] = false; });
  window.addEventListener('blur', function () { pressed = {}; });

  /* ---------- Nesne paneli ---------- */
  function renderSprites() {
    var l = $('#spriteList'); l.innerHTML = '';
    P.sprites.forEach(function (s, i) {
      var b = document.createElement('button'); b.className = i === cur ? 'on' : '';
      b.innerHTML = '<b>' + esc(s.costumes[s.ci % s.costumes.length]) + '</b>' + esc(s.name);
      b.onclick = function () { cur = i; P.sel = i; refreshAll(); scheduleSave(); };
      l.appendChild(b);
    });
    $('#curName').textContent = sp().name;
    $('#btnDelSprite').disabled = P.sprites.length < 2;
  }
  function renderBackdrops() {
    var d = $('#backdrops'); d.innerHTML = '';
    BACKS.forEach(function (c) {
      var b = document.createElement('button'); b.style.background = c; b.className = c === P.backdrop ? 'on' : '';
      b.onclick = function () { P.backdrop = c; commit(); renderBackdrops(); }; d.appendChild(b);
    });
  }
  var propIds = { pName: 'name', pX: 'x', pY: 'y', pSize: 'size', pDir: 'dir' };
  function syncProps() {
    var s = sp(), act = document.activeElement;
    Object.keys(propIds).forEach(function (id) {
      var el = $('#' + id); if (el === act) return; var v = s[propIds[id]]; if (typeof v === 'number') v = Math.round(v); if (el.value !== String(v)) el.value = v;
    });
    if ($('#pShow') !== act) $('#pShow').checked = s.visible;
  }
  Object.keys(propIds).forEach(function (id) {
    $('#' + id).addEventListener('change', function () {
      var s = sp(), k = propIds[id], v = this.value;
      if (k === 'name') { v = v.trim() || s.name; s.name = v; renderSprites(); renderScripts(); }
      else if (k === 'size') s.size = clamp(num(v), 5, 500);
      else if (k === 'dir') s.dir = norm(num(v));
      else s[k] = clamp(num(v), -300, 300);
      commit();
    });
  });
  $('#pShow').addEventListener('change', function () { sp().visible = this.checked; commit(); });

  /* ---------- Pencereler ---------- */
  function openModal(title, node) { $('#mTitle').textContent = title; var b = $('#mBody'); b.innerHTML = ''; b.appendChild(node); $('#modal').hidden = false; }
  function closeModal() { $('#modal').hidden = true; }
  $('#mClose').onclick = closeModal;
  $('#modal').addEventListener('pointerdown', function (e) { if (e.target === this) closeModal(); });
  function library(title, pick) {
    var g = div('lib');
    LIB.forEach(function (em) { var b = document.createElement('button'); b.textContent = em; b.onclick = function () { pick(em); closeModal(); }; g.appendChild(b); });
    openModal(title, g);
  }
  $('#btnAddSprite').onclick = function () {
    library('Nesne seç', function (em) {
      var s = newSprite(em, 'Nesne' + (P.sprites.length + 1), Math.round(Math.random() * 200 - 100), Math.round(Math.random() * 160 - 80));
      P.sprites.push(s); cur = P.sprites.length - 1; P.sel = cur; commit(); refreshAll();
    });
  };
  $('#btnAddCostume').onclick = function () { library('Kostüm ekle', function (em) { var s = sp(); s.costumes.push(em); s.ci = s.costumes.length - 1; commit(); renderSprites(); }); };
  $('#btnNextCostume').onclick = function () { var s = sp(); s.ci = (s.ci + 1) % s.costumes.length; commit(); renderSprites(); };
  $('#btnDelSprite').onclick = function () {
    if (P.sprites.length < 2 || !confirm(sp().name + ' nesnesi silinsin mi?')) return;
    P.sprites.splice(cur, 1); cur = Math.max(0, cur - 1); P.sel = cur; commit(); refreshAll();
  };

  /* ---------- Üst çubuk ---------- */
  $('#projName').addEventListener('change', function () { P.name = this.value.trim() || 'Projem'; commit(); });
  $('#btnFlag').onclick = function () { stopAll(); startHats(function (h) { return h.op === 'whenflag'; }); };
  $('#btnStop').onclick = stopAll;
  $('#btnUndo').onclick = doUndo;
  $('#btnClean').onclick = function () {
    var y = 20; sp().scripts.forEach(function (sc) { sc.x = 20; sc.y = y; y += 40 + sc.blocks.length * 36 + 40; });
    renderScripts();
    var els = $('#scripts').children, yy = 20;
    sp().scripts.forEach(function (sc, i) { sc.y = yy; els[i].style.top = yy + 'px'; yy += els[i].offsetHeight + 24; });
    commit();
  };
  function resetWith(p, sid) { stopAll(); P = normalize(p); P.sid = sid || null; cur = P.sel; pen.clearRect(0, 0, 480, 360); undo = []; last = ser(); refreshAll(); scheduleSave(); }
  function saveToServer() {
    status('Kaydediliyor…');
    (P.sid ? api('PUT', API + '/' + P.sid, body()) : api('POST', API, body())).then(function (r) {
      if (r.ok) { P.sid = r.j.id; last = ser(); flush(); status('"' + P.name + '" sunucuya kaydedildi'); }
      else if (r.status === 422 && r.j.error === 'limit') showLimit(r.j.projects || [], r.j.limit || MAXP);
      else if (r.status === 422 && r.j.error === 'name_taken') { status('Kaydedilemedi'); alert('Bu adda başka bir projen var. Proje adını değiştirip tekrar dene.'); }
      else if (r.status === 413) { status('Kaydedilemedi'); alert('Proje çok büyük (en fazla 1 MB).'); }
      else { status('Sunucuya kaydedilemedi'); alert('Sunucuya kaydedilemedi. Bağlantını kontrol edip tekrar dene; proje bu tarayıcıda kayıtlı kalır.'); }
    });
  }
  /* 10 proje dolu: en eski güncellenenleri silmeyi öner, yer açılınca kaydı otomatik tamamla. */
  function showLimit(projects, limit) {
    var box = div('plist'), msg = div(''), sorted = projects.slice().sort(function (a, b) { return String(a.updated_at).localeCompare(String(b.updated_at)); });
    msg.innerHTML = '<b>En fazla ' + limit + ' proje saklayabilirsin.</b> Yeni projeyi kaydetmek için aşağıdakilerden birkaçını sil. En uzun süredir açmadıkların önerilenler olarak işaretlendi.';
    box.appendChild(msg);
    sorted.forEach(function (p, i) {
      var it = div('pitem'), s0 = document.createElement('span'), d = document.createElement('button');
      s0.innerHTML = esc(p.name) + (i < 3 ? ' <b style="color:#b45309">(önerilen)</b>' : '') + '<br><small>' + new Date(p.updated_at).toLocaleString('tr-TR') + '</small>';
      d.textContent = 'Sil'; d.className = 'danger';
      d.onclick = function () {
        if (!confirm('"' + p.name + '" projesi kalıcı olarak silinsin mi?')) return;
        api('DELETE', API + '/' + p.id).then(function (r) {
          if (!r.ok) { alert('Silinemedi.'); return; }
          if (P.sid === p.id) P.sid = null;
          if ((r.j.projects || []).length < limit) { closeModal(); saveToServer(); } else showLimit(r.j.projects, limit);
        });
      };
      it.appendChild(s0); it.appendChild(d); box.appendChild(it);
    });
    openModal('Proje sınırına ulaştın (' + sorted.length + '/' + limit + ')', box);
  }
  $('#btnNew').onclick = function () { if (confirm('Yeni proje açılsın mı? Şu anki proje otomatik kaydedilmiş olarak kalır; adıyla saklamak için önce "Kaydet"e bas.')) resetWith(defaultProject()); };
  $('#btnSample').onclick = function () { if (confirm('Örnek proje açılsın mı?')) resetWith(sampleProject()); };
  $('#btnSave').onclick = function () {
    if (NOSAVE) return;
    if (SERVER) { saveToServer(); return; }
    var list = projectsList(), name = P.name; list[name] = { saved: Date.now(), data: ser() };
    try { localStorage.setItem(LIST_KEY, JSON.stringify(list)); flush(); status('"' + name + '" kaydedildi'); } catch (e) { status('Kaydedilemedi'); }
  };
  function serverProjects() {
    var box = div('plist'); box.textContent = 'Yükleniyor…'; openModal('Projelerim', box);
    api('GET', API).then(function (r) {
      box.textContent = '';
      if (!r.ok) { box.textContent = 'Projeler alınamadı. Bağlantını kontrol et.'; return; }
      var list = (r.j.projects || []).slice().reverse(), lim = r.j.limit || MAXP, head = div('');
      head.innerHTML = '<b>' + list.length + ' / ' + lim + '</b> proje kayıtlı.' + (list.length >= lim ? ' Yenisini kaydetmek için birkaçını silmen gerekir.' : '');
      box.appendChild(head);
      if (!list.length) box.appendChild(document.createTextNode(' Henüz kayıtlı proje yok. "Kaydet" ile projeni sunucuya kaydet.'));
      list.forEach(function (p) {
        var it = div('pitem'), s0 = document.createElement('span'), o = document.createElement('button'), d = document.createElement('button');
        s0.innerHTML = esc(p.name) + (p.id === P.sid ? ' <small>(açık)</small>' : '') + '<br><small>' + new Date(p.updated_at).toLocaleString('tr-TR') + '</small>';
        o.textContent = 'Aç';
        o.onclick = function () { api('GET', API + '/' + p.id).then(function (g) { if (g.ok) { resetWith(g.j.data, p.id); closeModal(); } else alert('Proje açılamadı.'); }); };
        d.textContent = 'Sil'; d.className = 'danger';
        d.onclick = function () {
          if (!confirm('"' + p.name + '" projesi kalıcı olarak silinsin mi?')) return;
          api('DELETE', API + '/' + p.id).then(function (x) { if (x.ok) { if (P.sid === p.id) P.sid = null; serverProjects(); } else alert('Silinemedi.'); });
        };
        it.appendChild(s0); it.appendChild(o); it.appendChild(d); box.appendChild(it);
      });
    });
  }
  $('#btnProjects').onclick = function () {
    if (SERVER) { serverProjects(); return; }
    var list = projectsList(), names = Object.keys(list), box = div('plist');
    if (!names.length) box.textContent = 'Henüz kaydedilmiş proje yok. "Kaydet" ile projeni adıyla sakla.';
    names.sort(function (a, b) { return list[b].saved - list[a].saved; }).forEach(function (n) {
      var it = div('pitem'), sp0 = document.createElement('span'), o = document.createElement('button'), d = document.createElement('button');
      sp0.innerHTML = esc(n) + '<br><small>' + new Date(list[n].saved).toLocaleString('tr-TR') + '</small>';
      o.textContent = 'Aç'; o.onclick = function () { resetWith(JSON.parse(list[n].data)); closeModal(); };
      d.textContent = 'Sil'; d.className = 'danger'; d.onclick = function () { if (!confirm(n + ' silinsin mi?')) return; delete list[n]; localStorage.setItem(LIST_KEY, JSON.stringify(list)); $('#btnProjects').click(); };
      it.appendChild(sp0); it.appendChild(o); it.appendChild(d); box.appendChild(it);
    });
    openModal('Projelerim', box);
  };
  $('#btnExport').onclick = function () {
    var a = document.createElement('a'), blob = new Blob([ser()], { type: 'application/json' });
    a.href = URL.createObjectURL(blob); a.download = (P.name || 'proje').replace(/[^\w\-çğıöşüÇĞİÖŞÜ ]+/g, '_') + '.json';
    document.body.appendChild(a); a.click(); setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
  };
  $('#btnImport').onclick = function () { $('#fileImport').click(); };
  $('#fileImport').addEventListener('change', function () {
    var f = this.files[0]; this.value = ''; if (!f) return;
    var r = new FileReader();
    r.onload = function () { try { var p = JSON.parse(r.result); if (!p || !Array.isArray(p.sprites)) throw 0; resetWith(p); } catch (e) { alert('Geçerli bir proje dosyası değil.'); } };
    r.readAsText(f);
  });

  /* ---------- Başlat ---------- */
  function refreshAll() { $('#projName').value = P.name; renderSprites(); renderBackdrops(); renderPalette(); renderScripts(); syncProps(); }
  $('#palette').addEventListener('pointerdown', onDown);
  $('#scripts').addEventListener('pointerdown', onDown);
  window.addEventListener('pagehide', function () { if (NOSAVE) return; clearTimeout(saveTimer); try { localStorage.setItem(CUR_KEY, ser()); } catch (e) {} });

  var saved = null; try { saved = JSON.parse(localStorage.getItem(CUR_KEY) || 'null'); } catch (e) {}
  P = saved ? normalize(saved) : sampleProject(); cur = P.sel; last = ser();
  renderCats(); refreshAll();

  if (SERVER && VIEW_ID) {
    /* Öğretmen/admin inceleme modu: öğrencinin projesi açılır, hiçbir şey kaydedilmez. */
    NOSAVE = true; document.body.classList.add('viewmode'); status('Proje yükleniyor…');
    ['#btnSave', '#btnNew', '#btnSample', '#btnImport', '#btnProjects'].forEach(function (s) { $(s).style.display = 'none'; });
    api('GET', API + '/' + VIEW_ID).then(function (r) {
      if (!r.ok) { status('Proje açılamadı (yetkin olmayabilir)'); return; }
      stopAll(); P = normalize(r.j.data); P.sid = null; cur = P.sel; last = ser(); undo = []; refreshAll();
      status('İnceleme modu: ' + (r.j.owner || 'öğrenci') + ' · ' + r.j.name + ' (kaydedilmez)');
    });
  } else if (SERVER) {
    /* Başka cihazda daha yeni kaydedilmiş proje varsa kaldığın yerden onu yükle. */
    api('GET', API).then(function (r) {
      if (!r.ok || !(r.j.projects || []).length) return;
      var ids = r.j.projects.map(function (p) { return p.id; });
      if (P.sid && ids.indexOf(P.sid) < 0) P.sid = null;
      var latest = r.j.projects.slice().sort(function (a, b) { return b.client_ts - a.client_ts; })[0];
      if (latest.client_ts > (P.ts || 0) || !saved) {
        api('GET', API + '/' + latest.id).then(function (g) {
          if (!g.ok) return; resetWith(g.j.data, latest.id); P.ts = latest.client_ts; last = ser(); status('Sunucudaki en güncel projen açıldı: ' + latest.name);
        });
      }
    });
  }
  window.__scratch = { P: function () { return P; }, spawn: spawn, startHats: startHats, tick: tick, stopAll: stopAll, ser: ser };
  requestAnimationFrame(frame);
})();
