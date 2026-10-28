@extends('layouts.public', ['title' => 'Neler Sunuyoruz', 'description' => 'Bilişim Kod modülleri: kodlama oyunları, ders ve slayt oluşturucu, canlı quiz, sınav ve soru havuzu, ödev takibi, rozet sistemi ve veli bildirimi.', 'canonical' => route('pages.features'), 'breadcrumb' => 'Neler Sunuyoruz'])

@section('content')
<h1>Neler Sunuyoruz?</h1>
<p class="meta">Bir dersliğin ihtiyaç duyduğu araçlar, tek girişle.</p>

<h2>Kodlama oyunları</h2>
<p>Noktaları Birleştir, Compute It, Blok Kodlama, Flamestone, Klavye Yarışı ve Kelime Arısı gibi etkinlikler; sıralama, döngü, koşul ve hata ayıklama gibi kavramları oyun içinde öğretir. Bir kısmı <a href="{{ route('public.games') }}">giriş yapmadan denenebilir</a>.</p>

<h2>Ders ve slayt oluşturucu</h2>
<p>Öğretmen; metin, görsel, kod bloğu ve soruyu aynı slaytta buluşturarak kendi ders akışını kurar. Dersler sınıf düzeyine göre atanır, öğrenci kendi panelinden açar.</p>

<h2>Canlı quiz</h2>
<p>Öğretmen quizi anında sınıfa açar, öğrenciler kendi cihazından katılır, sonuçlar ekranda canlı güncellenir. Ders sonu yoklaması için bire bir.</p>

<h2>Sınav ve soru havuzu</h2>
<p>Soruları bir kez ekleyin, farklı sınavlarda tekrar kullanın. İsterseniz havuzu diğer öğretmenlerle paylaşın; sınav kâğıdını çıktı olarak da alabilirsiniz.</p>

<h2>Ödev ve ilerleme takibi</h2>
<p>Ödevi sınıfa ya da seçili öğrencilere atayın, son tarih belirleyin, teslimleri tek listede görün. Öğrencinin tamamladığı her ders ve etkinlik otomatik olarak işlenir.</p>

<h2>XP, rozet ve sertifika</h2>
<p>Tamamlanan görevler öğrenciye puan ve rozet kazandırır; başarı sertifika olarak da belgelenebilir. Amaç rekabet değil, düzenli çalışma alışkanlığı.</p>

<h2>Veli bildirimi ve yönetim paneli</h2>
<p>Okul isterse devam ve ödev bilgileri veliye bildirilir. Yönetici; sınıf, öğretmen ve içerik atamalarını ve raporları tek panelden yönetir.</p>

<p class="note">Hepsini birlikte görmek için <a href="{{ route('pages.contact') }}">demo talep edebilir</a> ya da <a href="{{ route('pages.why') }}">neden Bilişim Kod olduğunu</a> okuyabilirsiniz.</p>
@endsection
