<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Canlı Quiz'e Katıl | Bilişim Kod</title>
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <style>
        :root{--bg:#050816;--card:rgba(255,255,255,.07);--line:rgba(255,255,255,.14);--ink:#eef4ff;--mut:#9cb0cb}
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:radial-gradient(1000px 500px at 10% -10%,#16357a55,transparent 60%),radial-gradient(800px 400px at 100% 110%,#5b3df533,transparent 60%),var(--bg);color:var(--ink);min-height:100vh;display:flex;flex-direction:column}
        .wrap{width:100%;max-width:460px;margin:0 auto;padding:20px 16px 48px}
        .top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:26px}
        .brand{display:flex;align-items:center;gap:10px;color:var(--ink);text-decoration:none;font-weight:900;font-size:18px}
        .brand img{width:34px;height:34px;border-radius:8px}
        .link{color:var(--mut);text-decoration:none;font-weight:700;font-size:14px}
        .card{padding:24px 20px;border-radius:24px;background:var(--card);border:1px solid var(--line);box-shadow:0 24px 60px rgba(0,0,0,.35)}
        h1{margin:0 0 6px;font-size:28px;line-height:1.1}
        .lead{margin:0 0 20px;color:var(--mut);font-size:15px;line-height:1.55}
        label{display:block;font-weight:800;font-size:13px;margin:14px 0 6px;color:#cfe0ff}
        input[type=text]{width:100%;height:50px;padding:0 14px;border-radius:14px;border:1px solid var(--line);background:rgba(255,255,255,.08);color:var(--ink);font-size:17px;font-weight:700;outline:none}
        input[type=text]:focus{border-color:#4f8cff;box-shadow:0 0 0 3px rgba(79,140,255,.25)}
        input.code{text-transform:uppercase;letter-spacing:.35em;text-align:center;font-size:24px;font-weight:900}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .btn{width:100%;height:54px;margin-top:20px;border:0;border-radius:16px;background:linear-gradient(135deg,#7c5cff,#4f8cff);color:#fff;font-size:18px;font-weight:900;cursor:pointer}
        .btn:disabled{opacity:.6}
        .err{margin:0 0 4px;padding:10px 12px;border-radius:12px;background:rgba(239,68,68,.16);border:1px solid rgba(239,68,68,.45);color:#fecaca;font-size:14px;font-weight:700}
        .ok{margin:0 0 14px;padding:10px 12px;border-radius:12px;background:rgba(34,197,94,.16);border:1px solid rgba(34,197,94,.45);color:#bbf7d0;font-size:14px;font-weight:700}
        .off{padding:22px;text-align:center;color:var(--mut);line-height:1.6}
        .note{margin-top:18px;color:var(--mut);font-size:13px;line-height:1.5;text-align:center}
        @media (max-width:420px){.row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <a class="brand" href="/"><img src="{{ asset('logo.webp') }}" alt="Bilişim Kod">Bilişim Kod</a>
        <a class="link" href="{{ route('public.games') }}">Oyunlar</a>
    </div>

    <div class="card">
        <h1>🏆 Canlı Quiz</h1>
        @if(session('ok'))<p class="ok">{{ session('ok') }}</p>@endif

        @if(! $enabled)
            <div class="off">Herkese açık canlı quiz şu an kapalı.<br>Quiz açıldığında bu sayfadan katılabilirsin.</div>
        @else
            <p class="lead">Quizi başlatan kişinin verdiği 6 haneli kodu ve adını yaz. Giriş yapmana gerek yok.</p>

            @if($signedInAsMember)
                <p class="err">Hesabınla giriş yapmışsın. Öğrenciysen Öğrenci Paneli &gt; Canlı Yarışmalar bölümünden katıl; bu sayfayı kullanmak için önce çıkış yap.</p>
            @endif
            @if($errors->any())
                @foreach($errors->all() as $e)<p class="err">{{ $e }}</p>@endforeach
            @endif

            <form method="POST" action="{{ route('public.live-quiz.join.post') }}" id="joinForm" autocomplete="off">
                @csrf
                <label for="join_code">Katılım kodu</label>
                <input type="text" id="join_code" name="join_code" class="code" maxlength="6" value="{{ old('join_code', $code) }}" inputmode="text" autocapitalize="characters" required>
                <div class="row">
                    <div>
                        <label for="first_name">Ad</label>
                        <input type="text" id="first_name" name="first_name" maxlength="30" value="{{ old('first_name') }}" autocomplete="given-name" required>
                    </div>
                    <div>
                        <label for="last_name">Soyad</label>
                        <input type="text" id="last_name" name="last_name" maxlength="30" value="{{ old('last_name') }}" autocomplete="family-name" required>
                    </div>
                </div>
                <button class="btn" type="submit" id="joinBtn">Quize Katıl</button>
            </form>
            <p class="note">Adın ve soyadın yarışma sıralamasında diğer katılımcılara görünür.</p>
        @endif
    </div>
</div>
<script>
    (function () {
        var f = document.getElementById('joinForm'), b = document.getElementById('joinBtn');
        if (f && b) f.addEventListener('submit', function () { b.disabled = true; });
    })();
</script>
</body>
</html>
