<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <title>Sertifika</title>
    <style>
        body{margin:0;background:#eef2f7;font-family:Georgia,serif}
        .wrap{width:1120px;max-width:96vw;margin:24px auto;background:#fff;border:12px solid #1e3a8a;padding:48px 56px;box-sizing:border-box}
        h1{font-size:52px;margin:0 0 10px;text-align:center;color:#1e3a8a}
        h2{font-size:28px;margin:0 0 28px;text-align:center;color:#334155}
        p{font-size:20px;line-height:1.8;color:#1f2937;text-align:center}
        .name{font-size:42px;font-weight:700;text-align:center;color:#0f172a;margin:24px 0}
        .signs{display:flex;justify-content:space-between;margin-top:50px}
        .sign{width:42%;text-align:center;font-size:18px;color:#334155}
        .line{border-top:1px solid #64748b;margin-top:40px;padding-top:8px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Basari Sertifikasi</h1>
    <h2>Kodlama ve Yazilim Gelisim Programi</h2>
    <p>Bu belge, <strong>{{ $student->user?->name }}</strong> isimli ogrencinin okul yonetim sistemi uzerinden kodlama, algoritma ve yazilim uygulamalarinda duzenli calisma yaparak gelisim gosterdigini ve egitsel gorevleri basariyla surdurdugunu onaylar.</p>
    <div class="name">{{ $student->user?->name }}</div>
    <p>Toplam Kazanilan XP: <strong>{{ $xp }}</strong> | Sinif: <strong>{{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }}</strong></p>

    <div class="signs">
        <div class="sign"><div class="line">{{ $teacherName }}</div>Ders Ogretmeni</div>
        <div class="sign"><div class="line">{{ $principalName }}</div>Okul Muduru</div>
    </div>
</div>
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>
