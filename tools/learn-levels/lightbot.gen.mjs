// Lightbot Runner (Code Robot Lab) — 100 bölümlük kategorili seviye seti üretici.
// Çalıştır: node tools/learn-levels/lightbot.gen.mjs  →  public/runner-assets/lightbot-runner/levels.js
// Kolay 34 (yalnız MAIN) · Orta 33 (PROC1) · Zor 33 (PROC1 + PROC2, iç içe çağrı).
// Program ÖNCE yazılır, tahta programın izlediği yoldan türetilir; ardından oyunun gerçek kuralları (F/J/B/prosedür yığını)
// ile yeniden oynatılıp tüm ışıkların yandığı doğrulanır. Böylece her bölüm çözülebilirdir.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(here, '../../public/runner-assets/lightbot-runner/levels.js');

function mulberry32(a) { return function () { a |= 0; a = (a + 0x6D2B79F5) | 0; let t = Math.imul(a ^ (a >>> 15), 1 | a); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
const DIRS = [{ x: 1, y: 0 }, { x: 0, y: 1 }, { x: -1, y: 0 }, { x: 0, y: -1 }];
const key = (x, y) => `${x},${y}`;
const MAX_H = 3;

// ---- programı "serbest dünyada" çalıştırıp tahtayı türet
function expand(program, track = 'main', depth = 0, out = []) {
  if (depth > 6) throw new Error('çok derin');
  for (const c of program[track]) {
    if (c === 'P1') expand(program, 'proc1', depth + 1, out);
    else if (c === 'P2') expand(program, 'proc2', depth + 1, out);
    else out.push(c);
    if (out.length > 160) throw new Error('çok uzun');
  }
  return out;
}

function deriveBoard(flat, startDir, startH, rnd) {
  const tiles = new Map();
  let x = 0, y = 0, h = startH, dir = startDir;
  tiles.set(key(0, 0), { x: 0, y: 0, h, light: false });
  for (const c of flat) {
    if (c === 'L') dir = (dir + 3) % 4;
    else if (c === 'R') dir = (dir + 1) % 4;
    else if (c === 'B') tiles.get(key(x, y)).light = true;
    else {
      const nx = x + DIRS[dir].x, ny = y + DIRS[dir].y;
      const ex = tiles.get(key(nx, ny));
      if (c === 'F') {
        if (ex) { if (ex.h !== h) return null; }
        else tiles.set(key(nx, ny), { x: nx, y: ny, h, light: false });
      } else { // J
        if (ex) { if (Math.abs(ex.h - h) !== 1) return null; h = ex.h; x = nx; y = ny; continue; }
        const nh = h <= 0 ? h + 1 : h >= MAX_H ? h - 1 : (rnd() < 0.62 ? h + 1 : h - 1);
        h = nh;
        tiles.set(key(nx, ny), { x: nx, y: ny, h, light: false });
      }
      x = nx; y = ny;
    }
  }
  return { tiles: [...tiles.values()], endX: x, endY: y };
}

// ---- oyunun gerçek kuralları (app.js executeOne / tickRun ile aynı)
function playGame(level, program) {
  const map = new Map(level.board.tiles.map((t) => [key(t.x, t.y), t]));
  const lit = new Set();
  const r = { x: level.board.start.x, y: level.board.start.y, h: level.board.start.h, dir: level.board.start.dir };
  const targets = level.board.tiles.filter((t) => t.light);
  const stack = [{ track: 'main', index: 0 }];
  let steps = 0;
  while (stack.length) {
    const f = stack[stack.length - 1];
    const seq = program[f.track] || [];
    if (f.index >= seq.length) { stack.pop(); continue; }
    const c = seq[f.index++];
    if (++steps > 500) return { ok: false, why: 'limit' };
    if (c === 'P1' || c === 'P2') {
      const t = c === 'P1' ? 'proc1' : 'proc2';
      if (!program[t] || !program[t].length) return { ok: false, why: 'boş proc' };
      stack.push({ track: t, index: 0 });
    } else {
      if (c === 'L') r.dir = (r.dir + 3) % 4;
      else if (c === 'R') r.dir = (r.dir + 1) % 4;
      else if (c === 'B') { const t = map.get(key(r.x, r.y)); if (!t || !t.light) return { ok: false, why: 'B' }; lit.add(key(r.x, r.y)); }
      else {
        const nx = r.x + DIRS[r.dir].x, ny = r.y + DIRS[r.dir].y;
        const nt = map.get(key(nx, ny));
        if (!nt) return { ok: false, why: 'boşta' };
        if (c === 'F') { if (nt.h !== r.h) return { ok: false, why: 'F yükseklik' }; }
        else if (Math.abs(nt.h - r.h) > 1 || nt.h === r.h) return { ok: false, why: 'J' };
        r.x = nx; r.y = ny; r.h = nt.h;
      }
    }
    if (targets.length && targets.every((t) => lit.has(key(t.x, t.y)))) return { ok: true, steps };
  }
  return { ok: false, why: 'ışıklar yanmadı' };
}

// ---- program üreticileri
const ri = (r, a, b) => a + Math.floor(r() * (b - a + 1));
const pick = (r, arr) => arr[Math.floor(r() * arr.length)];

function walk(r, { segs, runMin, runMax, turnProb, jumpProb, lightProb }) {
  const t = [];
  for (let s = 0; s < segs; s++) {
    if (s > 0 && r() < turnProb) t.push(r() < 0.5 ? 'L' : 'R');
    else if (s > 0) t.push('F');
    const run = ri(r, runMin, runMax);
    for (let k = 0; k < run; k++) t.push(r() < jumpProb ? 'J' : 'F');
    if (s === segs - 1 || r() < lightProb) t.push('B');
  }
  if (t[t.length - 1] !== 'B') t.push('B');
  return t;
}

// yön değiştirmeyen küçük motif (art arda çağrılınca merdiven/şerit oluşturur)
function motif(r, { jump, size }) {
  const a = ri(r, 1, Math.min(3, size)), b = ri(r, 1, Math.min(2, size));
  const turn = r() < 0.5 ? ['R', 'L'] : ['L', 'R'];
  const mv = (n) => Array.from({ length: n }, () => (jump && r() < 0.4 ? 'J' : 'F'));
  const shapes = [
    () => [...mv(a), 'B'],
    () => [...mv(a), 'B', turn[0], ...mv(b), turn[1]],
    () => [...mv(a), turn[0], ...mv(b), 'B', turn[1]],
    () => [turn[0], ...mv(b), 'B', turn[1], ...mv(a)],
  ];
  return pick(r, shapes)();
}

const repeat = (arr, n) => Array.from({ length: n }, () => arr).flat();

function makeProgram(group, idx, r) {
  // idx: grup içinde 1 tabanlı; t: 0..1 ilerleme
  const n = group === 'easy' ? 34 : 33;
  const t = (idx - 1) / (n - 1);
  if (group === 'easy') {
    if (idx <= 3) return { main: [...repeat(['F'], idx + 1), 'B'], proc1: [], proc2: [] };
    const jumpFrom = 17;
    const main = walk(r, {
      segs: 1 + Math.floor(1 + t * 4), runMin: 1, runMax: 2 + Math.floor(t * 2),
      turnProb: idx <= 8 ? 1 : 0.85, jumpProb: idx >= jumpFrom ? 0.22 + t * 0.12 : 0, lightProb: 0.35 + t * 0.2,
    });
    return { main, proc1: [], proc2: [] };
  }
  if (group === 'medium') {
    const size = 2 + Math.floor(t * 3);
    const m1 = motif(r, { jump: idx > 8, size });
    const reps = ri(r, 3, 4 + Math.floor(t * 3));
    let main;
    if (idx <= 10) main = repeat(['P1'], reps);
    else if (idx <= 22) main = [...walk(r, { segs: 1, runMin: 1, runMax: 2, turnProb: 0, jumpProb: 0, lightProb: 0 }).slice(0, -1), ...repeat(['P1'], reps)];
    else { const g = r() < 0.5 ? 'R' : 'L'; main = [...repeat(['P1'], ri(r, 2, 4)), g, ...repeat(['P1'], ri(r, 2, 3))]; }
    return { main, proc1: m1, proc2: [] };
  }
  // zor: PROC1 + PROC2 (PROC2, PROC1'i çağırabilir)
  const size = 2 + Math.floor(t * 3);
  const m1 = motif(r, { jump: idx > 4, size });
  const m2 = r() < 0.45 + t * 0.2 ? [...repeat(['P1'], ri(r, 2, 3)), pick(r, ['R', 'L']), ...(r() < 0.5 ? ['F'] : [])] : motif(r, { jump: idx > 4, size });
  const a = ri(r, 2, 3), b = ri(r, 2, 3 + Math.floor(t * 2));
  const main = r() < 0.5
    ? [...repeat(['P1'], a), pick(r, ['R', 'L']), ...repeat(['P2'], b)]
    : [...repeat(['P2'], a), 'P1', pick(r, ['R', 'L']), ...repeat(['P2'], Math.max(1, b - 1)), 'P1'];
  return { main, proc1: m1, proc2: m2 };
}

const GROUPS = [
  { key: 'easy', label: 'Kolay', prefix: 1, count: 34, xp: 20 },
  { key: 'medium', label: 'Orta', prefix: 2, count: 33, xp: 20 },
  { key: 'hard', label: 'Zor', prefix: 3, count: 33, xp: 20 },
];

function buildLevel(g, idx, seen) {
  for (let attempt = 0; attempt < 600; attempt++) {
    const r = mulberry32(7919 * (g.prefix * 100 + idx) + 104729 * attempt + 11);
    const program = makeProgram(g.key, idx, r);
    let flat;
    try { flat = expand(program); } catch (e) { continue; }
    const startDir = g.key === 'easy' && idx <= 16 ? 0 : (g.key === 'easy' ? pick(r, [0, 0, 1]) : ri(r, 0, 3));
    const startH = g.key === 'hard' ? ri(r, 0, 1) : 0;
    const d = deriveBoard(flat, startDir, startH, r);
    if (!d) continue;
    let tiles = d.tiles;
    const minX = Math.min(...tiles.map((t) => t.x)), minY = Math.min(...tiles.map((t) => t.y));
    tiles = tiles.map((t) => ({ x: t.x - minX, y: t.y - minY, h: t.h, light: t.light }));
    const w = Math.max(...tiles.map((t) => t.x)) + 1, h = Math.max(...tiles.map((t) => t.y)) + 1;
    const lights = tiles.filter((t) => t.light).length;
    const needSpan = g.key === 'easy' ? 7 : g.key === 'medium' ? 8 : 9;
    if (w > needSpan || h > needSpan || tiles.length > 34 || lights < 1) continue;
    if (g.key !== 'easy' && lights < 2) continue;
    // dekoratif (ulaşılmayan) karolar — orta/zorda tahtayı doldurur
    if (g.key !== 'easy' && r() < 0.7) {
      const occ = new Set(tiles.map((t) => key(t.x, t.y)));
      const extra = ri(r, 1, g.key === 'hard' ? 4 : 2);
      for (let e = 0; e < extra; e++) {
        const base = pick(r, tiles), dd = pick(r, DIRS);
        const nx = base.x + dd.x, ny = base.y + dd.y;
        if (nx < 0 || ny < 0 || nx >= needSpan || ny >= needSpan || occ.has(key(nx, ny))) continue;
        occ.add(key(nx, ny));
        tiles.push({ x: nx, y: ny, h: Math.max(0, Math.min(MAX_H, base.h + pick(r, [0, 0, 1, -1]))), light: false });
      }
    }
    const start = { x: -minX, y: -minY, h: startH, dir: startDir };
    const slackMain = g.key === 'easy' ? 2 : 1;
    const maxSlots = {
      main: program.main.length + slackMain,
      proc1: program.proc1.length ? program.proc1.length + (g.key === 'hard' ? 0 : 1) : 0,
      proc2: program.proc2.length ? program.proc2.length + 0 : 0,
    };
    const usesJ = flat.includes('J');
    const level = {
      id: `${g.prefix}-${idx}`,
      category: g.key,
      title: `${g.label} - ${idx}`,
      maxSlots,
      allow: { jump: usesJ || g.key !== 'easy', proc1: program.proc1.length > 0, proc2: program.proc2.length > 0 },
      board: { start, tiles, endHint: { x: d.endX - minX, y: d.endY - minY } },
      sampleSolution: flat,
    };
    // doğrulama 1: üretilen program gerçek kurallarla tüm ışıkları yakıyor
    const res = playGame(level, program);
    if (!res.ok) continue;
    // doğrulama 2: orta/zor seviyeler prosedürsüz MAIN'e sığmamalı (prosedür gerçekten gerekli)
    if (g.key !== 'easy' && flat.length <= maxSlots.main) continue;
    // doğrulama 3: kolayda düz çözüm MAIN'e sığar
    if (g.key === 'easy' && flat.length > maxSlots.main) continue;
    const sig = JSON.stringify(level.board.tiles) + JSON.stringify(level.board.start);
    if (seen.has(sig)) continue;
    seen.add(sig);
    level._program = program;
    return level;
  }
  throw new Error(`Seviye üretilemedi: ${g.label} ${idx}`);
}

const levels = [];
const seen = new Set();
// Her grubu zorluk puanına göre sıralayıp (kısa/az ışık → uzun/çok ışık) numaralandırır: kolaydan zora düzgün ilerleme.
const score = (l) => l.sampleSolution.length + l.board.tiles.length * 0.6 + l.board.tiles.filter((t) => t.light).length * 1.5
  + (l.maxSlots.proc1 ? 4 : 0) + (l.maxSlots.proc2 ? 6 : 0) + l.board.tiles.reduce((m, t) => Math.max(m, t.h), 0) * 2;
for (const g of GROUPS) {
  const group = [];
  for (let i = 1; i <= g.count; i++) group.push(buildLevel(g, i, seen));
  group.sort((p, q) => score(p) - score(q));
  group.forEach((lv, k) => { lv.id = `${g.prefix}-${k + 1}`; lv.title = `${g.label} - ${k + 1}`; levels.push(lv); });
}
if (levels.length !== 100) throw new Error('100 seviye bekleniyordu: ' + levels.length);

const out = levels.map(({ _program, ...rest }) => rest);
const js = `/* Lightbot Runner — 100 bölümlük kategorili seviye seti (otomatik üretildi: tools/learn-levels/lightbot.gen.mjs, elle düzenlemeyin). */
window.LIGHTBOT_LEVELS = ${JSON.stringify(out)};
`;
fs.writeFileSync(OUT, js);
const rows = levels.map((l) => `${l.id.padEnd(5)} ${l.title.padEnd(10)} main=${String(l.maxSlots.main).padStart(2)} p1=${String(l.maxSlots.proc1).padStart(2)} p2=${String(l.maxSlots.proc2).padStart(2)} düz=${String(l.sampleSolution.length).padStart(3)} karo=${String(l.board.tiles.length).padStart(2)} ışık=${l.board.tiles.filter((t) => t.light).length}`);
console.log(rows.join('\n'));
console.log(`\n100 seviye -> ${path.relative(process.cwd(), OUT)} (${(js.length / 1024).toFixed(1)} KB)`);
