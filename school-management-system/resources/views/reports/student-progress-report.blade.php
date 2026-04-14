<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Öğrenci Gelişim Raporu</title>
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
        .pdf-status{
            position:fixed;
            right:16px;
            bottom:16px;
            z-index:9999;
            display:none;
            background:#0f172a;
            color:#e2e8f0;
            border:1px solid #1e293b;
            border-radius:12px;
            padding:10px 12px;
            font-size:13px;
            min-width:260px;
        }
        .pdf-status.show{display:block}

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
    <button class="btn" type="button" id="download-report-pdf-btn">PDF İndir</button>
    <button class="btn" onclick="window.print()">PDF Olarak Kaydet / Yazdır</button>
    @if(($viewer ?? '') === 'student')
        <button class="btn" onclick="window.close()">Kapat</button>
    @endif
</div>
<div class="pdf-status" id="pdf-status">PDF hazırlanıyor... %0</div>

@include('reports.partials.student-progress-pages', ['student' => $student, 'report' => $report])

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(() => {
    const btn = document.getElementById('download-report-pdf-btn');
    const status = document.getElementById('pdf-status');
    if (!btn) return;

    const setStatus = (text, show = true) => {
        if (!status) return;
        status.textContent = text;
        status.classList.toggle('show', show);
    };

    btn.addEventListener('click', async () => {
        const pages = Array.from(document.querySelectorAll('.report-page'));
        if (!pages.length || !window.jspdf || typeof html2canvas === 'undefined') return;

        btn.disabled = true;
        setStatus('PDF hazırlanıyor... %0', true);

        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
            const pageWidth = 210;
            const pageHeight = 297;

            for (let i = 0; i < pages.length; i += 1) {
                const canvas = await html2canvas(pages[i], {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                });

                const imageData = canvas.toDataURL('image/png');
                let renderWidth = pageWidth;
                let renderHeight = (canvas.height * renderWidth) / canvas.width;
                if (renderHeight > pageHeight) {
                    renderHeight = pageHeight;
                    renderWidth = (canvas.width * renderHeight) / canvas.height;
                }
                const offsetX = (pageWidth - renderWidth) / 2;
                const offsetY = (pageHeight - renderHeight) / 2;

                if (i > 0) pdf.addPage();
                pdf.setFillColor(255, 255, 255);
                pdf.rect(0, 0, pageWidth, pageHeight, 'F');
                pdf.addImage(imageData, 'PNG', offsetX, offsetY, renderWidth, renderHeight, undefined, 'FAST');

                const percent = Math.round(((i + 1) / pages.length) * 100);
                setStatus(`PDF hazırlanıyor... %${percent}`, true);
            }

            pdf.save('ogrenci-gelisim-raporu.pdf');
            setStatus('PDF indirildi.', true);
            setTimeout(() => setStatus('', false), 1500);
        } catch (err) {
            console.error(err);
            setStatus('PDF oluşturulurken hata oluştu.', true);
            setTimeout(() => setStatus('', false), 2500);
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
</body>
</html>
