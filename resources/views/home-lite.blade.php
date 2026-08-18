<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Bilişim Kod - yapay zeka, kodlama ve robotik odaklı modern eğitim platformu.">
    <title>Bilişim Kod | Yapay Zeka, Kodlama, Robotik</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <style>
        body{margin:0;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#050816;color:#eef4ff}
        .wrap{min-height:100vh;display:grid;place-items:center;padding:32px}
        .card{max-width:880px;width:100%;padding:28px;border:1px solid rgba(255,255,255,.12);border-radius:24px;background:rgba(255,255,255,.06);backdrop-filter:blur(12px)}
        h1{margin:0 0 12px;font-size:clamp(40px,8vw,72px);line-height:.95}
        p{margin:0 0 22px;color:#9cb0cb;font-size:18px;line-height:1.7}
        .row{display:flex;gap:12px;flex-wrap:wrap}
        a{display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:0 20px;border-radius:14px;text-decoration:none;font-weight:800}
        .primary{background:linear-gradient(135deg,#4f8cff,#0ea5e9);color:#fff}
        .secondary{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:#fff}
    </style>
</head>
<body>
    <div class="wrap">
        <section class="card">
            <h1>Bilişim Kod</h1>
            <p>Bilişim teknolojileri ve yazılım öğrenimini tek bir modern platformda birleştirir.</p>
            <div class="row">
                <a class="primary" href="{{ route('login') }}">Hemen Başla</a>
                <a class="secondary" href="{{ route('support-requests.demo') }}" onclick="return false;">Demo Talebi</a>
            </div>
        </section>
    </div>
</body>
</html>
