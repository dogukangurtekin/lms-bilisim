<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5B3DF5">
    <title>{{ $title ?? 'Bilişim Kod' }} | Bilişim Kod</title>
    <meta name="description" content="{{ $description ?? 'Bilişim Kod - okullar için kodlama, robotik ve yapay zekâ platformu.' }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/logo-icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ url('/logo-icon-512.png') }}">
    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:site_name" content="Bilişim Kod">
    <meta property="og:title" content="{{ $title ?? 'Bilişim Kod' }} | Bilişim Kod">
    <meta property="og:description" content="{{ $description ?? 'Bilişim Kod - okullar için kodlama, robotik ve yapay zekâ platformu.' }}">
    <meta property="og:image" content="{{ url('/logo512.png') }}">
    @include('partials.analytics')
    <style>
        :root{--paper:#F7F6F2;--surface:#fff;--ink:#16182B;--ink-soft:#585A72;--line:#E4E1D8;--violet:#5B3DF5;--violet-ink:#3E28B8;--violet-tint:#EEEBFD}
        *{box-sizing:border-box}
        body{margin:0;background:var(--paper);color:var(--ink);font:16px/1.65 Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
        a{color:var(--violet-ink)}
        a:focus-visible,button:focus-visible{outline:3px solid var(--violet);outline-offset:2px;border-radius:4px}
        .skip-link{position:absolute;left:-9999px;top:8px;background:var(--ink);color:#fff;padding:10px 14px;border-radius:8px;z-index:100}
        .skip-link:focus{left:8px}
        .container{width:min(100% - 32px,820px);margin-inline:auto}
        header.site{border-bottom:1px solid var(--line);background:var(--surface)}
        header.site .container{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 0;width:min(100% - 32px,1100px)}
        .brand{display:flex;align-items:center;gap:10px;font-weight:700;color:var(--ink);text-decoration:none}
        .brand img{width:32px;height:auto;border-radius:7px}
        .btn{display:inline-block;background:var(--violet);color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px;font-weight:600;font-size:14.5px}
        main{padding:40px 0 56px}
        h1{font-size:clamp(26px,5vw,36px);line-height:1.2;margin:0 0 8px}
        h2{font-size:20px;margin:32px 0 8px}
        .meta{color:var(--ink-soft);font-size:14px;margin:0 0 24px}
        ul{padding-left:22px}
        li{margin:4px 0}
        table{width:100%;border-collapse:collapse;font-size:14.5px;margin:12px 0;background:var(--surface)}
        th,td{border:1px solid var(--line);padding:10px;text-align:left;vertical-align:top}
        th{background:var(--violet-tint)}
        .table-wrap{overflow-x:auto}
        .note{background:var(--violet-tint);border-radius:12px;padding:14px 16px;font-size:14.5px}
        @media (max-width:560px){main{padding:28px 0 40px}}
    </style>
</head>
<body>
<a href="#icerik" class="skip-link">İçeriğe geç</a>
<header class="site">
    <div class="container">
        <a href="/" class="brand"><img src="{{ asset('logo.webp') }}" alt="" width="32" height="18"> Bilişim Kod</a>
        <a href="{{ route('login') }}" class="btn">Giriş Yap</a>
    </div>
</header>
<main id="icerik">
    <div class="container">
        @yield('content')
    </div>
</main>
@include('partials.public-footer')
@include('partials.cookie-notice')
</body>
</html>
