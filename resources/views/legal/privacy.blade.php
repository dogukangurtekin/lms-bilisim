@extends('layouts.public', ['title' => 'Gizlilik Politikası ve KVKK Aydınlatma Metni', 'description' => 'Bilişim Kod platformunda kişisel verilerin hangi amaçlarla işlendiği, kimlerle paylaşıldığı, ne kadar süre saklandığı ve haklarınız.', 'canonical' => route('legal.privacy')])

@section('content')
<h1>Gizlilik Politikası ve KVKK Aydınlatma Metni</h1>
<p class="meta">Son güncelleme: 18 Ekim 2026</p>

<p>Bu metin, 6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) uyarınca bilisimkod.com (“Platform”) kullanıcılarının kişisel verilerinin işlenme süreçleri hakkında bilgilendirilmesi amacıyla hazırlanmıştır.</p>

<h2>1. Veri sorumlusu ve veri işleyen sıfatları</h2>
<ul>
    <li><strong>Okul veri sorumlusudur:</strong> Okul tarafından Platform’a tanımlanan öğrenci, öğretmen, yönetici ve veli bilgilerinin hangi amaçla ve hangi hukuki sebeple işleneceğine karar veren veri sorumlusu, hizmeti satın alan ilgili okul yönetimidir.</li>
    <li><strong>Bilişim Kod veri işleyendir:</strong> Platform Yönetimi, bu verileri yalnızca okul yönetiminin talimatları doğrultusunda, sistemin çalışabilmesi ve eğitim süreçlerinin yürütülebilmesi için barındıran ve işleyen veri işleyendir.</li>
    <li><strong>Doğrudan ilettiğiniz bilgilerde Bilişim Kod veri sorumlusudur:</strong> Sitedeki “Demo Talep Et” formu veya e-posta aracılığıyla doğrudan Platform Yönetimi’ne ilettiğiniz bilgilerin veri sorumlusu Bilişim Kod’dur. İletişim: {{ config('app.contact_email') }}</li>
</ul>

<h2>2. İşlenen kişisel veriler ve amaçlar</h2>
<div class="table-wrap">
<table>
    <tr><th>Veri</th><th>Kimden</th><th>Amaç</th></tr>
    <tr><td>Ad, soyad, e-posta, mesaj içeriği</td><td>“Demo Talep Et” formunu dolduran kişi</td><td>Talebe dönüş yapmak, iletişime geçmek ve bilgilendirme yapmak</td></tr>
    <tr><td>Ad soyad, kullanıcı adı veya e-posta, rol (yönetici/öğretmen/öğrenci), parola (parola veritabanında geri döndürülemez biçimde saklanır)</td><td>Okul yönetimi (hesabı tanımlayan)</td><td>Rol bazlı giriş panellerinin oluşturulması, kimlik doğrulama ve sistem güvenliği</td></tr>
    <tr><td>Öğrenci numarası, sınıf/şube, okul bilgisi, avatar seçimi</td><td>Okul yönetimi / öğrenci</td><td>Öğrencinin doğru sınıfta ve doğru öğretmenle eşleştirilmesi</td></tr>
    <tr><td><strong>Öğrenci giriş bilgisi kopyası</strong> (kullanıcı adı ve parola; parola geri çözülebilir biçimde şifreli saklanır)</td><td>Okul yönetimi</td><td>Yetkili yönetici ve öğretmenin (öğretmenin yalnızca kendi sınıfındaki öğrenciler için) giriş bilgilerini öğrenciye iletebilmesi ve giriş kartı çıktısı alabilmesi</td></tr>
    <tr><td>Tamamlanan dersler, ödevler, XP, rozetler, puanlar, canlı quiz ve yarışma sonuçları, oyun ve etkinlik ilerlemesi</td><td>Öğrenci (kullanım sırasında oluşur)</td><td>Öğretmene ve okul yönetimine eğitim ilerleme raporlarının sunulması</td></tr>
    <tr><td>Öğrencinin öğretmen/yöneticiye yazdığı mesajlar ve cevapları; sınıf panosu gönderileri</td><td>Öğrenci, öğretmen, yönetici</td><td>Eğitim süreci içinde iletişimin sağlanması</td></tr>
    <tr><td>Veli adı (girilmişse) ve veli telefon numarası</td><td>Okul yönetimi</td><td>Okul etkinleştirdiği takdirde öğrencinin gelişiminin veliye bildirilmesi</td></tr>
    <tr><td>Doğum tarihi ve adres (isteğe bağlı alanlar; okul doldurmazsa işlenmez)</td><td>Okul yönetimi</td><td>Yalnızca okulun öğrenci kaydı ihtiyacı doğrultusunda</td></tr>
    <tr><td>Yoklama ve not bilgileri (okul bu modülleri kullanırsa)</td><td>Öğretmen</td><td>Öğrenci devam ve başarı takibi</td></tr>
    <tr><td>Öğretmenlerin oluşturduğu ders, slayt, soru, sınav kâğıdı ve ödev içerikleri</td><td>Öğretmen</td><td>Eğitim içeriğinin sunulması</td></tr>
    <tr><td>IP adresi, tarayıcı/cihaz bilgisi, son giriş ve son görülme zamanı, aktif kalma süresi</td><td>Tüm kullanıcılar</td><td>Güvenlik, oturum yönetimi, canlı takip ve hata ayıklama</td></tr>
    <tr><td>Öğrencilerin ziyaret ettiği sayfa ve yaptığı işlem kayıtları (IP adresiyle birlikte)</td><td>Öğrenci</td><td>Güvenlik ve öğretmene etkinlik takibi sunulması; <strong>10 gün sonra otomatik silinir</strong></td></tr>
    <tr><td>Tarayıcı bildirim aboneliği (bildirim adresi, şifreleme anahtarları, tarayıcı bilgisi)</td><td>Kullanıcı (bildirimlere izin verirse)</td><td>Tarayıcı bildirimleri göndermek</td></tr>
