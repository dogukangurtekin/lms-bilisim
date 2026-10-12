<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>Başarı İstatistikleri</title>
<style>
    @page { margin: 22px 26px; }
    body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 10px; }
    h1 { font-size: 18px; margin: 0 0 2px; }
    h2 { font-size: 13px; margin: 16px 0 6px; border-bottom: 2px solid #5B3DF5; padding-bottom: 3px; }
    .muted { color: #64748b; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f1f5f9; text-align: left; font-size: 9px; padding: 5px 6px; border-bottom: 1px solid #cbd5e1; }
    td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
    .num { text-align: right; }
    .kpi td { border: 1px solid #e2e8f0; padding: 8px; text-align: center; width: 16.6%; }
    .kpi strong { display: block; font-size: 17px; }
    .kpi span { font-size: 8px; color: #64748b; }
    .bar { background: #e2e8f0; height: 7px; }
    .bar i { display: block; height: 7px; background: #5B3DF5; }
    .pill { color: #fff; padding: 1px 6px; font-size: 8px; font-weight: bold; }
    .dist td { padding: 0; height: 12px; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>
@php
    $o = $data['overall'];
    $fmt = fn ($v, $s = '') => $v === null ? '—' : number_format((float) $v, 1, ',', '.').$s;
@endphp
<h1>Öğrenci Başarı İstatistikleri</h1>
<div class="muted">{{ $scopeLabel }} · Dönem: {{ $rangeLabel }} · Oluşturulma: {{ $generatedAt }}</div>

<h2>Genel Özet</h2>
<table class="kpi"><tr>
    <td><strong>{{ $o['students'] }}</strong><span>Öğrenci</span></td>
    <td><strong>{{ $fmt($o['avg_score']) }}</strong><span>Ortalama başarı</span></td>
    <td><strong>{{ $fmt($o['lesson_pct'], '%') }}</strong><span>Ders tamamlama</span></td>
    <td><strong>{{ $fmt($o['homework_pct'], '%') }}</strong><span>Ödev tamamlama</span></td>
    <td><strong>{{ $fmt($o['quiz_accuracy'], '%') }}</strong><span>Quiz doğruluğu</span></td>
    <td><strong>{{ $fmt($o['active_pct'], '%') }}</strong><span>Son 7 gün aktif</span></td>
</tr></table>

<h2>Başarı Dağılımı</h2>
<table>
    @foreach($buckets as $b)
        @php $cnt = $o['buckets'][$b['key']] ?? 0; $pct = $o['students'] ? round($cnt / $o['students'] * 100, 1) : 0; @endphp
        <tr>
            <td style="width:22%">{{ $b['label'] }}</td>
            <td><div class="bar"><i style="width:{{ $pct }}%;background:{{ $b['color'] }}"></i></div></td>
            <td class="num" style="width:16%">{{ $cnt }} öğrenci · %{{ number_format($pct, 1, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

<h2>Sınıf Karşılaştırması</h2>
<table>
    <thead><tr><th>Sınıf</th><th class="num">Öğrenci</th><th class="num">Ort. Başarı</th>
        @foreach($buckets as $b)<th class="num">{{ explode(' (', $b['label'])[0] }}</th>@endforeach
        <th class="num">Ders %</th><th class="num">Ödev %</th><th class="num">Quiz %</th><th class="num">Aktif %</th><th class="num">Riskli</th></tr></thead>
    <tbody>
    @foreach($data['classes'] as $c)
        <tr>
            <td><strong>{{ $c['label'] }}</strong></td><td class="num">{{ $c['students'] }}</td><td class="num"><strong>{{ $fmt($c['avg_score']) }}</strong></td>
            @foreach($buckets as $b)<td class="num">{{ $c['buckets'][$b['key']] }}</td>@endforeach
            <td class="num">{{ $fmt($c['lesson_pct']) }}</td><td class="num">{{ $fmt($c['homework_pct']) }}</td>
            <td class="num">{{ $fmt($c['quiz_accuracy']) }}</td><td class="num">{{ $fmt($c['active_pct']) }}</td><td class="num">{{ $c['risk'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="page-break"></div>
<h2>Öğrenci Listesi ({{ count($students) }})</h2>
<table>
    <thead><tr><th>Sınıf</th><th>No</th><th>Ad Soyad</th><th class="num">Puan</th><th>Düzey</th><th class="num">Ders</th><th class="num">Ödev</th><th class="num">Quiz %</th><th class="num">XP</th><th class="num">Aktif (dk)</th></tr></thead>
    <tbody>
    @foreach($students as $s)
        <tr>
            <td>{{ $s['class_label'] }}</td><td>{{ $s['no'] }}</td><td>{{ $s['name'] }}</td>
            <td class="num"><strong>{{ $fmt($s['score']) }}</strong></td>
            <td><span class="pill" style="background:{{ $s['bucket_color'] }}">{{ explode(' (', $s['bucket_label'])[0] }}</span></td>
            <td class="num">{{ $s['lesson_done'] }}/{{ $s['lesson_total'] }}</td><td class="num">{{ $s['homework_done'] }}/{{ $s['homework_total'] }}</td>
            <td class="num">{{ $fmt($s['quiz_accuracy']) }}</td><td class="num">{{ number_format($s['total_xp'], 0, ',', '.') }}</td><td class="num">{{ (int) round($s['active_seconds'] / 60) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<p class="muted" style="margin-top:10px">Başarı puanı: ders ve ödev tamamlama oranları (sınıftaki en iyi öğrenciye göre ölçeklenir) ile varsa not ortalamasının ortalamasıdır.</p>
</body>
</html>
