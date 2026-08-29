<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - okullar için kodlama, robotik ve yapay zekâ müfredatını tek platformda birleştiren modern eğitim altyapısı.">
    <title>Bilişim Kod | Okullar için Kodlama &amp; Yapay Zekâ Platformu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root{
            --paper:#F7F6F2;
            --surface:#FFFFFF;
            --ink:#16182B;
            --ink-soft:#585A72;
            --line:#E4E1D8;
            --violet:#5B3DF5;
            --violet-ink:#3E28B8;
            --violet-tint:#EEEBFD;
            --signal:#FF7A45;
            --signal-tint:#FFEEE4;
            --mint:#0EA57A;
            --shadow-sm:0 1px 2px rgba(22,24,43,.04);
            --shadow-md:0 12px 32px -14px rgba(22,24,43,.16);
            --shadow-lg:0 30px 70px -24px rgba(22,24,43,.28);
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        @media (prefers-reduced-motion: reduce){
            html{scroll-behavior:auto}
            *{animation-duration:.001ms !important;animation-iteration-count:1 !important;transition-duration:.001ms !important;scroll-behavior:auto !important}
        }
        html,body{margin:0;min-height:100%;background:var(--paper);color:var(--ink);font-family:'Inter',ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;-webkit-font-smoothing:antialiased}
        a{color:inherit}
        img{max-width:100%;display:block}
        :focus-visible{outline:2.5px solid var(--violet);outline-offset:3px;border-radius:6px}
        .container{width:min(1160px,calc(100% - 40px));margin:0 auto}
        .eyebrow{display:inline-flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace;font-size:12.5px;font-weight:500;letter-spacing:.02em;color:var(--violet-ink);background:var(--violet-tint);border:1px solid rgba(91,61,245,.16);padding:6px 12px 6px 10px;border-radius:999px}
        .eyebrow::before{content:"";width:6px;height:6px;border-radius:999px;background:var(--signal)}
        h1,h2,h3{font-family:'Space Grotesk',ui-sans-serif,sans-serif;letter-spacing:-.02em;margin:0}

        /* ---------- Nav ---------- */
        .nav{position:sticky;top:0;z-index:40;background:rgba(247,246,242,.86);backdrop-filter:blur(10px);border-bottom:1px solid transparent;transition:border-color .2s ease,box-shadow .2s ease}
        .nav.is-scrolled{border-bottom-color:var(--line);box-shadow:0 1px 0 rgba(22,24,43,.02)}
        .nav-row{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 0}
        .brand{display:flex;align-items:center;gap:10px;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:17px}
        .brand img{width:34px;height:34px;border-radius:9px;object-fit:cover;box-shadow:var(--shadow-sm)}
        .nav-links{display:flex;align-items:center;gap:30px;font-size:14.5px;font-weight:500;color:var(--ink-soft)}
        .nav-links a{text-decoration:none;transition:color .15s ease}
        .nav-links a:hover{color:var(--ink)}
        .nav-cta{display:flex;align-items:center;gap:10px}
        @media (max-width:760px){.nav-links{display:none}}

        /* ---------- Buttons ---------- */
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:48px;padding:0 22px;border-radius:12px;font-size:15px;font-weight:600;border:1px solid transparent;cursor:pointer;text-decoration:none;transition:transform .16s ease,box-shadow .16s ease,background .16s ease,border-color .16s ease}
        .btn-primary{background:var(--ink);color:#fff;box-shadow:var(--shadow-sm)}
        .btn-primary:hover{background:var(--violet-ink);transform:translateY(-1px);box-shadow:var(--shadow-md)}
        .btn-ghost{background:var(--surface);color:var(--ink);border-color:var(--line)}
        .btn-ghost:hover{border-color:var(--ink);transform:translateY(-1px)}
        .btn-sm{height:40px;padding:0 16px;font-size:14px;border-radius:10px}

        /* ---------- Hero ---------- */
        .hero{padding:76px 0 40px;display:grid;grid-template-columns:minmax(0,1fr) minmax(360px,.86fr);gap:56px;align-items:center}
        .hero h1{font-size:clamp(36px,4.6vw,58px);line-height:1.05;margin:20px 0 22px}
        .hero h1 em{font-style:normal;color:var(--violet)}
        .hero-lead{max-width:520px;margin:0;color:var(--ink-soft);font-size:17.5px;line-height:1.72}
        .hero-cta{display:flex;flex-wrap:wrap;gap:12px;margin-top:32px}
        .hero-stages{display:flex;gap:8px;margin-top:26px;flex-wrap:wrap}
        .stage-chip{font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:500;color:var(--ink-soft);background:var(--surface);border:1px solid var(--line);padding:6px 11px;border-radius:8px}

        /* ---------- Hero visual: "ders defteri" panel ---------- */
        .ide-card{position:relative;background:var(--surface);border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow-lg);overflow:hidden}
        .ide-topbar{display:flex;align-items:center;gap:8px;padding:13px 16px;border-bottom:1px solid var(--line);background:#FBFAF7}
        .ide-dot{width:9px;height:9px;border-radius:999px;background:var(--line)}
        .ide-title{margin-left:8px;font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--ink-soft)}
        .ide-body{padding:22px 20px 24px;font-family:'JetBrains Mono',monospace;font-size:13.5px;line-height:1.9}
        .ide-line{display:grid;grid-template-columns:22px 1fr;gap:14px;color:var(--ink-soft);opacity:0;transform:translateY(4px);animation:lineIn .5s ease forwards}
        .ide-line span:first-child{text-align:right;color:#B8B4A6}
        .ide-line .key{color:var(--violet-ink);font-weight:600}
        .ide-line .str{color:var(--mint)}
        .ide-line .xp{display:inline-flex;align-items:center;gap:6px;margin-left:6px;font-size:11.5px;font-weight:600;color:#B45309;background:var(--signal-tint);padding:2px 8px;border-radius:999px}
        .cursor{display:inline-block;width:7px;height:15px;background:var(--signal);margin-left:2px;vertical-align:-2px;animation:blink 1s step-end infinite}
        @keyframes lineIn{to{opacity:1;transform:translateY(0)}}
        @keyframes blink{50%{opacity:0}}
        .ide-footer{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid var(--line);background:#FBFAF7}
        .progress-track{flex:1;height:6px;border-radius:999px;background:var(--line);overflow:hidden;margin-right:16px}
        .progress-fill{height:100%;width:0%;border-radius:999px;background:linear-gradient(90deg,var(--violet),var(--signal));animation:fillProgress 1.6s 1.4s ease forwards}
        @keyframes fillProgress{to{width:64%}}
        .ide-badge{font-family:'JetBrains Mono',monospace;font-size:11.5px;font-weight:600;color:var(--violet-ink);white-space:nowrap}
        .ide-float{position:absolute;background:var(--surface);border:1px solid var(--line);border-radius:12px;box-shadow:var(--shadow-md);padding:10px 13px;font-size:12.5px;font-weight:600;display:flex;align-items:center;gap:8px}
        .ide-float.f1{top:-16px;right:22px}
        .ide-float.f2{bottom:-14px;left:-18px}
        .ide-float .dot{width:8px;height:8px;border-radius:999px;background:var(--mint)}

        /* ---------- Modules ---------- */
        .section{padding:88px 0}
        .section-head{max-width:620px;margin-bottom:48px}
        .section-kicker{font-family:'JetBrains Mono',monospace;font-size:12.5px;font-weight:500;color:var(--violet-ink);letter-spacing:.03em;margin-bottom:12px;display:block}
        .section-head h2{font-size:clamp(28px,3.2vw,38px);line-height:1.15;margin-bottom:14px}
        .section-head p{color:var(--ink-soft);font-size:16px;line-height:1.7;margin:0}

        .modules{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1px;background:var(--line);border:1px solid var(--line);border-radius:20px;overflow:hidden}
        .module{background:var(--surface);padding:32px;position:relative;transition:background .18s ease}
        .module:hover{background:#FDFCFA}
        .module-num{font-family:'JetBrains Mono',monospace;font-size:13px;color:#B8B4A6;font-weight:600;display:block;margin-bottom:18px}
        .module h3{font-size:19px;margin-bottom:10px}
        .module p{color:var(--ink-soft);font-size:14.5px;line-height:1.7;margin:0}
        .module-tag{display:inline-block;margin-top:16px;font-family:'JetBrains Mono',monospace;font-size:11.5px;font-weight:500;color:var(--violet-ink);background:var(--violet-tint);padding:4px 10px;border-radius:999px}
        @media (max-width:760px){.modules{grid-template-columns:1fr}}

        /* ---------- Audience strip ---------- */
        .audience{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
        .audience-card{border:1px solid var(--line);border-radius:18px;padding:26px;background:var(--surface)}
        .audience-card .who{font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--ink-soft);margin-bottom:10px;display:block}
        .audience-card h4{font-family:'Space Grotesk',sans-serif;font-size:18px;margin:0 0 8px}
        .audience-card p{margin:0;color:var(--ink-soft);font-size:14px;line-height:1.65}
        @media (max-width:820px){.audience{grid-template-columns:1fr}}

        /* ---------- Final CTA ---------- */
        .cta-band{background:var(--ink);border-radius:24px;padding:56px 44px;display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;position:relative;overflow:hidden}
        .cta-band::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 82% 18%,rgba(91,61,245,.35),transparent 46%),radial-gradient(circle at 8% 90%,rgba(255,122,69,.22),transparent 40%)}
        .cta-band-copy{position:relative;max-width:480px}
        .cta-band-copy h2{color:#fff;font-size:clamp(24px,2.8vw,32px);margin-bottom:10px}
        .cta-band-copy p{color:#B9BAD1;margin:0;font-size:15.5px;line-height:1.65}
        .cta-band-actions{position:relative;display:flex;gap:12px;flex-wrap:wrap}
        .cta-band .btn-primary{background:#fff;color:var(--ink)}
        .cta-band .btn-primary:hover{background:var(--signal);color:#fff}
        .cta-band .btn-ghost{background:transparent;border-color:rgba(255,255,255,.22);color:#fff}
        .cta-band .btn-ghost:hover{border-color:#fff}

        /* ---------- Footer ---------- */
        footer{padding:36px 0 40px;border-top:1px solid var(--line);margin-top:20px}
        .footer-row{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;color:var(--ink-soft);font-size:13.5px}
        .footer-row .brand{font-size:14px}

        /* ---------- Modal ---------- */
        .modal-backdrop{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(22,24,43,.5);z-index:60}
        .modal-backdrop.is-open{display:flex}
        .modal{width:min(520px,100%);border-radius:20px;padding:28px;background:var(--surface);border:1px solid var(--line);box-shadow:var(--shadow-lg)}
        .modal h3{font-size:22px;margin-bottom:8px}
        .modal > p{margin:0 0 20px;color:var(--ink-soft);line-height:1.65;font-size:14.5px}
        .field{display:grid;gap:7px;margin-bottom:14px}
        .field label{font-size:13.5px;font-weight:600;color:var(--ink)}
        .field input,.field textarea{width:100%;border-radius:11px;border:1px solid var(--line);padding:12px 13px;background:var(--paper);color:var(--ink);font:inherit;font-size:14.5px}
        .field input:focus,.field textarea:focus{outline:none;border-color:var(--violet);box-shadow:0 0 0 3px var(--violet-tint)}
        .field textarea{min-height:110px;resize:vertical}
        .modal-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:16px}
        .alert{padding:13px 15px;border-radius:12px;border:1px solid transparent;font-weight:600;font-size:14px;line-height:1.5}
        .alert-success{background:#EAFBF4;border-color:#99E6C8;color:#0B6B4E}
        .alert-error{background:#FEECEA;border-color:#F5B3A9;color:#B3271B}

        @media (max-width:980px){
            .hero{grid-template-columns:1fr;padding-top:52px}
            .ide-card{order:-1;max-width:460px;margin:0 auto}
        }
        @media (max-width:640px){
            .container{width:min(100% - 32px,1160px)}
            .hero-cta{flex-direction:column;align-items:stretch}
            .hero-cta .btn{width:100%}
            .section{padding:60px 0}
            .cta-band{padding:36px 24px;flex-direction:column;align-items:flex-start}
            .cta-band-actions .btn{width:100%}
        }
    </style>
</head>
<body>
<div x-data="{ demoOpen:false, scrolled:false }" x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 8)">

    <nav class="nav" :class="{ 'is-scrolled': scrolled }">
        <div class="container nav-row">
            <a href="/" class="brand">
                <img src="{{ asset('images/bilisim-kod-logo.jpg') }}" alt="Bilişim Kod">
                Bilişim Kod
            </a>
            <div class="nav-links">
                <a href="#moduller">Modüller</a>
                <a href="#kimler-icin">Kimler için</a>
                <a href="#iletisim">İletişim</a>
            </div>
            <div class="nav-cta">
                <button type="button" class="btn btn-ghost btn-sm" @click="demoOpen = true">Demo Talep Et</button>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Giriş Yap</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="container hero">
            <div>
                <span class="eyebrow">İlkokul · Ortaokul · Lise için hazır müfredat</span>
                <h1>Kodlama dersini <em>ödev</em> olmaktan çıkarıp<br>alışkanlığa dönüştürün.</h1>
                <p class="hero-lead">Bilişim Kod; ders anlatımı, kodlama oyunları, canlı quiz, ödev takibi ve veli bildirimini tek panelde toplayan okul altyapısıdır. Öğretmen ders hazırlar, öğrenci oynayarak öğrenir, siz ilerlemeyi tek ekrandan izlersiniz.</p>
                <div class="hero-cta">
                    <a href="{{ route('login') }}" class="btn btn-primary">Hemen Başla</a>
                    <button type="button" class="btn btn-ghost" @click="demoOpen = true">Demo Talep Et</button>
                </div>
                <div class="hero-stages">
                    <span class="stage-chip">🧩 Blok Kodlama</span>
                    <span class="stage-chip">🎮 Kodlama Oyunları</span>
                    <span class="stage-chip">🏆 Canlı Quiz</span>
                    <span class="stage-chip">👨‍👩‍👧 Veli Bildirimi</span>
                </div>
            </div>

            <div class="ide-card" aria-hidden="true">
                <div class="ide-topbar">
                    <span class="ide-dot"></span><span class="ide-dot"></span><span class="ide-dot"></span>
                    <span class="ide-title">ders-akisi.bilisimkod</span>
                </div>
                <div class="ide-body">
                    <div class="ide-line" style="animation-delay:.1s"><span>1</span><span><span class="key">ders</span> = "Noktaları Birleştir · Bölüm 7"</span></div>
                    <div class="ide-line" style="animation-delay:.35s"><span>2</span><span><span class="key">kademe</span> = <span class="str">"Ortaokul"</span></span></div>
                    <div class="ide-line" style="animation-delay:.6s"><span>3</span><span><span class="key">öğrenci.tamamla</span>(ders) <span class="xp">+15 XP</span></span></div>
                    <div class="ide-line" style="animation-delay:.85s"><span>4</span><span><span class="key">rozet.kazan</span>(<span class="str">"Algoritma Ustası"</span>)</span></div>
                    <div class="ide-line" style="animation-delay:1.1s"><span>5</span><span><span class="key">veli.bildir</span>(<span class="str">"Bugün 2 ders tamamlandı"</span>)<span class="cursor"></span></span></div>
                </div>
                <div class="ide-footer">
                    <div class="progress-track"><div class="progress-fill"></div></div>
                    <span class="ide-badge">%64 tamamlandı</span>
                </div>
                <div class="ide-float f1"><span class="dot"></span>Canlı devam ediyor</div>
                <div class="ide-float f2">🔥 6 günlük seri</div>
            </div>
        </section>

        <section class="section container" id="moduller">
            <div class="section-head">
                <span class="section-kicker">// Platformun modülleri</span>
                <h2>Tek girişle, bir dersliğin ihtiyacı olan her şey.</h2>
                <p>Aşağıdaki dört modül birlikte çalışır: öğretmen hazırlar, öğrenci oynar, sistem ilerlemeyi otomatik işler.</p>
            </div>
            <div class="modules">
                <article class="module">
                    <span class="module-num">01</span>
                    <h3>Kodlama Oyunları</h3>
                    <p>Noktaları Birleştir, Compute It, Blok Kodlama ve Lightbot gibi oyunlarla öğrenciler kuralları ezberlemeden, deneyerek öğrenir.</p>
                    <span class="module-tag">Oyun ve Etkinlikler</span>
                </article>
                <article class="module">
                    <span class="module-num">02</span>
                    <h3>Ders &amp; Slayt Oluşturucu</h3>
                    <p>Öğretmen kendi ders akışını sınıf seviyesine göre kurar; metin, görsel, kod bloğu ve soruyu aynı slaytta birleştirir.</p>
                    <span class="module-tag">Ders Oluşturucu</span>
                </article>
                <article class="module">
                    <span class="module-num">03</span>
                    <h3>Canlı Quiz &amp; Rozet Sistemi</h3>
                    <p>XP, rozet ve liderlik tablosu ile motivasyon sürekli kılınır; öğretmen canlı quizi anında sınıfa açar.</p>
                    <span class="module-tag">Canlı Quiz</span>
                </article>
                <article class="module">
                    <span class="module-num">04</span>
                    <h3>Veli &amp; Yönetim Paneli</h3>
                    <p>Devam, ödev ve ilerleme durumu veliye otomatik bildirilir; yönetici tüm sınıfları tek panelden takip eder.</p>
                    <span class="module-tag">Bildirim &amp; Takip</span>
                </article>
            </div>
        </section>

        <section class="section container" id="kimler-icin">
            <div class="section-head">
                <span class="section-kicker">// Kimler için</span>
                <h2>Üç farklı ekran, tek ortak veri.</h2>
                <p>Herkes kendi işine odaklanır, hiçbir bilgi sistemler arasında kaybolmaz.</p>
            </div>
            <div class="audience">
                <div class="audience-card">
                    <span class="who">Öğrenci</span>
                    <h4>Oynayarak öğrenir</h4>
                    <p>Dersini açar, oyunla pekiştirir, rozetini kazanır. Sonraki adım her zaman nettir.</p>
                </div>
                <div class="audience-card">
                    <span class="who">Öğretmen</span>
                    <h4>Dakikalar içinde ders hazırlar</h4>
                    <p>Slayt oluşturur, sınıfa ödev atar, canlı quiz başlatır — hepsi tek ekrandan.</p>
                </div>
                <div class="audience-card">
                    <span class="who">Yönetici</span>
                    <h4>Tüm okulu tek yerden görür</h4>
                    <p>Sınıf, öğretmen ve içerik ataması; devam ve ilerleme raporları anında elinde.</p>
                </div>
            </div>
        </section>

        <section class="section container" id="iletisim">
            <div class="cta-band">
                <div class="cta-band-copy">
                    <h2>Okulunuz için kısa bir demo ayarlayalım.</h2>
                    <p>Panele girmeden önce nasıl çalıştığını görmek isterseniz, size özel 15 dakikalık bir gösterim planlayalım.</p>
                </div>
                <div class="cta-band-actions">
                    <button type="button" class="btn btn-primary" @click="demoOpen = true">Demo Talep Et</button>
                    <a href="{{ route('login') }}" class="btn btn-ghost">Hesabım Var, Giriş Yap</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container footer-row">
            <a href="/" class="brand" style="font-weight:700">
                <img src="{{ asset('images/bilisim-kod-logo.jpg') }}" alt="Bilişim Kod" style="width:26px;height:26px;border-radius:7px">
                Bilişim Kod
            </a>
            <div>© {{ date('Y') }} Bilişim Kod — Okullar için kodlama ve yapay zekâ platformu</div>
        </div>
    </footer>

    <div class="modal-backdrop" :class="{ 'is-open': demoOpen }" x-cloak @keydown.escape.window="demoOpen = false" @click.self="demoOpen = false">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title">
            <h3 id="demo-modal-title">Demo Talebi</h3>
            <p>Ad soyad, e-posta ve kısa bir not bırakın; talebiniz ekibimize düşsün, size dönelim.</p>

            @if(session('ok'))
                <div class="alert alert-success" style="margin-bottom:14px">{{ session('ok') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom:14px">{{ $errors->first() }}</div>
            @endif

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
                    <textarea id="message" name="message" required maxlength="6000" placeholder="Okulunuz ve ihtiyacınız hakkında kısa bir not">{{ old('message') }}</textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-ghost" @click="demoOpen = false">Vazgeç</button>
                    <button type="submit" class="btn btn-primary">Talebi Gönder</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
