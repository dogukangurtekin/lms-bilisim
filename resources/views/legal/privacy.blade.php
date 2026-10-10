@extends('layouts.public', ['title' => 'Gizlilik Politikası ve KVKK Aydınlatma Metni', 'description' => 'Bilişim Kod platformunda kişisel verilerin hangi amaçlarla işlendiği, kimlerle paylaşıldığı ve haklarınız.', 'canonical' => route('legal.privacy')])

@section('content')
<h1>Gizlilik Politikası ve KVKK Aydınlatma Metni</h1>
<p class="meta">Son güncelleme: 10 Ekim 2026</p>

<p>Bu metin, 6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) kapsamında Bilişim Kod platformunu (bilisimkod.com) kullanırken kişisel verilerinizin nasıl işlendiğini açıklar.</p>

<h2>1. Veri sorumlusu ve okulların rolü</h2>
<p>Platform, okullara kodlama ve yapay zekâ eğitimi altyapısı sağlar. Okul tarafından platforma tanımlanan öğrenci, öğretmen ve sınıf verileri bakımından veri sorumlusu ilgili okuldur; Bilişim Kod bu verileri okulun talimatları doğrultusunda veri işleyen olarak işler. Demo talebi gibi doğrudan Bilişim Kod’a ilettiğiniz bilgilerde veri sorumlusu Bilişim Kod’dur.</p>

<h2>2. İşlenen veriler</h2>
<div class="table-wrap">
<table>
    <tr><th>Veri</th><th>Kimden</th><th>Amaç</th></tr>
    <tr><td>Ad soyad, e-posta, mesaj</td><td>Demo formunu dolduran ziyaretçi</td><td>Demo talebine dönüş yapmak</td></tr>
    <tr><td>Kullanıcı adı/e-posta, parola (şifreli saklanır), rol, sınıf bilgisi</td><td>Öğrenci, öğretmen, yönetici</td><td>Hesap oluşturma ve giriş</td></tr>
    <tr><td>Ders ilerlemesi, ödev, puan, XP, quiz ve yarışma sonuçları</td><td>Öğrenci</td><td>Eğitim hizmetinin sunulması, ilerleme raporları</td></tr>
    <tr><td>Veli telefon numarası</td><td>Okul yönetimi</td><td>Veli bilgilendirme bildirimleri (okul tarafından etkinleştirilirse)</td></tr>
    <tr><td>Bildirim aboneliği bilgisi</td><td>Kullanıcı (izin verirse)</td><td>Tarayıcı bildirimleri göndermek</td></tr>
    <tr><td>IP adresi, tarayıcı bilgisi, oturum çerezi</td><td>Tüm kullanıcılar</td><td>Güvenlik, oturum yönetimi, hata ayıklama</td></tr>
</table>
</div>

<h2>3. Hukuki sebepler</h2>
<p>Veriler; sözleşmenin ifası, hukuki yükümlülüklerin yerine getirilmesi, meşru menfaat ve gerektiğinde açık rıza hukuki sebeplerine dayanılarak işlenir. 18 yaşından küçük öğrencilerin verileri, okul ve veli/vasi sorumluluğunda ve yalnızca eğitim amacıyla işlenir.</p>

<h2>4. Verilerin aktarılması</h2>
<ul>
    <li>Sunucu ve barındırma hizmeti sağlayıcıları.</li>
    <li>Veli bildirimi etkinleştirilmişse WhatsApp (Meta) iş mesajlaşma hizmeti.</li>
    <li>Tarayıcı bildirimleri için tarayıcı sağlayıcılarının push servisleri.</li>
    <li>Yasal yükümlülük halinde yetkili kurum ve kuruluşlar.</li>
</ul>
<p>Kişisel verileriniz reklam amacıyla satılmaz veya üçüncü taraflarla pazarlama için paylaşılmaz.</p>

<h2>5. Saklama süresi</h2>
<p>Veriler, hizmetin sürdüğü süre ve ilgili mevzuatın gerektirdiği süre boyunca saklanır. Okulun sözleşmesi sona erdiğinde veya silme talebi geldiğinde veriler silinir ya da anonimleştirilir.</p>

<h2>6. Haklarınız</h2>
<p>KVKK’nın 11. maddesi uyarınca; verilerinizin işlenip işlenmediğini öğrenme, bilgi talep etme, düzeltme, silme, işlemeye itiraz etme ve zarara uğramanız halinde tazminat talep etme haklarına sahipsiniz. Öğrenci verileriyle ilgili talepler için önce okulunuza başvurmanızı, diğer talepler için ana sayfadaki <a href="{{ url('/#iletisim') }}">demo/iletişim formunu</a> kullanmanızı rica ederiz.@if(config('app.contact_email')) Ayrıca <a href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a> adresine yazabilirsiniz.@endif</p>

<h2>7. Güvenlik</h2>
<p>Bağlantılar HTTPS ile şifrelenir, parolalar geri döndürülemez biçimde saklanır, yönetim ekranlarına rol bazlı erişim uygulanır.</p>

<h2>8. Çerezler</h2>
<p>Ayrıntılar için <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>’na bakın.</p>

<p class="note">Bu metin bilgilendirme amaçlıdır ve değiştirilebilir; güncel sürüm her zaman bu sayfada yayımlanır.</p>
@endsection
