@extends('layout.app')
@section('title', 'Öğrenci Analizi')
@section('content')
@php
    $r = $d['row'];
    $name = $student->user?->name ?? 'Öğrenci';
    $classLabel = trim(($student->schoolClass?->name ?? '').'/'.($student->schoolClass?->section ?? ''), '/');
    $fmt = fn ($v, $suffix = '') => $v === null ? '—' : number_format((float) $v, 1, ',', '.').$suffix;
    $dur = function (int $sec) {
        if ($sec <= 0) return '—';
        $h = intdiv($sec, 3600); $m = intdiv($sec % 3600, 60);
        return $h > 0 ? $h.' sa '.$m.' dk' : max(1, $m).' dk';
    };
    $rq = request()->only(['range', 'from', 'to', 'type']);
    $studentUrl = fn ($s) => route('student-data.analytics.student', ['student' => $s->id] + $rq);
    $bucketColor = $r['bucket_color'] ?? '#64748b';
    $qAnswered = (int) ($r['quiz_answered'] ?? 0);
    $qCorrect = (int) ($r['quiz_correct'] ?? 0);
    $show = fn (string $t) => $type === 'all' || $type === $t;
    $hasSeries = array_sum($d['series']['lessons']) + array_sum($d['series']['homeworks']) + array_sum($d['series']['quiz_correct']) + array_sum($d['series']['quiz_wrong']) > 0;
