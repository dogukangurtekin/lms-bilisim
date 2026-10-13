# 3D Blok Kodlama (v2) — teknik belge

2B Blok Kodlama ile aynı yenileme 3D oyuna da uygulandı: **65 seviye** (eski 15 + 50 yeni, sıfırdan kolaydan zora), ızgara **5×5 → 14×14**,
yeni izometrik (3 boyutlu görünümlü) görsel ve **mıknatıs gibi yapışan sürükle-bırak blok editörü**.

| Grup | Seviye | Izgara | XP | Dünya |
|---|---|---|---|---|
| Kolay | 1–20 | 5×5 → 7×7 | 5 | Çayır (1–10), Kumsal (11–20) |
| Orta | 21–42 | 7×7 → 10×10 | 12 | Kar (21–32), Alacakaranlık (33–42) |
| Zor | 43–58 | 11×11 → 13×13 | 21 | Yanardağ (43–54), Kozmik (55–58) |
| Uzman | 59–65 | 13×13 → 14×14 | 30 | Kozmik |

Tekrarla bloğu 12. seviyeden itibaren blok sınırlarıyla öğretilir; “Yıldıza ulaşana kadar tekrarla” ve “Eğer önüm kapalıysa” blokları 49. seviyeden itibaren açılır.
Yılan yolu ve sarmal seviyelerinin çevresine duvar çekilir (kestirme olmasın diye); en iyi çözüm (par) ile yıldız puanı hesaplanır.

## Dosyalar
- `index.html`, `style.css` — arayüz (açık/koyu tema, mobil uyumlu).
- `app.js` — oyun mantığı, ilerleme (`b3d.v2` anahtarı), platform mesajları.
- `render3d.js` — izometrik çizim motoru (kalın zemin blokları, engeller, dönen yıldız + ışık huzmesi, zıplayan robot, su/uzay arka planı).
- `levels.js` — **otomatik üretilmiş** 65 seviye; `node tools/block-levels/generate3d.mjs` ile yeniden üretilir (elle düzenlemeyin).
- `engine.js`, `editor.js` — 2B oyunla **ortak**; yalnızca `block-grid-runner/` içinde düzenleyin, sonra `node tools/block-levels/sync-shared.mjs` çalıştırın.

## Platform sözleşmesi (eski 3D sürümle uyumlu)
- URL: `role`, `levelStart/levelEnd` (veya `from/to`), `assignmentId`, `assignmentTitle`, `grant=1`; yetki `/runner-grant/block-3d-runner`.
- Giden mesajlar (`source: 'block-3d'`): `GAME_UPDATE`, `LEVEL_COMPLETED`, `ASSIGNMENT_RANGE_COMPLETED`, `CLOSE_ACTIVITY_MODAL`, `OPEN_BLOCK_HOMEWORK_ASSIGN` (yönetici “Ödev Ver”).
- `completedLevelIds` seviyelerin **sıra numaralarıdır** (1 tabanlı); `levels[]` özet liste (id, name, group, order, xp, completed, stars).
- `ASSIGNMENT_RANGE_COMPLETED` artık `assignmentId` olmasa da (Laravel ödev sayfası akışında) aralık bitince bir kez gönderilir ve `xp`, `elapsedSeconds` içerir.
- Seviye sırası ödev aralığını belirler: mevcut ödevler varken sırayı değiştirmeyin, yeni seviyeleri sona ekleyin.

## v2'de kaldırılanlar
Yönetici **seviye tasarım aracı** (ekle/düzenle/sil) kaldırıldı: tarayıcıya özel (`localStorage`) çalıştığı için başkalarına yansımıyordu. Eski özel seviye kayıtları açılışta temizlenir.
