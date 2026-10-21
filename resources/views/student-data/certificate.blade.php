<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.pwa-head')
    <title>Sertifika - {{ $student->user?->name }}</title>
    @php
        $styleKey = in_array(($certificateStyle ?? 'secondary'), ['primary', 'secondary', 'highschool'], true) ? $certificateStyle : 'secondary';
        $levelLabels = ['primary' => 'İlkokul', 'secondary' => 'Ortaokul', 'highschool' => 'Lise'];
        $rawName = trim((string) ($student->user?->name ?? '-'));
        $trUpper = fn (string $c) => mb_strtoupper(strtr($c, ['i' => 'İ', 'ı' => 'I']), 'UTF-8');
        $trLower = fn (string $c) => mb_strtolower(strtr($c, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
        $studentName = implode('', array_map(
            fn ($part) => ($part === '' || preg_match('/^[\s\-]+$/u', $part)) ? $part : $trUpper(mb_substr($part, 0, 1, 'UTF-8')).$trLower(mb_substr($part, 1, null, 'UTF-8')),
            preg_split('/(\s+|-)/u', $rawName, -1, PREG_SPLIT_DELIM_CAPTURE)
        ));
        $className = trim((string) ($student->schoolClass?->name ?? '')) ?: '-';
        $sectionName = trim((string) ($student->schoolClass?->section ?? '')) ?: '-';
        $schoolLabel = trim((string) $schoolName);
        // XP'ye göre unvan: sertifikaya kişiye özel bir rütbe ekler.
        $xpInt = (int) $xp;
        [$rankName, $rankMedal] = match (true) {
            $xpInt >= 5000 => ['Kod Efsanesi', 'ELMAS'],
            $xpInt >= 2000 => ['Kod Ustası', 'ALTIN'],
            $xpInt >= 500 => ['Kod Kaşifi', 'GÜMÜŞ'],
            default => ['Kod Çırağı', 'BRONZ'],
        };
    @endphp
    <style>
        :root{
            --ink:#0f172a; --muted:#475569; --gold:#b8892b; --gold-2:#e6c36a;
            --c1:#2563eb; --c2:#7c3aed; --paper:#fffdf7; --tint:#eef2ff;
        }
        .theme-primary{ --c1:#f59e0b; --c2:#ef4444; --paper:#fffaf0; --tint:#fff1d6; }
        .theme-secondary{ --c1:#2563eb; --c2:#7c3aed; --paper:#fbfcff; --tint:#e8eeff; }
        .theme-highschool{ --c1:#0f172a; --c2:#1e3a8a; --paper:#fdfcf8; --tint:#e9edf5; }
        *{box-sizing:border-box}
        html,body{margin:0}
        body{background:#e2e8f0;font-family:"Segoe UI",Tahoma,Arial,sans-serif;color:var(--ink);-webkit-print-color-adjust:exact;print-color-adjust:exact}

        /* ---- Üst araç çubuğu (yazdırırken gizlenir) ---- */
        .toolbar{position:sticky;top:0;z-index:20;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:12px 18px;background:#fff;border-bottom:1px solid #e2e8f0;box-shadow:0 4px 14px rgba(15,23,42,.06)}
        .toolbar strong{display:block;font-size:18px;font-weight:900}
        .toolbar small{display:block;color:#64748b;font-weight:700;font-size:12px;margin-top:2px}
        .toolbar-actions{display:flex;flex-wrap:wrap;gap:8px}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:10px;border:1px solid #e2e8f0;background:#fff;color:#0f172a;text-decoration:none;font-weight:800;font-size:13px;cursor:pointer;font-family:inherit}
        .btn:hover{background:#f8fafc}
        .btn.primary{background:#2563eb;border-color:#2563eb;color:#fff}
        .btn.primary:hover{background:#1d4ed8}

        /* ---- Sahne: A4 yatay belge ekrana sığacak şekilde ölçeklenir ---- */
        .stage{padding:22px 12px 36px;display:flex;justify-content:center}
        .fit{position:relative}
        .cert{
            position:absolute;left:50%;top:0;transform-origin:top center;
            width:297mm;height:210mm;background:var(--paper);overflow:hidden;
            box-shadow:0 30px 70px rgba(15,23,42,.28);
            background-image:
                radial-gradient(circle at 50% 46%, rgba(255,255,255,.9) 0 38%, transparent 70%),
                repeating-radial-gradient(circle at 50% 46%, rgba(184,137,43,.05) 0 2px, transparent 2px 9px);
        }
        /* çift çerçeve */
        .frame-1{position:absolute;inset:7mm;border:2.2mm solid var(--c1);border-radius:3mm}
        .frame-2{position:absolute;inset:12.5mm;border:.5mm solid var(--gold);border-radius:1.5mm}
        .frame-3{position:absolute;inset:15mm;border:.25mm dashed rgba(184,137,43,.7);border-radius:1mm}
        .corner{position:absolute;width:20mm;height:20mm;color:var(--gold)}
        .corner.tl{left:10mm;top:10mm}.corner.tr{right:10mm;top:10mm;transform:scaleX(-1)}
        .corner.bl{left:10mm;bottom:10mm;transform:scaleY(-1)}.corner.br{right:10mm;bottom:10mm;transform:scale(-1,-1)}
        /* üst şerit */
        .ribbon{position:absolute;left:50%;top:7mm;transform:translateX(-50%);width:120mm;height:16mm;background:linear-gradient(90deg,var(--c1),var(--c2));color:#fff;display:flex;align-items:center;justify-content:center;gap:4mm;border-radius:0 0 4mm 4mm;box-shadow:0 2mm 4mm rgba(15,23,42,.25);font-weight:800;letter-spacing:.32em;font-size:3.6mm;text-transform:uppercase}
        .ribbon svg{width:5mm;height:5mm}

        .content{position:absolute;inset:26mm 24mm 22mm;display:flex;flex-direction:column;align-items:center;text-align:center}
        .logo-badge,.level-badge{position:absolute;top:22mm;width:32mm;height:32mm;border-radius:50%;display:grid;place-items:center;z-index:2}
        .logo-badge{left:30mm;background:#fff;border:1.2mm solid var(--gold);box-shadow:0 0 0 1.2mm #fff,0 0 0 1.9mm var(--gold-2),0 3mm 6mm rgba(15,23,42,.28)}
        .logo-badge img{width:25mm;height:25mm;object-fit:contain;filter:drop-shadow(0 .4mm .6mm rgba(15,23,42,.25))}
        .level-badge{right:30mm;background:linear-gradient(145deg,var(--c1),var(--c2));color:#fff;border:1.2mm solid var(--gold);box-shadow:0 0 0 1.2mm #fff,0 0 0 1.9mm var(--gold-2),0 3mm 6mm rgba(15,23,42,.28);text-align:center;line-height:1.15}
        .level-badge b{display:block;font-size:4.4mm;font-weight:900;letter-spacing:.06em;text-transform:uppercase}
        .level-badge span{display:block;font-size:2.6mm;font-weight:700;letter-spacing:.16em;text-transform:uppercase;opacity:.9;margin-top:.8mm}
        .level-badge svg{width:5mm;height:5mm;margin:0 auto 1mm;display:block;color:var(--gold-2)}
        .brand{display:flex;align-items:center;gap:4mm;margin-top:1mm}
        .brand .school{font-weight:900;font-size:6mm;letter-spacing:.02em;color:var(--ink);line-height:1.1}
        .brand .level{font-size:3.2mm;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);margin-top:1mm}
        .title{font-family:Georgia,"Times New Roman",serif;font-size:15mm;font-weight:700;letter-spacing:.06em;margin:5mm 0 0;line-height:1;
            background:linear-gradient(180deg,var(--gold-2),var(--gold) 55%,#8a6414);-webkit-background-clip:text;background-clip:text;color:transparent}
        .subtitle{margin-top:2mm;font-size:3.6mm;letter-spacing:.38em;text-transform:uppercase;font-weight:700;color:var(--muted)}
        .divider{display:flex;align-items:center;gap:3mm;margin:4mm 0;color:var(--gold)}
        .divider i{display:block;width:34mm;height:.4mm;background:linear-gradient(90deg,transparent,var(--gold))}
        .divider i:last-child{background:linear-gradient(90deg,var(--gold),transparent)}
        .divider svg{width:5mm;height:5mm}
        .lead{font-size:4mm;color:var(--muted);margin:0}
        .name{font-family:Georgia,"Palatino Linotype","Times New Roman",serif;font-style:italic;font-weight:700;font-size:15mm;line-height:1.15;margin:3mm 0 1mm;color:var(--ink);max-width:230mm;
            border-bottom:.5mm solid var(--gold);padding:0 10mm 1.5mm}
        .meta{display:flex;gap:3mm;margin-top:3mm;flex-wrap:wrap;justify-content:center}
        .chip{padding:1.4mm 4mm;border-radius:999px;background:var(--tint);border:.3mm solid rgba(15,23,42,.12);font-weight:800;font-size:3.4mm;color:var(--ink)}
        .chip b{color:var(--c1)}
        .text{max-width:200mm;margin:4mm 0 0;font-size:4.1mm;line-height:1.55;color:#1f2937}
        .text strong{color:var(--ink)}

        /* altın mühür */
        .seal{position:absolute;left:50%;bottom:20mm;transform:translateX(-50%);width:38mm;height:44mm;text-align:center}
        .seal svg{width:38mm;height:44mm;display:block}
        .seal .xp{position:absolute;left:0;right:0;top:11mm;font-weight:900;line-height:1;color:#5b3d05;font-size:8mm}
        .seal .xp small{display:block;font-size:3mm;letter-spacing:.2em;margin-top:.8mm}
        .seal .medal{position:absolute;left:0;right:0;top:25mm;font-size:2.6mm;letter-spacing:.25em;font-weight:900;color:#5b3d05}

        .sign{position:absolute;bottom:20mm;width:70mm;text-align:center}
        .sign.l{left:30mm}.sign.r{right:30mm}
        .sign .line{height:.4mm;background:var(--ink);margin-bottom:2mm;opacity:.75}
        .sign strong{display:block;font-size:4mm}
        .sign span{display:block;font-size:3.2mm;color:var(--muted);margin-top:.5mm;letter-spacing:.08em}
        .foot{position:absolute;left:0;right:0;bottom:10.2mm;display:flex;justify-content:space-between;padding:0 24mm;font-size:2.9mm;color:#64748b;letter-spacing:.06em;font-weight:700}

        @media print{
            @page{size:A4 landscape;margin:0}
            html,body{background:#fff;width:297mm;height:210mm}
            .toolbar{display:none}
            .stage{padding:0;display:block}
            .fit{height:210mm !important}
            .cert{position:static;transform:none !important;box-shadow:none;left:auto}
        }
    </style>
</head>
<body>
<div class="toolbar">
    <div>
        <strong>🏅 Öğrenci Sertifikası</strong>
        <small>{{ $studentName }} · {{ $className }}/{{ $sectionName }} · {{ $rankName }}</small>
    </div>
    <div class="toolbar-actions">
        <a class="btn" href="{{ route('student-data.index') }}">← Öğrenci Verileri</a>
        <button class="btn primary" type="button" onclick="window.print()">🖨 Yazdır / PDF Kaydet</button>
    </div>
</div>

<div class="stage">
    <div class="fit" id="fit">
        <div class="cert theme-{{ $styleKey }}" id="cert">
            <div class="frame-1"></div><div class="frame-2"></div><div class="frame-3"></div>
            @foreach(['tl','tr','bl','br'] as $pos)
                <svg class="corner {{ $pos }}" viewBox="0 0 60 60" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path d="M4 56V18C4 10 10 4 18 4h38"/><path d="M12 56V24c0-7 5-12 12-12h32" opacity=".6"/>
                    <circle cx="8" cy="8" r="3.2" fill="currentColor"/><path d="M20 20l6 6-6 6-6-6z" fill="currentColor" stroke="none"/>
                </svg>
            @endforeach
            <div class="ribbon">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 17l-6.1 3.4 1.5-6.8L2.2 9l6.9-.7z"/></svg>
                Başarı Belgesi
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 17l-6.1 3.4 1.5-6.8L2.2 9l6.9-.7z"/></svg>
            </div>

            <div class="logo-badge"><img src="{{ $logoUrl }}" alt="Bilişim Kod"></div>
            <div class="level-badge" aria-hidden="true">
                <div>
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 17l-6.1 3.4 1.5-6.8L2.2 9l6.9-.7z"/></svg>
                    <b>{{ $levelLabels[$styleKey] }}</b>
                    <span>Bilişim</span>
                </div>
            </div>

            <div class="content">
                <div class="brand">
                    <div>
                        <div class="school">{{ $schoolLabel }}</div>
                        <div class="level">{{ $levelLabels[$styleKey] }} · Bilişim Teknolojileri</div>
                    </div>
                </div>

                <h1 class="title">SERTİFİKA</h1>
                <div class="subtitle">Üstün Başarı ve Gelişim Belgesi</div>
                <div class="divider"><i></i><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3 7 7 .6-5.3 4.7 1.6 7.2L12 17.7 5.7 21.5l1.6-7.2L2 9.6 9 9z"/></svg><i></i></div>

                <p class="lead">Bu belge, aşağıda adı yazılı öğrenciye verilmiştir</p>
                @php $nameLen = mb_strlen($studentName); $nameSize = $nameLen > 38 ? 8.5 : ($nameLen > 30 ? 10.5 : ($nameLen > 22 ? 13 : 15)); @endphp
                <div class="name" style="font-size:{{ $nameSize }}mm">{{ $studentName }}</div>
                <div class="meta">
                    <span class="chip">Sınıf: <b>{{ $className }}/{{ $sectionName }}</b></span>
                    <span class="chip">Toplam XP: <b>{{ number_format($xpInt, 0, ',', '.') }}</b></span>
                    <span class="chip">Unvan: <b>{{ $rankName }}</b></span>
                </div>
                <p class="text">
                    <strong>{{ $schoolLabel }}</strong> Bilişim Teknolojileri dersi kapsamında gösterdiği düzenli katılım,
                    görev tamamlama başarısı ve öğrenme disiplini nedeniyle <strong>{{ $xpInt }} XP</strong> kazanarak
                    <strong>“{{ $rankName }}”</strong> unvanına layık görülmüş ve bu belge ile ödüllendirilmiştir.
                </p>
            </div>

            <div class="seal" aria-hidden="true">
                <svg viewBox="0 0 80 92">
                    <defs>
                        <radialGradient id="sg" cx="50%" cy="38%" r="70%"><stop offset="0" stop-color="#fff3c4"/><stop offset=".55" stop-color="#e6c36a"/><stop offset="1" stop-color="#b8892b"/></radialGradient>
                    </defs>
                    <path d="M22 60l-10 30 15-7 8 8 8-28z" fill="var(--c1)"/><path d="M58 60l10 30-15-7-8 8-8-28z" fill="var(--c2)"/>
                    <circle cx="40" cy="38" r="34" fill="url(#sg)" stroke="#8a6414" stroke-width="1.5"/>
                    <circle cx="40" cy="38" r="28.5" fill="none" stroke="#fff6d5" stroke-width="1.2" stroke-dasharray="2 2.4"/>
                </svg>
                <div class="xp">{{ number_format($xpInt, 0, ',', '.') }}<small>XP</small></div>
                <div class="medal">{{ $rankMedal }}</div>
            </div>

            <div class="sign l"><div class="line"></div><strong>{{ $teacherName }}</strong><span>Ders Öğretmeni</span></div>
            <div class="sign r"><div class="line"></div><strong>{{ $principalName }}</strong><span>Okul Müdürü</span></div>

            <div class="foot"><span>Belge No: {{ $certificateNo }}</span><span>Tarih: {{ $certificateDate }}</span></div>
        </div>
    </div>
</div>

<script>
(() => {
    // A4 yatay belgeyi ekran genişliğine sığdır; yazdırırken gerçek boyuta döner.
    const cert = document.getElementById('cert');
    const fit = document.getElementById('fit');
    const PX = 96 / 25.4;
    const W = 297 * PX, H = 210 * PX;
    const apply = () => {
        const avail = Math.min(window.innerWidth - 24, 1500);
        const s = Math.min(1.25, avail / W);
        cert.style.transform = 'translateX(-50%) scale(' + s + ')';
        fit.style.width = (W * s) + 'px';
        fit.style.height = (H * s) + 'px';
    };
    window.addEventListener('resize', apply);
    window.addEventListener('beforeprint', () => { cert.style.transform = 'none'; });
    window.addEventListener('afterprint', apply);
    apply();
})();
</script>
<script src="{{ asset('pwa-init.js') }}" defer></script>
</body>
</html>
