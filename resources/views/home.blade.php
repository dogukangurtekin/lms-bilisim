<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - yapay zeka, kodlama ve robotik odaklı modern eğitim platformu.">
    <title>Bilişim Kod | Yapay Zeka, Kodlama, Robotik</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root{
            --bg:#050816;
            --bg2:#0b1730;
            --panel:rgba(255,255,255,.07);
            --border:rgba(148,163,184,.20);
            --text:#eef4ff;
            --muted:#9cb0cb;
            --brand:#4f8cff;
            --brand2:#0ea5e9;
            --brand3:#8b5cf6;
            --shadow:0 30px 90px rgba(4,10,24,.45);
        }
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--text);background:
            radial-gradient(circle at 14% 10%, rgba(79,140,255,.16), transparent 28%),
            radial-gradient(circle at 84% 16%, rgba(139,92,246,.12), transparent 26%),
            linear-gradient(160deg,var(--bg) 0%,var(--bg2) 56%,#04060d 100%);overflow-x:hidden}
        a{text-decoration:none;color:inherit}
        .page{min-height:100vh;position:relative;overflow:hidden}
        .page::before,.page::after{content:"";position:absolute;border-radius:999px;filter:blur(60px);pointer-events:none}
        .page::before{width:260px;height:260px;left:-80px;top:-80px;background:rgba(79,140,255,.22)}
        .page::after{width:340px;height:340px;right:-100px;bottom:-140px;background:rgba(139,92,246,.14)}
        .grain{position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);background-size:18px 18px;opacity:.26;pointer-events:none}
        .container{width:min(1180px,calc(100% - 28px));margin:0 auto;position:relative;z-index:1}
        .nav{padding:20px 0 8px}
        .hero{padding:34px 0 18px;display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,.92fr);gap:26px;align-items:center}
        .hero-copy{display:grid;justify-items:start}
        h1{margin:0 0 14px;font-size:clamp(44px,7vw,78px);line-height:.95;letter-spacing:-.05em}
        .lead{max-width:620px;margin:0;color:var(--muted);font-size:clamp(16px,1.8vw,19px);line-height:1.8}
        .cta-row{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px;justify-content:flex-start}
        .cta{display:inline-flex;align-items:center;justify-content:center;min-height:54px;padding:0 22px;border-radius:16px;font-size:16px;font-weight:900;border:1px solid transparent;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease,background .2s ease,border-color .2s ease,filter .2s ease}
        .cta-primary{background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;box-shadow:0 18px 36px rgba(37,99,235,.24)}
        .cta-secondary{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14);color:#fff;box-shadow:0 12px 28px rgba(37,99,235,.10)}
        .cta:hover{transform:translateY(-2px);filter:saturate(1.04)}
        .cta-primary:hover{box-shadow:0 22px 42px rgba(37,99,235,.28)}
        .cta-secondary:hover{border-color:rgba(37,99,255,.30);box-shadow:0 16px 34px rgba(37,99,235,.16)}
        .hero-card{position:relative;padding:18px;border-radius:30px;background:linear-gradient(180deg,rgba(255,255,255,.12),rgba(255,255,255,.05));border:1px solid var(--border);box-shadow:var(--shadow);backdrop-filter:blur(14px)}
        .hero-visual{position:relative;min-height:360px;border-radius:24px;overflow:hidden;background:
            radial-gradient(circle at 25% 20%, rgba(79,140,255,.35), transparent 22%),
            radial-gradient(circle at 78% 28%, rgba(139,92,246,.30), transparent 24%),
            linear-gradient(180deg,rgba(3,8,20,.92),rgba(9,18,38,.84))}
        .logo-orbit{position:absolute;inset:18px;display:grid;place-items:center}
        .logo-orbit img{width:min(100%,420px);height:auto;display:block;filter:drop-shadow(0 18px 40px rgba(0,0,0,.35))}
        .section{padding:16px 0 12px}
        .feature-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
        .feature{padding:18px;border-radius:22px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);box-shadow:0 16px 32px rgba(0,0,0,.12);transition:transform .2s ease,border-color .2s ease,background .2s ease}
        .feature:hover{transform:translateY(-3px);border-color:rgba(79,140,255,.34);background:rgba(255,255,255,.08)}
        .feature .icon{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(79,140,255,.24),rgba(25,211,255,.18));border:1px solid rgba(255,255,255,.12);margin-bottom:14px}
        .feature strong{display:block;font-size:16px;margin-bottom:8px}
        .feature p{margin:0;color:var(--muted);line-height:1.7;font-size:14px}
        .trust{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding:8px 0 30px}
        .panel{padding:22px;border-radius:24px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);backdrop-filter:blur(16px);box-shadow:0 16px 40px rgba(0,0,0,.18)}
        .panel h3{margin:0 0 10px;font-size:22px}
        .panel p{margin:0;color:var(--muted);line-height:1.8}
        .bullets{display:grid;gap:10px;margin-top:16px}
        .bullet{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:16px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
        .bullet i{width:24px;height:24px;border-radius:999px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:grid;place-items:center;font-style:normal;font-weight:900;font-size:12px;flex:none;color:#fff}
        footer{padding:12px 0 24px;color:var(--muted);font-size:13px}
        .footer-line{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,.10);padding-top:14px}
        .modal-backdrop{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(5,8,22,.62);z-index:60}
        .modal-backdrop.is-open{display:flex}
        .modal{width:min(560px,100%);border-radius:24px;padding:22px;background:linear-gradient(180deg,rgba(12,19,38,.96),rgba(11,17,30,.96));border:1px solid rgba(255,255,255,.12);box-shadow:var(--shadow);color:var(--text)}
        .modal h3{margin:0 0 8px;font-size:24px}
        .modal p{margin:0 0 18px;color:var(--muted);line-height:1.7}
        .field{display:grid;gap:8px;margin-bottom:12px}
        .field label{font-size:14px;font-weight:700}
        .field input,.field textarea{width:100%;border-radius:14px;border:1px solid rgba(255,255,255,.12);padding:12px 14px;background:rgba(255,255,255,.06);color:var(--text);font:inherit}
        .field textarea{min-height:120px;resize:vertical}
        .modal-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:14px}
        .btn-ghost{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12);color:#fff}
        .alert{padding:14px 16px;border-radius:16px;border:1px solid transparent;font-weight:700;line-height:1.5}
        .alert-success{background:#052e24;border-color:#0f766e;color:#ccfbf1}
        .alert-error{background:#3b0f18;border-color:#ef4444;color:#fecaca}
        @media (max-width: 980px){
            .hero,.trust{grid-template-columns:1fr}
            .hero-card{order:-1}
            .feature-grid{grid-template-columns:1fr}
        }
        @media (max-width: 720px){
            .container{width:min(100% - 22px,1180px)}
            .nav{padding:14px 0 6px}
            .hero-copy{justify-items:center;text-align:center}
            h1{font-size:clamp(38px,12vw,58px)}
            .lead{max-width:100%}
            .cta-row{align-items:stretch}
            .cta-row{flex-direction:column}
            .cta{width:100%}
            .hero-card{padding:14px;border-radius:24px}
            .hero-visual{min-height:300px}
            .feature{text-align:center}
            .feature .icon{margin-left:auto;margin-right:auto}
            .trust,.panel,.bullets{justify-items:center}
            .panel{text-align:center}
            .bullet{justify-content:center;text-align:left}
            .footer-line{flex-direction:column;align-items:flex-start}
            .modal{padding:18px}
        }
    </style>
</head>
<body>
<div class="page" x-data="{ demoOpen:false }">
    <div class="grain"></div>

    <header class="container">
        <div class="nav"></div>
    </header>

    <main class="container">
        <section class="hero">
            <div class="hero-copy">
                <h1>Bilişim Kod</h1>
                <p class="lead">Bilişim teknolojileri ve yazılım öğrenimini tek bir premium deneyimde birleştiren modern platform. Net, hızlı ve kurumsal.</p>

                <div class="cta-row">
                    <a href="{{ route('login') }}" class="cta cta-primary">Hemen Başla</a>
                    <button type="button" class="cta cta-secondary" @click="demoOpen = true">Demo Talebi</button>
                </div>
            </div>

            <div class="hero-card" aria-hidden="true">
                <div class="hero-visual">
                    <div class="logo-orbit">
                        <img src="{{ asset('images/bilisim-kod-logo.jpg') }}" alt="Bilişim Kod logo">
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="feature-grid">
                <article class="feature">
                    <div class="icon">✦</div>
                    <strong>Etkileşimli Öğrenme</strong>
                    <p>Ders, görev ve ilerleme akışı sade bir ürün deneyimiyle sunulur.</p>
                </article>
                <article class="feature">
                    <div class="icon">⌘</div>
                    <strong>Kurumsal Kontrol</strong>
                    <p>Öğretmen, öğrenci ve yönetim süreçleri tek çatı altında düzenli kalır.</p>
                </article>
                <article class="feature">
                    <div class="icon">◌</div>
                    <strong>Güçlü İlk İzlenim</strong>
                    <p>Logo odaklı premium kompozisyon marka algısını net biçimde güçlendirir.</p>
                </article>
            </div>
        </section>

        <section class="trust">
            <div class="panel">
                <h3>Platformun amacı</h3>
                <p>Bilişim Kod, eğitim teknolojileri için modern bir ana kapı oluşturur. Sade ama etkili bir yapı ile kurumun dijital yüzünü güçlü gösterir.</p>
                <div class="bullets">
                    <div class="bullet"><i>✓</i><div><strong>Minimal ama güçlü</strong><p style="margin:4px 0 0;color:var(--muted)">Gereksiz linkler yok, odak net.</p></div></div>
                    <div class="bullet"><i>✓</i><div><strong>Teknoloji hissi</strong><p style="margin:4px 0 0;color:var(--muted)">Cam etkisi, glow ve derinlik dengeli.</p></div></div>
                </div>
            </div>

            <div class="panel">
                <h3>Kimler için?</h3>
                <p>Öğrenciler, öğretmenler ve yöneticiler için hızlı giriş, güven veren tasarım ve doğrudan kullanım akışı.</p>
                <div class="bullets">
                    <div class="bullet"><i>1</i><div><strong>Öğrenciler</strong><p style="margin:4px 0 0;color:var(--muted)">Net ve anlaşılır başlangıç.</p></div></div>
                    <div class="bullet"><i>2</i><div><strong>Öğretmenler</strong><p style="margin:4px 0 0;color:var(--muted)">Kurumsal ve işlevsel panel algısı.</p></div></div>
                    <div class="bullet"><i>3</i><div><strong>Admin</strong><p style="margin:4px 0 0;color:var(--muted)">Kontrol, takip ve yönetim kolaylığı.</p></div></div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container footer-line">
            <div>© {{ date('Y') }} Bilişim Kod</div>
            <div>Modern teknoloji ve eğitim platformu</div>
        </div>
    </footer>

    <div class="modal-backdrop" :class="{ 'is-open': demoOpen }" x-cloak @click.self="demoOpen = false">
        <div class="modal">
            <h3>Demo Talebi</h3>
            <p>Ad Soyad, e-posta ve kısa mesaj bırak. Talep admin hesabındaki taleplerin arasına düşer.</p>
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
                    <textarea id="message" name="message" required maxlength="6000" placeholder="Demo talebinizle ilgili kısa notunuz">{{ old('message') }}</textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cta btn-ghost" @click="demoOpen = false">Vazgeç</button>
                    <button type="submit" class="cta cta-primary">Talebi Gönder</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>