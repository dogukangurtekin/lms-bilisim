@extends('layout.app')
@section('title','Canlı Quiz')
@section('content')
<div class="top"><h1>Canlı Quiz Katılım</h1></div>
<div class="card" style="max-width:520px;">
    <form method="POST" action="{{ route('student.live-quiz.join') }}">
        @csrf
        <label>Kod (Öğretmenden al)</label>
        <input class="form-control" type="text" name="join_code" maxlength="6" required placeholder="ABC123" style="text-transform:uppercase;">
        <button class="btn btn-primary" type="submit">Quize Katıl</button>
    </form>
</div>
@endsection

