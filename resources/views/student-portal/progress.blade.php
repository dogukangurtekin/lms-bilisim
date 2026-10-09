@extends('layout.app')
@section('title','Gelişim Karnem')
@section('content')
<div class="top"><h1>Gelişim Karnem</h1></div>
<div class="actions" style="margin-bottom:10px">
    <a class="btn" target="_blank" href="{{ route('student.portal.progress-report') }}">Detaylı Gelişim Raporu (PDF/Yazdır)</a>
</div>
<div class="v2-metrics">
    <article class="card"><span>Toplam XP</span><strong>{{ $xp }}</strong></article>
    <article class="card"><span>Ortalama</span><strong>{{ $avg }}</strong></article>
    <article class="card"><span>Rozet</span><strong>{{ $student->badges->count() }}</strong></article>
    <article class="card"><span>Avatar</span><strong>{{ $student->currentAvatar?->name ?? '-' }}</strong></article>
</div>
<div class="card">
    <h3 style="margin-top:0">İçerikler — Sistemde Yaptıkların</h3>
    @include('reports.partials.activity-log-grouped', ['log' => $activityLog])
</div>
@endsection
