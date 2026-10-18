@extends('layouts.public', ['title' => 'Kullanım Şartları', 'description' => 'Bilişim Kod platformunu kullanırken geçerli olan kurallar, hak ve sorumluluklar.', 'canonical' => route('legal.terms')])

@section('content')
<h1>Kullanım Şartları Sözleşmesi</h1>
<p class="meta">Son güncelleme: 18 Ekim 2026</p>

<h2>1. Taraflar ve kapsam</h2>
<p>İşbu Kullanım Şartları Sözleşmesi (“Sözleşme”), bilisimkod.com (“Platform”) internet sitesi ve bu siteye bağlı olarak sunulan kodlama ile yapay zekâ eğitim platformu hizmetlerinin kullanım şartlarını belirler. Platform, kurumsal bazda hizmet sunan bir B2B (şirketten şirkete) okul altyapısıdır; tüm hakları Doğukan Gürtekin’e (“Platform Yönetimi”) aittir.</p>
<p>Platform hizmetlerinden yararlanan okullar, kurumlar, idareciler, öğretmenler ve öğrenciler bu Sözleşme’nin taraflarıdır. Veliler Platform’a hesapla giriş yapmaz; yalnızca okul yönetiminin öğrenci kaydına eklediği iletişim bilgileri üzerinden, okul bu özelliği etkinleştirirse bilgilendirilir (ayrıntılar için <a href="{{ route('legal.privacy') }}">Gizlilik Politikası ve KVKK Aydınlatma Metni</a>).</p>

<h2>2. Hesap oluşturma, giriş ve kullanım modeli</h2>
<ul>
    <li><strong>Bireysel kayıt yoktur:</strong> Platform üzerinde dışarıdan kullanıcıların doğrudan kendi kendilerine hesap oluşturma, kayıt olma veya üyelik açma seçeneği bulunmaz.</li>
    <li><strong>Kurumsal tanımlama:</strong> Yönetici, Öğretmen ve Öğrenci hesaplarının tamamı, hizmeti satın alan veya demo erişimi sağlanan okul yönetimi tarafından panel üzerinden tanımlanır ve yetkilendirilir. Platform’da ayrı bir veli hesabı bulunmaz.</li>
    <li><strong>Şifre güvenliği:</strong> Okul yönetimi ve öğretmenler, öğrencilere ve diğer alt kullanıcılara atadıkları giriş bilgilerinin ve şifrelerin güvenliğinden bizzat sorumludur. Yönetici ve öğretmen panellerinde öğrenci giriş bilgileri görüntülenebilir ve giriş kartı olarak çıktı alınabilir; bu bilgilerin ve çıktıların üçüncü şahıslarla paylaşılmaması veya kötüye kullanılmaması okulun sorumluluğundadır. Kanunların izin verdiği ölçüde, bu bilgilerin paylaşılması ya da kötüye kullanılması hâlinde Doğukan Gürtekin’in hukuki veya cezai sorumluluğu bulunmaz.</li>
    <li>Hesabınızda yetkisiz bir kullanım fark ederseniz okul yöneticinize bildirmelisiniz. Öğrenci hesaplarının kullanımından okul ve veli/vasi sorumludur.</li>
</ul>

