@extends('layout.app')
@section('title','Sınav Editörü')
@section('content')
<style>
    .xe-top{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px}
    .xe-top h1{margin:0;font-size:22px;flex:1 1 auto;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .xe-chip{padding:6px 12px;border-radius:999px;font-size:12.5px;font-weight:800;background:#e2e8f0;color:#475569}
    .xe-chip.ok{background:#dcfce7;color:#166534}.xe-chip.dirty{background:#fef3c7;color:#92400e}.xe-chip.err{background:#fee2e2;color:#991b1b}
    .xe-btn{height:40px;padding:0 16px;border-radius:11px;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-weight:800;font-size:13.5px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
    .xe-btn.primary{background:linear-gradient(135deg,#2563eb,#1d4ed8);border-color:transparent;color:#fff;box-shadow:0 6px 14px rgba(37,99,235,.28)}
    .xe-btn.final{background:#16a34a;border-color:transparent;color:#fff}
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
    .xe-ic{border:0;background:#e2e8f0;border-radius:8px;width:28px;height:28px;cursor:pointer;font-weight:900;color:#334155}
    .xe-ic:hover{background:#cbd5e1}.xe-ic.del:hover{background:#fecaca;color:#b91c1c}
    .xe-qb{padding:10px;display:grid;gap:9px;border-top:1px solid #e2e8f0}
    .xe-f{display:grid;gap:3px;font-size:11.5px;font-weight:800;color:#475569}
    .xe-f input,.xe-f select,.xe-f textarea,.xe-qb input[type=text],.xe-qb input[type=number]{width:100%;border:1px solid #cbd5e1;border-radius:9px;padding:7px 9px;font:inherit;font-weight:600;font-size:13px;background:#fff}
    .xe-f textarea{min-height:76px;resize:vertical}
    .xe-f input,.xe-f select{height:36px}
    .xe-opt{display:flex;align-items:center;gap:6px}
    .xe-opt b{flex:0 0 22px;color:#2563eb}.xe-opt input[type=radio]{width:auto;margin:0}
    .xe-mini{border:0;background:#f1f5f9;border-radius:8px;padding:5px 10px;font-weight:800;font-size:12px;cursor:pointer;color:#334155}
    .xe-row2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .xe-empty{padding:18px 8px;text-align:center;color:#94a3b8;font-weight:700;font-size:13px}
    .xe-sw{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#334155}
    .xe-sw input{width:auto}
    .xe-cols{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .xe-cols button{border:2px solid #e2e8f0;border-radius:11px;background:#fff;padding:8px;font-weight:800;font-size:12.5px;cursor:pointer;color:#334155}
    .xe-cols button.on{border-color:#2563eb;background:#eff6ff;color:#1d4ed8}
    .xe-right .xe-f{margin-bottom:9px}
    /* ---- A4 önizleme ---- */
    .pv-wrap{background:#e2e8f0;border-radius:16px;padding:12px;min-width:0}
    .pv-holder{position:relative;width:100%}
    .pv-pages{position:absolute;top:0;left:0;transform-origin:top left}
    .paper{position:relative;width:794px;height:1123px;background:#fff;margin-bottom:24px;box-shadow:0 10px 30px rgba(15,23,42,.18);color:#111;overflow:hidden;box-sizing:border-box;line-height:1.35}
    .paper.fam-serif,.measure.fam-serif{font-family:"Times New Roman",Times,Georgia,serif}
    .paper.fam-sans,.measure.fam-sans{font-family:Arial,Helvetica,sans-serif}
    .measure{position:absolute;left:-99999px;top:0;visibility:hidden;box-sizing:border-box}
    .paper .cols{display:grid;gap:24px;align-items:start}
    .paper .col{min-width:0;overflow:hidden}
    .paper.two .cols{background:linear-gradient(#cbd5e1,#cbd5e1) center top/1px 100% no-repeat}
    .qblk{padding-bottom:12px;break-inside:avoid}
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
    .tpl-modern .ph{border-radius:10px;overflow:hidden;border:1px solid #c7d2fe}
    .tpl-modern .ph-top{background:linear-gradient(120deg,#1e3a8a,#2563eb);color:#fff;padding:10px 14px}
    .tpl-modern .ph-top .ph-info{color:#dbeafe}
    .tpl-modern .ph-rest{padding:6px 14px 10px}
    .tpl-modern .ph-meta{border-color:#c7d2fe}
    .tpl-modern .qn{color:#1d4ed8}
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
    <button type="button" class="xe-btn primary" id="xe-save">💾 Kaydet</button>
</div>

<div class="xe-grid">
    <aside class="xe-panel xe-left">
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
        <label class="xe-f">Şablon<select data-k="exam.template">@foreach($templates as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
        <div class="xe-f">Sayfa düzeni
            <div class="xe-cols"><button type="button" data-cols="1">▯ Tek sütun</button><button type="button" data-cols="2">▯▯ İki sütun</button></div>
        </div>
        <div class="xe-row2">
            <label class="xe-f">Yazı tipi<select data-k="settings.font_family"><option value="serif">Times (Serif)</option><option value="sans">Arial (Sans)</option></select></label>
            <label class="xe-f">Yazı boyutu<select data-k="settings.font_size" data-num>@for($i=9;$i<=16;$i++)<option value="{{ $i }}">{{ $i }} pt</option>@endfor</select></label>
        </div>
        <label class="xe-f">Kenar boşluğu (mm)<input type="number" data-k="settings.margin_mm" data-num min="8" max="30"></label>
        <label class="xe-sw"><input type="checkbox" data-k="settings.show_points"> Soru puanlarını göster</label>
        <label class="xe-sw" style="margin-top:6px"><input type="checkbox" data-k="settings.student_box"> Ad-soyad / numara alanı</label>
        <label class="xe-sw" style="margin:6px 0 9px"><input type="checkbox" data-k="settings.page_numbers"> Sayfa numarası</label>
        <label class="xe-f">Sınav açıklaması<textarea data-k="settings.instructions" maxlength="1000" rows="3" style="border:1px solid #cbd5e1;border-radius:9px;padding:7px 9px;font:inherit;font-size:13px" placeholder="Örn. Her soru 10 puandır. Süre 40 dakikadır."></textarea></label>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const INIT = @json($payload);
    const SAVE_URL = @json(route('exams.update', $exam));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const nl = (s) => esc(s).replace(/\n/g, '<br>');
    const LETTERS = 'ABCDEFGHIJ';
    const TYPE_NAME = { multiple_choice: 'Çoktan seçmeli', true_false: 'Doğru / Yanlış', fill_blank: 'Boşluk doldurma', open_ended: 'Açık uçlu', matching: 'Eşleştirme' };
    const PAGE_W = 794, PAGE_H = 1123, MM = 3.7795;

    let uidSeq = 1;
    const state = {
        exam: { ...INIT.exam },
        settings: { ...INIT.settings },
        questions: (INIT.questions || []).map((q) => ({ uid: uidSeq++, ...q, options: q.options || [] })),
    };
    let selected = state.questions.length ? 0 : -1;
    let dirty = false, saving = false, saveTimer = null, previewTimer = null;

    /* ---------- soru fabrikası ---------- */
    function newQuestion(type) {
        const base = { uid: uidSeq++, type, body: '', options: [], answer: '', points: 5, section_title: '', answer_lines: 0, bank_item_id: null };
        if (type === 'multiple_choice') { base.options = ['', '', '', '']; base.answer = 'A'; }
        if (type === 'true_false') { base.answer = 'D'; }
        if (type === 'fill_blank') { base.body = ''; }
        if (type === 'open_ended') { base.answer_lines = 3; base.points = 10; }
        if (type === 'matching') { base.options = [{ l: '', r: '' }, { l: '', r: '' }, { l: '', r: '' }, { l: '', r: '' }]; base.points = 10; }
        return base;
    }
    const shiftFor = (n) => Math.max(1, Math.floor(n / 2));
    // Eşleştirmede sağ sütun karışık gösterilir; doğru eşleşme anahtarı üretilir (ör. 1-C 2-A).
    function matchOrder(n) { const s = shiftFor(n); return Array.from({ length: n }, (_, j) => (j + s) % n); }
    function matchKey(q) {
        const n = q.options.length, order = matchOrder(n);
        return q.options.map((_, i) => (i + 1) + '-' + LETTERS[order.indexOf(i)]).join(' ');
    }

    /* ---------- önizleme: soru bloğu ---------- */
    function blockHtml(q, i) {
        const pts = state.settings.show_points && Number(q.points) > 0 ? '<span class="qp">(' + esc(String(q.points).replace('.', ',')) + ' p)</span>' : '';
        let inner = '';
        const text = q.body && q.body.trim() ? nl(q.body) : '<span style="color:#94a3b8">(Soru metnini yazın)</span>';
        if (q.type === 'multiple_choice') {
            const opts = q.options.map((o, k) => '<div class="qo"><b>' + LETTERS[k] + ')</b>' + esc(o) + '</div>').join('');
            const short = state.exam.columns === 1 && q.options.length <= 4 && q.options.every((o) => o.length <= 20);
            inner = '<div class="qtext">' + text + pts + '</div><div class="qopts' + (short ? ' inline' : '') + '">' + opts + '</div>';
        } else if (q.type === 'true_false') {
            inner = '<div class="qtext"><b>(&nbsp;&nbsp;&nbsp;&nbsp;)</b> ' + text + pts + '</div>';
        } else if (q.type === 'open_ended') {
            inner = '<div class="qtext">' + text + pts + '</div><div class="qlines">' + '<i></i>'.repeat(Math.max(0, Math.min(20, Number(q.answer_lines) || 0))) + '</div>';
        } else if (q.type === 'matching') {
            const n = q.options.length, order = matchOrder(n);
            const left = q.options.map((o, k) => '<div><b>(&nbsp;&nbsp;&nbsp;)</b> ' + (k + 1) + '. ' + esc(o.l) + '</div>').join('');
            const right = order.map((src, k) => '<div><b>' + LETTERS[k] + ')</b> ' + esc(q.options[src].r) + '</div>').join('');
            inner = '<div class="qtext">' + text + pts + '</div><div class="qmatch"><div>' + left + '</div><div>' + right + '</div></div>';
        } else {
            inner = '<div class="qtext">' + text + pts + '</div>';
        }
        const sec = q.section_title && q.section_title.trim() ? '<div class="qsec">' + esc(q.section_title) + '</div>' : '';
        return '<div class="qblk' + (i === selected ? ' sel' : '') + '" data-i="' + i + '">' + sec
            + '<div class="qrow"><span class="qn">' + (i + 1) + '.</span><div class="qbody">' + inner + '</div></div></div>';
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
        const top = '<div class="ph-school">' + esc(e.school_name) + '</div><div class="ph-title">' + esc(e.title) + '</div><div class="ph-info">' + info + '</div>';
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
        const fam = 'fam-' + s.font_family, tpl = 'tpl-' + e.template;
        const fs = 'font-size:' + s.font_size + 'pt;';

        // ölçüm
        const m = document.createElement('div');
        m.className = 'measure paper-m ' + fam + ' ' + tpl + (n === 2 ? ' two' : '');
        m.style.cssText = fs + 'width:' + colW + 'px';
        m.innerHTML = state.questions.map((q, i) => blockHtml(q, i)).join('');
        const mh = document.createElement('div');
        mh.className = 'measure ' + fam + ' ' + tpl;
        mh.style.cssText = fs + 'width:' + contentW + 'px';
        mh.innerHTML = headerHtml();
        document.body.appendChild(m); document.body.appendChild(mh);
        const heights = [...m.children].map((c) => c.getBoundingClientRect().height);
        const headerH = mh.getBoundingClientRect().height + 4;
        m.remove(); mh.remove();

        const pages = paginate(heights, headerH, contentH, n);
        const total = pages.length;
        const html = pages.map((pg, pIdx) => {
            const colsHtml = pg.cols.map((idxs) => '<div class="col" style="height:' + (contentH - (pIdx === 0 ? headerH : 0)) + 'px">' + idxs.map((k) => blockHtml(state.questions[k], k)).join('') + '</div>').join('');
            const empty = (!state.questions.length && pIdx === 0) ? '<div style="color:#94a3b8;text-align:center;padding:60px 0;font-weight:700">Soldaki "Soru Ekle" düğmeleriyle ilk sorunuzu ekleyin.</div>' : '';
            return '<div class="paper ' + fam + ' ' + tpl + (n === 2 ? ' two' : '') + '" style="' + fs + 'padding:' + margin + 'px">'
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

    function editorHtml(q, i) {
        let f = '<div class="xe-qb">';
        f += '<label class="xe-f">Bölüm başlığı (isteğe bağlı)<input type="text" data-q="section_title" value="' + esc(q.section_title) + '" maxlength="200" placeholder="Örn. A. Çoktan Seçmeli Sorular"></label>';
        f += '<label class="xe-f">' + TYPE_NAME[q.type] + ' — soru metni<textarea data-q="body" maxlength="6000" placeholder="' + (q.type === 'fill_blank' ? 'Boşlukları ........ ile gösterin' : 'Soru metnini yazın') + '">' + esc(q.body) + '</textarea></label>';
        if (q.type === 'multiple_choice') {
            f += '<div class="xe-f">Seçenekler (doğru olanı işaretleyin)</div>';
            q.options.forEach((o, k) => {
                f += '<div class="xe-opt"><input type="radio" name="ans' + q.uid + '" data-ans="' + LETTERS[k] + '"' + (q.answer === LETTERS[k] ? ' checked' : '') + '><b>' + LETTERS[k] + ')</b>'
                    + '<input type="text" data-opt="' + k + '" value="' + esc(o) + '" maxlength="500">'
                    + (q.options.length > 2 ? '<button type="button" class="xe-ic del" data-act="optdel" data-k="' + k + '">✕</button>' : '') + '</div>';
            });
            if (q.options.length < 6) f += '<div><button type="button" class="xe-mini" data-act="optadd">+ Seçenek ekle</button></div>';
        } else if (q.type === 'true_false') {
            f += '<div class="xe-f">Doğru cevap</div><div class="xe-row2"><label class="xe-sw"><input type="radio" name="ans' + q.uid + '" data-ans="D"' + (q.answer !== 'Y' ? ' checked' : '') + '> Doğru</label><label class="xe-sw"><input type="radio" name="ans' + q.uid + '" data-ans="Y"' + (q.answer === 'Y' ? ' checked' : '') + '> Yanlış</label></div>';
        } else if (q.type === 'matching') {
            f += '<div class="xe-f">Eşleşen çiftler (sol ↔ sağ)</div>';
            q.options.forEach((o, k) => {
                f += '<div class="xe-opt"><b>' + (k + 1) + '</b><input type="text" data-pl="' + k + '" value="' + esc(o.l) + '" placeholder="Sol" maxlength="500"><input type="text" data-pr="' + k + '" value="' + esc(o.r) + '" placeholder="Sağ" maxlength="500">'
                    + (q.options.length > 2 ? '<button type="button" class="xe-ic del" data-act="pairdel" data-k="' + k + '">✕</button>' : '') + '</div>';
            });
            if (q.options.length < 8) f += '<div><button type="button" class="xe-mini" data-act="pairadd">+ Çift ekle</button></div>';
            f += '<div class="xe-f" id="mk' + q.uid + '">Doğru eşleşme: ' + esc(matchKey(q)) + '</div>';
        } else if (q.type === 'open_ended') {
            f += '<div class="xe-row2"><label class="xe-f">Cevap satırı<input type="number" data-q="answer_lines" min="0" max="20" value="' + Number(q.answer_lines) + '"></label>'
                + '<label class="xe-f">Puan<input type="number" data-q="points" min="0" max="1000" step="0.5" value="' + Number(q.points) + '"></label></div>';
            f += '<label class="xe-f">Cevap anahtarı (öğretmen için)<textarea data-q="answer" maxlength="2000">' + esc(q.answer) + '</textarea></label>';
        } else if (q.type === 'fill_blank') {
            f += '<label class="xe-f">Doğru cevap<input type="text" data-q="answer" value="' + esc(q.answer) + '" maxlength="2000"></label>';
        }
        if (q.type !== 'open_ended') f += '<label class="xe-f">Puan<input type="number" data-q="points" min="0" max="1000" step="0.5" value="' + Number(q.points) + '"></label>';
        return f + '</div>';
    }

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
            settings: { ...s, font_size: Number(s.font_size), margin_mm: Number(s.margin_mm), show_points: !!s.show_points, student_box: !!s.student_box, page_numbers: !!s.page_numbers },
            questions: state.questions.map((q) => ({
                type: q.type, body: q.body || '', options: q.options, points: Number(q.points) || 0,
                answer: q.type === 'matching' ? matchKey(q) : (q.answer || ''),
                section_title: q.section_title || '', answer_lines: Number(q.answer_lines) || 0, bank_item_id: q.bank_item_id || null,
            })),
        };
    }
    async function save() {
        if (saving) { clearTimeout(saveTimer); saveTimer = setTimeout(save, 800); return; }
        clearTimeout(saveTimer); saving = true; setStatus('', 'Kaydediliyor…');
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
        else if (act === 'optadd') { q.options.push(''); }
        else if (act === 'optdel') { const k = Number(ev.target.dataset.k); q.options.splice(k, 1); if (LETTERS.indexOf(q.answer) >= q.options.length) q.answer = 'A'; }
        else if (act === 'pairadd') { q.options.push({ l: '', r: '' }); }
        else if (act === 'pairdel') { q.options.splice(Number(ev.target.dataset.k), 1); }
        else return;
        changed(true);
    });

    $('xe-list').addEventListener('input', (ev) => {
        const card = ev.target.closest('.xe-q'); if (!card) return;
        const q = state.questions[Number(card.dataset.i)], t = ev.target;
        if (t.dataset.q) { q[t.dataset.q] = t.type === 'number' ? t.value : t.value; if (t.dataset.q === 'points') q.points = t.value === '' ? 0 : Number(t.value); if (t.dataset.q === 'answer_lines') q.answer_lines = Number(t.value) || 0; }
        else if (t.dataset.opt !== undefined) q.options[Number(t.dataset.opt)] = t.value;
        else if (t.dataset.pl !== undefined) { q.options[Number(t.dataset.pl)].l = t.value; const mk = $('mk' + q.uid); if (mk) mk.textContent = 'Doğru eşleşme: ' + matchKey(q); }
        else if (t.dataset.pr !== undefined) { q.options[Number(t.dataset.pr)].r = t.value; }
        else if (t.dataset.ans) { q.answer = t.dataset.ans; }
        else return;
        const hdr = card.querySelector('.xe-qt'); if (hdr && t.dataset.q === 'body') hdr.textContent = (q.body || '').split('\n')[0] || TYPE_NAME[q.type];
        schedulePreview(); markDirty();
        if (t.dataset.q === 'points') $('xe-count').textContent = '(' + state.questions.length + ' · ' + (Math.round(totalPoints() * 100) / 100) + ' puan)';
    });

    // ayar alanları
    document.querySelectorAll('[data-k]').forEach((el) => {
        const [grp, key] = el.dataset.k.split('.');
        const get = () => state[grp][key];
        if (el.type === 'checkbox') el.checked = !!get(); else el.value = get() ?? '';
        const onChange = () => {
            let v = el.type === 'checkbox' ? el.checked : el.value;
            if (el.dataset.num !== undefined && v !== '') v = Number(v);
            state[grp][key] = v;
            if (grp === 'exam' && key === 'title') $('xe-heading').textContent = v || 'Sınav';
            if (key === 'template' || key === 'columns') renderList();
            schedulePreview(); markDirty();
        };
        el.addEventListener(el.tagName === 'SELECT' || el.type === 'checkbox' ? 'change' : 'input', onChange);
    });
    function refreshCols() { document.querySelectorAll('[data-cols]').forEach((b) => b.classList.toggle('on', Number(b.dataset.cols) === state.exam.columns)); }
    document.querySelectorAll('[data-cols]').forEach((b) => b.addEventListener('click', () => { state.exam.columns = Number(b.dataset.cols); refreshCols(); schedulePreview(); markDirty(); }));

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
    refreshFinalBtn(); refreshCols(); renderList();
    (document.fonts?.ready || Promise.resolve()).then(renderPreview);
    renderPreview();
})();
</script>
@endpush
