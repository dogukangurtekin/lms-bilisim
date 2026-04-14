<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1,h2,h3,p { margin: 0; }
        .head { margin-bottom: 14px; border-bottom: 2px solid #334155; padding-bottom: 8px; }
        .head h1 { font-size: 20px; margin-bottom: 4px; }
        .sub { color: #334155; font-size: 11px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .grid td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; }
        .kpi { font-size: 18px; font-weight: 700; color: #1d4ed8; }
        .section { margin-top: 14px; }
        .section h2 { font-size: 14px; margin-bottom: 6px; color: #1e293b; }
        ul { margin: 0; padding-left: 16px; }
        li { margin-bottom: 3px; }
    </style>
</head>
<body>
    <div class="head">
        <h1>Öğrenci Gelişim Raporu</h1>
        <p class="sub">
            Öğrenci: {{ $student->user?->name ?? 'Öğrenci' }} |
            Sınıf: {{ $student->schoolClass ? $student->schoolClass->name . '/' . $student->schoolClass->section : '-' }} |
            Tarih: {{ now()->format('d.m.Y H:i') }}
        </p>
    </div>

    <table class="grid">
        <tr>
            <td>Toplam XP<br><span class="kpi">{{ (int) ($report['metrics']['xp'] ?? 0) }}</span></td>
            <td>Tamamlanma<br><span class="kpi">%{{ (int) ($report['metrics']['completion'] ?? 0) }}</span></td>
            <td>Katılım<br><span class="kpi">%{{ (int) ($report['metrics']['attendance'] ?? 0) }}</span></td>
        </tr>
    </table>

    <div class="section">
        <h2>Özet</h2>
        <ul>
            <li>Doğru cevap: {{ (int) ($report['quiz']['correct'] ?? 0) }}</li>
            <li>Yanlış cevap: {{ (int) ($report['quiz']['wrong'] ?? 0) }}</li>
            <li>Toplam çözülen görev: {{ (int) ($report['progress']['completed'] ?? 0) }}</li>
            <li>Toplam çalışma süresi: {{ (int) floor(((int) ($report['metrics']['duration_ms'] ?? 0)) / 60000) }} dk</li>
        </ul>
    </div>

    <div class="section">
        <h2>Öğretmen Notu</h2>
        <p>{{ $report['teacher_note'] ?? 'Öğrenci düzenli katılım gösterdiğinde gelişimi daha da hızlanacaktır.' }}</p>
    </div>
</body>
</html>

