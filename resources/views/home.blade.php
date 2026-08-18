<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - bilişim teknolojileri ve yazılım için modern platform.">
    <title>Bilişim Kod</title>
    <style>
        :root{
            --bg:#f6f9ff;
            --panel:#ffffff;
            --panel-soft:#f8fbff;
            --border:#d8e2f0;
            --text:#172033;
            --muted:#5f6f87;
            --brand:#2563eb;
            --brand2:#06b6d4;
        }
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:
            radial-gradient(circle at top left,rgba(37,99,235,.10),transparent 28%),
            radial-gradient(circle at top right,rgba(6,182,212,.09),transparent 26%),
            linear-gradient(180deg,#f8fbff 0%,#eef4fb 100%);color:var(--text)}
        a{text-decoration:none;color:inherit}
        .page{min-height:100vh;display:flex;flex-direction:column;position:relative;overflow:hidden}
        .page::before,.page::after{content:"";position:absolute;border-radius:999px;filter:blur(50px);pointer-events:none}
        .page::before{width:260px;height:260px;background:rgba(37,99,235,.12);top:-80px;left:-60px}
        .page::after{width:300px;height:300px;background:rgba(6,182,212,.10);right:-100px;bottom:-120px}
        .container{width:min(1080px,calc(100% - 28px));margin:0 auto;position:relative;z-index:1}
        .nav{display:flex;justify-content:center;align-items:center;padding:24px 0 8px}
        .hero{flex:1;display:grid;place-items:center;padding:24px 0 30px}
        .center{width:min(720px,100%);text-align:center}
        .logo-wrap{display:grid;place-items:center;margin:0 auto 24px;width:min(540px,100%);padding:18px;border-radius:28px;background:var(--panel);border:1px solid var(--border);box-shadow:0 20px 60px rgba(37,99,235,.08)}
        .logo{width:min(100%,460px);height:auto;display:block;border-radius:20px}
        h1{margin:0 0 12px;font-size:clamp(36px,6vw,58px);line-height:1.02;letter-spacing:-.04em}
        .lead{margin:0 auto;max-width:640px;color:var(--muted);font-size:clamp(16px,2vw,19px);line-height:1.8}
        .actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px;margin-top:26px}
        .btn{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:0 20px;border-radius:14px;font-weight:800;border:1px solid transparent;transition:transform .2s ease,border-color .2s ease,background .2s ease}
        .btn-primary{background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;box-shadow:0 18px 40px rgba(37,99,235,.22)}
        .btn-secondary{background:#fff;border-color:var(--border);color:var(--text)}
        .btn-demo{background:#ffb800;color:#12203a;box-shadow:0 16px 36px rgba(255,184,0,.22)}
        .btn:hover{transform:translateY(-1px)}
        .modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;padding:16px;z-index:50}
        .modal-backdrop.is-open{display:flex}
        .modal{width:min(560px,100%);background:#fff;border:1px solid var(--border);border-radius:24px;box-shadow:0 24px 80px rgba(15,23,42,.22);padding:22px}
        .modal h3{margin:0 0 8px;font-size:24px}
        .modal p{margin:0 0 18px;color:var(--muted);line-height:1.7}
        .field{display:grid;gap:8px;margin-bottom:12px}
        .field label{font-weight:700;font-size:14px;color:var(--text)}
        .field input,.field textarea{width:100%;border:1px solid var(--border);border-radius:14px;padding:12px 14px;font:inherit;color:var(--text);background:#fff}
        .field textarea{min-height:120px;resize:vertical}
        .modal-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;margin-top:14px}
        .btn-ghost{background:#fff;border-color:var(--border);color:var(--text)}
        .strip{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:22px}
        .chip{padding:8px 12px;border-radius:999px;background:#fff;border:1px solid var(--border);color:#48607d;font-size:12px;font-weight:700}
        .section{padding:8px 0 32px}
        .section-title{display:grid;gap:10px;text-align:center;margin:10px auto 18px;max-width:760px}
        .section-title h2{margin:0;font-size:clamp(24px,3vw,34px);line-height:1.1}
        .section-title p{margin:0;color:var(--muted);line-height:1.75}
        .features{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
        .feature{padding:18px;border-radius:20px;background:var(--panel);border:1px solid var(--border);box-shadow:0 12px 30px rgba(37,99,235,.06)}
        .feature strong{display:block;font-size:16px;margin-bottom:8px}
        .feature p{margin:0;color:var(--muted);font-size:14px;line-height:1.7}
        .info-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:14px;margin-top:14px}
        .box{padding:22px;border-radius:24px;background:var(--panel);border:1px solid var(--border);box-shadow:0 12px 30px rgba(37,99,235,.06)}
        .box h3{margin:0 0 10px;font-size:22px}
        .box p{margin:0;color:var(--muted);line-height:1.8}
        .bullets{display:grid;gap:10px;margin-top:16px}
        .bullet{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:16px;background:var(--panel-soft);border:1px solid var(--border)}
        .bullet i{width:24px;height:24px;border-radius:999px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:grid;place-items:center;font-style:normal;font-weight:900;font-size:12px;flex:none;color:#fff}
        footer{padding:16px 0 24px;color:var(--muted);font-size:13px}
        .footer-line{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;border-top:1px solid var(--border);padding-top:14px}
        @media (max-width: 720px){
            .container{width:min(100% - 20px,1080px)}
            .nav{justify-content:center}
            .logo-wrap{padding:14px}
            .actions{flex-direction:column}
            .btn{width:100%}
            .features,.info-grid{grid-template-columns:1fr}
            .footer-line{flex-direction:column;align-items:flex-start}
        }
    </style>
</head>
<body>
<div class="page">
    <header class="container">
        <div class="nav"></div>
    </header>

    <main class="hero container">
        <section class="center">
            <div class="logo-wrap">
                <img class="logo" src="{{ asset('images/bilisim-kod-logo.jpg') }}" alt="Bilişim Kod logo">
            </div>
            <h1>Bilişim Kod</h1>
            <p class="lead">Bilişim teknolojileri ve yazılım öğrenimini sade, güçlü ve güven veren bir yapıda sunan modern platform.</p>
            <div class="actions">
                <a href="{{ route('login') }}" class="btn btn-primary">Hemen Başla</a>
                <a href="#hakkinda" class="btn btn-secondary">Daha Fazla Bilgi</a>
                <button type="button" class="btn btn-demo" id="openDemoRequest">Demo için talepde bulun</button>
            </div>
            <div class="strip" aria-hidden="true">
                <span class="chip">Teknoloji</span>
                <span class="chip">Yazılım</span>
                <span class="chip">Eğitim</span>
            </div>
        </section>
    </main>

    <section class="container section" id="hakkinda">
        <div class="section-title">
            <h2>Bilişim Kod, öğrenme akışını sade ve düzenli tutar</h2>
            <p>Öğrenciler, öğretmenler ve kurumlar için yazılım temelli bir dijital yapı. Karmaşık menüler yerine net yönlendirme, sade görsel dil ve güven veren içerik sunar.</p>
        </div>

        <div class="features">
            <article class="feature">
                <strong>Kurumsal giriş</strong>
                <p>İlk izlenimde profesyonel bir marka hissi verir, gereksiz kalabalık oluşturmaz.</p>
            </article>
            <article class="feature">
                <strong>Mobil uyum</strong>
                <p>Telefon, tablet ve masaüstünde aynı düzenli görünümü korur.</p>
            </article>
            <article class="feature">
                <strong>Hızlı kullanım</strong>
                <p>Login ve ana erişim noktaları net biçimde ayrılır, kullanıcıyı yormaz.</p>
            </article>
        </div>

        <div class="info-grid">
            <div class="box">
                <h3>Platform ne sunar?</h3>
                <p>Bilişim Kod; ders içerikleri, görevler, takip alanları ve dijital öğrenme deneyimini tek çatı altında birleştiren bir giriş yüzü olarak tasarlanmıştır.</p>
                <div class="bullets">
                    <div class="bullet"><i>✓</i><div><strong>Düzenli yapı</strong><p style="margin:4px 0 0;color:var(--muted)">İçerikler sade bloklarla sunulur.</p></div></div>
                    <div class="bullet"><i>✓</i><div><strong>Net iletişim</strong><p style="margin:4px 0 0;color:var(--muted)">Marka adı ve odak alanı hemen anlaşılır.</p></div></div>
                </div>
            </div>

            <div class="box">
                <h3>Kimler için?</h3>
                <p>Okul yönetimi, öğretmenler, öğrenciler ve eğitim içeriklerini dijitalleştirmek isteyen kurumlar için uygun bir başlangıç sayfası.</p>
                <div class="bullets">
                    <div class="bullet"><i>1</i><div><strong>Öğrenciler</strong><p style="margin:4px 0 0;color:var(--muted)">Basit ve anlaşılır yönlendirme.</p></div></div>
                    <div class="bullet"><i>2</i><div><strong>Öğretmenler</strong><p style="margin:4px 0 0;color:var(--muted)">Kurumsal, güven veren arayüz.</p></div></div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal-backdrop" id="demoRequestModal" aria-hidden="true">
        <div class="modal">
            <h3>Demo Talebi</h3>
            <p>Ad Soyad, e-posta ve mesajını bırak. Talep admin hesabındaki <strong>Taleplerim</strong> ekranına düşer.</p>
            <form method="POST" action="{{ route('support-requests.demo') }}">
                @csrf
                <div class="field">
                    <label for="guest_name">Ad Soyad</label>
                    <input id="guest_name" name="guest_name" type="text" required maxlength="190" value="{{ old('guest_name') }}" placeholder="Adınız ve soyadınız">
                </div>
                <div class="field">
                    <label for="guest_email">E-posta</label>
                    <input id="guest_email" name="guest_email" type="email" required maxlength="190" value="{{ old('guest_email') }}" placeholder="ornek@eposta.com">
                </div>
                <div class="field">
                    <label for="message">Mesaj</label>
                    <textarea id="message" name="message" required maxlength="6000" placeholder="Demo talebiniz ile ilgili kısa notunuz">{{ old('message') }}</textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-ghost" id="closeDemoRequest">Vazgeç</button>
                    <button type="submit" class="btn btn-primary">Talebi Gönder</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="container footer-line">
            <div>© {{ date('Y') }} Bilişim Kod</div>
            <div>Sade, açık renkli ve profesyonel giriş deneyimi</div>
        </div>
    </footer>
</div>
<script>
(function () {
    const openBtn = document.getElementById('openDemoRequest');
    const closeBtn = document.getElementById('closeDemoRequest');
    const modal = document.getElementById('demoRequestModal');
    if (!openBtn || !closeBtn || !modal) return;

    const openModal = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const firstInput = modal.querySelector('input, textarea, button');
        if (firstInput) firstInput.focus();
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        openBtn.focus();
    };

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
})();
</script>
</body>
</html>
