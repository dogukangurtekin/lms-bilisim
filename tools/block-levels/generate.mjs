// Blok Kodlama (2B) bölümlerini üretir ve doğrular.
//   node tools/block-levels/generate.mjs
// Çıktı: public/runner-assets/block-grid-runner/levels.js
// Bölümler kod içinde sabit veri olarak gönderilir (rastgelelik burada, sabit tohumla üretilir) böylece
// her öğrenci/öğretmen aynı bölümleri görür ve ödev aralıkları (1 tabanlı sıra) değişmez.
import path from 'node:path';
import { here, makeBuilder, generate } from './lib.mjs';

const outFile = path.resolve(here, '../../public/runner-assets/block-grid-runner/levels.js');
const LEVEL_SET_VERSION = 2;

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


generate({
  plan, expected: 82, version: LEVEL_SET_VERSION, globalName: 'BLOCK_LEVEL_SET', outFile, sourceNote: 'tools/block-levels/generate.mjs',
  build: makeBuilder({ themeFor, groupFor, XP, CONCEPT, HINTS, namePrefix: 'Bölüm ', loopsFrom: 13, advancedFrom: 61, easyStartMax: 12, mazeSteps: (i) => Math.round(12 + (i - 33) * 0.5) }),
});
