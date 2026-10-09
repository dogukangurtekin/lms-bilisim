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
    <table>
        <thead>
            <tr>
                <th>Tür</th>
                <th>İçerik</th>
                <th>Durum</th>
                <th>Sonuç</th>
                <th>Kazanılan XP</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
        @forelse($activityLog as $item)
            <tr>
                <td>{{ $item['kind'] }}</td>
                <td>{{ $item['title'] }}</td>
                <td>{{ $item['status'] }}</td>
                <td>{{ $item['result'] }}</td>
                <td>{{ (int) $item['xp'] }}</td>
                <td>{{ $item['sort_date'] ? \Carbon\Carbon::parse($item['sort_date'])->format('d.m.Y H:i') : '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Kayıt yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
