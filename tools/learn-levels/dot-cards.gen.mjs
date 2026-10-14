// Küçük Nokta — Kod Kartları: 100 seviyelik (Kolay/Orta/Zor/Uzman × 25) seviye seti üretici ve doğrulayıcı.
// Çalıştır: node tools/learn-levels/dot-cards.gen.mjs  →  public/runner-assets/dot-cards-runner/levels.js
// Üretim yöntemi (özgün): önce çözüm kartları rastgele üretilip haritada gerçekten çalıştırılır, hedef son konuma konur,
// sonra yem (decoy) kartlar eklenir. Çözücü her seviyenin çözülebildiğini ve tasarlanan kart sayısından kısa yol olmadığını doğrular.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const ENGINE = path.resolve(here, '../../public/runner-assets/dot-cards-runner/engine.js');
const OUT = path.resolve(here, '../../public/runner-assets/dot-cards-runner/levels.js');
const mod = { exports: {} };
new Function('module', 'exports', 'self', fs.readFileSync(ENGINE, 'utf8'))(mod, mod.exports, undefined);
const E = mod.exports;

function mulberry32(a) { return function () { a |= 0; a = (a + 0x6D2B79F5) | 0; let t = Math.imul(a ^ (a >>> 15), 1 | a); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
const DIRN = ['E', 'S', 'W', 'N'];

function makeRng(seed) {
  const f = mulberry32(seed);
  const r = { f, i: (a, b) => a + Math.floor(f() * (b - a + 1)), p: (arr) => arr[Math.floor(f() * arr.length)], sh: (arr) => { const a = arr.slice(); for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(f() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; } };
  return r;
}

// ---- kart adayları (tier: 1 mutlak yönler · 2 göreli+tekrar · 3 koşullu/iken · 4 iç içe)
const mv = (dir, n) => ({ t: 'move', dir, n });
const fw = (n) => ({ t: 'fwd', n });
const tn = (d) => ({ t: 'turn', d });
function cand(tier, r) {
  const dir = () => r.p(DIRN), side = () => r.p(['left', 'right']);
  if (tier === 1) return r.f() < 0.55 ? [mv(dir(), r.i(1, 4))] : [mv(dir(), r.i(1, 3)), mv(dir(), r.i(1, 3))];
  if (tier === 2) {
    const k = r.i(0, 5);
    if (k === 0) return [fw(r.i(1, 4))];
    if (k === 1) return [tn(side()), fw(r.i(1, 3))];
    if (k === 2) return [fw(r.i(1, 3)), tn(side()), fw(r.i(1, 3))];
    if (k === 3) return [{ t: 'repeat', n: r.i(2, 4), body: [fw(1)] }];
    if (k === 4) return [{ t: 'repeat', n: r.i(2, 3), body: [fw(r.i(1, 2)), tn(side())] }];
    return [mv(dir(), r.i(1, 3)), tn(side()), fw(r.i(1, 3))];
  }
  if (tier === 3) {
    const cond = r.p(['frontFree', 'frontSafe', 'rightFree', 'leftFree']);
    const k = r.i(0, 5);
    if (k === 0) return [{ t: 'while', cond: r.p(['frontFree', 'frontSafe']), body: [fw(1)] }];
    if (k === 1) return [{ t: 'while', cond: 'frontSafe', body: [fw(1)] }, tn(side())];
    if (k === 2) return [{ t: 'if', cond, then: [fw(r.i(1, 3))], else: [tn(side()), fw(r.i(1, 2))] }];
    if (k === 3) return [{ t: 'if', cond, then: [tn(side()), fw(r.i(1, 3))], else: [fw(r.i(1, 2))] }];
    if (k === 4) return [fw(r.i(1, 2)), { t: 'if', cond, then: [tn(side())], else: [] }, fw(r.i(1, 3))];
    return [{ t: 'repeat', n: r.i(2, 4), body: [{ t: 'if', cond, then: [fw(1)], else: [tn(side())] }] }];
  }
  const cond = r.p(['frontFree', 'frontSafe', 'rightFree', 'leftFree']);
  const k = r.i(0, 5);
  if (k === 0) return [{ t: 'while', cond: 'frontSafe', body: [fw(1)] }, tn(side()), { t: 'while', cond: 'frontSafe', body: [fw(1)] }];
  if (k === 1) return [{ t: 'repeat', n: r.i(2, 4), body: [{ t: 'if', cond: 'rightFree', then: [tn('right'), fw(1)], else: [fw(1)] }] }];
  if (k === 2) return [{ t: 'repeat', n: r.i(2, 3), body: [{ t: 'while', cond: 'frontSafe', body: [fw(1)] }, tn(side())] }];
  if (k === 3) return [{ t: 'if', cond, then: [{ t: 'while', cond: 'frontSafe', body: [fw(1)] }], else: [tn(side()), { t: 'repeat', n: r.i(1, 3), body: [fw(1)] }] }];
  if (k === 4) return [{ t: 'repeat', n: r.i(2, 3), body: [{ t: 'if', cond: 'leftFree', then: [tn('left'), fw(2)], else: [fw(1), tn('right')] }] }];
  return [fw(r.i(1, 2)), { t: 'while', cond, body: [fw(1)] }, tn(side()), fw(r.i(1, 3))];
}
const code = (ast) => E.codeLines(JSON.parse(JSON.stringify(ast))).join('\n');

// ---- plan (100 seviye: Kolay/Orta/Zor/Uzman × 25): [kategori, mod, tier, çözüm kartı, yem kartı, boyut, duvar yoğunluğu, çukur yoğunluğu]
const PLAN = [];
const add = (cat, mode, tier, k, dec, size, wall, pit) => PLAN.push({ cat, mode, tier, k, dec, size, wall, pit });
const lerp = (a, b, t) => a + (b - a) * t;
// KOLAY (25): 3 gezinti · 5 doğru-kartı-bul · 17 kart dizisi
add('easy', 'roam', 0, 0, 0, 5, 0.08, 0); add('easy', 'roam', 0, 0, 0, 5, 0.14, 0.04); add('easy', 'roam', 0, 0, 0, 6, 0.14, 0.06);
[[5, 3, 0.08, 0], [5, 4, 0.1, 0.03], [6, 4, 0.1, 0.03], [6, 4, 0.12, 0.04], [6, 5, 0.12, 0.04]].forEach(([s, d, w, p]) => add('easy', 'find', 1, 1, d, s, w, p));
for (let i = 0; i < 17; i++) { const t = i / 16; add('easy', 'seq', 1, 2 + Math.floor(t * 2.99), 1 + Math.floor(t * 2.2), 5 + Math.floor(t * 2.99), lerp(0.08, 0.13, t), lerp(0, 0.05, t)); }
// ORTA (25): 3 doğru-kartı-bul · 22 kart dizisi
[[6, 4, 0.1, 0.03], [6, 5, 0.11, 0.03], [7, 5, 0.12, 0.04]].forEach(([s, d, w, p]) => add('medium', 'find', 2, 1, d, s, w, p));
for (let i = 0; i < 22; i++) { const t = i / 21; add('medium', 'seq', 2, 2 + Math.floor(t * 3.99), 1 + Math.floor(t * 2.99), 6 + Math.floor(t * 2.99), lerp(0.1, 0.16, t), lerp(0.03, 0.07, t)); }
// ZOR (25)
[[7, 4, 0.14, 0.05], [7, 5, 0.15, 0.05], [8, 5, 0.16, 0.06]].forEach(([s, d, w, p]) => add('hard', 'find', 3, 1, d, s, w, p));
for (let i = 0; i < 22; i++) { const t = i / 21; add('hard', 'seq', 3, 2 + Math.floor(t * 3.99), 2 + Math.floor(t * 2.2), 7 + Math.floor(t * 2.99), lerp(0.14, 0.2, t), lerp(0.05, 0.09, t)); }
// UZMAN (25)
[[8, 5, 0.16, 0.06], [9, 6, 0.18, 0.07], [9, 6, 0.19, 0.08]].forEach(([s, d, w, p]) => add('expert', 'find', 4, 1, d, s, w, p));
for (let i = 0; i < 22; i++) { const t = i / 21; add('expert', 'seq', 4, 3 + Math.floor(t * 3.99), 2 + Math.floor(t * 2.99), 8 + Math.floor(t * 2.99), lerp(0.17, 0.22, t), lerp(0.07, 0.11, t)); }
if (PLAN.length !== 100) throw new Error('Plan 100 olmalı: ' + PLAN.length);

const TIPS = {
  roam: 'Ok tuşlarıyla (ya da ekrandaki yön düğmeleriyle) noktayı turuncu hedefe götür. Duvarlardan geçemezsin, çukurlara düşersen başa dönersin.',
  seq: 'Kartlardaki kodları oku, noktanın nereye gideceğini düşün ve kartları doğru sırayla seç. Yanlış bir kart noktayı duvara ya da çukura götürürse kart geri döner.',
  find: 'Bu bölümde yalnızca TEK bir kart noktayı hedefe götürüyor. Kodları okuyup doğru kartı bul!',
};
const CONCEPT_TIP = {
  1: 'Yeni: sağa(3) gibi komutlar noktayı o yönde 3 kare yürütür.',
  2: 'Yeni: ileri(n) noktanın baktığı yönde ilerler, dön_sağ() / dön_sol() baktığı yönü değiştirir, tekrarla (n) { … } içindekileri n kez yapar.',
  3: 'Yeni: eğer (koşul) { … } değilse { … } koşula göre karar verir; iken (koşul) { … } koşul doğru olduğu sürece tekrar eder. Örnek koşul: önüm_güvenli = önünde duvar ve çukur yok.',
  4: 'Uzman kartlar iç içe döngü ve koşullar içerir. Adım adım, noktanın baktığı yöne dikkat ederek oku.',
};

function buildLevel(spec, idx, seed) {
  for (let attempt = 0; attempt < 1500; attempt++) {
    const r = makeRng(seed * 7919 + attempt * 104729 + 17);
    const N = spec.size;
    const grid = Array.from({ length: N }, () => Array(N).fill('.'));
    const sx = r.i(0, N - 1), sy = r.i(0, N - 1), sdir = spec.cat === 'easy' ? r.p(['E', 'S']) : r.p(DIRN);
    for (let y = 0; y < N; y++) for (let x = 0; x < N; x++) {
      if (x === sx && y === sy) continue;
      const q = r.f();
      grid[y][x] = q < spec.pit ? 'o' : q < spec.pit + spec.wall ? '#' : '.';
    }
    const rows = () => grid.map((row) => row.join(''));
    const start = { x: sx, y: sy, dir: sdir };

    if (spec.mode === 'roam') {
      // hedef: başlangıçtan uzak bir hücre
      const cells = []; for (let y = 0; y < N; y++) for (let x = 0; x < N; x++) if (grid[y][x] === '.' && Math.abs(x - sx) + Math.abs(y - sy) >= N - 1) cells.push([x, y]);
      if (!cells.length) continue; const [gx, gy] = r.p(cells); grid[gy][gx] = 'G';
      const level = { cells: rows(), start };
      if (!E.roamSolvable(level)) continue;
      return { mode: 'roam', w: N, h: N, cells: rows(), start, cards: [], sol: [] };
    }

    // çözüm kartları
    const world0 = E.makeWorld({ cells: rows() });
    let st = { x: sx, y: sy, dir: sdir }; const visited = new Set([sx + ',' + sy]), cards = []; let ok = true;
    for (let k = 0; k < spec.k && ok; k++) {
      let placed = false;
      for (let t = 0; t < 80 && !placed; t++) {
        const ast = cand(spec.tier, r);
        const res = E.runCard(world0, st, ast);
        if (!res.ok || res.goal) continue;
        const moved = res.steps.some((s) => s.k === 'move');
        const endKey = res.end.x + ',' + res.end.y;
        if (!moved || visited.has(endKey)) continue;
        if (cards.some((c) => code(c.ast) === code(ast))) continue;
        visited.add(endKey); cards.push({ ast }); st = res.end; placed = true;
      }
      if (!placed) ok = false;
    }
    if (!ok) continue;
    const gx = st.x, gy = st.y; grid[gy][gx] = 'G';
    const solIds = cards.map((_, i) => i);
    // yem kartlar
    let decoys = 0, guard = 0;
    while (decoys < spec.dec && guard++ < 200) {
      const ast = cand(spec.tier, r);
      if (cards.some((c) => code(c.ast) === code(ast))) continue;
      cards.push({ ast }); decoys++;
    }
    if (decoys < spec.dec) continue;
    // karıştır
    const order = r.sh(cards.map((_, i) => i));
    const deck = order.map((i, pos) => ({ id: pos + 1, ast: cards[i].ast, _orig: i }));
    const level = { cells: rows(), start, cards: deck };
    const sols = E.solve(level, 200);
    if (!sols.length) continue;
    if (spec.mode === 'find') {
      if (sols.length !== 1 || sols[0].length !== 1) continue;
    } else {
      if (sols.some((s) => s.length < spec.k)) continue;                         // tasarlanandan kısa yol yok
      // tek kartla hedefe varılmasın
      if (sols.some((s) => s.length === 1)) continue;
      if (sols.length > ({ easy: 12, medium: 20, hard: 30, expert: 60 })[spec.cat]) continue;   // çok yollu (fazla kolay) seviyeleri ele
    }
    sols.sort((a, b) => a.length - b.length);
    return {
      mode: spec.mode, w: N, h: N, cells: rows(), start,
      cards: deck.map((c) => ({ id: c.id, ast: c.ast })),
      sol: sols[0].map((i) => deck[i].id),
      wins: sols.length,
    };
  }
  throw new Error('Seviye üretilemedi: ' + spec.cat + ' ' + idx);
}

const LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor', expert: 'Uzman' };
const XP = { easy: 10, medium: 15, hard: 20, expert: 25 };
const MODE_TITLE = { roam: 'Serbest Gezinti', find: 'Doğru Kartı Bul', seq: 'Kart Dizisi' };
const levels = []; const sigs = new Set(); const catCount = {}; const seenTier = new Set();
PLAN.forEach((spec, idx) => {
  let lv = null;
  for (let bump = 0; bump < 30 && !lv; bump++) {
    const cand0 = buildLevel(spec, idx, (idx + 1) * 131 + bump * 977);
    const sig = JSON.stringify([cand0.cells, cand0.start, cand0.cards.map((c) => c.ast)]);
    if (!sigs.has(sig)) { sigs.add(sig); lv = cand0; }
  }
  if (!lv) throw new Error('Tekrarlayan seviye: ' + idx);
  catCount[spec.cat] = (catCount[spec.cat] || 0) + 1;
  const tip = [];
  tip.push(TIPS[spec.mode]);
  const tierKey = spec.mode === 'roam' ? 0 : spec.tier;
  if (tierKey && !seenTier.has(tierKey)) { seenTier.add(tierKey); tip.push(CONCEPT_TIP[tierKey]); }
  levels.push({ id: idx + 1, cat: spec.cat, title: `${LABEL[spec.cat]} ${catCount[spec.cat]} · ${MODE_TITLE[spec.mode]}`, xp: XP[spec.cat], tip: tip.join(' '), ...lv });
});

// ---- son doğrulama (bağımsız): her seviye yeniden çözülür
for (const l of levels) {
  if (l.mode === 'roam') { if (!E.roamSolvable(l)) throw new Error('Roam çözümsüz: ' + l.id); continue; }
  const s = E.solve(l, 50);
  if (!s.length) throw new Error('Çözümsüz: ' + l.id);
  if (l.mode === 'find' && !(s.length === 1 && s[0].length === 1)) throw new Error('Tek kart kuralı bozuk: ' + l.id);
}
const js = `/* Küçük Nokta — Kod Kartları: 100 seviye (otomatik üretildi: tools/learn-levels/dot-cards.gen.mjs, elle düzenlemeyin). */\nwindow.DOT_CARDS_LEVELS = ${JSON.stringify(levels)};\n`;
fs.writeFileSync(OUT, js);
console.log(levels.map((l) => `${String(l.id).padStart(2)} ${l.title.padEnd(28)} ${l.w}x${l.h} kart=${String(l.cards.length).padStart(2)} çözüm=${(l.sol || []).join('→') || '-'} ${l.wins ? '(' + l.wins + ' yol)' : ''}`).join('\n'));
console.log(`\n${levels.length} seviye -> ${path.relative(process.cwd(), OUT)} (${(js.length / 1024).toFixed(1)} KB)`);
