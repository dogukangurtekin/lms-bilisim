# Sayı Arısı — Rakam Avı

Rakam tablosunda arıyı **yön bloklarıyla** gezdirerek hedef sayıyı rakam rakam oluşturma oyunu. [Kelime Arısı](../word-bee-runner/README.md) ile aynı mekanik; harfler yerine rakamlar kullanılır. Sıralama, tekrar (döngü) ve en az blokla çözme mantığını öğretir.

## Özgünlük notu
Motor, arayüz, arı çizimi ve seviyeler bu projede sıfırdan üretilmiştir; harici oyundan kod, görsel, ses ya da seviye verisi alınmamıştır. Seviyeler `tools/learn-levels/number-bee.gen.mjs` ile üretilir ve motor tarafından çözümleriyle doğrulanır (`node tools/learn-levels/number-bee.gen.mjs`).

## Kurallar
- Arı bir kareye indiğinde o karedeki rakamı toplar ve "Oluşturduğun sayı" alanına ekler (başladığı kare sayılmaz).
- Bloklar: **K** yukarı · **G** aşağı · **D** sağ · **B** sol ve **tekrarla (2–9 kez)**. Arı ızgaradan çıkarsa program durur.
- Blok sınırı: Orta/Zor/Uzman seviyelerde döngüsüz uzun çözümü engeller.
- Yıldız: yanlış denemesiz ve en az bloğa eşit/altında 3; ipucu kullanılırsa en çok 2.

## Seviyeler (100)
Kolay 25 (3–4 rakam) · Orta 25 (5–7, tekrarla) · Zor 25 (6–8, merdiven döngüleri) · Uzman 25 (6–8, iç içe döngüler). XP: 10 / 15 / 20 / 25.

## Platform
`/number-bee-runner`; ödev aralığı, `runner-grant`, `GAME_UPDATE` / `LEVEL_COMPLETED` / `ASSIGNMENT_RANGE_COMPLETED` olayları diğer runner'larla aynıdır.
