@extends('layout.app')
@section('title','Odev Tamamlandi')
@section('content')
<div class="card" style="text-align:center;padding:30px">
    <h1 style="margin:0 0 10px">Tebrikler!</h1>
    <p><b>{{ $homework->title }}</b> odevini basariyla tamamladiniz.</p>
    <div style="font-size:30px;font-weight:800;color:#16a34a;margin:12px 0">+{{ $earnedXp }} XP</div>
    <p>Ogretmenin belirledigi seviyeleri tamamlayarak puan kazandiniz.</p>
    <div class="actions" style="justify-content:center;margin-top:10px">
        <a class="btn" href="{{ route('student.portal.assignments') }}">Odevlerime Don</a>
        <a class="btn" href="{{ route('student.portal.dashboard') }}">Panele Don</a>
    </div>
</div>
@endsection

