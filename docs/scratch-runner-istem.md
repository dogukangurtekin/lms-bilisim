# Blok Kodlama Stüdyosu (Scratch benzeri) — Geliştirme İstemi

## Amaç
Bilişim Kod "Oyunlar ve Etkinlikler" bölümüne, Scratch'in çalışma mantığıyla aynı işleyen, tarayıcıda çalışan
görsel blok kodlama oyunu ekle. Öğrenci blokları sürükleyip birleştirerek bir sahnedeki nesneleri (sprite) programlar,
projesini kaydeder, sonra tekrar açıp kaldığı yerden devam eder.

## Kurallar
- Tek başına çalışan uygulama: `public/runner-assets/scratch-runner/` (index.html, style.css, app.js). Sunucu API'si gerekmez.
- Slug: `scratch-runner`. Diğer oyunlar gibi `ActivityController::games()` listesine, `/scratch-runner` rotasına,
  herkese açık oyun listesine ve "seviyesiz" (levelless) ödev oyunları listesine eklenir.
- Scratch'in adı, logosu, kedi görseli ve dosyaları kullanılmaz; kostümler emoji ile yapılır. Arayüz Türkçedir.
- Mobil ve tablette sürükle-bırak çalışmalıdır (pointer events).

## Adım adım
1. **Yerleşim:** solda blok paleti (kategori sekmeleri), ortada kod alanı, sağda sahne (480x360) ve nesne paneli.
2. **Blok modeli:** her blok `{op, args, subs}` JSON'udur. Tür: şapka (hat), yığın (stack), C-blok (iç içe), sayı/metin
   değer bloğu (reporter), doğru/yanlış bloğu (boolean). Tüm proje JSON olarak saklanır.
3. **Sürükle-bırak:** paletten kod alanına blok çek; bloklar alt alta yapışır; C-bloğun içine girer; değer blokları
   girdi kutularına oturur; paletin üstüne bırakılan silinir; tek tıklama bloğu/komut dizisini çalıştırır; Ctrl+Z geri alır.
4. **Kategoriler:** Hareket, Görünüm, Ses (nota), Olaylar, Denetim, Algılama, İşlemler, Değişkenler, Kalem.
5. **Yorumlayıcı:** her komut dizisi ayrı iş parçacığıdır (generator). Saniyede 30 kare; her döngü turunda bir kare
   bekler, böylece hareket animasyon olarak görünür. Bayrak, tuş, nesneye tıklama ve mesaj yayınlama olayları çalışır.
6. **Sahne:** emoji kostümlü nesneler, yön/boyut/görünürlük, konuşma balonu, kalem çizimi, değişken göstergeleri,
   fare ve tuş algılama, kenarda geri dönme, nesneler arası değme.
7. **Nesneler:** ekle, sil, yeniden adlandır, kostüm ekle/değiştir, x/y/boyut/yön alanları, arka plan rengi.
8. **Kaydet ve devam et:** her değişiklikte otomatik kayıt (kullanıcıya özel `localStorage` anahtarı); sayfa açılınca son
   proje kaldığı yerden yüklenir. Adlı projeler: Kaydet, Projelerim (aç/sil), `.json` indir/yükle.
9. **Örnek proje:** kare çizen nesne ve ok tuşlarıyla giden roket; ilk kullanımda öğrenciye yol gösterir.
10. **Doğrulama:** blok sürükleme, çalıştırma, kaydet/yeniden aç, geri al ve mobil görünüm elle denenir.