</table>
</div>
<p>Cihazınızda saklanan çerez ve tarayıcı depolama verileri için <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>’na bakın.</p>

<h2>3. Hukuki sebepler; velilerin açık rızası ve okulun taahhüdü</h2>
<p>Veriler; sözleşmenin ifası, hukuki yükümlülüklerin yerine getirilmesi, meşru menfaat ve gerektiğinde açık rıza hukuki sebeplerine dayanılarak işlenir. Platform’dan yararlanan ilkokul, ortaokul ve lise düzeyindeki öğrencilerin tamamı reşit değildir. KVKK uyarınca çocukların verilerinin işlenebilmesi için yasal temsilcilerinin (veli/vasi) açık rızasının alınması gereken hâllerde bu rızanın alınması zorunludur.</p>
<ul>
    <li><strong>Okulun sorumluluğu:</strong> Sisteme öğrenci tanımlayan her okul, ilgili öğrencilerin velilerinden KVKK açık rıza onaylarını aldığını ve bu rızaların hukuken geçerli olduğunu Platform Yönetimi’ne karşı taahhüt eder. Okul tarafından bu izinler alınmadan sisteme veri girişi yapılmamalıdır.</li>
</ul>

<h2>4. Kişisel verilerin aktarılması</h2>
<p>Verileriniz reklam, pazarlama veya ticari amaçla üçüncü kişilere, reklam şirketlerine ya da iş ortaklarına <strong>aktarılmaz ve satılmaz</strong>. Veriler yalnızca ilgili okulun idari personeli, öğretmenleri ve izin verilen velileriyle paylaşılır. Hizmetin teknik olarak yürütülebilmesi için aşağıdaki alıcılara sınırlı aktarım yapılabilir:</p>
<ul>
    <li><strong>Sunucu ve barındırma hizmeti sağlayıcısı:</strong> Verilerin saklandığı altyapı; sunucular yurt dışında bulunabilir.</li>
    <li><strong>WhatsApp (Meta):</strong> Okul veli bildirimi özelliğini kullanırsa veli telefon numarası ve gönderilen bilgilendirme mesajı, WhatsApp Business hizmeti üzerinden Meta’ya iletilir (yurt dışına aktarım içerir).</li>
    <li><strong>Tarayıcı bildirim servisleri:</strong> Bildirimlere izin veren kullanıcıların bildirimleri, tarayıcı sağlayıcılarının (ör. Google, Mozilla, Apple, Microsoft) push servisleri üzerinden iletilir.</li>
    <li><strong>Yetkili kurum ve kuruluşlar:</strong> Yasal yükümlülük bulunması halinde.</li>