<h2>3. Fikri ve sınai mülkiyet hakları</h2>
<ul>
    <li><strong>Platform hakları:</strong> Platform içinde yer alan “Noktaları Birleştir”, “Compute It” ve benzeri tüm kodlama oyunları, arayüz tasarımları, kaynak kodları, hazır müfredat içerikleri, algoritma yapıları ve rozetler 5846 sayılı Fikir ve Sanat Eserleri Kanunu (FSEK) ve ilgili mevzuat kapsamında koruma altındadır. Bu materyaller Doğukan Gürtekin’in yazılı izni olmaksızın kopyalanamaz, çoğaltılamaz, dağıtılamaz veya başka bir ticari amaçla kullanılamaz.</li>
    <li><strong>Marka:</strong> “Bilişim Kod” adı ve logosu için Türk Patent ve Marka Kurumu’na marka tescil başvurusu yapılmıştır. Başvuru süreci devam etmektedir; tescil kesinleşmemiştir. Marka haklarına ilişkin koruma 6769 sayılı Sınai Mülkiyet Kanunu kapsamındadır ve tescil sonrasında bu metin güncellenecektir.</li>
    <li><strong>Kullanıcı içerikleri:</strong> Öğretmenlerin “Ders &amp; Slayt Oluşturucu”, “Sınav Oluştur” ve “Soru Havuzu” gibi modülleri kullanarak sisteme yükledikleri, ürettikleri veya harici kaynaklardan ekledikleri her türlü metin, görsel, soru, sınav kâğıdı ve ders materyalinin FSEK kapsamındaki telif sorumluluğu tamamen ilgili öğretmene ve bağlı olduğu okul yönetimine aittir. Soru havuzunda “paylaşılan” olarak işaretlenen içerikler aynı Platform’daki diğer öğretmenlerce görülebilir ve kullanılabilir; kullanıcılar içeriklerinin haklarını korur ve bunların eğitim amacıyla Platform’da gösterilmesine izin verir.</li>
</ul>

<h2>4. Kabul edilebilir kullanım</h2>
<p>Aşağıdakiler yasaktır:</p>
<ul>
    <li>Platformun güvenliğini, işleyişini veya diğer kullanıcıların erişimini bozmaya yönelik girişimler (otomatik saldırı, zafiyet taraması, kötü amaçlı kod çalıştırma dahil).</li>
    <li>Başkalarının hesabına izinsiz erişmek veya puan/XP sistemini manipüle etmek.</li>
    <li>Hakaret, nefret, yasa dışı veya uygunsuz içerik paylaşmak.</li>
    <li>Platform içeriğini izinsiz kopyalamak, satmak veya yeniden dağıtmak.</li>
</ul>
<p>Kurallara aykırı kullanım halinde hesap askıya alınabilir veya kapatılabilir.</p>

<h2>5. Hizmet sürekliliği ve sorumluluğun sınırlandırılması</h2>
<ul>
    <li>Platform; hizmetlerin kesintisiz, hatasız veya siber saldırılardan tamamen muaf olacağını taahhüt etmez. Ancak sistem güvenliğini ve sürekliliğini en üst düzeyde tutmak için gerekli teknik altyapıyı (HTTPS şifreleme, rol bazlı erişim) sağlar. Bakım, güncelleme veya mücbir sebeplerle geçici kesintiler yaşanabilir; özellikler önceden bildirimle değiştirilebilir veya kaldırılabilir.</li>
    <li>Platform “mevcut hâliyle” sunulur. Yasaların izin verdiği ölçüde, hizmetin kullanılmasından doğan dolaylı zararlardan Platform Yönetimi sorumlu tutulamaz.</li>
    <li>Sitede yer alan “Demo Talep Et” formu üzerinden girilen bilgilerin doğruluğu kullanıcının sorumluluğundadır.</li>
</ul>

<h2>6. Kişisel veriler ve çerezler</h2>
<p>Verilerin işlenmesi <a href="{{ route('legal.privacy') }}">Gizlilik Politikası ve KVKK Aydınlatma Metni</a> ile <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>’nda açıklanmıştır.</p>

<h2>7. Değişiklikler ve uygulanacak hukuk</h2>
<p>Bu şartlar güncellenebilir; güncel sürüm bu sayfada yayımlanır. Şartlara Türkiye Cumhuriyeti hukuku uygulanır ve uyuşmazlıklarda Türkiye mahkemeleri yetkilidir.</p>

<h2>8. İletişim</h2>
<p>Şartlarla ilgili sorularınız için {{ config('app.contact_email') }} adresine yazabilirsiniz.</p>
@endsection
