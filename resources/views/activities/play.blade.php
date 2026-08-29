@extends('layout.app')
@section('title', $title)
@section('content')
<style>
    .activity-play-bar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:10px;
    }
    .activity-play-back{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:9px 14px;
        border-radius:10px;
        border:1px solid var(--line, #e5e7eb);
        background:#fff;
        color:var(--ink, #111827);
        font-weight:600;
        font-size:13.5px;
        text-decoration:none;
    }
    .activity-play-back:hover{background:var(--paper,#f3f4f6)}
    .activity-play-back svg{width:16px;height:16px;fill:currentColor}
    .activity-play-title{font-weight:700;font-size:14px;color:var(--ink-soft,#6b7280)}
    .activity-play-frame-wrap{
        border:1px solid var(--line, #e5e7eb);
        border-radius:14px;
        overflow:hidden;
        background:#000;
    }
    .activity-play-frame-wrap iframe{
        width:100%;
        display:block;
        border:0;
    }
</style>

<div class="activity-play-bar">
    <a class="activity-play-back" href="{{ route('activities.index') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Oyun ve Etkinliklere Geri Dön
    </a>
    <span class="activity-play-title">{{ $title }}</span>
</div>

<div class="activity-play-frame-wrap">
    <iframe id="activityPlayFrame" src="{{ $src }}" title="{{ $title }}" allow="fullscreen; autoplay" allowfullscreen></iframe>
</div>

<script>
(function () {
    var frame = document.getElementById('activityPlayFrame');
    if (!frame) return;
    function resize() {
        var top = frame.getBoundingClientRect().top;
        var footer = document.querySelector('.footer');
        var footerH = footer ? footer.offsetHeight + 20 : 20;
        var h = window.innerHeight - top - footerH;
        frame.style.height = Math.max(420, h) + 'px';
    }
    resize();
    window.addEventListener('resize', resize);
})();
</script>
@endsection
