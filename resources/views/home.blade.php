<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - bilişim teknolojileri ve yazılım için modern platform.">
    <title>Bilişim Kod</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root{
            --bg:#050914;
            --panel:rgba(255,255,255,.06);
            --border:rgba(148,163,184,.16);
            --text:#eff6ff;
            --muted:#a8b6cc;
            --brand:#3b82f6;
            --brand2:#a855f7;
        }
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:radial-gradient(circle at top,rgba(59,130,246,.18),transparent 28%),linear-gradient(180deg,#050914 0%,#081224 100%);color:var(--text)}
        a{text-decoration:none;color:inherit}
        .page{min-height:100vh;display:flex;flex-direction:column;position:relative;overflow:hidden}
        .page::before,.page::after{content:"";position:absolute;border-radius:999px;filter:blur(50px);pointer-events:none}
        .page::before{width:260px;height:260px;background:rgba(59,130,246,.20);top:-80px;left:-60px}
        .page::after{width:300px;height:300px;background:rgba(168,85,247,.12);right:-100px;bottom:-120px}
        .container{width:min(1080px,calc(100% - 28px));margin:0 auto;position:relative;z-index:1}
        .nav{display:flex;justify-content:flex-end;align-items:center;padding:20px 0}
        .login{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 18px;border-radius:14px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);font-weight:800;transition:transform .2s ease,background .2s ease}
        .login:hover{transform:translateY(-1px);background:rgba(255,255,255,.10)}
        .hero{flex:1;display:grid;place-items:center;padding:24px 0 40px}
        .center{width:min(680px,100%);text-align:center}
        .logo-wrap{display:grid;place-items:center;margin:0 auto 24px;width:min(520px,100%);padding:18px;border-radius:28px;background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.04));border:1px solid var(--border);box-shadow:0 20px 60px rgba(0,0,0,.28)}
        .logo{width:min(100%,460px);height:auto;display:block;border-radius:20px}
        h1{margin:0 0 12px;font-size:clamp(36px,6vw,58px);line-height:1.02;letter-spacing:-.04em}
        .lead{margin:0 auto;max-width:620px;color:var(--muted);font-size:clamp(16px,2vw,19px);line-height:1.8}
        .actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px;margin-top:26px}
        .btn{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:0 20px;border-radius:14px;font-weight:800;border:1px solid transparent;transition:transform .2s ease,border-color .2s ease,background .2s ease}
        .btn-primary{background:linear-gradient(135deg,var(--brand),var(--brand2));box-shadow:0 18px 40px rgba(59,130,246,.26)}
        .btn-secondary{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.12)}
        .btn:hover{transform:translateY(-1px)}
        .strip{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:22px}
        .chip{padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#dbe7fb;font-size:12px;font-weight:700}
        footer{padding:16px 0 24px;color:var(--muted);font-size:13px}
        .footer-line{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,.08);padding-top:14px}
        [x-cloak]{display:none !important}
        .mobile-menu{display:none}
        .mobile-menu-btn{display:none}
        @media (max-width: 720px){
            .container{width:min(100% - 20px,1080px)}
            .nav{justify-content:space-between}
            .mobile-menu-btn{display:inline-flex}
            .desktop-login{display:none}
            .mobile-menu{display:grid;gap:8px;padding-bottom:8px}
            .mobile-menu a{padding:11px 14px;border-radius:14px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
            .logo-wrap{padding:14px}
            .actions{flex-direction:column}
            .btn{width:100%}
            .footer-line{flex-direction:column;align-items:flex-start}
        }
    </style>
</head>
<body>
<div class="page" x-data="{ mobileMenu: false }">
    <header class="container">
        <div class="nav">
            <a href="{{ route('login') }}" class="login desktop-login">Login</a>
            <button type="button" class="login mobile-menu-btn" @click="mobileMenu = !mobileMenu" x-text="mobileMenu ? 'Kapat' : 'Menü'"></button>
        </div>
        <nav class="mobile-menu" x-cloak x-show="mobileMenu" x-transition.opacity.duration.150ms>
            <a href="{{ route('login') }}">Login</a>
        </nav>
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
            </div>
            <div class="strip" aria-hidden="true">
                <span class="chip">Teknoloji</span>
                <span class="chip">Yazılım</span>
                <span class="chip">Eğitim</span>
            </div>
        </section>
    </main>

    <section class="container" id="hakkinda" style="padding-bottom:26px;">
        <div class="footer-line" style="border-top:1px solid rgba(255,255,255,.08);padding-top:16px;">
            <div>Bilişim Kod, eğitim odaklı kurumsal bir dijital giriş ekranıdır.</div>
            <div>Login butonu üstte, logo merkezde, yapı sade tutuldu.</div>
        </div>
    </section>

    <footer>
        <div class="container footer-line">
            <div>© {{ date('Y') }} Bilişim Kod</div>
            <div>Tek sayfa, temiz ve profesyonel giriş deneyimi</div>
        </div>
    </footer>
</div>
</body>
</html>
