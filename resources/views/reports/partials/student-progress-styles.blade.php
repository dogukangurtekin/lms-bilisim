@page { size: A4 portrait; margin: 0; }

:root {
    --report-ink: #172033;
    --report-muted: #64748b;
    --report-blue: #2457d6;
    --report-blue-soft: #eef4ff;
    --report-teal: #0f9f8f;
    --report-line: #dbe5f2;
    --report-paper: #ffffff;
}

body {
    background: #e8edf5;
    color: var(--report-ink);
    font-family: "DejaVu Sans", "Segoe UI", Arial, sans-serif;
    font-size: 11px;
    line-height: 1.35;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.tools {
    position: sticky;
    top: 0;
    z-index: 50;
    padding: 10px;
    background: rgba(232, 237, 245, .94);
    backdrop-filter: blur(10px);
}

.btn {
    border-radius: 9px;
    padding: 9px 14px;
    box-shadow: 0 5px 14px rgba(37, 87, 214, .18);
}

.report-page {
    width: 210mm;
    height: 297mm;
    min-height: 297mm;
    margin: 0 auto 14px;
    padding: 8mm 9mm 9mm;
    border: 0;
    border-radius: 0;
    background: var(--report-paper);
    box-shadow: 0 16px 42px rgba(15, 23, 42, .14);
    overflow: hidden;
}

.report-page::before {
    height: 3mm;
    background: linear-gradient(90deg, #2457d6 0 58%, #19a69a 58% 82%, #f59e0b 82% 100%);
}

.hero {
    min-height: 17mm;
    margin: 1.5mm 0 3.5mm;
    padding-bottom: 3mm;
    border-bottom: 1px solid var(--report-line);
    gap: 8px;
}

.hero-left { gap: 9px; min-width: 0; }
.hero-left > div { min-width: 0; }
.brand-logo,
.brand-logo.small {
    width: 15mm;
    height: 15mm;
    flex: 0 0 15mm;
}

h1 { font-size: 20px; line-height: 1.15; letter-spacing: -.25px; }
h2 { font-size: 17px; line-height: 1.2; }
.report-eyebrow {
    margin: 0 0 2px;
    color: var(--report-blue);
    font-size: 7.5px;
    font-weight: 800;
    letter-spacing: 1.2px;
}
.subtitle {
    margin: 2px 0 0;
    color: var(--report-muted);
    font-size: 10px;
    line-height: 1.35;
}

.score-pill {
    padding: 6px 10px;
    border-color: #c9d9ff;
    border-radius: 8px;
    background: var(--report-blue-soft);
    font-size: 10px;
    white-space: nowrap;
}

.kpi-grid {
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 5px;
    margin-bottom: 6px;
}

.kpi-card,
.kpi-card:nth-child(2n),
.kpi-card:nth-child(3n) {
    min-height: 18mm;
    padding: 7px 8px;
    border: 1px solid var(--report-line);
    border-top: 3px solid var(--report-blue);
    border-radius: 8px;
    background: #fff;
}

.kpi-card:nth-child(2n) { border-top-color: var(--report-teal); }
.kpi-card:nth-child(3n) { border-top-color: #7c5ce5; }
.kpi-card span { margin-bottom: 3px; color: var(--report-muted); font-size: 8.5px; line-height: 1.15; }
.kpi-card strong,
.kpi-card strong.small { display: block; font-size: 15px; line-height: 1.12; color: var(--report-ink); }
.kpi-card strong.small + strong.small { margin-top: 2px; }
.kpi-card strong.small { font-size: 9px; }

.content-grid {
    grid-template-columns: .86fr 1.14fr;
    gap: 6px;
    margin-bottom: 0;
}

.panel {
    margin-bottom: 6px;
    padding: 8px 9px;
    border-color: var(--report-line);
    border-radius: 8px;
    break-inside: avoid;
    page-break-inside: avoid;
}

.panel h3 {
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 1px solid #e8eef7;
    color: #173a8f;
    font-size: 12px;
    line-height: 1.2;
}

.donut-wrap { gap: 10px !important; flex-wrap: nowrap !important; }
.donut-wrap > div:first-child,
.donut-wrap > div:first-child > div {
    width: 25mm !important;
    height: 25mm !important;
}
.donut-wrap > div:first-child > div > div { inset: 3.2mm !important; }
.donut-wrap > div:first-child > div > div > div > div:first-child { font-size: 17px !important; }
.donut-wrap > div:last-child { min-width: 0 !important; }
.donut-wrap p { margin-bottom: 5px !important; font-size: 9.5px; }

.bullet-list { padding-left: 14px; line-height: 1.35; }
.bullet-list li { margin-bottom: 3px; }

.category-chart {
    height: 40mm;
    padding: 8px 7px 7px 32px;
    border: 0;
    border-radius: 6px;
    background: linear-gradient(180deg, #f8fbff, #fff);
}
.category-grid span { left: 31px; right: 7px; border-color: #e4ebf5; }
.category-y { left: 3px; top: 6px; bottom: 24px; font-size: 7px; }
.category-bars { left: 33px; right: 8px; top: 8px; bottom: 7px; gap: 5px; }
.category-bar { border-radius: 4px 4px 0 0; }
.category-col small,
.category-col small[style] { margin-top: 2px; font-size: 7px !important; line-height: 1.05 !important; }
.chart-note { margin: 5px 1px 0; font-size: 8.5px; }

.report-table {
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid var(--report-line);
    border-radius: 6px;
    overflow: hidden;
}
.report-table th,
.report-table td {
    padding: 4px 5px;
    border: 0;
    border-right: 1px solid var(--report-line);
    border-bottom: 1px solid var(--report-line);
    overflow-wrap: anywhere;
}
.report-table th:last-child,
.report-table td:last-child { border-right: 0; }
.report-table tbody tr:last-child td { border-bottom: 0; }
.report-table th { background: #edf3ff; color: #173a8f; font-size: 8px; }
.report-table td { font-size: 8px; line-height: 1.28; }
.report-table tbody tr:nth-child(even) td { background: #f8fafc; }
.report-table tr { break-inside: avoid; page-break-inside: avoid; }

.badge-wrap { gap: 4px; }
.badge-item {
    padding: 4px 7px;
    border-color: #d6e2fa;
    border-radius: 6px;
    background: #f3f7ff;
    font-size: 8.5px;
}

.page-no {
    right: 9mm;
    bottom: 4mm;
    font-size: 8px;
    color: #7b879b;
}

@media screen and (max-width: 900px) {
    .report-page { transform-origin: top left; }
}

@media print {
    html, body { width: 210mm; margin: 0 !important; padding: 0 !important; background: #fff; }
    .tools, .pdf-status { display: none !important; }
    .report-page {
        width: 210mm;
        height: 297mm;
        min-height: 297mm;
        margin: 0 !important;
        padding: 8mm 9mm 9mm;
        box-shadow: none;
        page-break-after: always;
    }
    .report-page:last-child { page-break-after: auto; }
    .page-break { page-break-before: always; }
}
