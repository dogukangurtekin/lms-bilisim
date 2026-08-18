<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.pwa-head')
    <title>Sertifika</title>
    <style>
        :root{
            --paper:#ffffff;
            --ink:#0f172a;
            --muted:#475569;
            --line:rgba(15,23,42,.12);
            --shadow:0 28px 70px rgba(15,23,42,.14);
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            background:
                linear-gradient(180deg, #eef3f8 0%, #f7fafc 100%);
            font-family:"Segoe UI", Tahoma, Arial, sans-serif;
            color:var(--ink);
        }
        .page{
            max-width:1440px;
            margin:0 auto;
            padding:20px;
        }
        .toolbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:16px;
        }
        .toolbar-title{
            font-size:18px;
            font-weight:800;
            color:#0f172a;
        }
        .toolbar-actions{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:10px 16px;
            border-radius:999px;
            border:1px solid rgba(15,23,42,.12);
            background:#fff;
            color:#1e3a8a;
            text-decoration:none;
            font-weight:800;
            box-shadow:0 10px 24px rgba(15,23,42,.06);
        }
        .certificate-shell{
            display:grid;
            gap:18px;
        }
        .certificate-card{
            position:relative;
            overflow:hidden;
            border-radius:24px;
            min-height:840px;
            background:var(--paper);
            box-shadow:var(--shadow);
            border:1px solid rgba(255,255,255,.8);
        }
        .certificate-frame{
            position:absolute;
            inset:18px;
            border-radius:18px;
            border:1.5px solid rgba(15,23,42,.10);
            pointer-events:none;
        }
        .certificate-topbar,
        .certificate-bottombar{
            position:absolute;
            left:0;
            right:0;
            height:12px;
            z-index:0;
        }
        .certificate-topbar{top:0}
        .certificate-bottombar{bottom:0}
        .certificate-inner{
            position:relative;
            z-index:1;
            min-height:840px;
            display:flex;
            flex-direction:column;
            padding:30px 38px 26px;
        }
        .certificate-header{
            display:grid;
            grid-template-columns:1fr auto 1fr;
            align-items:center;
            gap:16px;
        }
        .brand{
            display:flex;
            align-items:center;
            gap:14px;
            min-width:0;
        }
        .brand-center{
            justify-content:center;
            text-align:center;
        }
        .brand-end{
            justify-content:flex-end;
        }
        .brand-logo{
            width:64px;
            height:64px;
            object-fit:contain;
            flex:0 0 auto;
        }
        .school-text{
            min-width:0;
        }
        .school-name{
            margin:0;
            font-size:clamp(20px,2vw,32px);
            line-height:1.1;
            font-weight:900;
            letter-spacing:.01em;
        }
        .school-subtitle{
            margin:6px 0 0;
            font-size:13px;
            font-weight:700;
            letter-spacing:.12em;
            text-transform:uppercase;
            color:rgba(15,23,42,.58);
        }
        .meta-chip{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:10px 14px;
            border-radius:999px;
            background:rgba(255,255,255,.8);
            border:1px solid rgba(15,23,42,.08);
            color:rgba(15,23,42,.72);
            font-size:13px;
            font-weight:800;
            white-space:nowrap;
        }
        .hero{
            margin:28px auto 10px;
            width:min(860px,100%);
            text-align:center;
        }
        .hero-kicker{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:7px 14px;
            border-radius:999px;
            font-size:12px;
            font-weight:900;
            letter-spacing:.16em;
            text-transform:uppercase;
            color:rgba(15,23,42,.70);
            background:rgba(255,255,255,.74);
            border:1px solid rgba(15,23,42,.09);
        }
        .hero-copy{
            margin:22px auto 0;
            max-width:900px;
            font-size:clamp(16px,1.4vw,21px);
            line-height:1.65;
            color:#1f2937;
            font-weight:600;
        }
        .student-name{
            margin:18px auto 0;
            text-align:center;
            font-size:clamp(24px,3vw,36px);
            line-height:1.08;
            font-weight:900;
            letter-spacing:.02em;
            text-transform:uppercase;
            word-break:break-word;
        }
        .student-meta{
            margin:12px auto 0;
            display:flex;
            justify-content:center;
            flex-wrap:wrap;
            gap:10px;
        }
        .student-meta .meta-chip{
            background:rgba(248,250,252,.92);
        }
        .info-grid{
            margin-top:20px;
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:12px;
            width:min(980px,100%);
            margin-left:auto;
            margin-right:auto;
        }
        .info-card{
            padding:14px 16px;
            border-radius:16px;
            border:1px solid rgba(15,23,42,.08);
            background:rgba(255,255,255,.82);
        }
        .info-label{
            font-size:12px;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.10em;
            color:rgba(15,23,42,.54);
            margin-bottom:8px;
        }
        .info-value{
            font-size:15px;
            font-weight:800;
            color:var(--ink);
            line-height:1.35;
        }
        .signatures{
            margin-top:auto;
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:30px;
            align-items:end;
            padding-top:26px;
        }
        .signature{
            text-align:center;
        }
        .signature-line{
            width:82%;
            height:1px;
            margin:0 auto 12px;
            background:rgba(15,23,42,.55);
        }
        .signature strong{
            display:block;
            font-size:clamp(17px,1.55vw,24px);
            line-height:1.2;
            margin-bottom:4px;
        }
        .signature span{
            display:block;
            font-size:14px;
            color:var(--muted);
            font-weight:700;
        }
        .certificate-note{
            margin-top:16px;
            text-align:center;
            color:rgba(15,23,42,.66);
            font-size:13px;
            font-weight:700;
            letter-spacing:.02em;
        }
        .certificate-card.primary{
            background:
                linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,250,239,.95)),
                linear-gradient(135deg, #fff7e8 0%, #fffdf8 100%);
        }
        .certificate-card.primary .certificate-topbar{background:linear-gradient(90deg,#d97706 0%,#f59e0b 100%)}
        .certificate-card.primary .certificate-bottombar{background:linear-gradient(90deg,#f59e0b 0%,#fb7185 100%)}
        .certificate-card.primary .hero-kicker{color:#92400e}
        .certificate-card.primary .student-name{color:#9a3412}
        .certificate-card.primary .school-subtitle{color:rgba(146,64,14,.62)}
        .certificate-card.primary .meta-chip{border-color:rgba(217,119,6,.16)}
        .certificate-card.secondary{
            background:
                linear-gradient(180deg, rgba(255,255,255,.94), rgba(243,248,255,.95)),
                linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
        }
        .certificate-card.secondary .certificate-topbar{background:linear-gradient(90deg,#1d4ed8 0%,#38bdf8 100%)}
        .certificate-card.secondary .certificate-bottombar{background:linear-gradient(90deg,#38bdf8 0%,#1d4ed8 100%)}
        .certificate-card.secondary .hero-kicker{color:#1d4ed8}
        .certificate-card.secondary .student-name{color:#0f172a}
        .certificate-card.secondary .school-subtitle{color:rgba(29,78,216,.62)}
        .certificate-card.secondary .meta-chip{border-color:rgba(29,78,216,.12)}
        .certificate-card.highschool{
            background:
                linear-gradient(180deg, rgba(255,255,255,.96), rgba(250,247,240,.95)),
                linear-gradient(135deg, #fbf7ef 0%, #f3ecd9 100%);
        }
        .certificate-card.highschool .certificate-topbar{background:linear-gradient(90deg,#0f172a 0%,#b45309 100%)}
        .certificate-card.highschool .certificate-bottombar{background:linear-gradient(90deg,#b45309 0%,#0f172a 100%)}
        .certificate-card.highschool .hero-kicker{color:#7c2d12}
        .certificate-card.highschool .student-name{color:#111827}
        .certificate-card.highschool .school-subtitle{color:rgba(124,45,18,.62)}
        .certificate-card.highschool .meta-chip{border-color:rgba(124,58,237,.10)}
        @media (max-width: 1100px){
            .certificate-inner{padding:26px 22px 22px}
            .certificate-card{min-height:auto}
            .certificate-inner{min-height:auto}
            .info-grid{grid-template-columns:1fr}
        }
        @media (max-width: 768px){
            .page{padding:10px}
            .toolbar{align-items:flex-start}
            .certificate-card{border-radius:18px}
            .certificate-frame{inset:10px;border-radius:14px}
            .certificate-header{
                grid-template-columns:1fr;
                justify-items:center;
                text-align:center;
            }
            .brand, .brand-end{justify-content:center}
            .signatures{grid-template-columns:1fr;gap:20px}
            .signature-line{width:72%}
            .hero{margin-top:20px}
            .hero-copy{font-size:16px}
            .student-name{font-size:clamp(22px,8vw,32px)}
        }
        @media print{
            body{background:#fff}
            .page{padding:0;max-width:none}
            .toolbar{display:none}
            .certificate-card{
                box-shadow:none;
                border:none;
                border-radius:0;
                page-break-inside:avoid;
                min-height:100vh;
            }
            .certificate-inner{
                padding:14mm 16mm 12mm;
                min-height:100vh;
            }
            .certificate-frame{inset:8mm}
            @page{size:A4 landscape;margin:8mm}
        }
    </style>
</head>
<body>
@php
    $gradeLevel = (int) ($student->schoolClass?->grade_level ?? 0);
    $certificateStyles = [
        'primary' => [
            'label' => 'İlkokul Sertifikası',
            'accent' => 'primary',
        ],
        'secondary' => [
            'label' => 'Ortaokul Sertifikası',
            'accent' => 'secondary',
        ],
        'highschool' => [
            'label' => 'Lise Sertifikası',
            'accent' => 'highschool',
        ],
    ];
    $styleKey = $certificateStyle ?? 'secondary';
    if (!isset($certificateStyles[$styleKey])) {
        $styleKey = 'secondary';
    }
    $className = trim((string) ($student->schoolClass?->name ?? '-'));
    $section = trim((string) ($student->schoolClass?->section ?? '-'));
    $studentName = trim((string) ($student->user?->name ?? '-'));
    $schoolLabel = trim((string) $schoolName);
    $classLabel = $className !== '' ? $className : '-';
    $sectionLabel = $section !== '' ? $section : '-';
@endphp
<div class="page">
    <div class="toolbar">
        <div class="toolbar-title">Öğrenci Verileri - Sertifika</div>
        <div class="toolbar-actions">
            <a class="btn" href="{{ route('student-data.index') }}">Geri Dön</a>
            <a class="btn" href="javascript:window.print()">Yazdır</a>
        </div>
    </div>

    <div class="certificate-shell">
        <section class="certificate-card {{ $styleKey }}">
            <div class="certificate-topbar"></div>
            <div class="certificate-bottombar"></div>
            <div class="certificate-frame"></div>

            <div class="certificate-inner">
                <header class="certificate-header">
                    <div class="brand">
                        <img src="{{ $logoUrl }}" alt="Logo" class="brand-logo">
                        <div class="school-text">
                            <p class="school-name">{{ $schoolLabel }}</p>
                            <p class="school-subtitle">{{ $certificateStyles[$styleKey]['label'] }}</p>
                        </div>
                    </div>

                    <div class="meta-chip">
                        Belge No: {{ $certificateNo }}
                    </div>

                    <div class="brand brand-end">
                        <div class="meta-chip">
                            Tarih: {{ $certificateDate }}
                        </div>
                    </div>
                </header>

                <section class="hero">
                    <div class="hero-kicker">Akademik Başarı Belgesi</div>
                    <p class="hero-copy">
                        Bu belge, öğrencimizin ders süreçlerindeki düzenli katılımını, görev tamamlama başarısını ve öğrenme disiplinini resmi olarak belgelemek amacıyla düzenlenmiştir.
                    </p>
                    <div class="student-name">{{ $studentName }}</div>
                    <div class="student-meta">
                        <span class="meta-chip">Sınıf: {{ $classLabel }} / {{ $sectionLabel }}</span>
                        <span class="meta-chip">Toplam XP: {{ $xp }}</span>
                        <span class="meta-chip">Kademe: {{ ucfirst($styleKey) }}</span>
                    </div>
                </section>

                <section class="info-grid" aria-label="Sertifika bilgileri">
                    <div class="info-card">
                        <div class="info-label">Okul Adı</div>
                        <div class="info-value">{{ $schoolLabel }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Sınıf Bilgisi</div>
                        <div class="info-value">{{ $classLabel }} / {{ $sectionLabel }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Belge Tarihi</div>
                        <div class="info-value">{{ $certificateDate }}</div>
                    </div>
                </section>

                <div class="certificate-note">
                    Öğrencinin gösterdiği emek, katılım ve gelişim doğrultusunda hazırlanmıştır.
                </div>

                <div class="signatures">
                    <div class="signature">
                        <div class="signature-line"></div>
                        <strong>{{ $teacherName }}</strong>
                        <span>Ders Öğretmeni</span>
                    </div>
                    <div class="signature">
                        <div class="signature-line"></div>
                        <strong>{{ $principalName }}</strong>
                        <span>Okul Müdürü</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>