<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - yazılım ve eğitim odaklı modern platform.">
    <title>Bilişim Kod</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root{
            --bg:#050816;
            --bg2:#0b1730;
            --panel:rgba(255,255,255,.07);
            --panel-strong:rgba(255,255,255,.12);
            --border:rgba(148,163,184,.20);
            --text:#eef4ff;
            --muted:#9cb0cb;
            --brand:#4f8cff;
            --brand2:#8b5cf6;
            --brand3:#19d3ff;
            --shadow:0 30px 90px rgba(4,10,24,.45);
        }
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--text);background:
            radial-gradient(circle at 12% 10%, rgba(79,140,255,.20), transparent 28%),
            radial-gradient(circle at 88% 18%, rgba(25,211,255,.14), transparent 26%),
            linear-gradient(160deg,var(--bg) 0%,var(--bg2) 58%,#04060d 100%);overflow-x:hidden}
        a{text-decoration:none;color:inherit}
        .page{min-height:100vh;position:relative;overflow:hidden}
        .page::before,.page::after{content:"";position:absolute;border-radius:999px;filter:blur(60px);pointer-events:none}
        .page::before{width:280px;height:280px;left:-90px;top:-80px;background:rgba(79,140,255,.22)}
        .page::after{width:360px;height:360px;right:-120px;bottom:-160px;background:rgba(139,92,246,.14)}
        .grain{position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);background-size:18px 18px;opacity:.26;pointer-events:none}
        .container{width:min(1180px,calc(100% - 32px));margin:0 auto;position:relative;z-index:1}
        .nav{display:flex;align-items:center;justify-content:center;padding:22px 0 10px}
        .brand{display:flex;align-items:center;gap:14px;min-width:0}
        .brand-mark{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;overflow:hidden;background:linear-gradient(135deg,rgba(255,255,255,.16),rgba(255,255,255,.05));border:1px solid rgba(255,255,255,.12);box-shadow:0 14px 36px rgba(0,0,0,.22);backdrop-filter:blur(10px)}
        .brand-mark img{width:100%;height:100%;object-fit:cover;display:block}
        .brand-text{display:none}
        .hero{padding:32px 0 20px;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(300px,.95fr);gap:28px;align-items:center}
        h1{margin:0 0 14px;font-size:clamp(46px,7vw,76px);line-height:.94;letter-spacing:-.05em}
        .lead{max-width:620px;margin:0;color:var(--muted);font-size:clamp(16px,1.8vw,19px);line-height:1.8}
        .cta-row{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}
        .cta{display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:0 20px;border-radius:16px;font-weight:900;border:1px solid transparent;transition:transform .2s ease,box-shadow .2s ease,background .2s ease,border-color .2s ease}
        .cta-primary{background:linear-gradient(135deg,var(--brand),var(--brand3));color:#fff;box-shadow:0 20px 40px rgba(79,140,255,.30)}
        .cta-secondary{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14);color:#fff}
        .cta:hover{transform:translateY(-1px)}
        .hero-card{position:relative;padding:24px;border-radius:30px;background:linear-gradient(180deg,rgba(255,255,255,.12),rgba(255,255,255,.05));border:1px solid var(--border);box-shadow:var(--shadow);backdrop-filter:blur(18px)}
        .hero-card::before{content:"";position:absolute;inset:14px;border-radius:22px;border:1px solid rgba(255,255,255,.08);pointer-events:none}
        .hero-visual{position:relative;min-height:390px;border-radius:22px;overflow:hidden;background:
            radial-gradient(circle at 25% 20%, rgba(79,140,255,.35), transparent 22%),
            radial-gradient(circle at 78% 28%, rgba(139,92,246,.30), transparent 24%),
            linear-gradient(180deg,rgba(3,8,20,.92),rgba(9,18,38,.84));}
        .hero-visual::before,
        .hero-visual::after{
            content:"";
            position:absolute;
            inset:auto;
            border-radius:999px;
            filter:blur(10px);
        }
        .hero-visual::before{width:220px;height:220px;background:rgba(25,211,255,.12);left:-60px;top:-40px}
        .hero-visual::after{width:260px;height:260px;background:rgba(139,92,246,.14);right:-80px;bottom:-90px}
        .logo-orbit{position:absolute;inset:24px;display:grid;place-items:center}
        .logo-orbit img{width:min(100%,420px);height:auto;display:block;filter:drop-shadow(0 18px 40px rgba(0,0,0,.35))}
        .orbit-chip{position:absolute;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(12px);font-size:12px;font-weight:800;color:#e8f2ff;box-shadow:0 12px 22px rgba(0,0,0,.16)}
        .orbit-chip.one{left:16px;top:18px}
        .orbit-chip.two{right:18px;top:34px}
        .orbit-chip.three{left:26px;bottom:28px}
        .orbit-chip.four{right:18px;bottom:22px}
        .stats{position:absolute;left:24px;right:24px;bottom:18px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
        .stat{padding:12px 14px;border-radius:18px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10)}
        .stat strong{display:block;font-size:18px}
        .stat span{display:block;color:#bed0e6;font-size:12px;margin-top:3px}
        .section{padding:20px 0 12px}
        .section-head{display:flex;justify-content:space-between;align-items:end;gap:16px;margin-bottom:18px}
        .section-head h2{margin:0;font-size:clamp(24px,3vw,34px);line-height:1.1}
        .section-head p{margin:0;max-width:620px;color:var(--muted);line-height:1.75}
        .feature-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
        .feature{padding:18px;border-radius:22px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);box-shadow:0 16px 32px rgba(0,0,0,.12);transition:transform .2s ease,border-color .2s ease,background .2s ease}
        .feature:hover{transform:translateY(-4px);border-color:rgba(79,140,255,.34);background:rgba(255,255,255,.08)}
        .feature .icon{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(79,140,255,.24),rgba(25,211,255,.18));border:1px solid rgba(255,255,255,.12);margin-bottom:14px}
        .feature strong{display:block;font-size:16px;margin-bottom:8px}
        .feature p{margin:0;color:var(--muted);line-height:1.7;font-size:14px}
        .trust{display:grid;grid-template-columns:1.1fr .9fr;gap:14px;padding:6px 0 34px}
        .panel{padding:22px;border-radius:24px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);backdrop-filter:blur(16px);box-shadow:0 16px 40px rgba(0,0,0,.18)}
        .panel h3{margin:0 0 10px;font-size:22px}
        .panel p{margin:0;color:var(--muted);line-height:1.8}
        .bullets{display:grid;gap:10px;margin-top:16px}
        .bullet{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:16px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
        .bullet i{width:24px;height:24px;border-radius:999px;background:linear-gradient(135deg,var(--brand),var(--brand3));display:grid;place-items:center;font-style:normal;font-weight:900;font-size:12px;flex:none;color:#fff}
        footer{padding:14px 0 24px;color:var(--muted);font-size:13px}
        .footer-line{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,.10);padding-top:14px}
        .modal-backdrop{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(5,8,22,.62);z-index:60}
        .modal-backdrop.is-open{display:flex}
        .modal{width:min(560px,100%);border-radius:24px;padding:22px;background:linear-gradient(180deg,rgba(12,19,38,.96),rgba(11,17,30,.96));border:1px solid rgba(255,255,255,.12);box-shadow:var(--shadow)}
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
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
        @media (max-width: 980px){
            .hero,.trust{grid-template-columns:1fr}
            .hero-card{order:-1}
            .feature-grid{grid-template-columns:1fr}
        }
        @media (max-width: 720px){
            .container{width:min(100% - 22px,1180px)}
            .nav{padding:16px 0 8px}
            .brand-mark{width:42px;height:42px}
            h1{font-size:clamp(38px,12vw,58px)}
            .cta-row{flex-direction:column}
            .cta{width:100%}
            .hero-card{padding:16px;border-radius:24px}
            .hero-visual{min-height:320px}
            .stats{grid-template-columns:1fr}
            .section-head{flex-direction:column;align-items:flex-start}
            .footer-line{flex-direction:column;align-items:flex-start}
            .modal{padding:18px}
        }
    </style>
</head>
<body>
<div class="page" x-data="{ demoOpen:false }">
    <div class="grain"></div>

    <header class="container">
        <div class="nav">
            <a href="{{ url('/') }}" class="brand" aria-label="Bilişim Kod ana sayfa">
                <span class="brand-mark">
                    <img src="{{ asset('images/bilisim-kod-logo.jpg') }}" alt="Bilişim Kod logo">
                </span>
            </a>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <div>
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
                    <span class="orbit-chip one">Kodlama</span>
                    <span class="orbit-chip two">Eğitim</span>
                    <span class="orbit-chip three">Takip</span>
                    <span class="orbit-chip four">Panel</span>

                    <div class="stats">
                        <div class="stat">
                            <strong>Modern UI</strong>
                            <span>Kurumsal ve premium hissiyat</span>
                        </div>
                        <div class="stat">
                            <strong>Responsive</strong>
                            <span>Mobilde de güçlü görünüm</span>
                        </div>
                        <div class="stat">
                            <strong>Hızlı Erişim</strong>
                            <span>Login ve demo çağrısı net</span>
                        </div>
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
                    <div class="bullet"><i>✓</i><div><strong>Teknoloji hissi</strong><p style="margin:4px 0 0;color:var(--muted)">Cam efektler, glow ve derinlik mevcut.</p></div></div>
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
