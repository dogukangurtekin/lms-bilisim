<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <title>Giriş</title>
    <link rel="stylesheet" href="{{ url('/public/css/admin.css') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');
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
        .brand-content img{width:110px;height:auto;filter:drop-shadow(0 10px 20px rgba(22,24,43,.14))}
        .brand-eyebrow{display:inline-flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace;font-size:12px;
            letter-spacing:.06em;color:var(--violet-ink);text-transform:uppercase;}
        .brand-eyebrow::before{content:"";width:6px;height:6px;border-radius:50%;background:var(--signal)}
        .brand-content h1{margin:0;font-family:'Space Grotesk',sans-serif;font-size:37px;line-height:1.14;font-weight:700;letter-spacing:-.01em;color:var(--ink)}
        .brand-content p{margin:0;font-size:16px;line-height:1.55;color:var(--ink-soft)}

        @keyframes fly{0%,100%{transform:translate(0,0) rotate(-38deg)}50%{transform:translate(-14px,-22px) rotate(-38deg)}}

        /* ---------- right: form-side (fresh light style) ---------- */
        .form-side{display:grid;place-items:center;padding:34px;background:var(--surface)}
        .login-form{width:min(420px,96%)}
        .login-form-brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:28px}
        .login-form-brand img{width:150px;height:auto}
        .login-form-brand span{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:15px;color:var(--ink)}
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
        .btn-game svg{width:19px;height:19px;fill:var(--signal)}
        .btn-qr svg{width:19px;height:19px;fill:var(--violet)}
        .game-login-help{margin-top:12px;font-size:12px;color:var(--ink-soft);line-height:1.45;text-align:left}
        .error-box{background:var(--signal-tint);border:1px solid #ffd4bb;border-radius:10px;color:#b8481f;padding:10px 12px;margin-bottom:16px;font-size:13.5px}
        .mini-game-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(2,6,23,.55);z-index:1200}
        .mini-game-modal.open{display:flex}
        .mini-game-card{width:min(760px,94vw);background:#fff;border-radius:16px;border:1px solid #dbeafe;box-shadow:0 20px 55px rgba(15,23,42,.35);padding:14px}
        .mini-game-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px}
        .mini-game-head h3{margin:0;font-size:20px}
        .mini-game-head p{margin:3px 0 0;color:#64748b;font-size:13px}
        .mini-game-close{background:#e2e8f0;color:#0f172a;border:0;border-radius:8px;padding:6px 10px;cursor:pointer;font-weight:700}
        #loginGameCanvas{width:100%;height:430px;background:linear-gradient(180deg,#e0f2fe,#dbeafe 48%,#bbf7d0);border-radius:12px;border:1px solid #bfdbfe;display:block;touch-action:none}
        .mini-game-foot{margin-top:8px;color:#334155;font-size:13px;display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
        .mini-game-win{color:#065f46;font-weight:800}
        .mini-game-stars{color:#f59e0b;font-weight:800}
        .win-toast{
            position:absolute;
            left:50%;
            top:50%;
            transform:translate(-50%,-50%) scale(.9);
            padding:14px 18px;
            border-radius:14px;
            background:linear-gradient(135deg,#22c55e,#16a34a);
            color:#fff;
            font-weight:800;
            font-size:18px;
            box-shadow:0 16px 40px rgba(22,163,74,.35);
            opacity:0;
            pointer-events:none;
            transition:all .28s ease;
            z-index:20;
            text-align:center;
            min-width:300px;
        }
        .win-toast.show{opacity:1;transform:translate(-50%,-50%) scale(1)}

        @keyframes twinkle{0%,100%{opacity:.3;transform:scale(.9)}50%{opacity:1;transform:scale(1.4)}}
        @keyframes streak{0%{transform:translateX(0) scaleX(.5)}100%{transform:translateX(30px) scaleX(1)}}
        @keyframes drift{0%,100%{transform:translateY(0) translateX(0)}50%{transform:translateY(-10px) translateX(6px)}}

        @media (max-width:1100px){
            .login-shell{grid-template-columns:1fr}
            .brand-side{min-height:280px;padding:28px}
            .brand-content{max-width:none}
            .brand-content img{width:88px}
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
            .btn-game,.btn-qr{gap:6px}
            .btn-game svg,.btn-qr svg{width:22px;height:22px}
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
                width:64px;
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
            .login-form-brand img{
                width:110px;
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
            .btn-game,
            .btn-qr{
                justify-content:center;
            }
            .game-login-help{
                font-size:11px;
                line-height:1.4;
                margin-top:8px;
            }
            #loginGameCanvas{
                height:300px;
            }
            .mini-game-card{
                width:min(96vw, 760px);
                padding:12px;
                border-radius:14px;
            }
            .mini-game-head h3{
                font-size:17px;
            }
            .mini-game-head p,
            .mini-game-foot{
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
            <img src="{{ url('/public/logo.png') }}" alt="Logo">
            <span class="brand-eyebrow">Bilişim Kod</span>
            <h1>Dijital Bilişim Eğitim Platformu</h1>
            <p>Kodlama oyunları ve ders defteriyle öğrenmeyi somutlaştıran platform. Özelsin çünkü gelecek sensin.</p>
        </div>
    </section>

    <section class="form-side">
        <div class="login-form">
            <div class="login-form-brand">
                <img src="{{ url('/public/logo.png') }}" alt="Logo">
            </div>
            <h2>Tekrar hoş geldin</h2>
            <p>Hesabına giriş yapmak için bilgilerini gir.</p>
            @if($errors->any())
                <div class="error-box">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="field">
                    <label>Kullanıcı Adı veya E-posta</label>
                    <input type="text" id="login-email" name="email" value="{{ old('email') }}" placeholder="ornek: pipek.5a" required>
                </div>
                <div class="field">
                    <label>Şifre</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button class="btn" type="submit">Giriş Yap</button>

                <div class="login-divider">veya</div>

                <div class="login-actions">
                    <button class="btn btn-game" type="button" id="openGameLoginBtn">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 6h10a3 3 0 013 3v6a3 3 0 01-3 3h-1.5l-2.2 2.2a1 1 0 01-1.6-.3L10.4 18H7a3 3 0 01-3-3V9a3 3 0 013-3zm1 3v2h2v2h2v-2h2V9h-2V7h-2v2H8zm8 0h2v2h-2V9z"/></svg>
                        Oyun ile Giriş
                    </button>
                    <button class="btn btn-qr" type="button" id="openQrLoginBtn">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v2H6v4H4V4zm10 0h6v6h-2V6h-4V4zM4 14h2v4h4v2H4v-6zm14 0h2v6h-6v-2h4v-4zM8 8h8v8H8V8zm2 2v4h4v-4h-4z"/></svg>
                        QR ile Giriş
                    </button>
                </div>
                <div class="game-login-help">
                    Oyun ile giriş için kullanıcı adı girmeniz yeterlidir. Oyunu geçince otomatik giriş yapılır.
                </div>
            </form>
        </div>
    </section>
</div>
<div id="miniGameModal" class="mini-game-modal">
    <div class="mini-game-card">
        <div class="mini-game-head">
            <div>
                <h3>Kanatli Kahraman Oyunu</h3>
                <p>Bilgi: Ok tuşları veya mobilde kaydırma ile oynanır. Zirveye çıkıp kazanınca giriş yapılır.</p>
            </div>
            <button type="button" class="mini-game-close" id="closeGameModalBtn">Kapat</button>
        </div>
        <canvas id="loginGameCanvas" width="700" height="430"></canvas>
        <div id="miniGameWinToast" class="win-toast">🎉 Tebrikler, giriş yapıyorsunuz...</div>
        <div class="mini-game-foot">
            <span>Kontroller: Sol/Sağ ok = hareket, Yukarı ok = zıplama | Mobil: Sağa/sola/yukarı kaydır</span>
            <span id="miniGameStatus">Hazır mısın?</span>
        </div>
    </div>
</div>
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
<script>
(() => {
    const openBtn = document.getElementById('openGameLoginBtn');
    const closeBtn = document.getElementById('closeGameModalBtn');
    const modal = document.getElementById('miniGameModal');
    const canvas = document.getElementById('loginGameCanvas');
    const winToast = document.getElementById('miniGameWinToast');
    const statusEl = document.getElementById('miniGameStatus');
    const usernameInput = document.getElementById('login-email');
    if (!openBtn || !canvas || !modal) return;
    let touchStartX = 0;
    let touchStartY = 0;
    let touchActive = false;
    let gameLoginPending = false;

    const ctx = canvas.getContext('2d');
    const keys = { left: false, right: false };
    const gravity = 0.5;
    const jumpPower = -11.2;
    let failCount = 0;
    const state = {
        running: false,
        won: false,
        player: { x: 28, y: 290, w: 28, h: 28, vx: 0, vy: 0, onGround: false, facing: 1, flap: 0 },
        platforms: [
            { x: 0, y: 405, w: 700, h: 25 },
            { x: 46, y: 368, w: 130, h: 12 },
            { x: 162, y: 334, w: 122, h: 12 },
            { x: 274, y: 304, w: 126, h: 12 },
            { x: 392, y: 276, w: 120, h: 12 },
            { x: 506, y: 248, w: 118, h: 12 },
            { x: 430, y: 218, w: 108, h: 12 },
            { x: 320, y: 190, w: 104, h: 12 },
            { x: 220, y: 162, w: 96, h: 12 },
            { x: 140, y: 136, w: 90, h: 10 }
        ],
        monsters: [
            { x: 72, y: 350, w: 20, h: 18, minX: 56, maxX: 160, dir: 1, speed: 1.05 },
            { x: 196, y: 316, w: 20, h: 18, minX: 170, maxX: 276, dir: -1, speed: 1.15 },
            { x: 316, y: 286, w: 20, h: 18, minX: 286, maxX: 396, dir: 1, speed: 1.2 },
            { x: 438, y: 258, w: 20, h: 18, minX: 404, maxX: 504, dir: -1, speed: 1.25 },
            { x: 548, y: 230, w: 20, h: 18, minX: 516, maxX: 616, dir: 1, speed: 1.3 },
            { x: 352, y: 172, w: 20, h: 18, minX: 326, maxX: 414, dir: -1, speed: 1.35 }
        ],
        goal: { x: 154, y: 88, w: 44, h: 24 },
        stars: [
            { x: 165, y: 282, taken: false },
            { x: 364, y: 222, taken: false },
            { x: 553, y: 170, taken: false }
        ],
        easyMode: false
    };

    function setEasyModeIfNeeded() {
        state.easyMode = failCount >= 3;
        if (state.easyMode) {
            state.monsters.forEach((m) => m.speed = Math.max(0.45, m.speed * 0.55));
            if (!state.platforms.find((p) => p.x === 112 && p.y === 232)) {
                state.platforms.push({ x: 112, y: 232, w: 110, h: 12 });
                state.platforms.push({ x: 156, y: 178, w: 100, h: 12 });
            }
        }
    }

    function playJumpSound() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'square';
            osc.frequency.setValueAtTime(520, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(240, audioCtx.currentTime + 0.12);
            gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.14);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.14);
        } catch (_) {}
    }

    function resetGame() {
        state.won = false;
        state.player.x = 28; state.player.y = 360; state.player.vx = 0; state.player.vy = 0; state.player.flap = 0;
        if (winToast) winToast.classList.remove('show');
        state.stars.forEach((s) => s.taken = false);
        setEasyModeIfNeeded();
        statusEl.innerHTML = 'Canavarlardan kaç ve zirveye çık! <span class="mini-game-stars">Deneme: ' + (failCount + 1) + (state.easyMode ? ' · Kolay Mod Aktif ⭐' : '') + '</span>';
        state.running = true;
    }

    function intersects(a, b) {
        return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
    }

    function update() {
        const p = state.player;
        p.vx = 0;
        if (keys.left) { p.vx = -3.8; p.facing = -1; }
        if (keys.right) { p.vx = 3.8; p.facing = 1; }
        p.x += p.vx;
        p.x = Math.max(0, Math.min(canvas.width - p.w, p.x));
        p.vy += gravity;
        p.y += p.vy;
        p.onGround = false;
        p.flap += 0.24;

        for (const plat of state.platforms) {
            if (p.vy >= 0 && p.x + p.w > plat.x && p.x < plat.x + plat.w && p.y + p.h >= plat.y && p.y + p.h <= plat.y + 16) {
                p.y = plat.y - p.h;
                p.vy = 0;
                p.onGround = true;
            }
        }

        if (p.y > canvas.height + 40) {
            failCount++;
            resetGame();
        }

        for (const m of state.monsters) {
            m.x += m.speed * m.dir;
            if (m.x <= m.minX || m.x + m.w >= m.maxX) m.dir *= -1;
            if (intersects(p, m)) {
                failCount++;
                statusEl.textContent = 'Canavara yakalandin. Tekrar dene!';
                resetGame();
                return;
            }
        }

        for (const s of state.stars) {
            if (s.taken) continue;
            if (intersects(p, { x: s.x - 8, y: s.y - 8, w: 16, h: 16 })) {
                s.taken = true;
            }
        }

        if (intersects(p, state.goal)) {
            if (gameLoginPending) return;
            state.won = true;
            state.running = false;
            statusEl.innerHTML = '<span class=\"mini-game-win\">Tebrikler! Giriş hazırlanıyor...</span>';
            if (winToast) winToast.classList.add('show');
            doGameLogin();
        }
    }

    function drawWingedPlayer(p) {
        ctx.save();
        ctx.translate(p.x + p.w / 2, p.y + p.h / 2);
        if (p.facing < 0) ctx.scale(-1, 1);
        const flap = Math.sin(p.flap) * 2.2;
        ctx.fillStyle = '#0f172a';
        ctx.beginPath(); ctx.arc(0, 0, 10, 0, Math.PI * 2); ctx.fill();
        ctx.fillStyle = '#f8fafc';
        ctx.beginPath(); ctx.arc(3, -2, 2.1, 0, Math.PI * 2); ctx.fill();
        ctx.fillStyle = '#fbbf24';
        ctx.beginPath(); ctx.moveTo(10, 1); ctx.lineTo(16, 4); ctx.lineTo(10, 6); ctx.closePath(); ctx.fill();
        ctx.fillStyle = '#a78bfa';
        ctx.beginPath(); ctx.ellipse(-10, -2 + flap, 7, 4, -0.5, 0, Math.PI * 2); ctx.fill();
        ctx.beginPath(); ctx.ellipse(-10, 4 - flap, 8, 4, 0.3, 0, Math.PI * 2); ctx.fill();
        ctx.restore();
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#ffffffcc';
        for (let i = 0; i < 6; i++) {
            ctx.beginPath();
            ctx.arc(70 + i * 118, 42 + (i % 2) * 8, 18, 0, Math.PI * 2);
            ctx.fill();
        }

        ctx.fillStyle = '#8b5cf6';
        for (const plat of state.platforms) {
            ctx.fillRect(plat.x, plat.y, plat.w, plat.h);
            ctx.fillStyle = '#c4b5fd';
            ctx.fillRect(plat.x, plat.y + plat.h - 3, plat.w, 3);
            ctx.fillStyle = '#8b5cf6';
        }
        ctx.fillStyle = '#16a34a';
        ctx.fillRect(state.goal.x, state.goal.y, state.goal.w, state.goal.h);
        ctx.fillStyle = '#052e16';
        ctx.fillText('HEDEF', state.goal.x + 4, state.goal.y + 17);

        for (const m of state.monsters) {
            ctx.fillStyle = '#ef4444';
            ctx.fillRect(m.x, m.y, m.w, m.h);
            ctx.fillStyle = '#fff';
            ctx.fillRect(m.x + 4, m.y + 6, 4, 4);
            ctx.fillRect(m.x + 14, m.y + 6, 4, 4);
        }

        for (const s of state.stars) {
            if (s.taken) continue;
            ctx.fillStyle = '#f59e0b';
            ctx.beginPath();
            ctx.moveTo(s.x, s.y - 8);
            ctx.lineTo(s.x + 3, s.y - 2);
            ctx.lineTo(s.x + 9, s.y - 2);
            ctx.lineTo(s.x + 4, s.y + 2);
            ctx.lineTo(s.x + 6, s.y + 8);
            ctx.lineTo(s.x, s.y + 4);
            ctx.lineTo(s.x - 6, s.y + 8);
            ctx.lineTo(s.x - 4, s.y + 2);
            ctx.lineTo(s.x - 9, s.y - 2);
            ctx.lineTo(s.x - 3, s.y - 2);
            ctx.closePath();
            ctx.fill();
        }
        drawWingedPlayer(state.player);

        const collected = state.stars.filter((s) => s.taken).length;
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 13px Manrope, sans-serif';
        ctx.fillText('Yildiz: ' + collected + '/' + state.stars.length, 12, 22);
    }

    function loop() {
        if (state.running) update();
        draw();
        requestAnimationFrame(loop);
    }

    async function doGameLogin() {
        if (gameLoginPending) return;
        const emailVal = (usernameInput?.value || '').trim();
        if (!emailVal) {
            statusEl.textContent = 'Lutfen once kullanici adini gir.';
            state.running = true;
            state.won = false;
            return;
        }
        gameLoginPending = true;
        try {
            const resp = await fetch('{{ route('login.game') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: emailVal, game_won: true })
            });
            const data = await resp.json();
            if (!resp.ok) throw new Error(data.message || 'Giriş başarısız');
            setTimeout(() => window.location.href = data.redirect || '{{ route('dashboard') }}', 1700);
        } catch (e) {
            statusEl.textContent = e.message || 'Giriş sırasında hata oluştu.';
            if (winToast) winToast.classList.remove('show');
            state.running = false;
            state.won = false;
            state.player.vx = 0;
            state.player.vy = 0;
        } finally {
            gameLoginPending = false;
        }
    }

    window.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('open')) return;
        if (e.key === 'ArrowLeft') keys.left = true;
        if (e.key === 'ArrowRight') keys.right = true;
        if (e.key === 'ArrowUp' && state.player.onGround && state.running) {
            state.player.vy = jumpPower;
            playJumpSound();
        }
    });
    window.addEventListener('keyup', (e) => {
        if (e.key === 'ArrowLeft') keys.left = false;
        if (e.key === 'ArrowRight') keys.right = false;
    });

    openBtn.addEventListener('click', () => {
        const emailVal = (usernameInput?.value || '').trim();
        if (!emailVal) {
            statusEl.textContent = 'Oyunla giriş için önce kullanıcı adını gir.';
            return;
        }
        modal.classList.add('open');
        gameLoginPending = false;
        resetGame();
    });
    closeBtn.addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('open');
    });
    modal.addEventListener('transitionend', () => {
        if (!modal.classList.contains('open') && winToast) winToast.classList.remove('show');
    });
    modal.addEventListener('touchmove', (e) => {
        if (modal.classList.contains('open')) e.preventDefault();
    }, { passive: false });

    canvas.addEventListener('touchstart', (e) => {
        if (!modal.classList.contains('open')) return;
        const t = e.touches[0];
        touchStartX = t.clientX;
        touchStartY = t.clientY;
        touchActive = true;
        e.preventDefault();
    }, { passive: false });

    canvas.addEventListener('touchmove', (e) => {
        if (!touchActive || !modal.classList.contains('open')) return;
        const t = e.touches[0];
        const dx = t.clientX - touchStartX;
        const dy = t.clientY - touchStartY;
        keys.left = false;
        keys.right = false;
        if (Math.abs(dx) > 22) {
            if (dx > 0) keys.right = true;
            if (dx < 0) keys.left = true;
        }
        if (dy < -24 && state.player.onGround && state.running) {
            state.player.vy = jumpPower;
            playJumpSound();
            touchStartY = t.clientY;
        }
        e.preventDefault();
    }, { passive: false });

    canvas.addEventListener('touchend', () => {
        touchActive = false;
        keys.left = false;
        keys.right = false;
    });

    ctx.font = '13px Manrope, sans-serif';
    resetGame();
    state.running = false;
    draw();
    loop();
})();
</script>
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
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(token);
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
        poll = setInterval(check, 1200);
    });
    const close = () => { modal.classList.remove('open'); if (poll) { clearInterval(poll); poll = null; } };
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
})();
</script>
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>

