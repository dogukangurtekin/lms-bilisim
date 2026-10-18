@extends('layouts.public', ['title' => 'Çerez Politikası', 'description' => 'Bilişim Kod sitesinde kullanılan çerezler, tarayıcı depolama alanları ve cihaz önbelleği.', 'canonical' => route('legal.cookies')])

@section('content')
<h1>Çerez Politikası</h1>
<p class="meta">Son güncelleme: 18 Ekim 2026</p>

<p>Çerezler, bir internet sitesini ziyaret ettiğinizde bilgisayarınızda veya mobil cihazınızda depolanan küçük metin dosyalarıdır. bilisimkod.com (“Platform”), kullanıcı gizliliğine en üst düzeyde önem verecek şekilde tasarlanmıştır ve yalnızca sitenin çalışması için zorunlu olan çerezleri kullanır.</p>

<h2>1. Kullandığımız çerezler</h2>
<div class="table-wrap">
<table>
    <tr><th>Ad</th><th>Tür</th><th>Amaç</th><th>Süre</th></tr>
    <tr><td>Oturum çerezi (<code>…-session</code>)</td><td>Zorunlu</td><td>Giriş yapmış kullanıcıyı tanımak, kullanıcı rolünü (yönetici, öğretmen, öğrenci) ayırt etmek ve oturumu sürdürmek</td><td>2 saat (hareketsizlik)</td></tr>
    <tr><td><code>XSRF-TOKEN</code></td><td>Zorunlu</td><td>Formlara yönelik sahte istek (CSRF) saldırılarını önlemek</td><td>2 saat</td></tr>
    <tr><td>Beni hatırla çerezi (<code>remember_web_…</code>)</td><td>Zorunlu (oturumu hatırlama)</td><td>Giriş yapan kullanıcının her seferinde yeniden giriş yapmadan devam edebilmesi. Öğrenci girişlerinde her zaman, öğretmen ve yöneticilerde “beni hatırla” seçilirse oluşturulur; çıkış yapıldığında silinir.</td><td>En fazla 400 gün</td></tr>
</table>
</div>
<p>Çerezler HTTPS bağlantısı üzerinden gönderilir, oturum çerezi tarayıcı betiklerinin erişimine kapalıdır ve yalnızca aynı siteden gelen isteklerde gönderilir (SameSite). Bu çerezler Platform’un temel işlevleri için teknik olarak zorunludur ve Platform üzerinden kapatılamaz.</p>

<h2>2. Tarayıcı depolama alanı (localStorage / sessionStorage)</h2>
<p>Tarayıcınızın yerel depolamasında yalnızca sunucuya gönderilmeyen, kullanım rahatlığı sağlayan küçük değerler saklanır:</p>
<ul>
    <li>Çerez bildirimini kapattığınız bilgisi (<code>cookie_notice_ok</code>);</li>
    <li>Görünüm teması, oyun ses/hız/yardım ayarları ve bazı oyunlarda seviye/ilerleme bilgisi;</li>
    <li>soru havuzunda son seçtiğiniz ders/sınıf, son gösterilen XP hediyesi bildirimi ve “hoş geldin” ekranının görüldüğü bilgisi gibi tercihler.</li>
</ul>
<p>Bu değerleri tarayıcı ayarlarından silebilirsiniz; silinmeleri hesabınızı etkilemez, yalnızca bu tercihler sıfırlanır.</p>

<h2>3. Cihaz önbelleği ve bildirimler (PWA)</h2>
<ul>
    <li>Platform, hızlı açılması ve bağlantı koptuğunda çalışabilmesi için bir <strong>servis çalışanı (service worker)</strong> kullanır. Bu bileşen sayfa dosyalarını, resimleri ve bazı panel yanıtlarını cihazınızın önbelleğinde tutabilir. Çıkış yaptığınızda ve giriş sayfasını açtığınızda kişisel veri içerebilen önbellekler temizlenir. Ortak kullanılan bilgisayarlarda çıkış yapmayı unutmamanızı öneririz.</li>
    <li>Tarayıcı bildirimlerine izin verirseniz, bildirim abonelik bilgisi (bildirim adresi ve şifreleme anahtarları) sunucuya kaydedilir; izni tarayıcı ayarlarından istediğiniz zaman kaldırabilirsiniz.</li>
</ul>

<h2>4. İzleme, reklam ve üçüncü taraf çerezleri</h2>
<p>Platformumuzda kullanıcıların internetteki hareketlerini izleyen, profil çıkaran, üçüncü taraf reklam ağlarına veri aktaran hedefleme, pazarlama veya reklam çerezleri <strong>kesinlikle kullanılmaz</strong>. Harici ölçüm/analitik hizmeti kullanılmaz; yazı tipleri, betik kitaplıkları ve QR kodları dahil tüm kaynaklar kendi sunucumuzdan sunulur, bu nedenle Platform’un kendi bileşenlerinden kaynaklanan üçüncü taraf çerezi veya isteği oluşmaz. Öğretmenlerin derslere kendi eklediği harici bağlantılı görseller bu kapsamın dışındadır; böyle bir görsel görüntülendiğinde ilgili sitenin sunucusuna istek yapılır. Gelecekte çerez kullanmayan, kişisel veri toplamayan bir ölçüm aracı kullanılırsa bu sayfa güncellenir.</p>

<h2>5. Çerezleri nasıl kontrol edebilirsiniz?</h2>
<p>Tarayıcınızın ayarlarını değiştirerek çerezleri tamamen engelleyebilir veya çerez gönderildiğinde uyarı almasını sağlayabilirsiniz. Ancak zorunlu çerezleri engellemeniz hâlinde Platform üzerindeki giriş panelleri ve eğitim modülleri kararlı çalışmayabilir; giriş yapamayabilirsiniz.</p>

<p>Daha fazla bilgi için <a href="{{ route('legal.privacy') }}">Gizlilik Politikası ve KVKK Aydınlatma Metni</a>’ne bakın. Sorularınız için {{ config('app.contact_email') }} adresine yazabilirsiniz.</p>
@endsection
