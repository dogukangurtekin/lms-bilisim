# Blok Kodlama (v2) — tasarım istemi ve teknik belge

Bu belge hem **yenileme isteminin** (neyi, neden yaptık) hem de oyunun **bakım rehberinin** özetidir.

## İstem (özet)
- Blok kodlama bölümleri **sıfırdan** düzenlensin; **50 yeni bölüm** eklensin (eski 32 + 50 = **82 bölüm**).
- Bölümler **kolaydan zora** ilerlesin; ilerledikçe **kare sayısı** (ızgara) büyüsün: **5×5 → 14×14**.
- Oyunun **görselliği** baştan yenilensin (eski görünüm çok klasikti).
- Platformla uyum bozulmasın: ödev aralıkları, XP, yetki kontrolü ve tamamlama bildirimleri aynen çalışsın.

## Bölüm planı (82 bölüm)
| Grup | Bölümler | Izgara | XP | Konular |
|---|---|---|---|---|
| Kolay | 1–24 | 5×5 → 7×7 | 5 | düz yol, ilk dönüş, zikzak, merdiven, **tekrar bloğu (13. bölümden)**, engel aşma, yılan yolu |
| Orta | 25–52 | 8×8 → 10×10 | 12 | merdivenler, yılan yolları, engel aşma, sarmal, ilk labirentler |
| Zor | 53–72 | 11×11 → 13×13 | 21 | büyük yılan yolları, sarmallar, labirentler; **“yıldıza kadar tekrarla” ve “eğer” blokları (61. bölümden, isteğe bağlı)** |
| Uzman | 73–82 | 13×13 → 14×14 | 30 | büyük labirentler, 82. bölüm finali |

Temalar (görsel dünya): Orman (1–12), Çöl (13–24), Buz (25–36), Uzay (37–52), Lav (53–66), Kozmik (67–82).

**Zorluk kuralları:** Izgara boyutu hiçbir bölümde azalmaz. Labirent uzunluğu 12 adımdan ~37 adıma kademeli artar.
13. bölümden itibaren bazı bölümlerde **blok sınırı** vardır (yalnızca “tekrar” bloğu kullanılınca sığar).
**Yıldız:** en iyi çözümden (par) az/eşit blokla 3★, +%40 içinde 2★, aksi halde 1★. 12. bölüme kadar “tekrar” bloğu olmadığı için par = düz çözümdür.

## Dosyalar
| Dosya | Görev |
|---|---|
| `index.html`, `style.css` | Arayüz (açık/koyu tema, mobil uyumlu), bölüm seçici, sonuç penceresi |
| `app.js` | Oyun mantığı, Blockly editörü, ilerleme, platform mesajları |
| `render.js` | Tuval çizimi: temalı zemin, engeller, parlayan yıldız, animasyonlu robot, parçacıklar |
| `engine.js` | DOM’dan bağımsız yürütme motoru (ileri/dön/tekrar/yıldıza kadar/eğer) — Node’da da test edilir |
| `levels.js` | **Otomatik üretilmiş** 82 bölüm — elle düzenlemeyin |
| `../../js/vendor/blockly/` | Blockly 10.4.3 (kendi sunucumuzdan; CDN’e bağlı değil) |
| `../../../tools/block-levels/generate.mjs` | Bölüm üretici + doğrulayıcı |

## Bölümleri yeniden üretmek / değiştirmek
```bash
node tools/block-levels/generate.mjs      # levels.js dosyasını üretir ve her bölümü doğrular
```
`generate.mjs` içindeki **plan** (dosyanın ortasındaki `add(...)` listesi) bölümleri tanımlar: tür (`straight`, `L`, `S`, `U`, `staircase`,
`detour`, `serpentine`, `spiral`, `maze`), ızgara boyutu, parametreler, `loops` (blok sınırı), `density` (dekoratif engel yoğunluğu).
Üretici sabit tohum kullanır: aynı plan her zaman aynı bölümleri verir. Her bölüm için çözülebilirlik (BFS), en kısa çözüm ve
`par` hesaplanır; çözülemeyen bölüm üretilmez. **Bölüm sırası ödevlerdeki “seviye aralığını” belirlediğinden, mevcut ödevler varken
sırayı değiştirmeyin; yeni bölümleri sona ekleyin.**

## Platform sözleşmesi (eski sürümle uyumlu)
- URL: `?role=teacher|admin`, `from/to` (veya `levelStart/levelEnd`), `assignmentId`, `grant=1`. Aralık parametresi yoksa serbest oyundur.
- Öğrenci erişimi `/runner-grant/block-grid-runner` ile doğrulanır (ödev aralığı dışına çıkılamaz, sıralı kilit).
- Giden mesajlar: `LEVEL_COMPLETED`, `ASSIGNMENT_RANGE_COMPLETED`, `LOCKED_LEVEL_WARNING`, `GAME_UPDATE`.
- Gelen mesajlar: `SET_LEVEL_RANGE`, `SET_ASSIGNMENT_PROGRESS`, `FORCE_ASSIGNMENT_LOCK`, `SET_CURRENT_LEVEL`, `RUN`, `STEP`, `RESET`,
  `LOAD_STATE` (yalnız bu sürümün ilerleme verisi), `REQUEST_SAVE`, `RESET_LOCAL`.
- İlerleme `localStorage` anahtarı: `bgr.v2` (eski `levels`/`unlocked` anahtarları açılışta temizlenir).

## v2’de kaldırılanlar
Yönetici **bölüm tasarım aracı** (seviye ekle/sil/taşı) kaldırıldı: tarayıcıya özel (`localStorage`) çalıştığı için başka kullanıcılara
yansımıyor ve kayıtlı bölümleri bozabiliyordu. Bölümler artık kodla (yukarıdaki üretici) yönetilir.
