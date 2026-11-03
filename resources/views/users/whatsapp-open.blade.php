<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>WhatsApp açılıyor</title>
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f7f6f2;color:#16182b}
        .card{width:min(100%,440px);background:#fff;border:1px solid #e4e1d8;border-radius:20px;padding:26px 22px;text-align:center;box-shadow:0 24px 56px rgba(22,24,43,.14)}
        h1{margin:0 0 8px;font-size:20px}
        p{margin:0 0 16px;color:#585a72;line-height:1.5;font-size:14.5px}
        a.go{display:block;padding:14px;border-radius:14px;background:#16a34a;color:#fff;font-weight:800;font-size:17px;text-decoration:none}
        small{display:block;margin-top:12px;color:#585a72}
    </style>
</head>
<body>
<main class="card">
    <div style="font-size:40px">📲</div>
    <h1>{{ $name }} için giriş bilgileri hazır</h1>
    <p>Yeni şifre oluşturuldu. WhatsApp birkaç saniye içinde hazır mesajla açılacak; açılmazsa aşağıdaki düğmeye basın ve alıcıyı seçin.</p>
    <a class="go" id="waLink" href="{{ $url }}">WhatsApp'ta Aç</a>
    <small>Bu sekmeyi işiniz bitince kapatabilirsiniz.</small>
</main>
<script>
    setTimeout(function () { window.location.href = document.getElementById('waLink').href; }, 600);
</script>
</body>
</html>
