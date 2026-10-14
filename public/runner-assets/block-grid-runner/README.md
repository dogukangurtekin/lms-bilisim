# 2B Blok Kodlama — 200 bölüm

Bölümler **kolaydan zora** doğru, gruplar arka arkaya gelir. Izgara büyüklüğü **5×5 → 14×14** olup bölüm ilerledikçe azalmaz.

| Grup | Bölümler | Izgara | XP/bölüm | Tema |
| --- | --- | --- | --- | --- |
| Kolay | 1–60 | 5×5 → 8×8 | 5 | Orman (1–30), Çöl (31–60) |
| Orta | 61–130 | 8×8 → 11×11 | 12 | Buz (61–95), Uzay (96–130) |
| Zor | 131–180 | 11×11 → 13×13 | 21 | Lav |
| Uzman | 181–200 | 13×13 → 14×14 | 30 | Kozmik, büyük labirentler |

- Tekrar bloğu 13. bölümden, “yıldıza kadar tekrarla / eğer” blokları 131. bölümden itibaren açılır.
- Her bölümün en kısa (par) çözümü BFS + tekrar sıkıştırma ile hesaplanır; yıldız puanı buna göre verilir.
- Yılan yolu ve sarmal bölümlerinde kutunun çevresine duvar çekilir (kestirme olmaz).

## Dosyalar
| Dosya | Açıklama |
| --- | --- |
| `levels.js` | **Otomatik üretilmiş** 200 bölüm — elle düzenlemeyin |
| `engine.js`, `render.js`, `editor.js`, `app.js` | Oyun motoru, çizim, sürükle-bırak blok editörü, arayüz |

Yeniden üretmek için: `node tools/block-levels/generate.mjs` (plan: `tools/block-levels/plan200.mjs`).
Bölüm sırası ödev aralıklarını (1 tabanlı) belirler; sıralama değiştiğinde `LEVEL_SET_VERSION` artırılır ve yerel ilerleme sıfırlanır.
