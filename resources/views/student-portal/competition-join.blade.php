@extends('layout.app')
@section('title','Canlı Yarışmalar')
@section('content')
@include('competitions._theme')
<div class="cx">
    <div class="cx-hero">
        <div class="cx-hero-icon">🎮</div>
        <div>
            <h1>Canlı Yarışmaya Katıl</h1>
            <p>Öğretmeninin verdiği 6 haneli kodu yaz, bekleme odasına gir.</p>
        </div>
        <div class="cx-chips"><span class="cx-chip">⚡ Hazır ol!</span></div>
    </div>
    <div class="comp-wait-stage" style="min-height:340px">
        <div class="comp-wait-box" style="max-width:520px">
            @if($errors->any())
                <div class="cx-err">⚠️ {{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('student.competitions.join') }}">
                @csrf
                <label for="joinCode" style="text-align:left">Katılım kodu (öğretmenden al)</label>
                <input id="joinCode" class="form-control" type="text" name="join_code" maxlength="6" required placeholder="ABC123" autocomplete="off" autofocus
                       style="text-transform:uppercase;text-align:center;font-size:34px;font-weight:900;letter-spacing:.3em;min-height:68px;margin-bottom:14px">
                <button class="btn btn-primary btn-start" type="submit" style="width:100%">🚀 Yarışmaya Katıl</button>
            </form>
        </div>
    </div>
</div>
@endsection
