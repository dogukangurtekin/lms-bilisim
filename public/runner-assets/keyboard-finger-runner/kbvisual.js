/* Ortak renkli klavye + el görseli (Klavye Parmak Eğitimi ve Klavye Yarışması kullanır) */
(function (g) {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  /* ---- Türkçe Q düzeni: [kod, normal, shift etiketi, parmak, genişlik sınıfı] ---- */
  var F = function (c, ch, sh, f, cls) { return { code: c, ch: ch, sh: sh || '', f: f, cls: cls || '' }; };
  var ROWS = [
    [F('Backquote', 'é', '"', 'L5'), F('Digit1', '1', '!', 'L5'), F('Digit2', '2', "'", 'L4'), F('Digit3', '3', '^', 'L3'), F('Digit4', '4', '+', 'L2'), F('Digit5', '5', '%', 'L2'), F('Digit6', '6', '&', 'R2'), F('Digit7', '7', '/', 'R2'), F('Digit8', '8', '(', 'R3'), F('Digit9', '9', ')', 'R4'), F('Digit0', '0', '=', 'R5'), F('Minus', '*', '?', 'R5'), F('Equal', '-', '_', 'R5'), F('Backspace', 'Sil', '', 'R5', 'w2 mod')],
    ['s15', F('KeyQ', 'q', '', 'L5'), F('KeyW', 'w', '', 'L4'), F('KeyE', 'e', '', 'L3'), F('KeyR', 'r', '', 'L2'), F('KeyT', 't', '', 'L2'), F('KeyY', 'y', '', 'R2'), F('KeyU', 'u', '', 'R2'), F('KeyI', 'ı', '', 'R3'), F('KeyO', 'o', '', 'R4'), F('KeyP', 'p', '', 'R5'), F('BracketLeft', 'ğ', '', 'R5'), F('BracketRight', 'ü', '', 'R5'), 's15'],
    ['s18', F('KeyA', 'a', '', 'L5'), F('KeyS', 's', '', 'L4'), F('KeyD', 'd', '', 'L3'), F('KeyF', 'f', '', 'L2', 'home'), F('KeyG', 'g', '', 'L2'), F('KeyH', 'h', '', 'R2'), F('KeyJ', 'j', '', 'R2', 'home'), F('KeyK', 'k', '', 'R3'), F('KeyL', 'l', '', 'R4'), F('Semicolon', 'ş', '', 'R5'), F('Quote', 'i', '', 'R5'), F('Backslash', ',', ';', 'R5'), F('Enter', 'Enter', '', 'R5', 'w25 mod')],
    [F('ShiftLeft', 'Shift', '', 'L5', 'w18 mod'), F('IntlBackslash', '<', '>', 'L5'), F('KeyZ', 'z', '', 'L5'), F('KeyX', 'x', '', 'L4'), F('KeyC', 'c', '', 'L3'), F('KeyV', 'v', '', 'L2'), F('KeyB', 'b', '', 'L2'), F('KeyN', 'n', '', 'R2'), F('KeyM', 'm', '', 'R2'), F('Comma', 'ö', '', 'R3'), F('Period', 'ç', '', 'R4'), F('Slash', '.', ':', 'R5'), F('ShiftRight', 'Shift', '', 'R5', 'w25 mod')],
    ['s3', F('Space', ' ', '', 'T', 'w6 sp'), 's3']
  ];
  var BYCH = {}, BYCODE = {};
  ROWS.forEach(function (r) { r.forEach(function (k) { if (typeof k === 'object') { BYCODE[k.code] = k; if (k.ch.length === 1 || k.ch === ' ') BYCH[k.ch] = k; } }); });
  var COLORS = { L5: 'var(--c-L5)', L4: 'var(--c-L4)', L3: 'var(--c-L3)', L2: 'var(--c-L2)', R2: 'var(--c-R2)', R3: 'var(--c-R3)', R4: 'var(--c-R4)', R5: 'var(--c-R5)', T: 'var(--c-T)' };
  var FNAME = { L5: 'sol serçe parmak', L4: 'sol yüzük parmağı', L3: 'sol orta parmak', L2: 'sol işaret parmağı', R2: 'sağ işaret parmağı', R3: 'sağ orta parmak', R4: 'sağ yüzük parmağı', R5: 'sağ serçe parmak', T: 'başparmak' };

  /* ---- klavye çizimi ---- */
  var keyEls = {}, kbEl = null;
  function buildKb() {
  kbEl.innerHTML = '';
  ROWS.forEach(function (r) {
    var row = document.createElement('div'); row.className = 'kr';
    r.forEach(function (k) {
      if (typeof k === 'string') { var sp = document.createElement('div'); sp.className = 'spacer ' + k; row.appendChild(sp); return; }
      var el = document.createElement('div'); el.className = 'k ' + k.cls; el.dataset.code = k.code;
      if (!/mod/.test(k.cls)) el.style.background = COLORS[k.f];
      if (k.f === 'T') el.style.background = COLORS.T;
      el.innerHTML = (k.sh ? '<span class="sh">' + k.sh + '</span>' : '') + (k.ch === ' ' ? '' : k.ch.length === 1 ? k.ch.toLocaleUpperCase('tr') : k.ch);
      if (k.code === 'KeyX' || k.code === 'KeyC' || k.code === 'KeyM') el.classList.add('dot');
      keyEls[k.code] = el; row.appendChild(el);
    });
    kbEl.appendChild(row);
  });
  }

  /* ---- eller (SVG) ---- */
  var handsEl = null, fingEls = {};
  function buildHands() {
    var h = '<g>', ns = 'http://www.w3.org/2000/svg';
    function hand(side, xs, ids) {
      var s = '';
      xs.forEach(function (x, i) { var hgt = [90, 125, 140, 118][side === 'L' ? i : 3 - i]; var id = ids[i]; s += '<rect class="finger" data-f="' + id + '" x="' + x + '" y="' + (150 - hgt) + '" width="36" height="' + (hgt + 10) + '" rx="18" fill="' + COLORS[id] + '"/>'; });
      return s;
    }
    h += hand('L', [20, 64, 108, 152], ['L5', 'L4', 'L3', 'L2']);
    h += '<rect class="palm" x="14" y="130" width="182" height="100" rx="26"/>';
    h += '<rect class="finger" data-f="T" x="200" y="150" width="36" height="86" rx="18" transform="rotate(-25 215 190)" fill="' + COLORS.T + '"/>';
    h += '<rect class="finger" data-f="T" x="284" y="150" width="36" height="86" rx="18" transform="rotate(25 305 190)" fill="' + COLORS.T + '"/>';
    h += hand('R', [324, 368, 412, 456], ['R2', 'R3', 'R4', 'R5']);
    h += '<rect class="palm" x="324" y="130" width="182" height="100" rx="26"/>';
    handsEl.innerHTML = h + '</g>';
    handsEl.querySelectorAll('.finger').forEach(function (f) { (fingEls[f.dataset.f] = fingEls[f.dataset.f] || []).push(f); });
  }
  function showFinger(id) {
    Object.keys(fingEls).forEach(function (k) { fingEls[k].forEach(function (e) { e.classList.toggle('hot', k === id); }); });
  }

  g.KBVisual = {
    BYCH: BYCH, BYCODE: BYCODE, COLORS: COLORS, FNAME: FNAME,
    mount: function (kbRoot, handsRoot) { kbEl = kbRoot; handsEl = handsRoot; keyEls = {}; fingEls = {}; buildKb(); buildHands(); return api; }
  };
  var api = {
    keyEls: function () { return keyEls; },
    showFinger: function (id) { showFinger(id); },
    hint: function (ch, on) {
      Object.keys(keyEls).forEach(function (c) { keyEls[c].classList.remove('hint'); });
      var k = BYCH[ch];
      if (!k) { showFinger(null); return null; }
      if (on !== false && keyEls[k.code]) keyEls[k.code].classList.add('hint');
      showFinger(k.f); return k;
    },
    flash: function (code, cls, ms) { var el = keyEls[code]; if (!el) return; el.classList.add(cls); setTimeout(function () { el.classList.remove(cls); }, ms || 120); }
  };
})(window);
