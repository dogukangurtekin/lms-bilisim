// Blok Kodlama bölümlerini üretir ve doğrular.
//   node tools/block-levels/generate.mjs
// Çıktı: public/runner-assets/block-grid-runner/levels.js
// Bölümler kod içinde sabit veri olarak gönderilir (rastgelelik burada, sabit tohumla üretilir) böylece
// her öğrenci/öğretmen aynı bölümleri görür ve ödev aralıkları (1 tabanlı sıra) değişmez.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const outFile = path.resolve(here, '../../public/runner-assets/block-grid-runner/levels.js');
// Kök package.json "type": "module" olduğundan engine.js (UMD) require ile yüklenemez; CommonJS gibi çalıştır.
function loadUmd(file) {
  const mod = { exports: {} };
  new Function('module', 'exports', fs.readFileSync(file, 'utf8'))(mod, mod.exports);
  return mod.exports;
}
const Engine = loadUmd(path.resolve(here, '../../public/runner-assets/block-grid-runner/engine.js'));

export const LEVEL_SET_VERSION = 2;

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

// ---------- program planı (82 bölüm) ----------
const plan = [];
const add = (type, n, params = {}, extra = {}) => plan.push({ type, n, params, ...extra });
// KOLAY 1-24 (orman 1-12, çöl 13-24)
add('straight', 5, { len: 2 }, { density: 0.08 }); add('straight', 5, { len: 3 }, { density: 0.08 }); add('straight', 5, { len: 4 }, { density: 0.08 });
add('L', 5, { a: 2, b: 2 }); add('L', 5, { a: 3, b: 2 }); add('L', 5, { a: 2, b: 3 });
add('L', 6, { a: 3, b: 3 }); add('L', 6, { a: 4, b: 2 }, { density: 0.15 });
add('S', 6, { a: 2, b: 2, c: 2 }); add('L', 6, { a: 4, b: 3 }, { density: 0.1 }); add('S', 6, { a: 2, b: 2, c: 3 }); add('S', 6, { a: 2, b: 3, c: 3 }, { density: 0.2 });
add('straight', 6, { len: 5 }, { loops: true });
add('straight', 7, { len: 6 }, { loops: true });
add('staircase', 7, { k: 2, sx: 1, sy: 1 }, { loops: true });
add('staircase', 7, { k: 3, sx: 1, sy: 1 }, { loops: true });
add('staircase', 7, { k: 2, sx: 2, sy: 1 }, { loops: true });
add('L', 7, { a: 6, b: 3 }, { loops: true });
add('S', 7, { a: 3, b: 3, c: 3 }, { loops: true });
add('detour', 7, { total: 6, bumps: [2] }, { density: 0.1 });
add('detour', 7, { total: 6, bumps: [2, 5] }, { density: 0.1 });
add('staircase', 7, { k: 3, sx: 2, sy: 2 }, { loops: true });
add('serpentine', 7, { k: 2, W: 5 });
add('serpentine', 7, { k: 3, W: 4 }, { loops: true });
// ORTA 25-52 (buz 25-36, uzay 37-52)
add('staircase', 8, { k: 3, sx: 2, sy: 1 }, { loops: true });
add('staircase', 8, { k: 3, sx: 1, sy: 2 }, { loops: true });
add('staircase', 8, { k: 5, sx: 1, sy: 1 }, { loops: true });
add('serpentine', 8, { k: 2, W: 6 });
add('serpentine', 8, { k: 3, W: 5 }, { loops: true });
add('detour', 8, { total: 7, bumps: [2, 5] }, { density: 0.15 });
add('detour', 8, { total: 7, bumps: [2, 5] }, { density: 0.3 });
add('spiral', 8, { s: 6 });
add('maze', 8, {});
add('serpentine', 8, { k: 4, W: 6 }, { loops: true });
add('staircase', 8, { k: 6, sx: 1, sy: 1 }, { loops: true });
add('S', 8, { a: 4, b: 4, c: 3 }, { loops: true });
add('serpentine', 9, { k: 3, W: 7 }, { loops: true });
add('serpentine', 9, { k: 4, W: 7 }, { loops: true });
add('staircase', 9, { k: 4, sx: 2, sy: 2 }, { loops: true });
add('detour', 9, { total: 8, bumps: [2, 5] }, { density: 0.25 });
add('spiral', 9, { s: 7 });
add('maze', 9, {});
add('maze', 9, {});
add('serpentine', 9, { k: 5, W: 6 }, { loops: true });
add('detour', 9, { total: 8, bumps: [2, 5] }, { density: 0.35 });
add('maze', 9, {});
add('serpentine', 10, { k: 4, W: 8 }, { loops: true });
add('spiral', 10, { s: 8 });
add('maze', 10, {});
add('maze', 10, {});
add('serpentine', 10, { k: 5, W: 8 }, { loops: true });
add('spiral', 10, { s: 8 });
// ZOR 53-72 (lav 53-66, kozmik 67-72)
add('serpentine', 11, { k: 5, W: 9 }, { loops: true });
add('maze', 11, {}); add('spiral', 11, { s: 8 }); add('maze', 11, {});
add('serpentine', 11, { k: 6, W: 9 }, { loops: true });
add('maze', 11, {}); add('spiral', 11, { s: 9 }); add('maze', 11, {});
add('maze', 12, {}); add('spiral', 12, { s: 9 });
add('serpentine', 12, { k: 6, W: 10 }, { loops: true });
add('maze', 12, {}); add('maze', 12, {}); add('spiral', 12, { s: 9 });
add('maze', 13, {});
add('serpentine', 13, { k: 7, W: 11 }, { loops: true });
add('maze', 13, {}); add('spiral', 13, { s: 10 }); add('maze', 13, {}); add('maze', 13, {});
// UZMAN 73-82
add('maze', 13, {}); add('spiral', 13, { s: 10 }); add('maze', 13, {});
add('serpentine', 13, { k: 7, W: 11 }, { loops: true });
add('maze', 13, {}); add('maze', 13, {});
add('maze', 14, {}); add('spiral', 14, { s: 10 }); add('maze', 14, {}); add('maze', 14, {}, { density: 0 });

