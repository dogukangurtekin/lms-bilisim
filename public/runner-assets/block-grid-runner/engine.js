/*
 * Blok Kodlama - yürütme motoru (DOM'dan bağımsız; tarayıcıda ve Node'da aynı dosya çalışır).
 *
 * Program bir AST'dir (Blockly bloklarından app.js üretir):
 *   { t:'forward'|'left'|'right', id }
 *   { t:'repeat', n, body:[...], id }              // n kez tekrarla
 *   { t:'until',  body:[...], id }                 // yıldıza ulaşana kadar tekrarla
 *   { t:'if', cond:'blocked'|'open', then:[...], else:[...], id }
 *
 * run(level, program) bir generator döndürür ve olayları (event) sırayla üretir:
 *   {kind:'turn',  id, side, dir}
 *   {kind:'move',  id, from:{x,y}, to:{x,y}, dir}
 *   {kind:'crash', id, reason:'wall'|'edge', at:{x,y}, target:{x,y}}
 *   {kind:'goal',  id, at:{x,y}}
 *   {kind:'end'}                // program bitti, yıldıza ulaşılmadı
 *   {kind:'limit'}              // adım sınırı aşıldı (sonsuz döngü koruması)
 * Her olaydan sonra generator durur; son olay (goal/crash/end/limit) sonrası biter.
 */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) module.exports = factory();
  else root.BlockEngine = factory();
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  var DIRS = [[1, 0], [0, 1], [-1, 0], [0, -1]]; // 0 sağ, 1 aşağı, 2 sol, 3 yukarı
  var MAX_STEPS = 4000;      // toplam eylem sınırı
  var MAX_LOOP = 600;        // "yıldıza kadar tekrarla" için döngü sınırı

  function makeWorld(level) {
    var walls = {};
    (level.obstacles || []).forEach(function (o) { walls[o.x + ',' + o.y] = true; });
    return { w: level.w, h: level.h, walls: walls, star: level.star };
  }

  function blockedAt(world, x, y) {
    if (x < 0 || y < 0 || x >= world.w || y >= world.h) return 'edge';
    if (world.walls[x + ',' + y]) return 'wall';
    return null;
  }

  function* run(level, program) {
    var world = makeWorld(level);
    var s = { x: level.start.x, y: level.start.y, dir: level.start.dir };
    var steps = 0;
    var finished = false;

    function atGoal() { return s.x === world.star.x && s.y === world.star.y; }

    function* execList(list) {
      for (var i = 0; i < list.length; i++) {
        yield* execNode(list[i]);
        if (finished) return;
      }
    }

    function* execNode(n) {
      if (finished) return;
      if (++steps > MAX_STEPS) { finished = true; yield { kind: 'limit' }; return; }
      switch (n.t) {
        case 'left':
          s.dir = (s.dir + 3) % 4;
          yield { kind: 'turn', id: n.id, side: 'left', dir: s.dir };
          return;
        case 'right':
          s.dir = (s.dir + 1) % 4;
          yield { kind: 'turn', id: n.id, side: 'right', dir: s.dir };
          return;
        case 'forward': {
          var nx = s.x + DIRS[s.dir][0], ny = s.y + DIRS[s.dir][1];
          var why = blockedAt(world, nx, ny);
          if (why) {
            finished = true;
            yield { kind: 'crash', id: n.id, reason: why, at: { x: s.x, y: s.y }, target: { x: nx, y: ny } };
            return;
          }
          var from = { x: s.x, y: s.y };
          s.x = nx; s.y = ny;
          yield { kind: 'move', id: n.id, from: from, to: { x: nx, y: ny }, dir: s.dir };
          if (atGoal()) { finished = true; yield { kind: 'goal', id: n.id, at: { x: s.x, y: s.y } }; }
          return;
        }
        case 'repeat': {
          var times = Math.max(0, Math.min(200, parseInt(n.n, 10) || 0));
          for (var k = 0; k < times; k++) {
            yield* execList(n.body || []);
            if (finished) return;
          }
          return;
        }
        case 'until': {
          var guard = 0;
          while (!atGoal() && guard++ < MAX_LOOP) {
            var before = steps;
            yield* execList(n.body || []);
            if (finished) return;
            if (steps === before) break; // boş gövde: sonsuz döngüyü önle
          }
          if (!atGoal() && guard >= MAX_LOOP) { finished = true; yield { kind: 'limit' }; }
          return;
        }
        case 'if': {
          var ahead = blockedAt(world, s.x + DIRS[s.dir][0], s.y + DIRS[s.dir][1]) !== null;
          var cond = n.cond === 'open' ? !ahead : ahead;
          yield* execList(cond ? (n.then || []) : (n.else || []));
          return;
        }
        default:
          return;
      }
    }

    yield* execList(program || []);
    if (!finished) { finished = true; yield { kind: 'end' }; }
  }

  /** Programı sonuna kadar çalıştırıp özet döndürür (test ve doğrulama için). */
  function simulate(level, program) {
    var it = run(level, program);
    var res = { reached: false, crashed: null, ended: false, limit: false, moves: 0, turns: 0, events: 0 };
    for (var r = it.next(); !r.done; r = it.next()) {
      var e = r.value;
      res.events++;
      if (e.kind === 'move') res.moves++;
      else if (e.kind === 'turn') res.turns++;
      else if (e.kind === 'goal') res.reached = true;
      else if (e.kind === 'crash') res.crashed = e.reason;
      else if (e.kind === 'end') res.ended = true;
      else if (e.kind === 'limit') res.limit = true;
    }
    return res;
  }

  /** Programdaki blok sayısı (BAŞLA bloğu hariç) */
  function countBlocks(program) {
    var c = 0;
    (function walk(list) {
      (list || []).forEach(function (n) {
        c++;
        if (n.body) walk(n.body);
        if (n.then) walk(n.then);
        if (n.else) walk(n.else);
      });
    })(program);
    return c;
  }

  /** Yıldız puanı: bloklar par'ı geçmezse 3, +%40 içinde 2, aksi 1 */
  function rateStars(blockCount, par) {
    if (!par || par < 1) return 1;
    if (blockCount <= par) return 3;
    if (blockCount <= par + Math.ceil(par * 0.4)) return 2;
    return 1;
  }

  return { DIRS: DIRS, run: run, simulate: simulate, countBlocks: countBlocks, rateStars: rateStars, makeWorld: makeWorld, blockedAt: blockedAt };
});
