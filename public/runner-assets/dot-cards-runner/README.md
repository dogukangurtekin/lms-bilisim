# Küçük Nokta — Kod Kartları

Kart tabanlı kodlama bulmacası. Nokta doğrudan yönetilmez; oyuncu her turda elindeki **kartlardan birini** seçer, kartın üzerinde yazan küçük program noktayı çalıştırır. Amaç noktayı turuncu hedefe ulaştırmaktır.

## İlham ve özgünlük notu
Bu uygulama, "kartlardaki kodu okuyup noktanın nereye gideceğini tahmin etme" türündeki kart tabanlı bulmaca **fikrinden/oyun mekaniğinden** esinlenmiştir (mekanikler ve fikirler telif konusu değildir). Referans sitedeki **hiçbir kod, görsel, ses, metin, harita ya da seviye verisi kullanılmamıştır**:
- Komut dili (Türkçe: `ileri`, `dön_sağ`, `tekrarla`, `eğer`, `iken`, …), kart biçimi, kural seti ve çalıştırma motoru (`engine.js`) sıfırdan yazılmıştır.
- Tüm görseller CSS/SVG ile bu projede çizilmiştir (`style.css`, `../../dot-cards-icon.svg`); sesler WebAudio ile üretilen kısa bipledir.
- 48 seviye, özgün bir üretici (`tools/learn-levels/dot-cards.gen.mjs`) ile rastgele ama doğrulanarak üretilir.

## Oyun kuralları
- **Kart Dizisi:** Kartları doğru sırayla seç. Kart noktayı duvara çarptırır ya da çukura düşürürse nokta önceki yerine döner ve kart geri gelir. Başarılı kart harcanır; kartlar bitince hedefe varılmadıysa seviye baştan başlar.
- **Doğru Kartı Bul:** Yalnızca bir kart hedefe götürür; kodları okuyarak bulunur.
- **Serbest Gezinti:** Ok tuşları/düğmelerle nokta doğrudan yönetilir.
- Yıldız: hatasız 3, ≤2 hata 2, fazlası 1; ipucu kullanılırsa en çok 2 yıldız.

## Seviyeler (48)
Kolay 12 (gezinti, mutlak yönlü kartlar) · Orta 12 (göreli hareket, `tekrarla`) · Zor 12 (`eğer`/`değilse`, `iken`) · Uzman 12 (iç içe yapılar, uzun diziler). Üretici her seviyeyi çözücüyle doğrular (çözülebilirlik, "Doğru Kartı Bul"da tek doğru kart, tasarlanandan kısa yol olmaması).

## Platform
`/dot-cards-runner`; ödev seviye aralığı (`from`/`to`), `runner-grant`, `GAME_UPDATE` / `LEVEL_COMPLETED` / `ASSIGNMENT_RANGE_COMPLETED` olayları diğer runner'larla aynı sözleşmedir. XP: Kolay 10 · Orta 15 · Zor 20 · Uzman 25.
