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
    <p style="margin-top:18px"><a href="{{ $attachment[1] }}" style="display:inline-flex;gap:8px;align-items:center;font-weight:600">📎 {{ $attachment[0] }} <span style="color:var(--ink-soft);font-weight:500">(indir)</span></a></p>
@endif

@if($action)
    <p style="margin-top:28px"><a class="btn" href="{{ $action[1] }}" style="font-size:16px;padding:14px 26px">{{ $action[0] }} →</a></p>
    @if($levels && $levels[1])
        <p class="meta">Etkinlik, 1. bölümden başlayarak {{ $levels[1] }}. bölüme kadar açıktır; ödevin bölümleri bunların içindedir.</p>
    @endif
@endif

<p class="note" style="margin-top:32px">Bu sayfa linki bilen herkese açıktır; giriş gerekmez. İlerlemen ve puanın kaydedilmez. Okulundaki hesabınla giriş yapmak istersen <a href="{{ route('login') }}">buradan giriş yapabilirsin</a>.</p>
@endsection
