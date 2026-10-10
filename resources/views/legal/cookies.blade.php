@extends('layouts.public', ['title' => 'Çerez Politikası', 'description' => 'Bilişim Kod sitesinde kullanılan çerezler ve tarayıcı depolama alanları.', 'canonical' => route('legal.cookies')])

@section('content')
<h1>Çerez Politikası</h1>
<p class="meta">Son güncelleme: 10 Ekim 2026</p>

<p>Çerezler, bir siteyi ziyaret ettiğinizde tarayıcınıza kaydedilen küçük dosyalardır. Bilişim Kod yalnızca sitenin çalışması için zorunlu olanları kullanır.</p>

<h2>Kullandığımız çerezler</h2>
<div class="table-wrap">
<table>
    <tr><th>Ad</th><th>Tür</th><th>Amaç</th><th>Süre</th></tr>
    <tr><td>Oturum çerezi (<code>…-session</code>)</td><td>Zorunlu</td><td>Giriş yapmış kullanıcıyı tanımak, oturumu sürdürmek</td><td>2 saat</td></tr>
    <tr><td><code>XSRF-TOKEN</code></td><td>Zorunlu</td><td>Formlara yönelik sahte istek (CSRF) saldırılarını önlemek</td><td>2 saat</td></tr>
</table>
</div>

<h2>Tarayıcı depolama alanı</h2>
<p>Çerez bildirimini kapattığınızı hatırlamak için tarayıcınızın yerel depolamasında (<code>cookie_notice_ok</code>) küçük bir değer saklanır. Bu değer sunucuya gönderilmez.</p>

<h2>İzleme ve reklam çerezleri</h2>
<p>Reklam veya kullanıcıyı siteler arasında izleyen çerez kullanmıyoruz. Ziyaret istatistiği için ileride çerez kullanmayan, kişisel veri toplamayan bir ölçüm aracı kullanılırsa bu sayfa güncellenir.</p>

<h2>Çerezleri yönetme</h2>
<p>Tarayıcı ayarlarından çerezleri silebilir veya engelleyebilirsiniz; ancak zorunlu çerezler engellenirse giriş yapamazsınız.</p>

<p>Daha fazla bilgi için <a href="{{ route('legal.privacy') }}">Gizlilik Politikası</a>’na bakın.</p>
@endsection
