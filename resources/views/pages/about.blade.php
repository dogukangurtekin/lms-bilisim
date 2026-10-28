@extends('layouts.public', ['title' => 'Hakkımızda', 'description' => 'Bilişim Kod’un hikâyesi: okullarda kodlama dersini sıkıcı bir ders olmaktan çıkarıp öğrencinin merak ettiği bir alışkanlığa dönüştürme amacı.', 'canonical' => route('pages.about'), 'breadcrumb' => 'Hakkımızda'])

@section('content')
<h1>Hakkımızda</h1>
<p class="meta">Bilişim Kod kimin için, neden yapıldı?</p>

<p>Bilişim Kod, sınıfta kodlama anlatan öğretmenlerin en çok yorulduğu yerden doğdu: ders hazırlığı bir yanda, ödev takibi bir yanda, öğrencinin ilgisini canlı tutmak başka bir yanda. Üstelik bunların her biri ayrı bir araçta, ayrı bir tabloda yaşıyordu.</p>
<p>Biz bu parçaları tek çatı altında topladık. Öğretmen dersini hazırlar, öğrenci oyunla pekiştirir, yönetici ilerlemeyi tek ekrandan görür. Hiçbir şey kurulum, lisans anahtarı ya da özel cihaz gerektirmez; tarayıcısı olan her bilgisayar ve tablet yeterlidir.</p>

<h2>Neye inanıyoruz?</h2>
<ul>
    <li><strong>Öğrenmek deneyerek olur.</strong> Bir döngüyü tanımını ezberleyerek değil, robotu hedefe götüremediğinde neyi yanlış yaptığını bularak anlarsınız. Oyunlarımız bu yüzden “hata yapmayı” güvenli hâle getirecek şekilde kurgulandı.</li>
    <li><strong>Öğretmenin zamanı değerlidir.</strong> Bir ödevi atmak, bir quizi açmak ya da bir sınıfın ilerlemesine bakmak birkaç tıklamayı geçmemeli.</li>
    <li><strong>Çocukların verisi okulundur.</strong> Bireysel üyelik yok; hesapları okul yönetimi tanımlar. Verilerin nasıl işlendiğini <a href="{{ route('legal.privacy') }}">Gizlilik Politikası</a>’nda açıkça yazdık.</li>
</ul>

<h2>Kimlerle çalışıyoruz?</h2>
<p>Bilişim Kod, kurumsal bazda hizmet veren bir okul altyapısıdır. İlkokul, ortaokul ve lise düzeyindeki okullar; her kademe için ayrı içerik, ayrı zorluk seviyesi ve ayrı panel kullanır. Yönetici, öğretmen ve öğrenci aynı veriyi farklı açılardan görür.</p>

<h2>Bir sonraki adım</h2>
<p>Platformu okulunuzda denemek isterseniz <a href="{{ route('pages.contact') }}">bize yazın</a> ya da <a href="{{ route('pages.features') }}">neler sunduğumuza</a> göz atın. Sorularınız için: <a href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a></p>
@endsection
