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
            <div class="att-chip"><strong>{{ $lesson->finalized_at ? $lesson->total_students : $classTotal }}</strong><small>Sınıf mevcudu</small></div>
            <div class="att-chip ok"><strong>{{ $lesson->present_count }}</strong><small>Geldi</small></div>
            <div class="att-chip bad"><strong>{{ $lesson->absent_count }}</strong><small>Gelmedi</small></div>
        </div>
        @unless($lesson->finalized_at)
            <p class="muted" style="margin:10px 0 0">Bu ders henüz kapatılmadı; ders bitince gelmeyenler otomatik "Gelmedi" yazılır. Derse giren öğrenci otomatik "Geldi" olur; elle işaretlediğiniz durumlar ders bitince değişmez.</p>
        @endunless
    </div>

    <div class="att-card">
        <h3>Öğrenciler</h3>
        <p class="muted" style="margin:0 0 10px;font-size:13px">Öğrenci derste olduğu halde sisteme girememişse durumu elle düzeltebilirsiniz: Geldi, Gelmedi ya da İzinli düğmesine basın.</p>
        <form method="POST" action="{{ route('attendance.lesson.all-present', $lesson) }}" style="margin:0 0 12px" onsubmit="return confirm('Sınıftaki tüm öğrenciler (Gelmedi ve İzinli olanlar dahil) Geldi olarak işaretlensin mi?')">
            @csrf
            <button class="btn" type="submit">✅ Sınıf Tam (herkes geldi)</button>
        </form>
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
                    <td><span class="att-badge st-{{ $r->status }}">{{ $r->status === 'none' ? 'Henüz girmedi' : ($labels[$r->status] ?? $r->status) }}</span>@if($late)<span class="att-late">geç girdi</span>@endif</td>
                    <td>{{ $r->first_seen_at ? $r->first_seen_at->format('H:i') : '—' }}</td>
                    <td>{{ $r->source === 'manual' ? 'Elle' : ($r->source === 'none' ? '—' : 'Otomatik') }}</td>
                    <td>
                        <form method="POST" action="{{ route('attendance.lesson.status', [$lesson, $r->student_id]) }}" style="margin:0">
                            @csrf
                            <div style="display:flex;gap:4px;flex-wrap:wrap">
                                @foreach($labels as $k => $v)
                                    <button type="submit" name="status" value="{{ $k }}" class="btn btn-sm st-{{ $k }}" style="{{ $r->status === $k ? 'outline:2px solid #0f172a;font-weight:800' : 'opacity:.6' }}" aria-pressed="{{ $r->status === $k ? 'true' : 'false' }}">{{ $v }}</button>
                                @endforeach
                            </div>
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
