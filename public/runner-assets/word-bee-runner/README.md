# Kelime Arısı — Harf Avı

Harf tablosunda arıyı **yön bloklarıyla** gezdirerek hedef kelimeyi harf harf heceleme oyunu. Blok tabanlı sıralama, tekrar (döngü) ve en az blokla çözme mantığını öğretir.

## İlham ve özgünlük notu
Bu uygulama, "bir harf ızgarasında yön komutlarıyla kelime heceletme" türündeki blok kodlama bulmacası **fikrinden/oyun mekaniğinden** esinlenmiştir (mekanikler ve fikirler telif konusu değildir). Referans alınan yapımdaki **hiçbir kod, görsel, ses, metin, harita ya da seviye verisi kullanılmamıştır**:
- Motor (`engine.js`), blok düzenleyici ve arayüz (`app.js`, `style.css`, `index.html`) sıfırdan yazılmıştır.
- Arı, bloklar ve ızgara CSS/SVG ile bu projede çizilmiştir (`../../word-bee-icon.svg` dahil); sesler WebAudio ile üretilen kısa bipledir.
- Kelime listesi ve 100 seviye, özgün bir üretici (`tools/learn-levels/word-bee.gen.mjs`) ile üretilir ve motor tarafından doğrulanır.

## Oyun kuralları
- Arı bir kareye indiğinde o karedeki harfi toplar ve "Hecelediğin kelime" alanına ekler (başladığı kare sayılmaz).
- Bloklar: **K** yukarı · **G** aşağı · **D** sağ · **B** sol (Kuzey, Güney, Doğu, Batı) ve **tekrarla (2–9 kez)** (Orta seviyeden itibaren, iç içe kullanılabilir).
- Blokları sürükleyerek ya da dokunarak ekle; tekrarla bloğunun başlığına dokunursan sonraki bloklar onun içine girer. Arı ızgaradan çıkarsa program durur.
- **Blok sınırı:** Orta/Zor/Uzman seviyelerde döngüsüz uzun çözümü engelleyen blok sınırı vardır.
- **Yıldız:** yanlış denemesiz ve par (en az) bloğa eşit/altında çözersen 3, 1 yanlış deneme ya da par+2 bloğa kadar 2, diğer durumlarda 1; ipucu kullanırsan en çok 2 (3 ipucu → 1).

## Seviyeler (100)
Kolay 25 (3–4 harf, 4×4–5×5, yalnızca yön blokları) · Orta 25 (5–7 harf, tekrarla, sınır par+0–2) · Zor 25 (6–8 harf, merdiven/zikzak döngüleri) · Uzman 25 (6–8 harf, iç içe döngüler, sınır = par).

## Platform
`/word-bee-runner`; ödev seviye aralığı (`from`/`to`), `runner-grant`, `GAME_UPDATE` / `LEVEL_COMPLETED` / `ASSIGNMENT_RANGE_COMPLETED` olayları diğer runner'larla aynı sözleşmedir. XP: Kolay 10 · Orta 15 · Zor 20 · Uzman 25.
