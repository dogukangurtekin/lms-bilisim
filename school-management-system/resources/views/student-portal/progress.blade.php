@extends('layout.app')
@section('title','Gelisim Karnem')
@section('content')
<div class="top"><h1>Gelisim Karnem</h1></div>
<div class="actions" style="margin-bottom:10px">
    <a class="btn" target="_blank" href="{{ route('student.portal.progress-report') }}">Detayli Gelisim Raporu (PDF/Yazdir)</a>
</div>
<div class="v2-metrics">
    <article class="card"><span>Toplam XP</span><strong>{{ $xp }}</strong></article>
    <article class="card"><span>Ortalama</span><strong>{{ $avg }}</strong></article>
    <article class="card"><span>Rozet</span><strong>{{ $student->badges->count() }}</strong></article>
    <article class="card"><span>Avatar</span><strong>{{ $student->currentAvatar?->name ?? '-' }}</strong></article>
</div>
<div class="card">
    <table>
        <thead><tr><th>Icerik</th><th>Tamamlandi</th><th>Kazanilan XP</th><th>Tarih</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $contentLabels[$r->content_id] ?? $r->content_id }}</td>
                <td>{{ $r->completed ? 'Evet' : 'Hayir' }}</td>
                <td>{{ $r->xp_awarded }}</td>
                <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Kayit yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $rows->links('partials.pagination') }}
</div>
@endsection
