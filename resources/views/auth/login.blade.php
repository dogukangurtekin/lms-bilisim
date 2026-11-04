<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <title>Giriş</title>
    <link rel="stylesheet" href="{{ url('/public/css/admin.css') }}">
    <style>
        @import url('{{ asset('fonts/fonts.css') }}');
        :root{
            --paper:#F7F6F2; --surface:#FFFFFF; --ink:#16182B; --ink-soft:#585A72;
            --line:#E4E1D8; --violet:#5B3DF5; --violet-ink:#3E28B8; --violet-tint:#EEEBFD;
            --signal:#FF7A45; --signal-tint:#FFEEE4; --mint:#0EA57A;
            --shadow-md:0 12px 30px rgba(22,24,43,.09);
            --shadow-lg:0 24px 56px rgba(22,24,43,.14);
        }
        body{margin:0;font-family:'Inter',Segoe UI,Arial,sans-serif;background:var(--paper);color:var(--ink)}
        .login-shell{min-height:100vh;display:grid;grid-template-columns:1.08fr .92fr;background:var(--paper)}

        /* ---------- left: brand-side (light, sky + rocket + stars) ---------- */
        .brand-side{position:relative;display:grid;place-items:center;padding:48px;overflow:hidden;
            background:radial-gradient(circle at 22% 18%,#EEEBFD 0%,var(--paper) 55%);
            border-right:1px solid var(--line);}
        .brand-grid{position:absolute;inset:0;z-index:0;pointer-events:none;
            background-image:linear-gradient(rgba(22,24,43,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(22,24,43,.05) 1px,transparent 1px);
            background-size:42px 42px; mask-image:radial-gradient(circle at 50% 40%,#000 0%,transparent 75%);}

        .star-layer{position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden}
        .star{position:absolute;width:5px;height:5px;border-radius:50%;background:var(--violet);animation:twinkle 2.4s ease-in-out infinite}
        .star.gold{background:var(--signal)}
        .star.s1{left:12%;top:16%;animation-delay:.2s}
        .star.s2{left:30%;top:62%;animation-delay:1.1s}
        .star.s3{left:60%;top:20%;animation-delay:.8s}
        .star.s4{left:76%;top:66%;animation-delay:1.6s}
        .star.s5{left:88%;top:34%;animation-delay:.5s}
        .star.s6{left:6%;top:8%;animation-delay:.9s}
        .star.s7{left:20%;top:82%;animation-delay:1.9s}
        .star.s8{left:70%;top:88%;animation-delay:1.3s}
        .star.s9{left:44%;top:10%;animation-delay:2.1s}

        .rocket{position:absolute;left:70%;top:56%;width:70px;height:70px;animation:fly 5.5s ease-in-out infinite;filter:drop-shadow(0 10px 18px rgba(91,61,245,.28))}
        .rocket-trail{position:absolute;left:14%;top:30%;width:150px;height:2px;border-radius:999px;
            background:linear-gradient(90deg,transparent,rgba(91,61,245,.5));animation:streak .9s linear infinite}
        .rocket-trail.t2{left:50%;top:76%;width:110px;animation-delay:.4s;background:linear-gradient(90deg,transparent,rgba(255,122,69,.5))}

        .code-float{position:absolute;z-index:1;font-family:'JetBrains Mono',monospace;font-size:12.5px;color:var(--ink-soft);
            background:#fff;border:1px solid var(--line);border-radius:8px;padding:6px 10px;box-shadow:var(--shadow-md);
            animation:drift 7s ease-in-out infinite;}
        .code-float.c1{left:6%;top:44%}
        .code-float.c2{left:58%;top:12%;animation-delay:1.2s}
        .code-float.c3{left:8%;top:78%;animation-delay:2.1s}
        .code-float.c4{left:62%;top:82%;animation-delay:.6s}
        .code-float.c5{left:80%;top:18%;animation-delay:1.8s;font-size:11.5px}

        .brand-content{position:relative;z-index:2;display:grid;gap:18px;justify-items:center;text-align:center;color:var(--ink);max-width:460px}
        .brand-content img{width:294px;height:auto;filter:drop-shadow(0 10px 20px rgba(22,24,43,.14))}
        .brand-eyebrow{display:inline-flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace;font-size:12px;
            letter-spacing:.06em;color:var(--violet-ink);text-transform:uppercase;}
        .brand-eyebrow::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--signal)}
        .brand-content h1{margin:0;font-family:'Space Grotesk',sans-serif;font-size:37px;line-height:1.14;font-weight:700;letter-spacing:-.01em;color:var(--ink)}
        .brand-content p{margin:0;font-size:16px;line-height:1.55;color:var(--ink-soft)}

        @keyframes fly{0%,100%{transform:translate(0,0) rotate(-38deg)}50%{transform:translate(-14px,-22px) rotate(-38deg)}}

        /* ---------- right: form-side (fresh light style) ---------- */
        .form-side{position:relative;display:grid;place-items:center;padding:34px;background:var(--surface)}
        .back-home{position:absolute;top:20px;left:22px;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--line);border-radius:999px;color:var(--ink-soft);font-size:13.5px;font-weight:600;text-decoration:none;background:var(--surface);transition:border-color .15s,color .15s}
        .back-home:hover{border-color:var(--violet);color:var(--violet-ink)}
        .back-home:focus-visible{outline:3px solid var(--violet);outline-offset:2px}
        @media (max-width:1100px){.form-side{padding-top:74px}}
        .bh-home{display:none}
        /* Telefon: buton başlıkların üstüne binmesin diye üst köşede küçük "← 🏠" simgesi olur (sayfa içeriğinden bağımsız, sayfayla kayar). */
        @media (max-width:640px){
            .login-shell{position:relative}
            .form-side{position:static}
            .back-home{top:12px;left:12px;z-index:5;padding:0;width:54px;height:40px;justify-content:center;gap:3px;font-size:18px;box-shadow:0 4px 12px rgba(22,24,43,.12)}
            .bh-home{display:inline}
            .bh-text{display:none}
        }
        .login-form{width:min(420px,96%)}
        .login-form h2{margin:0 0 6px;font-family:'Space Grotesk',sans-serif;font-size:28px;line-height:1.2;font-weight:700;color:var(--ink);text-align:center}
        .login-form p{margin:0 0 26px;color:var(--ink-soft);font-size:14.5px;text-align:center}
        .login-form label{display:block;font-weight:600;color:var(--ink);font-size:13px;margin:0 0 6px;text-align:center}
        .login-form .field{margin-bottom:16px}
        .login-form input{width:100%;padding:13px 14px;border:1.5px solid var(--line);border-radius:10px;background:var(--paper);font-size:16px;color:var(--ink);outline:none;transition:border-color .15s,box-shadow .15s,background .15s;box-sizing:border-box}
        .login-form input:focus{border-color:var(--violet);background:#fff;box-shadow:0 0 0 4px var(--violet-tint)}
        .login-form input::placeholder{color:#A6A4B8}
        .login-form .btn{width:100%;margin-top:6px;padding:13px 14px;border-radius:10px;font-weight:600;font-size:15px;line-height:1.2;font-family:'Space Grotesk',sans-serif;background:var(--violet);color:#fff;border:1px solid var(--violet);cursor:pointer;transition:background .15s,border-color .15s}
        .login-form .btn:hover{background:var(--violet-ink);border-color:var(--violet-ink)}
        .login-divider{display:flex;align-items:center;gap:12px;margin:22px 0;color:var(--ink-soft);font-size:12px}
        .login-divider::before,.login-divider::after{content:"";flex:1;height:1px;background:var(--line)}
        .login-actions{display:flex;gap:10px;align-items:stretch}
        .login-actions .btn{margin-top:0;width:auto;min-height:46px;flex:1;background:var(--surface);color:var(--ink);border:1.5px solid var(--line);font-size:13.5px;display:inline-flex;align-items:center;justify-content:center;gap:8px}
        .login-actions .btn:hover{background:var(--paper);border-color:var(--ink-soft)}
        .btn-qr svg{width:19px;height:19px;fill:var(--violet)}
        .error-box{background:var(--signal-tint);border:1px solid #ffd4bb;border-radius:10px;color:#b8481f;padding:10px 12px;margin-bottom:16px;font-size:13.5px}
        .mini-game-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(2,6,23,.55);z-index:1200}
        .mini-game-modal.open{display:flex}
        .mini-game-card{width:min(760px,94vw);background:#fff;border-radius:16px;border:1px solid #dbeafe;box-shadow:0 20px 55px rgba(15,23,42,.35);padding:14px}
        .mini-game-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px}
        .mini-game-head h3{margin:0;font-size:20px}
        .mini-game-head p{margin:3px 0 0;color:#64748b;font-size:13px}
        .mini-game-close{background:#e2e8f0;color:#0f172a;border:0;border-radius:8px;padding:6px 10px;cursor:pointer;font-weight:700}

        @keyframes twinkle{0%,100%{opacity:.3;transform:scale(.9)}50%{opacity:1;transform:scale(1.4)}}
        @keyframes streak{0%{transform:translateX(0) scaleX(.5)}100%{transform:translateX(30px) scaleX(1)}}
        @keyframes drift{0%,100%{transform:translateY(0) translateX(0)}50%{transform:translateY(-10px) translateX(6px)}}

        @media (max-width:1100px){
            .login-shell{grid-template-columns:1fr}
            .brand-side{min-height:280px;padding:28px}
            .brand-content{max-width:none}
            .brand-content img{width:172px}
            .brand-content h1{font-size:27px}
            .brand-content p{font-size:14.5px}
            .rocket{width:54px;height:54px}
            .code-float{display:none}
            .form-side{padding:16px}
            .login-form{width:min(440px,100%);padding:8px}
            .login-form h2{font-size:26px}
            .login-actions{gap:8px}
            .login-actions .btn{
                flex:1 1 0;
                min-width:0;
                min-height:44px;
                padding:9px 8px;
                font-size:13px;
                white-space:normal;
                overflow-wrap:anywhere;
                word-break:break-word;
            }
            .btn-qr{gap:6px}
            .btn-qr svg{width:22px;height:22px}
        }

        @media (max-width: 640px){
            .brand-side{
                min-height:180px;
                padding:18px 16px;
            }
            .brand-content{
                gap:8px;
            }
            .brand-content img{
                width:126px;
            }
            .brand-eyebrow{
                font-size:11px;
            }
            .brand-content h1{
                font-size:20px;
                line-height:1.2;
            }
            .brand-content p{
                font-size:13px;
                line-height:1.35;
            }
            .code-float,.rocket,.rocket-trail{
                display:none;
            }
            .form-side{
                padding:16px 12px;
            }
            .login-form{
                width:100%;
            }
            .login-form h2{
                font-size:22px;
                margin-bottom:4px;
            }
            .login-form p{
                font-size:13px;
                margin-bottom:18px;
            }
            .login-form label{
                margin:0 0 5px;
                font-size:13px;
            }
            .login-form input{
                padding:11px 12px;
                font-size:16px;
                border-radius:10px;
            }
            .login-actions{
                display:grid;
                gap:8px;
            }
            .login-actions .btn{
                width:100%;
                min-height:42px;
                padding:10px 12px;
                font-size:13px;
                border-radius:10px;
            }
            .btn-qr{
                justify-content:center;
            }
            .mini-game-card{
                width:min(96vw, 760px);
                padding:12px;
                border-radius:14px;
            }
            .mini-game-head h3{
                font-size:17px;
            }
            .mini-game-head p{
                font-size:12px;
            }
        }
    </style>
</head>
<body>
<div class="login-shell">
    <section class="brand-side">
        <div class="brand-grid"></div>

        <div class="star-layer">
            <span class="star s1"></span>
            <span class="star s2 gold"></span>
            <span class="star s3"></span>
            <span class="star s4 gold"></span>
            <span class="star s5"></span>
            <span class="star s6"></span>
            <span class="star s7 gold"></span>
            <span class="star s8"></span>
            <span class="star s9"></span>
            <span class="rocket-trail t1"></span>
            <span class="rocket-trail t2"></span>
        </div>

        <svg class="rocket" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M32 6C40 14 44 26 42 40L32 46L22 40C20 26 24 14 32 6Z" fill="#EEEBFD" stroke="#5B3DF5" stroke-width="2"/>
            <circle cx="32" cy="26" r="6" fill="#5B3DF5"/>
            <path d="M22 40L14 48L20 50L22 40Z" fill="#FF7A45" stroke="#5B3DF5" stroke-width="1.5"/>
            <path d="M42 40L50 48L44 50L42 40Z" fill="#FF7A45" stroke="#5B3DF5" stroke-width="1.5"/>
            <path d="M28 46L32 58L36 46" fill="#FFEEE4" stroke="#5B3DF5" stroke-width="1.5"/>
        </svg>

        <div class="code-float c1">while(code){ learn(); }</div>
        <div class="code-float c2">const xp = progress + effort;</div>
        <div class="code-float c3">deploy("dreams")</div>
        <div class="code-float c4">class FutureEngineer {}</div>
        <div class="code-float c5">if(future) return you;</div>

        <div class="brand-content">
            <img src="{{ \App\Support\Brand::logoUrl() }}" alt="Logo">
            <span class="brand-eyebrow">Bilişim Kod</span>
            <h1>Dijital Bilişim Eğitim Platformu</h1>
            <p>Bilişim Kod Yapay Zeka ve Kodlama Sistemleri ile geleceği kodla.</p>
        </div>
    </section>

    <section class="form-side">
        <a href="{{ url('/') }}" class="back-home" aria-label="Ana sayfaya dön" title="Ana sayfaya dön"><span aria-hidden="true">←</span><span class="bh-home" aria-hidden="true">🏠</span><span class="bh-text"> Ana sayfaya dön</span></a>
        <div class="login-form">
            <h2>Tekrar hoş geldin</h2>
            <p>Hesabına giriş yapmak için bilgilerini gir.</p>
            @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif
            @if(request()->query('force_logout'))
                <div class="modal open" id="force-logout-modal">
                    <div class="modal-card">
                        <div class="modal-head"><strong>Oturum Sonlandırıldı</strong></div>
                        <p style="margin:0 0 14px;color:#475569;">Öğretmen/admin tarafından çıkış yapılmıştır. Lütfen tekrar giriş yapın.</p>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="button" class="btn" id="force-logout-modal-ok">Tamam</button>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById('force-logout-modal-ok')?.addEventListener('click', () => {
                        document.getElementById('force-logout-modal')?.classList.remove('open');
                        const url = new URL(window.location.href);
                        url.searchParams.delete('force_logout');
                        window.history.replaceState({}, '', url);
                    });
                </script>
            @endif
            <form method="POST" action="{{ route('login.attempt') }}" id="login-form">
                @csrf
                <div class="field">
                    <label>Kullanıcı Adı veya E-posta</label>
                    <input type="text" id="login-email" name="email" value="{{ old('email') }}" placeholder="ornek: pipek.5a" required>
                </div>
                <div class="field">
                    <label>Şifre</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button class="btn" type="submit" id="login-submit">Giriş Yap</button>

                <div class="login-divider">veya</div>

                <div class="login-actions">
                    <button class="btn btn-qr" type="button" id="openQrLoginBtn">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v2H6v4H4V4zm10 0h6v6h-2V6h-4V4zM4 14h2v4h4v2H4v-6zm14 0h2v6h-6v-2h4v-4zM8 8h8v8H8V8zm2 2v4h4v-4h-4z"/></svg>
                        QR ile Giriş
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
(() => {
    const form = document.getElementById('login-form');
    const submit = document.getElementById('login-submit');
    if (!form || !submit) return;

    let submitting = false;
    form.addEventListener('submit', (event) => {
        if (submitting) {
            event.preventDefault();
            return;
        }

        submitting = true;
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        submit.textContent = 'Giriş yapılıyor...';
    });

    // Geri/ileri önbelleğinden dönüldüğünde form tekrar kullanılabilsin.
    window.addEventListener('pageshow', () => {
        submitting = false;
        submit.disabled = false;
        submit.removeAttribute('aria-busy');
        submit.textContent = 'Giriş Yap';
    });
})();
</script>
<div id="qrLoginModal" class="mini-game-modal">
    <div class="mini-game-card" style="max-width:420px">
        <div class="mini-game-head">
            <div><h3>QR ile Giriş</h3><p>Bu kodu öğretmen/admin panelinden okutun.</p></div>
            <button type="button" class="mini-game-close" id="closeQrModalBtn">Kapat</button>
        </div>
        <div style="display:grid;justify-items:center;gap:10px">
            <img id="qrLoginImage" alt="QR" style="width:260px;max-width:100%;border:1px solid #dbeafe;border-radius:12px;padding:10px;background:#fff;display:none">
            <p id="qrLoginStatus" style="margin:0;font-size:13px;color:#334155">Hazırlanıyor...</p>
        </div>
    </div>
</div>
<script src="{{ asset('js/vendor/qrcode.js') }}"></script>
<script>
(() => {
    const openBtn = document.getElementById('openQrLoginBtn');
    const closeBtn = document.getElementById('closeQrModalBtn');
    const modal = document.getElementById('qrLoginModal');
    const img = document.getElementById('qrLoginImage');
    const statusEl = document.getElementById('qrLoginStatus');
    if (!openBtn || !closeBtn || !modal) return;
    let token = '';
    let poll = null;
    async function generate() {
        const res = await fetch('{{ route('qr.guest.generate') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}', 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        const data = await res.json();
        token = String(data.token || '');
        if (!token) throw new Error('Token olusmadi');
        // QR kodu tarayıcıda üretilir; giriş eşleştirme kodu hiçbir üçüncü tarafa gönderilmez.
        const qr = qrcode(0, 'M'); qr.addData(token); qr.make();
        img.src = qr.createDataURL(8, 2);
        img.style.display = 'block';
        statusEl.textContent = 'Onay bekleniyor...';
    }
    async function check() {
        if (!token) return;
        const res = await fetch('{{ url('/qr/guest/status') }}/' + encodeURIComponent(token), { credentials: 'same-origin' });
        const data = await res.json().catch(() => ({}));
        if (data.expired) {
            statusEl.textContent = data.message || 'QR suresi doldu. Lutfen tekrar olusturun.';
            if (poll) { clearInterval(poll); poll = null; }
            return;
        }
        if (data.approved && data.redirect) {
            statusEl.textContent = 'Onaylandı. Öğrenci paneline giriş yapılıyor...';
            if (poll) { clearInterval(poll); poll = null; }
            window.location.href = data.redirect;
        }
    }
    openBtn.addEventListener('click', async () => {
        modal.classList.add('open');
        try { await generate(); } catch (_) { statusEl.textContent = 'QR olusturulamadi'; return; }
        if (poll) clearInterval(poll);
        const pollStartedAt = Date.now();
        poll = setInterval(() => {
            // QR penceresi açık unutulursa sunucu yorulmasın: 5 dakika sonra durur, sekme gizliyken sormaz.
            if (Date.now() - pollStartedAt > 5 * 60 * 1000) { clearInterval(poll); poll = null; statusEl.textContent = 'QR süresi doldu, pencereyi kapatıp yeniden açın.'; return; }
            if (document.hidden) return;
            check();
        }, 3000);
    });
    const close = () => { modal.classList.remove('open'); if (poll) { clearInterval(poll); poll = null; } };
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
})();
</script>
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>

