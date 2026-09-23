@extends('layout.app')
@section('title','Canlı Yarışmalar')
@section('content')
<div class="top"><h1>Canlı Yarışmaya Katıl</h1></div>
<div class="card" style="max-width:520px;">
    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;color:#b91c1c;padding:12px 14px;margin-bottom:16px;font-size:15px;font-weight:700;">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif
    <form method="POST" action="{{ route('student.competitions.join') }}">
        @csrf
        <label>Kod (Öğretmenden al)</label>
        <input class="form-control" type="text" name="join_code" maxlength="6" required placeholder="ABC123" style="text-transform:uppercase;">
        <button class="btn btn-primary" type="submit">Yarışmaya Katıl</button>
    </form>
</div>
@endsection
