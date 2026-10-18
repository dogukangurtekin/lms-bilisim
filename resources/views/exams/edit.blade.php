@extends('layout.app')
@section('title','Sınav Editörü')
@section('content')
<link rel="stylesheet" href="{{ asset('css/exam-q.css') }}?v={{ @filemtime(public_path('css/exam-q.css')) }}">
<style>
    @font-face{font-family:'ExamSerif';font-weight:400;font-style:normal;src:url('{{ asset('fonts/dejavu/DejaVuSerif.ttf') }}')}
    @font-face{font-family:'ExamSerif';font-weight:700;font-style:normal;src:url('{{ asset('fonts/dejavu/DejaVuSerif-Bold.ttf') }}')}
    @font-face{font-family:'ExamSerif';font-weight:400;font-style:italic;src:url('{{ asset('fonts/dejavu/DejaVuSerif-Italic.ttf') }}')}
    @font-face{font-family:'ExamSerif';font-weight:700;font-style:italic;src:url('{{ asset('fonts/dejavu/DejaVuSerif-BoldItalic.ttf') }}')}
    @font-face{font-family:'ExamSans';font-weight:400;font-style:normal;src:url('{{ asset('fonts/dejavu/DejaVuSans.ttf') }}')}
    @font-face{font-family:'ExamSans';font-weight:700;font-style:normal;src:url('{{ asset('fonts/dejavu/DejaVuSans-Bold.ttf') }}')}
    @font-face{font-family:'ExamSans';font-weight:400;font-style:italic;src:url('{{ asset('fonts/dejavu/DejaVuSans-Oblique.ttf') }}')}
    @font-face{font-family:'ExamSans';font-weight:700;font-style:italic;src:url('{{ asset('fonts/dejavu/DejaVuSans-BoldOblique.ttf') }}')}
    .xe-menu{position:relative}
    .xe-dd{position:absolute;right:0;top:44px;z-index:30;background:#fff;border:1px solid #dbe5f2;border-radius:12px;box-shadow:0 14px 30px rgba(15,23,42,.18);min-width:250px;padding:6px;display:none}
    .xe-dd.open{display:block}
    .xe-dd button{display:block;width:100%;text-align:left;border:0;background:transparent;padding:9px 12px;border-radius:8px;font:inherit;font-weight:700;font-size:13px;color:#0f172a;cursor:pointer}
    .xe-dd button:hover{background:#eff6ff;color:#1d4ed8}
    .xe-dd small{display:block;color:#64748b;font-weight:600;font-size:11.5px}
    .xe-top{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px}
    .xe-top h1{margin:0;font-size:22px;flex:1 1 auto;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .xe-chip{padding:6px 12px;border-radius:999px;font-size:12.5px;font-weight:800;background:#e2e8f0;color:#475569}
    .xe-chip.ok{background:#dcfce7;color:#166534}.xe-chip.dirty{background:#fef3c7;color:#92400e}.xe-chip.err{background:#fee2e2;color:#991b1b}
    .xe-tabs{display:grid;grid-template-columns:1fr 1fr;gap:4px;background:#eef2f7;padding:4px;border-radius:12px;margin-bottom:12px}
    .xe-tabs button{border:0;background:transparent;border-radius:9px;padding:8px 4px;font-weight:800;font-size:12.5px;color:#475569;cursor:pointer}
    .xe-tabs button.on{background:#fff;color:#1d4ed8;box-shadow:0 3px 8px rgba(15,23,42,.1)}
    .pl-filters{display:grid;gap:8px;margin-bottom:12px}
    .pl-filters input[type=search],.pl-filters select{width:100%;height:36px;border:1px solid #cbd5e1;border-radius:9px;padding:0 9px;font:inherit;font-size:13px;background:#fff}
    .pl-card{border:1px solid #e2e8f0;border-radius:12px;padding:10px;margin-bottom:8px;display:grid;gap:6px;background:#fff}
    .pl-tags{display:flex;gap:5px;flex-wrap:wrap}.pl-tags span{padding:1px 8px;border-radius:999px;font-size:11px;font-weight:800;background:#e0e7ff;color:#3730a3}
    .pl-tags .kolay{background:#dcfce7;color:#166534}.pl-tags .orta{background:#fef3c7;color:#92400e}.pl-tags .zor{background:#fee2e2;color:#991b1b}
    .pl-body{font-size:12.8px;font-weight:600;color:#0f172a;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;white-space:pre-wrap;word-break:break-word}
    .pl-meta{font-size:11.5px;color:#64748b;font-weight:700}
    .pl-add{height:32px;border:0;border-radius:9px;background:#2563eb;color:#fff;font-weight:800;font-size:12.5px;cursor:pointer}
    .pl-add:disabled{background:#dcfce7;color:#166534;cursor:default}
    .wt-cur{display:flex;align-items:center;gap:8px;justify-content:space-between;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:6px 8px;font-size:12px;font-weight:800;color:#065f46}
    .wt-cur span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .wt-panel{border:1px solid #fcd34d;background:#fffbeb;border-radius:12px;padding:10px;display:grid;gap:7px;font-size:12.5px;font-weight:700;color:#78350f}
    .wt-panel button{border:0;border-radius:9px;padding:8px 10px;font:inherit;font-weight:800;font-size:12.5px;cursor:pointer;background:#fff;color:#0f172a;border:1px solid #e5e7eb;text-align:left}
    .wt-panel button.p{background:#2563eb;color:#fff;border-color:transparent}
    .tpl-gal{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
    .tpl-card{border:2px solid #e2e8f0;border-radius:11px;background:#fff;padding:6px 3px 5px;font-size:10.5px;font-weight:800;color:#334155;cursor:pointer;text-align:center;line-height:1.15}
    .tpl-card.on{border-color:#2563eb;background:#eff6ff;color:#1d4ed8}
    .tpl-card .th{display:block;height:46px;margin:0 auto 4px;width:34px;background:#fff;border:1px solid #cbd5e1;border-radius:3px;position:relative;overflow:hidden}
    .th i{display:block;height:11px;margin:3px;border-radius:1px;background:#94a3b8}
    .th u{display:block;height:2px;margin:3px;background:#cbd5e1}
    .th-modern i{background:#2563eb;margin:0 0 3px}.th-plain i{height:5px;background:#cbd5e1}.th-quiz i{height:6px;background:#0f766e;border-bottom:2px solid #111}
    .th-school i{background:linear-gradient(90deg,#1e3a8a 30%,#cbd5e1 30%)}.th-school{border:2px solid #1e3a8a!important}
    .sw-row{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
    .sw{width:24px;height:24px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px #cbd5e1;cursor:pointer;padding:0}
    .sw.on{box-shadow:0 0 0 2px #2563eb}
    .sw-pick{width:30px!important;height:26px!important;padding:0!important;border:1px solid #cbd5e1!important;border-radius:6px!important;background:#fff}
    .logo-box{display:flex;gap:10px;align-items:center;border:1px dashed #cbd5e1;border-radius:11px;padding:8px}
    .logo-box img{width:56px;height:56px;object-fit:contain;border:1px solid #e2e8f0;border-radius:8px;background:#fff}
    .xe-grid{display:grid;grid-template-columns:minmax(300px,360px) minmax(0,1fr) minmax(250px,290px);gap:14px;align-items:start}
    .xe-panel{background:#fff;border:1px solid #dbe5f2;border-radius:16px;padding:14px;box-shadow:0 8px 20px rgba(15,23,42,.04)}
    .xe-panel h3{margin:0 0 10px;font-size:15px}
    .xe-left,.xe-right{position:sticky;top:12px;max-height:calc(100vh - 24px);overflow:auto}
    .xe-types{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
    .xe-type{border:2px solid #e2e8f0;border-radius:12px;background:#f8fafc;padding:9px 6px;font-weight:800;font-size:12.5px;cursor:pointer;color:#334155;text-align:center}
    .xe-type:hover{border-color:#2563eb;background:#eff6ff;color:#1d4ed8}
    .xe-type span{display:block;font-size:19px;margin-bottom:2px}
    .xe-q{border:1px solid #e2e8f0;border-radius:13px;background:#fff;margin-bottom:8px;overflow:hidden}
    .xe-q.sel{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.13)}
    .xe-qh{display:flex;align-items:center;gap:6px;padding:9px 10px;cursor:pointer;background:#f8fafc}
    .xe-q.sel .xe-qh{background:#eff6ff}
    .xe-qn{flex:0 0 auto;width:26px;height:26px;border-radius:8px;background:#2563eb;color:#fff;display:grid;place-items:center;font-weight:900;font-size:12.5px}
    .xe-qt{flex:1 1 auto;min-width:0;font-size:12.5px;font-weight:700;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .xe-cols{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .xe-cols button{border:2px solid #e2e8f0;border-radius:11px;background:#fff;padding:8px;font-weight:800;font-size:12.5px;cursor:pointer;color:#334155}
    .xe-cols button.on{border-color:#2563eb;background:#eff6ff;color:#1d4ed8}
    .xe-right .xe-f{margin-bottom:9px}
    /* ---- A4 önizleme ---- */
    .pv-wrap{background:#e2e8f0;border-radius:16px;padding:12px;min-width:0}
    .pv-holder{position:relative;width:100%}
    .pv-pages{position:absolute;top:0;left:0;transform-origin:top left}
    .paper{position:relative;width:794px;height:1123px;background:#fff;margin-bottom:24px;box-shadow:0 10px 30px rgba(15,23,42,.18);color:#111;overflow:hidden;box-sizing:border-box;line-height:1.35}
    .paper.fam-serif,.measure.fam-serif{font-family:ExamSerif,'DejaVu Serif',serif}
    .paper.fam-sans,.measure.fam-sans{font-family:ExamSans,'DejaVu Sans',sans-serif}
    .measure{position:absolute;left:-99999px;top:0;visibility:hidden;box-sizing:border-box}
    .paper .cols{display:grid;gap:24px;align-items:start}
    .paper .col{min-width:0;overflow:hidden}
    .paper.two .cols{background:linear-gradient(#cbd5e1,#cbd5e1) center top/1px 100% no-repeat}
    .qblk{padding-bottom:12px;break-inside:avoid}
    .sp-compact .qblk{padding-bottom:6px}.sp-relaxed .qblk{padding-bottom:20px}
    .paper.bordered::after{content:'';position:absolute;inset:var(--bi,24px);border:1.5px solid var(--ac,#111);pointer-events:none}
    .wm{position:absolute;left:0;right:0;top:46%;text-align:center;font-size:84px;font-weight:900;letter-spacing:.06em;color:rgba(100,116,139,.13);transform:rotate(-30deg);pointer-events:none;white-space:nowrap;text-transform:uppercase}
    .ph-row{display:flex;align-items:center;gap:14px}.ph-row .ph-txt{flex:1;min-width:0}
    .ph-logo{height:56px;width:auto;max-width:120px;object-fit:contain;flex:0 0 auto}
    .qsec{border-left-color:var(--ac,#334155)}
    .tpl-classic .ph{border:1.5px solid var(--ac,#111);padding:8px 10px}
    .tpl-classic .ph-meta{border-color:var(--ac,#111)}
    .tpl-school .ph{border-bottom:3px double var(--ac,#1e3a8a);padding-bottom:8px}
    .tpl-school .ph-title{color:var(--ac,#1e3a8a)}.tpl-school .qn,.tpl-quiz .qn{color:var(--ac,#111)}
    .tpl-quiz .ph{border-bottom-color:var(--ac,#111)}
    .qblk.sel .qrow{outline:2px solid rgba(37,99,235,.55);outline-offset:3px;border-radius:3px}
    .qsec{font-weight:800;font-size:1.05em;margin:2px 0 8px;padding:3px 8px;background:#eef2f7;border-left:4px solid #334155}
    .qrow{display:flex;gap:6px;align-items:flex-start}
    .qn{flex:0 0 auto;font-weight:800;min-width:1.6em}
    .qbody{flex:1 1 auto;min-width:0}
    .qtext{white-space:normal;word-break:break-word}
    .qp{font-size:.82em;color:#475569;font-weight:700;margin-left:4px;white-space:nowrap}
    .qopts{margin-top:4px;display:grid;gap:2px}
    .qopts.inline{grid-template-columns:repeat(4,minmax(0,1fr));gap:6px}
    .qo{word-break:break-word}.qo b{margin-right:3px}
    .qlines i{display:block;height:25px;border-bottom:1px dotted #64748b}
    .qmatch{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:5px}
    .qmatch div div{margin-bottom:3px;word-break:break-word}
    .ph{margin-bottom:12px}
    .ph-school{text-align:center;font-weight:800;letter-spacing:.02em;text-transform:uppercase;font-size:1.05em}
    .ph-title{text-align:center;font-weight:800;font-size:1.35em;margin:2px 0}
    .ph-info{text-align:center;color:#334155;font-size:.95em}
    .ph-meta{display:flex;justify-content:space-between;gap:10px;margin-top:8px;padding:5px 0;border-top:1px solid #111;border-bottom:1px solid #111;font-size:.92em;font-weight:700}
    .ph-stu{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:10px;margin-top:8px;font-size:.92em;font-weight:700}
    .ph-stu span{border-bottom:1px solid #111;padding-bottom:12px}
    .ph-ins{margin-top:8px;font-size:.88em;font-style:italic;color:#334155;white-space:pre-wrap}
    .tpl-modern .ph{border-radius:10px;overflow:hidden;border:1px solid var(--ac,#c7d2fe)}
    .tpl-modern .ph-top{background:var(--ac,#1e3a8a);background:linear-gradient(120deg,var(--ac,#1e3a8a),color-mix(in srgb,var(--ac,#1e3a8a) 60%,#38bdf8));color:#fff;padding:10px 14px}
    .tpl-modern .ph-top .ph-info{color:#dbeafe}
    .tpl-modern .ph-rest{padding:6px 14px 10px}
    .tpl-modern .ph-meta{border-color:#c7d2fe}
    .tpl-modern .qn{color:var(--ac,#1d4ed8)}
    .tpl-plain .ph-school,.tpl-plain .ph-title,.tpl-plain .ph-info{text-align:left}
    .tpl-plain .ph-meta{border:0;padding:4px 0}
    .tpl-quiz .ph{margin-bottom:8px;border-bottom:2px solid #111;padding-bottom:6px}
    .tpl-quiz .ph-school{display:none}.tpl-quiz .ph-title{font-size:1.15em;text-align:left}.tpl-quiz .ph-info{text-align:left}
    .pg-no{position:absolute;left:0;right:0;bottom:0;text-align:center;font-size:.8em;color:#64748b}
    @media (max-width:1279px){
        .xe-grid{grid-template-columns:1fr}
        .xe-left,.xe-right{position:static;max-height:none}
    }
</style>

<div class="xe-top">
    <a class="xe-btn" href="{{ route('exams.index') }}">← Sınavlar</a>
    <h1 id="xe-heading">{{ $exam->title }}</h1>
    <span class="xe-chip" id="xe-status">Kaydedildi</span>
    <button type="button" class="xe-btn" id="xe-final"></button>
    <div class="xe-menu">
        <button type="button" class="xe-btn" id="xe-pdf">📄 PDF ▾</button>
        <div class="xe-dd" id="xe-dd">
            <button type="button" data-pdf="student">Öğrenci kâğıdı<small>Sorular, cevapsız</small></button>
            <button type="button" data-pdf="student_key">Öğrenci kâğıdı + cevap anahtarı<small>Son sayfada cevap tablosu</small></button>
            <button type="button" data-pdf="teacher">Öğretmen kâğıdı (cevaplı)<small>Doğru şıklar ve cevaplar işaretli</small></button>
            <button type="button" data-pdf="key">Yalnız cevap anahtarı<small>Tek tablo</small></button>
        </div>
    </div>
    <div class="xe-menu">
        <button type="button" class="xe-btn" id="xe-word">📝 Word ▾</button>
        <div class="xe-dd" id="xe-dd-w">
            <button type="button" data-word="student">Öğrenci kâğıdı<small>Sorular, cevapsız</small></button>
            <button type="button" data-word="student_key">Öğrenci kâğıdı + cevap anahtarı<small>Son sayfada cevap tablosu</small></button>
            <button type="button" data-word="teacher">Öğretmen kâğıdı (cevaplı)<small>Doğru şıklar ve cevaplar işaretli</small></button>
            <button type="button" data-word="key">Yalnız cevap anahtarı<small>Tek tablo</small></button>
        </div>
    </div>
    <button type="button" class="xe-btn primary" id="xe-save">💾 Kaydet</button>
</div>

<div class="xe-grid">
    <aside class="xe-panel xe-left">
        <div class="xe-tabs" id="xe-tabs"><button type="button" class="on" data-tab="q">📝 Sorular</button><button type="button" data-tab="pool">📚 Soru Havuzu</button></div>
        <div id="tab-q">
        <h3>➕ Soru Ekle</h3>
        <div class="xe-types" id="xe-types">
            <button type="button" class="xe-type" data-add="multiple_choice"><span>🔘</span>Çoktan Seçmeli</button>
            <button type="button" class="xe-type" data-add="true_false"><span>✅</span>Doğru / Yanlış</button>
            <button type="button" class="xe-type" data-add="fill_blank"><span>✏️</span>Boşluk Doldurma</button>
            <button type="button" class="xe-type" data-add="open_ended"><span>📝</span>Açık Uçlu</button>
            <button type="button" class="xe-type" data-add="matching" style="grid-column:1/-1"><span>🔗</span>Eşleştirme</button>
        </div>
        <h3>Sorular <span id="xe-count" style="color:#64748b;font-weight:700"></span></h3>
        <div id="xe-list"></div>
        </div>
        <div id="tab-pool" style="display:none">
            <div class="pl-filters">
                <input type="search" id="pl-q" placeholder="Havuzda ara…">
                <div class="xe-row2">
                    <select id="pl-diff"><option value="">Tüm zorluklar</option><option value="kolay">Kolay</option><option value="orta">Orta</option><option value="zor">Zor</option></select>
                    <select id="pl-type"><option value="">Tüm tipler</option><option value="multiple_choice">Çoktan seçmeli</option><option value="true_false">Doğru / Yanlış</option><option value="fill_blank">Boşluk doldurma</option><option value="open_ended">Açık uçlu</option><option value="matching">Eşleştirme</option></select>
                </div>
                <label class="xe-sw"><input type="checkbox" id="pl-match"> Sınavın dersi / düzeyiyle sınırla</label>
                <label class="xe-sw"><input type="checkbox" id="pl-mine"> Yalnızca benim sorularım</label>
            </div>
            <div id="pl-list"></div>
            <button type="button" class="xe-btn" id="pl-more" style="display:none;margin:8px auto 0;height:36px">Daha fazla</button>
            <div style="margin-top:10px;font-size:12.5px;font-weight:800"><a href="{{ route('exams.bank.index') }}" target="_blank" style="color:#2563eb">📚 Havuzu yönet / yeni soru ekle →</a></div>
        </div>
    </aside>

    <section class="pv-wrap" id="pv-wrap">
        <div class="pv-holder" id="pv-holder"><div class="pv-pages" id="pv-pages"></div></div>
    </section>

    <aside class="xe-panel xe-right">
        <h3>⚙️ Sınav Ayarları</h3>
        <label class="xe-f">Başlık<input type="text" data-k="exam.title" maxlength="200"></label>
        <label class="xe-f">Okul adı<input type="text" data-k="exam.school_name" maxlength="200"></label>
        <label class="xe-f">Ders<input type="text" data-k="exam.subject" maxlength="120"></label>
        <div class="xe-row2">
            <label class="xe-f">Sınıf düzeyi<select data-k="exam.grade_level" data-num><option value="">—</option>@for($i=1;$i<=12;$i++)<option value="{{ $i }}">{{ $i }}. sınıf</option>@endfor</select></label>
            <label class="xe-f">Şube<input type="text" data-k="exam.class_label" maxlength="60" placeholder="5/A"></label>
        </div>
        <label class="xe-f">Dönem<input type="text" data-k="exam.term" maxlength="80"></label>
        <div class="xe-row2">
            <label class="xe-f">Tarih<input type="date" data-k="exam.exam_date"></label>
            <label class="xe-f">Süre (dk)<input type="number" data-k="exam.duration_min" data-num min="1" max="600"></label>
        </div>
        <div class="xe-f">Şablon
            <div class="tpl-gal" id="tpl-gal">
                @foreach($templates as $key => $label)
                    <button type="button" class="tpl-card" data-tpl="{{ $key }}"><span class="th th-{{ $key }}"><i></i><u></u><u></u><u></u></span>{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="xe-f">Sayfa düzeni
            <div class="xe-cols"><button type="button" data-cols="1">▯ Tek sütun</button><button type="button" data-cols="2">▯▯ İki sütun</button></div>
        </div>
        <div class="xe-row2">
            <label class="xe-f">Yazı tipi<select data-k="settings.font_family"><option value="serif">Serif (DejaVu)</option><option value="sans">Sans (DejaVu)</option></select></label>
            <label class="xe-f">Yazı boyutu<select data-k="settings.font_size" data-num>@for($i=9;$i<=16;$i++)<option value="{{ $i }}">{{ $i }} pt</option>@endfor</select></label>
        </div>
        <label class="xe-f">Kenar boşluğu (mm)<input type="number" data-k="settings.margin_mm" data-num min="8" max="30"></label>
        <label class="xe-sw"><input type="checkbox" data-k="settings.show_points"> Soru puanlarını göster</label>
        <label class="xe-sw" style="margin-top:6px"><input type="checkbox" data-k="settings.student_box"> Ad-soyad / numara alanı</label>
        <label class="xe-sw" style="margin:6px 0 9px"><input type="checkbox" data-k="settings.page_numbers"> Sayfa numarası</label>
        <h3 style="margin:14px 0 8px">🎨 Tasarım</h3>
        <div class="xe-f">Vurgu rengi
            <div class="sw-row" id="sw-row">
                @foreach(['#1e3a8a','#2563eb','#0f766e','#15803d','#b45309','#c2410c','#b91c1c','#7c3aed','#111827'] as $c)
                    <button type="button" class="sw" data-ac="{{ $c }}" style="background:{{ $c }}" aria-label="{{ $c }}"></button>
                @endforeach
                <input type="color" data-k="settings.accent" class="sw-pick" aria-label="Özel renk">
            </div>
        </div>
        <div class="xe-row2">
            <label class="xe-f">Soru aralığı<select data-k="settings.spacing"><option value="compact">Sıkı</option><option value="normal">Normal</option><option value="relaxed">Ferah</option></select></label>
            <label class="xe-f">Soru numarası<select data-k="settings.num_style"><option value="dot">1.</option><option value="paren">1)</option><option value="soru">Soru 1.</option></select></label>
        </div>
        <label class="xe-f">Şık biçimi<select data-k="settings.opt_style"><option value="paren">A)</option><option value="bracket">(A)</option><option value="dot">A.</option></select></label>
        <label class="xe-sw"><input type="checkbox" data-k="settings.page_border"> Sayfa çerçevesi</label>
        <label class="xe-f" style="margin-top:9px">Filigran (isteğe bağlı)<input type="text" data-k="settings.watermark" maxlength="40" placeholder="Örn. ÖRNEKTİR"></label>
        <div class="xe-f">Okul logosu
            <div class="logo-box">
                <img id="logo-img" alt="" style="display:none">
                <div style="display:grid;gap:6px;flex:1">
                    <label class="xe-mini" style="text-align:center;cursor:pointer">📤 Logo yükle<input type="file" id="logo-file" accept="image/png,image/jpeg,image/webp" style="display:none"></label>
                    <button type="button" class="xe-mini" id="logo-del" style="display:none">Logoyu kaldır</button>
                    <label class="xe-sw" style="font-size:12px"><input type="checkbox" data-k="settings.show_logo"> Başlıkta göster</label>
                </div>
            </div>
            <small style="color:#64748b;font-weight:600">PNG, JPG veya WEBP · en fazla 1 MB</small>
        </div>
        <h3 style="margin:14px 0 8px">📎 Word Şablonu</h3>
        <div class="xe-f">
            <div id="wt-current" class="wt-cur" style="display:none"><span id="wt-name"></span><button type="button" class="xe-mini" id="wt-del">Kaldır</button></div>
            <label class="xe-mini" style="text-align:center;cursor:pointer">📤 Word kâğıdı yükle (.docx)<input type="file" id="wt-file" accept=".docx" style="display:none"></label>
            <small style="color:#64748b;font-weight:600;line-height:1.4">Kendi sınav kâğıdınızı yükleyin: başlık, alt bilgi ve stiller korunur; kâğıttaki sorular okunup editöre eklenir. Word çıktısında sorular şablonun soru bölümüne yazılır (başlıktaki tarih/puan gibi alanlar şablondaki haliyle kalır). Soru yerini kendiniz belirlemek için şablona <b>&#123;&#123;SORULAR&#125;&#125;</b> yazabilirsiniz.</small>
            <div id="wt-panel" class="wt-panel" style="display:none"></div>
        </div>
        <label class="xe-f" style="margin-top:9px">Sınav açıklaması<textarea data-k="settings.instructions" maxlength="1000" rows="3" style="border:1px solid #cbd5e1;border-radius:9px;padding:7px 9px;font:inherit;font-size:13px" placeholder="Örn. Her soru 10 puandır. Süre 40 dakikadır."></textarea></label>
    </aside>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/exam-q.js') }}?v={{ @filemtime(public_path('js/exam-q.js')) }}"></script>
<script>
(() => {
    const INIT = @json($payload);
    const SAVE_URL = @json(route('exams.update', $exam));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const nl = (s) => esc(s).replace(/\n/g, '<br>');
    const { LETTERS, TYPE_NAME, newQuestion: mkQuestion, matchOrder, matchKey } = window.ExamQ;
    const LOGO_BASE = @json(rtrim(asset('/'), '/').'/');
    const PAGE_W = 794, PAGE_H = 1123, MM = 3.7795;

    let uidSeq = 1;
    const state = {
        exam: { ...INIT.exam },
        settings: { ...INIT.settings },
        questions: (INIT.questions || []).map((q) => ({ uid: uidSeq++, ...q, options: q.options || [] })),
    };
    let selected = state.questions.length ? 0 : -1;
    let lastLayout = null, lastHeaderH = 0;
    let dirty = false, saving = false, saveTimer = null, previewTimer = null;

    const newQuestion = (type) => ({ uid: uidSeq++, ...mkQuestion(type) });

    /* ---------- önizleme: soru bloğu ---------- */
    const optLabel = (k) => ({ paren: LETTERS[k] + ')', bracket: '(' + LETTERS[k] + ')', dot: LETTERS[k] + '.' }[state.settings.opt_style] || LETTERS[k] + ')');
    const numLabel = (i) => ({ dot: (i + 1) + '.', paren: (i + 1) + ')', soru: 'Soru ' + (i + 1) + '.' }[state.settings.num_style] || (i + 1) + '.');
    function blockHtml(q, i) {
        const pts = state.settings.show_points && Number(q.points) > 0 ? '<span class="qp">(' + esc(String(q.points).replace('.', ',')) + ' p)</span>' : '';
        let inner = '';
        const text = q.body && q.body.trim() ? nl(q.body) : '<span style="color:#94a3b8">(Soru metnini yazın)</span>';
        if (q.type === 'multiple_choice') {
            const opts = q.options.map((o, k) => '<div class="qo"><b>' + optLabel(k) + '</b>' + esc(o) + '</div>').join('');
            const short = state.exam.columns === 1 && q.options.length <= 4 && q.options.every((o) => o.length <= 20);
            inner = '<div class="qtext">' + text + pts + '</div><div class="qopts' + (short ? ' inline' : '') + '">' + opts + '</div>';
        } else if (q.type === 'true_false') {
            inner = '<div class="qtext"><b>(&nbsp;&nbsp;&nbsp;&nbsp;)</b> ' + text + pts + '</div>';
        } else if (q.type === 'open_ended') {
            inner = '<div class="qtext">' + text + pts + '</div><div class="qlines">' + '<i></i>'.repeat(Math.max(0, Math.min(20, Number(q.answer_lines) || 0))) + '</div>';
        } else if (q.type === 'matching') {
            const n = q.options.length, order = matchOrder(n);
            const left = q.options.map((o, k) => '<div><b>(&nbsp;&nbsp;&nbsp;)</b> ' + (k + 1) + '. ' + esc(o.l) + '</div>').join('');
            const right = order.map((src, k) => '<div><b>' + optLabel(k) + '</b> ' + esc(q.options[src].r) + '</div>').join('');
            inner = '<div class="qtext">' + text + pts + '</div><div class="qmatch"><div>' + left + '</div><div>' + right + '</div></div>';
        } else {
            inner = '<div class="qtext">' + text + pts + '</div>';
        }
        const sec = q.section_title && q.section_title.trim() ? '<div class="qsec">' + esc(q.section_title) + '</div>' : '';
        return '<div class="qblk' + (i === selected ? ' sel' : '') + '" data-i="' + i + '">' + sec
            + '<div class="qrow"><span class="qn">' + numLabel(i) + '</span><div class="qbody">' + inner + '</div></div></div>';
    }

    /* ---------- önizleme: üst bilgi ---------- */
    function totalPoints() { return state.questions.reduce((a, q) => a + (Number(q.points) || 0), 0); }
    function headerHtml() {
        const e = state.exam, s = state.settings;
        const info = [e.subject, e.grade_level ? e.grade_level + '. Sınıf' : '', e.class_label, e.term].filter(Boolean).map(esc).join(' · ');
        const date = e.exam_date ? e.exam_date.split('-').reverse().join('.') : '';
        const meta = '<div class="ph-meta"><span>Tarih: ' + esc(date || '…/…/……') + '</span><span>Süre: ' + (e.duration_min ? esc(e.duration_min) + ' dk' : '……') + '</span><span>Toplam Puan: ' + esc(String(Math.round(totalPoints() * 100) / 100).replace('.', ',')) + '</span></div>';
        const stu = s.student_box ? '<div class="ph-stu"><span>Adı Soyadı:</span><span>Numara:</span><span>Sınıf:</span><span>Puan:</span></div>' : '';
        const ins = s.instructions && s.instructions.trim() ? '<div class="ph-ins">' + esc(s.instructions) + '</div>' : '';
        let top = '<div class="ph-school">' + esc(e.school_name) + '</div><div class="ph-title">' + esc(e.title) + '</div><div class="ph-info">' + info + '</div>';
        if (s.show_logo && s.logo_path) top = '<div class="ph-row"><img class="ph-logo" src="' + esc(LOGO_BASE + s.logo_path) + '" alt=""><div class="ph-txt">' + top + '</div></div>';
        if (e.template === 'modern') return '<div class="ph"><div class="ph-top">' + top + '</div><div class="ph-rest">' + meta + stu + ins + '</div></div>';
        return '<div class="ph">' + top + meta + stu + ins + '</div>';
    }

    /* ---------- sayfalama (saf fonksiyon; test edilebilir) ---------- */
    function paginate(heights, headerH, pageContentH, nCols) {
        const pages = [{ cols: Array.from({ length: nCols }, () => []) }];
        let pi = 0, ci = 0, used = 0;
        const capacity = (p) => pageContentH - (p === 0 ? headerH : 0);
        heights.forEach((h, idx) => {
            if (used > 0 && used + h > capacity(pi)) {
                ci++; used = 0;
                if (ci >= nCols) { pages.push({ cols: Array.from({ length: nCols }, () => []) }); pi++; ci = 0; }
            }
            pages[pi].cols[ci].push(idx);
            used += h;
        });
        return pages;
    }
    window.__examPaginate = paginate;

    /* ---------- önizleme: çizim ---------- */
    function renderPreview() {
        const e = state.exam, s = state.settings;
        const n = e.columns === 2 ? 2 : 1;
        const margin = Math.round(s.margin_mm * MM);
        const contentW = PAGE_W - 2 * margin;
        const gap = 24;
        const colW = n === 2 ? Math.floor((contentW - gap) / 2) : contentW;
        const footer = s.page_numbers ? 26 : 0;
        const contentH = PAGE_H - 2 * margin - footer;
        const fam = 'fam-' + s.font_family, tpl = 'tpl-' + e.template + ' sp-' + s.spacing;
        const vars = '--ac:' + s.accent + ';--bi:' + Math.round(margin / 2) + 'px;';
        const fs = 'font-size:' + s.font_size + 'pt;';

        // ölçüm
        const m = document.createElement('div');
        m.className = 'measure paper-m ' + fam + ' ' + tpl + (n === 2 ? ' two' : '');
        m.style.cssText = fs + vars + 'width:' + colW + 'px';
        m.innerHTML = state.questions.map((q, i) => blockHtml(q, i)).join('');
        const mh = document.createElement('div');
        mh.className = 'measure ' + fam + ' ' + tpl;
        mh.style.cssText = fs + vars + 'width:' + contentW + 'px';
        mh.innerHTML = headerHtml();
        document.body.appendChild(m); document.body.appendChild(mh);
        const heights = [...m.children].map((c) => c.getBoundingClientRect().height);
        const headerH = mh.getBoundingClientRect().height + 12; // başlık + alt boşluk (.ph margin)
        m.remove(); mh.remove();

        const pages = paginate(heights, headerH, contentH, n);
        lastLayout = pages.map((p) => p.cols); lastHeaderH = Math.round(headerH);
        const total = pages.length;
        const html = pages.map((pg, pIdx) => {
            const colsHtml = pg.cols.map((idxs) => '<div class="col" style="height:' + (contentH - (pIdx === 0 ? headerH : 0)) + 'px">' + idxs.map((k) => blockHtml(state.questions[k], k)).join('') + '</div>').join('');
            const empty = (!state.questions.length && pIdx === 0) ? '<div style="color:#94a3b8;text-align:center;padding:60px 0;font-weight:700">Soldaki "Soru Ekle" düğmeleriyle ilk sorunuzu ekleyin.</div>' : '';
            return '<div class="paper ' + fam + ' ' + tpl + (n === 2 ? ' two' : '') + (s.page_border ? ' bordered' : '') + '" style="' + fs + vars + 'padding:' + margin + 'px">'
                + (s.watermark && s.watermark.trim() ? '<div class="wm">' + esc(s.watermark) + '</div>' : '')
                + (pIdx === 0 ? headerHtml() : '')
                + empty
                + '<div class="cols" style="grid-template-columns:repeat(' + n + ',1fr)">' + colsHtml + '</div>'
                + (s.page_numbers ? '<div class="pg-no" style="height:' + footer + 'px;line-height:' + footer + 'px">Sayfa ' + (pIdx + 1) + ' / ' + total + '</div>' : '')
                + '</div>';
        }).join('');
        const host = $('pv-pages');
        host.innerHTML = html;
        fitScale(total);
    }

    function fitScale(totalPages) {
        const wrap = $('pv-wrap'), holder = $('pv-holder'), host = $('pv-pages');
        const avail = Math.max(280, wrap.clientWidth - 24);
        const scale = Math.min(1, avail / PAGE_W);
        host.style.transform = 'scale(' + scale + ')';
        holder.style.height = Math.ceil((totalPages * (PAGE_H + 24)) * scale) + 'px';
        host.style.left = Math.max(0, (avail - PAGE_W * scale) / 2) + 'px';
    }
    const schedulePreview = () => { clearTimeout(previewTimer); previewTimer = setTimeout(renderPreview, 120); };

    /* ---------- soru listesi (sol panel) ---------- */
    function renderList() {
        $('xe-count').textContent = '(' + state.questions.length + ' · ' + (Math.round(totalPoints() * 100) / 100) + ' puan)';
        const box = $('xe-list');
        if (!state.questions.length) { box.innerHTML = '<div class="xe-empty">Henüz soru yok. Yukarıdan bir soru tipi seçin.</div>'; return; }
        box.innerHTML = state.questions.map((q, i) => {
            const open = i === selected;
            return '<div class="xe-q' + (open ? ' sel' : '') + '" data-i="' + i + '">'
                + '<div class="xe-qh" data-act="select"><span class="xe-qn">' + (i + 1) + '</span>'
                + '<span class="xe-qt">' + esc((q.body || '').split('\n')[0] || TYPE_NAME[q.type]) + '</span>'
                + '<button type="button" class="xe-ic" data-act="up" title="Yukarı">▲</button><button type="button" class="xe-ic" data-act="down" title="Aşağı">▼</button>'
                + '<button type="button" class="xe-ic" data-act="dup" title="Çoğalt">⧉</button><button type="button" class="xe-ic del" data-act="del" title="Sil">✕</button></div>'
                + (open ? editorHtml(q, i) : '') + '</div>';
        }).join('');
    }

    const editorHtml = (q) => window.ExamQ.formHtml(q, { section: true, points: true, uid: q.uid }).replace(/<\/div>$/, '<div><button type="button" class="xe-mini" data-act="tobank"' + (q.bank_item_id ? ' disabled' : '') + '>' + (q.bank_item_id ? '✓ Havuzda' : '📚 Havuza kaydet') + '</button></div></div>');

    /* ---------- durum / kaydet ---------- */
    function setStatus(kind, text) { const c = $('xe-status'); c.className = 'xe-chip ' + kind; c.textContent = text; }
    function markDirty() {
        dirty = true; setStatus('dirty', 'Kaydedilmemiş değişiklikler');
        clearTimeout(saveTimer); saveTimer = setTimeout(save, 2500);
    }
    function payload() {
        const e = state.exam, s = state.settings;
        const numOrNull = (v) => (v === '' || v === null || v === undefined || Number.isNaN(Number(v))) ? null : Number(v);
        return {
            exam: { ...e, grade_level: numOrNull(e.grade_level), duration_min: numOrNull(e.duration_min), exam_date: e.exam_date || null, columns: e.columns === 2 ? 2 : 1 },
            settings: { ...s, font_size: Number(s.font_size), margin_mm: Number(s.margin_mm), show_points: !!s.show_points, student_box: !!s.student_box, page_numbers: !!s.page_numbers, page_border: !!s.page_border, show_logo: !!s.show_logo },
            layout: lastLayout, layout_header_h: lastHeaderH,
            questions: state.questions.map((q) => ({
                type: q.type, body: q.body || '', options: q.options, points: Number(q.points) || 0,
                answer: window.ExamQ.answerOf(q),
                section_title: q.section_title || '', answer_lines: Number(q.answer_lines) || 0, bank_item_id: q.bank_item_id || null,
            })),
        };
    }
    async function save() {
        if (saving) { clearTimeout(saveTimer); saveTimer = setTimeout(save, 800); return; }
        clearTimeout(saveTimer); saving = true; setStatus('', 'Kaydediliyor…');
        renderPreview(); // sayfa dağılımı kayıtla birlikte güncel gitsin (PDF/Word bu dağılımı kullanır)
        try {
            const res = await fetch(SAVE_URL, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload()) });
            if (!res.ok) {
                let msg = 'Kaydedilemedi (' + res.status + ')';
                try { const j = await res.json(); const first = j.errors ? Object.values(j.errors)[0][0] : j.message; if (first) msg = first; } catch (_) {}
                throw new Error(msg);
            }
            const j = await res.json(); dirty = false; setStatus('ok', 'Kaydedildi ' + (j.saved_at || ''));
        } catch (err) { setStatus('err', err.message || 'Kaydedilemedi'); }
        saving = false;
    }

    /* ---------- olaylar ---------- */
    function refreshFinalBtn() { const b = $('xe-final'); b.textContent = state.exam.status === 'final' ? '✔ Kesin (taslağa al)' : 'Kesinleştir'; b.className = 'xe-btn' + (state.exam.status === 'final' ? ' final' : ''); }
    function changed(structural) { if (structural) renderList(); schedulePreview(); markDirty(); }

    $('xe-types').addEventListener('click', (ev) => {
        const b = ev.target.closest('[data-add]'); if (!b) return;
        state.questions.push(newQuestion(b.dataset.add)); selected = state.questions.length - 1; changed(true);
        $('xe-list').lastElementChild?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    });

    $('xe-list').addEventListener('click', (ev) => {
        const card = ev.target.closest('.xe-q'); if (!card) return;
        const i = Number(card.dataset.i), q = state.questions[i], act = ev.target.closest('[data-act]')?.dataset.act;
        if (!act) return;
        if (act === 'select') { selected = selected === i ? -1 : i; renderList(); schedulePreview(); return; }
        if (act === 'up' && i > 0) { [state.questions[i - 1], state.questions[i]] = [state.questions[i], state.questions[i - 1]]; selected = i - 1; }
        else if (act === 'down' && i < state.questions.length - 1) { [state.questions[i + 1], state.questions[i]] = [state.questions[i], state.questions[i + 1]]; selected = i + 1; }
        else if (act === 'dup') { const c = JSON.parse(JSON.stringify(q)); c.uid = uidSeq++; c.bank_item_id = null; state.questions.splice(i + 1, 0, c); selected = i + 1; }
        else if (act === 'del') { state.questions.splice(i, 1); selected = Math.min(selected, state.questions.length - 1); }
        else if (act === 'tobank') { saveToBank(q); return; }
        else if (!window.ExamQ.applyClick(q, ev.target)) return;
        changed(true);
    });

    $('xe-list').addEventListener('input', (ev) => {
        const card = ev.target.closest('.xe-q'); if (!card) return;
        const q = state.questions[Number(card.dataset.i)], t = ev.target;
        if (!window.ExamQ.applyInput(q, t, card)) return;
        const hdr = card.querySelector('.xe-qt'); if (hdr && t.dataset.q === 'body') hdr.textContent = (q.body || '').split('\n')[0] || TYPE_NAME[q.type];
        schedulePreview(); markDirty();
        if (t.dataset.q === 'points') $('xe-count').textContent = '(' + state.questions.length + ' · ' + (Math.round(totalPoints() * 100) / 100) + ' puan)';
    });

    // ayar alanları
    const syncControls = () => {
        document.querySelectorAll('[data-k]').forEach((el) => {
            const [grp, key] = el.dataset.k.split('.');
            const val = state[grp][key];
            if (el.type === 'checkbox') el.checked = !!val; else el.value = val ?? '';
        });
        document.querySelectorAll('[data-tpl]').forEach((b) => b.classList.toggle('on', b.dataset.tpl === state.exam.template));
        document.querySelectorAll('[data-ac]').forEach((b) => b.classList.toggle('on', b.dataset.ac.toLowerCase() === String(state.settings.accent).toLowerCase()));
        const img = $('logo-img'), has = !!state.settings.logo_path;
        img.style.display = has ? '' : 'none'; if (has) img.src = LOGO_BASE + state.settings.logo_path;
        $('logo-del').style.display = has ? '' : 'none';
        const wn = state.settings.word_template_name || '';
        $('wt-current').style.display = wn ? 'flex' : 'none'; $('wt-name').textContent = wn;
    };
    document.querySelectorAll('[data-k]').forEach((el) => {
        const [grp, key] = el.dataset.k.split('.');
        const onChange = () => {
            let v = el.type === 'checkbox' ? el.checked : el.value;
            if (el.dataset.num !== undefined && v !== '') v = Number(v);
            state[grp][key] = v;
            if (grp === 'exam' && key === 'title') $('xe-heading').textContent = v || 'Sınav';
            if (key === 'template' || key === 'columns') renderList();
            if (key === 'accent') syncControls();
            schedulePreview(); markDirty();
        };
        el.addEventListener(el.tagName === 'SELECT' || el.type === 'checkbox' ? 'change' : 'input', onChange);
    });

    // şablon galerisi: şablon seçilince o şablonun varsayılan tasarımı uygulanır
    const PRESETS = {
        classic: { accent: '#111827', spacing: 'normal', num_style: 'dot', opt_style: 'paren', page_border: false },
        modern: { accent: '#2563eb', spacing: 'relaxed', num_style: 'dot', opt_style: 'bracket', page_border: false },
        plain: { accent: '#334155', spacing: 'compact', num_style: 'paren', opt_style: 'paren', page_border: false },
        quiz: { accent: '#0f766e', spacing: 'compact', num_style: 'soru', opt_style: 'dot', page_border: true },
        school: { accent: '#1e3a8a', spacing: 'normal', num_style: 'dot', opt_style: 'paren', page_border: true },
    };
    $('tpl-gal').addEventListener('click', (ev) => {
        const b = ev.target.closest('[data-tpl]'); if (!b) return;
        state.exam.template = b.dataset.tpl;
        Object.assign(state.settings, PRESETS[b.dataset.tpl] || {});
        if (b.dataset.tpl === 'school' && state.settings.logo_path) state.settings.show_logo = true;
        syncControls(); schedulePreview(); markDirty();
    });
    $('sw-row').addEventListener('click', (ev) => {
        const b = ev.target.closest('[data-ac]'); if (!b) return;
        state.settings.accent = b.dataset.ac; syncControls(); schedulePreview(); markDirty();
    });

    // logo yükleme / kaldırma
    const LOGO_URL = @json(route('exams.logo.upload', $exam));
    $('logo-file').addEventListener('change', async () => {
        const f = $('logo-file').files[0]; if (!f) return;
        if (f.size > 1024 * 1024) { setStatus('err', 'Logo en fazla 1 MB olabilir.'); return; }
        setStatus('', 'Logo yükleniyor…');
        const fd = new FormData(); fd.append('logo', f);
        try {
            const res = await fetch(LOGO_URL, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: fd });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(j.errors ? Object.values(j.errors)[0][0] : (j.message || 'Logo yüklenemedi'));
            state.settings.logo_path = j.logo_path; state.settings.show_logo = true;
            syncControls(); schedulePreview(); markDirty(); setStatus('ok', 'Logo yüklendi');
        } catch (err) { setStatus('err', err.message); }
        $('logo-file').value = '';
    });
    $('logo-del').addEventListener('click', async () => {
        try { await fetch(LOGO_URL, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } }); } catch (_) {}
        state.settings.logo_path = ''; state.settings.show_logo = false;
        syncControls(); schedulePreview(); markDirty();
    });
    function refreshCols() { document.querySelectorAll('[data-cols]').forEach((b) => b.classList.toggle('on', Number(b.dataset.cols) === state.exam.columns)); }
    document.querySelectorAll('[data-cols]').forEach((b) => b.addEventListener('click', () => { state.exam.columns = Number(b.dataset.cols); refreshCols(); schedulePreview(); markDirty(); }));

    /* ---------- soru havuzu (sol panel sekmesi) ---------- */
    const BANK = { search: @json(route('exams.bank.search')), store: @json(route('exams.bank.store')) };
    const pool = { page: 1, items: [], loading: false, timer: null };
    const poolParams = () => {
        const p = new URLSearchParams({ q: $('pl-q').value, difficulty: $('pl-diff').value, type: $('pl-type').value, scope: $('pl-mine').checked ? 'mine' : '' });
        if ($('pl-match').checked) { if (state.exam.subject) p.set('subject', state.exam.subject); if (state.exam.grade_level) p.set('grade', state.exam.grade_level); }
        return p;
    };
    async function loadPool(reset) {
        if (pool.loading) return; pool.loading = true;
        if (reset) { pool.page = 1; pool.items = []; }
        try {
            const res = await fetch(BANK.search + '?' + poolParams() + '&page=' + pool.page, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const j = await res.json();
            pool.items = pool.items.concat(j.data); $('pl-more').style.display = j.has_more ? 'block' : 'none';
        } catch (_) { $('pl-list').innerHTML = '<div class="xe-empty">Havuz yüklenemedi.</div>'; pool.loading = false; return; }
        pool.loading = false; renderPool();
    }
    function renderPool() {
        const used = new Set(state.questions.map((q) => q.bank_item_id).filter(Boolean));
        $('pl-list').innerHTML = pool.items.length ? pool.items.map((i) =>
            '<div class="pl-card" data-id="' + i.id + '"><div class="pl-tags"><span>' + TYPE_NAME[i.type] + '</span><span class="' + i.difficulty + '">' + ({ kolay: 'Kolay', orta: 'Orta', zor: 'Zor' }[i.difficulty]) + '</span></div>'
            + '<div class="pl-body">' + esc(i.body) + '</div>'
            + '<div class="pl-meta">' + [i.subject, i.grade_level ? i.grade_level + '. sınıf' : '', i.unit].filter(Boolean).map(esc).join(' · ') + '</div>'
            + '<button type="button" class="pl-add"' + (used.has(i.id) ? ' disabled>✓ Sınavda' : '>+ Sınava ekle') + '</button></div>'
        ).join('') : '<div class="xe-empty">Havuzda uygun soru bulunamadı.</div>';
    }
    function addFromBank(i) {
        const q = newQuestion(i.type);
        q.body = i.body; q.options = JSON.parse(JSON.stringify(i.options || [])); q.answer = i.answer || q.answer;
        q.bank_item_id = i.id;
        state.questions.push(q); selected = state.questions.length - 1;
        renderList(); renderPool(); schedulePreview(); markDirty();
    }
    async function saveToBank(q) {
        if (!(q.body || '').trim()) { setStatus('err', 'Önce soru metnini yazın.'); return; }
        setStatus('', 'Havuza kaydediliyor…');
        try {
            const res = await fetch(BANK.store, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({
                type: q.type, body: q.body, options: q.options, answer: window.ExamQ.answerOf(q), difficulty: 'orta',
                subject: state.exam.subject || '', grade_level: state.exam.grade_level || null, unit: '', tags: '', is_shared: false }) });
            if (!res.ok) throw new Error('Havuza kaydedilemedi (' + res.status + ')');
            const j = await res.json(); q.bank_item_id = j.item.id;
            renderList(); markDirty(); setStatus('ok', 'Soru havuza kaydedildi');
        } catch (err) { setStatus('err', err.message); }
    }
    $('xe-tabs').addEventListener('click', (ev) => {
        const b = ev.target.closest('[data-tab]'); if (!b) return;
        document.querySelectorAll('#xe-tabs button').forEach((x) => x.classList.toggle('on', x === b));
        $('tab-q').style.display = b.dataset.tab === 'q' ? '' : 'none';
        $('tab-pool').style.display = b.dataset.tab === 'pool' ? '' : 'none';
        if (b.dataset.tab === 'pool') { $('pl-match').checked = !!(state.exam.subject || state.exam.grade_level); loadPool(true); }
    });
    $('pl-list').addEventListener('click', (ev) => {
        const btn = ev.target.closest('.pl-add'); if (!btn || btn.disabled) return;
        const item = pool.items.find((x) => x.id === Number(btn.closest('.pl-card').dataset.id)); if (item) addFromBank(item);
    });
    $('pl-more').addEventListener('click', () => { pool.page++; loadPool(false); });
    ['pl-diff', 'pl-type', 'pl-match', 'pl-mine'].forEach((id) => $(id).addEventListener('change', () => loadPool(true)));
    $('pl-q').addEventListener('input', () => { clearTimeout(pool.timer); pool.timer = setTimeout(() => loadPool(true), 350); });

    $('xe-save').addEventListener('click', save);
    $('xe-final').addEventListener('click', () => { state.exam.status = state.exam.status === 'final' ? 'draft' : 'final'; refreshFinalBtn(); markDirty(); save(); });
    $('pv-pages').addEventListener('click', (ev) => {
        const b = ev.target.closest('.qblk'); if (!b) return;
        selected = Number(b.dataset.i); renderList(); schedulePreview();
        $('xe-list').querySelector('.xe-q.sel')?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    });
    window.addEventListener('resize', () => fitScale($('pv-pages').children.length));
    window.addEventListener('beforeunload', (ev) => { if (dirty) { ev.preventDefault(); ev.returnValue = ''; } });

    $('xe-heading').textContent = state.exam.title || 'Sınav';
    syncControls(); refreshFinalBtn(); refreshCols(); renderList();
    const ensureFonts = () => {
        if (!document.fonts || !document.fonts.load) return Promise.resolve();
        const f = state.settings.font_family === 'sans' ? 'ExamSans' : 'ExamSerif';
        return Promise.all(['400', '700'].map((w) => document.fonts.load(w + ' 11pt ' + f)).concat(document.fonts.load('italic 400 11pt ' + f))).catch(() => {});
    };
    document.querySelector('[data-k="settings.font_family"]').addEventListener('change', () => ensureFonts().then(renderPreview));
    ensureFonts().then(renderPreview);
    renderPreview();

    // Word menüsü
    const WORD_URL = @json(route('exams.word', $exam));
    $('xe-word').addEventListener('click', (ev) => { ev.stopPropagation(); $('xe-dd-w').classList.toggle('open'); $('xe-dd').classList.remove('open'); });
    $('xe-pdf').addEventListener('click', () => $('xe-dd-w').classList.remove('open'));
    document.addEventListener('click', () => $('xe-dd-w').classList.remove('open'));
    $('xe-dd-w').addEventListener('click', async (ev) => {
        const b = ev.target.closest('[data-word]'); if (!b) return;
        $('xe-dd-w').classList.remove('open');
        await ensureFonts(); await save();
        if (!dirty) { setStatus('ok', 'Word dosyası hazırlanıyor…'); window.location.href = WORD_URL + '?mode=' + b.dataset.word; }
    });

    // Word şablonu yükle / kaldır
    const WT_URL = @json(route('exams.word.template.upload', $exam));
    const panel = $('wt-panel');
    $('wt-file').addEventListener('change', async () => {
        const f = $('wt-file').files[0]; if (!f) return;
        if (f.size > 5 * 1024 * 1024) { setStatus('err', 'Dosya en fazla 5 MB olabilir.'); return; }
        setStatus('', 'Word kâğıdı okunuyor…');
        const fd = new FormData(); fd.append('word', f);
        try {
            const res = await fetch(WT_URL, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: fd });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(j.message || (j.errors ? Object.values(j.errors)[0][0] : 'Yüklenemedi (' + res.status + ')'));
            state.settings.word_template_name = j.name; syncControls(); setStatus('ok', 'Şablon eklendi');
            const n = j.questions.length, nt = j.notes.skipped_tables, nm = j.notes.mc_without_answer;
            if (!n) { panel.style.display = 'grid'; panel.innerHTML = '<div>Şablon eklendi. Kâğıtta numaralı soru bulunamadı; soruları editörden ekleyebilirsiniz.</div><button type="button" id="wt-close">Tamam</button>'; $('wt-close').onclick = () => { panel.style.display = 'none'; }; return; }
            panel.style.display = 'grid';
            panel.innerHTML = '<div>📄 Kâğıttan <b>' + n + '</b> soru okundu.' + (nt ? ' ' + nt + ' tablo (ör. eşleştirme) okunamadı.' : '') + (nm ? ' Çoktan seçmeli soruların doğru şıkkını işaretleyin.' : '') + '</div>'
                + '<button type="button" class="p" id="wt-add">Mevcut soruların sonuna ekle</button><button type="button" id="wt-rep">Mevcut soruların yerine koy</button><button type="button" id="wt-skip">Soru ekleme (yalnız şablon)</button>';
            const take = (replace) => {
                const mapped = j.questions.map((q) => ({ uid: uidSeq++, ...q, options: q.options || [] }));
                if (replace) state.questions = [];
                state.questions.push(...mapped); selected = state.questions.length ? state.questions.length - mapped.length : -1;
                panel.style.display = 'none'; renderList(); schedulePreview(); markDirty();
            };
            $('wt-add').onclick = () => take(false);
            $('wt-rep').onclick = () => take(true);
            $('wt-skip').onclick = () => { panel.style.display = 'none'; };
        } catch (err) { setStatus('err', err.message); }
        $('wt-file').value = '';
    });
    $('wt-del').addEventListener('click', async () => {
        try { await fetch(WT_URL, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } }); } catch (_) {}
        state.settings.word_template_name = ''; syncControls(); setStatus('ok', 'Şablon kaldırıldı');
    });

    // PDF menüsü
    const PDF_URL = @json(route('exams.pdf', $exam));
    $('xe-pdf').addEventListener('click', (ev) => { ev.stopPropagation(); $('xe-dd').classList.toggle('open'); });
    document.addEventListener('click', () => $('xe-dd').classList.remove('open'));
    $('xe-dd').addEventListener('click', async (ev) => {
        const b = ev.target.closest('[data-pdf]'); if (!b) return;
        $('xe-dd').classList.remove('open');
        await ensureFonts(); await save();
        if (!dirty) { setStatus('ok', 'PDF hazırlanıyor…'); window.location.href = PDF_URL + '?mode=' + b.dataset.pdf; }
    });
})();
</script>
@endpush
