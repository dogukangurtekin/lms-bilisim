<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Devam Etmeden Önce</title>
    <style>
        :root{--paper:#F7F6F2;--surface:#fff;--ink:#16182B;--soft:#585A72;--line:#E4E1D8;--violet:#5B3DF5;--violet-ink:#3E28B8;--tint:#EEEBFD;--mint:#0EA57A}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;min-height:100dvh;padding:18px 14px 40px;font-family:'Inter',Segoe UI,Arial,sans-serif;color:var(--ink);background:radial-gradient(circle at 20% 0%,#EEEBFD 0%,var(--paper) 55%)}
        .wrap{width:min(100%,680px);margin:0 auto}
        .head{text-align:center;margin:8px 0 18px}
        .head .emo{font-size:44px;line-height:1}
        h1{margin:8px 0 6px;font-size:24px}
        .lead{margin:0 auto;max-width:520px;color:var(--soft);line-height:1.55;font-size:15px}
        .hello{display:inline-block;margin-top:10px;background:var(--tint);color:var(--violet-ink);border-radius:999px;padding:6px 14px;font-weight:800;font-size:13.5px}
        .card{background:var(--surface);border:1px solid var(--line);border-radius:20px;padding:18px 18px;margin-bottom:14px;box-shadow:0 12px 30px rgba(22,24,43,.07)}
        .card h2{margin:0 0 10px;font-size:16px}
        ul.sum{margin:0;padding:0;list-style:none;display:grid;gap:9px}
        ul.sum li{background:#faf9ff;border:1px solid #ece8ff;border-radius:12px;padding:10px 12px;line-height:1.5;font-size:14.5px}
        .doc{display:grid;grid-template-columns:40px 1fr;gap:12px;padding:12px 0;border-top:1px solid var(--line)}
        .doc:first-of-type{border-top:0;padding-top:2px}
        .doc .ic{font-size:28px;line-height:1.1;text-align:center}
        .doc b{display:block;font-size:14.5px;margin-bottom:3px}
        .doc p{margin:0 0 4px;color:var(--soft);font-size:13.5px;line-height:1.5}
        .doc p em{font-style:normal;font-weight:800;color:var(--ink)}
        .doc a{color:var(--violet-ink);font-weight:800;font-size:13.5px;text-decoration:none}
        .doc a:hover{text-decoration:underline}
        .go{width:100%;margin-top:6px;padding:17px 14px;border:0;border-radius:16px;background:linear-gradient(135deg,#7c5cff,#5B3DF5);color:#fff;font:inherit;font-weight:900;font-size:19px;letter-spacing:.2px;cursor:pointer;box-shadow:0 14px 28px rgba(91,61,245,.35);transition:transform .12s,box-shadow .12s}
        .go:hover{transform:translateY(-2px);box-shadow:0 18px 34px rgba(91,61,245,.42)}
        .go:disabled{opacity:.7;transform:none;cursor:wait}
        .fine{margin:12px 4px 0;text-align:center;color:var(--soft);font-size:12.5px;line-height:1.5}
        .out{display:block;text-align:center;margin-top:12px;color:var(--soft);font-size:13px}
    </style>
</head>
<body>
<main class="wrap">
    <div class="head">
        <div class="emo">{{ $isStudent ? '👋' : '📝' }}</div>
        <h1>Devam etmeden önce</h1>
        <p class="lead">
            @if($isStudent)
                Bilişim Kod'u kullanmaya başlamadan önce bilgilerinin nasıl kullanıldığını ve platformun kurallarını okumanı istiyoruz. Bunu yalnızca bir kez yapacaksın.
            @else
                Platformu kullanmaya devam etmeden önce kişisel verilerin işlenmesine ve kullanım kurallarına ilişkin metinleri okuyup onaylamanız gerekir. Bu işlem bir kez yapılır.
            @endif
        </p>
        <span class="hello">{{ $user->name }}</span>
    </div>

    <section class="card">
        <h2>{{ $isStudent ? 'Kısaca şunlar:' : 'Özetle:' }}</h2>
        <ul class="sum">
            @foreach($summary as $line)<li>{{ $line }}</li>@endforeach
        </ul>
    </section>

    <section class="card">
        <h2>{{ $isStudent ? 'Onayladığın metinler' : 'Onayladığınız metinler' }}</h2>
        @foreach($documents as $d)
            <div class="doc">
                <div class="ic">{{ $d['icon'] }}</div>
                <div>
                    <b>{{ $d['title'] }}</b>
                    <p><em>İçeriği:</em> {{ $d['what'] }}</p>
                    <p><em>Neden:</em> {{ $d['why'] }}</p>
                    <a href="{{ route($d['route']) }}" target="_blank" rel="noopener">Tam metni oku ↗</a>
                </div>
            </div>
        @endforeach
    </section>

    <form method="POST" action="{{ route('consent.accept') }}" id="consentForm">
        @csrf
        <button type="submit" class="go" id="consentBtn">✅ Okudum, Onayladım</button>
    </form>
    <p class="fine">Onayın tarihi ve metin sürümü kayıt altına alınır. Metinler önemli ölçüde değişirse yeniden onay istenir.</p>
    <a class="out" href="{{ route('logout.get') }}">Onaylamıyorum, çıkış yap</a>
</main>
<script>
(function () {
    var form = document.getElementById('consentForm'), btn = document.getElementById('consentBtn');
    if (!form || !btn) return;
    var busy = false;
    form.addEventListener('submit', function (e) {
        if (busy) { e.preventDefault(); return; }
        busy = true; btn.disabled = true;
        // Sayfa uzun süre açık kaldıysa güncel CSRF belirtecini alıp öyle gönder.
        e.preventDefault();
        var send = function () { form.submit(); };
        try {
            fetch(@json(route('password.force.token')), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) { if (d && d.token) form.querySelector('input[name=_token]').value = d.token; send(); })
                .catch(send);
        } catch (err) { send(); }
    });
})();
</script>
</body>
</html>
