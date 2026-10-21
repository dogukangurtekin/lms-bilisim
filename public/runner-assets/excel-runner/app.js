(function () {
  'use strict';
  var XL = window.XL;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var SS = window.SaveSlots || { inAssignment: false, blocked: false, draftKey: function (k) { return k; } };
  var KEY = SS.draftKey('bk_excel_wb_v1');   // kullanıcıya (ve ödeve) özel anahtar: başka öğrencinin çalışması görünmez
  var DEF_W = 90, MAX_ROWS = 1000, MAX_COLS = 100;

  var wb, calc, tds = [], cols = [], grid = $('#grid'), wrap = $('#wrap');
  var anchor = { r: 0, c: 0 }, focus = { r: 0, c: 0 };
  var editing = false, editMode = '', editOrigin = null, lastPoint = null, showF = false;
  var undoStack = [], redoStack = [], clip = null, saveTimer = null, dragging = false, fillDrag = null, pointDrag = false;

  /* ================= veri modeli ================= */
  function newSheet(name) { return { name: name, cells: {}, colW: {}, rows: 100, cols: 26, freeze: false, filter: null }; }
  function newBook() { return { name: 'Çalışma Kitabı1', sheets: [newSheet('Sayfa1')], active: 0 }; }
  function S() { return wb.sheets[wb.active]; }
  function key(r, c) { return r + ',' + c; }
  function getCell(r, c) { return S().cells[key(r, c)]; }
  function ensureCell(r, c) { var k = key(r, c); return S().cells[k] || (S().cells[k] = { v: '' }); }
  function tidy(r, c) { var k = key(r, c), x = S().cells[k]; if (x && (x.v === '' || x.v == null) && Object.keys(x).length <= 1) delete S().cells[k]; }
  function newCalc() {
    calc = new XL.Calc(function (si, r, c) { var sh = wb.sheets[si]; var x = sh && sh.cells[r + ',' + c]; return x ? x.v : undefined; },
      function (name) { for (var i = 0; i < wb.sheets.length; i++) if (wb.sheets[i].name.toLowerCase() === String(name).toLowerCase()) return i; return -1; });
  }

  /* ================= kayıt / geri al ================= */
  function snapshot() { return JSON.stringify(wb); }
  function pushUndo() { undoStack.push(snapshot()); if (undoStack.length > 60) undoStack.shift(); redoStack = []; }
  function restoreSnap(js) { wb = JSON.parse(js); newCalc(); buildGrid(); setSel(anchor.r, anchor.c); renderSheetTabs(); saveSoon(); }
  function undo() { if (!undoStack.length) return; redoStack.push(snapshot()); restoreSnap(undoStack.pop()); }
  function redo() { if (!redoStack.length) return; undoStack.push(snapshot()); restoreSnap(redoStack.pop()); }
  function saveSoon() { $('#saveState').textContent = 'Kaydediliyor…'; clearTimeout(saveTimer); saveTimer = setTimeout(save, 600); }
  function save() { if (SS.blocked) return; try { localStorage.setItem(KEY, JSON.stringify(wb)); $('#saveState').textContent = 'Kaydedildi'; } catch (e) { $('#saveState').textContent = 'Kaydedilemedi'; } }
  function load() { try { var d = JSON.parse(localStorage.getItem(KEY) || 'null'); if (d && d.sheets && d.sheets.length) return d; } catch (e) {} return null; }
  function changed() { calc.reset(); refreshAll(); updateBar(); saveSoon(); }
  function mutate(fn) { pushUndo(); fn(); changed(); }

  /* ================= ızgara ================= */
  function buildGrid() {
    var sh = S(), i, j, h = '<colgroup><col style="width:46px">';
    for (j = 0; j < sh.cols; j++) h += '<col style="width:' + (sh.colW[j] || DEF_W) + 'px">';
    h += '</colgroup><thead><tr><th class="corner rh" id="corner"></th>';
    for (j = 0; j < sh.cols; j++) h += '<th data-c="' + j + '">' + XL.colName(j) + '<span class="rz" data-rz="' + j + '"></span></th>';
    h += '</tr></thead><tbody>';
    for (i = 0; i < sh.rows; i++) {
      h += '<tr data-r="' + i + '"><th class="rh" data-r="' + i + '">' + (i + 1) + '</th>';
      for (j = 0; j < sh.cols; j++) h += '<td data-r="' + i + '" data-c="' + j + '"></td>';
      h += '</tr>';
    }
    grid.innerHTML = h + '</tbody>';
    grid.classList.toggle('freeze', !!sh.freeze);
    tds = []; cols = $$('col', grid).slice(1);
    $$('tbody tr', grid).forEach(function (tr, r) { tds[r] = $$('td', tr); });
    prevSel = null; refreshAll(); applyFilter(); renderSel();
  }
  function refreshAll() { var sh = S(); for (var r = 0; r < sh.rows; r++) for (var c = 0; c < sh.cols; c++) paint(r, c); }
  function paint(r, c) {
    var td = tds[r] && tds[r][c]; if (!td) return;
    var x = getCell(r, c), v = null, txt = '', cls = '', st = '';
    if (x && x.v !== '' && x.v != null) {
      v = calc.cell(wb.active, r, c);
      if (showF && typeof x.v === 'string' && x.v.charAt(0) === '=') txt = x.v;
      else txt = display(v, x);
    }
    var isErr = v instanceof XL.FErr;
    var al = x && x.al ? x.al : (isErr || typeof v === 'boolean' ? 'center' : typeof v === 'number' ? 'right' : 'left');
    if (showF) al = 'left';
    td.textContent = txt;
    td.className = (isErr ? 'err ' : '') + (x && x.wr ? 'wrap ' : '') + (td.classList.contains('insel') ? 'insel ' : '') + (td.classList.contains('act') ? 'act ' : '');
    var s = td.style;
    s.textAlign = al;
    s.fontWeight = x && x.b ? '700' : ''; s.fontStyle = x && x.i ? 'italic' : ''; s.textDecoration = x && x.u ? 'underline' : '';
    s.background = x && x.bg ? x.bg : ''; s.color = x && x.fg && !isErr ? x.fg : '';
    var bd = x && x.bd || {};
    s.borderTop = bd.t ? '1px solid #222' : ''; s.borderBottom = bd.b ? '1px solid #222' : ''; s.borderLeft = bd.l ? '1px solid #222' : ''; s.borderRight = bd.r ? '1px solid #222' : '';
  }
  function display(v, x) {
    if (v instanceof XL.FErr) return v.code;
    if (v == null) return '';
    if (typeof v === 'boolean') return v ? 'DOĞRU' : 'YANLIŞ';
    if (typeof v === 'number') {
      var nf = x.nf || 'gen';
      if (nf === 'gen' && typeof x.v === 'string' && /^=\s*(BUGÜN|TODAY)\s*\(\s*\)\s*$/i.test(x.v)) nf = 'date';
      if (nf === 'gen' && typeof x.v === 'string' && /^=\s*(ŞİMDİ|NOW)\s*\(\s*\)\s*$/i.test(x.v)) nf = 'datetime';
      return XL.formatNumber(v, nf, x.dp);
    }
    return String(v);
  }

  /* ================= seçim ================= */
  var prevSel = null;
  function selRect() { return { r1: Math.min(anchor.r, focus.r), c1: Math.min(anchor.c, focus.c), r2: Math.max(anchor.r, focus.r), c2: Math.max(anchor.c, focus.c) }; }
  function setSel(r, c, extend) {
    var sh = S(); r = Math.max(0, Math.min(sh.rows - 1, r)); c = Math.max(0, Math.min(sh.cols - 1, c));
    if (extend) { focus = { r: r, c: c }; } else { anchor = { r: r, c: c }; focus = { r: r, c: c }; }
    renderSel(); updateBar();
  }
  function renderSel() {
    var rect = selRect(), r, c;
    if (prevSel) {
      for (r = prevSel.r1; r <= prevSel.r2; r++) for (c = prevSel.c1; c <= prevSel.c2; c++) { var t = tds[r] && tds[r][c]; if (t) t.classList.remove('insel', 'act'); }
      $$('th.sel', grid).forEach(function (h) { h.classList.remove('sel'); });
    }
    for (r = rect.r1; r <= rect.r2; r++) for (c = rect.c1; c <= rect.c2; c++) { var td = tds[r] && tds[r][c]; if (td) td.classList.add('insel'); }
    var act = tds[anchor.r] && tds[anchor.r][anchor.c]; if (act) act.classList.add('act');
    var rh = $$('th.rh', grid), ch = $$('thead th[data-c]', grid);
    for (r = rect.r1; r <= rect.r2; r++) { var h1 = $('tbody tr[data-r="' + r + '"] th', grid); if (h1) h1.classList.add('sel'); }
    for (c = rect.c1; c <= rect.c2; c++) if (ch[c]) ch[c].classList.add('sel');
    prevSel = rect; placeHandle(); ensureVisible(tds[focus.r] && tds[focus.r][focus.c]);
    var one = rect.r1 === rect.r2 && rect.c1 === rect.c2;
    $('#nameBox').value = one ? XL.addr(rect.r1, rect.c1) : XL.addr(rect.r1, rect.c1) + ':' + XL.addr(rect.r2, rect.c2);
    stats(rect);
  }
  function placeHandle() {
    var rect = selRect(), td = tds[rect.r2] && tds[rect.r2][rect.c2], hd = $('#fillHandle');
    if (!td || editing) { hd.hidden = true; return; }
    var a = td.getBoundingClientRect(), w = wrap.getBoundingClientRect();
    hd.style.left = (a.right - w.left + wrap.scrollLeft - 5) + 'px'; hd.style.top = (a.bottom - w.top + wrap.scrollTop - 5) + 'px'; hd.hidden = false;
  }
  function ensureVisible(td) {
    if (!td) return;
    var a = td.getBoundingClientRect(), w = wrap.getBoundingClientRect(), topOff = 24 + (S().freeze ? 22 : 0), leftOff = 46;
    if (a.top < w.top + topOff) wrap.scrollTop -= (w.top + topOff - a.top);
    else if (a.bottom > w.bottom) wrap.scrollTop += (a.bottom - w.bottom + 4);
    if (a.left < w.left + leftOff) wrap.scrollLeft -= (w.left + leftOff - a.left);
    else if (a.right > w.right) wrap.scrollLeft += (a.right - w.right + 4);
  }
  function stats(rect) {
    var nums = [], cnt = 0;
    if (rect.r1 === rect.r2 && rect.c1 === rect.c2) { $('#stats').textContent = ''; return; }
    for (var r = rect.r1; r <= rect.r2; r++) for (var c = rect.c1; c <= rect.c2; c++) {
      var x = getCell(r, c); if (!x || x.v === '') continue; cnt++;
      var v = calc.cell(wb.active, r, c); if (typeof v === 'number') nums.push(v);
    }
    var sum = nums.reduce(function (a, b) { return a + b; }, 0);
    $('#stats').textContent = 'Sayı: ' + cnt + (nums.length ? '   Toplam: ' + XL.formatNumber(sum) + '   Ortalama: ' + XL.formatNumber(sum / nums.length) + '   Min: ' + XL.formatNumber(Math.min.apply(null, nums)) + '   Maks: ' + XL.formatNumber(Math.max.apply(null, nums)) : '');
  }
  function updateBar() {
    if (editing) return;
    var x = getCell(anchor.r, anchor.c); $('#fInput').value = x ? x.v : '';
    $('#nfSel').value = x && x.nf ? x.nf : 'gen';
    $$('[data-fmt]').forEach(function (b) { b.classList.toggle('on', !!(x && x[b.dataset.fmt])); });
    $$('[data-al]').forEach(function (b) { b.classList.toggle('on', !!(x && x.al === b.dataset.al)); });
    $('#btnWrap').classList.toggle('on', !!(x && x.wr));
    $('#btnFreeze').classList.toggle('on', !!S().freeze);
    $('#btnUndo').disabled = !undoStack.length; $('#btnRedo').disabled = !redoStack.length;
  }
  function eachSel(fn) { var s = selRect(); for (var r = s.r1; r <= s.r2; r++) for (var c = s.c1; c <= s.c2; c++) fn(r, c); }

  /* ================= fare ================= */
  function cellOf(e) { var t = e.target.closest ? e.target.closest('td,th') : null; return t; }
  grid.addEventListener('mousedown', function (e) {
    if (e.button === 2) { var t0 = cellOf(e); if (t0 && t0.tagName === 'TD') { var rr = selRect(), r0 = +t0.dataset.r, c0 = +t0.dataset.c; if (r0 < rr.r1 || r0 > rr.r2 || c0 < rr.c1 || c0 > rr.c2) setSel(r0, c0); } return; }
    var rz = e.target.dataset && e.target.dataset.rz;
    if (rz != null) { startResize(+rz, e); e.preventDefault(); return; }
    var t = cellOf(e); if (!t) return;
    if (t.tagName === 'TD') {
      var r = +t.dataset.r, c = +t.dataset.c;
      if (editing && canPoint()) { e.preventDefault(); pointDrag = true; insertRef(r, c, false); return; }
      if (editing) commitEdit();
      dragging = true; setSel(r, c, e.shiftKey);
    } else if (t.classList.contains('rh') && t.dataset.r != null) {
      if (editing) commitEdit(); var rr2 = +t.dataset.r; anchor = { r: rr2, c: 0 }; focus = { r: rr2, c: S().cols - 1 }; renderSel(); updateBar();
    } else if (t.dataset.c != null) {
      if (editing) commitEdit(); var cc = +t.dataset.c; anchor = { r: 0, c: cc }; focus = { r: S().rows - 1, c: cc }; renderSel(); updateBar();
    } else if (t.id === 'corner') { anchor = { r: 0, c: 0 }; focus = { r: S().rows - 1, c: S().cols - 1 }; renderSel(); }
    e.preventDefault();
  });
  document.addEventListener('mousemove', function (e) {
    if (!dragging && !fillDrag && !pointDrag) return;
    var el = document.elementFromPoint(e.clientX, e.clientY), t = el && el.closest ? el.closest('td') : null; if (!t) return;
    var r = +t.dataset.r, c = +t.dataset.c;
    if (pointDrag) { insertRef(r, c, true); return; }
    if (fillDrag) { fillDrag.to = { r: r, c: c }; var s = fillDrag.src, ext = fillExtent(); anchor = { r: ext.r1, c: ext.c1 }; focus = { r: ext.r2, c: ext.c2 }; renderSel(); return; }
    if (focus.r !== r || focus.c !== c) { focus = { r: r, c: c }; renderSel(); updateBar(); }
  });
  document.addEventListener('mouseup', function () {
    if (fillDrag) { doFill(); fillDrag = null; }
    dragging = false; pointDrag = false;
  });
  grid.addEventListener('dblclick', function (e) {
    var t = cellOf(e); if (t && t.tagName === 'TD') startEdit('f2');
  });
  function startResize(c, e) {
    var x0 = e.clientX, w0 = S().colW[c] || DEF_W;
    function mv(ev) { var w = Math.max(24, w0 + (ev.clientX - x0) / (zoom / 100)); S().colW[c] = w; cols[c].style.width = w + 'px'; placeHandle(); }
    function up() { document.removeEventListener('mousemove', mv); document.removeEventListener('mouseup', up); saveSoon(); }
    document.addEventListener('mousemove', mv); document.addEventListener('mouseup', up);
  }
  wrap.addEventListener('scroll', function () { if (editing) positionEdit(); });
  $('#fillHandle').addEventListener('mousedown', function (e) { e.preventDefault(); e.stopPropagation(); fillDrag = { src: selRect(), to: null }; });

  /* ================= düzenleme ================= */
  var ce = $('#cellEdit'), fi = $('#fInput');
  function positionEdit() {
    var td = tds[anchor.r][anchor.c], a = td.getBoundingClientRect(), w = wrap.getBoundingClientRect();
    ce.style.left = (a.left - w.left + wrap.scrollLeft - 1) + 'px'; ce.style.top = (a.top - w.top + wrap.scrollTop - 1) + 'px';
    ce.style.width = Math.max(a.width + 1, 80) + 'px'; ce.style.height = (a.height + 1) + 'px';
  }
  function startEdit(mode, initial) {
    if (editing) return;
    editing = true; editMode = mode; lastPoint = null; editOrigin = { r: anchor.r, c: anchor.c, sheet: wb.active };
    var x = getCell(anchor.r, anchor.c), val = initial != null ? initial : (x ? x.v : '');
    ce.hidden = false; positionEdit(); ce.value = val; fi.value = val; ce.focus();
    if (initial == null) ce.setSelectionRange(val.length, val.length);
    $('#fillHandle').hidden = true; onEditInput();
  }
  function canPoint() {
    var v = ce.value; if (v.charAt(0) !== '=') return false;
    var pos = (ce === document.activeElement ? ce : fi).selectionStart;
    if (lastPoint && pos === lastPoint.end) return true;
    return /[=+\-*\/^(,;&<>:]$/.test(v.slice(0, pos));
  }
  function insertRef(r, c, extend) {
    var src = ce === document.activeElement || !fi.matches(':focus') ? ce : fi;
    var v = ce.value, ref;
    var prefix = wb.active !== editOrigin.sheet ? (/[\s'!]/.test(S().name) ? "'" + S().name + "'" : S().name) + '!' : '';
    if (!lastPoint || !extend) {
      var pos = src.selectionStart; if (lastPoint && pos === lastPoint.end) pos = lastPoint.start;
      lastPoint = { start: pos, end: pos, r: r, c: c };
    }
    var a = { r: lastPoint.r, c: lastPoint.c };
    ref = prefix + XL.addr(a.r, a.c) + ((a.r !== r || a.c !== c) ? ':' + XL.addr(r, c) : '');
    var nv = v.slice(0, lastPoint.start) + ref + v.slice(lastPoint.end);
    lastPoint.end = lastPoint.start + ref.length;
    ce.value = nv; fi.value = nv; ce.focus(); ce.setSelectionRange(lastPoint.end, lastPoint.end); highlightRefs(); showHint();
  }
  var refCols = ['ref0', 'ref1', 'ref2', 'ref3'];
  function highlightRefs() {
    $$('td.ref0,td.ref1,td.ref2,td.ref3', grid).forEach(function (t) { t.classList.remove.apply(t.classList, refCols); });
    if (!editing || ce.value.charAt(0) !== '=' || wb.active !== editOrigin.sheet) return;
    var re = /(\$?[A-Za-z]{1,3}\$?\d+)(?::(\$?[A-Za-z]{1,3}\$?\d+))?/g, m, i = 0, s = ce.value.replace(/"[^"]*"/g, function (x) { return new Array(x.length + 1).join(' '); });
    while ((m = re.exec(s))) {
      var a = XL.parseAddr(m[1]), b = m[2] ? XL.parseAddr(m[2]) : a; if (!a || !b) continue;
      var cls = refCols[i++ % 4];
      for (var r = Math.min(a.r, b.r); r <= Math.max(a.r, b.r); r++) for (var c = Math.min(a.c, b.c); c <= Math.max(a.c, b.c); c++) if (tds[r] && tds[r][c]) tds[r][c].classList.add(cls);
    }
  }
  function onEditInput(e) {
    var src = e && e.target === fi ? fi : ce;
    if (src === fi) ce.value = fi.value; else fi.value = ce.value;
    if (!(lastPoint && src.selectionStart === lastPoint.end)) lastPoint = null;
    highlightRefs(); showHint();
  }
  ce.addEventListener('input', onEditInput);
  fi.addEventListener('input', function (e) { if (!editing) { editing = true; editMode = 'f2'; editOrigin = { r: anchor.r, c: anchor.c, sheet: wb.active }; ce.hidden = false; positionEdit(); $('#fillHandle').hidden = true; } onEditInput(e); });
  fi.addEventListener('focus', function () { if (!editing) { editing = true; editMode = 'f2'; editOrigin = { r: anchor.r, c: anchor.c, sheet: wb.active }; ce.hidden = false; positionEdit(); ce.value = fi.value; $('#fillHandle').hidden = true; } });
  function endEdit() {
    editing = false; ce.hidden = true; lastPoint = null; hideHint(); highlightRefs(); placeHandle();
  }
  function cancelEdit() { endEdit(); updateBar(); wrap.focus(); }
  function commitEdit() {
    if (!editing) return;
    var val = ce.value, o = editOrigin, prevSheet = wb.active;
    endEdit();
    if (o.sheet !== wb.active) { wb.active = o.sheet; }
    applyInput(o.r, o.c, val);
    if (prevSheet !== wb.active) renderSheetTabs();
  }
  function applyInput(r, c, val) {
    var old = getCell(r, c), oldV = old ? old.v : '';
    var parsed = parseInput(val);
    if (parsed.v === oldV && !parsed.nf) { updateBar(); return; }
    mutate(function () {
      var x = ensureCell(r, c); x.v = parsed.v;
      if (parsed.nf && !x.nf) { x.nf = parsed.nf; if (parsed.dp != null) x.dp = parsed.dp; }
      tidy(r, c);
    });
  }
  function parseInput(t) {
    var s = String(t);
    if (s.charAt(0) === '=') return { v: s };
    var m;
    if ((m = /^(\d{1,2})[.\/](\d{1,2})[.\/](\d{4})$/.exec(s.trim()))) {
      var d = new Date(+m[3], +m[2] - 1, +m[1]); if (d.getMonth() === +m[2] - 1) return { v: String(XL.serial(d)), nf: 'date' };
    }
    if ((m = /^([+-]?\d+(?:[.,]\d+)?)\s*%$/.exec(s.trim()))) { var dec = (m[1].split(/[.,]/)[1] || '').length; return { v: String(parseFloat(m[1].replace(',', '.')) / 100), nf: 'pct', dp: dec }; }
    if ((m = /^([+-]?[\d.,]+)\s*(₺|TL)$/i.exec(s.trim()))) { var n = toNumTr(m[1]); if (!isNaN(n)) return { v: String(n), nf: 'cur', dp: 2 }; }
    if (/^[+-]?\d{1,3}(\.\d{3})+(,\d+)?$/.test(s.trim())) return { v: s.trim().replace(/\./g, '').replace(',', '.') };
    return { v: s };
  }
  function toNumTr(t) { t = t.trim(); if (/^[+-]?\d{1,3}(\.\d{3})+(,\d+)?$/.test(t)) return parseFloat(t.replace(/\./g, '').replace(',', '.')); return parseFloat(t.replace(',', '.')); }

  ce.addEventListener('keydown', function (e) { editKey(e); });
  fi.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === 'Escape' || e.key === 'Tab') editKey(e); });
  function editKey(e) {
    var hint = $('#hint');
    if (!hint.hidden) {
      var items = $$('div', hint), ai = items.findIndex(function (x) { return x.classList.contains('act'); });
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); items.forEach(function (x) { x.classList.remove('act'); }); items[(ai + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length].classList.add('act'); return; }
      if (e.key === 'Tab') { e.preventDefault(); pickHint(items[ai < 0 ? 0 : ai].textContent); return; }
    }
    if (e.key === 'Enter') { e.preventDefault(); commitEdit(); moveBy(e.shiftKey ? -1 : 1, 0); wrap.focus(); }
    else if (e.key === 'Tab') { e.preventDefault(); commitEdit(); moveBy(0, e.shiftKey ? -1 : 1); wrap.focus(); }
    else if (e.key === 'Escape') { e.preventDefault(); cancelEdit(); }
    else if (editMode === 'enter' && ce.value.charAt(0) !== '=' && /^Arrow/.test(e.key) && e.target === ce) {
      e.preventDefault(); commitEdit(); moveBy(e.key === 'ArrowDown' ? 1 : e.key === 'ArrowUp' ? -1 : 0, e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0); wrap.focus();
    }
  }

  /* ---------- işlev önerileri ---------- */
  function showHint() {
    var hint = $('#hint'); if (!editing || ce.value.charAt(0) !== '=') { hideHint(); return; }
    var src = fi.matches(':focus') ? fi : ce, pos = src.selectionStart, before = ce.value.slice(0, pos);
    var m = /([A-Za-zÇĞİÖŞÜçğıöşü_][A-Za-z0-9ÇĞİÖŞÜçğıöşü._]*)$/.exec(before);
    if (!m || /\d$/.test(m[1]) && /^[A-Za-z]{1,3}\d+$/.test(m[1])) { hideHint(); return; }
    var q = XL.norm(m[1]), list = XL.fnNames.filter(function (n) { return XL.norm(n).indexOf(q) === 0 && XL.norm(n) !== q; }).slice(0, 8);
    if (!list.length) { hideHint(); return; }
    hint.innerHTML = list.map(function (n, i) { return '<div' + (i === 0 ? ' class="act"' : '') + '>' + n + '</div>'; }).join(''); hint.hidden = false;
  }
  function hideHint() { $('#hint').hidden = true; }
  function pickHint(name) {
    var src = fi.matches(':focus') ? fi : ce, pos = src.selectionStart, v = ce.value;
    var m = /([A-Za-zÇĞİÖŞÜçğıöşü_][A-Za-z0-9ÇĞİÖŞÜçğıöşü._]*)$/.exec(v.slice(0, pos));
    var start = pos - m[1].length, nv = v.slice(0, start) + name + '(' + v.slice(pos);
    ce.value = nv; fi.value = nv; src.focus(); src.setSelectionRange(start + name.length + 1, start + name.length + 1); hideHint(); highlightRefs();
  }
  $('#hint').addEventListener('mousedown', function (e) { e.preventDefault(); if (e.target.tagName === 'DIV') pickHint(e.target.textContent); });

  /* ================= klavye ================= */
  function moveBy(dr, dc, extend) {
    var f = extend ? focus : anchor;
    var nr = f.r + dr, nc = f.c + dc, sh = S();
    if (nr >= sh.rows - 1 && dr > 0 && sh.rows < MAX_ROWS && nr >= sh.rows) { addRows(50); }
    setSel(nr, nc, extend);
  }
  wrap.tabIndex = 0;
  document.addEventListener('keydown', function (e) {
    var tgt = e.target;
    if (tgt === ce || tgt === fi || tgt === $('#nameBox') || tgt.tagName === 'SELECT' || (tgt.tagName === 'INPUT' && tgt.type !== 'color' && tgt.id !== 'x')) {
      if (tgt === $('#nameBox') && e.key === 'Enter') { gotoName($('#nameBox').value); wrap.focus(); }
      return;
    }
    if ($('#chartBox').hidden === false) { if (e.key === 'Escape') $('#chartBox').hidden = true; return; }
    var mod = e.ctrlKey || e.metaKey, k = e.key;
    if (mod) {
      var kk = k.toLowerCase();
      if (kk === 'z') { e.preventDefault(); undo(); } else if (kk === 'y') { e.preventDefault(); redo(); }
      else if (kk === 'b') { e.preventDefault(); toggleFmt('b'); } else if (kk === 'i') { e.preventDefault(); toggleFmt('i'); } else if (kk === 'u') { e.preventDefault(); toggleFmt('u'); }
      else if (kk === 'a') { e.preventDefault(); anchor = { r: 0, c: 0 }; focus = { r: S().rows - 1, c: S().cols - 1 }; renderSel(); }
      else if (kk === 's') { e.preventDefault(); save(); }
      else if (k === 'Home') { e.preventDefault(); setSel(0, 0, e.shiftKey); }
      else if (k === '`') { e.preventDefault(); toggleShowF(); }
      else if (k === 'ArrowDown' || k === 'ArrowUp' || k === 'ArrowRight' || k === 'ArrowLeft') { e.preventDefault(); jumpEdge(k, e.shiftKey); }
      return;
    }
    switch (k) {
      case 'ArrowDown': e.preventDefault(); moveBy(1, 0, e.shiftKey); return;
      case 'ArrowUp': e.preventDefault(); moveBy(-1, 0, e.shiftKey); return;
      case 'ArrowLeft': e.preventDefault(); moveBy(0, -1, e.shiftKey); return;
      case 'ArrowRight': e.preventDefault(); moveBy(0, 1, e.shiftKey); return;
      case 'Tab': e.preventDefault(); moveBy(0, e.shiftKey ? -1 : 1); return;
      case 'Enter': e.preventDefault(); moveBy(e.shiftKey ? -1 : 1, 0); return;
      case 'PageDown': e.preventDefault(); moveBy(20, 0, e.shiftKey); return;
      case 'PageUp': e.preventDefault(); moveBy(-20, 0, e.shiftKey); return;
      case 'F2': e.preventDefault(); startEdit('f2'); return;
      case 'Delete': case 'Backspace': e.preventDefault(); clearValues(); return;
      case 'Escape': clip = null; $$('td.cut', grid).forEach(function (t) { t.classList.remove('cut'); }); return;
    }
    if (e.altKey && k === '=') { e.preventDefault(); autoSum('TOPLA'); return; }
    if (k.length === 1 && !e.altKey) { e.preventDefault(); startEdit('enter', k); }
  });
  function jumpEdge(k, ext) {
    var f = ext ? focus : anchor, dr = k === 'ArrowDown' ? 1 : k === 'ArrowUp' ? -1 : 0, dc = k === 'ArrowRight' ? 1 : k === 'ArrowLeft' ? -1 : 0, sh = S();
    var r = f.r, c = f.c, filled = function (r, c) { var x = getCell(r, c); return x && x.v !== ''; };
    var startFilled = filled(r, c);
    for (;;) {
      var nr = r + dr, nc = c + dc; if (nr < 0 || nc < 0 || nr >= sh.rows || nc >= sh.cols) break;
      r = nr; c = nc;
      if (startFilled && filled(r, c) && !filled(r + dr, c + dc)) break;
      if (!startFilled && filled(r, c)) break;
      if (startFilled && !filled(r, c)) { var nr2 = r + dr, nc2 = c + dc; if (nr2 < 0 || nc2 < 0 || nr2 >= sh.rows || nc2 >= sh.cols) break; if (filled(nr2, nc2)) { r = nr2; c = nc2; break; } }
    }
    setSel(r, c, ext);
  }
  function gotoName(t) {
    t = t.trim().toUpperCase(); var m = /^(\$?[A-Z]{1,3}\$?\d+)(?::(\$?[A-Z]{1,3}\$?\d+))?$/.exec(t); if (!m) return;
    var a = XL.parseAddr(m[1]), b = m[2] ? XL.parseAddr(m[2]) : a; if (!a || !b) return;
    while (Math.max(a.r, b.r) >= S().rows && S().rows < MAX_ROWS) addRows(50);
    anchor = { r: Math.min(a.r, S().rows - 1), c: Math.min(a.c, S().cols - 1) }; focus = { r: Math.min(b.r, S().rows - 1), c: Math.min(b.c, S().cols - 1) }; renderSel(); updateBar();
  }
  $('#nameBox').addEventListener('keydown', function (e) { if (e.key === 'Enter') { gotoName(this.value); wrap.focus(); } });

  /* ================= biçim ================= */
  function setFmt(fn) { mutate(function () { eachSel(function (r, c) { var x = ensureCell(r, c); fn(x); }); eachSel(tidy); }); }
  function toggleFmt(p) { var cur = getCell(anchor.r, anchor.c), on = !(cur && cur[p]); setFmt(function (x) { if (on) x[p] = 1; else delete x[p]; }); }
  $$('[data-fmt]').forEach(function (b) { b.addEventListener('click', function () { toggleFmt(b.dataset.fmt); wrap.focus(); }); });
  $$('[data-al]').forEach(function (b) { b.addEventListener('click', function () { var al = b.dataset.al; setFmt(function (x) { if (x.al === al) delete x.al; else x.al = al; }); }); });
  $('#btnWrap').addEventListener('click', function () { var cur = getCell(anchor.r, anchor.c), on = !(cur && cur.wr); setFmt(function (x) { if (on) x.wr = 1; else delete x.wr; }); });
  function paintBars() { $('#fillBar').style.setProperty('--c', $('#fillColor').value); $('#textBar').style.setProperty('--c', $('#textColor').value); }
  $('#fillColor').addEventListener('input', function () { var v = this.value; paintBars(); setFmt(function (x) { x.bg = v; }); });
  $('#textColor').addEventListener('input', function () { var v = this.value; paintBars(); setFmt(function (x) { x.fg = v; }); });
  paintBars();

  /* ================= yakınlaştırma ================= */
  var zoom = 100, ZKEY = 'excelRunnerZoom';
  function setZoom(z, keep) {
    z = Math.max(50, Math.min(200, Math.round(z / 10) * 10));
    var cx = wrap.scrollLeft + wrap.clientWidth / 2, cy = wrap.scrollTop + wrap.clientHeight / 2, k = z / zoom;
    zoom = z; $('#grid').style.zoom = z / 100;
    $('#zoomRange').value = z; $('#zoomPct').textContent = z + '%';
    if (keep !== false) { wrap.scrollLeft = cx * k - wrap.clientWidth / 2; wrap.scrollTop = cy * k - wrap.clientHeight / 2; }
    try { localStorage.setItem(ZKEY, String(z)); } catch (e) {}
    if (editing) positionEdit();
    placeHandle();
  }
  $('#zoomIn').addEventListener('click', function () { setZoom(zoom + 10); });
  $('#zoomOut').addEventListener('click', function () { setZoom(zoom - 10); });
  $('#zoomRange').addEventListener('input', function () { setZoom(+this.value); });
  $('#zoomPct').addEventListener('click', function () { setZoom(100); });
  wrap.addEventListener('wheel', function (e) { if (!e.ctrlKey) return; e.preventDefault(); setZoom(zoom + (e.deltaY < 0 ? 10 : -10)); }, { passive: false });
  document.addEventListener('keydown', function (e) {
    if (!(e.ctrlKey || e.metaKey) || editing) return;
    if (e.key === '+' || e.key === '=') { e.preventDefault(); setZoom(zoom + 10); }
    else if (e.key === '-') { e.preventDefault(); setZoom(zoom - 10); }
    else if (e.key === '0') { e.preventDefault(); setZoom(100); }
  });
  try { var zs = +localStorage.getItem(ZKEY); if (zs >= 50 && zs <= 200) setZoom(zs, false); } catch (e) {}
  $('#nfSel').addEventListener('change', function () { var v = this.value; setFmt(function (x) { if (v === 'gen') { delete x.nf; delete x.dp; } else { x.nf = v; if (v === 'pct') x.dp = 0; else if (v === 'num' || v === 'cur') x.dp = 2; else delete x.dp; } }); wrap.focus(); });
  function decimals(d) { setFmt(function (x) { if (!x.nf || x.nf === 'gen') { x.nf = 'num'; x.dp = 2; } var cur = x.dp == null ? 2 : x.dp; x.dp = Math.max(0, Math.min(10, cur + d)); }); }
  $('#btnDecInc').addEventListener('click', function () { decimals(1); });
  $('#btnDecDec').addEventListener('click', function () { decimals(-1); });
  $('#borderSel').addEventListener('change', function () {
    var v = this.value, s = selRect(); this.value = ''; if (!v) return;
    setFmt(function () { });
    pushUndo();
    eachSel(function (r, c) {
      var x = ensureCell(r, c); x.bd = x.bd || {};
      if (v === 'none') delete x.bd; else if (v === 'all') x.bd = { t: 1, b: 1, l: 1, r: 1 };
      else if (v === 'outer') { if (r === s.r1) x.bd.t = 1; if (r === s.r2) x.bd.b = 1; if (c === s.c1) x.bd.l = 1; if (c === s.c2) x.bd.r = 1; }
      else if (v === 'bottom' && r === s.r2) x.bd.b = 1;
      tidy(r, c);
    });
    changed();
  });
  $('#btnClearFmt').addEventListener('click', function () { mutate(function () { eachSel(function (r, c) { var x = getCell(r, c); if (x) { S().cells[key(r, c)] = { v: x.v }; tidy(r, c); } }); }); });
  $('#btnClear').addEventListener('click', function () { mutate(function () { eachSel(function (r, c) { delete S().cells[key(r, c)]; }); }); });
  function clearValues() { mutate(function () { eachSel(function (r, c) { var x = getCell(r, c); if (x) { x.v = ''; tidy(r, c); } }); }); }
  function toggleShowF() { showF = !showF; $('#btnShowF').classList.toggle('on', showF); $('#btnShowF').textContent = showF ? 'Formülleri gizle' : 'Formülleri göster'; refreshAll(); }
  $('#btnShowF').addEventListener('click', toggleShowF);

  function currentRegion() {
    var s = selRect();
    if (s.r1 !== s.r2 || s.c1 !== s.c2) return s;
    var filled = function (r, c) { var x = getCell(r, c); return x && x.v !== ''; };
    var r1 = s.r1, r2 = s.r1, c1 = s.c1, c2 = s.c1, grew = true, sh = S(), i;
    if (!filled(s.r1, s.c1)) return s;
    while (grew) {
      grew = false;
      var chk = function (rr1, rr2, cc1, cc2) { for (var r = rr1; r <= rr2; r++) for (var c = cc1; c <= cc2; c++) if (r >= 0 && c >= 0 && r < sh.rows && c < sh.cols && filled(r, c)) return true; return false; };
      if (r1 > 0 && chk(r1 - 1, r1 - 1, c1 - 1, c2 + 1)) { r1--; grew = true; }
      if (r2 < sh.rows - 1 && chk(r2 + 1, r2 + 1, c1 - 1, c2 + 1)) { r2++; grew = true; }
      if (c1 > 0 && chk(r1 - 1, r2 + 1, c1 - 1, c1 - 1)) { c1--; grew = true; }
      if (c2 < sh.cols - 1 && chk(r1 - 1, r2 + 1, c2 + 1, c2 + 1)) { c2++; grew = true; }
    }
    return { r1: r1, c1: c1, r2: r2, c2: c2 };
  }
  $('#btnTableFmt').addEventListener('click', function () {
    var g = currentRegion(); pushUndo();
    for (var r = g.r1; r <= g.r2; r++) for (var c = g.c1; c <= g.c2; c++) {
      var x = ensureCell(r, c); x.bd = { t: 1, b: 1, l: 1, r: 1 };
      if (r === g.r1) { x.bg = '#107c41'; x.fg = '#ffffff'; x.b = 1; x.al = x.al || 'center'; }
      else { x.bg = (r - g.r1) % 2 === 0 ? '#e8f4ec' : '#ffffff'; delete x.fg; }
    }
    anchor = { r: g.r1, c: g.c1 }; focus = { r: g.r2, c: g.c2 }; changed(); renderSel();
  });

  /* ================= kopyala / yapıştır ================= */
  function selTSV() {
    var s = selRect(), rows = [];
    for (var r = s.r1; r <= s.r2; r++) { var row = []; for (var c = s.c1; c <= s.c2; c++) { var x = getCell(r, c); row.push(x && x.v !== '' ? displayText(r, c) : ''); } rows.push(row.join('\t')); }
    return rows.join('\n');
  }
  function displayText(r, c) { var td = tds[r][c]; return showF ? (function () { var v = calc.cell(wb.active, r, c); return v instanceof XL.FErr ? v.code : display(v, getCell(r, c)); })() : td.textContent; }
  function captureClip(cut) {
    var s = selRect(), cells = [];
    for (var r = s.r1; r <= s.r2; r++) { var row = []; for (var c = s.c1; c <= s.c2; c++) { var x = getCell(r, c); row.push(x ? JSON.parse(JSON.stringify(x)) : null); } cells.push(row); }
    clip = { cells: cells, r: s.r1, c: s.c1, cut: cut, rect: s, sheet: wb.active, text: selTSV() };
    $$('td.cut', grid).forEach(function (t) { t.classList.remove('cut'); });
    if (cut) eachSel(function (r, c) { tds[r][c].classList.add('cut'); });
  }
  document.addEventListener('copy', function (e) { if (editing || isFormEl(e.target)) return; e.preventDefault(); captureClip(false); e.clipboardData.setData('text/plain', clip.text); });
  document.addEventListener('cut', function (e) { if (editing || isFormEl(e.target)) return; e.preventDefault(); captureClip(true); e.clipboardData.setData('text/plain', clip.text); });
  document.addEventListener('paste', function (e) {
    if (editing || isFormEl(e.target)) return; e.preventDefault();
    var text = (e.clipboardData || window.clipboardData).getData('text/plain');
    pasteText(text);
  });
  function isFormEl(t) { return t && (t.tagName === 'INPUT' || t.tagName === 'SELECT' || t.tagName === 'TEXTAREA'); }
  function pasteText(text) {
    var s = selRect();
    if (clip && text.replace(/\r/g, '') === clip.text) {
      var h = clip.cells.length, w = clip.cells[0].length, tr = h === 1 && w === 1 ? s.r2 - s.r1 + 1 : h, tc = h === 1 && w === 1 ? s.c2 - s.c1 + 1 : w;
      mutate(function () {
        var srcSheet = wb.sheets[clip.sheet];
        for (var i = 0; i < tr; i++) for (var j = 0; j < tc; j++) {
          var sx = clip.cells[i % h][j % w], r = s.r1 + i, c = s.c1 + j; if (r >= S().rows || c >= S().cols) continue;
          if (!sx) { delete S().cells[key(r, c)]; continue; }
          var nx = JSON.parse(JSON.stringify(sx));
          if (typeof nx.v === 'string' && nx.v.charAt(0) === '=') nx.v = XL.shiftFormula(nx.v, r - (clip.r + i % h), c - (clip.c + j % w));
          S().cells[key(r, c)] = nx;
        }
        if (clip.cut) { var q = clip.rect; for (var rr = q.r1; rr <= q.r2; rr++) for (var cc = q.c1; cc <= q.c2; cc++) { if (clip.sheet === wb.active && rr >= s.r1 && rr < s.r1 + tr && cc >= s.c1 && cc < s.c1 + tc) continue; delete srcSheet.cells[key(rr, cc)]; } clip = null; }
        anchor = { r: s.r1, c: s.c1 }; focus = { r: Math.min(S().rows - 1, s.r1 + tr - 1), c: Math.min(S().cols - 1, s.c1 + tc - 1) };
      });
      $$('td.cut', grid).forEach(function (t) { t.classList.remove('cut'); }); renderSel();
    } else {
      var rows = text.replace(/\r/g, '').replace(/\n$/, '').split('\n').map(function (l) { return l.split('\t'); });
      mutate(function () {
        rows.forEach(function (row, i) { row.forEach(function (val, j) {
          var r = s.r1 + i, c = s.c1 + j; while (r >= S().rows && S().rows < MAX_ROWS) S().rows += 50; if (c >= S().cols) return;
          var p = parseInput(val); var x = ensureCell(r, c); x.v = p.v; if (p.nf && !x.nf) { x.nf = p.nf; if (p.dp != null) x.dp = p.dp; } tidy(r, c);
        }); });
        if (rows.length + s.r1 > tds.length) buildGrid();
        anchor = { r: s.r1, c: s.c1 }; focus = { r: Math.min(S().rows - 1, s.r1 + rows.length - 1), c: Math.min(S().cols - 1, s.c1 + rows[0].length - 1) };
      });
      renderSel();
    }
  }
  $('#btnCopy').addEventListener('click', function () { captureClip(false); try { navigator.clipboard.writeText(clip.text); } catch (e) {} });
  $('#btnCut').addEventListener('click', function () { captureClip(true); try { navigator.clipboard.writeText(clip.text); } catch (e) {} });
  $('#btnPaste').addEventListener('click', function () {
    if (navigator.clipboard && navigator.clipboard.readText) navigator.clipboard.readText().then(pasteText).catch(function () { if (clip) pasteText(clip.text); else alert('Yapıştırmak için Ctrl+V kullanın.'); });
    else if (clip) pasteText(clip.text); else alert('Yapıştırmak için Ctrl+V kullanın.');
  });

  /* ================= doldurma tutamacı ================= */
  function fillExtent() {
    var s = fillDrag.src, t = fillDrag.to; if (!t) return s;
    var dr = t.r > s.r2 ? t.r - s.r2 : t.r < s.r1 ? t.r - s.r1 : 0, dc = t.c > s.c2 ? t.c - s.c2 : t.c < s.c1 ? t.c - s.c1 : 0;
    if (Math.abs(dr) >= Math.abs(dc) && dr !== 0) return { r1: Math.min(s.r1, t.r), r2: Math.max(s.r2, t.r), c1: s.c1, c2: s.c2, axis: 'v' };
    if (dc !== 0) return { c1: Math.min(s.c1, t.c), c2: Math.max(s.c2, t.c), r1: s.r1, r2: s.r2, axis: 'h' };
    return s;
  }
  var LISTS = [['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'], ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık']];
  function doFill() {
    var s = fillDrag.src, ext = fillExtent(); if (!ext.axis) return;
    pushUndo();
    var vertical = ext.axis === 'v', len = vertical ? s.r2 - s.r1 + 1 : s.c2 - s.c1 + 1, lines = vertical ? s.c2 - s.c1 + 1 : s.r2 - s.r1 + 1;
    for (var ln = 0; ln < lines; ln++) {
      var src = [];
      for (var i = 0; i < len; i++) { var r = vertical ? s.r1 + i : s.r1 + ln, c = vertical ? s.c1 + ln : s.c1 + i; src.push(getCell(r, c) ? JSON.parse(JSON.stringify(getCell(r, c))) : null); }
      var nums = src.every(function (x) { return x && /^[+-]?\d+([.,]\d+)?$/.test(String(x.v)); }) ? src.map(function (x) { return parseFloat(String(x.v).replace(',', '.')); }) : null;
      var step = nums && nums.length >= 2 ? nums[1] - nums[0] : 0;
      var from = vertical ? ext.r1 : ext.c1, to = vertical ? ext.r2 : ext.c2, base = vertical ? s.r1 : s.c1;
      for (var p = from; p <= to; p++) {
        if (p >= base && p < base + len) continue;
        var rel = p - base, si = ((rel % len) + len) % len, sx = src[si], r2 = vertical ? p : s.r1 + ln, c2 = vertical ? s.c1 + ln : p;
        if (!sx) { delete S().cells[key(r2, c2)]; continue; }
        var nx = JSON.parse(JSON.stringify(sx)), v = nx.v;
        if (typeof v === 'string' && v.charAt(0) === '=') nx.v = XL.shiftFormula(v, vertical ? p - (base + si) : 0, vertical ? 0 : p - (base + si));
        else if (nums && len >= 2) nx.v = String(parseFloat((nums[0] + step * rel).toPrecision(12)));
        else if (nums && len === 1 && false) { }
        else if (typeof v === 'string' && v !== '') {
          var m = /^(.*?)(\d+)$/.exec(v), list = null;
          LISTS.forEach(function (L) { if (L.indexOf(v) >= 0 && len === 1) list = L; });
          if (list) nx.v = list[((list.indexOf(v) + rel) % list.length + list.length) % list.length];
          else if (m && len === 1 && !/^[+-]?\d+([.,]\d+)?$/.test(v)) nx.v = m[1] + Math.max(0, parseInt(m[2], 10) + rel);
        }
        S().cells[key(r2, c2)] = nx;
      }
    }
    anchor = { r: ext.r1, c: ext.c1 }; focus = { r: ext.r2, c: ext.c2 }; changed(); renderSel();
  }

  /* ================= satır / sütun işlemleri ================= */
  function addRows(n) { var sh = S(); if (sh.rows >= MAX_ROWS) return; sh.rows = Math.min(MAX_ROWS, sh.rows + n); var a = anchor, f = focus; buildGrid(); anchor = a; focus = f; renderSel(); saveSoon(); }
  function addCols(n) { var sh = S(); if (sh.cols >= MAX_COLS) return; sh.cols = Math.min(MAX_COLS, sh.cols + n); var a = anchor, f = focus; buildGrid(); anchor = a; focus = f; renderSel(); saveSoon(); }
  $('#btnAddRowsB').addEventListener('click', function () { addRows(100); });
  $('#btnAddColsB').addEventListener('click', function () { addCols(10); });
  function structure(axis, at, n) {
    pushUndo();
    var sh = S(), nc = {}, own = sh.name;
    Object.keys(sh.cells).forEach(function (k) {
      var p = k.split(','), r = +p[0], c = +p[1], idx = axis === 'r' ? r : c, ni = idx;
      if (n > 0) { if (idx >= at) ni = idx + n; } else { if (idx >= at && idx < at - n) return; if (idx >= at - n) ni = idx + n; }
      var nk = axis === 'r' ? ni + ',' + c : r + ',' + ni; nc[nk] = sh.cells[k];
    });
    sh.cells = nc;
    if (axis === 'c') { var nw = {}; Object.keys(sh.colW).forEach(function (k) { var i = +k, ni = i; if (n > 0) { if (i >= at) ni = i + n; } else { if (i >= at && i < at - n) return; if (i >= at - n) ni = i + n; } nw[ni] = sh.colW[k]; }); sh.colW = nw; }
    if (n > 0) { if (axis === 'r') sh.rows = Math.min(MAX_ROWS, sh.rows + n); else sh.cols = Math.min(MAX_COLS, sh.cols + n); }
    wb.sheets.forEach(function (s2) {
      Object.keys(s2.cells).forEach(function (k) { var x = s2.cells[k]; if (typeof x.v === 'string' && x.v.charAt(0) === '=') x.v = XL.adjustFormula(x.v, axis, at, n, sh.name, s2.name); });
    });
    var a = anchor; buildGrid(); changed(); setSel(a.r, a.c);
  }
  $('#btnInsRow').addEventListener('click', function () { var s = selRect(); structure('r', s.r1, s.r2 - s.r1 + 1); });
  $('#btnDelRow').addEventListener('click', function () { var s = selRect(); structure('r', s.r1, -(s.r2 - s.r1 + 1)); });
  $('#btnInsCol').addEventListener('click', function () { var s = selRect(); structure('c', s.c1, s.c2 - s.c1 + 1); });
  $('#btnDelCol').addEventListener('click', function () { var s = selRect(); structure('c', s.c1, -(s.c2 - s.c1 + 1)); });
  $('#btnFreeze').addEventListener('click', function () { S().freeze = !S().freeze; grid.classList.toggle('freeze', S().freeze); updateBar(); saveSoon(); });

  /* ================= formül / otomatik toplam ================= */
  function autoSum(fn) {
    var s = selRect(), sh = S(), isNum = function (r, c) { var x = getCell(r, c); return x && x.v !== '' && typeof calc.cell(wb.active, r, c) === 'number'; };
    pushUndo();
    if (s.r1 === s.r2 && s.c1 === s.c2) {
      var r = s.r1, c = s.c1, up = r - 1;
      while (up >= 0 && isNum(up, c)) up--;
      var f;
      if (up < r - 1) f = '=' + fn + '(' + XL.addr(up + 1, c) + ':' + XL.addr(r - 1, c) + ')';
      else { var lf = c - 1; while (lf >= 0 && isNum(r, lf)) lf--; f = lf < c - 1 ? '=' + fn + '(' + XL.addr(r, lf + 1) + ':' + XL.addr(r, c - 1) + ')' : '=' + fn + '()'; }
      ensureCell(r, c).v = f;
    } else if (s.r2 + 1 < sh.rows) {
      for (var cc = s.c1; cc <= s.c2; cc++) ensureCell(s.r2 + 1, cc).v = '=' + fn + '(' + XL.addr(s.r1, cc) + ':' + XL.addr(s.r2, cc) + ')';
    }
    changed();
  }
  $('#btnSum').addEventListener('click', function () { autoSum('TOPLA'); });
  $('#autoFn').addEventListener('change', function () { if (this.value) autoSum(this.value); this.value = ''; });
  var fsel = $('#fnInsert');
  XL.fnNames.slice().sort(function (a, b) { return a.localeCompare(b, 'tr'); }).forEach(function (n) { var o = document.createElement('option'); o.textContent = n; fsel.appendChild(o); });
  fsel.addEventListener('change', function () {
    var n = this.value; this.value = ''; if (!n) return;
    if (!editing) startEdit('f2', '=' + n + '('); else { var v = ce.value, p = ce.selectionStart; ce.value = v.slice(0, p) + n + '(' + v.slice(p); fi.value = ce.value; }
    ce.focus();
  });

  /* ================= sıralama / filtre ================= */
  function sortRegion(desc) {
    var g = currentRegion(), col = anchor.c; if (col < g.c1 || col > g.c2) col = g.c1;
    if (g.r1 === g.r2) return;
    var first = function (c) { return getCell(g.r1, c); };
    var hdr = true, anyNum = false;
    for (var c = g.c1; c <= g.c2; c++) { var v = calc.cell(wb.active, g.r1, c); if (typeof v === 'number') hdr = false; var v2 = g.r1 + 1 <= g.r2 ? calc.cell(wb.active, g.r1 + 1, c) : null; if (typeof v2 === 'number') anyNum = true; }
    if (!anyNum) hdr = false;
    var start = hdr ? g.r1 + 1 : g.r1, rows = [];
    for (var r = start; r <= g.r2; r++) {
      var cellsRow = []; for (var cc = g.c1; cc <= g.c2; cc++) cellsRow.push(getCell(r, cc) ? JSON.parse(JSON.stringify(getCell(r, cc))) : null);
      rows.push({ key: calc.cell(wb.active, r, col), cells: cellsRow, r: r });
    }
    rows.sort(function (a, b) {
      var ka = a.key, kb = b.key, ea = ka == null || ka instanceof XL.FErr, eb = kb == null || kb instanceof XL.FErr;
      if (ea || eb) return ea && eb ? 0 : ea ? 1 : -1;
      var cmp = XL.cmpVals(ka, kb); return desc ? -cmp : cmp;
    });
    pushUndo();
    rows.forEach(function (row, i) {
      var nr = start + i;
      row.cells.forEach(function (x, j) {
        var k = key(nr, g.c1 + j);
        if (!x) { delete S().cells[k]; return; }
        if (typeof x.v === 'string' && x.v.charAt(0) === '=') x.v = XL.shiftFormula(x.v, nr - row.r, 0);
        S().cells[k] = x;
      });
    });
    changed();
  }
  $('#btnSortAsc').addEventListener('click', function () { sortRegion(false); });
  $('#btnSortDesc').addEventListener('click', function () { sortRegion(true); });
  function applyFilter() {
    var f = S().filter;
    $$('tbody tr', grid).forEach(function (tr) { tr.classList.remove('hid'); });
    $('#btnFilter').classList.toggle('on', !!f);
    if (!f) return;
    var m = XL.matcher(f.crit);
    for (var r = f.r1 + 1; r <= f.r2; r++) { var v = calc.cell(wb.active, r, f.c); if (!m(v instanceof XL.FErr ? null : v)) $('tbody tr[data-r="' + r + '"]', grid).classList.add('hid'); }
  }
  $('#btnFilter').addEventListener('click', function () {
    if (S().filter) { S().filter = null; applyFilter(); saveSoon(); return; }
    var g = currentRegion(), c = anchor.c; if (g.r1 === g.r2) { alert('Filtre için önce tablonun içinde bir hücre seçin.'); return; }
    var crit = prompt('"' + XL.colName(c) + '" sütununu filtrele.\nÖrnekler:  >50   <=10   Ali*   =Geçti   <>Kaldı', '');
    if (!crit) return; S().filter = { c: c, crit: crit, r1: g.r1, r2: g.r2 }; applyFilter(); saveSoon();
  });

  /* ================= sayfalar ================= */
  function renderSheetTabs() {
    var h = '';
    wb.sheets.forEach(function (s, i) { h += '<div class="tab' + (i === wb.active ? ' active' : '') + '" data-i="' + i + '">' + s.name.replace(/</g, '&lt;') + '</div>'; });
    $('#sheetTabs').innerHTML = h;
  }
  function switchSheet(i) {
    if (editing) commitEdit(); wb.active = i; anchor = { r: 0, c: 0 }; focus = { r: 0, c: 0 }; renderSheetTabs(); buildGrid(); updateBar(); saveSoon();
  }
  $('#sheetTabs').addEventListener('click', function (e) { var t = e.target.closest('.tab'); if (t) switchSheet(+t.dataset.i); });
  $('#sheetTabs').addEventListener('dblclick', function (e) {
    var t = e.target.closest('.tab'); if (!t) return; var i = +t.dataset.i, old = wb.sheets[i].name, nn = prompt('Sayfa adı:', old);
    if (!nn || nn === old) return; nn = nn.replace(/[!':\[\]\\\/?*]/g, '').trim(); if (!nn || wb.sheets.some(function (s, j) { return j !== i && s.name.toLowerCase() === nn.toLowerCase(); })) { alert('Geçersiz veya tekrarlanan ad.'); return; }
    pushUndo();
    wb.sheets.forEach(function (s) { Object.keys(s.cells).forEach(function (k) { var x = s.cells[k]; if (typeof x.v === 'string' && x.v.charAt(0) === '=') x.v = x.v.replace(new RegExp("(^|[^A-Za-z0-9_.])(?:'" + old.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + "'|" + old.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ")!", 'gi'), function (m, p) { return p + (/[\s]/.test(nn) ? "'" + nn + "'" : nn) + '!'; }); }); });
    wb.sheets[i].name = nn; renderSheetTabs(); changed();
  });
  $('#sheetTabs').addEventListener('contextmenu', function (e) {
    var t = e.target.closest('.tab'); if (!t) return; e.preventDefault();
    var i = +t.dataset.i;
    showCtx(e.clientX, e.clientY, [['Sayfayı sil', function () {
      if (wb.sheets.length < 2) { alert('Son sayfa silinemez.'); return; } if (!confirm('"' + wb.sheets[i].name + '" sayfası silinsin mi?')) return;
      pushUndo(); wb.sheets.splice(i, 1); wb.active = Math.min(wb.active, wb.sheets.length - 1); newCalc(); renderSheetTabs(); buildGrid(); updateBar(); saveSoon();
    }], ['Yeniden adlandır (çift tıkla)', function () { }]]);
  });
  $('#addSheet').addEventListener('click', function () {
    var n = wb.sheets.length + 1, name; do { name = 'Sayfa' + n++; } while (wb.sheets.some(function (s) { return s.name === name; }));
    pushUndo(); wb.sheets.push(newSheet(name)); switchSheet(wb.sheets.length - 1);
  });

  /* ================= bağlam menüsü ================= */
  function showCtx(x, y, items) {
    var m = $('#ctx'); m.innerHTML = ''; items.forEach(function (it) { var d = document.createElement('div'); d.textContent = it[0]; d.addEventListener('click', function () { m.hidden = true; it[1](); }); m.appendChild(d); });
    m.style.left = Math.min(x, innerWidth - 200) + 'px'; m.style.top = Math.min(y, innerHeight - items.length * 32 - 10) + 'px'; m.hidden = false;
  }
  grid.addEventListener('contextmenu', function (e) {
    e.preventDefault();
    showCtx(e.clientX, e.clientY, [
      ['Kes', function () { captureClip(true); }], ['Kopyala', function () { captureClip(false); try { navigator.clipboard.writeText(clip.text); } catch (er) {} }],
      ['Yapıştır', function () { $('#btnPaste').click(); }],
      ['Üste satır ekle', function () { $('#btnInsRow').click(); }], ['Satırı sil', function () { $('#btnDelRow').click(); }],
      ['Sola sütun ekle', function () { $('#btnInsCol').click(); }], ['Sütunu sil', function () { $('#btnDelCol').click(); }],
      ['İçeriği temizle', clearValues]
    ]);
  });
  document.addEventListener('mousedown', function (e) { if (!e.target.closest('#ctx')) $('#ctx').hidden = true; });

  /* ================= dosya işlemleri ================= */
  function fname(ext) { return ($('#bookName').value || 'Kitap').replace(/[\\/:*?"<>|]+/g, '_') + ext; }
  function download(name, mime, content) {
    var a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([content], { type: mime })); a.download = name; document.body.appendChild(a); a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
  }
  function usedRect() {
    var r2 = -1, c2 = -1; Object.keys(S().cells).forEach(function (k) { var p = k.split(','), x = S().cells[k]; if (x.v !== '') { r2 = Math.max(r2, +p[0]); c2 = Math.max(c2, +p[1]); } });
    return { r2: r2, c2: c2 };
  }
  $('#btnCsv').addEventListener('click', function () {
    var u = usedRect(), out = [];
    for (var r = 0; r <= u.r2; r++) { var row = []; for (var c = 0; c <= u.c2; c++) { var x = getCell(r, c), t = x && x.v !== '' ? displayText(r, c) : ''; if (/[;"\n\r]/.test(t)) t = '"' + t.replace(/"/g, '""') + '"'; row.push(t); } out.push(row.join(';')); }
    download(fname('.csv'), 'text/csv;charset=utf-8', '﻿' + out.join('\r\n'));
  });
  $('#btnXls').addEventListener('click', function () {
    var u = usedRect(), h = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8"></head><body><table border="1">';
    for (var r = 0; r <= u.r2; r++) {
      h += '<tr>';
      for (var c = 0; c <= u.c2; c++) {
        var x = getCell(r, c) || {}, td = tds[r][c], st = 'text-align:' + td.style.textAlign + ';' + (x.b ? 'font-weight:bold;' : '') + (x.bg ? 'background:' + x.bg + ';' : '') + (x.fg ? 'color:' + x.fg + ';' : '');
        h += '<td style="' + st + '">' + td.textContent.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</td>';
      }
      h += '</tr>';
    }
    download(fname('.xls'), 'application/vnd.ms-excel', '﻿' + h + '</table></body></html>');
  });
  $('#btnImport').addEventListener('click', function () { $('#fileCsv').click(); });
  $('#fileCsv').addEventListener('change', function () {
    var f = this.files[0]; if (!f) return; var fr = new FileReader();
    fr.onload = function () {
      var t = String(fr.result).replace(/^﻿/, ''), first = t.split(/\r?\n/)[0], d = [';', '\t', ','].map(function (x) { return [x, first.split(x).length]; }).sort(function (a, b) { return b[1] - a[1]; })[0][0];
      var rows = [], row = [], cur = '', q = false;
      for (var i = 0; i < t.length; i++) {
        var ch = t[i];
        if (q) { if (ch === '"') { if (t[i + 1] === '"') { cur += '"'; i++; } else q = false; } else cur += ch; }
        else if (ch === '"') q = true; else if (ch === d) { row.push(cur); cur = ''; } else if (ch === '\n') { row.push(cur.replace(/\r$/, '')); rows.push(row); row = []; cur = ''; } else cur += ch;
      }
      if (cur || row.length) { row.push(cur); rows.push(row); }
      pushUndo(); var sh = S(); sh.cells = {}; sh.rows = Math.min(MAX_ROWS, Math.max(100, rows.length + 20)); sh.cols = Math.min(MAX_COLS, Math.max(26, Math.max.apply(null, rows.map(function (r) { return r.length; })) + 2));
      rows.forEach(function (rw, r) { if (r >= sh.rows) return; rw.forEach(function (v, c) { if (v !== '' && c < sh.cols) { var p = parseInput(v); var x = { v: p.v }; if (p.nf) { x.nf = p.nf; if (p.dp != null) x.dp = p.dp; } sh.cells[key(r, c)] = x; } }); });
      $('#bookName').value = f.name.replace(/\.[^.]+$/, ''); newCalc(); buildGrid(); setSel(0, 0); saveSoon();
    };
    fr.readAsText(f); this.value = '';
  });
  $('#btnNew').addEventListener('click', function () { if (!confirm('Yeni çalışma kitabı açılsın mı? Kaydedilmemiş değişiklikler silinir.')) return; pushUndo(); wb = newBook(); newCalc(); renderSheetTabs(); buildGrid(); setSel(0, 0); $('#bookName').value = wb.name; saveSoon(); });
  $('#bookName').addEventListener('input', function () { wb.name = this.value; saveSoon(); });
  $('#btnUndo').addEventListener('click', undo); $('#btnRedo').addEventListener('click', redo);

  $('#btnSample').addEventListener('click', function () {
    if (!confirm('Örnek not tablosu yüklensin mi? Bu sayfadaki veriler silinir.')) return;
    pushUndo(); var sh = S(); sh.cells = {}; sh.colW = { 0: 130, 4: 100, 5: 100, 7: 150 };
    var data = [['Öğrenci', 'Matematik', 'Fen', 'Türkçe', 'Ortalama', 'Durum'], ['Ali', 70, 55, 80], ['Ayşe', 90, 85, 95], ['Mehmet', 40, 35, 50], ['Zeynep', 65, 70, 60], ['Can', 30, 45, 25], ['Elif', 88, 92, 79], ['Burak', 52, 48, 60], ['Selin', 100, 95, 98]];
    data.forEach(function (row, r) { row.forEach(function (v, c) { sh.cells[key(r, c)] = { v: String(v) }; }); if (r > 0) { sh.cells[key(r, 4)] = { v: '=YUVARLA(ORTALAMA(B' + (r + 1) + ':D' + (r + 1) + ');1)', nf: 'num', dp: 1 }; sh.cells[key(r, 5)] = { v: '=EĞER(E' + (r + 1) + '>=50;"Geçti";"Kaldı")' }; } });
    sh.cells[key(10, 0)] = { v: 'Sınıf ortalaması', b: 1 }; sh.cells[key(10, 4)] = { v: '=ORTALAMA(E2:E9)', nf: 'num', dp: 1, b: 1 };
    sh.cells[key(11, 0)] = { v: 'Geçen sayısı', b: 1 }; sh.cells[key(11, 4)] = { v: '=EĞERSAY(F2:F9;"Geçti")', b: 1 };
    sh.cells[key(12, 0)] = { v: 'En yüksek ortalama', b: 1 }; sh.cells[key(12, 4)] = { v: '=MAK(E2:E9)', nf: 'num', dp: 1, b: 1 };
    sh.cells[key(13, 0)] = { v: 'En başarılı öğrenci', b: 1 }; sh.cells[key(13, 4)] = { v: '=İNDİS(A2:A9;KAÇINCI(E13;E2:E9;0))', b: 1 };
    sh.cells[key(10, 7)] = { v: 'Öğrenci ara →' }; sh.cells[key(10, 8)] = { v: 'Ayşe', bg: '#fff2cc' };
    sh.cells[key(11, 7)] = { v: 'Fen notu →' }; sh.cells[key(11, 8)] = { v: '=DÜŞEYARA(I11;A2:D9;3;0)', b: 1 };
    sh.colW[7] = 110; wb.name = 'Not Çizelgesi'; $('#bookName').value = wb.name;
    newCalc(); buildGrid(); anchor = { r: 0, c: 0 }; focus = { r: 8, c: 5 }; renderSel(); $('#btnTableFmt').click(); setSel(0, 0); saveSoon();
  });

  /* ================= grafik ================= */
  function chartData() {
    var g = currentRegion(), hdr = $('#chartHdr').checked, cats = [], series = [];
    if (g.r1 === g.r2 && g.c1 === g.c2) return null;
    var numAt = function (r, c) { var v = calc.cell(wb.active, r, c); return typeof v === 'number' ? v : null; };
    var firstColText = false; for (var r0 = g.r1 + (hdr ? 1 : 0); r0 <= g.r2; r0++) { var v0 = calc.cell(wb.active, r0, g.c1); if (typeof v0 === 'string') firstColText = true; }
    var cStart = firstColText || g.c1 === g.c2 ? (g.c1 === g.c2 ? g.c1 : g.c1 + 1) : g.c1;
    for (var r = g.r1 + (hdr ? 1 : 0); r <= g.r2; r++) { var lab = firstColText ? calc.cell(wb.active, r, g.c1) : r - g.r1 + (hdr ? 0 : 1); cats.push(String(lab == null ? '' : lab instanceof XL.FErr ? lab.code : lab)); }
    for (var c = cStart; c <= g.c2; c++) {
      var vals = [], any = false; for (var rr = g.r1 + (hdr ? 1 : 0); rr <= g.r2; rr++) { var n = numAt(rr, c); vals.push(n); if (n != null) any = true; }
      if (any) { var nm = hdr ? calc.cell(wb.active, g.r1, c) : 'Seri ' + (series.length + 1); series.push({ name: String(nm == null ? '' : nm), vals: vals }); }
    }
    return series.length ? { cats: cats, series: series } : null;
  }
  var PAL = ['#107c41', '#2b6de0', '#e08a00', '#d62e8a', '#6a3dd1', '#00a3a3', '#c00000', '#7a8b00'];
  function drawChart() {
    var cv = $('#chartCv'), ctx = cv.getContext('2d'), W = cv.width, H = cv.height, d = chartData(), type = $('#chartType').value;
    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, W, H); ctx.font = '12px Segoe UI, Arial'; ctx.textBaseline = 'middle';
    if (!d) { ctx.fillStyle = '#555'; ctx.textAlign = 'center'; ctx.fillText('Önce sayısal veri içeren bir aralık seçin (başlıklarıyla birlikte).', W / 2, H / 2); return; }
    var n = d.cats.length;
    if (type === 'pie') {
      var vals = d.series[0].vals.map(function (v) { return Math.max(0, v || 0); }), tot = vals.reduce(function (a, b) { return a + b; }, 0) || 1, a0 = -Math.PI / 2, cx = 190, cy = H / 2, R = 120;
      vals.forEach(function (v, i) { var a1 = a0 + v / tot * Math.PI * 2; ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, R, a0, a1); ctx.closePath(); ctx.fillStyle = PAL[i % PAL.length]; ctx.fill(); a0 = a1; });
      ctx.textAlign = 'left'; d.cats.forEach(function (c, i) { if (i > 14) return; ctx.fillStyle = PAL[i % PAL.length]; ctx.fillRect(350, 30 + i * 20, 12, 12); ctx.fillStyle = '#222'; ctx.fillText(c + ' (' + Math.round(vals[i] / tot * 100) + '%)', 368, 36 + i * 20); });
      return;
    }
    var L = 56, T = 24, B = 46, Rr = 16, pw = W - L - Rr, ph = H - T - B, max = 0, min = 0;
    d.series.forEach(function (s) { s.vals.forEach(function (v) { if (v != null) { max = Math.max(max, v); min = Math.min(min, v); } }); });
    if (max === min) max = min + 1;
    var step = Math.pow(10, Math.floor(Math.log10(max - min))), top = Math.ceil(max / step) * step, bot = min < 0 ? Math.floor(min / step) * step : 0;
    function Y(v) { return T + ph - (v - bot) / (top - bot) * ph; }
    ctx.textAlign = 'right'; ctx.strokeStyle = '#e1e4e8'; ctx.fillStyle = '#555';
    for (var i = 0; i <= 5; i++) { var v = bot + (top - bot) * i / 5, y = Y(v); ctx.beginPath(); ctx.moveTo(L, y); ctx.lineTo(W - Rr, y); ctx.stroke(); ctx.fillText(XL.formatNumber(v), L - 6, y); }
    var gw = pw / n, ns = d.series.length;
    ctx.textAlign = 'center';
    d.cats.forEach(function (c, i) { var t = c.length > 10 ? c.slice(0, 9) + '…' : c; if (n <= 24 || i % Math.ceil(n / 24) === 0) ctx.fillText(t, L + gw * (i + .5), H - B + 14); });
    d.series.forEach(function (s, si) {
      ctx.fillStyle = ctx.strokeStyle = PAL[si % PAL.length];
      if (type === 'bar') { var bw = Math.min(40, gw * .8 / ns); s.vals.forEach(function (v, i) { if (v == null) return; var x = L + gw * i + (gw - bw * ns) / 2 + bw * si, y0 = Y(Math.max(0, bot)), y1 = Y(v); ctx.fillRect(x, Math.min(y0, y1), bw - 1, Math.abs(y0 - y1)); }); }
      else { ctx.lineWidth = 2; ctx.beginPath(); var started = false; s.vals.forEach(function (v, i) { if (v == null) return; var x = L + gw * (i + .5), y = Y(v); if (!started) { ctx.moveTo(x, y); started = true; } else ctx.lineTo(x, y); }); ctx.stroke(); s.vals.forEach(function (v, i) { if (v != null) { ctx.beginPath(); ctx.arc(L + gw * (i + .5), Y(v), 3, 0, 7); ctx.fill(); } }); }
    });
    ctx.textAlign = 'left'; ctx.strokeStyle = '#999'; ctx.strokeRect(L, T, pw, ph);
    d.series.forEach(function (s, si) { ctx.fillStyle = PAL[si % PAL.length]; ctx.fillRect(L + si * 110, 6, 10, 10); ctx.fillStyle = '#222'; ctx.fillText(s.name.slice(0, 12), L + si * 110 + 14, 11); });
  }
  $('#btnChart').addEventListener('click', function () { $('#chartBox').hidden = false; drawChart(); });
  $('#chartType').addEventListener('change', drawChart); $('#chartHdr').addEventListener('change', drawChart);
  $('#chartClose').addEventListener('click', function () { $('#chartBox').hidden = true; });
  $('#chartPng').addEventListener('click', function () { var a = document.createElement('a'); a.href = $('#chartCv').toDataURL('image/png'); a.download = fname('-grafik.png'); a.click(); });

  /* ================= sekmeler ================= */
  $$('#ribbonTabs button').forEach(function (b) { b.addEventListener('click', function () {
    $$('#ribbonTabs button').forEach(function (x) { x.classList.toggle('active', x === b); }); $$('.panel').forEach(function (p) { p.classList.toggle('active', p.dataset.panel === b.dataset.tab); });
  }); });
  $('.ribbon').addEventListener('mousedown', function (e) { if (e.target.closest('button') && !editing) e.preventDefault(); });
  window.addEventListener('beforeunload', save);
  window.addEventListener('resize', placeHandle);

  /* ================= başlat ================= */
  // Ödev olarak açıldığında çalışma kitabı BOŞ başlar; önceki çalışma varsa Kayıtlarım penceresi sorar.
  var pendingDraft = SS.inAssignment ? load() : null;
  wb = (SS.inAssignment ? null : load()) || newBook(); newCalc();
  $('#bookName').value = wb.name || 'Çalışma Kitabı1';
  renderSheetTabs(); buildGrid(); setSel(0, 0); wrap.focus();
  if (window.SaveSlots && SaveSlots.mount) {
    SaveSlots.mount({
      app: 'excel', title: 'Çalışma Kitabı', draftStorageKey: KEY,
      getState: function () { return JSON.stringify(wb); },
      currentName: function () { return $('#bookName').value || wb.name; },
      setState: function (js) {
        wb = JSON.parse(js); newCalc(); undoStack = []; redoStack = [];
        $('#bookName').value = wb.name || 'Çalışma Kitabı1';
        buildGrid(); setSel(0, 0); renderSheetTabs(); saveSoon();
      },
      readDraft: function () { return pendingDraft ? { data: JSON.stringify(pendingDraft), name: pendingDraft.name } : null; },
      isBlankData: function (js) { try { return JSON.parse(js).sheets.every(function (s) { return !Object.keys(s.cells || {}).length; }); } catch (e) { return true; } }
    });
  }
})();
