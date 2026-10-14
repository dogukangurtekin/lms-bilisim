// 3D Blok Kodlama bölümlerini üretir ve doğrular (200 bölüm: Kolay 60, Orta 70, Zor 50, Uzman 20).
//   node tools/block-levels/generate3d.mjs
// Çıktı: public/runner-assets/block-3d-runner/levels.js
// Bölüm sırası ödevlerdeki "seviye aralığını" belirler; bölümler kolaydan zora doğru, gruplar arka arkaya gelir.
import path from 'node:path';
import { here, makeBuilder, generate } from './lib.mjs';
import { makePlan, hintsFor, groupAt, TOTAL } from './plan200.mjs';

const outFile = path.resolve(here, '../../public/runner-assets/block-3d-runner/levels.js');
const LEVEL_SET_VERSION = 3;   // 200 bölümlü yeni sıralama: eski yerel ilerleme geçersiz sayılır

const CONCEPT = {
  straight: 'Düz yol', L: 'İlk dönüş', S: 'Zikzak', U: 'U dönüşü', staircase: 'Merdiven',
  detour: 'Engeli aş', serpentine: 'Yılan yolu', spiral: 'Sarmal', maze: 'Labirent',
};
const THEMES = [[30, 'meadow'], [60, 'beach'], [100, 'snow'], [130, 'dusk'], [180, 'volcano'], [200, 'cosmic']];
const themeFor = (i) => THEMES.find(([max]) => i <= max)[1];
const groupFor = groupAt;
const XP = { Kolay: 5, Orta: 12, Zor: 21, Uzman: 30 };
const ADVANCED_FROM = 131;

const cfg = (hints) => ({ themeFor, groupFor, XP, CONCEPT, HINTS: hints, namePrefix: 'Seviye ', loopsFrom: 12, advancedFrom: ADVANCED_FROM, easyStartMax: 11, sealRing: true, mazeSteps: (i) => Math.max(8, Math.round(8 + (i - 40) * 0.3)) });

const probeBuild = makeBuilder(cfg({}));
const plan = makePlan((i, spec) => { try { probeBuild(i, spec); return true; } catch (e) { return false; } });
const HINTS = hintsFor(plan, {
  first: 'Robotu hedefe götürmek için “İleri git” bloklarını ▶ BAŞLA kutusunun içine, alt alta bırak.',
  L: 'Köşelerde dönmen gerek: “Sola dön” ve “Sağa dön” bloklarını kullan.',
  loops: 'Tekrarla bloğunu dene! Aynı bloğu tekrar tekrar koymak yerine sayıyı değiştir.',
  detour: 'Engelin etrafından dolaşman gerekiyor. Önce hangi yöne döneceğini düşün.',
  serpentine: 'Yılan yolu! Aynı hareketler tekrar ediyor — tekrar bloğu işini kolaylaştırır.',
  spiral: 'Sarmal yol: içe doğru dönüyor. Döngüyü iç içe kullanabilirsin.',
  maze: 'İlk labirent! Duvarlara çarpmadan hedefe ulaş.',
  advanced: 'Yeni: “Yıldıza ulaşana kadar tekrarla” ve “Eğer önüm kapalıysa” blokları (isteğe bağlı).',
}, ADVANCED_FROM);

generate({
  plan, expected: TOTAL, version: LEVEL_SET_VERSION, globalName: 'BLOCK_LEVEL_SET', outFile, sourceNote: 'tools/block-levels/generate3d.mjs',
  build: makeBuilder(cfg(HINTS)),
});
