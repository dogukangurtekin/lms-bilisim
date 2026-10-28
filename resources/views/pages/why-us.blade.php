@extends('layouts.public', ['title' => 'Neden Biz', 'description' => 'Okullar neden Bilişim Kod’u seçiyor: tek panel, oyunla öğrenme, otomatik ödev ve ilerleme takibi, kurulum gerektirmeyen tarayıcı tabanlı altyapı.', 'canonical' => route('pages.why'), 'breadcrumb' => 'Neden Biz'])

@section('content')
<h1>Neden Bilişim Kod?</h1>
<p class="meta">Bir okul altyapısında aradığımız şeyleri sıraladık.</p>

<h2>1. Her şey tek panelde</h2>
<p>Ders anlatımı, kodlama oyunları, canlı quiz, ödev ve ilerleme raporu aynı yerde. Öğretmen dört farklı sekme arasında gidip gelmez; öğrenci de hangi uygulamayı açacağını aramaz.</p>

<h2>2. Öğrenci oynarken öğrenir</h2>
<p>Noktaları Birleştir, Compute It, Blok Kodlama ve benzeri oyunlar algoritmik düşünmeyi küçük, ölçülebilir adımlara böler. XP, rozet ve liderlik tablosu da öğrencinin “bir bölüm daha” demesini sağlar.</p>

<h2>3. Öğretmen için hazır, ama esnek</h2>
<p>Hazır içeriklerle hemen derse başlanabilir; isteyen öğretmen kendi slaytını, sınavını ve soru havuzunu oluşturur. Ödev bir sınıfa ya da tek tek öğrencilere atanabilir, teslimler tek listede görünür.</p>

<h2>4. Yönetici için görünürlük</h2>
<p>Hangi sınıf hangi derse nerede, kim ödevini teslim etti, hangi öğrenci geride kaldı? Bu soruların cevabı rapor ekranlarında hazırdır; Excel’e dönüp elle birleştirmek gerekmez.</p>

<h2>5. Kurulum yok, bakım yok</h2>
<p>Platform tarayıcıdan çalışır. Okulun BT sorumlusu bilgisayarlara bir şey yüklemez, güncelleme takvimi tutmaz. QR ile giriş sayesinde küçük yaştaki öğrenciler şifre yazmakla vakit kaybetmeden derse geçer.</p>

<h2>6. Veri okulda kalır</h2>
<p>Hesapları yalnızca okul yönetimi açar, bağlantılar HTTPS ile korunur, roller arasında yetki ayrımı vardır. Ayrıntılar için <a href="{{ route('legal.privacy') }}">Gizlilik Politikası ve KVKK Aydınlatma Metni</a>’ne bakabilirsiniz.</p>

<p class="note">Okulunuzda nasıl çalışacağını görmek ister misiniz? <a href="{{ route('pages.contact') }}">Kısa bir demo talep edin</a>.</p>
@endsection
