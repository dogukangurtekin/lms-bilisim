<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <title>Gelişim Raporları</title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #edf2f7;
            color: #0f172a;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 14px;
        }
        .tools { display: flex; justify-content: center; gap: 10px; padding: 14px; }
        .btn {
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            font-weight: 700;
            padding: 10px 16px;
            cursor: pointer;
        }

        .report-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 10px;
            background: #fff;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            padding: 12mm;
            position: relative;
            overflow: hidden;
        }
        .report-page::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 9px;
            background: linear-gradient(90deg, #2563eb, #06b6d4, #14b8a6);
        }
        .hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .hero-left { display: flex; align-items: center; gap: 12px; }
        .brand-logo { width: 80px; height: 80px; object-fit: contain; }
        .brand-logo.small { width: 64px; height: 64px; }
        h1, h2, h3, h4 { margin: 0; }
        h1 { font-size: 30px; letter-spacing: -.3px; }
        h2 { font-size: 24px; }
        .subtitle { margin-top: 4px; color: #475569; font-weight: 600; }
        .score-pill {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 8px 14px;
            font-weight: 800;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        .kpi-card {
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 10px;
            background: #f8fbff;
        }
        .kpi-card:nth-child(2n) { background: #f0fdfa; }
        .kpi-card:nth-child(3n) { background: #f5f3ff; }
        .kpi-card span { color: #334155; font-weight: 600; display: block; margin-bottom: 4px; }
        .kpi-card strong { font-size: 24px; line-height: 1.1; }
        .kpi-card strong.small { font-size: 18px; }

        .content-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .panel {
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 12px;
            background: #fff;
            margin-bottom: 10px;
        }
        .panel h3 { margin-bottom: 8px; font-size: 18px; color: #1e3a8a; }

        .donut-wrap { display: flex; align-items: center; gap: 14px; }
        .donut {
            width: 145px;
            height: 145px;
            border-radius: 50%;
            position: relative;
        }
        .donut::after {
            content: "";
            position: absolute;
            inset: 22px;
            background: #fff;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
        }

        .bullet-list { margin: 0; padding-left: 18px; line-height: 1.5; }
        .bullet-list li { margin-bottom: 4px; }

        .category-chart {
            position: relative;
            height: 250px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #f8fbff;
            padding: 16px 12px 12px 56px;
        }
        .category-grid span {
            position: absolute;
            left: 50px;
            right: 10px;
            border-top: 1px solid #dbeafe;
        }
        .category-y {
            position: absolute;
            left: 8px;
            top: 12px;
            bottom: 32px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #64748b;
            font-size: 11px;
            font-style: normal;
        }
        .category-y em { font-style: normal; }
        .category-bars {
            position: absolute;
            left: 56px;
            right: 12px;
            bottom: 10px;
            top: 16px;
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
        }
        .category-col {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            min-width: 0;
        }
        .category-bar-wrap {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: flex-end;
        }
        .category-bar {
            display: block;
            width: 100%;
            border-radius: 8px 8px 0 0;
            min-height: 6px;
        }
        .category-col small {
            margin-top: 6px;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .chart-note {
            margin: 8px 2px 0;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
        }

        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th,
        .report-table td {
            border: 1px solid #dbeafe;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .report-table th {
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 12px;
        }
        .report-table td { font-size: 12px; }

        .badge-wrap { display: flex; flex-wrap: wrap; gap: 8px; }
        .badge-item {
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 8px 12px;
            background: #eff6ff;
            font-weight: 700;
            color: #1d4ed8;
        }

        .page-no {
            position: absolute;
            right: 12mm;
            bottom: 7mm;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }
        .page-break { page-break-before: always; }

        @media print {
            .tools { display: none; }
            .report-page {
                margin: 0;
                border: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
<div class="tools">
    @if(!empty($downloadUrl))
        <a class="btn" href="{{ $downloadUrl }}">Gelişim Raporlarını İndir</a>
    @endif
    <button class="btn" onclick="window.print()">Yazdır / PDF Kaydet</button>
    <button class="btn" onclick="window.close()">Ekranı Kapat</button>
</div>

@foreach($reports as $item)
    @include('reports.partials.student-progress-pages', ['student' => $item['student'], 'report' => $item['report']])
@endforeach

<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>
