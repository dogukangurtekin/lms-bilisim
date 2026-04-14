@extends('layout.app')
@section('title','Canli Quiz Oturumu')
@section('content')
@php
    $questions = $session->quiz?->questions ?? collect();
    $current = $questions->get($session->current_index);
    $left = (array) ($current?->options['left'] ?? []);
    $right = (array) ($current?->options['right'] ?? []);
@endphp
<div class="top"><h1>Canli Quiz Oturumu</h1></div>
<div class="card" style="margin-bottom:12px;">
    <p><strong>Quiz:</strong> {{ $session->quiz?->title }}</p>
    <p><strong>Katilim Kodu:</strong> <span style="font-size:20px">{{ $session->join_code }}</span></p>
    <p><strong>Durum:</strong> {{ $session->status }} | <strong>Soru:</strong> {{ $session->current_index + 1 }}/{{ $questions->count() }}</p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <form method="POST" action="{{ route('live-quiz.session.lock', $session) }}">@csrf<button class="btn" type="submit">{{ $session->is_locked ? 'Kilidi Ac' : 'Kilitle' }}</button></form>
        <form method="POST" action="{{ route('live-quiz.session.next', $session) }}">@csrf<button class="btn btn-primary" type="submit">Sonraki Soru</button></form>
        <form method="POST" action="{{ route('live-quiz.session.finish', $session) }}">@csrf<button class="btn btn-danger" type="submit">Quizi Bitir</button></form>
    </div>
</div>

<div class="card" style="margin-bottom:12px;">
    <h3>Anlik Durum</h3>
    <div style="display:grid;grid-template-columns:repeat(4,minmax(120px,1fr));gap:8px;">
        <div class="card" style="padding:10px;"><strong>Katilan:</strong> {{ $currentQuestionStats['joined'] }}</div>
        <div class="card" style="padding:10px;"><strong>Cevaplayan:</strong> {{ $currentQuestionStats['answered'] }}</div>
        <div class="card" style="padding:10px;"><strong>Dogru:</strong> {{ $currentQuestionStats['correct'] }}</div>
        <div class="card" style="padding:10px;"><strong>Yanlis:</strong> {{ $currentQuestionStats['wrong'] }}</div>
    </div>

    @if($current)
        <div style="margin-top:10px;">
            <strong>Aktif Soru ({{ strtoupper($current->type) }})</strong>
            <p>{{ $current->question_text }}</p>
            @if($current->type === 'multiple' || $current->type === 'truefalse')
                @foreach((array) $current->options as $idx => $opt)
                    <div>{{ chr(65 + $idx) }}) {{ $opt }}</div>
                @endforeach
            @elseif($current->type === 'dragdrop')
                @foreach($left as $idx => $l)
                    <div>{{ $l }} -> {{ $right[$idx] ?? '-' }}</div>
                @endforeach
            @endif
        </div>
    @endif
</div>

<div class="card" style="margin-bottom:12px;">
    <h3>Katilan Ogrenciler</h3>
    <table>
        <thead><tr><th>#</th><th>Ogrenci</th><th>Katilim</th></tr></thead>
        <tbody>
        @forelse($session->participants as $i => $participant)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $participant->studentUser?->name ?? ('user_'.$participant->student_user_id) }}</td>
                <td>{{ $participant->created_at?->format('H:i:s') }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Henuz katilan yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Canli Siralama / Rapor</h3>
    <table>
        <thead><tr><th>#</th><th>Ogrenci</th><th>Dogru</th><th>Yanlis</th><th>XP</th></tr></thead>
        <tbody>
        @forelse($rows as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row['student_name'] }}</td>
                <td>{{ $row['correct'] }}</td>
                <td>{{ $row['wrong'] }}</td>
                <td>{{ $row['xp'] }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Henuz cevap yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@if($session->status === 'live')
    @push('scripts')
    <script>
        setTimeout(() => window.location.reload(), 4000);
    </script>
    @endpush
@endif
@endsection
