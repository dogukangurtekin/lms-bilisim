@extends('layouts.public', ['title' => 'İletişim', 'description' => 'Bilişim Kod ile iletişime geçin: okulunuz için demo talebi, fiyat ve kurulum soruları, destek ve iş birliği.', 'canonical' => route('pages.contact'), 'breadcrumb' => 'İletişim'])

@section('content')
<h1>İletişim</h1>
<p class="meta">Soru, talep ve önerileriniz için bize ulaşın.</p>

<h2>E-posta</h2>
<p><a href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a> adresine yazabilirsiniz. Mesajlara iş günlerinde dönüş yapmaya çalışıyoruz.</p>

<h2>Okulunuz için demo</h2>
<p>Platformu okulunuzun sınıf yapısına göre 15 dakikalık bir gösterimle tanıtabiliriz. Ana sayfadaki <a href="{{ url('/#iletisim') }}">Demo Talep Et</a> formunu doldurmanız yeterli.</p>

<h2>Mesajınıza şunları eklerseniz</h2>
<ul>
    <li>Okulun adı ve bulunduğu şehir</li>
    <li>Hangi kademeler (ilkokul, ortaokul, lise) için kullanmayı düşündüğünüz</li>
    <li>Yaklaşık öğrenci ve öğretmen sayısı</li>
</ul>
<p>Böylece ilk yanıtta size doğrudan işe yarar bir bilgi gönderebiliriz.</p>

<h2>Mevcut kullanıcılar</h2>
<p>Öğrenci ve öğretmen hesapları okul yönetimi tarafından açılır. Şifre ya da hesap sorunlarında önce okul yöneticinize başvurun; çözülemeyen teknik sorunları bize iletebilirsiniz.</p>
@endsection
