@extends('layouts.public', ['title' => $title, 'description' => 'Bilişim Kod ödev sayfası.', 'robots' => 'noindex, nofollow'])

@section('content')
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

@if(trim($details) !== '')
    <h2 style="margin-top:0">Ödev açıklaması</h2>
    <div style="background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:16px 18px;white-space:pre-line">{{ $details }}</div>
@endif

@if($attachment)
    <h2>Ödev dosyası <small style="font-weight:500;color:var(--ink-soft);font-size:14px">· {{ $attachment[0] }}</small></h2>
    <div id="hw-preview" class="hw-preview" data-url="{{ $attachment[1] }}" data-name="{{ $attachment[0] }}"><div class="sh-msg">Dosya yükleniyor…</div></div>
@endif

@if($action)
    <p style="margin-top:28px"><a class="btn" href="{{ $action[1] }}" style="font-size:16px;padding:14px 26px">{{ $action[0] }} →</a></p>
    @if($levels && $levels[1])
        <p class="meta">Etkinlik, 1. bölümden başlayarak {{ $levels[1] }}. bölüme kadar açıktır; ödevin bölümleri bunların içindedir.</p>
    @endif
@endif

<p class="note" style="margin-top:32px">Bu sayfa linki bilen herkese açıktır; giriş gerekmez. İlerlemen ve puanın kaydedilmez. Okulundaki hesabınla giriş yapmak istersen <a href="{{ route('login') }}">buradan giriş yapabilirsin</a>.</p>

@if($attachment)
<style>
    .hw-preview{background:var(--surface);border:1px solid var(--line);border-radius:14px;overflow:auto;max-height:80vh;min-height:120px}
    .hw-preview img{display:block;max-width:100%;height:auto;margin:0 auto}
    .hw-preview iframe{display:block;width:100%;height:75vh;border:0}
    .hw-preview .sh-msg{padding:28px 18px;text-align:center;color:var(--ink-soft)}
    .hw-preview .sh-pager{position:sticky;top:0;z-index:2;display:flex;align-items:center;justify-content:center;gap:12px;padding:8px;background:var(--violet-tint);border-bottom:1px solid var(--line)}
    .hw-preview .sh-pager button,.hw-preview .sh-sheet-tabs button{font:inherit;font-size:14px;padding:6px 12px;border:1px solid var(--line);border-radius:8px;background:var(--surface);color:var(--ink);cursor:pointer}
    .hw-preview .sh-pager button:disabled{opacity:.45;cursor:default}
    .hw-preview .sh-pager-info{font-size:14px;color:var(--ink-soft)}
    .hw-preview .sh-docxhost{padding:12px;overflow:auto;background:#eceae3}
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
    window.FilePreview.render(box, box.dataset.url, box.dataset.name);
})();
</script>
@endif
@endsection
