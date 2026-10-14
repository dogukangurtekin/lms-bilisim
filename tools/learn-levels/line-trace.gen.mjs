// Çizgi Oyunu (Line Trace) — 100 bölümlük kategorili seviye seti üretici.
// Çalıştır: node tools/learn-levels/line-trace.gen.mjs  →  public/runner-assets/line-trace-runner/levels.js
// Kolay 25 · Orta 30 · Zor 25 · Uzman 20. Yollar 2:1 oranlı birim uzayda (x: 0..2, y: 0..1) tasarlanır ve tahtaya
// oranı bozmadan (daire daire kalacak şekilde) yerleştirilir (units: "ar2").
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(here, '../../public/runner-assets/line-trace-runner/levels.js');

function mulberry32(a) { return function () { a |= 0; a = (a + 0x6D2B79F5) | 0; let t = Math.imul(a ^ (a >>> 15), 1 | a); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
const rnd = mulberry32(20261014);
const rr = (a, b) => a + rnd() * (b - a);
const ri = (a, b) => Math.floor(rr(a, b + 1));
const TAU = Math.PI * 2;

const dist = (a, b) => Math.hypot(a[0] - b[0], a[1] - b[1]);
const pathLen = (pts) => pts.reduce((s, p, i) => (i ? s + dist(pts[i - 1], p) : 0), 0);

// ---- şekil üreticileri: [x, y] noktaları (serbest ölçekte), sonra fit() ile 2x1 kutuya sığdırılır
const poly = (verts, closeExtra = 0.18) => { // köşeli yol; kapalıysa başlangıcı biraz geçer
  const pts = verts.map((v) => v.slice());
  if (closeExtra) { const a = pts[0], b = pts[1]; pts.push([a[0] + (b[0] - a[0]) * closeExtra, a[1] + (b[1] - a[1]) * closeExtra]); }
  return pts;
};
const regular = (n, rot = -Math.PI / 2, closed = true) => { const v = []; for (let i = 0; i < n; i++) v.push([Math.cos(rot + (TAU * i) / n), Math.sin(rot + (TAU * i) / n)]); return closed ? poly(v) : v; };
const starShape = (n, inner) => { const v = []; for (let i = 0; i < n * 2; i++) { const r = i % 2 ? inner : 1; const a = -Math.PI / 2 + (Math.PI * i) / n; v.push([Math.cos(a) * r, Math.sin(a) * r]); } return poly(v, 0.15); };
const curve = (fn, steps) => Array.from({ length: steps + 1 }, (_, i) => fn(i / steps));
const zigzag = (k, amp) => { const v = []; for (let i = 0; i <= k * 2; i++) v.push([i / (k * 2), i % 2 ? amp : -amp]); return v; };
const wave = (periods, amp) => curve((t) => [t, Math.sin(t * periods * TAU) * amp], Math.max(24, periods * 28));
const spiral = (turns, startR = 0.06) => curve((t) => { const a = t * turns * TAU, r = startR + (1 - startR) * t; return [Math.cos(a) * r, Math.sin(a) * r]; }, Math.round(turns * 40));
const circle = (rx = 1, ry = 1, extra = 0.1) => curve((t) => [Math.cos(-Math.PI / 2 + t * TAU * (1 + extra)) * rx, Math.sin(-Math.PI / 2 + t * TAU * (1 + extra)) * ry], 72);
const heart = () => curve((t) => { const a = t * TAU; return [16 * Math.sin(a) ** 3, -(13 * Math.cos(a) - 5 * Math.cos(2 * a) - 2 * Math.cos(3 * a) - Math.cos(4 * a))]; }, 80);
const infinity = () => curve((t) => { const a = t * TAU; const d = 1 + Math.sin(a) ** 2; return [Math.cos(a) / d, Math.sin(a) * Math.cos(a) / d]; }, 80);
const rose = (k) => curve((t) => { const a = t * TAU * (k % 2 === 0 ? 2 : 1); const r = Math.cos(k * a); return [r * Math.cos(a), r * Math.sin(a)]; }, k * 60);
const serpentine = (rows, cols) => { const v = []; for (let r = 0; r < rows; r++) { const y = rows === 1 ? 0 : r / (rows - 1); const xs = r % 2 === 0 ? [0, 1] : [1, 0]; v.push([xs[0], y], [xs[1], y]); } return v; };
const bolt = () => [[0.55, 0], [0.15, 0.55], [0.5, 0.55], [0.35, 1], [0.9, 0.35], [0.55, 0.35], [0.85, 0]];
const arrow = () => [[0, 0.35], [0.6, 0.35], [0.6, 0], [1, 0.5], [0.6, 1], [0.6, 0.65], [0, 0.65]];
const house = () => [[0, 1], [0, 0.4], [0.5, 0], [1, 0.4], [1, 1], [0, 1], [0, 0.4]];
const crown = () => [[0, 1], [0, 0.25], [0.25, 0.6], [0.5, 0], [0.75, 0.6], [1, 0.25], [1, 1], [0, 1]];
const letter = (c) => ({ M: [[0, 1], [0, 0], [0.5, 0.65], [1, 0], [1, 1]], W: [[0, 0], [0.25, 1], [0.5, 0.35], [0.75, 1], [1, 0]], N: [[0, 1], [0, 0], [1, 1], [1, 0]], Z: [[0, 0], [1, 0], [0, 1], [1, 1]], S: curve((t) => [0.5 + 0.5 * Math.cos(t * TAU * 0.5 + Math.PI * 1.1) * (t < 0.5 ? 1 : 1), t], 0), E: [[1, 0], [0, 0], [0, 0.5], [0.8, 0.5], [0, 0.5], [0, 1], [1, 1]] })[c];
const sCurve = () => curve((t) => [t, 0.5 + 0.5 * Math.sin((t - 0.5) * Math.PI * 2) * 0.9], 40);
const arc = (sweep) => curve((t) => [Math.cos(Math.PI + t * sweep), -Math.sin(Math.PI + t * sweep)], 40);
const smooth = (n, seed, wobble = 1) => { // Catmull-Rom ile rastgele akıcı eğri
  const r = mulberry32(seed);
  const ctrl = Array.from({ length: n }, (_, i) => [i / (n - 1) * 2 + (r() - 0.5) * 0.25 * wobble, r()]);
  const out = [];
  for (let i = 0; i < n - 1; i++) {
    const p0 = ctrl[Math.max(0, i - 1)], p1 = ctrl[i], p2 = ctrl[i + 1], p3 = ctrl[Math.min(n - 1, i + 2)];
    for (let s = 0; s < 14; s++) {
      const t = s / 14, t2 = t * t, t3 = t2 * t;
      out.push([0.5 * (2 * p1[0] + (-p0[0] + p2[0]) * t + (2 * p0[0] - 5 * p1[0] + 4 * p2[0] - p3[0]) * t2 + (-p0[0] + 3 * p1[0] - 3 * p2[0] + p3[0]) * t3),
        0.5 * (2 * p1[1] + (-p0[1] + p2[1]) * t + (2 * p0[1] - 5 * p1[1] + 4 * p2[1] - p3[1]) * t2 + (-p0[1] + 3 * p1[1] - 3 * p2[1] + p3[1]) * t3)]);
    }
  }
  out.push(ctrl[n - 1]);
  return out;
};

// ---- 2x1 kutuya (kenar boşluğuyla) oran bozulmadan sığdır; çok sık noktaları seyrelt
function fit(pts, { stretchX = false } = {}) {
  const xs = pts.map((p) => p[0]), ys = pts.map((p) => p[1]);
  const x0 = Math.min(...xs), x1 = Math.max(...xs), y0 = Math.min(...ys), y1 = Math.max(...ys);
  const bw = Math.max(1e-6, x1 - x0), bh = Math.max(1e-6, y1 - y0);
  const boxW = 1.84, boxH = 0.82;
  let sx = boxW / bw, sy = boxH / bh;
  let s = Math.min(sx, sy);
  if (bh < 0.02) s = sx;           // tam yatay çizgi
  const w = bw * s, h = bh * s;
  const ox = 1 - w / 2 - x0 * s, oy = 0.5 - h / 2 - y0 * s;
  let out = pts.map((p) => [p[0] * s + ox, p[1] * s + oy]);
  // seyrelt: toplam uzunluğa göre en fazla ~150 nokta, ama köşeler korunur (poligonlarda zaten az)
  if (out.length > 150) {
    const L = pathLen(out), step = L / 150, red = [out[0]];
    for (let i = 1; i < out.length - 1; i++) if (dist(red[red.length - 1], out[i]) >= step) red.push(out[i]);
    red.push(out[out.length - 1]);
    out = red;
  }
  return out.map((p) => [Math.round(p[0] * 1000) / 1000, Math.round(p[1] * 1000) / 1000]);
}
const rot = (pts, a) => pts.map(([x, y]) => [x * Math.cos(a) - y * Math.sin(a), x * Math.sin(a) + y * Math.cos(a)]);
const flipX = (pts) => pts.map(([x, y]) => [-x, y]);
const rev = (pts) => pts.slice().reverse();

// ---- bölüm tarifleri (sırayla kolaydan zora)
const R = { easy: [], medium: [], hard: [], expert: [] };
const add = (cat, name, pts, opts = {}) => R[cat].push({ name, pts, ...opts });

// KOLAY (25)
add('easy', 'Düz Çizgi', [[0, 0], [1, 0]]);
add('easy', 'Eğik Çizgi', [[0, 1], [1, 0]]);
add('easy', 'Kısa Dalga', wave(1, 0.35));
add('easy', 'Zikzak', zigzag(2, 0.5));
add('easy', 'Üçgen', regular(3));
add('easy', 'Kare', regular(4, -Math.PI / 4));
add('easy', 'Eğim Aşağı', [[0, 0], [0.45, 0.2], [1, 1]]);
add('easy', 'Yarım Daire', arc(Math.PI));
add('easy', 'Basamak', [[0, 1], [0.34, 1], [0.34, 0.5], [0.67, 0.5], [0.67, 0], [1, 0]]);
add('easy', 'Uzun Zikzak', zigzag(3, 0.5));
add('easy', 'Elmas', regular(4, -Math.PI / 2));
add('easy', 'S Kıvrımı', sCurve());
add('easy', 'Ok', arrow());
add('easy', 'Yay', arc(Math.PI * 1.3));
add('easy', 'Ev', house());
add('easy', 'Çift Dalga', wave(2, 0.4));
add('easy', 'Ters Üçgen', rot(regular(3), Math.PI));
add('easy', 'Zikzak Yukarı', rot(zigzag(3, 0.4), -0.35));
add('easy', 'Beşgen', regular(5));
add('easy', 'Köşeli U', [[0, 0], [0, 1], [1, 1], [1, 0]]);
add('easy', 'Harf Z', letter('Z'));
add('easy', 'Harf N', letter('N'));
add('easy', 'Dalga ve Tepe', wave(1.5, 0.45));
add('easy', 'Dik Zikzak', rot(zigzag(4, 0.35), Math.PI / 2));
add('easy', 'Yıldırım', bolt());
// ORTA (30)
add('medium', 'Altıgen', regular(6));
add('medium', 'Daire', circle(1, 1));
add('medium', 'Elips', circle(1.8, 0.8));
add('medium', 'Üç Dalga', wave(3, 0.4));
add('medium', 'Harf M', letter('M'));
add('medium', 'Harf W', letter('W'));
add('medium', 'Küçük Spiral', spiral(1.5));
add('medium', 'Taç', crown());
add('medium', 'Harf E', letter('E'));
add('medium', 'Yılan 3', serpentine(3, 2));
add('medium', 'Sekizgen', regular(8));
add('medium', 'Yıldırım Zinciri', [...bolt(), ...bolt().map(([x, y]) => [x + 1.1, y])]);
add('medium', 'Sık Zikzak', zigzag(6, 0.45));
add('medium', 'Elmas Zinciri', [[0, 0.5], [0.25, 0], [0.5, 0.5], [0.75, 1], [1, 0.5], [1.25, 0], [1.5, 0.5], [1.75, 1], [2, 0.5]]);
add('medium', 'Spiral 2 Tur', spiral(2));
add('medium', 'Beş Yıldız Yayı', rot(arc(Math.PI * 1.6), 0.4));
add('medium', 'Dalga ve Döngü', curve((t) => [t * 2 + 0.28 * Math.sin(t * TAU * 3), 0.5 + 0.4 * Math.cos(t * TAU * 3)], 90));
add('medium', 'İç İçe Kare', [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0.2], [0.8, 0.2], [0.8, 0.8], [0.2, 0.8], [0.2, 0.4]]);
add('medium', 'Yılan 4', serpentine(4, 2));
add('medium', 'Kule', [[0, 1], [0, 0.55], [0.25, 0.55], [0.25, 0.3], [0.5, 0.3], [0.5, 0.05], [0.75, 0.05], [0.75, 0.3], [1, 0.3], [1, 1]]);
add('medium', 'Çift S', curve((t) => [t, 0.5 + 0.42 * Math.sin(t * TAU * 2.5)], 70));
add('medium', 'Bumerang', curve((t) => [Math.cos(t * Math.PI * 1.15) * (0.4 + 0.6 * t), Math.sin(t * Math.PI * 1.15) * (0.4 + 0.6 * t)], 60));
add('medium', 'Kalp Yarısı', curve((t) => { const a = t * Math.PI; return [16 * Math.sin(a) ** 3, -(13 * Math.cos(a) - 5 * Math.cos(2 * a) - 2 * Math.cos(3 * a) - Math.cos(4 * a))]; }, 40));
add('medium', 'Pençe', [[0, 0], [0.35, 1], [0.55, 0.15], [0.75, 1], [1, 0.1], [1.2, 1]]);
add('medium', 'Dalgalı Kare', curve((t) => { const a = t * TAU; return [Math.sign(Math.cos(a)) * Math.abs(Math.cos(a)) ** 0.5, Math.sign(Math.sin(a)) * Math.abs(Math.sin(a)) ** 0.5]; }, 70));
add('medium', 'Çarpı Yolu', [[0, 0], [1, 1], [1, 0], [0, 1], [0, 0.5]]);
add('medium', 'Zigzag Sarmal', [...zigzag(4, 0.5), ...zigzag(4, 0.5).map(([x, y]) => [1 - x, y + 1.3]).slice(1)]);
add('medium', 'Akıcı Eğri 1', smooth(6, 101));
add('medium', 'Akıcı Eğri 2', smooth(7, 202));
add('medium', 'Kement', spiral(1));
// ZOR (25)
add('hard', 'Yıldız', starShape(5, 0.4));
add('hard', 'Kalp', heart());
add('hard', 'Sonsuzluk', infinity());
add('hard', 'Üç Yapraklı Çiçek', rose(3));
add('hard', 'Spiral 2,5 Tur', spiral(2.5));
add('hard', 'Altı Köşeli Yıldız', starShape(6, 0.5));
add('hard', 'Yılan 5', serpentine(5, 2));
add('hard', 'Sık Dalga', wave(5, 0.35));
add('hard', 'Dört Yapraklı Çiçek', rose(2));
add('hard', 'Akıcı Eğri 3', smooth(8, 303));
add('hard', 'Akıcı Eğri 4', smooth(9, 404));
add('hard', 'Yedi Köşeli Yıldız', starShape(7, 0.42));
add('hard', 'Dişli', (() => { const v = []; for (let i = 0; i < 24; i++) v.push([Math.cos(TAU * i / 24) * (i % 2 ? 0.8 : 1), Math.sin(TAU * i / 24) * (i % 2 ? 0.8 : 1)]); return poly(v, 0.1); })());
add('hard', 'Çift Spiral', curve((t) => { const a = t * TAU * 2; const r = 0.15 + 0.85 * Math.abs(Math.sin(t * Math.PI)); return [Math.cos(a) * r * 1.2, Math.sin(a) * r]; }, 110));
add('hard', 'Kuş', [[0, 0.2], [0.25, 0.7], [0.5, 0.3], [0.75, 0.7], [1, 0.2]].concat(wave(3, 0.2).map(([x, y]) => [1 + x * 0.9, 0.5 + y])));
add('hard', 'Yıldırım Üçlüsü', [...bolt(), ...bolt().map(([x, y]) => [x + 1, y]), ...bolt().map(([x, y]) => [x + 2, y])]);
add('hard', 'Yılan 6', serpentine(6, 2));
add('hard', 'Sekiz Yaprak', rose(4));
add('hard', 'Akıcı Eğri 5', smooth(10, 505));
add('hard', 'Spiral Zigzag', curve((t) => { const a = t * TAU * 3; const r = 0.1 + 0.9 * t; return [Math.cos(a) * r + 0.04 * Math.sin(a * 9), Math.sin(a) * r + 0.04 * Math.cos(a * 9)]; }, 200));
add('hard', 'Labirent Yolu', [[0, 1], [0, 0], [0.2, 0], [0.2, 0.8], [0.4, 0.8], [0.4, 0.2], [0.6, 0.2], [0.6, 0.8], [0.8, 0.8], [0.8, 0.2], [1, 0.2], [1, 1]]);
add('hard', 'Akıcı Eğri 6', smooth(11, 606));
add('hard', 'Kelebek', curve((t) => { const a = t * TAU; const r = Math.exp(Math.cos(a)) - 2 * Math.cos(4 * a) + Math.sin(a / 12) ** 5; return [Math.sin(a) * r, -Math.cos(a) * r]; }, 220));
add('hard', 'Akıcı Eğri 7', smooth(12, 707));
add('hard', 'Gül Spirali', curve((t) => { const a = t * TAU * 3; const r = 0.3 + 0.7 * t + 0.18 * Math.cos(5 * a); return [Math.cos(a) * r, Math.sin(a) * r]; }, 240));
// UZMAN (20)
add('expert', 'Beş Yaprak', rose(5));
add('expert', 'Spiral 3,5 Tur', spiral(3.5, 0.04));
add('expert', 'Dokuz Köşeli Yıldız', starShape(9, 0.55));
add('expert', 'Akıcı Eğri 8', smooth(13, 808, 1.4));
add('expert', 'Akıcı Eğri 9', smooth(14, 909, 1.4));
add('expert', 'Yılan 7', serpentine(7, 2));
add('expert', 'Üç Halka', curve((t) => { const a = t * TAU * 3; return [Math.cos(a) * (0.4 + 0.6 * Math.abs(Math.sin(t * Math.PI * 3))), Math.sin(a) * (0.4 + 0.6 * Math.abs(Math.sin(t * Math.PI * 3)))]; }, 220));
add('expert', 'Akıcı Eğri 10', smooth(15, 1010, 1.5));
add('expert', 'Yedi Yaprak', rose(7));
add('expert', 'Akıcı Eğri 11', smooth(16, 1111, 1.5));
add('expert', 'Sık Spiral', spiral(4.5, 0.03));
add('expert', 'Akıcı Eğri 12', smooth(17, 1212, 1.6));
add('expert', 'Labirent Spiral', [[0, 0], [1, 0], [1, 1], [0.1, 1], [0.1, 0.2], [0.8, 0.2], [0.8, 0.8], [0.3, 0.8], [0.3, 0.4], [0.6, 0.4], [0.6, 0.6]]);
add('expert', 'Akıcı Eğri 13', smooth(18, 1313, 1.7));
add('expert', 'Yıldız Zinciri', [...starShape(5, 0.45), ...starShape(5, 0.45).map(([x, y]) => [x + 2.4, y])]);
add('expert', 'Akıcı Eğri 14', smooth(19, 1414, 1.8));
add('expert', 'Dalga Fırtınası', curve((t) => [t * 2, 0.5 + 0.3 * Math.sin(t * TAU * 6) + 0.12 * Math.sin(t * TAU * 17)], 200));
add('expert', 'Akıcı Eğri 15', smooth(21, 1515, 1.9));
add('expert', 'Karmaşık Çiçek', curve((t) => { const a = t * TAU * 6; const r = 0.55 + 0.35 * Math.cos(6 * a / 3) + 0.1 * Math.cos(11 * a / 3); return [Math.cos(a) * r, Math.sin(a) * r]; }, 300));
add('expert', 'Usta Parkur', smooth(23, 1616, 2));

const CONF = {
  easy: { label: 'easy', count: 25, pass: [50, 58], tol: [18, 15], xp: 6 },
  medium: { label: 'medium', count: 30, pass: [60, 68], tol: [15, 13], xp: 9 },
  hard: { label: 'hard', count: 25, pass: [70, 76], tol: [13, 11], xp: 17 },
  expert: { label: 'expert', count: 20, pass: [76, 84], tol: [11, 9], xp: 25 },
};

const levels = [];
for (const cat of ['easy', 'medium', 'hard', 'expert']) {
  const cf = CONF[cat];
  if (R[cat].length !== cf.count) throw new Error(`${cat}: ${R[cat].length} tarif var, ${cf.count} bekleniyordu`);
  R[cat].forEach((rec, k) => {
    const t = k / (cf.count - 1);
    const pts = fit(rec.pts);
    const L = pathLen(pts);
    if (pts.length < 2 || L < 0.6) throw new Error(`${rec.name}: yol çok kısa`);
    levels.push({
      name: rec.name, difficulty: cat, units: 'ar2', points: pts,
      pass: Math.round(cf.pass[0] + (cf.pass[1] - cf.pass[0]) * t),
      baseTol: Math.round((cf.tol[0] + (cf.tol[1] - cf.tol[0]) * t) * 10) / 10,
    });
  });
}
if (levels.length !== 100) throw new Error('100 seviye bekleniyordu: ' + levels.length);

// doğrulama: tüm noktalar kutunun içinde ve yol tek bir noktadan ibaret değil
for (const l of levels) for (const [x, y] of l.points) if (x < 0.02 || x > 1.98 || y < 0.02 || y > 0.98 || !Number.isFinite(x + y)) throw new Error('sınır dışı: ' + l.name);

const js = `/* Çizgi Oyunu — 100 bölümlük kategorili seviye seti (otomatik üretildi: tools/learn-levels/line-trace.gen.mjs, elle düzenlemeyin). */\nwindow.LINE_TRACE_LEVELS = ${JSON.stringify(levels)};\n`;
fs.writeFileSync(OUT, js);
console.log(levels.map((l, i) => `${String(i + 1).padStart(3)} ${l.difficulty.padEnd(7)} ${l.name.padEnd(22)} nokta=${String(l.points.length).padStart(3)} uzunluk=${pathLen(l.points).toFixed(2)} geçme=%${l.pass} tol=${l.baseTol}`).join('\n'));
console.log(`\n100 seviye -> ${path.relative(process.cwd(), OUT)} (${(js.length / 1024).toFixed(1)} KB)`);
