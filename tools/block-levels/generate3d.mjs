// 3D Blok Kodlama bölümlerini üretir ve doğrular.
//   node tools/block-levels/generate3d.mjs
// Çıktı: public/runner-assets/block-3d-runner/levels.js  (65 bölüm = eski 15 + 50 yeni, sıfırdan kolaydan zora)
// Bölüm sırası ödevlerdeki "seviye aralığını" belirler: mevcut ödevler varken sırayı değiştirmeyin, yeni bölümleri sona ekleyin.
import path from 'node:path';
import { here, makeBuilder, generate } from './lib.mjs';

const outFile = path.resolve(here, '../../public/runner-assets/block-3d-runner/levels.js');
const LEVEL_SET_VERSION = 2;

const plan = [];
const add = (type, n, params = {}, extra = {}) => plan.push({ type, n, params, ...extra });
// KOLAY 1-20 (çayır 1-10, kumsal 11-20)  — 5x5 → 7x7
add('straight', 5, { len: 2 }, { density: 0.08 }); add('straight', 5, { len: 3 }, { density: 0.08 }); add('straight', 5, { len: 4 }, { density: 0.08 });
add('L', 5, { a: 2, b: 2 }); add('L', 5, { a: 3, b: 2 }); add('L', 5, { a: 2, b: 3 });
add('L', 6, { a: 3, b: 3 }); add('L', 6, { a: 4, b: 2 }, { density: 0.15 });
add('S', 6, { a: 2, b: 2, c: 2 }); add('L', 6, { a: 4, b: 3 }, { density: 0.1 });
add('S', 6, { a: 2, b: 2, c: 3 });                                   // 11
add('straight', 6, { len: 5 }, { loops: true });                     // 12  tekrar bloğu tanıtımı
add('straight', 7, { len: 6 }, { loops: true });
add('staircase', 7, { k: 2, sx: 1, sy: 1 }, { loops: true });
add('staircase', 7, { k: 3, sx: 1, sy: 1 }, { loops: true });
add('staircase', 7, { k: 2, sx: 2, sy: 1 }, { loops: true });
add('detour', 7, { total: 6, bumps: [2] }, { density: 0.1 });         // 17
add('detour', 7, { total: 6, bumps: [2, 5] }, { density: 0.1 });
add('L', 7, { a: 6, b: 3 }, { loops: true });
add('S', 7, { a: 3, b: 3, c: 3 }, { loops: true });                   // 20
// ORTA 21-42 (kar 21-32, alacakaranlık 33-42) — 7x7 → 10x10
add('serpentine', 7, { k: 2, W: 5 });
add('serpentine', 8, { k: 2, W: 6 });
add('staircase', 8, { k: 3, sx: 2, sy: 1 }, { loops: true });
add('staircase', 8, { k: 3, sx: 1, sy: 2 }, { loops: true });
add('serpentine', 8, { k: 3, W: 5 }, { loops: true });
add('detour', 8, { total: 7, bumps: [2, 5] }, { density: 0.15 });
add('spiral', 8, { s: 6 });                                          // 27
add('maze', 8, {});                                                  // 28 ilk labirent
add('staircase', 8, { k: 5, sx: 1, sy: 1 }, { loops: true });
add('serpentine', 8, { k: 3, W: 6 }, { loops: true });
add('serpentine', 9, { k: 3, W: 7 }, { loops: true });
add('detour', 9, { total: 8, bumps: [2, 5] }, { density: 0.25 });
add('spiral', 9, { s: 7 });
add('maze', 9, {});
add('staircase', 9, { k: 4, sx: 2, sy: 2 }, { loops: true });
add('serpentine', 9, { k: 3, W: 7 }, { loops: true });
add('maze', 9, {});
add('serpentine', 10, { k: 3, W: 8 }, { loops: true });
add('spiral', 10, { s: 8 });
add('maze', 10, {});
add('serpentine', 10, { k: 4, W: 8 }, { loops: true });
add('maze', 10, {});                                                 // 42
// ZOR 43-58 (yanardağ 43-54, kozmik 55-58) — 11x11 → 13x13
add('serpentine', 11, { k: 5, W: 9 }, { loops: true });
add('maze', 11, {}); add('spiral', 11, { s: 8 }); add('maze', 11, {});
add('serpentine', 11, { k: 6, W: 9 }, { loops: true });
add('maze', 11, {}); add('spiral', 11, { s: 9 }); add('maze', 11, {});
add('maze', 12, {}); add('spiral', 12, { s: 9 });
add('serpentine', 12, { k: 6, W: 10 }, { loops: true });
add('maze', 12, {}); add('maze', 12, {}); add('spiral', 12, { s: 9 });
add('maze', 13, {});
add('serpentine', 13, { k: 7, W: 11 }, { loops: true });             // 58
// UZMAN 59-65 (kozmik) — 13x13 → 14x14
add('maze', 13, {}); add('spiral', 13, { s: 10 }); add('maze', 13, {}); add('maze', 13, {});
add('maze', 14, {}); add('spiral', 14, { s: 10 }); add('maze', 14, {}, { density: 0 });

const CONCEPT = {
  straight: 'Düz yol', L: 'İlk dönüş', S: 'Zikzak', U: 'U dönüşü', staircase: 'Merdiven',
  detour: 'Engeli aş', serpentine: 'Yılan yolu', spiral: 'Sarmal', maze: 'Labirent',
};
const THEMES = [[10, 'meadow'], [20, 'beach'], [32, 'snow'], [42, 'dusk'], [54, 'volcano'], [65, 'cosmic']];
const themeFor = (i) => THEMES.find(([max]) => i <= max)[1];
const groupFor = (i) => (i <= 20 ? 'Kolay' : i <= 42 ? 'Orta' : i <= 58 ? 'Zor' : 'Uzman');
const XP = { Kolay: 5, Orta: 12, Zor: 21, Uzman: 30 };
const HINTS = {
  1: 'Robotu hedefe götürmek için “İleri git” bloklarını ▶ BAŞLA kutusunun içine, alt alta bırak.',
  4: 'Köşelerde dönmen gerek: “Sola dön” ve “Sağa dön” bloklarını kullan.',
  12: 'Tekrarla bloğunu dene! Aynı bloğu tekrar tekrar koymak yerine sayıyı değiştir.',
  17: 'Engelin etrafından dolaşman gerekiyor. Önce hangi yöne döneceğini düşün.',
  21: 'Yılan yolu! Aynı hareketler tekrar ediyor — tekrar bloğu işini kolaylaştırır.',
  27: 'Sarmal yol: içe doğru dönüyor. Döngüyü iç içe kullanabilirsin.',
  28: 'İlk labirent! Duvarlara çarpmadan hedefe ulaş.',
  49: 'Yeni: “Yıldıza ulaşana kadar tekrarla” ve “Eğer önüm kapalıysa” blokları (isteğe bağlı).',
};

generate({
  plan, expected: 65, version: LEVEL_SET_VERSION, globalName: 'BLOCK_LEVEL_SET', outFile, sourceNote: 'tools/block-levels/generate3d.mjs',
  build: makeBuilder({ themeFor, groupFor, XP, CONCEPT, HINTS, namePrefix: 'Seviye ', loopsFrom: 12, advancedFrom: 49, easyStartMax: 11, sealRing: true, mazeSteps: (i) => Math.round(12 + (i - 28) * 0.65) }),
});