</ul>
<p>Platform’da harici yazı tipi, betik (CDN), harita, reklam veya ölçüm hizmeti kullanılmaz; sayfa görüntülenirken IP adresiniz bu tür üçüncü taraf sağlayıcılara gönderilmez. QR kodları da tarayıcınızda üretilir. Yalnızca öğretmenlerin derslere kendi eklediği harici bağlantılı görseller bu kapsamın dışındadır; böyle bir görsel görüntülendiğinde ilgili sitenin sunucusuna istek yapılır.</p>

<h2>5. Saklama süresi</h2>
<ul>
    <li>Hesap, eğitim ve ilerleme verileri hizmetin sürdüğü süre boyunca saklanır; okulun sözleşmesi sona erdiğinde veya silme talebi geldiğinde silinir ya da anonimleştirilir.</li>
    <li>Öğrenci sayfa ve işlem kayıtları 10 gün sonra otomatik silinir.</li>
    <li>Oturum kayıtları oturum süresince (varsayılan 2 saat) ve süre dolduktan sonra sistem temizliğine kadar tutulur.</li>
    <li>Demo talepleri, talebin sonuçlandırılması ve gerekli iletişim için gereken süre boyunca saklanır.</li>
</ul>

<h2>6. Güvenlik</h2>
<ul>
    <li>Tüm veri trafiği HTTPS şifreleme protokolüyle korunur; yönetim ekranlarına rol bazlı erişim uygulanır.</li>
    <li>Giriş parolaları veritabanında geri döndürülemez (hash) biçimde saklanır.</li>
    <li>Öğrenci giriş bilgisi kopyası ise (bkz. 2. madde) yetkili kişilerin bilgileri öğrenciye iletebilmesi için uygulama anahtarıyla şifreli ve geri çözülebilir biçimde tutulur; yalnızca yetkili yönetici ve öğretmenler görebilir (öğretmenler yalnızca kendi sınıflarındaki öğrencilerin bilgilerine erişir). Okullar, çıktısı alınan giriş kartlarını güvenle saklamalı ve yetkisiz kişilerle paylaşmamalıdır.</li>
    <li>Hesabınızda şüpheli bir kullanım fark ederseniz okul yöneticinize ve {{ config('app.contact_email') }} adresine bildirin.</li>
</ul>

<h2>7. İlgili kişilerin (veri sahiplerinin) hakları</h2>
<p>KVKK’nın 11. maddesi uyarınca verilerinizin işlenip işlenmediğini öğrenme, bilgi talep etme, düzeltme, silme, işlemeye itiraz etme ve zarara uğramanız hâlinde tazminat talep etme haklarına sahipsiniz. Öğrenci, öğretmen ve veli verilerine ilişkin talepler için öncelikle kendi Okul Yönetiminize (veri sorumlusuna) başvurmalısınız; okulun resmi talebi doğrultusunda Platform, gerekli silme veya düzeltme işlemlerini yasal süreler içinde yerine getirir. Doğrudan Platform Yönetimi’ne ilettiğiniz bilgilere ilişkin talepler için {{ config('app.contact_email') }} adresine yazabilirsiniz.</p>

<h2>8. Çerezler</h2>
<p>Ayrıntılar için <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>’na bakın.</p>

<p class="meta">Bu metin bilgilendirme amaçlıdır ve değiştirilebilir; güncel sürüm her zaman bu sayfada yayımlanır.</p>
@endsection
