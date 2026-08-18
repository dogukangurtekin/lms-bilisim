<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - bilişim teknolojileri ve yazılım öğrenimini sade, güçlü ve etkileşimli hale getiren modern platform.">
    <title>Bilişim Kod</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root{
            --bg:#07111f;
            --bg2:#0b1d36;
            --panel:rgba(255,255,255,.08);
            --panel-strong:rgba(255,255,255,.12);
            --border:rgba(148,163,184,.24);
            --text:#e5eefc;
            --muted:#9db0ca;
            --brand:#4f8cff;
            --brand-2:#1dd3ff;
            --accent:#7cfcf5;
            --shadow:0 24px 80px rgba(3,8,20,.38);
        }
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:
            radial-gradient(circle at 20% 10%, rgba(77,145,255,.25), transparent 30%),
            radial-gradient(circle at 80% 20%, rgba(29,211,255,.20), transparent 28%),
            linear-gradient(160deg,var(--bg) 0%,var(--bg2) 52%,#050b14 100%);color:var(--text)}
        body{overflow-x:hidden}
        a{text-decoration:none;color:inherit}
        .page{position:relative;min-height:100vh;overflow:hidden}
        .grain{position:absolute;inset:0;opacity:.08;background-image:radial-gradient(rgba(255,255,255,.8) 1px, transparent 1px);background-size:18px 18px;pointer-events:none}
        .glow{position:absolute;border-radius:999px;filter:blur(40px);pointer-events:none}
        .glow.one{width:260px;height:260px;background:rgba(77,145,255,.35);top:10%;left:-80px}
        .glow.two{width:320px;height:320px;background:rgba(29,211,255,.18);bottom:12%;right:-120px}
        .container{width:min(1180px,calc(100% - 40px));margin:0 auto;position:relative;z-index:1}
        .nav{display:flex;align-items:center;justify-content:space-between;padding:22px 0}
        .brand{display:flex;align-items:center;gap:14px;font-weight:900;letter-spacing:.01em}
        .brand-mark{width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,var(--brand),var(--brand-2));box-shadow:0 12px 30px rgba(79,140,255,.32);display:grid;place-items:center;color:#fff;font-size:20px}
        .brand-text{display:grid;line-height:1.05}
        .brand-text span:first-child{font-size:18px}
        .brand-text small{color:var(--muted);font-size:12px;font-weight:600}
        .nav-actions{display:flex;align-items:center;gap:12px}
        .login-btn{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 18px;border-radius:14px;border:1px solid rgba(255,255,255,.18);background:linear-gradient(135deg,rgba(255,255,255,.16),rgba(255,255,255,.08));color:#fff;font-weight:800;box-shadow:0 16px 40px rgba(0,0,0,.18);transition:transform .2s ease,box-shadow .2s ease,background .2s ease}
        .login-btn:hover{transform:translateY(-1px);box-shadow:0 20px 46px rgba(0,0,0,.26);background:linear-gradient(135deg,rgba(255,255,255,.22),rgba(255,255,255,.10))}
        .hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(340px,.85fr);gap:34px;align-items:center;padding:42px 0 32px}
        .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border:1px solid rgba(29,211,255,.28);border-radius:999px;background:rgba(255,255,255,.05);color:#dff6ff;font-size:13px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
        h1{margin:18px 0 14px;font-size:clamp(40px,7vw,72px);line-height:.95;letter-spacing:-.04em}
        .lead{max-width:640px;font-size:clamp(17px,2vw,20px);line-height:1.75;color:var(--muted);margin:0}
        .cta-row{display:flex;flex-wrap:wrap;gap:14px;margin-top:30px}
        .cta-primary,.cta-secondary{display:inline-flex;align-items:center;justify-content:center;min-height:54px;padding:0 22px;border-radius:16px;font-weight:800;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
        .cta-primary{background:linear-gradient(135deg,var(--brand),var(--brand-2));color:#fff;box-shadow:0 18px 40px rgba(79,140,255,.34)}
        .cta-secondary{border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.05);color:#fff}
        .cta-primary:hover,.cta-secondary:hover{transform:translateY(-1px)}
        .hero-aside{position:relative;min-height:520px;display:grid;place-items:center}
        .orb{position:absolute;border-radius:999px;filter:blur(10px);opacity:.85}
        .orb.a{width:180px;height:180px;top:18px;right:20px;background:rgba(29,211,255,.16);animation:float 8s ease-in-out infinite}
        .orb.b{width:120px;height:120px;bottom:60px;left:10px;background:rgba(124,252,245,.14);animation:float 10s ease-in-out infinite reverse}
        .tech-panel{width:min(100%,470px);padding:22px;border-radius:28px;border:1px solid rgba(255,255,255,.14);background:linear-gradient(180deg,rgba(255,255,255,.13),rgba(255,255,255,.06));backdrop-filter:blur(18px);box-shadow:var(--shadow)}
        .tech-card{border-radius:22px;padding:22px;background:linear-gradient(180deg,rgba(7,17,31,.92),rgba(8,23,44,.74));border:1px solid rgba(255,255,255,.08);min-height:360px;display:grid;gap:14px}
        .chip-row{display:flex;gap:8px;flex-wrap:wrap}
        .chip{padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.08);color:#d8e8ff;font-size:12px;font-weight:700;border:1px solid rgba(255,255,255,.08)}
        .mock-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:6px}
        .mock{padding:16px;border-radius:18px;background:linear-gradient(180deg,rgba(79,140,255,.16),rgba(255,255,255,.04));border:1px solid rgba(255,255,255,.08)}
        .mock strong{display:block;font-size:17px;margin-bottom:6px}
        .mock p{margin:0;color:var(--muted);font-size:13px;line-height:1.5}
        .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:auto}
        .stat{padding:14px;border-radius:18px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08)}
        .stat strong{display:block;font-size:20px}
        .stat span{display:block;color:var(--muted);font-size:12px;margin-top:4px}
        .section{padding:26px 0 10px}
        .section-head{display:flex;justify-content:space-between;align-items:end;gap:18px;margin-bottom:18px}
        .section-head h2{margin:0;font-size:clamp(24px,3vw,34px);line-height:1.1}
        .section-head p{margin:0;color:var(--muted);max-width:620px;line-height:1.7}
        .features{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
        .feature{position:relative;padding:22px;border-radius:22px;background:linear-gradient(180deg,rgba(255,255,255,.10),rgba(255,255,255,.06));border:1px solid var(--border);box-shadow:0 16px 36px rgba(0,0,0,.16);transition:transform .2s ease,border-color .2s ease,background .2s ease}
        .feature:hover{transform:translateY(-4px);border-color:rgba(29,211,255,.34);background:linear-gradient(180deg,rgba(255,255,255,.14),rgba(255,255,255,.07))}
        .feature .icon{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(79,140,255,.22),rgba(29,211,255,.18));border:1px solid rgba(255,255,255,.1);margin-bottom:14px;font-size:20px}
        .feature h3{margin:0 0 8px;font-size:18px}
        .feature p{margin:0;color:var(--muted);line-height:1.65;font-size:14px}
        .lower{display:grid;grid-template-columns:1.25fr .75fr;gap:18px;align-items:start;padding:28px 0 42px}
        .info-card,.trust-card{padding:24px;border-radius:24px;background:linear-gradient(180deg,rgba(255,255,255,.09),rgba(255,255,255,.05));border:1px solid var(--border);box-shadow:var(--shadow)}
        .info-card p,.trust-card p{color:var(--muted);line-height:1.8;margin:0}
        .bullets{display:grid;gap:12px;margin-top:18px}
        .bullet{display:flex;gap:12px;align-items:flex-start;padding:14px 16px;border-radius:18px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
        .bullet i{width:26px;height:26px;border-radius:999px;background:linear-gradient(135deg,var(--brand),var(--brand-2));display:grid;place-items:center;color:#fff;font-style:normal;font-size:12px;font-weight:900;flex:none}
        footer{padding:18px 0 30px;color:var(--muted);font-size:14px}
        .footer-line{display:flex;justify-content:space-between;gap:16px;align-items:center;border-top:1px solid rgba(255,255,255,.08);padding-top:18px;flex-wrap:wrap}
        [x-cloak]{display:none !important}
        .mobile-menu{display:none}
        .mobile-menu-btn{display:none}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
        @media (max-width: 1024px){
            .hero,.lower{grid-template-columns:1fr}
            .hero-aside{min-height:auto}
            .features{grid-template-columns:repeat(2,minmax(0,1fr))}
        }
        @media (max-width: 768px){
            .container{width:min(100% - 24px,1180px)}
            .nav{padding:16px 0}
            .mobile-menu-btn{display:inline-flex}
            .mobile-menu{display:grid;gap:10px;padding:14px 0 0}
            .mobile-menu a{padding:12px 14px;border-radius:14px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
            .desktop-actions{display:none}
            .hero{padding:26px 0 22px}
            .cta-row{gap:10px}
            .cta-primary,.cta-secondary{width:100%}
            .tech-panel{padding:16px}
            .tech-card{min-height:auto;padding:18px}
            .mock-grid,.stats,.features{grid-template-columns:1fr}
            .section-head{align-items:start;flex-direction:column}
            .lower{padding-bottom:28px}
        }
    </style>
</head>
<body>
<div class="page" x-data="{ mobileMenu: false, activeFeature: 0 }">
    <div class="grain"></div>
    <div class="glow one"></div>
    <div class="glow two"></div>

    <header class="container">
        <div class="nav">
            <a href="{{ url('/') }}" class="brand" aria-label="Bilişim Kod ana sayfa">
                <span class="brand-mark">BK</span>
                <span class="brand-text">
                    <span>Bilişim Kod</span>
                    <small>Yazılım ve bilişim platformu</small>
                </span>
            </a>

            <div class="nav-actions">
                <a class="login-btn desktop-actions" href="{{ route('login') }}">Login</a>
                <button type="button" class="login-btn mobile-menu-btn" @click="mobileMenu = !mobileMenu" x-text="mobileMenu ? 'Kapat' : 'Menü'"></button>
            </div>
        </div>

        <nav class="mobile-menu" x-cloak x-show="mobileMenu" x-transition.opacity.duration.180ms>
            <a href="{{ route('login') }}">Login</a>
            <a href="#ozellikler">Özellikler</a>
            <a href="#hakkinda">Hakkında</a>
        </nav>
    </header>

    <main class="container">
        <section class="hero">
            <div>
                <span class="eyebrow">Teknoloji • Yazılım • Eğitim</span>
                <h1>Bilişim Kod</h1>
                <p class="lead">Bilişim teknolojileri ve yazılım öğrenimini sade, güçlü ve etkileşimli hale getiren modern platform. Öğrenciler, öğretmenler ve kurumlar için düzenli, kurumsal ve güven veren bir deneyim sunar.</p>

                <div class="cta-row">
                    <a href="{{ route('login') }}" class="cta-primary">Hemen Başla</a>
                    <a href="#hakkinda" class="cta-secondary">Daha Fazla Bilgi</a>
                </div>
            </div>

            <div class="hero-aside" aria-hidden="true">
                <div class="orb a"></div>
                <div class="orb b"></div>
                <div class="tech-panel">
                    <div class="tech-card">
                        <div class="chip-row">
                            <span class="chip">Laravel</span>
                            <span class="chip">Alpine.js</span>
                            <span class="chip">Mobil Uyum</span>
                        </div>

                        <div class="mock-grid">
                            <div class="mock">
                                <strong>Canlı İçerik</strong>
                                <p>Etkinlikler, dersler ve ilerleme takibi tek akışta.</p>
                            </div>
                            <div class="mock">
                                <strong>Kurumsal Panel</strong>
                                <p>Öğretmen ve öğrenci süreçleri düzenli bir yapıda.</p>
                            </div>
                            <div class="mock">
                                <strong>Akıllı Takip</strong>
                                <p>Performans, görev ve içerik yönetimi sade görünür.</p>
                            </div>
                            <div class="mock">
                                <strong>Hızlı Erişim</strong>
                                <p>Login butonu her zaman görünür ve erişilebilir.</p>
                            </div>
                        </div>

                        <div class="stats">
                            <div class="stat">
                                <strong>+3</strong>
                                <span>Öne çıkan özellik</span>
                            </div>
                            <div class="stat">
                                <strong>24/7</strong>
                                <span>Erişilebilir yapı</span>
                            </div>
                            <div class="stat">
                                <strong>100%</strong>
                                <span>Responsive deneyim</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="ozellikler">
            <div class="section-head">
                <div>
                    <h2>Öğrenmeyi sadeleştiren modern yapı</h2>
                    <p>Kurumsal görünüm, net yönlendirme ve eğitsel akış bir arada. Bilişim Kod, sınıf içi ve çevrim içi kullanım için dengeli bir ön yüz sunar.</p>
                </div>
            </div>

            <div class="features">
                @php
                    $features = [
                        ['icon' => '↗', 'title' => 'Etkileşimli Öğrenme', 'text' => 'Dersler, görevler ve içerikler kullanıcının dikkatini dağıtmadan akıcı biçimde sunulur.'],
                        ['icon' => '{}', 'title' => 'Kod Mantığı', 'text' => 'Teknik düşünmeyi destekleyen sade arayüzlerle yazılım kavramları görünür hale gelir.'],
                        ['icon' => '◎', 'title' => 'Kolay Kullanım', 'text' => 'Mobil ve masaüstünde okunaklı, hızlı ve pratik bir deneyim sağlar.'],
                        ['icon' => '★', 'title' => 'Düzenli İçerik', 'text' => 'Öğrenme akışı, yönetim ve sunum yapısı profesyonel bir kurumsal düzende tutulur.'],
                    ];
                @endphp
                @foreach($features as $index => $feature)
                    <article class="feature" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
                        <div class="icon" :style="hovered ? 'transform:translateY(-1px) scale(1.03);' : ''">{{ $feature['icon'] }}</div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="lower" id="hakkinda">
            <div class="info-card">
                <span class="eyebrow">Neden Bilişim Kod?</span>
                <h2 style="margin:14px 0 12px;font-size:clamp(24px,3vw,34px);line-height:1.1;">Güven veren, modern ve eğitim odaklı bir giriş noktası</h2>
                <p>Bilişim Kod; yazılım öğrenimini karmaşadan uzak, düzenli ve görsel olarak güçlü bir deneyime dönüştürmek için tasarlandı. Kurumun dijital yüzünü profesyonel biçimde gösterir.</p>
                <div class="bullets">
                    <div class="bullet"><i>✓</i><div><strong>Net erişim</strong><p>Login bağlantısı üstte görünür, kullanıcıyı doğrudan doğru akışa yönlendirir.</p></div></div>
                    <div class="bullet"><i>✓</i><div><strong>Mobil uyum</strong><p>Kartlar ve bloklar telefon ekranında da dengeli görünür.</p></div></div>
                    <div class="bullet"><i>✓</i><div><strong>Kurumsal ton</strong><p>Renk, boşluk ve tipografi dili teknoloji odaklı bir marka algısı üretir.</p></div></div>
                </div>
            </div>

            <aside class="trust-card">
                <span class="eyebrow">Kısa Notlar</span>
                <p style="margin-top:14px;">Bu ana sayfa demo hissi vermeden, gerçek bir eğitim platformunun giriş ekranı gibi çalışır.</p>
                <div class="bullets">
                    <div class="bullet"><i>1</i><div><strong>Hızlı açılış</strong><p>Tek sayfa yapısı sayesinde hafif ve akıcıdır.</p></div></div>
                    <div class="bullet"><i>2</i><div><strong>Temiz yapı</strong><p>Gereksiz bileşenler yerine net bir ön izleme sunar.</p></div></div>
                    <div class="bullet"><i>3</i><div><strong>İleri kullanım</strong><p>İleride içerik, duyuru ve modül alanları kolayca genişletilebilir.</p></div></div>
                </div>
            </aside>
        </section>
    </main>

    <footer>
        <div class="container footer-line">
            <div>© {{ date('Y') }} Bilişim Kod</div>
            <div>Teknoloji, yazılım ve eğitim için modern platform</div>
        </div>
    </footer>
</div>
</body>
</html>