const CONCEPT = {
  straight: 'Düz yol', L: 'İlk dönüş', S: 'Zikzak', U: 'U dönüşü', staircase: 'Merdiven',
  detour: 'Engeli aş', serpentine: 'Yılan yolu', spiral: 'Sarmal', maze: 'Labirent',
};
const THEMES = [[12, 'forest'], [24, 'desert'], [36, 'ice'], [52, 'space'], [66, 'lava'], [82, 'cosmic']];
const themeFor = (i) => THEMES.find(([max]) => i <= max)[1];
const groupFor = (i) => (i <= 24 ? 'Kolay' : i <= 52 ? 'Orta' : i <= 72 ? 'Zor' : 'Uzman');
const XP = { Kolay: 5, Orta: 12, Zor: 21, Uzman: 30 };

const HINTS = {
  1: 'Karakteri yıldıza götürmek için “İleri git” bloklarını ▶ BAŞLA kutusunun içine, alt alta bırak.',
  4: 'Köşelerde dönmen gerek: “Sola dön” ve “Sağa dön” bloklarını kullan.',
  13: 'Tekrarla bloğunu dene! Aynı bloğu tekrar tekrar koymak yerine sayıyı değiştir.',
  20: 'Engelin etrafından dolaşman gerekiyor. Önce hangi yöne döneceğini düşün.',
  23: 'Yılan yolu! Aynı hareketler tekrar ediyor — tekrar bloğu işini kolaylaştırır.',
  32: 'Sarmal yol: içe doğru dönüyor. Döngüyü iç içe kullanabilirsin.',
  33: 'İlk labirent! Duvarlara çarpmadan yıldıza ulaş.',
  61: 'Yeni: “Yıldıza kadar tekrarla” ve “Eğer önüm kapalıysa” blokları (isteğe bağlı).',
};

