@extends('layout.app')
@section('title','Arkadaşlarım')
@section('content')
<div class="top"><h1>Arkadaşlarım</h1></div>

<style>
    .friends-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:12px;
    }
    @media (min-width: 1200px){
        .friends-grid{
            grid-template-columns:repeat(8,minmax(0,1fr));
        }
    }
    .friend-card{
        aspect-ratio:1/1;
        border:1px solid #dbe5f2;
        border-radius:12px;
        padding:12px;
        background:#fff;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        text-align:center;
        gap:8px;
    }
</style>

<div class="card">
    @if($friends->isEmpty())
        <p>Sınıfında henüz başka öğrenci bulunmuyor.</p>
    @else
        <div class="friends-grid">
            @foreach($friends as $friend)
                <article class="friend-card">
                    <img
                        src="{{ $friend['avatar_path'] ? asset($friend['avatar_path']) : asset('logo192.png') }}"
                        alt="{{ $friend['first_name'] }} {{ $friend['last_name'] }}"
                        style="width:78px;height:78px;border-radius:12px;object-fit:cover;background:#f8fafc"
                    >
                    <div style="font-weight:700;line-height:1.2">{{ $friend['first_name'] }}</div>
                    <div style="color:#475569;line-height:1.2">{{ $friend['last_name'] }}</div>
                    <div style="font-weight:800;color:#0f766e">{{ $friend['xp'] }} XP</div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
