@extends('layout.app')
@section('title', 'Ders Yoklaması')
@section('content')
@include('attendance._style')
@php $labels = \App\Models\LessonAttendanceRecord::STATUSES; @endphp
<div class="att">
    <div class="top">
        <h1>📋 {{ $lesson->classLabel() }} · {{ $lesson->subject }}</h1>
        <div class="att-tools"><a class="btn" href="{{ route('attendance.index', ['tab' => 'history']) }}">← Yoklama Geçmişi</a></div>
    </div>
    @if(session('ok'))<div class="att-flash ok">{{ session('ok') }}</div>@endif

    <div class="att-card">
        <div class="att-chips">
            <div class="att-chip"><strong>{{ $lesson->date->format('d.m.Y') }}</strong><small>{{ $lesson->start_at->format('H:i') }} - {{ $lesson->end_at->format('H:i') }}</small></div>
            <div class="att-chip"><strong>{{ $lesson->total_students }}</strong><small>Sınıf mevcudu</small></div>
            <div class="att-chip ok"><strong>{{ $lesson->present_count }}</strong><small>Geldi</small></div>
            <div class="att-chip bad"><strong>{{ $lesson->absent_count }}</strong><small>Gelmedi</small></div>
        </div>
        @unless($lesson->finalized_at)
            <p class="muted" style="margin:10px 0 0">Bu ders henüz kapatılmadı; ders bitince gelmeyenler otomatik "Gelmedi" yazılır.</p>
        @endunless
    </div>

    <div class="att-card">
        <h3>Öğrenciler</h3>
        <p class="muted" style="margin:0 0 10px;font-size:13px">Öğrenci derste olduğu halde sisteme girememişse durumu elle düzeltebilirsiniz (ör. "Geldi" ya da "İzinli").</p>
        <div class="att-wrap"><table class="att-table">
            <thead><tr><th>#</th><th>Öğrenci</th><th>Durum</th><th>İlk giriş</th><th>Kaynak</th><th>Düzelt</th></tr></thead>
            <tbody>
            @forelse($records as $r)
                @php
                    $late = $r->status === 'present' && $r->first_seen_at && $r->first_seen_at->gt($lesson->start_at->copy()->addMinutes($lateAfter));
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><b>{{ $r->student?->user?->name ?? '—' }}</b></td>
                    <td><span class="att-badge st-{{ $r->status }}">{{ $labels[$r->status] ?? $r->status }}</span>@if($late)<span class="att-late">geç girdi</span>@endif</td>
                    <td>{{ $r->first_seen_at ? $r->first_seen_at->format('H:i') : '—' }}</td>
                    <td>{{ $r->source === 'manual' ? 'Elle' : 'Otomatik' }}</td>
                    <td>
                        <form method="POST" action="{{ route('attendance.lesson.status', [$lesson, $r->student_id]) }}" style="margin:0">
                            @csrf
                            <select class="att-select" name="status" onchange="this.form.submit()" aria-label="Durum">
                                @foreach($labels as $k => $v)<option value="{{ $k }}" @selected($r->status === $k)>{{ $v }}</option>@endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="att-empty">Bu ders için henüz öğrenci kaydı yok.</div></td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
@endsection
