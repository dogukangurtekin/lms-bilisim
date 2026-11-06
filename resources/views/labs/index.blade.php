@extends('layout.app')
@section('title', 'Laboratuvar')
@section('content')
<style>
    .labs-page{max-width:1180px;margin:0 auto}
    .labs-hero{margin:0 0 18px}
    .labs-hero h1{margin:0 0 4px;font-size:26px}
    .labs-hero p{margin:0;color:#475569}
    .labs-note{margin:0 0 16px;padding:12px 14px;border-radius:12px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:14px}
    .labs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}
    .lab-tile{background:#fff;border:1px solid #dbe5e3;border-radius:18px;padding:16px;display:flex;flex-direction:column;gap:10px;box-shadow:0 6px 20px rgba(15,42,40,.06)}
    .lab-tile.soon{opacity:.72;border-style:dashed;box-shadow:none}
    .lab-tile .head{display:flex;gap:12px;align-items:center}
    .lab-tile img{width:56px;height:56px;border-radius:14px}
    .lab-tile .ph{width:56px;height:56px;border-radius:14px;background:#e2eeec;display:flex;align-items:center;justify-content:center;font-size:26px}
    .lab-tile .cat{font-size:11px;font-weight:800;letter-spacing:.7px;text-transform:uppercase;color:#b45309}
    .lab-tile h3{margin:2px 0 0;font-size:18px}
    .lab-tile p{margin:0;color:#475569;font-size:14px;line-height:1.5;flex:1}
    .lab-tile .chips{display:flex;gap:6px;flex-wrap:wrap}
    .lab-tile .chip{padding:4px 10px;border-radius:999px;background:#d9f3ef;color:#0f766e;font-size:12px;font-weight:800}
    .lab-tile .chip.soon{background:#f1f5f9;color:#475569}
    .lab-tile .acts{display:flex;gap:8px;flex-wrap:wrap}
    .labs-section{margin:26px 0 12px;font-size:18px}
</style>
<div class="labs-page">
    <div class="labs-hero">
        <h1>🧪 Laboratuvar</h1>
        <p>Etkileşimli simülasyonlarla adım adım görevler çöz; kuralları dene, sonucu hemen gör.</p>
    </div>
    @if($isStudent && count($labs) === 0)
        <div class="labs-note">Henüz sana açılmış bir laboratuvar yok. Öğretmenin ödev verdiğinde burada ve Ödevlerim sayfasında görünür.</div>
    @endif
    <div class="labs-grid">
        @foreach($labs as $slug => $lab)
            <div class="lab-tile">
                <div class="head">
                    <img src="{{ asset(ltrim($lab['icon'], '/')) }}" alt="" width="56" height="56">
                    <div><div class="cat">{{ $lab['category'] }}</div><h3>{{ $lab['title'] }}</h3></div>
                </div>
                <p>{{ $lab['summary'] }}</p>
                <div class="chips"><span class="chip">{{ $lab['grades'] }}</span><span class="chip">{{ $lab['difficulty'] }}</span><span class="chip">{{ $lab['tasks'] }} görev</span></div>
                <div class="acts">
                    @if($isStudent)
                        <a class="btn" href="{{ route('runner.open', ['slug' => $slug, 'from' => 1, 'to' => $lab['tasks']]) }}">Laboratuvarı aç</a>
                    @else
                        <a class="btn" href="{{ url('/'.$slug) }}" target="_blank" rel="noopener">Laboratuvarı aç</a>
                        @if($canAssign)<a class="btn" href="{{ route('activities.assignments.create', $slug) }}">Ödev ver</a>@endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @unless($isStudent)
        <h2 class="labs-section">Yakında</h2>
        <div class="labs-grid">
            @foreach($upcoming as $lab)
                <div class="lab-tile soon">
                    <div class="head"><div class="ph" aria-hidden="true">🔧</div><div><div class="cat">{{ $lab['category'] }}</div><h3>{{ $lab['title'] }}</h3></div></div>
                    <p>{{ $lab['summary'] }}</p>
                    <div class="chips"><span class="chip soon">Hazırlanıyor</span></div>
                </div>
            @endforeach
        </div>
    @endunless
</div>
@endsection
