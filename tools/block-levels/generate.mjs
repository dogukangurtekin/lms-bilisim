// Blok Kodlama (2B) bölümlerini üretir ve doğrular (200 bölüm: Kolay 60, Orta 70, Zor 50, Uzman 20).
//   node tools/block-levels/generate.mjs
// Çıktı: public/runner-assets/block-grid-runner/levels.js
// Bölümler kod içinde sabit veri olarak gönderilir (rastgelelik burada, sabit tohumla üretilir) böylece
// her öğrenci/öğretmen aynı bölümleri görür ve ödev aralıkları (1 tabanlı sıra) değişmez.
import path from 'node:path';
import { here, makeBuilder, generate } from './lib.mjs';
import { makePlan, hintsFor, groupAt, TOTAL } from './plan200.mjs';

const outFile = path.resolve(here, '../../public/runner-assets/block-grid-runner/levels.js');
const LEVEL_SET_VERSION = 3;   // 200 bölümlü yeni sıralama: eski yerel ilerleme geçersiz sayılır

const CONCEPT = {
  straight: 'Düz yol', L: 'İlk dönüş', S: 'Zikzak', U: 'U dönüşü', staircase: 'Merdiven',
  detour: 'Engeli aş', serpentine: 'Yılan yolu', spiral: 'Sarmal', maze: 'Labirent',
};
const THEMES = [[30, 'forest'], [60, 'desert'], [95, 'ice'], [130, 'space'], [180, 'lava'], [200, 'cosmic']];
const themeFor = (i) => THEMES.find(([max]) => i <= max)[1];
const groupFor = groupAt;
const XP = { Kolay: 5, Orta: 12, Zor: 21, Uzman: 30 };
const ADVANCED_FROM = 131;

const cfg = (hints) => ({ themeFor, groupFor, XP, CONCEPT, HINTS: hints, namePrefix: 'Bölüm ', loopsFrom: 13, sealRing: true, advancedFrom: ADVANCED_FROM, easyStartMax: 12, mazeSteps: (i) => Math.max(8, Math.round(8 + (i - 40) * 0.27)) });

const probeBuild = makeBuilder(cfg({}));
const plan = makePlan((i, spec) => { try { probeBuild(i, spec); return true; } catch (e) { return false; } });
const HINTS = hintsFor(plan, {
  first: 'Karakteri yıldıza götürmek için “İleri git” bloklarını ▶ BAŞLA kutusunun içine, alt alta bırak.',
  L: 'Köşelerde dönmen gerek: “Sola dön” ve “Sağa dön” bloklarını kullan.',
  loops: 'Tekrarla bloğunu dene! Aynı bloğu tekrar tekrar koymak yerine sayıyı değiştir.',
  detour: 'Engelin etrafından dolaşman gerekiyor. Önce hangi yöne döneceğini düşün.',
  serpentine: 'Yılan yolu! Aynı hareketler tekrar ediyor — tekrar bloğu işini kolaylaştırır.',
  spiral: 'Sarmal yol: içe doğru dönüyor. Döngüyü iç içe kullanabilirsin.',
  maze: 'İlk labirent! Duvarlara çarpmadan yıldıza ulaş.',
  advanced: 'Yeni: “Yıldıza kadar tekrarla” ve “Eğer önüm kapalıysa” blokları (isteğe bağlı).',
}, ADVANCED_FROM);

generate({
  plan, expected: TOTAL, version: LEVEL_SET_VERSION, globalName: 'BLOCK_LEVEL_SET', outFile, sourceNote: 'tools/block-levels/generate.mjs',
  build: makeBuilder(cfg(HINTS)),
});
