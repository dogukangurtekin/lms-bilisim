@extends('layout.app')
@section('title','Öğrenci Blok Projeleri')
@section('content')
<div class="top">
    <h1>Öğrenci Blok Projeleri</h1>
    <a class="btn" href="{{ route('teacher.assignments.index') }}">Ödevlere Dön</a>
</div>
<div class="card">
    <p style="color:#6b7280;margin:0 0 12px">Blok Kodlama Stüdyosu'nda öğrencilerin sunucuya kaydettiği projeler. "İncele" projeyi salt okunur modda açar, öğrencinin projesi değişmez.</p>
    <table>
        <thead><tr><th>Öğrenci</th><th>Sınıf</th><th>Proje</th><th>Son güncelleme</th><th>İşlem</th></tr></thead>
        <tbody>
        @forelse($projects as $p)
            @php($cls = $p->user?->student?->schoolClass)
            <tr>
                <td>{{ $p->user?->name ?? '-' }}</td>
                <td>{{ $cls ? $cls->name.'/'.$cls->section : '-' }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->updated_at?->format('d.m.Y H:i') }}</td>
                <td><a class="btn" href="{{ url('/scratch-runner') }}?project={{ $p->id }}" target="_blank" rel="noopener">İncele</a></td>
            </tr>
        @empty
            <tr><td colspan="5">Henüz kaydedilmiş proje yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $projects->links('partials.pagination') }}
</div>
@endsection
