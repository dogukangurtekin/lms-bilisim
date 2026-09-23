@extends('layout.app')
@section('title','Canlı Yarışmalar')
@section('content')
<div class="top"><h1>Canlı Yarışmaya Katıl</h1></div>
<div class="card" style="max-width:520px;">
    <form method="POST" action="{{ route('student.competitions.join') }}">
        @csrf
        <label>Kod (Öğretmenden al)</label>
        <input class="form-control" type="text" name="join_code" maxlength="6" required placeholder="ABC123" style="text-transform:uppercase;">
        @if($errors->any())
            <div style="color:#dc2626;font-size:13px;margin:6px 0">{{ $errors->first() }}</div>
        @endif
        <button class="btn btn-primary" type="submit">Yarışmaya Katıl</button>
    </form>
</div>
@endsection
