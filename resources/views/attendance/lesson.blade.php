@extends('layout.app')
@section('title', 'Ders Yoklaması')
@section('content')
@include('attendance._style')
@php $labels = \App\Models\LessonAttendanceRecord::STATUSES; @endphp
<style>
    .att-pills{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
    .att-pill{position:relative;margin:0;cursor:pointer}
    .att-pill input{position:absolute;opacity:0;inset:0;width:100%;height:100%;margin:0;cursor:pointer}
    .att-pill span{display:inline-block;padding:8px 12px;border-radius:10px;border:2px solid #cbd5e1;background:#fff;color:#475569;font-weight:700;font-size:13px;min-width:64px;text-align:center}
    .att-pill input:checked + span.p-present{background:#dcfce7;border-color:#16a34a;color:#166534}
    .att-pill input:checked + span.p-absent{background:#fee2e2;border-color:#dc2626;color:#b91c1c}
    .att-pill input:checked + span.p-excused{background:#fef3c7;border-color:#d97706;color:#92400e}
    .att-pill input:focus-visible + span{outline:3px solid #2563eb}
    .att-savebar{position:sticky;bottom:0;z-index:5;margin:12px -4px 0;padding:10px 4px;background:linear-gradient(180deg,rgba(255,255,255,0),#fff 35%);display:flex;gap:8px;flex-wrap:wrap}
    .att-savebar .btn{min-height:44px;font-size:15px;font-weight:800}
    @media (max-width:640px){ .att-hide-sm{display:none} }
</style>
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
        <p class="muted" style="margin:0 0 10px;font-size:13px">Her öğrencinin altından Geldi, Gelmedi ya da İzinli seçin ve <b>Kaydet</b>'e basın. Tüm sınıf derstelerse <b>Sınıf Tam</b> herkesi Geldi seçip hemen kaydeder.</p>
        <form method="POST" action="{{ route('attendance.lesson.bulk', $lesson) }}" id="attBulkForm" style="margin:0">
            @csrf
            <div class="att-savebar" style="position:static;background:none;margin:0 0 10px;padding:0">
                <button class="btn" type="button" id="attAllPresent" style="min-height:44px;font-weight:800">✅ Sınıf Tam (herkes geldi)</button>
            </div>
            <div class="att-wrap"><table class="att-table">
                <thead><tr><th>#</th><th>Öğrenci / Durum seç</th><th class="att-hide-sm">Kayıtlı durum</th><th class="att-hide-sm">İlk giriş</th><th class="att-hide-sm">Kaynak</th></tr></thead>
                <tbody>
                @forelse($records as $r)
                    @php
                        $late = $r->status === 'present' && $r->first_seen_at && $r->first_seen_at->gt($lesson->start_at->copy()->addMinutes($lateAfter));
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <b>{{ $r->student?->user?->name ?? '—' }}</b>@if($late) <span class="att-late">geç girdi</span>@endif
                            <div class="att-pills" role="radiogroup" aria-label="Durum">
                                @foreach($labels as $k => $v)
                                    <label class="att-pill"><input type="radio" name="status[{{ $r->student_id }}]" value="{{ $k }}" @checked($r->status === $k)><span class="p-{{ $k }}">{{ $v }}</span></label>
                                @endforeach
                            </div>
                        </td>
                        <td class="att-hide-sm"><span class="att-badge st-{{ $r->status }}">{{ $r->status === 'none' ? 'Henüz girmedi' : ($labels[$r->status] ?? $r->status) }}</span></td>
                        <td class="att-hide-sm">{{ $r->first_seen_at ? $r->first_seen_at->format('H:i') : '—' }}</td>
                        <td class="att-hide-sm">{{ $r->source === 'manual' ? 'Elle' : ($r->source === 'none' ? '—' : 'Otomatik') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="att-empty">Bu sınıfta öğrenci yok.</div></td></tr>
                @endforelse
                </tbody>
            </table></div>
            <div class="att-savebar">
                <button class="btn" type="submit" style="background:#16a34a;color:#fff;border-color:#16a34a">💾 Kaydet</button>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    var form = document.getElementById('attBulkForm');
    var btn = document.getElementById('attAllPresent');
    if (!form || !btn) return;
    btn.addEventListener('click', function () {
        form.querySelectorAll('input[type=radio][value=present]').forEach(function (r) { r.checked = true; });
        btn.disabled = true; btn.textContent = 'Kaydediliyor...';
        form.submit();
    });
})();
</script>
@endsection
