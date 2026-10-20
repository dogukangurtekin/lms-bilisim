@extends('layout.app')
@section('title','Canlı Yarışmalar')
@section('content')
@include('competitions._theme')
<div class="cx" style="min-height:540px">
    <div class="cx-bubbles"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
    <div class="cx-hero">
        <div class="cx-hero-icon spin">🏁</div>
        <div>
            <h1 class="cx-title-wave">@foreach(preg_split('//u', 'Canlı Yarışmalar', -1, PREG_SPLIT_NO_EMPTY) as $i => $ch)<span style="animation-delay:{{ $i * .08 }}s">{{ $ch === ' ' ? "\u{00a0}" : $ch }}</span>@endforeach</h1>
            <p>Canlı quiz, oyun yarışması ve klavye yarışması için tek giriş. Öğretmeninin verdiği kodu yaz, nereye gireceğini sistem bulur.</p>
        </div>
        <div class="cx-chips"><span class="cx-chip">🎯 Quiz</span><span class="cx-chip">🎮 Oyun</span><span class="cx-chip">⌨️ Klavye</span></div>
    </div>
    <div class="comp-wait-stage" style="min-height:400px">
        <div class="comp-wait-box" style="max-width:580px">
            @if($errors->any())
                <div class="cx-err">⚠️ {{ $errors->first() }}</div>
            @endif
            <div class="cx-keys" aria-hidden="true"><span class="cx-key">K</span><span class="cx-key">O</span><span class="cx-key">D</span></div>
            <form method="POST" action="{{ route('student.competitions.join') }}">
                @csrf
                <label for="joinCode" style="text-align:left">Katılım kodu</label>
                <input id="joinCode" class="form-control" type="text" name="join_code" value="{{ old('join_code') }}" maxlength="6" required placeholder="K7M2PX" autocomplete="off" autocapitalize="characters" spellcheck="false" autofocus
                       style="text-transform:uppercase;text-align:center;font-size:36px;font-weight:900;letter-spacing:.3em;min-height:72px;margin-bottom:6px">
                <p style="font-size:12px;margin:0 0 14px">Kodda <b style="color:#fff">O</b>, <b style="color:#fff">0</b>, <b style="color:#fff">I</b> ve <b style="color:#fff">1</b> karakterleri bulunmaz.</p>
                <button class="btn btn-primary btn-start" type="submit" style="width:100%">🚀 Katıl</button>
            </form>
        </div>
    </div>

    @if(($openRooms ?? collect())->isNotEmpty())
    <div class="cx-panel" style="margin-top:18px">
        <h3 style="margin:0 0 4px">🏆 Açık Canlı Yarışmalar</h3>
        <p style="margin:0 0 12px;font-size:13px;opacity:.85">Kod girmeden, aşağıdan tek tıkla katılabilirsin.</p>
        <div style="display:grid;gap:10px">
            @foreach($openRooms as $openRoom)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:12px 14px;border-radius:14px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2)">
                    <div style="min-width:0">
                        <b style="font-size:16px">{{ $openRoom->game_name }}</b>
                        <div style="font-size:12px;opacity:.85">
                            Seviye {{ $openRoom->level_from }}–{{ $openRoom->level_to }} · {{ (int) ($openRoom->duration_seconds / 60) }} dk ·
                            {{ $openRoom->status === 'live' ? '● Canlı' : '⏳ Lobide' }}
                            @if($openRoom->schoolClass) · {{ $openRoom->schoolClass->name }}/{{ $openRoom->schoolClass->section }} @endif
                        </div>
                    </div>
                    <a class="btn btn-primary" href="{{ route('student.competitions.open-join', $openRoom) }}">Yarışmaya Katıl</a>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
<script>
document.getElementById('joinCode')?.addEventListener('input', function () {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6);
});
</script>
@endsection
