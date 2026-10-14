// 200 bölümlük plan üretici (2B ve 3B ortak): kolaydan zora, kare sayısı (n) azalmadan artar.
// Gruplar: Kolay 1-60, Orta 61-130, Zor 131-180, Uzman 181-200.
// Her aday şekil, verilen "probe" ile (gerçekten üretilip çözülebildiği doğrulanarak) denenir; olmazsa küçültülerek yeniden denenir.

export const TOTAL = 200;
export const GROUP_BOUNDS = [[60, 'Kolay'], [130, 'Orta'], [180, 'Zor'], [200, 'Uzman']];
export const groupAt = (i) => GROUP_BOUNDS.find(([max]) => i <= max)[1];

const N_STEPS = [[8, 5], [20, 6], [40, 7], [62, 8], [90, 9], [118, 10], [146, 11], [170, 12], [188, 13], [200, 14]];
export const nFor = (i) => N_STEPS.find(([max]) => i <= max)[1];

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

const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));
const LOOP_FROM = 13;

// i. bölüm için tercih sırasına dizilmiş aday şekil listesi
function candidates(i, n, r) {
  const ri = (a, b) => a + Math.floor(r() * (b - a + 1));
  const pick = (arr) => arr[Math.floor(r() * arr.length)];
  const loops = i >= LOOP_FROM;
  const dens = (lo, hi) => Math.round((lo + r() * (hi - lo)) * 100) / 100;
  const out = [];
  const add = (type, params, extra = {}) => out.push({ type, n, params, ...extra });

  const straight = () => add('straight', { len: clamp(ri(2 + Math.floor(i / 6), 3 + Math.floor(i / 5)), 2, n - 1) }, { loops, density: dens(0.05, 0.15) });
  const L = () => { const hi = Math.max(2, n - 2); add('L', { a: ri(2, hi), b: ri(2, hi) }, { loops, density: dens(0.05, 0.2) }); };
  const S = () => { const hi = Math.max(2, Math.floor((n - 2) / 2)); add('S', { a: ri(2, hi), b: ri(2, Math.max(2, n - 2)), c: ri(2, hi) }, { loops, density: dens(0.05, 0.2) }); };
  const U = () => { const hi = Math.max(2, n - 2); add('U', { a: ri(2, hi), b: ri(2, Math.max(2, Math.min(4, n - 2))), c: ri(2, hi) }, { loops, density: dens(0.05, 0.18) }); };
  const stair = (kMax) => { const sx = ri(1, 2), sy = ri(1, 2); const k = clamp(ri(2, kMax), 2, Math.floor((n - 1) / Math.max(sx, sy))); add('staircase', { k, sx, sy }, { loops: true, density: dens(0.05, 0.2) }); };
  const detour = (bumpsN) => {
    const bumps = [2]; for (let b = 1; b < bumpsN; b++) bumps.push(bumps[b - 1] + 3);
    const total = Math.min(n - 1, bumps[bumps.length - 1] + 2 + ri(1, 2));
    add('detour', { total, bumps }, { loops, density: dens(0.1, 0.4) });
  };
  const serp = (kMax) => { const W = clamp(n - 1 - ri(0, 1), 3, n - 1); const k = clamp(ri(2, kMax), 2, Math.floor((n - 1) / 2) + 1); add('serpentine', { k, W }, { loops: true }); };
  const spiral = (lo) => add('spiral', { s: clamp(ri(lo, n), 4, n) });
  const maze = () => add('maze', {}, { density: 0 });

  if (i <= 3) add('straight', { len: i + 1 }, { density: 0.08 });
  else if (i <= 8) { const a = 2 + (i % 2), b = 2 + ((i >> 1) % 2); add('L', { a, b }, { density: i > 6 ? 0.15 : 0 }); }
  else if (i <= 12) pick([S, L, S])();
  else if (i <= 24) pick([straight, L, S, U, () => stair(3)])();
  else if (i <= 40) pick([() => stair(4), () => detour(1), S, U, () => serp(2), L])();
  else if (i <= 60) pick([() => detour(r() < 0.4 ? 2 : 1), () => stair(5), () => serp(3), () => detour(2), U, S])();
  else if (i <= 130) pick([() => serp(5), () => stair(6), () => spiral(n - 1), () => detour(2), maze, maze, () => serp(4)])();
  else if (i <= 180) pick([() => serp(7), maze, maze, () => spiral(n - 1), () => detour(3), maze])();
  else pick([maze, maze, maze, () => spiral(n), () => serp(8)])();

  // yedekler: daha küçük/kolay şekiller
  add('S', { a: 2, b: 2, c: 2 }, { loops });
  add('L', { a: 2, b: 2 }, { loops });
  add('straight', { len: 3 }, { loops });
  return out;
}

/** probe(i, spec) -> true/false (ör. makeBuilder ile gerçekten üretilip doğrulanabiliyor mu) */
export function makePlan(probe) {
  const plan = [];
  for (let i = 1; i <= TOTAL; i++) {
    const r = mulberry32(i * 2654435761 + 97);
    const n = nFor(i);
    let chosen = null;
    for (let tries = 0; tries < 12 && !chosen; tries++) {
      const list = candidates(i, n, tries === 0 ? r : mulberry32(i * 31 + tries * 7919));
      for (const spec of list) { if (probe(i, spec)) { chosen = spec; break; } }
    }
    if (!chosen) throw new Error('Plan üretilemedi: bölüm ' + i);
    plan.push(chosen);
  }
  return plan;
}

/** Plan türlerinin ilk görüldüğü bölüme ipucu yerleştirir. texts: {first, L, loops, detour, serpentine, spiral, maze, advanced} */
export function hintsFor(plan, texts, advancedFrom) {
  const H = {};
  const seen = new Set();
  plan.forEach((spec, idx) => {
    const i = idx + 1;
    const k = spec.type;
    if (!seen.has(k) && texts[k] && !H[i]) { H[i] = texts[k]; seen.add(k); }
    if (spec.loops && !seen.has('loops') && texts.loops && !H[i]) { H[i] = texts.loops; seen.add('loops'); }
  });
  H[1] = texts.first;
  if (advancedFrom && texts.advanced) H[advancedFrom] = texts.advanced;
  return H;
}
