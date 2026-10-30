@extends('layouts.public', ['title' => $title, 'description' => 'Bilişim Kod ödev sayfası.', 'robots' => 'noindex, nofollow'])

@section('content')
@if($gated)
<div class="hw-blur" aria-hidden="true">
@endif
<p class="meta" style="margin-bottom:6px">{{ $typeLabel }}@if($contentName) · {{ $contentName }}@endif</p>
<h1>{{ $title }}</h1>

<div style="display:flex;flex-wrap:wrap;gap:8px;margin:14px 0 22px">
    @if($due)
        <span style="padding:6px 12px;border-radius:999px;font-size:13.5px;font-weight:600;background:{{ $overdue ? '#fee2e2' : '#eef2ff' }};color:{{ $overdue ? '#b91c1c' : '#3E28B8' }}">
            📅 Son teslim: {{ $due->format('d.m.Y') }}@if($overdue) · süresi doldu @endif
        </span>
    @endif
    @if($levels && $levels[0] && $levels[1])
        <span style="padding:6px 12px;border-radius:999px;font-size:13.5px;font-weight:600;background:#fff7ed;color:#c2410c">🎯 Bölüm {{ $levels[0] }}–{{ $levels[1] }}</span>
    @endif
</div>

@if($gated)
    <div style="height:90px;border-radius:14px;background:var(--line);margin-bottom:14px"></div>
    <div style="height:260px;border-radius:14px;background:var(--line)"></div>
@endif
@if(trim($details) !== '')
    <h2 style="margin-top:0">Ödev açıklaması</h2>
    <div style="background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:16px 18px;white-space:pre-line">{{ $details }}</div>
@endif

@if($attachment)
    <h2>Ödev dosyası <small style="font-weight:500;color:var(--ink-soft);font-size:14px">· {{ $attachment[0] }}</small></h2>
    <div id="hw-preview" class="hw-preview" data-url="{{ $attachment[1] }}" data-name="{{ $attachment[0] }}"><div class="sh-msg">Dosya yükleniyor…</div></div>
    @if(preg_match('/\.(pdf|png|jpe?g|webp)$/i', $attachment[1]))
        <a class="hw-open" href="{{ $attachment[1] }}" target="_blank" rel="noopener">⛶ Tam ekranda aç</a>
    @endif
@endif

@if($action)
    <p style="margin-top:28px"><a class="btn" href="{{ $action[1] }}" style="font-size:16px;padding:14px 26px">{{ $action[0] }} →</a></p>
    @if($levels && $levels[1])
        <p class="meta">Etkinlik, 1. bölümden başlayarak {{ $levels[1] }}. bölüme kadar açıktır; ödevin bölümleri bunların içindedir.</p>
    @endif
@endif

