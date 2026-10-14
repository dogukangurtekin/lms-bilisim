/* Küçük Nokta — Kod Kartları: motor (saf JS). Kart programlarını bir ızgara dünyasında çalıştırır.
 * Özgün tasarım: komut dili, kart biçimi ve kurallar bu projeye aittir. Tarayıcıda window.DotEngine, Node'da module.exports. */
(function (root) {
  'use strict';

  var DIRS = { E: { x: 1, y: 0 }, S: { x: 0, y: 1 }, W: { x: -1, y: 0 }, N: { x: 0, y: -1 } };
  var ORDER = ['E', 'S', 'W', 'N'];                       // sağa dönüş sırası
  var ABS = { sağa: 'E', aşağı: 'S', sola: 'W', yukarı: 'N' };
  var ABS_NAME = { E: 'sağa', S: 'aşağı', W: 'sola', N: 'yukarı' };
  var COND_NAME = { frontFree: 'önüm_boş', frontSafe: 'önüm_güvenli', rightFree: 'sağım_boş', leftFree: 'solum_boş' };
  var MAX_STEPS = 400, MAX_WHILE = 40;

  function turn(dir, d) { var i = ORDER.indexOf(dir); return ORDER[(i + (d === 'right' ? 1 : 3)) % 4]; }

  // ---- dünya: cells = satır dizgileri ('.' zemin, '#' duvar, 'o' çukur, 'G' hedef)
  function makeWorld(level) {
    var rows = level.cells, w = rows[0].length, h = rows.length;
    return { w: w, h: h, at: function (x, y) { return x < 0 || y < 0 || x >= w || y >= h ? '#' : rows[y][x]; } };
  }
  function isFree(world, x, y) { return world.at(x, y) !== '#'; }
  function isSafe(world, x, y) { var c = world.at(x, y); return c !== '#' && c !== 'o'; }

  function sense(world, st, cond) {
    var f = DIRS[st.dir], r = DIRS[turn(st.dir, 'right')], l = DIRS[turn(st.dir, 'left')];
    if (cond === 'frontFree') return isFree(world, st.x + f.x, st.y + f.y);
    if (cond === 'frontSafe') return isSafe(world, st.x + f.x, st.y + f.y);
    if (cond === 'rightFree') return isFree(world, st.x + r.x, st.y + r.y);
    return isFree(world, st.x + l.x, st.y + l.y);
  }

  // ---- bir kartı çalıştır: adım listesi üretir (arayüz animasyonu aynı listeyi oynatır)
  //  adım: {k:'move',from,to,ln} | {k:'turn',dir,ln} | {k:'fail',reason:'wall'|'pit',at,toward,ln} | {k:'goal'}
  function runCard(world, start, ast) {
    var st = { x: start.x, y: start.y, dir: start.dir }, steps = [], res = { ok: true, goal: false, failure: null, steps: steps, end: st };
    var budget = MAX_STEPS;
    function emit(s) { steps.push(s); }
    function goStep(dx, dy, ln) {
      var nx = st.x + dx, ny = st.y + dy;
      if (!isFree(world, nx, ny)) { emit({ k: 'fail', reason: 'wall', at: { x: st.x, y: st.y }, toward: { x: nx, y: ny }, ln: ln }); res.ok = false; res.failure = 'wall'; return false; }
      emit({ k: 'move', from: { x: st.x, y: st.y }, to: { x: nx, y: ny }, ln: ln });
      st.x = nx; st.y = ny;
      var c = world.at(nx, ny);
      if (c === 'o') { emit({ k: 'fail', reason: 'pit', at: { x: nx, y: ny }, ln: ln }); res.ok = false; res.failure = 'pit'; return false; }
      if (c === 'G') { res.goal = true; emit({ k: 'goal' }); return false; }
      return true;
    }
    function exec(list) {
      for (var i = 0; i < list.length; i++) {
        if (!res.ok || res.goal) return;
        if (--budget < 0) { res.ok = false; res.failure = 'loop'; return; }
        var n = list[i];
        if (n.t === 'move') { st.dir = n.dir; for (var a = 0; a < n.n; a++) if (!goStep(DIRS[n.dir].x, DIRS[n.dir].y, n.ln)) return; }
        else if (n.t === 'fwd') { for (var b = 0; b < n.n; b++) if (!goStep(DIRS[st.dir].x, DIRS[st.dir].y, n.ln)) return; }
        else if (n.t === 'turn') { st.dir = turn(st.dir, n.d); emit({ k: 'turn', dir: st.dir, ln: n.ln }); }
        else if (n.t === 'repeat') { for (var r = 0; r < n.n; r++) { exec(n.body); if (!res.ok || res.goal) return; } }
        else if (n.t === 'if') { exec(sense(world, st, n.cond) ? n.then : (n.else || [])); }
        else if (n.t === 'while') { var g = 0; while (sense(world, st, n.cond)) { if (++g > MAX_WHILE) { res.ok = false; res.failure = 'loop'; return; } var before = steps.length; exec(n.body); if (!res.ok || res.goal) return; if (steps.length === before) { res.ok = false; res.failure = 'loop'; return; } } }
      }
    }
    exec(ast);
    res.end = { x: st.x, y: st.y, dir: st.dir };
    return res;
  }

  // ---- kart kodunu satırlara dök (her işlem düğümüne satır numarası ln atanır)
  function codeLines(ast) {
    var lines = [];
    function pr(list, ind) {
      list.forEach(function (n) {
        var pad = new Array(ind + 1).join('  ');
        if (n.t === 'move') { n.ln = lines.length; lines.push(pad + ABS_NAME[n.dir] + '(' + n.n + ')'); }
        else if (n.t === 'fwd') { n.ln = lines.length; lines.push(pad + 'ileri(' + n.n + ')'); }
        else if (n.t === 'turn') { n.ln = lines.length; lines.push(pad + (n.d === 'right' ? 'dön_sağ()' : 'dön_sol()')); }
        else if (n.t === 'repeat') { lines.push(pad + 'tekrarla (' + n.n + ') {'); pr(n.body, ind + 1); lines.push(pad + '}'); }
        else if (n.t === 'while') { lines.push(pad + 'iken (' + COND_NAME[n.cond] + ') {'); pr(n.body, ind + 1); lines.push(pad + '}'); }
        else if (n.t === 'if') {
          lines.push(pad + 'eğer (' + COND_NAME[n.cond] + ') {'); pr(n.then, ind + 1);
          if (n.else && n.else.length) { lines.push(pad + '} değilse {'); pr(n.else, ind + 1); }
          lines.push(pad + '}');
        }
      });
    }
    pr(ast, 0);
    return lines;
  }

  // ---- çözücü: kartları hangi sırayla oynarsan hedefe varırsın? (başarısız kart elenir, kart geri döner)
  function solve(level, maxSolutions) {
    var world = makeWorld(level), cards = level.cards, n = cards.length, sols = [], seen = {};
    var limit = maxSolutions || 50;
    function dfs(state, mask, order) {
      if (sols.length >= limit) return;
      var key = mask + ':' + state.x + ',' + state.y + state.dir;
      if (seen[key]) return; seen[key] = true;
      for (var i = 0; i < n; i++) {
        if (mask & (1 << i)) continue;
        var r = runCard(world, state, cards[i].ast);
        if (r.goal) { sols.push(order.concat(i)); if (sols.length >= limit) return; continue; }
        if (!r.ok) continue;
        dfs(r.end, mask | (1 << i), order.concat(i));
      }
    }
    dfs({ x: level.start.x, y: level.start.y, dir: level.start.dir }, 0, []);
    return sols;
  }
  // ---- serbest gezinti seviyesi: BFS ile ulaşılabilirlik
  function roamSolvable(level) {
    var world = makeWorld(level), q = [[level.start.x, level.start.y]], seen = {}; seen[level.start.x + ',' + level.start.y] = true;
    for (var i = 0; i < q.length; i++) {
      var p = q[i];
      if (world.at(p[0], p[1]) === 'G') return true;
      ['E', 'S', 'W', 'N'].forEach(function (d) { var x = p[0] + DIRS[d].x, y = p[1] + DIRS[d].y, c = world.at(x, y); if (c === '#' || c === 'o' || seen[x + ',' + y]) return; seen[x + ',' + y] = true; q.push([x, y]); });
    }
    return false;
  }

  var api = { DIRS: DIRS, ORDER: ORDER, ABS_NAME: ABS_NAME, COND_NAME: COND_NAME, turn: turn, makeWorld: makeWorld, sense: sense, runCard: runCard, codeLines: codeLines, solve: solve, roamSolvable: roamSolvable };
  root.DotEngine = api;
  if (typeof module === 'object' && module.exports) module.exports = api;
})(typeof self !== 'undefined' ? self : this);
