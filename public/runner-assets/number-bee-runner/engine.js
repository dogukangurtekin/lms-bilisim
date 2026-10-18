/* Sayı Arısı — motor. Program: [{t:'m', d:'N|S|E|W'} | {t:'r', n:2..9, body:[...]}]. Arı her hareketin sonunda bastığı karedeki rakamı toplar.
 * Hem tarayıcıda (window.NumberBeeEngine) hem Node'da (module.exports) çalışır; seviye üretici ve arayüz aynı kuralları kullanır. */
(function (root) {
  'use strict';

  var DIRS = { N: [0, -1], S: [0, 1], E: [1, 0], W: [-1, 0] };
  var LETTER = { N: 'K', S: 'G', E: 'D', W: 'B' };          // Kuzey, Güney, Doğu, Batı
  var NAME = { N: 'yukarı (K)', S: 'aşağı (G)', E: 'sağa (D)', W: 'sola (B)' };
  var MAX_STEPS = 120;

  /** Bloğu (döngüler dahil) adım listesine açar. Her adım, ilgili hareket bloğunu da taşır (vurgulama için). */
  function flatten(prog, out, depth) {
    out = out || { steps: [], overflow: false };
    (prog || []).forEach(function (n) {
      if (out.overflow) return;
      if (n.t === 'm') {
        if (out.steps.length >= MAX_STEPS) { out.overflow = true; return; }
        out.steps.push({ d: n.d, node: n });
      } else if (n.t === 'r') {
        var times = Math.max(1, Math.min(9, n.n | 0));
        for (var i = 0; i < times && !out.overflow; i++) flatten(n.body, out, (depth || 0) + 1);
      }
    });
    return out;
  }

  function countBlocks(prog) {
    var c = 0;
    (prog || []).forEach(function (n) { c += 1; if (n.t === 'r') c += countBlocks(n.body); });
    return c;
  }

  /**
   * Programı seviyede çalıştırır.
   * @returns {{status:string, spelled:string, trace:Array, blocks:number}}
   *   status: ok (kelime tam) | short (doğru ama eksik) | wrong (yanlış harf) | long (fazla harf) | out (ızgara dışı) | empty | overflow
   */
  function run(level, prog) {
    var fl = flatten(prog), trace = [], x = level.start.x, y = level.start.y, spelled = '', word = level.word;
    var res = { status: 'empty', spelled: '', trace: trace, blocks: countBlocks(prog) };
    if (!fl.steps.length) return res;
    for (var i = 0; i < fl.steps.length; i++) {
      var s = fl.steps[i], v = DIRS[s.d], nx = x + v[0], ny = y + v[1];
      if (nx < 0 || ny < 0 || nx >= level.w || ny >= level.h) {
        trace.push({ x: x, y: y, out: true, d: s.d, node: s.node });
        res.status = 'out'; res.spelled = spelled; return res;
      }
      x = nx; y = ny;
      var ch = level.grid[y][x];
      spelled += ch;
      trace.push({ x: x, y: y, ch: ch, d: s.d, node: s.node });
    }
    res.spelled = spelled;
    if (fl.overflow) res.status = 'overflow';
    else if (spelled === word) res.status = 'ok';
    else if (spelled.length > word.length && spelled.indexOf(word) === 0) res.status = 'long';
    else if (spelled.length < word.length && word.indexOf(spelled) === 0) res.status = 'short';
    else res.status = 'wrong';
    return res;
  }

  /** Programı kısa metne çevirir (ipucu / okunabilirlik): "D D tekrarla(3){ G D }" */
  function describe(prog) {
    return (prog || []).map(function (n) { return n.t === 'm' ? LETTER[n.d] : 'tekrarla(' + n.n + '){ ' + describe(n.body) + ' }'; }).join(' ');
  }

  var api = { DIRS: DIRS, LETTER: LETTER, NAME: NAME, MAX_STEPS: MAX_STEPS, flatten: flatten, countBlocks: countBlocks, run: run, describe: describe };
  if (typeof module !== 'undefined' && module.exports) module.exports = api; else root.NumberBeeEngine = api;
})(typeof self !== 'undefined' ? self : this);
