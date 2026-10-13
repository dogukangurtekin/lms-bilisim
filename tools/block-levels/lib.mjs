// Blok Kodlama (2B ve 3B) için ortak bölüm üretim kütüphanesi: arketipler, yerleştirme, BFS çözücü, tekrar sıkıştırma.
// Kullanım: generate.mjs (2B) ve generate3d.mjs (3B) buradan içe aktarır.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export const here = path.dirname(fileURLToPath(import.meta.url));
// Kök package.json "type": "module" olduğundan engine.js (UMD) require ile yüklenemez; CommonJS gibi çalıştır.
function loadUmd(file) {
  const mod = { exports: {} };
  new Function('module', 'exports', fs.readFileSync(file, 'utf8'))(mod, mod.exports);
  return mod.exports;
}
const Engine = loadUmd(path.resolve(here, '../../public/runner-assets/block-grid-runner/engine.js'));


export { Engine };
// ---------- yardımcılar ----------
function mulberry32(seed) {
  let a = seed >>> 0;
  return function () {
    a = (a + 0x6D2B79F5) >>> 0;
    let t = a;
    t = Math.imul(t ^ (t >>> 15), t | 1);
    t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}
const key = (x, y) => x + ',' + y;
const DIRS = Engine.DIRS;

function polyline(moves) {
  const cells = [{ x: 0, y: 0 }];
  let x = 0, y = 0;
  for (const [dx, dy, c] of moves) for (let i = 0; i < c; i++) { x += dx; y += dy; cells.push({ x, y }); }
  return cells;
}
const bbox = (cells) => cells.reduce((b, c) => ({
  x0: Math.min(b.x0, c.x), y0: Math.min(b.y0, c.y), x1: Math.max(b.x1, c.x), y1: Math.max(b.y1, c.y),
}), { x0: 1e9, y0: 1e9, x1: -1e9, y1: -1e9 });

// ---------- arketipler: { cells: sıralı yol, walls: zorunlu engeller, fill: bbox içini engelle doldur } ----------
const A = {
  straight: ({ len }) => ({ cells: polyline([[1, 0, len]]), walls: [] }),
  L: ({ a, b, v = 1 }) => ({ cells: polyline([[1, 0, a], [0, v, b]]), walls: [] }),
  S: ({ a, b, c }) => ({ cells: polyline([[1, 0, a], [0, 1, b], [1, 0, c]]), walls: [] }),
  U: ({ a, b, c }) => ({ cells: polyline([[1, 0, a], [0, 1, b], [-1, 0, c]]), walls: [] }),
  staircase: ({ k, sx, sy }) => {
    const moves = [];
    for (let i = 0; i < k; i++) { moves.push([1, 0, sx]); moves.push([0, 1, sy]); }
    return { cells: polyline(moves), walls: [] };
  },
  detour: ({ total, bumps }) => {
    // düz yol; her "bump" noktasında engelin etrafından dolan
    const cells = [{ x: 0, y: 0 }];
    const walls = [];
    let x = 0;
    const push = (nx, ny) => cells.push({ x: nx, y: ny });
    for (const bx of bumps) {
      while (x < bx - 1) { x++; push(x, 0); }
      push(x, 1); push(x + 1, 1); push(x + 2, 1); push(x + 2, 0);
      walls.push({ x: x + 1, y: 0 });
      x += 2;
    }
    while (x < total) { x++; push(x, 0); }
    return { cells, walls };
  },
  serpentine: ({ k, W }) => {
    const moves = [];
    for (let r = 0; r < k; r++) {
      moves.push([r % 2 === 0 ? 1 : -1, 0, W]);
      if (r < k - 1) moves.push([0, 1, 2]);
    }
    return { cells: polyline(moves), walls: [], fill: true };
  },
  spiral: ({ s: n }) => {
    const vis = new Set([key(0, 0)]);
    const cells = [{ x: 0, y: 0 }];
    let x = 0, y = 0, d = 0, turned = 0;
    const inb = (a, b) => a >= 0 && b >= 0 && a < n && b < n;
    for (let guard = 0; guard < n * n * 4; guard++) {
      const [dx, dy] = DIRS[d];
      const nx = x + dx, ny = y + dy;
      const ahead2 = inb(x + 2 * dx, y + 2 * dy) && vis.has(key(x + 2 * dx, y + 2 * dy));
      if (inb(nx, ny) && !vis.has(key(nx, ny)) && !ahead2) {
        x = nx; y = ny; vis.add(key(x, y)); cells.push({ x, y }); turned = 0;
      } else {
        d = (d + 1) % 4; turned++;
        if (turned > 2) break;
      }
    }
    return { cells, walls: [], fill: true };
  },
  maze: ({ n, rnd, steps }) => {
    const m = n % 2 === 1 ? n : n - 1;
    const open = Array.from({ length: m }, () => Array(m).fill(false));
    const stack = [[0, 0]];
    open[0][0] = true;
    while (stack.length) {
      const [cx, cy] = stack[stack.length - 1];
      const opts = [];
      for (const [dx, dy] of [[2, 0], [-2, 0], [0, 2], [0, -2]]) {
        const nx = cx + dx, ny = cy + dy;
        if (nx >= 0 && ny >= 0 && nx < m && ny < m && !open[ny][nx]) opts.push([dx, dy]);
      }
      if (!opts.length) { stack.pop(); continue; }
      const [dx, dy] = opts[Math.floor(rnd() * opts.length)];
      open[cy + dy / 2][cx + dx / 2] = true;
      open[cy + dy][cx + dx] = true;
      stack.push([cx + dx, cy + dy]);
    }
    // başlangıçtan en uzak hücre = yıldız
    const dist = new Map([[key(0, 0), 0]]);
    const prev = new Map();
    const q = [[0, 0]];
    for (let i = 0; i < q.length; i++) {
      const [cx, cy] = q[i];
      for (const [dx, dy] of DIRS) {
        const nx = cx + dx, ny = cy + dy;
        if (nx < 0 || ny < 0 || nx >= m || ny >= m || !open[ny][nx] || dist.has(key(nx, ny))) continue;
        dist.set(key(nx, ny), dist.get(key(cx, cy)) + 1);
        prev.set(key(nx, ny), [cx, cy]);
        q.push([nx, ny]);
      }
    }
    let best = [0, 0], bd = 1e9;
    for (const [k, v] of dist) {
      const gap = Math.abs(v - steps) + rnd() * 0.5;
      if (v > 0 && gap < bd) { bd = gap; best = k.split(',').map(Number); }
    }
    const cells = [];
    let cur = best;
    while (cur) { cells.unshift({ x: cur[0], y: cur[1] }); cur = prev.get(key(cur[0], cur[1])); }
    const walls = [];
    for (let yy = 0; yy < n; yy++) for (let xx = 0; xx < n; xx++) {
      const isOpen = xx < m && yy < m && open[yy][xx];
      if (!isOpen) walls.push({ x: xx, y: yy });   // labirent duvarları (n çiftse fazla satır/sütun da duvar)
    }
    return { cells, walls, fixed: true };
  },
};

// ---------- yerleştirme / dönüşüm ----------
function placeShape(shape, n, rnd) {
  const bb = bbox(shape.cells);
  const bw = bb.x1 - bb.x0 + 1, bh = bb.y1 - bb.y0 + 1;
  if (bw > n || bh > n) throw new Error(`şekil sığmıyor: ${bw}x${bh} > ${n}`);
  const ox = shape.fixed ? 0 : Math.floor(rnd() * (n - bw + 1)) - bb.x0;
  const oy = shape.fixed ? 0 : Math.floor(rnd() * (n - bh + 1)) - bb.y0;
  const cells = shape.cells.map((c) => ({ x: c.x + ox, y: c.y + oy }));
  const walls = new Map();
  shape.walls.forEach((w) => walls.set(key(w.x + ox, w.y + oy), { x: w.x + ox, y: w.y + oy }));
  if (shape.fill) {
    const onPath = new Set(cells.map((c) => key(c.x, c.y)));
    const b2 = shape.fixed ? { x0: 0, y0: 0, x1: n - 1, y1: n - 1 } : bbox(cells);
    for (let y = b2.y0; y <= b2.y1; y++) for (let x = b2.x0; x <= b2.x1; x++) {
      if (!onPath.has(key(x, y))) walls.set(key(x, y), { x, y });
    }
  }
  return { cells, walls };
}

function transform(p, n, t) {
  const f = (c) => {
    let { x, y } = c;
    if (t & 4) x = n - 1 - x;
    for (let i = 0; i < (t & 3); i++) { const nx = n - 1 - y; y = x; x = nx; }
    return { x, y };
  };
  const walls = new Map();
  p.walls.forEach((w) => { const q = f(w); walls.set(key(q.x, q.y), q); });
  return { cells: p.cells.map(f), walls };
}

// ---------- çözücü: en kısa program (BFS) ve tekrarlı sıkıştırma (par) ----------
function solveShortest(level) {
  const world = Engine.makeWorld(level);
  const sk = (x, y, d) => x + ',' + y + ',' + d;
  const start = { x: level.start.x, y: level.start.y, d: level.start.dir };
  const prev = new Map([[sk(start.x, start.y, start.d), null]]);
  const q = [start];
  for (let i = 0; i < q.length; i++) {
    const c = q[i];
    if (c.x === world.star.x && c.y === world.star.y) {
      const cmds = [];
      let k = sk(c.x, c.y, c.d);
      while (prev.get(k)) { const p = prev.get(k); cmds.unshift(p.cmd); k = p.from; }
      return cmds;
    }
    const nexts = [
      { cmd: 'L', x: c.x, y: c.y, d: (c.d + 3) % 4 },
      { cmd: 'R', x: c.x, y: c.y, d: (c.d + 1) % 4 },
    ];
    const nx = c.x + DIRS[c.d][0], ny = c.y + DIRS[c.d][1];
    if (!Engine.blockedAt(world, nx, ny)) nexts.push({ cmd: 'F', x: nx, y: ny, d: c.d });
    for (const n of nexts) {
      const k = sk(n.x, n.y, n.d);
      if (prev.has(k)) continue;
      prev.set(k, { cmd: n.cmd, from: sk(c.x, c.y, c.d) });
      q.push(n);
    }
  }
  return null;
}

// Komut dizisini "tekrar" bloklarıyla en az blokla ifade et (aralık DP).
function compress(cmds) {
  const n = cmds.length;
  const INF = 1e9;
  const cost = Array.from({ length: n + 1 }, () => Array(n + 1).fill(INF));
  const how = Array.from({ length: n + 1 }, () => Array(n + 1).fill(null));
  for (let len = 1; len <= n; len++) {
    for (let i = 0; i + len <= n; i++) {
      const j = i + len;
      if (len === 1) { cost[i][j] = 1; how[i][j] = { t: 'one' }; continue; }
      let best = INF, bh = null;
      for (let k = i + 1; k < j; k++) {
        const c = cost[i][k] + cost[k][j];
        if (c < best) { best = c; bh = { t: 'split', k }; }
      }
      for (let p = 1; p * 2 <= len; p++) {
        if (len % p) continue;
        const reps = len / p;
        if (reps < 2 || reps > 50) continue;
        let same = true;
        for (let t = i + p; t < j && same; t++) if (cmds[t] !== cmds[t - p]) same = false;
        if (same) {
          const c = 1 + cost[i][i + p];
          if (c < best) { best = c; bh = { t: 'rep', p, reps }; }
        }
      }
      cost[i][j] = best; how[i][j] = bh;
    }
  }
  const build = (i, j) => {
    const h = how[i][j];
    if (h.t === 'one') return [{ t: cmds[i] === 'F' ? 'forward' : cmds[i] === 'L' ? 'left' : 'right' }];
    if (h.t === 'split') return build(i, h.k).concat(build(h.k, j));
    return [{ t: 'repeat', n: h.reps, body: build(i, i + h.p) }];
  };
  return { par: cost[0][n], program: build(0, n) };
}


export { A, placeShape, transform, solveShortest, compress, mulberry32, key, DIRS, polyline };

/**
 * Bir bölüm oluşturucu üretir. cfg:
 *  themeFor(i), groupFor(i), XP{grup:xp}, CONCEPT{tür:ad}, HINTS{i:metin}, namePrefix,
 *  loopsFrom (tekrar bloğu ve blok sınırı bu bölümden itibaren), advancedFrom ("yıldıza kadar"/"eğer" blokları),
 *  sealRing (true: yılan yolu/sarmal kutusunun çevresine duvar çek, kestirme olmasın), easyStartMax (bu bölüme kadar karakter ilk adıma dönük başlar), mazeSteps(i) (labirent hedef adım sayısı)
 */
export function makeBuilder(cfg) {
  return function buildLevel(i, spec) {
    // tekrar edilebilir: aynı tohumla aynı sonuç
    for (let attempt = 0; attempt < 60; attempt++) {
      const rnd = mulberry32(1000003 * i + 7919 * attempt + 17);
      const n = spec.n;
      const shape = A[spec.type]({ steps: cfg.mazeSteps(i), ...spec.params, n, rnd });
      const placed = placeShape(shape, n, rnd);
      const sym = Math.floor(rnd() * 8);
      const tf = transform(placed, n, sym);
      const cells = tf.cells;
      if (cells.length < 3) continue;
      const walls = tf.walls;
      const pathSet = new Set(cells.map((c) => key(c.x, c.y)));
      // Koridor bölümlerinde (yılan yolu/sarmal) kutunun dışından kestirme yapılamasın diye çevresine bir sıra duvar çek.
      if (cfg.sealRing && shape.fill) {
        const bb = bbox(cells);
        for (let y = bb.y0 - 1; y <= bb.y1 + 1; y++) for (let x = bb.x0 - 1; x <= bb.x1 + 1; x++) {
          if (x < 0 || y < 0 || x >= n || y >= n) continue;
          const k = key(x, y);
          if (!pathSet.has(k) && !walls.has(k)) walls.set(k, { x, y });
        }
      }

      // başlangıç yönü: ilk adıma bak
      const d1 = DIRS.findIndex(([dx, dy]) => dx === cells[1].x - cells[0].x && dy === cells[1].y - cells[0].y);
      let dir = d1;
      const easyStart = i <= cfg.easyStartMax;
      if (!easyStart && rnd() < 0.55) dir = (d1 + (rnd() < 0.5 ? 1 : 3)) % 4;
      if (!easyStart && spec.type === 'maze' && rnd() < 0.2) dir = (d1 + 2) % 4;

      // dekoratif engeller
      const density = spec.density ?? (spec.type === 'maze' || spec.type === 'serpentine' || spec.type === 'spiral' ? 0 : 0.12);
      if (density > 0) {
        for (let y = 0; y < n; y++) for (let x = 0; x < n; x++) {
          const k = key(x, y);
          if (pathSet.has(k) || walls.has(k)) continue;
          if (rnd() < density) walls.set(k, { x, y });
        }
      }
      const obstacles = [...walls.values()].sort((a, b) => a.y - b.y || a.x - b.x);

      const level = {
        id: i, order: i, name: cfg.namePrefix + i, group: cfg.groupFor(i), theme: cfg.themeFor(i),
        concept: cfg.CONCEPT[spec.type], w: n, h: n,
        start: { x: cells[0].x, y: cells[0].y, dir },
        star: { x: cells[cells.length - 1].x, y: cells[cells.length - 1].y },
        obstacles,
      };
      // doğrulama: BFS çözümü var mı, ve niyet edilen yol çalışıyor mu
      const cmds = solveShortest(level);
      if (!cmds) continue;
      const { par, program } = compress(cmds);
      const sim = Engine.simulate(level, program);
      if (!sim.reached) throw new Error(`Bölüm ${i}: sıkıştırılmış çözüm yıldıza ulaşamadı`);
      const plain = cmds.length;
      level.plain = plain;
      level.par = i >= cfg.loopsFrom ? par : plain;   // tekrar bloğu gelmeden önce par = düz çözüm
      level.xp = cfg.XP[level.group];
      // Temel bloklar (ileri/sola/sağa/tekrarla) her bölümde açıktır; ileri bloklar advancedFrom'dan itibaren eklenir.
      level.blocks = ['forward', 'left', 'right', 'repeat'].concat(i >= cfg.advancedFrom ? ['until', 'if'] : []);
      // tekrar öğreten bölümlerde blok sınırı koy (yalnız tekrar kullanınca sığar)
      if (spec.loops && i >= cfg.loopsFrom) {
        const limit = par + Math.max(2, Math.ceil(par * 0.35));
        if (plain > limit) level.limit = limit;
      }
      level.hint = cfg.HINTS[i] || (level.limit ? `En fazla ${level.limit} blok kullanabilirsin — tekrar bloğu işini kolaylaştırır.` : '');
      if (plain > 400) continue;
      return level;
    }
    throw new Error('Bölüm üretilemedi: ' + i + ' ' + spec.type);
  };
}

/** Planı bölümlere çevirir, doğrular ve levels.js dosyasını yazar. opts: {plan, build, expected, globalName, outFile, version, sourceNote} */
export function generate(opts) {
  const failures = [];
  const levels = opts.plan.map((spec, idx) => {
    try { return opts.build(idx + 1, spec); } catch (e) { failures.push('Bölüm ' + (idx + 1) + ' (' + spec.type + ' n=' + spec.n + ' ' + JSON.stringify(spec.params) + '): ' + e.message); return null; }
  });
  if (failures.length) { console.error(failures.join('\n')); process.exit(1); }
  if (levels.length !== opts.expected) throw new Error(opts.expected + ' bölüm bekleniyordu: ' + levels.length);
  for (let i = 1; i < levels.length; i++) {
    if (levels[i].w < levels[i - 1].w) throw new Error(`Kare sayısı azalıyor: bölüm ${i + 1}`);
  }
  const header = `/* OTOMATİK ÜRETİLDİ — elle düzenlemeyin. Kaynak: ${opts.sourceNote} (node ile yeniden üretilir). */\n`;
  const body = `(function (root) {\n  var data = ${JSON.stringify({ version: opts.version, levels })};\n  root.${opts.globalName} = data;\n  if (typeof module === 'object' && module.exports) module.exports = data;\n})(typeof self !== 'undefined' ? self : this);\n`;
  fs.writeFileSync(opts.outFile, header + body);
  const rows = levels.map((l) => `${String(l.id).padStart(2)} ${l.group.padEnd(6)} ${l.theme.padEnd(7)} ${String(l.w).padStart(2)}x${l.h} ${l.concept.padEnd(11)} obstacles=${String(l.obstacles.length).padStart(3)} plain=${String(l.plain).padStart(3)} par=${String(l.par).padStart(2)} limit=${l.limit ?? '-'} blocks=${l.blocks.length}`);
  console.log(rows.join('\n'));
  console.log(`\n${levels.length} bölüm -> ${path.relative(process.cwd(), opts.outFile)} (${(fs.statSync(opts.outFile).size / 1024).toFixed(1)} KB)`);
  return levels;
}
