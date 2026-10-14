# 3D Blok Kodlama — 200 seviye

2B Blok Kodlama ile aynı plan, izometrik 3 boyutlu görünümle: **200 seviye**, kolaydan zora; ızgara **5×5 → 14×14**.

| Grup | Seviyeler | Izgara | XP/seviye | Tema |
| --- | --- | --- | --- | --- |
| Kolay | 1–60 | 5×5 → 8×8 | 5 | Çayır (1–30), Kumsal (31–60) |
| Orta | 61–130 | 8×8 → 11×11 | 12 | Kar (61–100), Alacakaranlık (101–130) |
| Zor | 131–180 | 11×11 → 13×13 | 21 | Yanardağ |
| Uzman | 181–200 | 13×13 → 14×14 | 30 | Kozmik |

## Dosyalar
- `levels.js` — **otomatik üretilmiş** 200 seviye; `node tools/block-levels/generate3d.mjs` ile yeniden üretilir (elle düzenlemeyin).
- `engine.js`, `render3d.js`, `editor.js`, `app.js` — motor, izometrik çizim, sürükle-bırak blok editörü, arayüz.

Plan `tools/block-levels/plan200.mjs` içindedir. Sıra değiştiğinde `LEVEL_SET_VERSION` artırılır ve yerel ilerleme sıfırlanır.