function buildLevel(i, spec) {
  // tekrar edilebilir: aynı tohumla aynı sonuç
  for (let attempt = 0; attempt < 60; attempt++) {
    const rnd = mulberry32(1000003 * i + 7919 * attempt + 17);
    const n = spec.n;
    const shape = A[spec.type]({ steps: Math.round(12 + (i - 33) * 0.5), ...spec.params, n, rnd });
    const placed = placeShape(shape, n, rnd);
    const sym = Math.floor(rnd() * 8);
    const tf = transform(placed, n, sym);
    const cells = tf.cells;
    if (cells.length < 3) continue;
    const walls = tf.walls;
    const pathSet = new Set(cells.map((c) => key(c.x, c.y)));

    // başlangıç yönü: ilk adıma bak
    const d1 = DIRS.findIndex(([dx, dy]) => dx === cells[1].x - cells[0].x && dy === cells[1].y - cells[0].y);
    let dir = d1;
    const easyStart = i <= 12;
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
      id: i, order: i, name: 'Bölüm ' + i, group: groupFor(i), theme: themeFor(i),
      concept: CONCEPT[spec.type], w: n, h: n,
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
    const turns = cmds.filter((c) => c !== 'F').length;
    level.plain = plain;
    level.par = i >= 13 ? par : plain;   // 13. bölüme kadar tekrar bloğu yok
    level.xp = XP[level.group];
    // Temel bloklar (ileri/sola/sağa/tekrarla) her bölümde açıktır; ileri bloklar 61. bölümden itibaren eklenir.
    level.blocks = ['forward', 'left', 'right', 'repeat'].concat(i >= 61 ? ['until', 'if'] : []);
    // tekrar öğreten bölümlerde blok sınırı koy (yalnız tekrar kullanınca sığar)
    if (spec.loops && i >= 13) {
      const limit = par + Math.max(2, Math.ceil(par * 0.35));
      if (plain > limit) level.limit = limit;
    }
    level.hint = HINTS[i] || (level.limit ? `En fazla ${level.limit} blok kullanabilirsin — tekrar bloğu işini kolaylaştırır.` : '');
    if (plain > 400) continue;
    return level;
  }
  throw new Error('Bölüm üretilemedi: ' + i + ' ' + spec.type);
}

const failures = [];
const levels = plan.map((spec, idx) => {
  try { return buildLevel(idx + 1, spec); } catch (e) { failures.push('Bölüm ' + (idx + 1) + ' (' + spec.type + ' n=' + spec.n + ' ' + JSON.stringify(spec.params) + '): ' + e.message); return null; }
});
if (failures.length) { console.error(failures.join('\n')); process.exit(1); }

// ---------- kontrol ----------
if (levels.length !== 82) throw new Error('82 bölüm bekleniyordu: ' + levels.length);
for (let i = 1; i < levels.length; i++) {
  if (levels[i].w < levels[i - 1].w) throw new Error(`Kare sayısı azalıyor: bölüm ${i + 1}`);
}
const header = `/* OTOMATİK ÜRETİLDİ — elle düzenlemeyin. Kaynak: tools/block-levels/generate.mjs (node ile yeniden üretilir). */\n`;
const body = `(function (root) {\n  var data = ${JSON.stringify({ version: LEVEL_SET_VERSION, levels })};\n  root.BLOCK_LEVEL_SET = data;\n  if (typeof module === 'object' && module.exports) module.exports = data;\n})(typeof self !== 'undefined' ? self : this);\n`;
fs.writeFileSync(outFile, header + body);

// özet
const rows = levels.map((l) => `${String(l.id).padStart(2)} ${l.group.padEnd(6)} ${l.theme.padEnd(7)} ${String(l.w).padStart(2)}x${l.h} ${l.concept.padEnd(11)} obstacles=${String(l.obstacles.length).padStart(3)} plain=${String(l.plain).padStart(3)} par=${String(l.par).padStart(2)} limit=${l.limit ?? '-'} blocks=${l.blocks.length}`);
console.log(rows.join('\n'));
console.log(`\n${levels.length} bölüm -> ${path.relative(process.cwd(), outFile)} (${(fs.statSync(outFile).size / 1024).toFixed(1)} KB)`);
