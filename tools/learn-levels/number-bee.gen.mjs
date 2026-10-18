// Sayı Arısı — 100 seviyelik (Kolay/Orta/Zor/Uzman × 25) seviye üretici ve doğrulayıcı.
// Çalıştır: node tools/learn-levels/number-bee.gen.mjs  →  public/runner-assets/number-bee-runner/levels.js
// Yöntem (özgün): önce çözüm programı (yön blokları + tekrarla döngüleri) üretilir, programın çizdiği yol ızgaraya yerleştirilir,
// yolun harfleri hedef kelimeyi oluşturur, kalan kareler yem harflerle doldurulur. Motor her seviyeyi çözümüyle doğrular;
// döngü gerektiren seviyelerde blok sınırı, döngüsüz uzun çözümü (L blok) imkânsız kılar.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const ENGINE = path.resolve(here, '../../public/runner-assets/number-bee-runner/engine.js');
const OUT = path.resolve(here, '../../public/runner-assets/number-bee-runner/levels.js');
const mod = { exports: {} };
new Function('module', 'exports', 'self', fs.readFileSync(ENGINE, 'utf8'))(mod, mod.exports, undefined);
const E = mod.exports;

function mulberry32(a) { return function () { a |= 0; a = (a + 0x6D2B79F5) | 0; let t = Math.imul(a ^ (a >>> 15), 1 | a); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
const makeRng = (seed) => { const f = mulberry32(seed); return { f, i: (a, b) => a + Math.floor(f() * (b - a + 1)), p: (arr) => arr[Math.floor(f() * arr.length)] }; };

// ---- hedef sayılar: rakamları rastgele, ilk rakam 0 değil, tümü aynı rakam değil, daha önce kullanılmamış.
const ALPHA = [...'0123456789'];
function makeNumber(r, L, used) {
  for (let k = 0; k < 500; k++) {
    let n = String(r.i(1, 9)); while (n.length < L) n += r.i(0, 9);
    if (new Set(n).size < Math.min(3, L) || used.has(n)) continue;
    return n;
  }
  throw new Error('Sayı üretilemedi');
}

// ---- program şablonları (kademeye göre)
const D4 = ['N', 'S', 'E', 'W'];
const OPP = { N: 'S', S: 'N', E: 'W', W: 'E' };
const mv = (d) => ({ t: 'm', d });
const rp = (n, body) => ({ t: 'r', n, body });
const turnDir = (r, from) => r.p(D4.filter((d) => d !== from && d !== OPP[from]));

function progEasy(r, L) {
  const prog = []; let prev = null;
  for (let i = 0; i < L; i++) {
    const d = prev && r.f() < 0.55 ? prev : r.p(D4.filter((x) => !prev || x !== OPP[prev]));
    prog.push(mv(d)); prev = d;
  }
  return prog;
}
function run3(r, L) {            // orta: düz koşular → tekrarla
  const k = r.i(1, 3);
  const dirs = []; let prev = null;
  for (let i = 0; i < k; i++) { const d = prev ? turnDir(r, prev) : r.p(D4); dirs.push(d); prev = d; }
  // uzunluğu k koşuya böl (her koşu ≥ 1); en az bir koşu ≥ 3 olsun
  const lens = Array(k).fill(1); let rest = L - k;
  while (rest-- > 0) lens[r.i(0, k - 1)]++;
  if (Math.max(...lens) < 3) return null;
  const prog = [];
  dirs.forEach((d, i) => { const n = lens[i]; if (n >= 3) prog.push(rp(n, [mv(d)])); else for (let j = 0; j < n; j++) prog.push(mv(d)); });
  return prog;
}
function zig(r, L) {              // zor: iki hareketlik merdiven döngüsü + giriş/çıkış hareketleri
  const a = r.p(D4), b = turnDir(r, a), n = r.i(2, 4), body = [mv(a), mv(b)];
  const lead = r.f() < 0.5 ? [mv(turnDir(r, b))] : [];
  const tail = [];
  let total = lead.length + 2 * n;
  if (total > L) return null;
  while (total < L) { tail.push(mv(r.p(D4))); total++; }
  if (r.f() < 0.4 && L - total === 0 && n >= 3) return [...lead, rp(n, body)];
  return [...lead, rp(n, body), ...tail];
}
function nested(r, L) {           // uzman: iç içe döngüler / karma
  const k = r.i(0, 3);
  const a = r.p(D4), b = turnDir(r, a);
  if (k === 0) { const n = r.i(2, 3), m = r.i(2, 3); return [rp(n, [mv(a), rp(m, [mv(b)])])]; }
  if (k === 1) { const n = r.i(2, 3), m = r.i(2, 3); return [mv(b), rp(n, [rp(m, [mv(a)]), mv(b)])]; }
  if (k === 2) { const n = r.i(2, 3), m = r.i(2, 3), c = turnDir(r, b); return [rp(n, [mv(a), mv(b)]), rp(m, [mv(c)])]; }
  const n = r.i(2, 3), m = r.i(2, 3); return [rp(m, [mv(a)]), rp(n, [mv(b), mv(a)])];
}
const MAKERS = { easy: progEasy, medium: run3, hard: zig, expert: nested };

function pathOf(prog) {
  const fl = E.flatten(prog); if (fl.overflow) return null;
  let x = 0, y = 0; const cells = [[0, 0]], seen = new Set(['0,0']);
  for (const s of fl.steps) {
    x += E.DIRS[s.d][0]; y += E.DIRS[s.d][1];
    const key = x + ',' + y; if (seen.has(key)) return null;    // kendini kesen yol yok
    seen.add(key); cells.push([x, y]);
  }
  return cells;
}

const CATS = ['easy', 'medium', 'hard', 'expert'];
const LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor', expert: 'Uzman' };
const XP = { easy: 10, medium: 15, hard: 20, expert: 25 };
const TIPS = {
  easy: 'Arıyı yön bloklarıyla hareket ettir. Bastığın her karedeki rakam, oluşturduğun sayıya eklenir. Arının bulunduğu kare sayılmaz.',
  medium: 'Düz giden yolları “tekrarla” bloğuyla kısalt. Blok sayın sınırı aşmamalı!',
  hard: 'Merdiven gibi tekrar eden hareketleri tek bir “tekrarla” bloğunda topla.',
  expert: 'Döngünün içine döngü koyabilirsin. En az blokla sayıyı oluşturabilir misin?',
};

function sizeFor(cat, t) {
  if (cat === 'easy') return t < 0.32 ? 4 : 5;
  if (cat === 'medium') return t < 0.4 ? 5 : 6;
  if (cat === 'hard') return t < 0.4 ? 6 : 7;
  return t < 0.4 ? 7 : 8;
}
function lenFor(cat, t) {
  if (cat === 'easy') return t < 0.4 ? 3 : 4;
  if (cat === 'medium') return t < 0.35 ? 5 : t < 0.75 ? 6 : 7;
  if (cat === 'hard') return t < 0.35 ? 6 : t < 0.7 ? 7 : 8;
  return t < 0.3 ? 6 : t < 0.65 ? 7 : 8;
}
const SLACK = { easy: null, medium: 2, hard: 1, expert: 0 };

const levels = []; const usedWords = {}; const usedNums = new Set(); const sigs = new Set();
for (let id = 1; id <= 100; id++) {
  const cat = CATS[Math.floor((id - 1) / 25)], t = ((id - 1) % 25) / 24;
  const w = sizeFor(cat, t), L = lenFor(cat, t), r = makeRng(id * 7919 + 13);
  let level = null;
  for (let tries = 0; tries < 40000 && !level; tries++) {
    const prog = MAKERS[cat](r, L); if (!prog) continue;
    const fl = E.flatten(prog); if (fl.overflow || fl.steps.length !== L) continue;
    const par = E.countBlocks(prog);
    // Blok sınırı: istenen pay (orta 2, zor 1, uzman 0) ama döngüsüz L bloklu çözümü her zaman dışarıda bırakır (sınır ≤ L-1).
    const slack = cat === 'easy' ? null : Math.min(SLACK[cat], L - 1 - par);
    if (cat !== 'easy' && slack < 0) continue;
    if (cat === 'easy' && prog.some((n) => n.t === 'r')) continue;
    const cells = pathOf(prog); if (!cells) continue;
    const xs = cells.map((c) => c[0]), ys = cells.map((c) => c[1]);
    const minX = Math.min(...xs), maxX = Math.max(...xs), minY = Math.min(...ys), maxY = Math.max(...ys);
    if (maxX - minX + 1 > w || maxY - minY + 1 > w) continue;
    const sx = r.i(-minX, w - 1 - maxX), sy = r.i(-minY, w - 1 - maxY);
    const word = makeNumber(r, L, usedNums), chars = [...word];
    const grid = Array.from({ length: w }, () => Array(w).fill(null));
    cells.slice(1).forEach((c, i) => { grid[sy + c[1]][sx + c[0]] = chars[i]; });
    for (let y = 0; y < w; y++) for (let x = 0; x < w; x++) if (!grid[y][x]) grid[y][x] = r.f() < 0.35 ? r.p(chars) : r.p(ALPHA);
    const rows = grid.map((row) => row.join(''));
    const sig = rows.join('|') + sx + sy + word; if (sigs.has(sig)) continue;
    level = {
      id, cat, title: LABEL[cat] + ' · ' + (((id - 1) % 25) + 1), w, h: w, grid: rows, start: { x: sx, y: sy }, word,
      par, maxBlocks: slack === null ? null : par + slack, loops: cat !== 'easy', xp: XP[cat], tip: TIPS[cat], sol: prog,
    };
    sigs.add(sig); usedNums.add(word);
  }
  if (!level) throw new Error('Seviye üretilemedi: ' + id);
  // ---- doğrulama
  const res = E.run(level, level.sol);
  if (res.status !== 'ok') throw new Error('Çözüm doğrulanamadı ' + id + ': ' + res.status);
  if (level.maxBlocks !== null && level.par > level.maxBlocks) throw new Error('Blok sınırı çözümden küçük ' + id);
  if (level.maxBlocks !== null && E.flatten(level.sol).steps.length <= level.maxBlocks) throw new Error('Döngüsüz çözüm sınıra sığıyor ' + id);
  levels.push(level);
}

const body = JSON.stringify(levels);
fs.writeFileSync(OUT, '// Otomatik üretildi: node tools/learn-levels/number-bee.gen.mjs — elle düzenlemeyin.\nwindow.NUMBER_BEE_LEVELS = ' + body + ';\n');
const stat = {}; for (const l of levels) { stat[l.cat] = stat[l.cat] || { n: 0, len: new Set(), grid: new Set() }; stat[l.cat].n++; stat[l.cat].len.add([...l.word].length); stat[l.cat].grid.add(l.w); }
for (const [k, v] of Object.entries(stat)) console.log(k, v.n, 'rakam', [...v.len].join('/'), 'ızgara', [...v.grid].join('/'));
console.log('toplam', levels.length, 'benzersiz sayı', new Set(levels.map((l) => l.word)).size, 'boyut', Math.round(body.length / 1024) + ' KB');
