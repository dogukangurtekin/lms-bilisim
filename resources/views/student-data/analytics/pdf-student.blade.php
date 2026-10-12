<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>Öğrenci Analizi</title>
<style>
    @page { margin: 24px 28px; }
    body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 10px; }
    h1 { font-size: 18px; margin: 0; }
    h2 { font-size: 12px; margin: 14px 0 6px; border-bottom: 2px solid #5B3DF5; padding-bottom: 3px; }
    .muted { color: #64748b; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f1f5f9; text-align: left; font-size: 9px; padding: 5px 6px; border-bottom: 1px solid #cbd5e1; }
    td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .num { text-align: right; }
    .hero { background: #3E28B8; color: #fff; padding: 12px 14px; }
    .hero td { border: 0; color: #fff; }
    .score { font-size: 28px; font-weight: bold; text-align: right; }
    .kpi td { border: 1px solid #e2e8f0; padding: 7px; text-align: center; width: 16.6%; }
    .kpi strong { display: block; font-size: 14px; }
    .kpi span { font-size: 8px; color: #64748b; }
    .bar { background: #e2e8f0; height: 6px; }
    .bar i { display: block; height: 6px; background: #5B3DF5; }
    .pill { color: #fff; padding: 1px 6px; font-size: 8px; font-weight: bold; }
</style>
</head>
<body>
@php
    $r = $d['row'];
    $fmt = fn ($v, $s = '') => $v === null ? '—' : number_format((float) $v, 1, ',', '.').$s;
    $dur = function (int $sec) { if ($sec <= 0) return '—'; $h = intdiv($sec, 3600); $m = intdiv($sec % 3600, 60); return $h > 0 ? $h.' sa '.$m.' dk' : max(1, $m).' dk'; };
    $type = $d['type'];
    $show = fn (string $t) => $type === 'all' || $type === $t;
@endphp
<table class="hero"><tr>
    <td>
        <h1>{{ $student->user?->name }}</h1>
        <div>{{ $r['class_label'] ?? '' }} · No: {{ $student->student_no ?: '—' }}</div>
        <div>Dönem: {{ $rangeLabel }} · Sınıf sırası: {{ $d['rank'] ? $d['rank'].' / '.$d['class_size'] : '—' }} · {{ $generatedAt }}</div>
    </td>
    <td class="score">{{ $fmt($r['score'] ?? null) }}<div style="font-size:10px">{{ $r['bucket_label'] ?? '' }}</div></td>
</tr></table>

<h2>Özet</h2>
<table class="kpi"><tr>
    <td><strong>{{ $r['lesson_done'] ?? 0 }}/{{ $r['lesson_total'] ?? 0 }}</strong><span>Ders (%{{ $fmt($r['lesson_pct'] ?? null) }}) · sınıf {{ $fmt($d['class']['lesson_pct'] ?? null, '%') }}</span></td>
    <td><strong>{{ $r['homework_done'] ?? 0 }}/{{ $r['homework_total'] ?? 0 }}</strong><span>Ödev (%{{ $fmt($r['homework_pct'] ?? null) }}) · sınıf {{ $fmt($d['class']['homework_pct'] ?? null, '%') }}</span></td>
    <td><strong>{{ $fmt($r['quiz_accuracy'] ?? null, '%') }}</strong><span>Quiz doğruluğu ({{ $r['quiz_correct'] ?? 0 }}/{{ $r['quiz_answered'] ?? 0 }})</span></td>
    <td><strong>{{ number_format((int) ($r['total_xp'] ?? 0), 0, ',', '.') }}</strong><span>Toplam XP</span></td>
    <td><strong>{{ $dur((int) ($r['active_seconds'] ?? 0)) }}</strong><span>Aktif süre</span></td>
    <td><strong>{{ (int) ($r['active_days_30'] ?? 0) }}</strong><span>Son 30 gün aktif gün</span></td>
</tr></table>

<h2>Alan Profili (öğrenci / sınıf ortalaması)</h2>
<table>
    @foreach($d['radar']['labels'] as $i => $label)
        <tr>
            <td style="width:20%">{{ $label }}</td>
            <td style="width:40%"><div class="bar"><i style="width:{{ min(100, (float) $d['radar']['student'][$i]) }}%"></i></div></td>
            <td class="num" style="width:12%"><strong>{{ number_format((float) $d['radar']['student'][$i], 1, ',', '.') }}</strong></td>
            <td class="muted" style="width:28%">sınıf: {{ number_format((float) $d['radar']['class'][$i], 1, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

@if($show('lesson'))
<h2>Dersler ({{ collect($d['lessons'])->where('done', true)->count() }}/{{ count($d['lessons']) }} tamamlandı)</h2>
@if(count($d['strong']) || count($d['weak']))
<table><tr>
    <td style="border:0;width:50%"><strong>Güçlü:</strong> {{ collect($d['strong'])->map(fn ($l) => $l['name'].' (%'.$l['q_pct'].')')->implode(', ') }}</td>
    <td style="border:0"><strong>Tekrar edilmeli:</strong> {{ collect($d['weak'])->map(fn ($l) => $l['name'].' (%'.$l['q_pct'].')')->implode(', ') }}</td>
</tr></table>
@endif
<table>
    <thead><tr><th>Ders</th><th>Durum</th><th>Tarih</th><th class="num">Doğru/Soru</th><th class="num">XP</th></tr></thead>
    <tbody>@foreach($d['lessons'] as $l)<tr><td>{{ $l['name'] }}</td><td>{{ $l['done'] ? 'Tamamlandı' : 'Bekliyor' }}</td><td>{{ $l['done_at'] ?? '—' }}</td><td class="num">{{ $l['q_total'] ? $l['q_correct'].'/'.$l['q_total'] : '—' }}</td><td class="num">{{ $l['xp'] ?: '—' }}</td></tr>@endforeach</tbody>
</table>
@endif

@if($show('homework'))
<h2>Ödevler ({{ collect($d['homeworks'])->where('done', true)->count() }}/{{ count($d['homeworks']) }} teslim)</h2>
<table>
    <thead><tr><th>Ödev</th><th>Son tarih</th><th>Durum</th><th>Teslim</th><th class="num">XP</th></tr></thead>
    <tbody>@foreach($d['homeworks'] as $h)<tr><td>{{ $h['title'] }}</td><td>{{ $h['due'] ?? '—' }}</td><td>{{ $h['done'] ? 'Teslim edildi' : 'Bekliyor' }}</td><td>{{ $h['done_at'] ?? '—' }}</td><td class="num">{{ $h['xp'] ?: '—' }}</td></tr>@endforeach</tbody>
</table>
@endif

@if($show('quiz'))
<h2>Canlı Quizler</h2>
<table>
    <thead><tr><th>Quiz</th><th>Tarih</th><th class="num">Doğru</th><th class="num">Sıra</th><th class="num">XP</th></tr></thead>
    <tbody>
    @forelse($d['quizzes'] as $q)<tr><td>{{ $q['title'] }}</td><td>{{ $q['date'] }}</td><td class="num">{{ $q['correct'] }}/{{ $q['answered'] }}</td><td class="num">{{ $q['rank'] ? $q['rank'].'/'.$q['participants'] : '—' }}</td><td class="num">{{ $q['xp'] }}</td></tr>
    @empty<tr><td colspan="5" class="muted">Quiz kaydı yok.</td></tr>@endforelse
    </tbody>
</table>
@if(count($d['wrong_questions']))
<h2>Yanlış Yapılan Sorular</h2>
<table><tbody>@foreach($d['wrong_questions'] as $w)<tr><td>{{ $w['question'] }}<div class="muted">{{ $w['quiz'] }} · {{ $w['date'] }}</div></td></tr>@endforeach</tbody></table>
@endif
@endif
</body>
</html>
