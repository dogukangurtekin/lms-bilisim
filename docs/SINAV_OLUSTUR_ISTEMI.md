# Sınav Oluştur Modülü — İstem ve Yol Haritası

Bu belge, "Sınav Oluştur" modülünün **ne olacağını** ve **hangi adımlarla** geliştirileceğini tanımlar.
Her adım ayrı teslim edilir, test edilir ve onaylandıktan sonra bir sonrakine geçilir.

## 1. Amaç

Admin (ve admin izin verirse öğretmenler), hazır bir şablon üzerinden profesyonel görünümlü sınav
kâğıdı hazırlar; soruları elle ekler ya da **soru havuzundan** seçip kâğıda ekler; sayfayı
**tek sütun / iki sütun** düzeninde gösterir; çıktıyı **PDF** ve **Word (.docx)** olarak alır
ve sınavı **kaydeder** (sonradan açıp düzenleyebilir, kopyalayabilir).

## 2. Erişim kuralları

- Sol menüde **"Sınav Oluştur"** bağlantısı vardır; öğrenciler asla görmez.
- Admin her zaman erişir.
- Öğretmen yalnızca admin, **Profilim → "Öğretmenlere Sınav Oluştur"** ayarını açtıysa görür/kullanır
  (varsayılan: KAPALI). Ayar kapanınca menü gizlenir, adrese gidilirse 403 verilir.
- Öğretmen yalnızca **kendi** sınavlarını ve **kendi + paylaşılan** havuz sorularını görür; admin hepsini görür.

## 3. Veri modeli

| Tablo | Önemli alanlar |
|---|---|
| `exams` | `user_id` (sahip), `title`, `subject`, `grade_level`, `class_label`, `term` (dönem), `exam_date`, `duration_min`, `school_name`, `template` (şablon anahtarı), `columns` (1\|2), `settings` (JSON: yazı tipi/boyutu, kenar boşlukları, başlık alanları, puan gösterimi, cevap anahtarı, filigran), `status` (draft\|final) |
| `exam_questions` | `exam_id`, `sort_order`, `type`, `body`, `options` (JSON), `answer`, `points`, `image_path`, `section_title`, `bank_item_id` (havuzdan geldiyse), `answer_lines` |
| `question_bank_items` | `owner_id`, `subject`, `grade_level`, `unit`, `difficulty` (kolay\|orta\|zor), `type`, `body`, `options` (JSON), `answer`, `image_path`, `tags`, `is_shared` |

Soru tipleri: `multiple_choice` (A–E), `true_false`, `fill_blank`, `open_ended` (satır sayısı belirlenir),
`matching` (eşleştirme).

## 4. Sayfa ve özellikler

1. **Sınav listesi:** sınavlarım, durum, son düzenleme, kopyala / sil, yeni sınav.
2. **Yeni sınav:** başlık, ders, sınıf düzeyi, dönem, tarih, süre, okul adı, **şablon seçimi**, sütun düzeni.
3. **Editör (üç bölme):** solda soru havuzu (arama + ders/düzey/zorluk filtresi, "Sınava ekle"),
   ortada **A4 canlı önizleme** (1 veya 2 sütun, sayfa taşmasında otomatik yeni sayfa), sağda sınav ve soru ayarları.
4. **Soru ekleme:** elle yazma (tip seçerek), görsel yükleme, puan, soruyu havuza kaydetme; sürükle-bırak sıralama,
   çoğalt, sil; bölüm başlığı (ör. "A. Çoktan Seçmeli").
5. **Şablonlar:** Klasik Yazılı, Modern Renkli, Sade/Test (yalnız çoktan seçmeli), Quiz (kısa), Okul Logolu.
   Şablon = başlık bloğu + tipografi + çerçeve/renk teması.
6. **Çıktı:** PDF (A4, sayfa numarası, başlık tekrarı, 1/2 sütun), Word `.docx` (düzenlenebilir, 1/2 sütun),
   isteğe bağlı **cevap anahtarı** sayfası ve **öğrenci kâğıdı / öğretmen kâğıdı** ayrımı.
7. **Kaydet:** taslak otomatik kaydedilir; "Kesinleştir" ile durum `final`.
8. **Yönetim ayarı:** Profilim sayfasında öğretmenlere aç/kapat anahtarı.

## 5. Kalite ölçütleri

- Türkçe karakterler PDF ve Word'de doğru görünür (DejaVu / gömülü yazı tipi).
- Soru bloğu sütun ya da sayfa sonunda **bölünmez** (page-break-inside: avoid).
- Çıktıdaki sayfa düzeni, ekrandaki önizlemeyle aynıdır.
- Tüm sunucu işlemleri yetki kontrollüdür; öğretmen başkasının sınavına erişemez.
- Yüklenen görseller güvenli doğrulanır (tür/boyut), yolu doğrudan kullanıcıdan alınmaz.

## 6. Adımlar

| Adım | İçerik | Durum |
|---|---|---|
| 1 | İstem + erişim (menü, Profilim açma/kapama, yetki) + veritabanı + sınav listesi/yeni sınav oluşturma | Tamam |
| 2 | Editör iskeleti: A4 önizleme, soru ekleme/silme/sıralama, tek/çift sütun, kaydetme | Tamam |
| 3 | Soru havuzu: havuz yönetimi, filtre, havuzdan sınava ekleme, soruyu havuza kaydetme | Tamam |
| 4 | Şablonlar ve tasarım ayarları (başlık, tipografi, tema, logo) + havuz soruları için Excel dışa/içe aktarma | **Bu teslim** |
| 5 | PDF çıktısı (+ cevap anahtarı) | |
| 6 | Word (.docx) çıktısı | |
| 7 | Cilalama: kopyalama, görsel yükleme, sürükle-bırak, yazdırma, hata/sınır durumları | |
