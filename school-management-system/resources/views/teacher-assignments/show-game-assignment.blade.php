@extends('layout.app')
@section('title','Oyun/Uygulama Odevi Onizleme')
@section('content')
<div class="top">
    <h1>Oyun/Uygulama Odevi Onizleme</h1>
    <a class="btn" href="{{ route('teacher.assignments.index') }}">Odevlere Don</a>
</div>
<div class="card">
    <p><b>Icerik:</b> {{ $assignment->game_name }} ({{ $assignment->game_slug }})</p>
    <p><b>Baslik:</b> {{ $assignment->title }}</p>
    <p><b>Teslim:</b> {{ $assignment->due_date?->format('Y-m-d') ?? '-' }}</p>
    <p><b>Level:</b> {{ $assignment->level_from ?? '-' }} - {{ $assignment->level_to ?? '-' }}</p>
    <p><b>Siniflar:</b> {{ $assignment->classes->map(fn($c) => $c->name.'/'.$c->section)->implode(', ') ?: '-' }}</p>
    <p><b>Puanlar:</b> {{ $assignment->levels->map(fn($l) => 'L'.$l->level.':'.$l->points)->implode(', ') ?: '-' }}</p>
</div>
<div class="card" style="padding:10px">
    <p><b>Öğretmen Önizleme:</b> Öğrencideki gibi oyunu/uygulamayı burada oynayabilirsiniz.</p>
    <iframe
        id="teacher-assignment-runner"
        src="{{ $gameUrl }}"
        data-slug="{{ $assignment->game_slug }}"
        data-level-start="{{ (int) ($assignment->level_from ?? 1) }}"
        data-level-end="{{ (int) ($assignment->level_to ?? ($assignment->level_from ?? 1)) }}"
        style="width:100%;height:calc(100vh - 260px);min-height:680px;border:1px solid #d1d5db;border-radius:10px;display:block"
    ></iframe>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var iframe = document.getElementById('teacher-assignment-runner');
    if (!iframe) return;
    var slug = String(iframe.dataset.slug || '');
    var levelStart = Math.max(1, Number(iframe.dataset.levelStart || 1));
    var levelEnd = Math.max(levelStart, Number(iframe.dataset.levelEnd || levelStart));
    var needsPostMessageLock = ['compute-it-runner', 'block-grid-runner', 'lightbot-runner'].includes(slug);
    if (!needsPostMessageLock) return;
    iframe.addEventListener('load', function () {
        var payload = { type: 'SET_LEVEL_RANGE', levelStart: levelStart, levelEnd: levelEnd };
        try {
            iframe.contentWindow.postMessage(payload, '*');
            setTimeout(function () { iframe.contentWindow.postMessage(payload, '*'); }, 150);
        } catch (e) {}
    });
});
</script>
@endpush
@endsection
