@extends('layout.app')
@section('title', 'Başarı İstatistikleri')
@section('content')
@php
    $o = $data['overall'];
    $fmt = fn ($v, $suffix = '') => $v === null ? '—' : number_format((float) $v, 1, ',', '.').$suffix;
    $delta = function ($cur, $old) {
        if ($cur === null || $old === null) return null;
        $d = round($cur - $old, 1);
        return $d;
    };
    $query = request()->query();
    $linkQuery = fn (array $extra = []) => array_merge(request()->only(['academic_year', 'grade_level', 'section', 'range', 'from', 'to']), $extra);
    $studentLinkQuery = request()->only(['range', 'from', 'to']);
    $heat = fn ($v) => $v === null ? 'background:#f1f5f9;color:#94a3b8' : 'background:hsl('.round(max(0, min(100, $v)) * 1.2).',65%,86%);color:#0f172a';
    $hasData = ($o['students'] ?? 0) > 0;
@endphp
<style>
    .an-wrap{display:grid;gap:14px}
    .an-filter{display:flex;flex-wrap:wrap;gap:10px;align-items:end}
    .an-filter label{display:grid;gap:4px;font-size:12px;font-weight:700;color:#475569}
    .an-filter select,.an-filter input{min-width:140px;margin:0}
    .an-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
    .an-kpi{border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#fff}
    .an-kpi span{display:block;font-size:12px;font-weight:700;color:#64748b}
    .an-kpi strong{display:block;font-size:26px;font-weight:900;margin-top:4px;color:#0f172a}
    .an-kpi small{display:block;margin-top:2px;font-size:12px;font-weight:800}
    .an-up{color:#16a34a}.an-down{color:#dc2626}.an-flat{color:#64748b}
    .an-grid2{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.6fr);gap:14px}
    .an-grid2e{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .an-card{border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#fff}
    .an-card h3{margin:0 0 4px;font-size:16px}
    .an-card p.an-sub{margin:0 0 10px;font-size:12px;color:#64748b}
    .an-chart{position:relative;height:300px}
    .an-chart.tall{height:380px}
    .an-table-wrap{overflow:auto;-webkit-overflow-scrolling:touch}
    .an-table{width:100%;border-collapse:collapse;font-size:13px}
    .an-table th,.an-table td{padding:8px 10px;border-bottom:1px solid #eef2f7;text-align:left;white-space:nowrap}
    .an-table th{font-size:12px;color:#475569;background:#f8fafc;cursor:pointer;user-select:none}
    .an-table th.nosort{cursor:default}
    .an-table td.num,.an-table th.num{text-align:right}
    .an-table tr:hover td{background:#faf9ff}
    .an-heat td{text-align:center;font-weight:800;border:2px solid #fff}
    .an-pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:800;color:#fff}
    .an-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .an-rank{display:grid;gap:6px;margin:0;padding:0;list-style:none}
    .an-rank li{display:flex;justify-content:space-between;gap:8px;padding:8px 10px;border-radius:10px;background:#f8fafc;font-size:13px}
    .an-empty{text-align:center;padding:30px;color:#64748b;font-weight:700}
    .an-note{font-size:12px;color:#64748b;line-height:1.5}
    @media (max-width:1200px){.an-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.an-grid2,.an-grid2e,.an-two{grid-template-columns:1fr}}
    @media (max-width:600px){.an-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.an-filter select,.an-filter input{min-width:0;width:100%}.an-filter label{flex:1 1 140px}}
</style>

<div class="top">
    <h1>Başarı İstatistikleri</h1>
    <div class="actions">
        <a class="btn" href="{{ route('student-data.index') }}">← Öğrenci Verileri</a>
        <a class="btn" style="background:#16a34a;color:#fff" href="{{ route('student-data.analytics.export', ['format' => 'xlsx'] + $query) }}">⬇ Excel</a>
        <a class="btn" style="background:#dc2626;color:#fff" href="{{ route('student-data.analytics.export', ['format' => 'pdf'] + $query) }}">⬇ PDF</a>
    </div>
</div>

<div class="an-wrap">
    {{-- Filtreler --}}
    <form method="GET" class="card an-filter" id="anFilter">
        <label>Eğitim yılı
            <select name="academic_year">
                <option value="">Tümü</option>
                @foreach($ctx['options']['years'] as $y)<option value="{{ $y }}" @selected($filters['academic_year'] === (string) $y)>{{ $y }}</option>@endforeach
            </select>
        </label>
        <label>Kademe / sınıf seviyesi
            <select name="grade_level">
                <option value="0">Tümü</option>
                @foreach($ctx['options']['grades'] as $g)<option value="{{ $g }}" @selected($filters['grade_level'] === (int) $g)>{{ $g }}. sınıf</option>@endforeach
            </select>
        </label>
        <label>Şube
            <select name="section">
                <option value="">Tümü</option>
                @foreach($ctx['options']['sections'] as $s)<option value="{{ $s }}" @selected($filters['section'] === (string) $s)>{{ $s }}</option>@endforeach
            </select>
        </label>
        <label>Tarih aralığı
            <select name="range" id="anRange">
                @foreach(['all' => 'Tüm zamanlar', '7' => 'Son 7 gün', '30' => 'Son 30 gün', '90' => 'Son 90 gün', 'custom' => 'Özel aralık'] as $k => $label)
                    <option value="{{ $k }}" @selected($filters['range'] === (string) $k)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="an-custom" @if($filters['range'] !== 'custom') style="display:none" @endif>Başlangıç
            <input type="date" name="from" value="{{ $filters['from'] }}">
        </label>
        <label class="an-custom" @if($filters['range'] !== 'custom') style="display:none" @endif>Bitiş
            <input type="date" name="to" value="{{ $filters['to'] }}">
        </label>
        <button class="btn btn-primary" type="submit">Uygula</button>
        <a class="btn" href="{{ route('student-data.analytics') }}">Sıfırla</a>
        <a class="btn" href="{{ request()->fullUrlWithQuery(['refresh' => 1]) }}" title="Önbelleği atla, güncel veriyi hesapla">↻ Yenile</a>
    </form>

    <div class="an-note">
        <strong>Başarı puanı</strong> panodaki “Başarı Dağılımı” ile aynıdır: ders tamamlama %, ödev tamamlama % (sınıftaki en iyi öğrenciye göre ölçeklenir) ve varsa not ortalamasının ortalaması.
        Düzeyler: Çok İyi 60+, İyi 40-59, Orta 20-39, Düşük 0-19.
        Gösterilen dönem: <strong>{{ $ctx['range_label'] }}</strong> · Kapsam: <strong>{{ $ctx['catalog']->count() }}</strong> erişilebilir sınıftan <strong>{{ count($ctx['class_ids']) }}</strong> sınıf.
    </div>

    @if(! $hasData)
        <div class="card an-empty">Seçili filtrelerde öğrenci bulunamadı.</div>
    @else
    {{-- KPI --}}
    @php
        $kpis = [
            ['Öğrenci', $o['students'], null, 'students', false],
            ['Genel başarı ortalaması', $o['avg_score'], '', 'avg_score', true],
            ['Ders tamamlama', $o['lesson_pct'], '%', 'lesson_pct', true],
            ['Ödev tamamlama', $o['homework_pct'], '%', 'homework_pct', true],
            ['Quiz doğruluğu', $o['quiz_accuracy'], '%', 'quiz_accuracy', true],
            ['Son 7 gün aktif', $o['active_pct'], '%', 'active_pct', true],
        ];
    @endphp
    <div class="an-kpis">
        @foreach($kpis as [$label, $value, $suffix, $key, $isFloat])
            @php $d = $prev ? $delta($o[$key] ?? null, $prev[$key] ?? null) : null; @endphp
            <div class="an-kpi">
                <span>{{ $label }}</span>
                <strong>{{ $isFloat ? $fmt($value, $suffix) : number_format((int) $value, 0, ',', '.') }}</strong>
                @if($prev && $key !== 'students')
                    @if($d === null)<small class="an-flat">önceki dönem verisi yok</small>
                    @elseif($d > 0)<small class="an-up">▲ {{ number_format($d, 1, ',', '.') }} (önceki döneme göre)</small>
                    @elseif($d < 0)<small class="an-down">▼ {{ number_format(abs($d), 1, ',', '.') }} (önceki döneme göre)</small>
                    @else<small class="an-flat">= değişmedi</small>@endif
                @elseif($key === 'students')
                    <small class="an-down">Destek bekleyen: {{ $o['risk'] }}</small>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Grafikler --}}
    <div class="an-grid2">
        <div class="an-card">
            <h3>Başarı Dağılımı</h3>
            <p class="an-sub">Seçili kapsamdaki tüm öğrenciler</p>
            <div class="an-chart"><canvas id="chDist"></canvas></div>
        </div>
        <div class="an-card">
            <h3>Sınıflara Göre Ortalama Başarı</h3>
            <p class="an-sub">Sınıf başarı puanı ortalaması (0-100)</p>
            <div class="an-chart"><canvas id="chClassAvg"></canvas></div>
        </div>
    </div>

    <div class="an-grid2e">
        <div class="an-card">
            <h3>Sınıf İçi Başarı Dağılımı</h3>
            <p class="an-sub">Her sınıfta düzeylerin yüzdesi</p>
            <div class="an-chart tall"><canvas id="chClassDist"></canvas></div>
        </div>
        <div class="an-card">
            <h3>Haftalık Etkinlik</h3>
            <p class="an-sub">Ders, ödev ve quiz cevapları (hafta başına)</p>
            <div class="an-chart tall"><canvas id="chWeekly"></canvas></div>
        </div>
    </div>

    <div class="an-card">
        <h3>Sınıfların Haftalık Etkinliği</h3>
        <p class="an-sub">En etkin sınıflar — haftada tamamlanan ders + ödev + cevaplanan quiz sorusu</p>
        <div class="an-chart tall"><canvas id="chClassWeekly"></canvas></div>
    </div>

    {{-- Isı haritası --}}
    <div class="an-card">
        <h3>Tamamlama Isı Haritası</h3>
        <p class="an-sub">Sınıf × alan. Yeşil yüksek, kırmızı düşük (yüzde).</p>
        <div class="an-table-wrap">
            <table class="an-table an-heat">
                <thead><tr><th class="nosort">Sınıf</th><th class="nosort num">Başarı</th><th class="nosort num">Ders</th><th class="nosort num">Ödev</th><th class="nosort num">Quiz</th><th class="nosort num">Aktif</th></tr></thead>
                <tbody>
                @foreach($data['classes'] as $c)
                    <tr>
                        <td style="text-align:left;font-weight:800"><a href="{{ route('student-data.analytics', $linkQuery(['class_id' => $c['id']])) }}">{{ $c['label'] }}</a></td>
                        <td style="{{ $heat($c['avg_score']) }}">{{ $fmt($c['avg_score']) }}</td>
                        <td style="{{ $heat($c['lesson_pct']) }}">{{ $fmt($c['lesson_pct'], '%') }}</td>
                        <td style="{{ $heat($c['homework_pct']) }}">{{ $fmt($c['homework_pct'], '%') }}</td>
                        <td style="{{ $heat($c['quiz_accuracy']) }}">{{ $fmt($c['quiz_accuracy'], '%') }}</td>
                        <td style="{{ $heat($c['active_pct']) }}">{{ $fmt($c['active_pct'], '%') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sınıf karşılaştırma tablosu --}}
    <div class="an-card">
        <h3>Sınıf Karşılaştırması</h3>
        <p class="an-sub">Sütun başlığına tıklayarak sıralayın · sınıf adına tıklayınca öğrenci listesi açılır</p>
        <div class="an-table-wrap">
            <table class="an-table" id="anClassTable">
                <thead><tr>
                    <th>Sınıf</th><th class="num">Öğrenci</th><th class="num">Ort. Başarı</th>
                    @foreach($buckets as $b)<th class="num" title="{{ $b['label'] }}">{{ explode(' (', $b['label'])[0] }}</th>@endforeach
                    <th class="num">Ders %</th><th class="num">Ödev %</th><th class="num">Quiz %</th><th class="num">Aktif %</th><th class="num">Riskli</th>
                </tr></thead>
                <tbody>
                @foreach($data['classes'] as $c)
                    <tr>
                        <td data-v="{{ $c['label'] }}"><a href="{{ route('student-data.analytics', $linkQuery(['class_id' => $c['id']])) }}" style="font-weight:800">{{ $c['label'] }}</a></td>
                        <td class="num" data-v="{{ $c['students'] }}">{{ $c['students'] }}</td>
                        <td class="num" data-v="{{ $c['avg_score'] ?? -1 }}"><strong>{{ $fmt($c['avg_score']) }}</strong></td>
                        @foreach($buckets as $b)<td class="num" data-v="{{ $c['buckets'][$b['key']] }}">{{ $c['buckets'][$b['key']] }}</td>@endforeach
                        <td class="num" data-v="{{ $c['lesson_pct'] ?? -1 }}">{{ $fmt($c['lesson_pct']) }}</td>
                        <td class="num" data-v="{{ $c['homework_pct'] ?? -1 }}">{{ $fmt($c['homework_pct']) }}</td>
                        <td class="num" data-v="{{ $c['quiz_accuracy'] ?? -1 }}">{{ $fmt($c['quiz_accuracy']) }}</td>
                        <td class="num" data-v="{{ $c['active_pct'] ?? -1 }}">{{ $fmt($c['active_pct']) }}</td>
                        <td class="num" data-v="{{ $c['risk'] }}"><span class="an-pill" style="background:{{ $c['risk'] > 0 ? '#ef4444' : '#16a34a' }}">{{ $c['risk'] }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Seçili sınıf detayı --}}
    @if($selectedClass)
        @php $sc = $selectedClass['agg']; @endphp
        <div class="an-card" id="classDetail">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
                <h3 style="margin:0">{{ $sc['label'] ?? 'Sınıf' }} — Öğrenci Listesi</h3>
                <a class="btn" href="{{ route('student-data.analytics', $linkQuery()) }}">Sınıf seçimini kaldır</a>
            </div>
            <div class="an-two" style="margin-bottom:14px">
                <div>
                    <strong>🏆 En başarılı 5 öğrenci</strong>
                    <ul class="an-rank" style="margin-top:8px">
                        @foreach($selectedClass['top'] as $i => $s)
                            <li><span>{{ $i + 1 }}. <a href="{{ route('student-data.analytics.student', ['student' => $s['id']] + $studentLinkQuery) }}">{{ $s['name'] }}</a></span><strong>{{ $fmt($s['score']) }}</strong></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <strong>🤝 Destek bekleyen 5 öğrenci</strong>
                    <ul class="an-rank" style="margin-top:8px">
                        @foreach($selectedClass['support'] as $s)
                            <li><span><a href="{{ route('student-data.analytics.student', ['student' => $s['id']] + $studentLinkQuery) }}">{{ $s['name'] }}</a></span><strong>{{ $fmt($s['score']) }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="an-table-wrap">
                <table class="an-table" id="anStudentTable">
                    <thead><tr><th>#</th><th>Ad Soyad</th><th>No</th><th class="num">Puan</th><th>Düzey</th><th class="num">Ders</th><th class="num">Ödev</th><th class="num">Quiz %</th><th class="num">XP</th><th class="num">Aktif (dk)</th><th class="nosort"></th></tr></thead>
                    <tbody>
                    @foreach($selectedClass['students'] as $i => $s)
                        <tr>
                            <td data-v="{{ $i + 1 }}">{{ $i + 1 }}</td>
                            <td data-v="{{ $s['name'] }}"><a href="{{ route('student-data.analytics.student', ['student' => $s['id']] + $studentLinkQuery) }}" style="font-weight:800">{{ $s['name'] }}</a></td>
                            <td data-v="{{ $s['no'] }}">{{ $s['no'] }}</td>
                            <td class="num" data-v="{{ $s['score'] }}"><strong>{{ $fmt($s['score']) }}</strong></td>
                            <td data-v="{{ $s['score'] }}"><span class="an-pill" style="background:{{ $s['bucket_color'] }}">{{ explode(' (', $s['bucket_label'])[0] }}</span></td>
                            <td class="num" data-v="{{ $s['lesson_done'] }}">{{ $s['lesson_done'] }}/{{ $s['lesson_total'] }}</td>
                            <td class="num" data-v="{{ $s['homework_done'] }}">{{ $s['homework_done'] }}/{{ $s['homework_total'] }}</td>
                            <td class="num" data-v="{{ $s['quiz_accuracy'] ?? -1 }}">{{ $fmt($s['quiz_accuracy']) }}</td>
                            <td class="num" data-v="{{ $s['total_xp'] }}">{{ number_format($s['total_xp'], 0, ',', '.') }}</td>
                            <td class="num" data-v="{{ $s['active_seconds'] }}">{{ (int) round($s['active_seconds'] / 60) }}</td>
                            <td><a class="btn" href="{{ route('student-data.analytics.student', ['student' => $s['id']] + $studentLinkQuery) }}">Analiz →</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="an-note">Bir sınıfın öğrenci listesini ve öğrenci analizlerini görmek için yukarıdaki tablolardan sınıf adına tıklayın.</div>
    @endif
    @endif
</div>

@if($hasData)
@push('scripts')
<script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
<script>
(() => {
    if (typeof Chart === 'undefined') return;
    const classes = @json($data['classes']);
    const series = @json($data['series']);
    const buckets = @json($buckets);
    const overall = @json($o['buckets']);
    const textColor = getComputedStyle(document.body).color || '#0f172a';
    Chart.defaults.color = '#475569';
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.maintainAspectRatio = false;
    const palette = ['#5B3DF5', '#16a34a', '#f59e0b', '#ef4444', '#0ea5e9', '#ec4899', '#14b8a6', '#8b5cf6'];

    // 1) Genel dağılım
    new Chart(document.getElementById('chDist'), {
        type: 'doughnut',
        data: { labels: buckets.map(b => b.label), datasets: [{ data: buckets.map(b => overall[b.key] || 0), backgroundColor: buckets.map(b => b.color), borderWidth: 2, borderColor: '#fff' }] },
        options: { cutout: '58%', plugins: { legend: { position: 'bottom' } } },
    });

    // 2) Sınıf ortalamaları (yatay)
    const sorted = [...classes].sort((a, b) => (b.avg_score ?? 0) - (a.avg_score ?? 0));
    document.getElementById('chClassAvg').parentElement.style.height = Math.max(300, sorted.length * 26 + 60) + 'px';
    new Chart(document.getElementById('chClassAvg'), {
        type: 'bar',
        data: { labels: sorted.map(c => c.label), datasets: [{ label: 'Ortalama başarı', data: sorted.map(c => c.avg_score ?? 0), backgroundColor: '#5B3DF5', borderRadius: 6 }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { min: 0, max: 100 } } },
    });

    // 3) Sınıf içi dağılım (%100 yığılmış)
    document.getElementById('chClassDist').parentElement.style.height = Math.max(380, classes.length * 28 + 80) + 'px';
    new Chart(document.getElementById('chClassDist'), {
        type: 'bar',
        data: {
            labels: classes.map(c => c.label),
            datasets: buckets.map(b => ({
                label: b.label, backgroundColor: b.color,
                data: classes.map(c => c.students ? Math.round((c.buckets[b.key] || 0) / c.students * 1000) / 10 : 0),
            })),
        },
        options: { indexAxis: 'y', scales: { x: { stacked: true, max: 100, ticks: { callback: v => v + '%' } }, y: { stacked: true } }, plugins: { legend: { position: 'bottom' } } },
    });

    // 4) Haftalık etkinlik (yığılmış sütun)
    if (series && series.labels) {
        new Chart(document.getElementById('chWeekly'), {
            type: 'bar',
            data: {
                labels: series.labels,
                datasets: [
                    { label: 'Ders', data: series.lessons, backgroundColor: '#5B3DF5' },
                    { label: 'Ödev', data: series.homeworks, backgroundColor: '#0ea5e9' },
                    { label: 'Quiz (doğru)', data: series.quiz_correct, backgroundColor: '#16a34a' },
                    { label: 'Quiz (yanlış)', data: series.quiz_wrong, backgroundColor: '#ef4444' },
                ],
            },
            options: { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } },
        });

        // 5) Sınıf bazlı haftalık çizgiler
        const byClass = Object.values(series.by_class || {});
        new Chart(document.getElementById('chClassWeekly'), {
            type: 'line',
            data: {
                labels: series.labels,
                datasets: byClass.map((c, i) => ({ label: c.label, data: c.data, borderColor: palette[i % palette.length], backgroundColor: palette[i % palette.length] + '22', tension: .3, pointRadius: 3, fill: false })),
            },
            options: { scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } }, interaction: { mode: 'index', intersect: false } },
        });
    }

    // Tablo sıralama (başlığa tıkla)
    document.querySelectorAll('#anClassTable, #anStudentTable').forEach((table) => {
        const heads = table.querySelectorAll('thead th');
        heads.forEach((th, col) => {
            if (th.classList.contains('nosort')) return;
            let asc = false;
            th.addEventListener('click', () => {
                asc = !asc;
                const rows = Array.from(table.tBodies[0].rows);
                rows.sort((a, b) => {
                    const av = a.cells[col].dataset.v ?? a.cells[col].textContent.trim();
                    const bv = b.cells[col].dataset.v ?? b.cells[col].textContent.trim();
                    const an = parseFloat(av), bn = parseFloat(bv);
                    const cmp = (!isNaN(an) && !isNaN(bn) && String(an) === String(av) && String(bn) === String(bv))
                        ? an - bn : String(av).localeCompare(String(bv), 'tr');
                    return asc ? cmp : -cmp;
                });
                rows.forEach(r => table.tBodies[0].appendChild(r));
            });
        });
    });
})();
</script>
@endpush
@endif

@push('scripts')
<script>
(() => {
    const range = document.getElementById('anRange');
    if (!range) return;
    const toggle = () => document.querySelectorAll('.an-custom').forEach(el => { el.style.display = range.value === 'custom' ? '' : 'none'; });
    range.addEventListener('change', toggle);
})();
</script>
@endpush
@endsection
