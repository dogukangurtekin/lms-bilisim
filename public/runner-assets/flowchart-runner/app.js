/* Flowchart Programlama — arayüz. Motor: engine.js (FlowEngine), görevler: tasks.js. */
(function () {
  'use strict';

  var E = window.FlowEngine;
  var ALL_TASKS = Array.isArray(window.FLOWCHART_TASKS) ? window.FLOWCHART_TASKS : [];
  var $ = function (id) { return document.getElementById(id); };
  var SVGNS = 'http://www.w3.org/2000/svg';
  var NW = 170, NH = 56, DW = 190, DH = 94;
  var TYPE_LABEL = { start: 'Başla', end: 'Bitir', input: 'Girdi', output: 'Çıktı', process: 'İşlem', decision: 'Karar' };
  var CAT_LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor', expert: 'Uzman' };
  var STORE_KEY = 'flowchart_progress_v1';
  var COACH_KEY = 'flowchart_coach_v1';

  // ------------------------------------------------------------------ kullanıcı / ödev parametreleri (diğer runner'larla aynı sözleşme)
  var params = new URLSearchParams(window.location.search);
  var roleParam = (params.get('role') || '').toLowerCase();
  var runnerRole = (roleParam === 'teacher' || roleParam === 'admin') ? roleParam : 'student';
  var isStaff = runnerRole === 'teacher' || runnerRole === 'admin';
  var assignmentId = String(params.get('assignmentId') || '').trim();
  var assignmentTitle = String(params.get('assignmentTitle') || 'Flowchart Programlama Ödevi').trim();
  var rangeFrom = Math.max(1, Number(params.get('levelStart') || params.get('from') || 0));
  var rangeTo = Math.max(rangeFrom, Number(params.get('levelEnd') || params.get('to') || rangeFrom));
  var hasRange = Number.isFinite(rangeFrom) && rangeFrom > 0;
  var enforceGrant = params.get('grant') === '1' || params.get('enforceGrant') === '1';
  var needsGrantCheck = runnerRole === 'student' && (!!assignmentId || hasRange || enforceGrant);
  var grantDenied = false;
  var levelStart = hasRange ? rangeFrom : 1;
  var levelEndParam = hasRange ? rangeTo : levelStart;
  var isAssignmentMode = !!assignmentId || hasRange;
  if (!isStaff && !isAssignmentMode) { isAssignmentMode = false; levelStart = 1; levelEndParam = 3; }   // ödevsiz öğrenci: ilk 3 görev + serbest çalışma
  if (isStaff) { isAssignmentMode = false; levelStart = 1; levelEndParam = 9999; }
  var sessionStartedAt = Date.now();
  var rangeCompleteFired = false;

  // ------------------------------------------------------------------ durum
  var tasks = [];                    // aktif aralıktaki görevler
  var S = {
    chart: { nodes: [], edges: [] },
    sel: null,                       // {kind:'node'|'edge', id}
    view: { x: 0, y: 0, k: 1 },
    undo: [], redo: [],
    taskIdx: -1,                     // -1 = Serbest Çalışma
    nid: 1, eid: 1,
    validation: { errors: [], warnings: [], ok: true },
    hintsShown: 0,
  };
  var run = null;                    // çalışma durumu
  var store = { completed: {}, drafts: {}, free: null, last: 0, hints: {} };

  var svg, gEdges, gNodes, gTemp, viewport, board;

  function loadStore() {
    try { var raw = localStorage.getItem(STORE_KEY); if (raw) { var p = JSON.parse(raw); if (p && typeof p === 'object') store = Object.assign(store, p); } } catch (e) {}
  }
  var saveTimer = 0;
  function saveStore(now) {
    clearTimeout(saveTimer);
    var go = function () { try { localStorage.setItem(STORE_KEY, JSON.stringify(store)); } catch (e) {} };
    if (now) go(); else saveTimer = setTimeout(go, 250);
  }
  function saveDraft() {
    if (S.taskIdx >= 0 && tasks[S.taskIdx]) store.drafts[tasks[S.taskIdx].id] = JSON.parse(JSON.stringify(S.chart));
    else store.free = JSON.parse(JSON.stringify(S.chart));
    saveStore();
  }

  // ------------------------------------------------------------------ yardımcılar
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function el(name, attrs, parent) {
    var e = document.createElementNS(SVGNS, name);
    if (attrs) Object.keys(attrs).forEach(function (k) { e.setAttribute(k, attrs[k]); });
    if (parent) parent.appendChild(e);
    return e;
  }
  var toastTimer = 0;
  function toast(msg, kind, ms) {
    var t = $('toast'); t.textContent = msg; t.className = 'fc-toast show' + (kind ? ' ' + kind : '');
    clearTimeout(toastTimer); toastTimer = setTimeout(function () { t.className = 'fc-toast'; }, ms || 2200);
  }
  function mdLite(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\*\*([^*]+)\*\*/g, '<b>$1</b>').replace(/`([^`]+)`/g, '<code>$1</code>');
  }
  function elapsedSeconds() { return Math.max(0, Math.round((Date.now() - sessionStartedAt) / 1000)); }

  // ------------------------------------------------------------------ şema yardımcıları
  function nodeById(id) { for (var i = 0; i < S.chart.nodes.length; i++) if (S.chart.nodes[i].id === id) return S.chart.nodes[i]; return null; }
  function edgeById(id) { for (var i = 0; i < S.chart.edges.length; i++) if (S.chart.edges[i].id === id) return S.chart.edges[i]; return null; }
  function size(n) { return n.type === 'decision' ? { w: DW, h: DH } : { w: NW, h: NH }; }
  function portPos(n, port) { var s = size(n); return port === 'no' ? { x: n.x + s.w, y: n.y + s.h / 2 } : { x: n.x + s.w / 2, y: n.y + s.h }; }
  function inPos(n) { var s = size(n); return { x: n.x + s.w / 2, y: n.y }; }
  function outEdge(nodeId, port) { for (var i = 0; i < S.chart.edges.length; i++) { var e = S.chart.edges[i]; if (e.from === nodeId && (e.port || 'out') === port) return e; } return null; }
  function ports(n) { return n.type === 'end' ? [] : n.type === 'decision' ? ['yes', 'no'] : ['out']; }
  function freePort(n) { var p = ports(n); for (var i = 0; i < p.length; i++) if (!outEdge(n.id, p[i])) return p[i]; return null; }
  function newId(kind) { return kind + (kind === 'n' ? S.nid++ : S.eid++); }
  function syncCounters() {
    var mn = 0, me = 0;
    S.chart.nodes.forEach(function (n) { var m = /^n(\d+)$/.exec(n.id); if (m) mn = Math.max(mn, +m[1]); });
    S.chart.edges.forEach(function (e) { var m = /^e(\d+)$/.exec(e.id); if (m) me = Math.max(me, +m[1]); });
    S.nid = mn + 1; S.eid = me + 1;
  }
  function snapshot() { return JSON.stringify(S.chart); }
  function pushHistory(snap) {
    S.undo.push(snap || snapshot()); if (S.undo.length > 120) S.undo.shift(); S.redo = []; updateToolState();
  }
  function setChart(chart, keepHistory) {
    S.chart = { nodes: (chart.nodes || []).map(function (n) { return { id: String(n.id), type: n.type, text: String(n.text || ''), x: +n.x || 0, y: +n.y || 0 }; }), edges: (chart.edges || []).map(function (e) { return { id: String(e.id), from: String(e.from), to: String(e.to), port: e.port || 'out' }; }) };
    syncCounters();
    if (!keepHistory) { S.undo = []; S.redo = []; }
    S.sel = null;
  }

  // ------------------------------------------------------------------ bağlantı yönlendirme (dik açılı, yumuşak köşeli)
  function routeEdge(e, backIdx) {
    var a = nodeById(e.from), b = nodeById(e.to);
    if (!a || !b) return [];
    var P = portPos(a, e.port || 'out'), T = inPos(b), side = e.port === 'no';
    var pts = [P];
    if (T.y >= P.y + 26) {
      if (side) { var x1 = P.x + 34; pts.push({ x: x1, y: P.y }, { x: x1, y: T.y - 26 }, { x: T.x, y: T.y - 26 }); }
      else if (Math.abs(P.x - T.x) > 2) { var my = P.y + (T.y - P.y) / 2; pts.push({ x: P.x, y: my }, { x: T.x, y: my }); }
    } else {
      var sa = size(a), sb = size(b);
      var gx = Math.max(a.x + sa.w, b.x + sb.w) + 44 + backIdx * 18;
      if (side) pts.push({ x: gx, y: P.y });
      else pts.push({ x: P.x, y: P.y + 26 }, { x: gx, y: P.y + 26 });
      pts.push({ x: gx, y: T.y - 26 }, { x: T.x, y: T.y - 26 });
    }
    pts.push(T);
    return pts;
  }
  function roundedPath(pts, r) {
    if (pts.length < 2) return '';
    var d = 'M' + pts[0].x + ' ' + pts[0].y;
    for (var i = 1; i < pts.length - 1; i++) {
      var p0 = pts[i - 1], p1 = pts[i], p2 = pts[i + 1];
      var v1x = p0.x - p1.x, v1y = p0.y - p1.y, v2x = p2.x - p1.x, v2y = p2.y - p1.y;
      var l1 = Math.hypot(v1x, v1y), l2 = Math.hypot(v2x, v2y), rr = Math.min(r, l1 / 2, l2 / 2);
      if (rr < 1) { d += ' L' + p1.x + ' ' + p1.y; continue; }
      var ax = p1.x + (v1x / l1) * rr, ay = p1.y + (v1y / l1) * rr, bx = p1.x + (v2x / l2) * rr, by = p1.y + (v2y / l2) * rr;
      d += ' L' + ax + ' ' + ay + ' Q' + p1.x + ' ' + p1.y + ' ' + bx + ' ' + by;
    }
    var last = pts[pts.length - 1];
    return d + ' L' + last.x + ' ' + last.y;
  }

  // ------------------------------------------------------------------ çizim
  function shorten(s, max) { s = String(s); return s.length > max ? s.slice(0, max - 1) + '…' : s; }
  function nodeLabel(n) {
    var t = String(n.text || '').trim();
    switch (n.type) {
      case 'start': return 'Başla';
      case 'end': return 'Bitir';
      case 'input': return t ? 'Oku: ' + t : '';
      case 'output': return t ? 'Yaz: ' + t : '';
      case 'decision': return t ? t + ' ?' : '';
      default: return t;
    }
  }
  function shapeFor(n, parent) {
    var s = size(n), w = s.w, h = s.h;
    if (n.type === 'start' || n.type === 'end') return el('rect', { class: 'shape', x: 0, y: 0, width: w, height: h, rx: h / 2 }, parent);
    if (n.type === 'process') return el('rect', { class: 'shape', x: 0, y: 0, width: w, height: h, rx: 10 }, parent);
    if (n.type === 'decision') return el('polygon', { class: 'shape', points: [w / 2, 0, w, h / 2, w / 2, h, 0, h / 2].join(' ') }, parent);
    return el('polygon', { class: 'shape', points: [18, 0, w, 0, w - 18, h, 0, h].join(' ') }, parent);   // girdi/çıktı: paralelkenar
  }
  function issueMap() {
    var m = {};
    S.validation.warnings.forEach(function (w) { if (w.nodeId && !m[w.nodeId]) m[w.nodeId] = 'warn'; });
    S.validation.errors.forEach(function (w) { if (w.nodeId) m[w.nodeId] = 'err'; });
    return m;
  }

  function render() {
    viewport.setAttribute('transform', 'translate(' + S.view.x + ',' + S.view.y + ') scale(' + S.view.k + ')');
    gEdges.textContent = ''; gNodes.textContent = '';
    var issues = issueMap();
    var backCount = 0;
    S.chart.edges.forEach(function (e) {
      var a = nodeById(e.from), b = nodeById(e.to); if (!a || !b) return;
      var isBack = inPos(b).y < portPos(a, e.port || 'out').y + 26;
      var pts = routeEdge(e, isBack ? backCount++ : 0);
      var cls = 'edge' + (e.port === 'yes' ? ' yes' : e.port === 'no' ? ' no' : '') +
        (S.sel && S.sel.kind === 'edge' && S.sel.id === e.id ? ' selected' : '') + (run && run.activeEdge === e.id ? ' active' : '');
      var g = el('g', { class: cls, 'data-edge': e.id }, gEdges);
      var d = roundedPath(pts, 12);
      el('path', { class: 'hit', d: d }, g);
      el('path', { class: 'line', d: d }, g);
      var T = pts[pts.length - 1], Pp = pts[pts.length - 2] || T, ang = Math.atan2(T.y - Pp.y, T.x - Pp.x);
      var ax = Math.cos(ang), ay = Math.sin(ang);
      el('polygon', { class: 'arrow', points: [T.x, T.y, T.x - ax * 13 - ay * 7, T.y - ay * 13 + ax * 7, T.x - ax * 13 + ay * 7, T.y - ay * 13 - ax * 7].join(' ') }, g);
      if (e.port === 'yes' || e.port === 'no') {
        var lg = el('g', { class: 'elabel', transform: 'translate(' + (e.port === 'yes' ? pts[0].x + 26 : pts[0].x + 22) + ',' + (e.port === 'yes' ? pts[0].y + 16 : pts[0].y - 14) + ')' }, g);
        el('rect', { x: -22, y: -10, width: 44, height: 20, rx: 10 }, lg);
        var tx = el('text', {}, lg); tx.textContent = e.port === 'yes' ? 'Evet' : 'Hayır';
      }
    });
    S.chart.nodes.forEach(function (n) {
      var s = size(n);
      var cls = 'node t-' + n.type + (S.sel && S.sel.kind === 'node' && S.sel.id === n.id ? ' selected' : '') +
        (run && run.activeNode === n.id ? ' active' : '') + (run && run.visited[n.id] && run.activeNode !== n.id ? ' done' : '') +
        (run && run.errorNode === n.id ? ' err' : (!run && issues[n.id] ? ' ' + issues[n.id] : ''));
      var g = el('g', { class: cls, 'data-node': n.id, transform: 'translate(' + n.x + ',' + n.y + ')' }, gNodes);
      shapeFor(n, g);
      var label = nodeLabel(n);
      var t = el('text', { x: s.w / 2, y: s.h / 2 }, g);
      if (!label) { t.setAttribute('opacity', '.65'); t.textContent = '(boş — doldur)'; }
      else t.textContent = shorten(label, n.type === 'decision' ? 20 : 24);
      var tt = el('title', {}, g); tt.textContent = (TYPE_LABEL[n.type] || '') + (n.text ? ': ' + n.text : '');
      // çıkış halkaları
      ports(n).forEach(function (p) {
        var pp = portPos({ x: 0, y: 0, type: n.type }, p);
        var used = !!outEdge(n.id, p);
        el('circle', { class: 'port ' + p + (used ? ' used' : ' free'), 'data-port': p, 'data-owner': n.id, cx: pp.x, cy: pp.y, r: 7 }, g);
      });
      var iss = !run ? issues[n.id] : null;
      if (iss) {
        var bg = el('g', { class: 'badge' + (iss === 'warn' ? ' warn' : ''), transform: 'translate(' + (s.w - 6) + ',4)' }, g);
        el('circle', { r: 10 }, bg); var bt = el('text', {}, bg); bt.textContent = '!';
        bt.setAttribute('style', 'fill:#fff;font:900 12px sans-serif;text-anchor:middle;dominant-baseline:central');
      }
    });
  }

  function worldFromClient(cx, cy) {
    var r = svg.getBoundingClientRect();
    return { x: (cx - r.left - S.view.x) / S.view.k, y: (cy - r.top - S.view.y) / S.view.k };
  }
  function fitView() {
    var r = svg.getBoundingClientRect();
    if (!S.chart.nodes.length) { S.view = { x: r.width / 2 - 130, y: 40, k: 1 }; render(); return; }
    var x0 = 1e9, y0 = 1e9, x1 = -1e9, y1 = -1e9;
    S.chart.nodes.forEach(function (n) { var s = size(n); x0 = Math.min(x0, n.x); y0 = Math.min(y0, n.y); x1 = Math.max(x1, n.x + s.w); y1 = Math.max(y1, n.y + s.h); });
    var pad = 60, w = x1 - x0 + pad * 2 + 80, h = y1 - y0 + pad * 2;
    var k = clamp(Math.min(r.width / w, r.height / h), 0.3, 1.15);
    S.view = { k: k, x: (r.width - (x1 - x0) * k) / 2 - x0 * k, y: pad * k * 0.6 - y0 * k + (r.height - h * k) / 2 };
    render();
  }
  function zoomAt(factor, cx, cy) {
    var r = svg.getBoundingClientRect(), px = (cx === undefined ? r.left + r.width / 2 : cx), py = (cy === undefined ? r.top + r.height / 2 : cy);
    var w = worldFromClient(px, py), nk = clamp(S.view.k * factor, 0.25, 2.2);
    S.view.k = nk; S.view.x = (px - r.left) - w.x * nk; S.view.y = (py - r.top) - w.y * nk; render();
  }

  // ------------------------------------------------------------------ doğrulama çubuğu / düzenleme çubuğu
  function locked() { return !!run; }
  function refreshValidation() { S.validation = E.validate(S.chart); renderValidation(); }
  function renderValidation() {
    var box = $('validation'), v = S.validation;
    box.textContent = ''; box.className = 'fc-validation';
    if (!S.chart.nodes.length) { box.classList.add('warn'); box.textContent = 'Tuval boş — soldan blok ekleyerek başla.'; return; }
    var msgs = v.errors.length ? v.errors : v.warnings;
    if (!msgs.length) { box.classList.add('ok'); box.textContent = '✓ Şema geçerli — ▶ Çalıştır\'a basabilirsin.'; return; }
    box.classList.add(v.errors.length ? 'err' : 'warn');
    var head = document.createElement('span');
    head.textContent = (v.errors.length ? '⚠ ' + v.errors.length + ' sorun: ' : '⚠ ' + v.warnings.length + ' uyarı: ');
    box.appendChild(head);
    msgs.slice(0, 3).forEach(function (m) {
      var c = document.createElement('span'); c.className = 'fc-vmsg'; c.textContent = m.message;
      if (m.nodeId) c.addEventListener('click', function () { selectNode(m.nodeId); focusNode(m.nodeId); });
      box.appendChild(c);
    });
    if (msgs.length > 3) { var more = document.createElement('span'); more.textContent = '+' + (msgs.length - 3) + ' daha'; box.appendChild(more); }
  }
  function focusNode(id) {
    var n = nodeById(id); if (!n) return; var r = svg.getBoundingClientRect(), s = size(n);
    S.view.x = r.width / 2 - (n.x + s.w / 2) * S.view.k; S.view.y = r.height / 2 - (n.y + s.h / 2) * S.view.k; render();
  }
  var EB_HINT = {
    input: 'Okunacak değişken(ler): a, b',
    output: 'Yazdırılacak: "Toplam:", a + b',
    process: 'İşlem: x = x + 1   (birden çok için ; kullan)',
    decision: 'Koşul: a > b   (== eşit mi?, ve, veya, değil)',
  };
  var editSnap = null;
  function textError(n) {
    var v = E.validate({ nodes: [n], edges: [] }), label = TYPE_LABEL[n.type] + ':';
    for (var i = 0; i < v.errors.length; i++) if (v.errors[i].message.indexOf(label) === 0) return v.errors[i].message;
    return '';
  }
  function updateEditBar() {
    var inp = $('ebInput'), tp = $('ebType'), delBtn = $('ebDelEdge');
    delBtn.classList.add('hidden'); inp.classList.remove('bad');
    if (S.sel && S.sel.kind === 'node' && nodeById(S.sel.id)) {
      var n = nodeById(S.sel.id); tp.textContent = TYPE_LABEL[n.type]; tp.dataset.t = n.type;
      if (n.type === 'start' || n.type === 'end') { inp.value = ''; inp.disabled = true; inp.placeholder = n.type === 'start' ? 'Programın başlangıcı — yazı gerekmez' : 'Programın sonu — yazı gerekmez'; }
      else { if (document.activeElement !== inp) inp.value = n.text; inp.disabled = locked(); inp.placeholder = EB_HINT[n.type]; }
    } else if (S.sel && S.sel.kind === 'edge') {
      tp.textContent = 'Bağlantı'; tp.dataset.t = ''; inp.value = ''; inp.disabled = true; inp.placeholder = 'Silmek için Delete tuşuna ya da sağdaki düğmeye bas';
      if (!locked()) delBtn.classList.remove('hidden');
    } else { tp.textContent = 'Blok seçilmedi'; tp.dataset.t = ''; inp.value = ''; inp.disabled = true; inp.placeholder = 'Bir bloğa tıkla, içeriğini burada düzenle'; }
  }
  function updateToolState() {
    var lk = locked();
    $('btnUndo').disabled = lk || !S.undo.length; $('btnRedo').disabled = lk || !S.redo.length;
    var hasSel = !!S.sel, selNode = S.sel && S.sel.kind === 'node' ? nodeById(S.sel.id) : null;
    $('btnDel').disabled = lk || !hasSel; $('btnDup').disabled = lk || !selNode || selNode.type === 'start';
    ['btnLayout', 'btnClear', 'btnImport'].forEach(function (id) { $(id).disabled = lk; });
    var hasStart = S.chart.nodes.some(function (n) { return n.type === 'start'; });
    Array.prototype.forEach.call(document.querySelectorAll('.pal'), function (b) { b.disabled = lk || (b.dataset.type === 'start' && hasStart); });
    var tip = $('addTip');
    if (S.sel && S.sel.kind === 'node') {
      var n2 = nodeById(S.sel.id), fp = n2 ? freePort(n2) : null;
      tip.textContent = !n2 ? '' : fp === 'yes' ? 'Karar bloğu seçili: eklediğin ilk blok Evet koluna bağlanır.' : fp === 'no' ? 'Sıra Hayır kolunda: eklediğin blok Hayır koluna bağlanır.' : fp === 'out' ? 'Eklediğin blok, seçili bloğun ardına otomatik bağlanır.' : 'Bu bloğun boş çıkışı yok. Çıkış halkasını sürükleyerek bağlantı kur.';
    } else tip.textContent = 'Bir blok seç, ardından aşağıdan ekle: otomatik bağlanır.';
    $('board').classList.toggle('locked', lk); $('runBanner').classList.toggle('hidden', !lk);
  }
  function afterChange() { refreshValidation(); render(); updateEditBar(); updateToolState(); saveDraft(); }
  function selectNode(id) { S.sel = id ? { kind: 'node', id: id } : null; render(); updateEditBar(); updateToolState(); }
  function selectEdge(id) { S.sel = id ? { kind: 'edge', id: id } : null; render(); updateEditBar(); updateToolState(); }

  // ------------------------------------------------------------------ blok ekleme / silme / bağlama
  function overlaps(x, y, w, h, ignoreId) {
    return S.chart.nodes.some(function (n) { if (n.id === ignoreId) return false; var s = size(n); return x < n.x + s.w + 18 && x + w + 18 > n.x && y < n.y + s.h + 18 && y + h + 18 > n.y; });
  }
  function addNode(type) {
    if (locked()) { toast('Çalışırken düzenleme kapalı. Önce Sıfırla\'ya bas.', 'bad'); return; }
    if (type === 'start' && S.chart.nodes.some(function (n) { return n.type === 'start'; })) { toast('Şemada zaten bir Başla bloğu var.', 'bad'); return; }
    pushHistory();
    var anchor = S.sel && S.sel.kind === 'node' ? nodeById(S.sel.id) : null;
    var port = anchor ? freePort(anchor) : null;
    var node = { id: newId('n'), type: type, text: '', x: 0, y: 0 }, s = size(node);
    if (anchor && port) {
      var as = size(anchor);
      if (port === 'no') { node.x = anchor.x + as.w + 70; node.y = anchor.y + as.h + 40; }
      else { node.x = anchor.x + (as.w - s.w) / 2; node.y = anchor.y + as.h + 56; }
    } else {
      var r = svg.getBoundingClientRect(), c = worldFromClient(r.left + r.width / 2, r.top + r.height * 0.35);
      node.x = Math.round(c.x - s.w / 2); node.y = Math.round(c.y - s.h / 2);
    }
    for (var guard = 0; guard < 40 && overlaps(node.x, node.y, s.w, s.h, node.id); guard++) { if (anchor && port === 'no') node.x += 40; else node.y += 40; }
    S.chart.nodes.push(node);
    if (anchor && port) S.chart.edges.push({ id: newId('e'), from: anchor.id, to: node.id, port: port });
    S.sel = { kind: 'node', id: node.id };
    afterChange();
    ensureVisible(node);
    if (type !== 'start' && type !== 'end') setTimeout(function () { var i = $('ebInput'); if (!i.disabled) { i.focus(); i.select(); } }, 30);
  }
  function ensureVisible(n) {
    var r = svg.getBoundingClientRect(), s = size(n);
    var sx = n.x * S.view.k + S.view.x, sy = n.y * S.view.k + S.view.y, ex = (n.x + s.w) * S.view.k + S.view.x, ey = (n.y + s.h) * S.view.k + S.view.y;
    var dx = 0, dy = 0;
    if (sx < 20) dx = 20 - sx; else if (ex > r.width - 20) dx = r.width - 20 - ex;
    if (sy < 20) dy = 20 - sy; else if (ey > r.height - 70) dy = r.height - 70 - ey;
    if (dx || dy) { S.view.x += dx; S.view.y += dy; render(); }
  }
  function deleteSelection() {
    if (locked() || !S.sel) return;
    pushHistory();
    if (S.sel.kind === 'node') {
      var id = S.sel.id;
      S.chart.nodes = S.chart.nodes.filter(function (n) { return n.id !== id; });
      S.chart.edges = S.chart.edges.filter(function (e) { return e.from !== id && e.to !== id; });
    } else S.chart.edges = S.chart.edges.filter(function (e) { return e.id !== S.sel.id; });
    S.sel = null; afterChange();
  }
  function duplicateSelection() {
    if (locked() || !S.sel || S.sel.kind !== 'node') return;
    var n = nodeById(S.sel.id); if (!n || n.type === 'start') return;
    pushHistory();
    var c = { id: newId('n'), type: n.type, text: n.text, x: n.x + 36, y: n.y + 36 };
    S.chart.nodes.push(c); S.sel = { kind: 'node', id: c.id }; afterChange();
  }
  function connect(fromId, port, toId) {
    var to = nodeById(toId);
    if (!to || fromId === toId) { toast('Bir blok kendine bağlanamaz.', 'bad'); return false; }
    if (to.type === 'start') { toast('Başla bloğuna bağlantı gelemez.', 'bad'); return false; }
    pushHistory();
    S.chart.edges = S.chart.edges.filter(function (e) { return !(e.from === fromId && (e.port || 'out') === port); });
    S.chart.edges.push({ id: newId('e'), from: fromId, to: toId, port: port });
    afterChange(); return true;
  }
  function undo() {
    if (locked() || !S.undo.length) return;
    S.redo.push(snapshot()); setChart(JSON.parse(S.undo.pop()), true); afterChange();
  }
  function redo() {
    if (locked() || !S.redo.length) return;
    S.undo.push(snapshot()); setChart(JSON.parse(S.redo.pop()), true); afterChange();
  }

  // ------------------------------------------------------------------ işaretçi etkileşimi (sürükle, bağla, kaydır, yakınlaş)
  var ptrs = new Map(), drag = null, conn = null, pan = null, pinch = null;
  function clearTemp() { gTemp.textContent = ''; }
  function onDown(ev) {
    svg.focus({ preventScroll: true });
    ptrs.set(ev.pointerId, { x: ev.clientX, y: ev.clientY });
    if (ptrs.size === 2) {
      drag = conn = pan = null; clearTemp(); svg.classList.remove('panning');
      var v = Array.from(ptrs.values()); pinch = { d: Math.hypot(v[0].x - v[1].x, v[0].y - v[1].y), k: S.view.k }; return;
    }
    var t = ev.target;
    var portEl = t.closest && t.closest('[data-port]'), nodeEl = t.closest && t.closest('[data-node]'), edgeEl = t.closest && t.closest('[data-edge]');
    if (portEl && !locked()) {
      var owner = portEl.getAttribute('data-owner'), port = portEl.getAttribute('data-port');
      S.sel = { kind: 'node', id: owner }; updateEditBar(); updateToolState();
      conn = { from: owner, port: port }; svg.setPointerCapture(ev.pointerId); return;
    }
    if (nodeEl) {
      var id = nodeEl.getAttribute('data-node'); selectNode(id);
      if (!locked()) { var n = nodeById(id), w = worldFromClient(ev.clientX, ev.clientY); drag = { id: id, dx: w.x - n.x, dy: w.y - n.y, snap: snapshot(), moved: false }; svg.setPointerCapture(ev.pointerId); }
      return;
    }
    if (edgeEl) { selectEdge(edgeEl.getAttribute('data-edge')); return; }
    if (S.sel) { S.sel = null; render(); updateEditBar(); updateToolState(); }
    pan = { x: ev.clientX, y: ev.clientY, vx: S.view.x, vy: S.view.y }; svg.classList.add('panning'); svg.setPointerCapture(ev.pointerId);
  }
  function onMove(ev) {
    if (ptrs.has(ev.pointerId)) ptrs.set(ev.pointerId, { x: ev.clientX, y: ev.clientY });
    if (pinch && ptrs.size >= 2) {
      var v = Array.from(ptrs.values()), d = Math.hypot(v[0].x - v[1].x, v[0].y - v[1].y);
      var k = clamp(pinch.k * (d / Math.max(1, pinch.d)), 0.25, 2.2), mx = (v[0].x + v[1].x) / 2, my = (v[0].y + v[1].y) / 2;
      zoomAt(k / S.view.k, mx, my); return;
    }
    if (drag) {
      var n = nodeById(drag.id), w = worldFromClient(ev.clientX, ev.clientY);
      var nx = w.x - drag.dx, ny = w.y - drag.dy;
      if (!drag.moved && Math.hypot(nx - n.x, ny - n.y) < 3) return;
      drag.moved = true; n.x = nx; n.y = ny; render(); return;
    }
    if (conn) {
      var a = nodeById(conn.from); if (!a) return;
      var P = portPos(a, conn.port), w2 = worldFromClient(ev.clientX, ev.clientY);
      clearTemp(); el('path', { class: 'tempedge', d: 'M' + P.x + ' ' + P.y + ' L' + w2.x + ' ' + w2.y }, gTemp); return;
    }
    if (pan) { S.view.x = pan.vx + (ev.clientX - pan.x); S.view.y = pan.vy + (ev.clientY - pan.y); render(); }
  }
  function onUp(ev) {
    ptrs.delete(ev.pointerId);
    if (pinch) { if (ptrs.size < 2) pinch = null; return; }
    if (drag) {
      var d = drag; drag = null;
      if (d.moved) { var n = nodeById(d.id); n.x = Math.round(n.x / 12) * 12; n.y = Math.round(n.y / 12) * 12; pushHistory(d.snap); afterChange(); }
      return;
    }
    if (conn) {
      var c = conn; conn = null; clearTemp();
      var under = document.elementFromPoint(ev.clientX, ev.clientY), nodeEl = under && under.closest ? under.closest('[data-node]') : null;
      if (nodeEl) connect(c.from, c.port, nodeEl.getAttribute('data-node'));
      else { var ow = nodeById(c.from); if (ow) toast('Bağlamak için halkayı başka bir bloğun üzerine bırak.', '', 1800); render(); }
      return;
    }
    if (pan) { pan = null; svg.classList.remove('panning'); }
  }
  function onWheel(ev) { ev.preventDefault(); zoomAt(ev.deltaY < 0 ? 1.1 : 0.9, ev.clientX, ev.clientY); }

  function onKey(ev) {
    var tag = (ev.target && ev.target.tagName) || '';
    var typing = tag === 'INPUT' || tag === 'TEXTAREA';
    var mod = ev.ctrlKey || ev.metaKey;
    if (typing) { if (ev.key === 'Escape') { ev.target.blur(); svg.focus(); } return; }
    if (!$('pickerModal').classList.contains('hidden') || !$('helpModal').classList.contains('hidden')) { if (ev.key === 'Escape') closeModals(); return; }
    if (mod && (ev.key === 'z' || ev.key === 'Z')) { ev.preventDefault(); if (ev.shiftKey) redo(); else undo(); return; }
    if (mod && (ev.key === 'y' || ev.key === 'Y')) { ev.preventDefault(); redo(); return; }
    if (mod && (ev.key === 'd' || ev.key === 'D')) { ev.preventDefault(); duplicateSelection(); return; }
    if (ev.key === 'Delete' || ev.key === 'Backspace') { ev.preventDefault(); deleteSelection(); return; }
    if (ev.key === 'Escape') { S.sel = null; render(); updateEditBar(); updateToolState(); return; }
    if (ev.key === 'Enter' && S.sel && S.sel.kind === 'node') { var i = $('ebInput'); if (!i.disabled) { ev.preventDefault(); i.focus(); i.select(); } return; }
    if (S.sel && S.sel.kind === 'node' && !locked() && ev.key.indexOf('Arrow') === 0) {
      ev.preventDefault(); var n = nodeById(S.sel.id), step = ev.shiftKey ? 48 : 12;
      pushHistory();
      if (ev.key === 'ArrowUp') n.y -= step; if (ev.key === 'ArrowDown') n.y += step; if (ev.key === 'ArrowLeft') n.x -= step; if (ev.key === 'ArrowRight') n.x += step;
      afterChange();
    }
  }

  // ------------------------------------------------------------------ araç düğmeleri
  function autoArrange() {
    if (locked()) return;
    if (!S.chart.nodes.length) { toast('Düzenlenecek blok yok.'); return; }
    pushHistory(); E.autoLayout(S.chart); afterChange(); fitView(); toast('Şema düzenlendi ✨', 'good', 1400);
  }
  function clearAll() {
    if (locked()) return;
    if (S.chart.nodes.length && !window.confirm('Tuvaldeki tüm bloklar silinsin mi? (Geri al ile döndürebilirsin)')) return;
    pushHistory(); S.chart = { nodes: [], edges: [] }; S.sel = null;
    if (S.taskIdx >= 0 || true) { S.chart.nodes.push({ id: newId('n'), type: 'start', text: '', x: 260, y: 40 }); S.sel = { kind: 'node', id: S.chart.nodes[0].id }; }
    afterChange(); fitView();
  }
  function download(name, blob) {
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = name; document.body.appendChild(a); a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
  }
  function exportJSON() {
    var data = { app: 'flowchart-programlama', version: 1, task: S.taskIdx >= 0 && tasks[S.taskIdx] ? tasks[S.taskIdx].id : null, chart: S.chart };
    download('sema.json', new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' }));
  }
  function importJSON(file) {
    var fr = new FileReader();
    fr.onload = function () {
      try {
        var d = JSON.parse(String(fr.result)), c = d && d.chart ? d.chart : d;
        if (!c || !Array.isArray(c.nodes) || !Array.isArray(c.edges)) throw new Error('biçim');
        var ok = c.nodes.every(function (n) { return n && E.TYPES.indexOf(n.type) >= 0; });
        if (!ok) throw new Error('tür');
        if (c.nodes.length > 300) throw new Error('çok büyük');
        pushHistory(); setChart(c, true); afterChange(); fitView(); toast('Şema yüklendi', 'good');
      } catch (e) { toast('Dosya okunamadı: geçerli bir şema dosyası seç.', 'bad', 3000); }
    };
    fr.readAsText(file);
  }
  function exportPNG() {
    if (!S.chart.nodes.length) { toast('Önce bir şema çiz.'); return; }
    var x0 = 1e9, y0 = 1e9, x1 = -1e9, y1 = -1e9;
    S.chart.nodes.forEach(function (n) { var s = size(n); x0 = Math.min(x0, n.x); y0 = Math.min(y0, n.y); x1 = Math.max(x1, n.x + s.w); y1 = Math.max(y1, n.y + s.h); });
    var pad = 70, W = Math.ceil(x1 - x0 + pad * 2 + 120), H = Math.ceil(y1 - y0 + pad * 2);
    var clone = viewport.cloneNode(true);
    clone.setAttribute('transform', 'translate(' + (pad - x0) + ',' + (pad - y0) + ')');
    Array.prototype.forEach.call(clone.querySelectorAll('.port,.badge,.hit,.elabel title'), function (e) { e.remove(); });
    var live = viewport.querySelectorAll('*'), cl = clone.querySelectorAll('*');
    // canlı elemanların hesaplanmış stilleri kopyaya satır içi yazılır (SVG tek başına çizilebilsin)
    var props = ['fill', 'stroke', 'stroke-width', 'stroke-dasharray', 'font-family', 'font-size', 'font-weight', 'text-anchor', 'dominant-baseline', 'opacity'];
    for (var i = 0; i < live.length && i < cl.length; i++) {
      var cs = getComputedStyle(live[i]), st = '';
      props.forEach(function (p) { st += p + ':' + cs.getPropertyValue(p) + ';'; });
      cl[i].setAttribute('style', st);
      if (cl[i].tagName === 'path' && cl[i].getAttribute('class') === 'line') cl[i].style.fill = 'none';
    }
    var xml = '<svg xmlns="' + SVGNS + '" width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '"><rect width="100%" height="100%" fill="#0f2a63"/>' + new XMLSerializer().serializeToString(clone) + '</svg>';
    var img = new Image(); img.onload = function () {
      var cv = document.createElement('canvas'); cv.width = W * 2; cv.height = H * 2; var cx = cv.getContext('2d'); cx.scale(2, 2); cx.drawImage(img, 0, 0, W, H);
      cv.toBlob(function (b) { if (b) download('sema.png', b); else toast('Resim oluşturulamadı.', 'bad'); }, 'image/png');
    };
    img.onerror = function () { toast('Resim oluşturulamadı.', 'bad'); };
    img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
  }

  // ------------------------------------------------------------------ çalıştırma
  var SPEEDS = [0.25, 0.5, 1, 2, 4, 8];
  function speedDelay() { return 700 / SPEEDS[(+$('speed').value || 3) - 1]; }
  function updateSpeedLabel() { var m = SPEEDS[(+$('speed').value || 3) - 1]; $('speedLabel').textContent = (m < 1 ? m : String(m)) + '×'; }
  function fmtVar(v) { return typeof v === 'string' ? '"' + v + '"' : E.fmt(v); }
  function addConsole(text, cls) {
    var c = $('console'); var s = document.createElement('span'); if (cls) s.className = cls; s.textContent = text + '\n'; c.appendChild(s); c.scrollTop = c.scrollHeight;
  }
  function updateVars(vars, changed) {
    var body = $('varsBody'); body.textContent = '';
    Object.keys(vars).forEach(function (k) { if (run.varOrder.indexOf(k) < 0) run.varOrder.push(k); });
    run.varOrder.forEach(function (k) {
      if (!Object.prototype.hasOwnProperty.call(vars, k)) return;
      var tr = document.createElement('tr'); if (changed && changed.indexOf(k) >= 0) tr.className = 'changed';
      var a = document.createElement('td'); a.textContent = k; var b = document.createElement('td'); b.textContent = fmtVar(vars[k]);
      tr.appendChild(a); tr.appendChild(b); body.appendChild(tr);
    });
    $('varsEmpty').classList.toggle('hidden', body.children.length > 0);
  }
  function switchTab(name) {
    Array.prototype.forEach.call(document.querySelectorAll('.fc-tab'), function (b) { b.classList.toggle('active', b.dataset.tab === name); });
    ['task', 'vars', 'out'].forEach(function (t) { $('panel-' + t).classList.toggle('hidden', t !== name); });
  }
  function exampleInputs() { var t = tasks[S.taskIdx]; return t && t.tests && t.tests[0] ? t.tests[0].in.map(String) : []; }
  function beginRun() {
    refreshValidation();
    if (!S.validation.ok) {
      var m = S.validation.errors[0]; toast(m.message, 'bad', 3400);
      if (m.nodeId) { selectNode(m.nodeId); focusNode(m.nodeId); }
      return false;
    }
    var runner;
    try { runner = new E.Runner(S.chart, [], { interactive: true }); } catch (e) { toast(e.message, 'bad'); return false; }
    run = { runner: runner, playing: false, timer: 0, activeNode: runner.cur, activeEdge: null, visited: {}, errorNode: null, finished: false, error: false, varOrder: [], waiting: null, wasPlaying: false, examples: exampleInputs() };
    $('console').textContent = ''; $('varsBody').textContent = ''; $('varsEmpty').classList.remove('hidden');
    S.sel = null; updateEditBar(); updateToolState(); render(); updateRunButtons();
    switchTab('out');
    return true;
  }
  function stepOnce() {
    if (!run || run.finished || run.error || run.waiting) return;
    var ev = run.runner.step();
    if (ev.waiting) {
      run.waiting = { name: ev.name }; run.wasPlaying = run.playing; run.playing = false; clearTimeout(run.timer);
      showInput(ev.name); updateRunButtons(); render(); return;
    }
    if (ev.error) {
      run.error = true; run.errorNode = ev.nodeId; run.playing = false; clearTimeout(run.timer);
      addConsole('⚠ ' + ev.error, 'err'); toast(ev.error, 'bad', 3600); render(); updateRunButtons(); return;
    }
    run.visited[ev.nodeId] = true; run.activeEdge = ev.edgeId || null;
    if (ev.output !== null && ev.output !== undefined) addConsole(ev.output);
    if (ev.vars) updateVars(ev.vars, ev.changed);
    if (ev.finished) { run.finished = true; run.activeNode = ev.nodeId; run.playing = false; clearTimeout(run.timer); addConsole('— Program bitti ✓ (' + run.runner.steps + ' adım)', 'ok'); updateRunButtons(); }
    else run.activeNode = run.runner.cur;
    render();
  }
  function tick() {
    if (!run || !run.playing) return;
    stepOnce();
    if (run && run.playing && !run.finished && !run.error && !run.waiting) run.timer = setTimeout(tick, speedDelay());
    updateRunButtons();
  }
  function play() { if (!run) return; run.playing = true; clearTimeout(run.timer); updateRunButtons(); run.timer = setTimeout(tick, 60); }
  function showInput(name) {
    var row = $('inputRow'), inp = $('runInput'); row.classList.remove('hidden'); switchTab('out');
    $('inputLabel').textContent = '"' + name + '" değerini gir';
    inp.value = run.examples && run.examples.length ? run.examples.shift() : '';
    setTimeout(function () { inp.focus(); inp.select(); }, 30);
  }
  function submitInput() {
    if (!run || !run.waiting) return;
    var inp = $('runInput'), val = inp.value;
    if (!String(val).trim()) { toast('Bir değer yaz.', 'bad', 1400); return; }
    run.runner.provide(val); addConsole('← ' + val, 'dim');
    $('inputRow').classList.add('hidden'); var was = run.wasPlaying; run.waiting = null;
    stepOnce();
    if (run && was && !run.finished && !run.error) play(); else updateRunButtons();
  }
  function resetRun() {
    if (run) clearTimeout(run.timer);
    run = null; $('inputRow').classList.add('hidden'); $('console').textContent = ''; $('varsBody').textContent = ''; $('varsEmpty').classList.remove('hidden');
    render(); updateEditBar(); updateToolState(); updateRunButtons();
  }
  function updateRunButtons() {
    var playing = !!(run && run.playing), over = !!(run && (run.finished || run.error));
    $('btnRun').classList.toggle('hidden', playing);
    $('btnPause').classList.toggle('hidden', !playing);
    $('btnRun').textContent = !run ? '▶ Çalıştır' : over ? '▶ Yeniden' : '▶ Devam';
    $('btnStep').disabled = over || !!(run && run.waiting);
    $('btnReset').disabled = !run;
    $('btnCheck').classList.toggle('hidden', S.taskIdx < 0);
  }
  function onRunClick() {
    if (run && (run.finished || run.error)) resetRun();
    if (!run && !beginRun()) return;
    if (!run.waiting) play();
  }
  function onStepClick() {
    if (!run && !beginRun()) return;
    if (run.playing) { run.playing = false; clearTimeout(run.timer); }
    stepOnce(); updateRunButtons();
  }

  // ------------------------------------------------------------------ görevler
  function setTasksRange() {
    tasks = ALL_TASKS.filter(function (t) { return t.id >= levelStart && t.id <= levelEndParam; });
  }
  function isDone(t) { return !!store.completed[t.id]; }
  function unlockedMax() {
    if (isStaff) return tasks.length - 1;
    var u = 0; while (u < tasks.length - 1 && isDone(tasks[u])) u++;
    return u;
  }
  function completedCount() { return tasks.filter(isDone).length; }
  function totalXp() { return ALL_TASKS.reduce(function (s, t) { return s + (store.completed[t.id] ? t.xp : 0); }, 0); }
  function starterChart() { return { nodes: [{ id: 'n1', type: 'start', text: '', x: 260, y: 40 }], edges: [] }; }

  function renderTaskPanel() {
    var t = S.taskIdx >= 0 ? tasks[S.taskIdx] : null;
    $('tkResults').textContent = '';
    if (!t) {
      $('tkTitle').textContent = 'Serbest Çalışma'; $('tkChips').innerHTML = '<span class="fc-chip">Görevsiz tuval</span><span class="fc-chip">XP yok</span>';
      $('tkStory').innerHTML = mdLite('İstediğin akış şemasını kur, **Çalıştır** ile dene. Şemanı **Kaydet** ile dosyaya, **PNG** ile resme çevirebilirsin. Görevlere ☰ düğmesinden geçebilirsin.');
      $('tkExample').textContent = ''; $('tkHints').textContent = ''; $('btnHint').classList.add('hidden');
      $('taskName').textContent = 'Serbest Çalışma'; $('taskMeta').textContent = 'Görevsiz'; $('btnCheck').classList.add('hidden');
      return;
    }
    $('btnHint').classList.remove('hidden');
    $('tkTitle').textContent = t.id + '. ' + t.title;
    var chips = '<span class="fc-chip ' + t.cat + '">' + CAT_LABEL[t.cat] + '</span><span class="fc-chip">⭐ ' + t.xp + ' XP</span>';
    var need = t.need || {};
    if (need.decision) chips += '<span class="fc-chip">◇ Karar bloğu</span>';
    if (need.loop) chips += '<span class="fc-chip">↻ Döngü</span>';
    if (need.input) chips += '<span class="fc-chip">▱ Girdi</span>';
    if (need.maxNodes) chips += '<span class="fc-chip">≤ ' + need.maxNodes + ' blok</span>';
    $('tkChips').innerHTML = chips;
    $('tkStory').innerHTML = mdLite(t.story);
    var ex = t.tests[0], exIn = ex.in.length ? ex.in.join('\n') : '(girdi yok)';
    $('tkExample').innerHTML = '<b>Örnek girdi</b><pre></pre><b>Beklenen çıktı</b><pre></pre>';
    var pres = $('tkExample').querySelectorAll('pre'); pres[0].textContent = exIn; pres[1].textContent = ex.out.join('\n');
    renderHints();
    $('taskName').textContent = t.id + '. ' + t.title; $('taskMeta').textContent = CAT_LABEL[t.cat] + ' · ⭐ ' + t.xp + ' XP';
    $('btnCheck').classList.remove('hidden');
  }
  function renderHints() {
    var t = tasks[S.taskIdx]; if (!t) return;
    var box = $('tkHints'); box.textContent = '';
    for (var i = 0; i < Math.min(S.hintsShown, t.hints.length); i++) { var d = document.createElement('div'); d.className = 'fc-hint'; d.textContent = '💡 ' + t.hints[i]; box.appendChild(d); }
    var left = t.hints.length - S.hintsShown;
    $('btnHint').textContent = left > 0 ? '💡 İpucu göster (' + S.hintsShown + '/' + t.hints.length + ')' : '💡 Tüm ipuçları açık';
    $('btnHint').disabled = left <= 0;
  }
  function showHint() {
    var t = tasks[S.taskIdx]; if (!t || S.hintsShown >= t.hints.length) return;
    S.hintsShown++; store.hints[t.id] = S.hintsShown; saveStore(); renderHints();
  }
  function updateHeader() {
    $('progressPill').textContent = completedCount() + ' / ' + tasks.length;
    $('xpPill').textContent = '⭐ ' + totalXp() + ' XP';
    $('btnPrevTask').disabled = S.taskIdx <= 0 && !(S.taskIdx === 0 && false);
    $('btnPrevTask').disabled = S.taskIdx <= 0;
    $('btnNextTask').disabled = S.taskIdx < 0 ? !tasks.length : (S.taskIdx >= tasks.length - 1 || S.taskIdx + 1 > unlockedMax());
  }
  function openTask(idx) {
    if (idx >= tasks.length) return;
    if (idx >= 0 && idx > unlockedMax()) { toast('Önce önceki görevi tamamla.', 'bad'); return; }
    resetRun();
    saveDraft();
    S.taskIdx = idx;
    var t = idx >= 0 ? tasks[idx] : null;
    var draft = t ? store.drafts[t.id] : store.free;
    if (draft && draft.nodes && draft.nodes.length) setChart(draft); else setChart(starterChart());
    S.hintsShown = t ? (store.hints[t.id] || 0) : 0;
    if (idx >= 0) { store.last = idx; saveStore(); }
    var sinks = S.chart.nodes.filter(function (n) { return n.type !== 'end' && freePort(n); });
    S.sel = sinks.length ? { kind: 'node', id: sinks[sinks.length - 1].id } : null;
    renderTaskPanel(); refreshValidation(); render(); fitView(); updateEditBar(); updateToolState(); updateRunButtons(); updateHeader();
    switchTab('task');
    emitGameUpdate();
  }

  function renderResults(res, t) {
    var box = $('tkResults'); box.textContent = '';
    var add = function (cls, html) { var d = document.createElement('div'); d.className = 'fc-res ' + cls; d.innerHTML = html; box.appendChild(d); return d; };
    if (!res.valid) {
      add('bad', '<b>Önce şemadaki sorunları düzelt</b>');
      res.validation.errors.slice(0, 4).forEach(function (e) { var d = add('bad', ''); d.textContent = '• ' + e.message; });
      return;
    }
    res.structure.forEach(function (m) { var d = add('bad', ''); d.textContent = '• ' + m; });
    var allOk = res.passed === res.total;
    add(allOk ? 'ok' : 'bad', '<b>' + res.passed + ' / ' + res.total + ' test geçti</b>' + (allOk && !res.structure.length ? ' — harika!' : ''));
    var shown = 0;
    res.tests.forEach(function (r, i) {
      if (r.ok || shown >= 3) return; shown++;
      var d = add('bad', '');
      if (r.hidden) { d.innerHTML = '<b>Gizli test ' + (i + 1) + ' başarısız.</b> Şeman örnekte çalışıyor olabilir ama başka değerlerde yanlış sonuç veriyor. Farklı girdilerle kendin dene!'; return; }
      var dt = document.createElement('div'); dt.innerHTML = '<b>Test ' + (i + 1) + '</b>';
      var pre = document.createElement('pre'); pre.textContent = 'Girdi: ' + (r.input.length ? r.input.join(', ') : '(yok)') + '\nBeklenen: ' + r.expected.join(' | ') + '\nSenin çıktın: ' + (r.got.length ? r.got.join(' | ') : '(boş)') + (r.error ? '\nHata: ' + r.error : (!r.finished ? '\nProgram Bitir bloğuna ulaşmadı' : ''));
      d.appendChild(dt); d.appendChild(pre);
    });
  }
  function checkClick() {
    var t = tasks[S.taskIdx]; if (!t) return;
    if (run) resetRun();
    var res = E.checkTask(S.chart, t);
    switchTab('task'); renderResults(res, t);
    if (!res.valid) { var e0 = res.validation.errors[0]; if (e0 && e0.nodeId) { selectNode(e0.nodeId); focusNode(e0.nodeId); } toast('Şemada düzeltilecek yerler var.', 'bad'); return; }
    if (res.ok) completeTask(); else toast(res.structure.length && res.passed === res.total ? res.structure[0] : res.passed + ' / ' + res.total + ' test geçti', 'bad', 2600);
  }
  function completeTask() {
    var t = tasks[S.taskIdx]; if (!t) return;
    var first = !store.completed[t.id];
    store.completed[t.id] = true; saveStore(true);
    updateHeader();
    emitLevelCompleted(); emitGameUpdate(); emitRangeCompletedIfNeeded();
    var last = S.taskIdx >= tasks.length - 1;
    $('doneText').textContent = last ? (isAssignmentMode ? 'Ödevdeki tüm görevleri bitirdin. Tebrikler!' : 'Bu bölümdeki son görevi de çözdün. Süper!') : '"' + t.title + '" görevini çözdün. Sıradaki görev açıldı.';
    $('doneXp').textContent = first ? '+' + t.xp + ' XP' : 'Tekrar çözdün ✓';
    $('doneNext').classList.toggle('hidden', last);
    $('doneModal').classList.remove('hidden');
  }

  // ------------------------------------------------------------------ görev seçici
  function openPicker() {
    var body = $('pickerBody'); body.textContent = '';
    var groups = [], max = unlockedMax();
    tasks.forEach(function (t, i) { var g = groups.filter(function (x) { return x.cat === t.cat; })[0]; if (!g) { g = { cat: t.cat, items: [] }; groups.push(g); } g.items.push({ t: t, i: i }); });
    groups.forEach(function (g) {
      var sec = document.createElement('div'); sec.className = 'fc-group ' + g.cat;
      var done = g.items.filter(function (x) { return isDone(x.t); }).length;
      var h = document.createElement('h4'); h.innerHTML = CAT_LABEL[g.cat] + ' <small>' + done + ' / ' + g.items.length + ' görev · ⭐ ' + g.items[0].t.xp + ' XP</small>'; sec.appendChild(h);
      var grid = document.createElement('div'); grid.className = 'fc-grid';
      g.items.forEach(function (x) {
        var b = document.createElement('button'); b.type = 'button';
        var open = x.i <= max;
        b.className = 'fc-tile' + (isDone(x.t) ? ' done' : '') + (x.i === S.taskIdx ? ' current' : ''); b.disabled = !open;
        b.innerHTML = (open ? '' : '<span class="lock">🔒</span>') + '<b></b><small></small>';
        b.querySelector('b').textContent = x.t.id + '. ' + x.t.title; b.querySelector('small').textContent = isDone(x.t) ? '✓ Tamamlandı' : x.t.tests.length + ' test';
        b.addEventListener('click', function () { closeModals(); openTask(x.i); });
        grid.appendChild(b);
      });
      sec.appendChild(grid); body.appendChild(sec);
    });
    $('btnFree').classList.toggle('hidden', !isStaff && isAssignmentMode);
    $('pickerModal').classList.remove('hidden');
    var cur = body.querySelector('.fc-tile.current'); if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: 'center' });
  }
  function closeModals() { $('pickerModal').classList.add('hidden'); $('helpModal').classList.add('hidden'); }

  // ------------------------------------------------------------------ platform mesajları (lightbot/web-lab ile aynı sözleşme)
  function relIds() { var out = []; tasks.forEach(function (t, i) { if (isDone(t)) out.push(i + 1); }); return out; }
  function levelEndEff() { return Math.max(levelStart, Math.min(levelEndParam, levelStart + tasks.length - 1)); }
  function progressPercent() { return tasks.length ? Math.min(100, Math.round((completedCount() / tasks.length) * 100)) : 0; }
  function levelsList() { return tasks.map(function (t) { return { id: t.id, name: t.title, completed: isDone(t) }; }); }
  function post(msg) { try { if (window.parent && window.parent !== window) window.parent.postMessage(msg, '*'); } catch (e) {} }
  function emitGameUpdate() {
    post({ type: 'GAME_UPDATE', source: 'flowchart', assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: levelEndEff(), levels: levelsList(), currentLevelIndex: Math.max(0, S.taskIdx), progressPercent: progressPercent() });
  }
  function emitLevelCompleted() {
    var t = tasks[S.taskIdx]; if (!t) return;
    post({ type: 'LEVEL_COMPLETED', source: 'flowchart', assignmentId: assignmentId || null, assignmentTitle: assignmentTitle, levelId: String(t.id), levelNo: S.taskIdx + 1, levelStart: levelStart, levelEnd: levelEndEff(), completedLevelIds: relIds(), currentLevelIndex: S.taskIdx, percent: progressPercent(), progressPercent: progressPercent(), xp: t.xp, elapsedSeconds: elapsedSeconds(), levels: levelsList() });
  }
  function emitRangeCompletedIfNeeded() {
    if (!isAssignmentMode || rangeCompleteFired || !tasks.length || completedCount() < tasks.length) return;
    rangeCompleteFired = true;
    post({ type: 'ASSIGNMENT_RANGE_COMPLETED', source: 'flowchart', assignmentId: assignmentId, assignmentTitle: assignmentTitle, levelStart: levelStart, levelEnd: levelEndEff(), completedLevelIds: relIds(), xp: tasks.reduce(function (s, t) { return s + t.xp; }, 0), elapsedSeconds: elapsedSeconds(), currentLevelIndex: S.taskIdx });
  }
  function resolveGrant() {
    if (isStaff || !needsGrantCheck) return Promise.resolve();
    var url = (window.RUNNER_APP_BASE || '').replace(/\/$/, '') + '/runner-grant/flowchart-runner' + window.location.search;
    return fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(function (res) {
      if (!res.ok) { grantDenied = true; return null; }
      return res.json();
    }).then(function (g) {
      if (!g) return;
      if (!g.ok) { grantDenied = true; return; }
      if (g.role === 'student') { levelStart = Math.max(1, Number(g.from || 1)); levelEndParam = Math.max(levelStart, Number(g.to || levelStart)); isAssignmentMode = true; }
      else if (g.role === 'staff') { levelStart = 1; levelEndParam = 9999; isAssignmentMode = false; }
    }).catch(function () { grantDenied = true; });
  }

  // ------------------------------------------------------------------ başlatma
  function bind() {
    svg.addEventListener('pointerdown', onDown); svg.addEventListener('pointermove', onMove);
    svg.addEventListener('pointerup', onUp); svg.addEventListener('pointercancel', onUp);
    svg.addEventListener('wheel', onWheel, { passive: false });
    svg.addEventListener('dblclick', function (ev) { var ne = ev.target.closest && ev.target.closest('[data-node]'); if (ne) { selectNode(ne.getAttribute('data-node')); var i = $('ebInput'); if (!i.disabled) { i.focus(); i.select(); } } });
    document.addEventListener('keydown', onKey);
    window.addEventListener('resize', function () { render(); });
    Array.prototype.forEach.call(document.querySelectorAll('.pal'), function (b) { b.addEventListener('click', function () { addNode(b.dataset.type); }); });
    $('btnUndo').addEventListener('click', undo); $('btnRedo').addEventListener('click', redo);
    $('btnDel').addEventListener('click', deleteSelection); $('btnDup').addEventListener('click', duplicateSelection);
    $('btnLayout').addEventListener('click', autoArrange); $('btnFit').addEventListener('click', fitView);
    $('btnZoomIn').addEventListener('click', function () { zoomAt(1.2); }); $('btnZoomOut').addEventListener('click', function () { zoomAt(1 / 1.2); });
    $('btnExport').addEventListener('click', exportJSON); $('btnPng').addEventListener('click', exportPNG); $('btnClear').addEventListener('click', clearAll);
    $('btnImport').addEventListener('click', function () { $('fileInput').click(); });
    $('fileInput').addEventListener('change', function (e) { var f = e.target.files && e.target.files[0]; if (f) importJSON(f); e.target.value = ''; });
    $('ebDelEdge').addEventListener('click', deleteSelection);
    var inp = $('ebInput');
    inp.addEventListener('focus', function () { editSnap = snapshot(); });
    inp.addEventListener('input', function () {
      var n = S.sel && S.sel.kind === 'node' ? nodeById(S.sel.id) : null; if (!n || locked()) return;
      n.text = inp.value; refreshValidation(); render(); inp.classList.toggle('bad', !!inp.value.trim() && !!textError(n)); inp.title = textError(n); saveDraft();
    });
    var commit = function () { if (editSnap !== null && editSnap !== snapshot()) pushHistory(editSnap); editSnap = null; updateToolState(); };
    inp.addEventListener('blur', commit);
    inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); inp.blur(); svg.focus(); } });
    $('btnRun').addEventListener('click', onRunClick); $('btnStep').addEventListener('click', onStepClick);
    $('btnPause').addEventListener('click', function () { if (run) { run.playing = false; clearTimeout(run.timer); updateRunButtons(); } });
    $('btnReset').addEventListener('click', resetRun); $('btnCheck').addEventListener('click', checkClick);
    $('speed').addEventListener('input', updateSpeedLabel);
    $('runInputOk').addEventListener('click', submitInput);
    $('runInput').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); submitInput(); } });
    Array.prototype.forEach.call(document.querySelectorAll('.fc-tab'), function (b) { b.addEventListener('click', function () { switchTab(b.dataset.tab); }); });
    $('btnHint').addEventListener('click', showHint);
    $('btnTasks').addEventListener('click', openPicker);
    $('btnPrevTask').addEventListener('click', function () { openTask(S.taskIdx - 1); });
    $('btnNextTask').addEventListener('click', function () { openTask(S.taskIdx < 0 ? 0 : S.taskIdx + 1); });
    $('btnFree').addEventListener('click', function () { closeModals(); openTask(-1); });
    $('btnHelp').addEventListener('click', function () { $('helpModal').classList.remove('hidden'); });
    Array.prototype.forEach.call(document.querySelectorAll('[data-close]'), function (b) { b.addEventListener('click', closeModals); });
    $('doneNext').addEventListener('click', function () { $('doneModal').classList.add('hidden'); openTask(S.taskIdx + 1); });
    $('doneStay').addEventListener('click', function () { $('doneModal').classList.add('hidden'); });
    $('coachClose').addEventListener('click', function () { $('coach').classList.add('hidden'); try { localStorage.setItem(COACH_KEY, '1'); } catch (e) {} });
    $('btnClose').addEventListener('click', function () { post({ type: 'REQUEST_CLOSE_ACTIVITY', source: 'flowchart' }); });
    if (window.parent && window.parent !== window) $('btnClose').classList.remove('hidden');
  }

  function init() {
    svg = $('svg'); viewport = $('viewport'); gEdges = $('gEdges'); gNodes = $('gNodes'); gTemp = $('gTemp'); board = $('board');
    loadStore();
    resolveGrant().then(function () {
      if (grantDenied) {
        document.body.innerHTML = '<div style="padding:40px;max-width:520px;margin:60px auto;text-align:center;font:800 17px Nunito,sans-serif;color:#7f1d1d;background:#fee2e2;border-radius:20px">Bu oyuna sadece atanmış ödev aralığından erişebilirsiniz.</div>';
        return;
      }
      setTasksRange(); bind(); updateSpeedLabel();
      var seen = false; try { seen = localStorage.getItem(COACH_KEY) === '1'; } catch (e) {}
      if (seen) $('coach').classList.add('hidden');
      var start = 0;
      if (tasks.length) { start = Math.min(unlockedMax(), tasks.length - 1); if (isStaff) start = Math.min(store.last || 0, tasks.length - 1); }
      else start = -1;
      openTask(start);
      emitGameUpdate();
    });
  }
  init();
})();
