// Arı Bahçesi (CSS Kodlama) — 100 bölümlük kategorili seviye seti üretici.
// Çalıştır: node tools/learn-levels/bee-garden.gen.mjs  →  public/runner-assets/bee-garden-runner/levels.js
// Kolay 25 · Orta 30 · Zor 25 · Uzman 20. Hedef çiçek konumları oyunda gerçek flexbox düzeninden hesaplanır;
// burada çözüm (container + items) tanımlanır, tekrarlayan ve belirsiz (aynı görünen) çözümler elenir.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(here, '../../public/runner-assets/bee-garden-runner/levels.js');

function mulberry32(a) { return function () { a |= 0; a = (a + 0x6D2B79F5) | 0; let t = Math.imul(a ^ (a >>> 15), 1 | a); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
const rnd = mulberry32(20261014);
const ri = (a, b) => a + Math.floor(rnd() * (b - a + 1));
const pick = (arr) => arr[Math.floor(rnd() * arr.length)];

const JC = [['flex-start', 'Başa Yasla'], ['center', 'Ortala'], ['flex-end', 'Sona Yasla'], ['space-between', 'Aralıklı (Kenar Boş)'], ['space-around', 'Aralıklı (Kenar Var)'], ['space-evenly', 'Eşit Aralıklı']];
const AI = [['flex-start', 'Yukarı Yasla'], ['center', 'Ortala'], ['flex-end', 'Aşağı Yasla'], ['stretch', 'Uzat']];
const FD = [['row', 'Yatay ➜'], ['column', 'Dikey ⬇'], ['row-reverse', 'Yatay Ters ⟵'], ['column-reverse', 'Dikey Ters ⬆']];
const CODE = { 'flex-start': 'basa', center: 'ortaya', 'flex-end': 'sona', 'space-between': 'esit-arada', 'space-around': 'esit-kenarli', 'space-evenly': 'tam-esit', stretch: 'uzat', row: 'yatay', column: 'dikey', 'row-reverse': 'yatay-ters', 'column-reverse': 'dikey-ters' };
const opts = (list, n) => list.slice(0, n).map(([value, text]) => ({ value, text }));
const J3 = ['flex-start', 'center', 'flex-end'];
const J6 = JC.map((x) => x[0]);

const TUT = {
  jc: 'Bu oyunda arıyı sağdaki bahçede çiçeğin üstüne konduracaksın. Sol taraftaki kutucuğa kurallar yazarak (ya da blokları sürükleyerek) arıya komut verirsin. <code>yatay-hizala</code> komutu arının SOLA-SAĞA doğru nereye gideceğini belirler: <code>basa</code> (sola), <code>ortaya</code> (ortaya), <code>sona</code> (sağa).',
  ai: 'Yeni komut: <code>dikey-hizala</code>. Bu komut arının YUKARI-AŞAĞI doğru nereye gideceğini belirler: <code>basa</code> (yukarı), <code>ortaya</code> (ortaya), <code>sona</code> (aşağı).',
  both: 'İki komutu AYNI ANDA kullanabilirsin! <code>yatay-hizala</code> ve <code>dikey-hizala</code> birlikte yazılırsa arı hem yatayda hem dikeyde istediğin yere gider.',
  multi: 'Birden fazla arı olunca <code>yatay-hizala</code> için yeni değerler kullanabilirsin: <code>esit-arada</code> (aralarında eşit boşluk, kenarlar boş), <code>esit-kenarli</code> (kenarlarda da boşluk var), <code>tam-esit</code> (hepsi tamamen eşit aralıklı).',
  fd: 'Yeni komut: <code>ucus-yonu</code>. Arıların hangi YÖNDE dizileceğini değiştirir: <code>yatay</code> (soldan sağa, varsayılan) veya <code>dikey</code> (yukarıdan aşağıya). Yön değişince dikkat: <code>yatay-hizala</code> artık YUKARI-AŞAĞI eksenini kontrol etmeye başlar!',
  rev: 'Yönün <code>yatay-ters</code> ve <code>dikey-ters</code> değerleri arıların sırasını tersine çevirir: <code>basa</code> artık karşı uçtur.',
  self: 'Yeni komut: <code>kendi-hizasi</code>. Diğer komutlar TÜM arılara birden uygulanır, ama bu komut SADECE seçtiğin TEK bir arıya özel bir kural yazmanı sağlar. Aşağıdan hangi arı olduğunu seç.',
};

// Oyundaki bahçe: kenarlık+iç boşluk düşüldükten sonra içerik alanı CWxCH, arılar 52x52. Varsayılan düzen: row / flex-start / flex-start.
const BEE = 52;
function flexCenters(bees, container, items, CW, CH) {
  const dir = container.flexDirection || 'row', jc = container.justifyContent || 'flex-start', ai = container.alignItems || 'flex-start';
  const row = dir.startsWith('row'), rev = dir.endsWith('reverse');
  const main = row ? CW : CH, cross = row ? CH : CW;
  const free = Math.max(0, main - bees * BEE);
  let offset = 0, gap = 0;
  if (jc === 'center') offset = free / 2;
  else if (jc === 'flex-end') offset = free;
  else if (jc === 'space-between') gap = bees > 1 ? free / (bees - 1) : 0;
  else if (jc === 'space-around') { gap = free / bees; offset = gap / 2; }
  else if (jc === 'space-evenly') { gap = free / (bees + 1); offset = gap; }
  const out = [];
  for (let i = 0; i < bees; i++) {
    let pos = offset + i * (BEE + gap);
    if (rev) pos = main - pos - BEE;
    const al = (items && items[i + 1] && items[i + 1].alignSelf) || ai;
    const cpos = al === 'center' ? (cross - BEE) / 2 : al === 'flex-end' ? cross - BEE : 0;
    out.push(row ? { x: pos + BEE / 2, y: cpos + BEE / 2 } : { x: cpos + BEE / 2, y: pos + BEE / 2 });
  }
  return out;
}
// Başlangıçta herhangi bir arı kendi çiçeğinin üstündeyse (iki farklı bahçe boyutunda) seviye reddedilir.
function startsOnFlower(bees, container, items) {
  return [[600, 362], [580, 342], [520, 320], [480, 300], [440, 280], [380, 262]].some(([CW, CH]) => {
    const start = flexCenters(bees, {}, null, CW, CH), target = flexCenters(bees, container, items, CW, CH);
    return start.some((s, i) => Math.abs(s.x - target[i].x) < BEE * 0.6 && Math.abs(s.y - target[i].y) < BEE * 0.6);
  });
}

const recipes = []; // { cat, feature, bees, fd?, jc?, ai?, items?: n, opt:{jc,ai,fd} }
const signature = new Set();

function trySolution(cat, spec, k = 0) {
  // spec: { bees, jcSet, aiSet, fdSet, items, optJC, optAI, optFD, tut, feature }
  for (let attempt = 0; attempt < 200; attempt++) {
    const bees = typeof spec.bees === 'function' ? spec.bees() : spec.bees;
    const container = {};
    const fd = spec.fdSet ? pick(spec.fdSet) : null;
    const jc = spec.jcSet ? (spec.ordered ? spec.jcSet[k % spec.jcSet.length] : pick(spec.jcSet)) : null;
    const ai = spec.aiSet ? (spec.ordered ? spec.aiSet[k % spec.aiSet.length] : pick(spec.aiSet)) : null;
    if (fd) container.flexDirection = fd;
    if (jc) container.justifyContent = jc;
    if (ai) container.alignItems = ai;
    if (bees === 1 && jc && !J3.includes(jc)) continue;           // tek arıda "aralıklı" değerler belirsiz
    if (bees === 1 && fd && fd.includes('reverse')) continue;
    if (bees >= 2 && spec.jcRequireSpread && !(jc && jc.startsWith('space'))) continue;
    let items = null;
    if (spec.items) {
      items = {};
      const idxs = Array.from({ length: bees }, (_, i) => i + 1).sort(() => rnd() - 0.5).slice(0, Math.min(spec.items, bees - 1));
      for (const i of idxs) {
        const base = ai || 'flex-start';
        const choices = J3.filter((v) => v !== base);
        items[i] = { alignSelf: pick(choices) };
      }
    }
    const isDefault = (!fd || fd === 'row') && (!jc || jc === 'flex-start') && (!ai || ai === 'flex-start') && !items;
    if (isDefault) continue;
    if (startsOnFlower(bees, container, items)) continue;   // arı zaten çiçeğin üstünde başlamasın
    const sig = JSON.stringify([bees, container, items]);
    if (signature.has(sig)) continue;
    signature.add(sig);
    return { bees, container, items };
  }
  throw new Error('Çözüm bulunamadı: ' + cat + ' ' + spec.feature);
}

const STAGES = [
  // ---- KOLAY (25)
  ['easy', 2, { bees: 1, jcSet: ['center', 'flex-end'], optJC: 3, tut: 'jc', feature: 'Yatay hizalama', ordered: true }],
  ['easy', 2, { bees: 1, aiSet: ['center', 'flex-end'], optAI: 3, tut: 'ai', feature: 'Dikey hizalama', ordered: true }],
  ['easy', 7, { bees: 1, jcSet: J3, aiSet: J3, optJC: 3, optAI: 3, tut: 'both', feature: 'Köşeye kondur' }],
  ['easy', 6, { bees: () => ri(2, 3), jcSet: J6, optJC: 6, tut: 'multi', feature: 'Eşit aralık' }],
  ['easy', 6, { bees: 3, jcSet: J6, aiSet: J3, optJC: 6, optAI: 3, feature: 'Karma hizalama' }],
  ['easy', 2, { bees: 1, fdSet: ['column'], jcSet: J3, optFD: 2, optJC: 3, tut: 'fd', feature: 'Dikey dizilim' }],
  // ---- ORTA (30)
  ['medium', 6, { bees: () => ri(2, 3), fdSet: ['column'], jcSet: J6, optFD: 2, optJC: 6, feature: 'Dikey dizilim' }],
  ['medium', 6, { bees: 3, fdSet: ['row-reverse', 'column-reverse', 'column'], jcSet: J6, optFD: 4, optJC: 6, tut: 'rev', feature: 'Ters yön' }],
  ['medium', 6, { bees: () => ri(2, 4), fdSet: ['row', 'column'], jcSet: J6, aiSet: J3, optFD: 2, optJC: 6, optAI: 3, feature: 'Yön + hizalama' }],
  ['medium', 6, { bees: 3, aiSet: J3, items: 1, optAI: 3, tut: 'self', feature: 'Farklı çiçek' }],
  ['medium', 6, { bees: () => ri(3, 4), fdSet: ['row', 'column'], aiSet: J3, items: 1, optFD: 2, optAI: 3, feature: 'Yön + farklı çiçek' }],
  // ---- ZOR (25)
  ['hard', 6, { bees: () => ri(3, 4), fdSet: ['row', 'column', 'row-reverse', 'column-reverse'], jcSet: J6, aiSet: J3, optFD: 4, optJC: 6, optAI: 3, feature: 'Üç komut' }],
  ['hard', 7, { bees: () => ri(3, 4), aiSet: J3, items: 2, optAI: 3, feature: 'İki farklı çiçek' }],
  ['hard', 6, { bees: 4, fdSet: ['row', 'column', 'row-reverse', 'column-reverse'], jcSet: J6, items: 1, optFD: 4, optJC: 6, optAI: 3, feature: 'Yön + dağıtım + özel' }],
  ['hard', 6, { bees: () => ri(4, 5), fdSet: ['row', 'column', 'row-reverse', 'column-reverse'], jcSet: J6, aiSet: J3, items: 1, optFD: 4, optJC: 6, optAI: 3, feature: 'Hepsi bir arada' }],
  // ---- UZMAN (20)
  ['expert', 6, { bees: () => ri(4, 5), fdSet: ['row', 'column', 'row-reverse', 'column-reverse'], jcSet: J6, aiSet: J3, optFD: 4, optJC: 6, optAI: 4, feature: 'Uzman: tam kontrol' }],
  ['expert', 7, { bees: () => ri(4, 5), fdSet: ['row', 'column', 'row-reverse', 'column-reverse'], jcSet: J6, aiSet: J3, items: 2, optFD: 4, optJC: 6, optAI: 4, feature: 'Uzman: özel arılar' }],
  ['expert', 7, { bees: 5, fdSet: ['row', 'column', 'row-reverse', 'column-reverse'], jcSet: J6, aiSet: J3, items: 3, optFD: 4, optJC: 6, optAI: 4, feature: 'Uzman: büyük sürü' }],
];

const LABEL = { easy: 'Kolay', medium: 'Orta', hard: 'Zor', expert: 'Uzman' };
const levels = [];
const catCount = { easy: 0, medium: 0, hard: 0, expert: 0 };
const ASSIGN_OPT = opts(AI, 3);

function titleFor(sol, feature) {
  const c = sol.container, it = sol.items;
  if (it) return 'Farklı Çiçekler';
  if (c.flexDirection && c.flexDirection.includes('reverse')) return 'Ters Yönde Uçuş';
  if (c.flexDirection === 'column') return 'Dikey Dizilim';
  if (c.justifyContent && c.justifyContent.startsWith('space')) return 'Eşit Aralıkla Diz';
  if (c.justifyContent && c.alignItems) return 'Köşeye Kondur';
  if (c.alignItems && !c.justifyContent) return 'Dikey Hizala';
  if (c.justifyContent && !c.alignItems) return 'Yatay Hizala';
  return feature;
}

function descFor(cat, level) {
  const c = level.solution.container;
  const names = level.properties.map((p) => p.cssName);
  if (level.itemProperty) names.push(level.itemProperty.cssName);
  if (cat === 'easy') {
    const bits = [];
    if (c.flexDirection) bits.push(`"ucus-yonu" komutunu "${CODE[c.flexDirection]}" yap`);
    if (c.justifyContent) bits.push(`"yatay-hizala" komutunu "${CODE[c.justifyContent]}" yap`);
    if (c.alignItems) bits.push(`"dikey-hizala" komutunu "${CODE[c.alignItems]}" yap`);
    return `${level.beeCount} arıyı çiçeklere kondur. ${bits.join(', ')}.`;
  }
  const hint = cat === 'medium' ? ' Çiçeklere dikkatle bak.' : cat === 'hard' ? ' Yön değişince eksenlerin yer değiştirdiğini unutma.' : ' Çiçeklerin konumundan doğru komut birleşimini çıkar.';
  return `${level.beeCount} arıyı çiçeklere kondur. Kullanabileceğin komutlar: ${names.join(', ')}.${hint}`;
}

function build() {
  const seenTut = new Set();
  for (const [cat, count, spec] of STAGES) {
    const sols = [];
    for (let i = 0; i < count; i++) sols.push(trySolution(cat, spec, i));
    if (!spec.ordered) {
      // aşamada kolaydan zora: önce çözümü basit (az özel değer) olanlar
      const score = (s) => (s.container.flexDirection && s.container.flexDirection !== 'row' ? 1 : 0) + (s.container.justifyContent && s.container.justifyContent.startsWith('space') ? 1 : 0) + (s.items ? Object.keys(s.items).length : 0) + s.bees * 0.3;
      sols.sort((a, b) => score(a) - score(b));
    }
    sols.forEach((s, k) => {
      const properties = [];
      const hasFD = !!s.container.flexDirection || !!spec.fdSet;
      if (spec.fdSet) properties.push({ key: 'flexDirection', cssName: 'ucus-yonu', label: 'Yön (ucus-yonu)', options: opts(FD, spec.optFD) });
      if (spec.jcSet) properties.push({ key: 'justifyContent', cssName: 'yatay-hizala', label: (hasFD ? 'Ana Eksen Hizalama' : 'Yatay Hizalama') + ' (yatay-hizala)', options: opts(JC, spec.optJC) });
      if (spec.aiSet) properties.push({ key: 'alignItems', cssName: 'dikey-hizala', label: (hasFD ? 'Yan Eksen Hizalama' : 'Dikey Hizalama') + ' (dikey-hizala)', options: opts(AI, spec.optAI) });
      const level = { category: cat, title: titleFor(s, spec.feature), beeCount: s.bees, properties, solution: { container: s.container } };
      if (s.items) { level.solution.items = s.items; level.itemProperty = { key: 'alignSelf', cssName: 'kendi-hizasi', label: 'Seçili Arının Kendi Hizalaması (kendi-hizasi)', options: ASSIGN_OPT }; }
      level.desc = descFor(cat, level);
      if (spec.tut && k === 0 && !seenTut.has(spec.tut)) { level.tutorial = TUT[spec.tut]; seenTut.add(spec.tut); }
      catCount[cat]++;
      level.title = `${LABEL[cat]} ${catCount[cat]} · ${level.title}`;
      levels.push(level);
    });
  }
}
build();
if (levels.length !== 100) throw new Error('100 seviye bekleniyordu: ' + levels.length);
levels.forEach((l, i) => { l.id = i + 1; });

// ---- doğrulama: her seviyede çözümdeki her değer, sunulan seçenekler arasında olmalı
for (const l of levels) {
  for (const p of l.properties) {
    const v = l.solution.container[p.key];
    if (v !== undefined && !p.options.some((o) => o.value === v)) throw new Error(`#${l.id} ${p.key}=${v} seçeneklerde yok`);
    if (v === undefined && !['flexDirection', 'justifyContent', 'alignItems'].includes(p.key)) throw new Error('beklenmeyen özellik');
  }
  for (const k of Object.keys(l.solution.container)) if (!l.properties.some((p) => p.key === k)) throw new Error(`#${l.id}: ${k} için komut verilmemiş`);
}

const js = `/* Arı Bahçesi — 100 bölümlük kategorili seviye seti (otomatik üretildi: tools/learn-levels/bee-garden.gen.mjs, elle düzenlemeyin). */\nwindow.BEE_GARDEN_LEVELS = ${JSON.stringify(levels)};\n`;
fs.writeFileSync(OUT, js);
const by = levels.reduce((a, l) => ((a[l.category] = (a[l.category] || 0) + 1), a), {});
console.log(levels.map((l) => `${String(l.id).padStart(3)} ${l.title}  [arı ${l.beeCount}]`).join('\n'));
console.log(`\n100 seviye ${JSON.stringify(by)} -> ${path.relative(process.cwd(), OUT)} (${(js.length / 1024).toFixed(1)} KB)`);
