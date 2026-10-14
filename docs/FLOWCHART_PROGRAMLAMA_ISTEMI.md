# Flowchart Programlama — Ürün ve Üretim İstemi (v2)

> Bu belge, eski "Flowchart Programming" (Vue + API tabanlı) uygulamasının yerine sıfırdan yazılacak **Flowchart Programlama** uygulamasının tam istemidir.
> Üretim bu istem birebir izlenerek yapılır; kabul ölçütleri (bölüm 12) sağlanmadan iş bitmiş sayılmaz.

## 1. Amaç
İlkokul/ortaokul ve lise başlangıç düzeyindeki öğrencilerin **algoritmayı akış şemasıyla** kurmayı, şemayı **adım adım çalıştırıp izlemeyi** ve görevleri **otomatik doğrulanarak** çözmeyi öğrendiği, öğretmenin **ödev olarak verebildiği** (seviye aralığıyla) bir etkinlik.
Ölçüt: Hiç bilmeyen bir öğrenci **ilk görevini 2 dakika içinde, kimseye sormadan** tamamlayabilmeli.

## 2. Temel ilkeler
1. **Kolaylık önce:** Sürükle-bırak, fare/dokunmatik/klavye; kurulum yok, hesap yok, ayrı sunucu çağrısı yok. Her şey tarayıcıda çalışır.
2. **Hata affedici:** Geri al / yinele sınırsız; silme geri alınabilir; her hata mesajı Türkçe, nedeni ve çözümü söyler ("Karar bloğunun *Hayır* çıkışı bağlı değil").
3. **Görünür yürütme:** Çalışırken aktif blok parlar, akan çizgi gösterilir, değişkenler tablosu ve çıktı anında güncellenir.
4. **Tek doğru kaynak:** Şema = veri. Çalıştırma, doğrulama, görev kontrolü aynı motoru kullanır (saf JS, DOM'dan bağımsız → test edilebilir).
5. **Ödeve uygun:** Mevcut oyun/ödev altyapısıyla (grant, `from`/`to`, `postMessage`) birebir uyumlu bir "runner" olarak çalışır.
6. **Güvenli:** `eval`/`Function` yok. Kullanıcı ifadeleri kendi ayrıştırıcımızla değerlendirilir. Sonsuz döngü adım sınırıyla durdurulur.

## 3. Kullanıcılar ve modlar
| Kullanıcı | Deneyim |
| --- | --- |
| Öğrenci (ödevli) | Yalnızca atanan görev aralığı (`from`–`to`) açık; görevler sırayla açılır. |
| Öğrenci (ödevsiz) | İlk 3 görev + Serbest Çalışma. |
| Öğretmen / Yönetici | Tüm görevler açık, Serbest Çalışma. (Çözüm gösterme ilk sürümde yok; referans çözümler yalnızca doğrulama için üretici araçta tutulur, istemciye gönderilmez.) |

**Serbest Çalışma:** Görevsiz tuval. Çalıştırınca girdi sorulur. Dışa aktar (JSON) / içe aktar / yazdır. XP yok.

## 4. Bloklar (6 tür)
| Blok | Şekil | Alan | Davranış |
| --- | --- | --- | --- |
| **Başla** | Hap (yeşil) | — | Tek çıkış. Şemada tam 1 tane. |
| **Bitir** | Hap (kırmızı) | — | Çıkışı yok. En az 1 tane. |
| **Girdi** | Paralelkenar (mavi) | `a, b` değişken adları | Kullanıcıdan/test girdisinden sırayla okur. |
| **Çıktı** | Paralelkenar (turuncu) | `"Toplam:", a + b` | Virgülle ayrılmış ifadeleri aralarına boşluk koyup tek satır yazar. |
| **İşlem** | Dikdörtgen (mor) | `x = x + 1; y = 0` | Bir ya da daha çok atama (`;` ile ayrılır). |
| **Karar** | Eşkenar dörtgen (sarı) | `a > b` | İki çıkış: **Evet** (yeşil) ve **Hayır** (kırmızı). Döngü, Evet/Hayır oklarını geriye bağlayarak kurulur. |

Blok boyutu sabit (okunaklı), uzun metin blok içinde kısaltılır, tamamı üzerine gelince/seçilince görünür.

## 5. İfade dili (Türkçe dostu, güvenli)
- **Sayı:** `12`, `3.5`. **Metin:** `"merhaba"` veya `'merhaba'`. **Mantık:** `doğru`, `yanlış` (`true/false` de kabul).
- **Değişken:** harf/alt çizgi ile başlar; Türkçe harfler serbest (`toplam`, `sayı1`, `öğrenci_adı`).
- **Aritmetik:** `+ - * / % **` ve `^` (üs). Parantez serbest. `/` her zaman ondalık böler; tam bölme için `taban(a / b)`.
- **Karşılaştırma:** `== != < > <= >=` (tek `=` yalnızca **İşlem** bloğunda atamadır; Karar bloğunda `=` yazılırsa "Eşitlik için == kullan" uyarısı).
- **Mantıksal:** `ve`, `veya`, `değil` (ayrıca `and or not && || !`).
- **Metin birleştirme:** `+` (metin + sayı → metin).
- **Fonksiyonlar:** `karekök`, `mutlak`, `yuvarla`, `taban`, `tavan`, `enküçük`, `enbüyük`, `uzunluk`, `sayı`, `metin`.
- **Çıktı biçimi:** tamsayılar `5`, ondalıklar en fazla 12 anlamlı basamak (`0.333333333333`), mantık `doğru/yanlış`.
- **Hatalar (örnekler):** `'x' değişkenine henüz değer verilmedi`, `Sıfıra bölme`, `Metin ile çarpma yapılamaz`, `Beklenmeyen ")" işareti`.

## 6. Düzenleyici (kolaylık özellikleri)
1. **Palet:** 6 büyük düğme. Tıklayınca blok **seçili bloğun ardına otomatik eklenip bağlanır**; Karar bloğu seçiliyken sırayla önce *Evet*, sonra *Hayır* çıkışına eklenir. Seçili blok yoksa tuvalin boş bir yerine konur.
2. **Sürükle:** Blok taşıma (ızgaraya yapışma), çıkış noktasından (halka) başka bloğa **sürükleyerek bağlama**, bağlantıya tıklayıp **Delete** ile silme, bağlantının ucunu başka bloğa taşıma.
3. **Metin düzenleme:** Seçili blok için tuvalin üstünde tek satırlık düzenleme çubuğu (otomatik odak, Enter ile kapanır). Blok türüne göre örnek/ipucu yazısı.
4. **Geri al / Yinele**, **Sil**, **Kopyala (Ctrl+D)**, **Otomatik düzenle** (şemayı yukarıdan aşağı hizalar), **Yaklaş/Uzaklaş/Sığdır**, kaydırma (boş alanı sürükle), tekerlek ile yakınlaştırma, iki parmakla dokunmatik yakınlaştırma.
5. **Klavye:** `Delete` sil, `Ctrl+Z/Y` geri/yinele, `Ctrl+D` çoğalt, oklar ile seçili bloğu kaydır, `Enter` düzenle, `Esc` seçimi kaldır.
6. **Canlı doğrulama:** Eksik/yanlış yerleri bloğun üstünde kırmızı/sarı rozetle işaretler; alt çubukta madde madde listeler.
7. **Bağlantı çizimi:** Dik açılı (ortogonal) yumuşak çizgiler; geri dönen bağlantılar yandan dolanır; *Evet/Hayır* etiketleri renkli.

## 7. Çalıştırma
- **Çalıştır** (otomatik adımlama, hız 0.25×–4×), **Adım** (tek adım), **Duraklat/Devam**, **Sıfırla**.
- Çalışırken: aktif blok parlar, geçilen bağlantı vurgulanır; **Değişkenler** tablosu (son değişen satır vurgulu) ve **Çıktı** konsolu canlı güncellenir.
- **Girdi bloğu** manuel çalıştırmada konsolun altında bir giriş kutusu açar ("`a` değerini gir" — Enter); görevlerde ve "Kontrol Et"te test girdileri otomatik verilir.
- **Güvenlik:** En çok 5000 adım; aşılırsa "Sonsuz döngü olabilir" uyarısı ve durdurma. Hata anında hatalı blok kırmızı işaretlenir.
- **Ön koşul:** Çalıştırmadan önce yapısal doğrulama; hata varsa çalışmaz, nedenini söyler.

## 8. Yapısal doğrulama kuralları
- Tam 1 **Başla**, en az 1 **Bitir**. Başla'nın gelen bağlantısı yok.
- Başla/Girdi/Çıktı/İşlem: tam 1 çıkış. Karar: tam 2 (Evet, Hayır). Bitir: çıkış yok.
- Boş metinli (Girdi/Çıktı/İşlem/Karar) blok → hata. Ayrıştırılamayan ifade → hata + konum.
- Başla'dan ulaşılamayan blok → uyarı. Hiçbir Bitir'e ulaşamayan yol → uyarı ("bu yol bitmiyor").
- Kendine bağlantı, aynı çıkıştan birden çok bağlantı engellenir.

## 9. Görev (seviye) sistemi
- **≥ 40 görev**, 4 grup: **Kolay (12) · Orta (12) · Zor (10) · Uzman (6)** — seviye aralığıyla ödev verilebilsin diye sıralı ve sabit numaralı.
- Her görev: başlık, öykü/açıklama, **örnek giriş/çıkış**, **gizli ek testler**, ipuçları (3 kademe, kullanıldıkça XP düşmez), isteğe bağlı **yapı koşulu** (ör. "Karar bloğu kullan", "Döngü kur", "en çok 8 blok") ve bazılarında **hazır başlangıç şeması**.
- **Kontrol Et:** Şema tüm testlerde çalıştırılır; çıktılar karşılaştırılır (sayısal toleranslı, satır sonu/boşluk toleranslı). Sonuç: hangi test geçti/kaldı, kalan için *girdi · beklenen · senin çıktın*. Gizli testlerde yalnızca "gizli test başarısız" denir.
- Tüm testler + yapı koşulu + geçerli yapı → **Görev tamamlandı** (konfeti, XP, "Sonraki görev").
- Müfredat (özet): çıktı yazma → girdi/işlem → karar (iki yollu) → iç içe karar → sayaçlı döngü → toplam/çarpım biriktirme → döngü + karar → klasik algoritmalar (asal, OBEB, faktöriyel, Fibonacci, basamak toplamı, FizzBuzz, artık yıl vb.).
- Her görev için **referans çözüm** (üretim sırasında) motorla doğrulanır; çözümsüz/çelişkili görev yayınlanmaz.

## 10. Ödev / platform entegrasyonu (mevcut runner sözleşmesi)
- Slug: `flowchart-runner`, adres `/flowchart-runner`; `ActivityRunnerController::serveRunner` ile `public/runner-assets/flowchart-runner/` servis edilir.
- `ActivityController::games()` listesine eklenir → öğretmen/yönetici **oyun ödevi** olarak seçebilir; seviye aralığı = görev numaraları.
- Öğrenci erişimi `/runner-grant/flowchart-runner` ile doğrulanır (ödev aralığı `from`/`to`, `assignmentId`, `enforceGrant`).
- `window.parent.postMessage` olayları (diğer runner'larla aynı biçim): `GAME_UPDATE` (görev listesi + tamamlananlar + yüzde), `LEVEL_COMPLETED` (levelNo, levelId, xp, süre, tamamlananlar), `ASSIGNMENT_RANGE_COMPLETED`, `REQUEST_CLOSE_ACTIVITY`.
- XP: Kolay 10 · Orta 15 · Zor 20 · Uzman 25 (görev başına, yalnızca ilk geçişte).
- Hafıza: Şema taslakları ve ilerleme `localStorage` (görev başına taslak); ödev ilerlemesi sunucuya `postMessage` ile bildirilir.

## 11. Arayüz ve erişilebilirlik
- Yeni platform teması: lacivert üst şerit, cam kartlar, kabartmalı düğmeler (diğer yenilenen oyunlarla aynı dil).
- Yerleşim: üst şerit (görev seçici ☰, önceki/sonraki, XP, Çalıştır/Adım/Sıfırla/Kontrol Et), **sol** palet + araçlar, **orta** tuval (büyük), **sağ** sekmeler: *Görev · Değişkenler · Çıktı*, **alt** doğrulama çubuğu. Dar ekranda panel alta iner.
- Görev seçici: **Kolay / Orta / Zor / Uzman** başlıklı kutucuk ızgarası; kilit, tamamlandı, mevcut görev işaretleri.
- Klavyeyle kullanılabilir, odak halkası görünür, renk dışında şekil/etiketle ayrım (renk körlüğü), `prefers-reduced-motion` desteği, dokunmatik hedefler ≥ 44 px.

## 12. Kabul ölçütleri (üretim bitiş kontrol listesi)
1. Motor testleri (Node): ifade ayrıştırıcı/değerlendirici, yürütme, doğrulama; **her görevin referans çözümü tüm testleri geçer**, boş şema geçmez.
2. Yeni öğrenci ilk görevi (Başla → Çıktı → Bitir) palet tıklamalarıyla ve fare kullanmadan bile kurabilir.
3. Sonsuz döngü 5000 adımda durur; sıfıra bölme, tanımsız değişken Türkçe hata verir.
4. Ödev modu: `from`/`to` dışındaki görevler kilitli; tamamlanınca `LEVEL_COMPLETED` ve aralık bitince `ASSIGNMENT_RANGE_COMPLETED` gönderilir.
5. Eski Vue/API uygulaması, rotaları, modelleri, servisleri ve derleme çıktıları **kaldırılmış**; `/flowchart-programming` yeni uygulamaya yönlendirir.
6. Etkinlikler listesinde ve ödev seçiminde "Flowchart Programlama" görünür.
7. Mobil/dokunmatik: blok ekleme, taşıma, bağlama, çalıştırma çalışır.

## 13. Kapsam dışı
Alt programlar/fonksiyonlar, dizi/liste veri türü, gerçek zamanlı çoklu kullanıcı, sunucuda şema saklama (ilk sürümde yerel + dışa aktarım).