<p class="note" style="margin-top:32px">Bu sayfa linki bilen herkese açıktır; giriş gerekmez. İlerlemen ve puanın kaydedilmez. Okulundaki hesabınla giriş yapmak istersen <a href="{{ route('login') }}">buradan giriş yapabilirsin</a>.</p>
@if($gated)
</div>
<style>
    .hw-blur{filter:blur(9px);pointer-events:none;user-select:none;opacity:.8}
    .hw-gate{position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(247,246,242,.45)}
    .hw-gate form{background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:24px;width:min(100%,420px);box-shadow:0 12px 40px rgba(22,24,43,.18)}
    .hw-gate h2{margin:0 0 6px}
    .hw-gate input{display:block;width:100%;font:inherit;padding:12px 14px;margin:14px 0 6px;border:1px solid var(--line);border-radius:10px}
    .hw-gate .err{color:#b91c1c;font-size:14px;margin:0 0 6px}
    .hw-gate button{width:100%;font:inherit;font-weight:600;padding:12px;border:0;border-radius:10px;background:var(--violet);color:#fff;cursor:pointer;margin-top:8px}
</style>
<div class="hw-gate" role="dialog" aria-modal="true" aria-labelledby="hw-gate-title">
    <form method="POST" action="{{ route('public.homework.enter', $token) }}">
        @csrf
        <h2 id="hw-gate-title">Ödeve girmek için</h2>
        <p class="meta" style="margin:0">Adını ve soyadını yaz.</p>
        <input type="text" name="student_name" value="{{ old('student_name') }}" placeholder="Ad Soyad" autocomplete="name" maxlength="120" required autofocus>
        @error('student_name')<p class="err">{{ $message }}</p>@enderror
        <button type="submit">Ödevi Aç</button>
    </form>
</div>
@endif

@if($attachment)
<style>
    /* Ödev dosyası olan sayfada içerik alanı geniş olsun; önizleme tam genişliği kullansın */
    main .container{width:min(100% - 24px,1040px)}
    .hw-preview{background:var(--surface);border:1px solid var(--line);border-radius:14px;overflow:auto;min-height:120px;width:100%}
    .hw-preview img{display:block;width:100%;max-width:100%;height:auto}
    .hw-preview iframe{display:block;width:100%;height:85vh;min-height:480px;border:0}
    .hw-open{display:inline-block;margin:10px 0 0;font-size:14px;font-weight:600}
    @media (max-width:560px){.hw-preview{border-radius:10px}.hw-preview iframe{height:75vh;min-height:360px}}
    .hw-preview .sh-msg{padding:28px 18px;text-align:center;color:var(--ink-soft)}
    .hw-preview .sh-pager{position:sticky;top:0;z-index:2;display:flex;align-items:center;justify-content:center;gap:12px;padding:8px;background:var(--violet-tint);border-bottom:1px solid var(--line)}
    .hw-preview .sh-pager button,.hw-preview .sh-sheet-tabs button{font:inherit;font-size:14px;padding:6px 12px;border:1px solid var(--line);border-radius:8px;background:var(--surface);color:var(--ink);cursor:pointer}
    .hw-preview .sh-pager button:disabled{opacity:.45;cursor:default}
    .hw-preview .sh-pager-info{font-size:14px;color:var(--ink-soft)}
    .hw-preview .sh-docxhost{padding:0;overflow:auto;background:#eceae3}
    .hw-preview .docx-wrapper{padding:12px!important}
    .hw-preview .sh-docxhost section.docx{margin:0 auto 12px!important;box-shadow:0 2px 10px rgba(22,24,43,.12)}
    .hw-preview .sh-docx-page{padding:20px 24px;max-width:820px;margin:0 auto}
    .hw-preview .sh-sheet-tabs{display:flex;flex-wrap:wrap;gap:6px;padding:8px;background:var(--violet-tint);border-bottom:1px solid var(--line);position:sticky;top:0;z-index:2}
    .hw-preview .sh-sheet-tabs button.on{background:var(--violet);border-color:var(--violet);color:#fff}
    .hw-preview .sh-sheet{overflow:auto;padding:10px}
    .hw-preview .sh-sheet table{width:auto;min-width:100%;font-size:13.5px}
    .hw-preview .sh-sheet-imgs{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}
    .hw-preview .sh-sheet-imgs-t{width:100%;font-weight:600}
    .hw-preview .sh-sheet-imgs img{max-width:100%;height:auto;border:1px solid var(--line)}
</style>
<script src="{{ asset('js/file-preview.js') }}?v={{ @filemtime(public_path('js/file-preview.js')) ?: 1 }}"></script>
<script>
(function () {
    var box = document.getElementById('hw-preview');
    if (!box || !window.FilePreview) return;
    // Word sayfaları ve Excel tabloları kutu genişliğine göre ölçeklenir: mobilde sığar, geniş ekranda küçük kalmaz.
    function fit() {
        var w = box.clientWidth; if (!w) return;
        var doc = box.querySelector('.docx-wrapper');
        if (doc) {
            doc.style.zoom = 1;
            var nat = doc.scrollWidth || w;
            doc.style.zoom = Math.max(0.3, Math.min(1.6, w / nat));
        }
        var sheet = box.querySelector('.sh-sheet'), tbl = sheet && sheet.querySelector('table');
        if (tbl) {
            sheet.style.zoom = 1;
            var tw = tbl.scrollWidth + 24;
            sheet.style.zoom = Math.max(0.6, Math.min(1.4, w / tw));
        }
    }
    var raf = 0;
    function schedule() { cancelAnimationFrame(raf); raf = requestAnimationFrame(fit); }
    new MutationObserver(schedule).observe(box, { childList: true, subtree: true });
    window.addEventListener('resize', schedule);
    window.addEventListener('orientationchange', schedule);
    window.FilePreview.render(box, box.dataset.url, box.dataset.name).then(schedule);
})();
</script>
@endif
@endsection
