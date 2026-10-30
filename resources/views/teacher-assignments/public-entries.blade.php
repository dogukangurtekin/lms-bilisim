@extends('layout.app')
@section('title','Açık Ödev Girişleri')
@section('content')
<div class="top">
    <h1>Açık Ödev Girişleri</h1>
    <a class="btn" href="{{ route('teacher.assignments.index') }}">Ödevlere Dön</a>
</div>
<div class="card">
    <h3>{{ $title }}</h3>
    <p style="color:#6b7280;margin:0 0 12px">Link: <a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a> · Toplam giriş: {{ $entries->total() }}</p>
    <table>
        <thead><tr><th>#</th><th>Ad Soyad</th><th>Giriş Zamanı</th></tr></thead>
        <tbody>
        @forelse($entries as $i => $e)
            <tr>
                <td>{{ $entries->total() - (($entries->currentPage() - 1) * $entries->perPage()) - $i }}</td>
                <td>{{ $e->student_name }}</td>
                <td>{{ $e->created_at?->format('d.m.Y H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Henüz giriş yapan yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $entries->links('partials.pagination') }}
</div>
@endsection
