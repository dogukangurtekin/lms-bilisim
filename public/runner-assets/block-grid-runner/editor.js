/*
 * Blok Kodlama — sürükle-bırak blok editörü (v3, bağımlılıksız).
 *
 * Bloklar alt alta "mıknatıs gibi" yapışır: bir bloğu tutup çalışma alanına getirince en yakın bağlantı noktasında
 * bir yapışma çizgisi belirir, bırakınca blok oraya oturur. Döngü/koşul kutularının içine de bırakılabilir.
 * Çalışma alanı dışına (ör. palete) bırakılan blok silinir. Dokunmatik ekranda da (Pointer Events) çalışır.
 * Palette bir bloğa kısa dokunmak/tıklamak onu programın sonuna (ya da seçili kutunun içine) ekler.
 *
 * Program modeli doğrudan engine.js'in AST'sidir:
 *   {t:'forward'|'left'|'right', id} | {t:'repeat', n, body:[]} | {t:'until', body:[]} | {t:'if', cond, then:[], else:[]}
 *
 * Bu dosyadaki "Model" bölümü DOM'a dokunmaz; Node'da test edilir.
 */
(function (root) {
  'use strict';

  var BLOCKS = {
    forward: { label: '⬆ İleri git', cls: 'motion' },
    left: { label: '↺ Sola dön', cls: 'turn' },
    right: { label: '↻ Sağa dön', cls: 'turn' },
    repeat: { label: '🔁 Tekrarla', cls: 'loop' },
    until: { label: '🔁 Yıldıza ulaşana kadar tekrarla', cls: 'loop' },
    if: { label: '❓ Eğer', cls: 'logic' },
  };
  var SLOTS = { repeat: ['body'], until: ['body'], if: ['then', 'else'] };

  // =================================================================== Model (DOM yok)
  var seq = 0;
  function newId() { return 'b' + (++seq) + Math.random().toString(36).slice(2, 5); }
  function makeNode(key) {
    var n = { t: key, id: newId() };
    if (key === 'repeat') { n.n = 2; n.body = []; }
    if (key === 'until') n.body = [];
    if (key === 'if') { n.cond = 'blocked'; n.then = []; n.else = []; }
    return n;
  }
  function subLists(node) { return (SLOTS[node.t] || []).map(function (s) { return node[s]; }).filter(Boolean); }
  function find(rootList, id) {
    function walk(list) {
      for (var i = 0; i < list.length; i++) {
        if (list[i].id === id) return { list: list, index: i, node: list[i] };
        var subs = subLists(list[i]);
        for (var k = 0; k < subs.length; k++) { var r = walk(subs[k]); if (r) return r; }
      }
      return null;
    }
    return walk(rootList);
  }
  function listFor(rootList, ref) {
    if (!ref || ref.owner == null) return rootList;
    var f = find(rootList, ref.owner);
    return f && f.node[ref.slot] ? f.node[ref.slot] : rootList;
  }
  function insertAt(rootList, ref, index, node) {
    var l = listFor(rootList, ref);
    l.splice(Math.max(0, Math.min(index, l.length)), 0, node);
    return node;
  }
  function removeNode(rootList, id) { var f = find(rootList, id); if (!f) return null; f.list.splice(f.index, 1); return f.node; }
  function count(list) { var c = 0; (list || []).forEach(function (n) { c++; subLists(n).forEach(function (s) { c += count(s); }); }); return c; }
  function contains(node, id) { return subLists(node).some(function (l) { return l.some(function (c) { return c.id === id || contains(c, id); }); }); }

  // Kayıtlı/dış kaynaklı programı güvenli biçimde doğrula (bilinmeyen tür, bozuk sayı vb. atılır)
  function sanitize(list, allowed, depth) {
    var out = [];
    if (!Array.isArray(list) || (depth || 0) > 8) return out;
    list.forEach(function (n) {
      if (!n || typeof n !== 'object' || !BLOCKS[n.t]) return;
      var c = makeNode(n.t);
      if (n.t === 'repeat') c.n = Math.max(1, Math.min(50, parseInt(n.n, 10) || 2));
      if (n.t === 'if') c.cond = n.cond === 'open' ? 'open' : 'blocked';
      (SLOTS[n.t] || []).forEach(function (s) { c[s] = sanitize(n[s], allowed, (depth || 0) + 1); });
      out.push(c);
    });
    return out;
  }
  function cloneJson(x) { return JSON.parse(JSON.stringify(x)); }

  // Bırakma noktaları arasından imlece en uygun olanı seç: imlecin içinde bulunduğu en iç kutu önceliklidir.
  function nearest(pts, px, py) {
    var best = null, bestCost = Infinity;
    pts.forEach(function (p) {
      var inside = px >= p.rect.left - 10 && px <= p.rect.right + 10 && py >= p.rect.top - 14 && py <= p.rect.bottom + 14;
      var dx = Math.max(p.x - px, 0, px - (p.x + p.w)), dy = py - p.y;
      var cost = Math.hypot(dx, dy) - (inside ? 160 + p.depth * 60 : 0);
      if (cost < bestCost) { bestCost = cost; best = p; }
    });
    return best;
  }

  var Model = { nearest: nearest, BLOCKS: BLOCKS, SLOTS: SLOTS, makeNode: makeNode, find: find, listFor: listFor, insertAt: insertAt, removeNode: removeNode, count: count, contains: contains, sanitize: sanitize, newId: newId };

  // =================================================================== Arayüz (DOM)
  function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; }

  function create(opts) {
    var ws = opts.workspace, palette = opts.palette, onChange = opts.onChange || function () {};
    var program = [];
    var keys = ['forward', 'left', 'right', 'repeat'];
    var selectedId = null, runId = null, drag = null, dropLine = null;

    ws.classList.add('ed-ws');
    function change(silent) { if (!silent) onChange(); }

    // ---------------------------------------------------------------- çizim
    function render() {
      ws.innerHTML = '';
      var prog = el('div', 'ed-prog');
      var head = el('div', 'ed-hat', '▶ BAŞLA'); head.title = 'Programın buradan başlar';
      prog.appendChild(head);
      var body = el('div', 'ed-prog-body');
      body.appendChild(listEl(program, null, null));
      prog.appendChild(body);
      ws.appendChild(prog);
      dropLine = el('div', 'ed-dropline'); dropLine.style.display = 'none'; ws.appendChild(dropLine);
      applyMarks();
    }

    function listEl(list, owner, slot) {
      var l = el('div', 'ed-list'); l.dataset.owner = owner || ''; l.dataset.slot = slot || '';
      list.forEach(function (n) { l.appendChild(blockEl(n)); });
      if (!list.length) l.appendChild(el('div', 'ed-empty', owner ? 'buraya bırak' : 'Blokları buraya sürükle ya da yukarıdan bir bloğa dokun'));
      return l;
    }

    function blockEl(n) {
      var d = BLOCKS[n.t], b = el('div', 'ed-blk ed-' + d.cls + (SLOTS[n.t] ? ' ed-c' : '')); b.dataset.id = n.id;
      var head = el('div', 'ed-head');
      head.appendChild(el('span', 'ed-label', d.label));
      if (n.t === 'repeat') {
        var inp = el('input', 'ed-num'); inp.type = 'number'; inp.min = '1'; inp.max = '50'; inp.value = String(n.n); inp.setAttribute('aria-label', 'Tekrar sayısı');
        inp.addEventListener('pointerdown', function (e) { e.stopPropagation(); });
        inp.addEventListener('change', function () { n.n = Math.max(1, Math.min(50, parseInt(inp.value, 10) || 1)); inp.value = String(n.n); change(); });
        head.appendChild(inp); head.appendChild(el('span', 'ed-label', 'kez'));
      }
      if (n.t === 'if') {
        var sel = el('select', 'ed-sel');
        [['blocked', 'önüm kapalı'], ['open', 'önüm açık']].forEach(function (o) { var op = el('option', null, o[1]); op.value = o[0]; if (n.cond === o[0]) op.selected = true; sel.appendChild(op); });
        sel.addEventListener('pointerdown', function (e) { e.stopPropagation(); });
        sel.addEventListener('change', function () { n.cond = sel.value === 'open' ? 'open' : 'blocked'; change(); });
        head.appendChild(sel); head.appendChild(el('span', 'ed-label', 'ise'));
      }
      head.addEventListener('pointerdown', function (e) { startDrag(e, n, false); });
      b.appendChild(head);
      (SLOTS[n.t] || []).forEach(function (s, i) {
        if (n.t === 'if' && i === 1) b.appendChild(el('div', 'ed-sep', 'değilse'));
        var slot = el('div', 'ed-slot'); slot.appendChild(listEl(n[s], n.id, s)); b.appendChild(slot);
      });
      if (SLOTS[n.t]) b.appendChild(el('div', 'ed-foot'));
      return b;
    }

    function applyMarks() {
      ws.querySelectorAll('.ed-blk').forEach(function (b) {
        b.classList.toggle('ed-sel-on', b.dataset.id === selectedId);
        b.classList.toggle('ed-run', b.dataset.id === runId);
      });
    }

    // ---------------------------------------------------------------- palet
    function buildPalette() {
      if (!palette) return;
      palette.innerHTML = '';
      keys.forEach(function (k) {
        var b = el('button', 'pal-btn pal-' + BLOCKS[k].cls, BLOCKS[k].label); b.type = 'button'; b.dataset.key = k;
        b.title = 'Sürükle ya da dokun: programa ekle';
        b.addEventListener('pointerdown', function (e) { startDrag(e, makeNode(k), true); });
        palette.appendChild(b);
      });
      palette.appendChild(el('span', 'pal-tip', 'Sürükle-bırak ya da dokun: blok alt alta yapışır.'));
    }

    // ---------------------------------------------------------------- sürükle-bırak
    function wsInside(x, y) {
      var r = ws.getBoundingClientRect();
      return x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;
    }

    // Bırakılabilecek tüm bağlantı noktaları: her listenin her aralığı
    function dropPoints(skipEl) {
      var pts = [];
      ws.querySelectorAll('.ed-list').forEach(function (l) {
        if (skipEl && skipEl.contains(l)) return; // bloğu kendi içine bırakma
        var r = l.getBoundingClientRect(), kids = Array.prototype.filter.call(l.children, function (c) { return c.classList.contains('ed-blk') && c !== skipEl; });
        var depth = 0, p = l; while ((p = p.parentElement) && p !== ws) if (p.classList && p.classList.contains('ed-list')) depth++;
        var ref = { owner: l.dataset.owner || null, slot: l.dataset.slot || null };
        if (!kids.length) pts.push({ ref: ref, index: 0, x: r.left + 10, y: r.top + 8, w: Math.max(40, r.width - 20), depth: depth, rect: r });
        else for (var i = 0; i <= kids.length; i++) {
          var kr = (i === 0 ? kids[0] : kids[i - 1]).getBoundingClientRect();
          pts.push({ ref: ref, index: i, x: kr.left, y: i === 0 ? kr.top : kr.bottom, w: kr.width, depth: depth, rect: r });
        }
      });
      return pts;
    }

    function showLine(p) {
      if (!dropLine) return;
      if (!p) { dropLine.style.display = 'none'; return; }
      var wr = ws.getBoundingClientRect();
      dropLine.style.display = 'block';
      dropLine.style.left = (p.x - wr.left + ws.scrollLeft) + 'px';
      dropLine.style.top = (p.y - wr.top + ws.scrollTop - 3) + 'px';
      dropLine.style.width = Math.max(60, p.w) + 'px';
    }

    function startDrag(e, node, fromPalette) {
      if (e.button != null && e.button !== 0) return;
      e.preventDefault();
      var startX = e.clientX, startY = e.clientY, moved = false;
      var srcEl = fromPalette ? null : ws.querySelector('.ed-blk[data-id="' + node.id + '"]');
      var ghost = null, target = null;

      function makeGhost() {
        ghost = el('div', 'ed-ghost ed-' + BLOCKS[node.t].cls, BLOCKS[node.t].label);
        if (node.t === 'repeat') ghost.textContent = BLOCKS.repeat.label + ' ' + node.n + ' kez';
        document.body.appendChild(ghost);
        if (srcEl) srcEl.classList.add('ed-dragging');
        ws.classList.add('ed-active');
      }
      function move(ev) {
        var dx = ev.clientX - startX, dy = ev.clientY - startY;
        if (!moved && Math.hypot(dx, dy) < 6) return;
        if (!moved) { moved = true; makeGhost(); }
        ghost.style.left = (ev.clientX + 8) + 'px'; ghost.style.top = (ev.clientY + 8) + 'px';
        var inside = wsInside(ev.clientX, ev.clientY);
        ws.classList.toggle('ed-trash', !inside && !fromPalette);
        target = inside ? nearest(dropPoints(srcEl), ev.clientX, ev.clientY) : null;
        showLine(target);
      }
      function end(ev) {
        document.removeEventListener('pointermove', move); document.removeEventListener('pointerup', end); document.removeEventListener('pointercancel', cancel);
        if (ghost) ghost.remove();
        ws.classList.remove('ed-active', 'ed-trash'); showLine(null);
        if (!ev) { render(); return; } // iptal (ör. dokunmatik kaydırma devraldı): hiçbir şey değişmesin
        if (!moved) { // kısa dokunma/tıklama
          if (fromPalette) addToEnd(node); else toggleSelect(node.id);
          return;
        }
        var inside = ev && wsInside(ev.clientX, ev.clientY);
        if (target && inside) {
          if (!fromPalette) removeNode(program, node.id);
          insertAt(program, target.ref, target.index, node);
          selectedId = node.id; render(); flash(node.id); change();
        } else if (!fromPalette) { // çalışma alanı dışına bırakıldı: sil
          removeNode(program, node.id); if (selectedId === node.id) selectedId = null; render(); change();
        } else render();
      }
      function cancel() { end(null); }
      document.addEventListener('pointermove', move); document.addEventListener('pointerup', end); document.addEventListener('pointercancel', cancel);
    }

    function flash(id) {
      var b = ws.querySelector('.ed-blk[data-id="' + id + '"]');
      if (b) { b.classList.add('ed-pop'); setTimeout(function () { b.classList.remove('ed-pop'); }, 400); }
    }
    function toggleSelect(id) { selectedId = selectedId === id ? null : id; applyMarks(); }

    function addToEnd(node) {
      var sel = selectedId && find(program, selectedId);
      var ref = { owner: null, slot: null }, idx = program.length;
      if (sel && SLOTS[sel.node.t]) { var slot = SLOTS[sel.node.t][0]; ref = { owner: sel.node.id, slot: slot }; idx = sel.node[slot].length; }
      insertAt(program, ref, idx, node);
      selectedId = node.id; render(); flash(node.id); change();
      var last = ws.querySelector('.ed-blk[data-id="' + node.id + '"]'); if (last && last.scrollIntoView) { try { last.scrollIntoView({ block: 'nearest' }); } catch (e) {} }
    }

    document.addEventListener('keydown', function (e) {
      if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId && !/INPUT|SELECT|TEXTAREA/.test((e.target && e.target.tagName) || '')) {
        removeNode(program, selectedId); selectedId = null; render(); change(); e.preventDefault();
      }
    });

    // ---------------------------------------------------------------- dış API
    buildPalette(); render();
    return {
      setKeys: function (k) { keys = k.filter(function (x) { return BLOCKS[x]; }); buildPalette(); },
      ast: function () { return program; },
      count: function () { return count(program); },
      serialize: function () { return cloneJson(program); },
      load: function (saved) { program = sanitize(saved); selectedId = null; render(); },
      clear: function () { program = []; selectedId = null; render(); },
      highlight: function (id) { runId = id || null; applyMarks(); },
      add: function (key) { addToEnd(makeNode(key)); },
    };
  }

  root.BlockEditor = { create: create, Model: Model, BLOCKS: BLOCKS };
  if (typeof module === 'object' && module.exports) module.exports = root.BlockEditor;
})(typeof self !== 'undefined' ? self : this);