@endphp
<style>
    .an-wrap{display:grid;gap:14px}
    .an-filter{display:flex;flex-wrap:wrap;gap:10px;align-items:end}
    .an-filter label{display:grid;gap:4px;font-size:12px;font-weight:700;color:#475569}
    .an-filter select,.an-filter input{min-width:140px;margin:0}
    .an-hero{display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;border-radius:16px;padding:18px;background:linear-gradient(135deg,#3E28B8,#5B3DF5 55%,#7c3aed);color:#fff}
    .an-hero h2{margin:0;font-size:26px;font-weight:900;color:#fff}
    .an-hero p{margin:4px 0 0;opacity:.9;font-weight:700}
    .an-score{text-align:center;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);border-radius:16px;padding:12px 22px}
    .an-score strong{display:block;font-size:44px;line-height:1;font-weight:950}
    .an-score span{display:inline-block;margin-top:6px;padding:3px 12px;border-radius:999px;font-weight:900;font-size:12px;background:#fff;color:#111}
    .an-picker{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
    .an-picker select{min-width:260px;margin:0}
    .an-picker input{min-width:160px;margin:0}
    .an-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
    .an-kpi{border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#fff}
    .an-kpi span{display:block;font-size:12px;font-weight:700;color:#64748b}
    .an-kpi strong{display:block;font-size:22px;font-weight:900;margin-top:4px;color:#0f172a}
    .an-kpi small{display:block;margin-top:2px;font-size:12px;font-weight:700;color:#64748b}
    .an-grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .an-grid3{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.5fr);gap:14px}
    .an-card{border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#fff}
    .an-card h3{margin:0 0 4px;font-size:16px}
    .an-card p.an-sub{margin:0 0 10px;font-size:12px;color:#64748b}
    .an-chart{position:relative;height:300px}
    .an-table-wrap{overflow:auto;-webkit-overflow-scrolling:touch;max-height:420px}
    .an-table{width:100%;border-collapse:collapse;font-size:13px}
    .an-table th,.an-table td{padding:8px 10px;border-bottom:1px solid #eef2f7;text-align:left}
    .an-table th{font-size:12px;color:#475569;background:#f8fafc;position:sticky;top:0}
    .an-table td.num,.an-table th.num{text-align:right}
    .an-pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:800;color:#fff}
    .an-bar{height:8px;border-radius:999px;background:#e2e8f0;overflow:hidden;min-width:70px}
    .an-bar i{display:block;height:100%;background:#5B3DF5}
    .an-list{margin:0;padding:0;list-style:none;display:grid;gap:6px}
    .an-list li{padding:8px 10px;border-radius:10px;background:#f8fafc;font-size:13px;display:flex;justify-content:space-between;gap:8px}
    .an-tl{margin:0;padding:0;list-style:none;display:grid;gap:8px}
    .an-tl li{display:grid;grid-template-columns:90px 1fr;gap:10px;font-size:13px;padding-bottom:8px;border-bottom:1px dashed #e2e8f0}
    .an-tl time{color:#64748b;font-weight:700}
    .an-empty{color:#64748b;font-weight:700;padding:14px;text-align:center}
    .an-good{color:#16a34a}.an-bad{color:#dc2626}
    @media (max-width:1200px){.an-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.an-grid2,.an-grid3{grid-template-columns:1fr}}
    @media (max-width:600px){.an-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.an-picker select,.an-picker input,.an-filter select,.an-filter input{min-width:0;width:100%}.an-tl li{grid-template-columns:1fr}}
</style>

<div class="top">
    <h1>Öğrenci Analizi</h1>
    <div class="actions">
        <a class="btn" href="{{ route('student-data.analytics', ['class_id' => $student->school_class_id] + request()->only(['range', 'from', 'to'])) }}">← Sınıf İstatistikleri</a>
        <a class="btn" href="{{ route('student-data.progress-report', $student) }}" target="_blank">Gelişim Karnesi</a>
        <a class="btn" style="background:#16a34a;color:#fff" href="{{ route('student-data.analytics.student.export', ['student' => $student->id, 'format' => 'xlsx'] + request()->query()) }}">⬇ Excel</a>
        <a class="btn" style="background:#dc2626;color:#fff" href="{{ route('student-data.analytics.student.export', ['student' => $student->id, 'format' => 'pdf'] + request()->query()) }}">⬇ PDF</a>
    </div>
</div>

<div class="an-wrap">
    {{-- Öğrenci seçici + gezinme --}}
    <div class="card an-picker">
        @if($prevStudent)<a class="btn" href="{{ $studentUrl($prevStudent) }}" title="{{ $prevStudent->user?->name }}">‹ Önceki</a>@endif
        <input type="search" id="pickFilter" placeholder="Öğrenci ara…" autocomplete="off">
        <select id="pickSelect" aria-label="Öğrenci seç">
            @foreach($picker as $group => $list)
                <optgroup label="{{ $group }}">
                    @foreach($list as $s)
                        <option value="{{ $studentUrl($s) }}" @selected($s->id === $student->id)>{{ $s->user?->name }}{{ $s->student_no ? ' ('.$s->student_no.')' : '' }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @if($nextStudent)<a class="btn" href="{{ $studentUrl($nextStudent) }}" title="{{ $nextStudent->user?->name }}">Sonraki ›</a>@endif
    </div>

    {{-- Filtreler --}}
    <form method="GET" class="card an-filter">
        <label>Tarih aralığı
            <select name="range" id="anRange">
                @foreach(['all' => 'Tüm zamanlar', '7' => 'Son 7 gün', '30' => 'Son 30 gün', '90' => 'Son 90 gün', 'custom' => 'Özel aralık'] as $k => $label)
                    <option value="{{ $k }}" @selected($range['mode'] === (string) $k)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="an-custom" @if($range['mode'] !== 'custom') style="display:none" @endif>Başlangıç <input type="date" name="from" value="{{ $range['from_input'] }}"></label>
        <label class="an-custom" @if($range['mode'] !== 'custom') style="display:none" @endif>Bitiş <input type="date" name="to" value="{{ $range['to_input'] }}"></label>
        <label>İçerik türü
            <select name="type">
                @foreach(['all' => 'Hepsi', 'lesson' => 'Dersler', 'homework' => 'Ödevler', 'quiz' => 'Canlı quiz'] as $k => $label)
                    <option value="{{ $k }}" @selected($type === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Uygula</button>
        <a class="btn" href="{{ route('student-data.analytics.student', $student) }}">Sıfırla</a>
        <a class="btn" href="{{ request()->fullUrlWithQuery(['refresh' => 1]) }}" title="Önbelleği atla">↻ Yenile</a>
    </form>

    {{-- Üst kart --}}
    <div class="an-hero">
        <div>
            <h2>{{ $name }}</h2>
            <p>{{ $classLabel }} · No: {{ $student->student_no ?: '—' }} · Dönem: {{ $range['label'] }}</p>
            <p>Sınıf sırası: <strong>{{ $d['rank'] ? $d['rank'].' / '.$d['class_size'] : '—' }}</strong>
                @if($d['rank'] && $d['class_size'] > 1) · Sınıfın en üst %{{ (int) ceil($d['rank'] / $d['class_size'] * 100) }}'lik dilimi @endif</p>
        </div>
        <div class="an-score"><strong>{{ $fmt($r['score'] ?? null) }}</strong><span style="color:{{ $bucketColor }}">{{ $r['bucket_label'] ?? '—' }}</span></div>
    </div>

    <div class="an-kpis">
        <div class="an-kpi"><span>Ders tamamlama</span><strong>{{ $r['lesson_done'] ?? 0 }}/{{ $r['lesson_total'] ?? 0 }}</strong><small>{{ $fmt($r['lesson_pct'] ?? null, '%') }} · sınıf ort. {{ $fmt($d['class']['lesson_pct'] ?? null, '%') }}</small></div>
        <div class="an-kpi"><span>Ödev tamamlama</span><strong>{{ $r['homework_done'] ?? 0 }}/{{ $r['homework_total'] ?? 0 }}</strong><small>{{ $fmt($r['homework_pct'] ?? null, '%') }} · sınıf ort. {{ $fmt($d['class']['homework_pct'] ?? null, '%') }}</small></div>
        <div class="an-kpi"><span>Quiz doğruluğu</span><strong>{{ $fmt($r['quiz_accuracy'] ?? null, '%') }}</strong><small>{{ $qCorrect }}/{{ $qAnswered }} doğru</small></div>
        <div class="an-kpi"><span>Toplam XP</span><strong>{{ number_format((int) ($r['total_xp'] ?? 0), 0, ',', '.') }}</strong><small>quizden {{ (int) ($r['quiz_xp'] ?? 0) }} XP</small></div>
        <div class="an-kpi"><span>Aktif süre</span><strong>{{ $dur((int) ($r['active_seconds'] ?? 0)) }}</strong><small>son 30 günde {{ (int) ($r['active_days_30'] ?? 0) }} aktif gün</small></div>
        <div class="an-kpi"><span>Son görülme</span><strong style="font-size:16px">{{ !empty($r['last_seen_at']) ? \Carbon\Carbon::parse($r['last_seen_at'])->format('d.m.Y H:i') : '—' }}</strong><small>{{ !empty($r['active_7d']) ? 'Son 7 günde aktif' : 'Son 7 günde aktif değil' }}</small></div>
    </div>

    {{-- Grafikler --}}
    <div class="an-grid3">
        <div class="an-card">
            <h3>Alan Profili</h3>
            <p class="an-sub">Öğrenci ve sınıf ortalaması (0-100)</p>
            <div class="an-chart"><canvas id="chRadar"></canvas></div>
        </div>
        <div class="an-card">
            <h3>Haftalık Etkinlik ve XP</h3>
            <p class="an-sub">Çubuk: tamamlanan işler · çizgi: kazanılan XP</p>
            @if($hasSeries)<div class="an-chart"><canvas id="chWeek"></canvas></div>@else<div class="an-empty">Bu dönemde etkinlik yok.</div>@endif
        </div>
    </div>

    @if($show('quiz'))
    <div class="an-grid3">
        <div class="an-card">
            <h3>Quiz Doğru / Yanlış</h3>
            <p class="an-sub">Seçili dönemdeki tüm canlı quiz cevapları</p>
            @if($qAnswered > 0)<div class="an-chart"><canvas id="chQuizPie"></canvas></div>@else<div class="an-empty">Canlı quiz cevabı yok.</div>@endif
        </div>
        <div class="an-card">
            <h3>Quiz Bazlı Performans</h3>
            <p class="an-sub">Son 10 quizde doğruluk yüzdesi</p>
            @if(count($d['quizzes']))<div class="an-chart"><canvas id="chQuizBar"></canvas></div>@else<div class="an-empty">Henüz quiz yok.</div>@endif
        </div>
    </div>
    @endif

    {{-- Güçlü / zayıf --}}
    @if($show('lesson') && (count($d['strong']) || count($d['weak'])))
    <div class="an-grid2">
        <div class="an-card">
            <h3 class="an-good">💪 Güçlü olduğu dersler</h3>
            <p class="an-sub">Ders içi soruların doğru yüzdesine göre</p>
            <ul class="an-list">@foreach($d['strong'] as $l)<li><span>{{ $l['name'] }}</span><strong class="an-good">%{{ $l['q_pct'] }}</strong></li>@endforeach</ul>
        </div>
        <div class="an-card">
            <h3 class="an-bad">🎯 Tekrar edilmesi gereken dersler</h3>
            <p class="an-sub">En düşük doğru yüzdesi</p>
            <ul class="an-list">@foreach($d['weak'] as $l)<li><span>{{ $l['name'] }}</span><strong class="an-bad">%{{ $l['q_pct'] }}</strong></li>@endforeach</ul>
        </div>
    </div>
    @endif

    {{-- Dersler --}}
    @if($show('lesson'))
    <div class="an-card">
        <h3>Dersler</h3>
        <p class="an-sub">{{ collect($d['lessons'])->where('done', true)->count() }} / {{ count($d['lessons']) }} ders tamamlandı</p>
        @if(count($d['lessons']))
        <div class="an-table-wrap"><table class="an-table">
            <thead><tr><th>Ders</th><th>Durum</th><th>Tarih</th><th class="num">Doğru / Soru</th><th>Başarı</th><th class="num">XP</th><th class="num">Süre</th></tr></thead>
            <tbody>
            @foreach($d['lessons'] as $l)
                <tr>
                    <td>{{ $l['name'] }}</td>
                    <td><span class="an-pill" style="background:{{ $l['done'] ? '#16a34a' : '#94a3b8' }}">{{ $l['done'] ? 'Tamamlandı' : 'Bekliyor' }}</span></td>
                    <td>{{ $l['done_at'] ?? '—' }}</td>
                    <td class="num">{{ $l['q_total'] ? $l['q_correct'].' / '.$l['q_total'] : '—' }}</td>
                    <td>@if($l['q_pct'] !== null)<div class="an-bar"><i style="width:{{ $l['q_pct'] }}%"></i></div>@else — @endif</td>
                    <td class="num">{{ $l['xp'] ?: '—' }}</td>
                    <td class="num">{{ $dur($l['duration']) }}</td>
                </tr>
            @endforeach
            </tbody></table></div>
        @else<div class="an-empty">Bu sınıfa atanmış ders yok.</div>@endif
    </div>
    @endif

    {{-- Ödevler --}}
    @if($show('homework'))
    <div class="an-card">
        <h3>Ödevler</h3>
        <p class="an-sub">{{ collect($d['homeworks'])->where('done', true)->count() }} / {{ count($d['homeworks']) }} ödev teslim edildi</p>
        @if(count($d['homeworks']))
        <div class="an-table-wrap"><table class="an-table">
            <thead><tr><th>Ödev</th><th>Tür</th><th>Son tarih</th><th>Durum</th><th>Teslim</th><th class="num">XP</th><th class="num">Süre</th></tr></thead>
            <tbody>
            @foreach($d['homeworks'] as $h)
                <tr>
                    <td>{{ $h['title'] }}</td><td>{{ $h['type'] }}</td><td>{{ $h['due'] ?? '—' }}</td>
                    <td><span class="an-pill" style="background:{{ $h['done'] ? '#16a34a' : '#94a3b8' }}">{{ $h['done'] ? 'Teslim edildi' : 'Bekliyor' }}</span></td>
                    <td>{{ $h['done_at'] ?? '—' }}</td><td class="num">{{ $h['xp'] ?: '—' }}</td><td class="num">{{ $dur($h['seconds']) }}</td>
                </tr>
            @endforeach
            </tbody></table></div>
        @else<div class="an-empty">Bu sınıfa verilmiş ödev yok.</div>@endif
    </div>
    @endif

    {{-- Quizler --}}
    @if($show('quiz'))
    <div class="an-grid2">
        <div class="an-card">
            <h3>Canlı Quizler</h3>
            @if(count($d['quizzes']))
            <div class="an-table-wrap"><table class="an-table">
                <thead><tr><th>Quiz</th><th>Tarih</th><th class="num">Doğru</th><th class="num">Sıra</th><th class="num">XP</th></tr></thead>
                <tbody>@foreach($d['quizzes'] as $q)<tr><td>{{ $q['title'] }}</td><td>{{ $q['date'] }}</td><td class="num">{{ $q['correct'] }}/{{ $q['answered'] }}</td><td class="num">{{ $q['rank'] ? $q['rank'].' / '.$q['participants'] : '—' }}</td><td class="num">{{ $q['xp'] }}</td></tr>@endforeach</tbody>
            </table></div>
            @else<div class="an-empty">Henüz canlı quiz yok.</div>@endif
        </div>
        <div class="an-card">
            <h3>Yanlış Yapılan Sorular</h3>
            <p class="an-sub">Son 10 yanlış cevap</p>
            @if(count($d['wrong_questions']))
                <ul class="an-list">@foreach($d['wrong_questions'] as $w)<li style="display:block"><strong>{{ $w['question'] }}</strong><br><small style="color:#64748b">{{ $w['quiz'] }} · {{ $w['date'] }}</small></li>@endforeach</ul>
            @else<div class="an-empty">Yanlış cevap kaydı yok 🎉</div>@endif
        </div>
    </div>
    @endif

    {{-- Kronoloji --}}
    <div class="an-card">
        <h3>Son Etkinlikler</h3>
        @if(count($d['timeline']))
            <ul class="an-tl">@foreach($d['timeline'] as $t)<li><time>{{ $t['at'] }}</time><span>{{ $t['icon'] }} {{ $t['text'] }}</span></li>@endforeach</ul>
        @else<div class="an-empty">Bu dönemde kayıtlı etkinlik yok.</div>@endif
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
<script>
(() => {
    // Öğrenci seçici
    const sel = document.getElementById('pickSelect');
    sel?.addEventListener('change', () => { if (sel.value) window.location.href = sel.value; });
    const filter = document.getElementById('pickFilter');
    filter?.addEventListener('input', () => {
        const q = filter.value.trim().toLocaleLowerCase('tr');
        sel.querySelectorAll('option').forEach((o) => { o.hidden = q !== '' && !o.textContent.toLocaleLowerCase('tr').includes(q) && !o.selected; });
        sel.querySelectorAll('optgroup').forEach((g) => { g.hidden = Array.from(g.children).every((o) => o.hidden); });
    });
    const range = document.getElementById('anRange');
    range?.addEventListener('change', () => document.querySelectorAll('.an-custom').forEach(el => { el.style.display = range.value === 'custom' ? '' : 'none'; }));

    if (typeof Chart === 'undefined') return;
    Chart.defaults.color = '#475569';
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.maintainAspectRatio = false;

    const radar = @json($d['radar']);
    new Chart(document.getElementById('chRadar'), {
        type: 'radar',
        data: { labels: radar.labels, datasets: [
            { label: @json($name), data: radar.student, borderColor: '#5B3DF5', backgroundColor: '#5B3DF533', pointBackgroundColor: '#5B3DF5' },
            { label: 'Sınıf ortalaması', data: radar.class, borderColor: '#f59e0b', backgroundColor: '#f59e0b22', pointBackgroundColor: '#f59e0b', borderDash: [5, 4] },
        ] },
        options: { scales: { r: { min: 0, max: 100, ticks: { stepSize: 25, backdropColor: 'transparent' } } }, plugins: { legend: { position: 'bottom' } } },
    });

    const s = @json($d['series']);
    const weekEl = document.getElementById('chWeek');
    if (weekEl) {
        new Chart(weekEl, {
            data: { labels: s.labels, datasets: [
                { type: 'bar', label: 'Ders', data: s.lessons, backgroundColor: '#5B3DF5', stack: 'a', yAxisID: 'y' },
                { type: 'bar', label: 'Ödev', data: s.homeworks, backgroundColor: '#0ea5e9', stack: 'a', yAxisID: 'y' },
                { type: 'bar', label: 'Quiz doğru', data: s.quiz_correct, backgroundColor: '#16a34a', stack: 'a', yAxisID: 'y' },
                { type: 'bar', label: 'Quiz yanlış', data: s.quiz_wrong, backgroundColor: '#ef4444', stack: 'a', yAxisID: 'y' },
                { type: 'line', label: 'XP', data: s.xp, borderColor: '#f59e0b', backgroundColor: '#f59e0b', tension: .3, yAxisID: 'y1', pointRadius: 3 },
            ] },
            options: { scales: { y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Adet' } }, y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'XP' } } }, plugins: { legend: { position: 'bottom' } } },
        });
    }

    const pie = document.getElementById('chQuizPie');
    if (pie) {
        new Chart(pie, { type: 'doughnut', data: { labels: ['Doğru', 'Yanlış'], datasets: [{ data: [{{ $qCorrect }}, {{ max(0, $qAnswered - $qCorrect) }}], backgroundColor: ['#16a34a', '#ef4444'], borderWidth: 2, borderColor: '#fff' }] }, options: { cutout: '60%', plugins: { legend: { position: 'bottom' } } } });
    }
    const quizzes = @json(array_reverse($d['quizzes']));
    const bar = document.getElementById('chQuizBar');
    if (bar && quizzes.length) {
        new Chart(bar, { type: 'bar', data: { labels: quizzes.map(q => q.title.length > 22 ? q.title.slice(0, 21) + '…' : q.title), datasets: [{ label: 'Doğruluk %', data: quizzes.map(q => q.answered ? Math.round(q.correct / q.answered * 100) : 0), backgroundColor: '#5B3DF5', borderRadius: 6 }] }, options: { scales: { y: { min: 0, max: 100 } }, plugins: { legend: { display: false } } } });
    }
})();
</script>
@endpush
@endsection
