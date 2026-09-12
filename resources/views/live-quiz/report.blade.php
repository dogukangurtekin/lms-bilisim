@extends('layout.app')
@section('title','Canli Quiz Raporu')
@section('content')
<div class="top"><h1>Canli Quiz Raporu</h1></div>

<div class="card" style="margin-bottom:12px;">
    <p><strong>Quiz:</strong> {{ $session->quiz?->title }}</p>
    <p><strong>Katilim Kodu:</strong> {{ $session->join_code }}</p>
    <p><strong>Durum:</strong> {{ $session->status === 'finished' ? 'Tamamlandi' : $session->status }}</p>
    @if($session->started_at_ms && $session->finished_at_ms)
        <p><strong>Sure:</strong> {{ round((($session->finished_at_ms - $session->started_at_ms)) / 1000) }} sn</p>
    @endif
    <a class="btn" href="{{ route('live-quiz.index') }}">Canli Quiz Merkezine Don</a>
</div>

<div class="card" style="margin-bottom:12px;">
    <h3>Soru Bazinda Ozet</h3>
    <table>
        <thead><tr><th>#</th><th>Soru</th><th>Cevaplayan</th><th>Dogru</th><th>Yanlis</th><th>Ort. XP</th></tr></thead>
        <tbody>
        @forelse($questionBreakdown as $q)
            <tr>
                <td>{{ $q['index'] + 1 }}</td>
                <td>{{ $q['question_text'] }}</td>
                <td>{{ $q['answered'] }}</td>
                <td>{{ $q['correct'] }}</td>
                <td>{{ $q['wrong'] }}</td>
                <td>{{ $q['avg_xp'] }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Soru bulunamadi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Ogrenci Siralamasi</h3>
    <table>
        <thead><tr><th>#</th><th>Ogrenci</th><th>Cevaplanan</th><th>Dogru</th><th>Yanlis</th><th>Toplam XP</th></tr></thead>
        <tbody>
        @forelse($rows as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row['student_name'] }}</td>
                <td>{{ $row['answered'] }}</td>
                <td>{{ $row['correct'] }}</td>
                <td>{{ $row['wrong'] }}</td>
                <td>{{ $row['xp'] }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Henuz cevap yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
