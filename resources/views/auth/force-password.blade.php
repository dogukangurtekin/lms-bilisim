<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Şifreni Güncelle</title>
    <style>
        :root{--paper:#F7F6F2;--surface:#fff;--ink:#16182B;--soft:#585A72;--line:#E4E1D8;--violet:#5B3DF5;--violet-ink:#3E28B8;--tint:#EEEBFD;--bad:#b42318}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;min-height:100dvh;display:grid;place-items:center;padding:16px;font-family:'Inter',Segoe UI,Arial,sans-serif;background:radial-gradient(circle at 20% 15%,var(--tint) 0%,var(--paper) 55%);color:var(--ink)}
        .card{width:min(100%,460px);background:var(--surface);border:1px solid var(--line);border-radius:20px;padding:26px 24px;box-shadow:0 24px 56px rgba(22,24,43,.14)}
        .logo{display:block;margin:0 auto 10px;width:72px;height:72px;object-fit:contain}
        h1{margin:0 0 6px;font-size:22px;text-align:center}
        p.lead{margin:0 0 16px;text-align:center;color:var(--soft);line-height:1.5;font-size:14.5px}
        .hello{background:var(--tint);color:var(--violet-ink);border-radius:12px;padding:10px 12px;font-weight:700;font-size:14px;margin-bottom:14px;text-align:center}
        label{display:block;margin:12px 0 6px;font-weight:700;font-size:13.5px}
        .field{position:relative}
        input[type=password],input[type=text]{width:100%;padding:13px 44px 13px 14px;border:1.5px solid #cbd5e1;border-radius:12px;font:inherit;font-size:16px}
        input:focus{outline:none;border-color:var(--violet);box-shadow:0 0 0 3px rgba(91,61,245,.15)}
        .eye{position:absolute;right:6px;top:50%;transform:translateY(-50%);border:0;background:transparent;font-size:18px;cursor:pointer;padding:8px}
        .rules{margin:12px 0 0;padding:0;list-style:none;font-size:13px;color:var(--soft);line-height:1.7}
        .err{margin:10px 0 0;padding:10px 12px;border-radius:10px;background:#fff1f0;color:var(--bad);font-weight:700;font-size:13.5px}
        button.go{width:100%;margin-top:18px;padding:14px;border:0;border-radius:12px;background:var(--violet);color:#fff;font:inherit;font-weight:800;font-size:16px;cursor:pointer}
        button.go:hover{background:var(--violet-ink)}
        .out{display:block;text-align:center;margin-top:14px;color:var(--soft);font-size:13px}
    </style>
</head>
<body>
<main class="card">
    <img class="logo" src="{{ \App\Support\Brand::logoUrl() }}" alt="Logo">
    <h1>Şifreni güncelle</h1>
    <p class="lead">Hesabının güvenliği için yeni bir şifre belirlemen gerekiyor. Bu işlem <b>yalnızca bir kez</b> yapılır; şifreni güncelleyince devam edebilirsin.</p>
    <div class="hello">{{ $user->name }}</div>

    <form method="POST" action="{{ route('password.force.update') }}" autocomplete="off">
        @csrf
        <label for="pw1">Yeni şifre</label>
        <div class="field">
            <input id="pw1" type="password" name="password" minlength="6" maxlength="72" required autofocus autocomplete="new-password">
            <button type="button" class="eye" data-eye="pw1" aria-label="Şifreyi göster">👁</button>
        </div>
        <label for="pw2">Yeni şifre (tekrar)</label>
        <div class="field">
            <input id="pw2" type="password" name="password_confirmation" minlength="6" maxlength="72" required autocomplete="new-password">
            <button type="button" class="eye" data-eye="pw2" aria-label="Şifreyi göster">👁</button>
        </div>
        @if($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @endif
        <ul class="rules">
            <li>• En az 6 karakter olsun.</li>
            <li>• Eski şifren ve kullanıcı adın olmasın.</li>
            <li>• 123456 gibi kolay tahmin edilen şifreler kabul edilmez.</li>
            <li>• Kullanıcı adın değiştirilemez, yalnızca şifren değişir.</li>
        </ul>
        <button class="go" type="submit">Şifremi Güncelle</button>
    </form>
    <a class="out" href="{{ route('logout.get') }}">Şimdi değil, çıkış yap</a>
</main>
<script>
document.querySelectorAll('[data-eye]').forEach(function (b) {
    b.addEventListener('click', function () {
        var i = document.getElementById(b.dataset.eye);
        i.type = i.type === 'password' ? 'text' : 'password';
    });
});
</script>
</body>
</html>
