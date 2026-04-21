<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    @include('partials.pwa-head')
    <title>Öğrenci Giriş Kartları</title>
    <style>
        body{font-family:"Segoe UI",Arial,sans-serif;background:#e5e7eb;margin:0;color:#111}
        .tools{padding:12px;text-align:center;display:flex;justify-content:center;gap:10px}
        .btn{border:0;background:#111;color:#fff;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer}
        .sheet{width:210mm;min-height:297mm;margin:0 auto 12px;background:#fff;padding:10mm;box-sizing:border-box}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:6mm}
        .download-progress{
            position:fixed;
            right:16px;
            bottom:16px;
            width:300px;
            background:#0f172a;
            color:#e2e8f0;
            border:1px solid #1e293b;
            border-radius:12px;
            box-shadow:0 12px 24px rgba(15,23,42,.35);
            padding:12px;
            z-index:9999;
            display:none;
        }
        .download-progress.show{display:block}
        .download-progress-title{font-size:13px;font-weight:700;margin-bottom:8px}
        .download-progress-meta{display:flex;justify-content:space-between;align-items:center;font-size:12px;margin-bottom:8px}
        .download-progress-bar{
            width:100%;
            height:8px;
            background:#334155;
            border-radius:999px;
            overflow:hidden;
        }
        .download-progress-fill{
            width:0%;
            height:100%;
            background:linear-gradient(90deg,#22c55e,#38bdf8);
            transition:width .2s ease;
        }
        .card{
            position:relative;
            border:2px solid #4b1f74;
            border-radius:14px;
            padding:12px;
            background:
                radial-gradient(circle at 88% 14%, rgba(255,255,255,.42) 0 22%, transparent 23%),
                radial-gradient(circle at 12% 86%, rgba(255,255,255,.34) 0 18%, transparent 19%),
                linear-gradient(150deg,#f8f5ff 0%,#efe9ff 38%,#e5dbff 62%,#ffffff 100%);
            height:52mm;
            display:flex;
            flex-direction:column;
            justify-content:flex-start;
            gap:6px;
            box-shadow:0 10px 22px rgba(76,29,149,.18), inset 0 0 0 1px rgba(255,255,255,.35);
            overflow:hidden;
        }
        .card::before{
            content:"";
            position:absolute;
            left:0;top:0;right:0;height:10px;
            background:#111;
        }
        .card::after{
            content:"";
            position:absolute;
            right:-24px;bottom:-24px;
            width:110px;height:110px;border-radius:999px;
            border:2px solid rgba(255,255,255,.3);
        }
        .head{
            margin-top:4px;
            font-weight:800;
            color:#2e1065;
            font-size:15px;
            letter-spacing:.2px;
            text-transform:uppercase;
            display:grid;
            grid-template-columns:130px 1fr;
            align-items:center;
            column-gap:10px;
            line-height:1.15;
        }
        .brand{
            width:130px;
            height:auto;
            max-height:72px;
            object-fit:contain;
            object-position:left center;
            display:block;
            flex:0 0 auto;
        }
        .head-text{display:block}
        .line{
            font-size:13px;
            color:#1f2937;
            margin-top:3px;
            border-bottom:1px dashed rgba(30,41,59,.22);
            padding-bottom:2px;
        }
        .line:last-child{border-bottom:0}
        .line b{
            display:inline-block;
            min-width:122px;
            color:#111827;
            font-size:13px;
        }
        @media print {
            .tools{display:none}
            .sheet{margin:0;box-shadow:none}
            .card{break-inside:avoid;box-shadow:none}
        }
    </style>
</head>
<body>
<div class="tools">
    <button class="btn" id="download-pdf-btn" type="button">İndir (PDF)</button>
</div>
<div id="download-progress" class="download-progress" aria-live="polite">
    <div class="download-progress-title">İndirme hazırlanıyor</div>
    <div class="download-progress-meta">
        <span id="download-progress-text">Sayfalar işleniyor...</span>
        <strong id="download-progress-percent">0%</strong>
    </div>
    <div class="download-progress-bar">
        <div id="download-progress-fill" class="download-progress-fill"></div>
    </div>
</div>
@php $chunks = $students->chunk(10); @endphp
@foreach($chunks as $chunk)
    <div class="sheet">
        <div class="grid">
            @foreach($chunk as $student)
                <div class="card">
                    <div class="head">
                        <img class="brand" src="{{ asset('logo.png') }}" alt="Logo">
                        <span class="head-text">BİLİŞİM DERSİ<br>ÖĞRENCİ GİRİŞ KARTI</span>
                    </div>
                    <div style="margin-top:8px">
                        <div class="line"><b>Ad Soyad:</b> {{ $student->user?->name }}</div>
                        <div class="line"><b>Kullanıcı Adı:</b> {{ $student->credential?->username }}</div>
                        <div class="line"><b>Şifre:</b> {{ $student->credential?->plain_password }}</div>
                        <div class="line"><b>Sınıf:</b> {{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(() => {
    const btn = document.getElementById('download-pdf-btn');
    const progressEl = document.getElementById('download-progress');
    const progressTextEl = document.getElementById('download-progress-text');
    const progressPercentEl = document.getElementById('download-progress-percent');
    const progressFillEl = document.getElementById('download-progress-fill');
    if (!btn) return;

    const setProgress = (percent, text) => {
        const safe = Math.max(0, Math.min(100, Math.round(percent)));
        progressPercentEl.textContent = safe + '%';
        progressFillEl.style.width = safe + '%';
        if (text) progressTextEl.textContent = text;
    };

    btn.addEventListener('click', async () => {
        const sheets = Array.from(document.querySelectorAll('.sheet'));
        if (!sheets.length) return;
        btn.disabled = true;
        progressEl.classList.add('show');
        setProgress(0, 'Sayfalar işleniyor...');

        if (typeof html2canvas === 'undefined' || !window.jspdf || !window.jspdf.jsPDF) {
            setProgress(0, 'PDF bileşeni yüklenemedi. Ctrl+P ile yazdırın.');
            setTimeout(() => progressEl.classList.remove('show'), 3200);
            btn.disabled = false;
            return;
        }

        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

            for (let i = 0; i < sheets.length; i += 1) {
                const canvas = await html2canvas(sheets[i], {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                });
                const imageData = canvas.toDataURL('image/png');
                if (i > 0) pdf.addPage();
                const pageWidth = 210;
                const pageHeight = 297;
                let renderWidth = pageWidth;
                let renderHeight = (canvas.height * renderWidth) / canvas.width;
                if (renderHeight > pageHeight) {
                    renderHeight = pageHeight;
                    renderWidth = (canvas.width * renderHeight) / canvas.height;
                }
                const offsetX = (pageWidth - renderWidth) / 2;
                const offsetY = (pageHeight - renderHeight) / 2;
                pdf.setFillColor(255, 255, 255);
                pdf.rect(0, 0, pageWidth, pageHeight, 'F');
                pdf.addImage(imageData, 'PNG', offsetX, offsetY, renderWidth, renderHeight, undefined, 'FAST');
                setProgress(((i + 1) / sheets.length) * 100, `Sayfa ${i + 1}/${sheets.length} işlendi`);
            }

            setProgress(100, 'Raporlar hazırlandı');
            pdf.save('ogrenci-giris-kartlari.pdf');
            setTimeout(() => progressEl.classList.remove('show'), 1800);
        } catch (error) {
            console.error(error);
            setProgress(0, 'İndirme sırasında hata oluştu');
            setTimeout(() => progressEl.classList.remove('show'), 2500);
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>
