@extends('layout.app')
@section('title','Canlı Quiz')
@section('content')
@include('competitions._theme')
<div class="cx" style="min-height:520px">
    <div class="cx-bubbles"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
    <div class="cx-hero">
        <div class="cx-hero-icon spin">🎯</div>
        <div>
            <h1 class="cx-title-wave">@foreach(preg_split('//u', 'Canlı Quiz Katılım', -1, PREG_SPLIT_NO_EMPTY) as $i => $ch)<span style="animation-delay:{{ $i * .08 }}s">{{ $ch === ' ' ? "\u{00a0}" : $ch }}</span>@endforeach</h1>
            <p>Öğretmeninin verdiği 6 karakterli kodu yaz ve quize katıl.</p>
        </div>
        <div class="cx-chips"><span class="cx-chip">⚡ Hazır ol!</span></div>
    </div>
    <div class="comp-wait-stage" style="min-height:380px">
        <div class="comp-wait-box" style="max-width:560px">
            @if($errors->any())
                <div class="cx-err">⚠️ {{ $errors->first() }}</div>
            @endif
            <div class="cx-keys" aria-hidden="true"><span class="cx-key">Q</span><span class="cx-key">U</span><span class="cx-key">İ</span><span class="cx-key">Z</span></div>
            <form method="POST" action="{{ route('student.live-quiz.join') }}">
                @csrf
                <label for="joinCode" style="text-align:left">Katılım kodu</label>
                <input id="joinCode" class="form-control" type="text" name="join_code" maxlength="6" required placeholder="K7M2PX" autocomplete="off" autocapitalize="characters" spellcheck="false" autofocus
                       style="text-transform:uppercase;text-align:center;font-size:36px;font-weight:900;letter-spacing:.3em;min-height:72px;margin-bottom:6px">
                <p style="font-size:12px;margin:0 0 14px">Kodda <b style="color:#fff">O</b>, <b style="color:#fff">0</b>, <b style="color:#fff">I</b> ve <b style="color:#fff">1</b> karakterleri bulunmaz.</p>
                <button class="btn btn-primary btn-start" type="submit" style="width:100%">🚀 Quize Katıl</button>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('joinCode')?.addEventListener('input', function () {
    this.value = this.value.toUpperCase().replace(/[^ABCDEFGHJKLMNPQRSTUVWXYZ23456789]/g, '').slice(0, 6);
});
</script>
@endsection
