@extends('layouts.public', ['title' => 'Kullanım Şartları', 'description' => 'Bilişim Kod platformunu kullanırken geçerli olan kurallar ve sorumluluklar.', 'canonical' => route('legal.terms')])

@section('content')
<h1>Kullanım Şartları</h1>
<p class="meta">Son güncelleme: 10 Ekim 2026</p>

<p>Bilişim Kod platformunu (bilisimkod.com) kullanarak aşağıdaki şartları kabul etmiş olursunuz.</p>

<h2>1. Hizmetin tanımı</h2>
<p>Bilişim Kod; okullara ders içeriği, kodlama etkinlikleri, canlı quiz, yarışma, ödev takibi ve raporlama sunan bir eğitim platformudur. Hesaplar okul yönetimi veya yetkili öğretmenler tarafından oluşturulur.</p>

<h2>2. Hesap ve güvenlik</h2>
<ul>
    <li>Giriş bilgilerinizi gizli tutmaktan siz sorumlusunuz; başkasıyla paylaşmayın.</li>
    <li>Hesabınızda yetkisiz bir kullanım fark ederseniz okul yöneticinize bildirin.</li>
    <li>Öğrenci hesaplarının kullanımından okul ve veli/vasi sorumludur.</li>
</ul>

<h2>3. Kabul edilebilir kullanım</h2>
<p>Aşağıdakiler yasaktır:</p>
<ul>
    <li>Platformun güvenliğini, işleyişini veya diğer kullanıcıların erişimini bozmaya yönelik girişimler (otomatik saldırı, zafiyet taraması, kötü amaçlı kod çalıştırma dahil).</li>
    <li>Başkalarının hesabına izinsiz erişmek veya puan/XP sistemini manipüle etmek.</li>
    <li>Hakaret, nefret, yasa dışı veya uygunsuz içerik paylaşmak.</li>
    <li>Platform içeriğini izinsiz kopyalamak, satmak veya yeniden dağıtmak.</li>
</ul>
<p>Kurallara aykırı kullanım halinde hesap askıya alınabilir veya kapatılabilir.</p>

<h2>4. Fikri mülkiyet</h2>
<p>Platformun yazılımı, tasarımı ve ders içerikleri Bilişim Kod’a veya lisans verenlerine aittir. Kullanıcılar kendi oluşturdukları içeriklerin haklarını korur ve bunların eğitim amacıyla platformda gösterilmesine izin verir.</p>

<h2>5. Hizmet sürekliliği</h2>
<p>Hizmetin kesintisiz ve hatasız olması için makul çaba gösteririz; ancak bakım, güncelleme veya mücbir sebeplerle geçici kesintiler yaşanabilir. Özellikler önceden bildirimle değiştirilebilir veya kaldırılabilir.</p>

<h2>6. Sorumluluğun sınırı</h2>
<p>Platform “mevcut haliyle” sunulur. Yasaların izin verdiği ölçüde, hizmetin kullanılmasından doğan dolaylı zararlardan Bilişim Kod sorumlu tutulamaz.</p>

<h2>7. Kişisel veriler</h2>
<p>Verilerinizin işlenmesi <a href="{{ route('legal.privacy') }}">Gizlilik Politikası</a> ve <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>’nda açıklanmıştır.</p>

<h2>8. Değişiklikler ve uygulanacak hukuk</h2>
<p>Bu şartlar güncellenebilir; güncel sürüm bu sayfada yayımlanır. Şartlara Türkiye Cumhuriyeti hukuku uygulanır ve uyuşmazlıklarda Türkiye mahkemeleri yetkilidir.</p>
@endsection
