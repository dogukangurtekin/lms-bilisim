<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oyunlar | Bilişim Kod</title>
    <meta name="description" content="Bilişim Kod oyunları: giriş yapmadan kodlama, kelime ve mantık oyunlarını hemen oyna.">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <style>
        :root{--bg:#050816;--card:rgba(255,255,255,.07);--line:rgba(255,255,255,.14);--ink:#eef4ff;--mut:#9cb0cb}
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:radial-gradient(1000px 500px at 10% -10%,#16357a55,transparent 60%),var(--bg);color:var(--ink);min-height:100vh}
        .wrap{max-width:1100px;margin:0 auto;padding:20px 16px 56px}
        .top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:28px}
        .brand{display:flex;align-items:center;gap:10px;color:var(--ink);text-decoration:none;font-weight:900;font-size:18px}
        .brand img{width:34px;height:34px;border-radius:8px}
        .btns{display:flex;gap:8px;flex-wrap:wrap}
        .btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 16px;border-radius:12px;text-decoration:none;font-weight:800;font-size:14px;border:1px solid var(--line);color:var(--ink);background:var(--card)}
        .btn.primary{background:linear-gradient(135deg,#4f8cff,#0ea5e9);border-color:transparent}
        h1{margin:0 0 8px;font-size:clamp(30px,6vw,52px);line-height:1}
        .lead{margin:0 0 26px;color:var(--mut);font-size:17px;line-height:1.6;max-width:680px}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px}
        .game{display:flex;flex-direction:column;gap:10px;padding:16px;border-radius:20px;background:var(--card);border:1px solid var(--line);text-decoration:none;color:var(--ink);transition:transform .15s,border-color .15s}
        .game:hover{transform:translateY(-3px);border-color:#4f8cff}
        .game img{width:64px;height:64px;object-fit:contain;border-radius:14px;background:#ffffff14;padding:6px}
        .game strong{font-size:17px}
        .game span{color:var(--mut);font-size:14px;line-height:1.5;flex:1}
        .game em{font-style:normal;font-weight:800;color:#7dd3fc;font-size:14px}
        .empty{padding:36px 20px;text-align:center;border:1px dashed var(--line);border-radius:20px;color:var(--mut)}
        .note{margin-top:28px;color:var(--mut);font-size:13px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <a class="brand" href="/"><img src="{{ asset('logo.webp') }}" alt="Bilişim Kod">Bilişim Kod</a>
        <div class="btns">
            <a class="btn" href="/">Anasayfa</a>
            <a class="btn primary" href="{{ route('login') }}">Giriş Yap</a>
        </div>
    </div>
    <h1>🎮 Oyunlar</h1>
    <p class="lead">Giriş yapmadan hemen oynayabileceğin kodlama, kelime ve mantık oyunları. Kaldığın yerden devam edebilmen için ilerlemen bu tarayıcıda saklanır.</p>
    @if(count($games))
        <div class="grid">
            @foreach($games as $g)
                <a class="game" href="{{ route('public.game', $g['slug']) }}">
                    <img src="{{ asset($g['image']) }}" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
                    <strong>{{ $g['name'] }}</strong>
                    <span>{{ $g['desc'] }}</span>
                    <em>Oyna →</em>
                </a>
            @endforeach
        </div>
    @else
        <div class="empty">Şu anda herkese açık oyun bulunmuyor. Lütfen daha sonra tekrar kontrol et.</div>
    @endif
    <p class="note">Öğrenci hesabınla giriş yaparsan ödevlerin, XP'n ve rozetlerin de kaydedilir.</p>
</div>
</body>
</html>
