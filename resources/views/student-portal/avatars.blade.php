@extends('layout.app')
@section('title','Avatarlarım')
@section('content')
<div class="top"><h1>Avatar Mağazası</h1></div>

<div class="v2-metrics">
    <article class="card"><span>Toplam XP</span><strong>{{ $xp }}</strong></article>
    <article class="card"><span>Harcanan XP</span><strong>{{ $spent }}</strong></article>
    <article class="card"><span>Kalan XP</span><strong>{{ $availableXp }}</strong></article>
</div>

@if($errors->any())
    <div class="card" style="color:#b91c1c">{{ $errors->first() }}</div>
@endif

<div class="card">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px">
        @foreach($avatars as $avatar)
            @php
                $isOwned = in_array($avatar->id, $owned, true);
                $isCurrent = (int) ($student->current_avatar_id ?? 0) === (int) $avatar->id;
                $cost = (int) $avatar->required_xp;
            @endphp
            <div style="border:1px solid #dbe5f2;border-radius:12px;padding:10px;background:#fff">
                <img src="{{ asset($avatar->image_path) }}" alt="{{ $avatar->name }}" style="width:100%;height:110px;object-fit:contain;border-radius:10px;background:#f8fafc">
                <h4 style="margin:8px 0 4px">{{ $avatar->name }}</h4>
                <div style="font-size:13px;color:#475569;margin-bottom:8px">Fiyat: {{ $cost }} XP</div>
                @if($isCurrent)
                    <span class="badge">Kullanılıyor</span>
                @elseif($isOwned)
                    <form method="POST" action="{{ route('student.portal.avatars.equip', $avatar) }}">
                        @csrf
                        <button class="btn" type="submit">Kullan</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('student.portal.avatars.buy', $avatar) }}">
                        @csrf
                        <button class="btn" type="submit" @disabled($availableXp < $cost)>Satın Al</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
