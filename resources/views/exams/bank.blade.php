@extends('layout.app')
@section('title','Soru Havuzu')
@section('content')
<link rel="stylesheet" href="{{ asset('css/exam-q.css') }}?v={{ @filemtime(public_path('css/exam-q.css')) }}">
<style>
    .bk-top{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .bk-top h1{margin:0;font-size:22px;flex:1 1 auto}
    .bk-result{background:#f0fdf4;border:1px solid #86efac;border-radius:14px;padding:12px 16px;margin-bottom:14px;font-size:13.5px;font-weight:700;color:#14532d}
    .bk-result.warn{background:#fffbeb;border-color:#fcd34d;color:#78350f}
    .bk-result.err{background:#fef2f2;border-color:#fca5a5;color:#7f1d1d}
    .bk-result ul{margin:6px 0 0;padding-left:20px;font-weight:600}
    .bk-filters{display:grid;grid-template-columns:minmax(200px,2fr) repeat(5,minmax(120px,1fr));gap:8px;margin-bottom:14px}
    .bk-filters input,.bk-filters select{height:40px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;font:inherit;font-size:13.5px;background:#fff;width:100%}
    .bk-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:12px}
    .bk-card{background:#fff;border:1px solid #dbe5f2;border-radius:16px;padding:14px;display:grid;gap:9px;box-shadow:0 8px 18px rgba(15,23,42,.04);align-content:start}
    .bk-tags{display:flex;gap:6px;flex-wrap:wrap}
    .bk-tag{padding:2px 9px;border-radius:999px;font-size:11.5px;font-weight:800;background:#e0e7ff;color:#3730a3}
    .bk-tag.kolay{background:#dcfce7;color:#166534}.bk-tag.orta{background:#fef3c7;color:#92400e}.bk-tag.zor{background:#fee2e2;color:#991b1b}
    .bk-tag.sh{background:#cffafe;color:#155e75}
    .bk-body{font-size:14px;font-weight:600;color:#0f172a;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;white-space:pre-wrap;word-break:break-word}
    .bk-opts{font-size:12.5px;color:#475569;display:grid;gap:2px}
    .bk-opts .ok{color:#15803d;font-weight:800}
    .bk-meta{font-size:12px;color:#64748b;font-weight:700}
    .bk-act{display:flex;gap:8px;flex-wrap:wrap}
    .bk-act button{height:34px;padding:0 12px;border-radius:9px;border:1px solid #cbd5e1;background:#fff;font-weight:800;font-size:12.5px;cursor:pointer;color:#334155}
    .bk-act button.del{background:#fff1f2;border-color:#fecdd3;color:#be123c}
    .bk-act button.armed{background:#be123c;color:#fff;border-color:transparent}
    .bk-empty{padding:40px 10px;text-align:center;color:#94a3b8;font-weight:700;grid-column:1/-1}
    .bk-more{display:block;margin:16px auto;height:42px;padding:0 22px}
    .bk-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:3050;padding:14px}
    .bk-modal.open{display:flex}
    .bk-dlg{width:min(96vw,720px);max-height:94vh;overflow:auto;background:#fff;border-radius:20px;padding:18px;box-shadow:0 30px 80px rgba(2,6,23,.35)}
    .bk-dlg h2{margin:0 0 12px;font-size:19px}
    .bk-grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}
    .bk-err{background:#fee2e2;color:#991b1b;border-radius:10px;padding:8px 12px;font-weight:700;font-size:13px;margin-bottom:10px;display:none}
    .bk-foot{display:flex;justify-content:flex-end;gap:10px;margin-top:14px}
    @media (max-width:900px){.bk-filters{grid-template-columns:1fr 1fr}.bk-grid2{grid-template-columns:1fr}}
</style>

<div class="bk-top">
    <a class="xe-btn" href="{{ route('exams.index') }}">← Sınavlar</a>
    <h1>📚 Soru Havuzu</h1>
    <a class="xe-btn" href="{{ route('exams.bank.template') }}" title="Her soru tipinden örnek satır içeren boş şablon">⬇ Şablon indir</a>
    <a class="xe-btn" href="{{ route('exams.bank.export') }}" id="bk-export" title="Ekranda görünen filtreye uyan soruları aynı biçimde indirir">⬇ Dışa aktar</a>
    <button type="button" class="xe-btn" id="bk-import" title="Şablon biçimindeki Excel/CSV dosyasından toplu soru yükler">⬆ İçe aktar</button>
    <input type="file" id="bk-file" accept=".xlsx,.csv,.txt" style="display:none">
    <button type="button" class="xe-btn primary" id="bk-new">➕ Yeni Soru</button>
</div>
<div class="bk-result" id="bk-result" style="display:none"></div>

<div class="bk-filters">
    <input type="search" id="f-q" placeholder="Soru, konu veya etiket ara…">
    <select id="f-subject"><option value="">Tüm dersler</option>@foreach($subjects as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach</select>
    <select id="f-grade"><option value="">Tüm düzeyler</option>@for($i=1;$i<=12;$i++)<option value="{{ $i }}">{{ $i }}. sınıf</option>@endfor</select>
    <select id="f-diff"><option value="">Tüm zorluklar</option><option value="kolay">Kolay</option><option value="orta">Orta</option><option value="zor">Zor</option></select>
    <select id="f-type"><option value="">Tüm tipler</option><option value="multiple_choice">Çoktan seçmeli</option><option value="true_false">Doğru / Yanlış</option><option value="fill_blank">Boşluk doldurma</option><option value="open_ended">Açık uçlu</option><option value="matching">Eşleştirme</option></select>
    <select id="f-scope"><option value="">{{ $isAdmin ? 'Tüm sorular' : 'Benim + paylaşılan' }}</option><option value="mine">Yalnızca benim</option></select>
</div>

<div class="bk-list" id="bk-list"></div>
<button type="button" class="xe-btn bk-more" id="bk-more" style="display:none">Daha fazla göster</button>
<div style="text-align:center;color:#64748b;font-weight:700;font-size:13px" id="bk-total"></div>

<div class="bk-modal" id="bk-modal" role="dialog" aria-modal="true">
    <div class="bk-dlg">
        <h2 id="bk-title">Yeni Soru</h2>
        <div class="bk-err" id="bk-err"></div>
        <div class="bk-grid2">
            <label class="xe-f">Soru tipi<select id="m-type">
                <option value="multiple_choice">Çoktan seçmeli</option><option value="true_false">Doğru / Yanlış</option><option value="fill_blank">Boşluk doldurma</option><option value="open_ended">Açık uçlu</option><option value="matching">Eşleştirme</option>
            </select></label>
            <label class="xe-f">Zorluk<select id="m-diff"><option value="kolay">Kolay</option><option value="orta" selected>Orta</option><option value="zor">Zor</option></select></label>
            <label class="xe-f">Ders<input type="text" id="m-subject" maxlength="120" list="bk-subjects" placeholder="Bilişim Teknolojileri"></label>
            <label class="xe-f">Sınıf düzeyi<select id="m-grade"><option value="">—</option>@for($i=1;$i<=12;$i++)<option value="{{ $i }}">{{ $i }}. sınıf</option>@endfor</select></label>
            <label class="xe-f">Ünite / konu<input type="text" id="m-unit" maxlength="160"></label>
            <label class="xe-f">Etiketler (virgülle)<input type="text" id="m-tags" maxlength="255"></label>
        </div>
        <datalist id="bk-subjects">@foreach($subjects as $s)<option value="{{ $s }}">@endforeach</datalist>
        <div id="m-form"></div>
        <label class="xe-sw" style="margin-top:8px"><input type="checkbox" id="m-shared"> Diğer öğretmenlerle paylaş</label>
        <div class="bk-foot">
            <button type="button" class="xe-btn" id="bk-cancel">İptal</button>
            <button type="button" class="xe-btn primary" id="bk-save">Kaydet</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/exam-q.js') }}?v={{ @filemtime(public_path('js/exam-q.js')) }}"></script>
<script>
(() => {
    const URLS = { search: @json(route('exams.bank.search')), store: @json(route('exams.bank.store')), item: @json(url('/sinav-olustur/soru-havuzu')) };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const { esc, TYPE_NAME, LETTERS, newQuestion, answerOf, formHtml, applyClick, applyInput } = window.ExamQ;
    const $ = (id) => document.getElementById(id);
    const DIFF = { kolay: 'Kolay', orta: 'Orta', zor: 'Zor' };
    let page = 1, items = [], timer = null, editing = null, q = null, armedId = null, armTimer = null;

    const params = () => new URLSearchParams({ q: $('f-q').value, subject: $('f-subject').value, grade: $('f-grade').value, difficulty: $('f-diff').value, type: $('f-type').value, scope: $('f-scope').value });
    async function load(reset) {
        if (reset) { page = 1; items = []; }
        const res = await fetch(URLS.search + '?' + params() + '&page=' + page, { headers: { Accept: 'application/json' } });
        if (!res.ok) { $('bk-total').textContent = 'Liste alınamadı (' + res.status + ')'; return; }
        const j = await res.json();
        items = items.concat(j.data);
        $('bk-more').style.display = j.has_more ? 'block' : 'none';
        $('bk-total').textContent = j.total + ' soru';
        render();
    }
    function optsHtml(i) {
        if (i.type === 'multiple_choice') return '<div class="bk-opts">' + i.options.map((o, k) => '<div class="' + (i.answer === LETTERS[k] ? 'ok' : '') + '">' + LETTERS[k] + ') ' + esc(o) + '</div>').join('') + '</div>';
        if (i.type === 'true_false') return '<div class="bk-opts"><div class="ok">Cevap: ' + (i.answer === 'Y' ? 'Yanlış' : 'Doğru') + '</div></div>';
        if (i.type === 'matching') return '<div class="bk-opts">' + i.options.map((o, k) => '<div>' + (k + 1) + '. ' + esc(o.l) + ' ↔ ' + esc(o.r) + '</div>').join('') + '</div>';
        if (i.answer) return '<div class="bk-opts"><div class="ok">Cevap: ' + esc(i.answer) + '</div></div>';
        return '';
    }
    function render() {
        $('bk-list').innerHTML = items.length ? items.map((i) => {
            const canEdit = i.mine || @json($isAdmin);
            return '<div class="bk-card" data-id="' + i.id + '"><div class="bk-tags"><span class="bk-tag">' + TYPE_NAME[i.type] + '</span><span class="bk-tag ' + i.difficulty + '">' + DIFF[i.difficulty] + '</span>'
                + (i.is_shared ? '<span class="bk-tag sh">Paylaşılan</span>' : '') + '</div>'
                + '<div class="bk-body">' + esc(i.body) + '</div>' + optsHtml(i)
                + '<div class="bk-meta">' + [i.subject, i.grade_level ? i.grade_level + '. sınıf' : '', i.unit, i.owner && !i.mine ? i.owner : ''].filter(Boolean).map(esc).join(' · ') + '</div>'
                + (canEdit ? '<div class="bk-act"><button type="button" data-act="edit">Düzenle</button><button type="button" class="del" data-act="del">' + (armedId === i.id ? 'Emin misiniz?' : 'Sil') + '</button></div>' : '')
                + '</div>';
        }).join('') : '<div class="bk-empty">Bu filtrelere uyan soru yok. "Yeni Soru" ile havuza soru ekleyebilirsiniz.</div>';
    }

    /* ---- modal ---- */
    function renderForm() { $('m-form').innerHTML = formHtml(q, { uid: 'bank' }); }
    function openModal(item) {
        editing = item ? item.id : null;
        q = item ? { type: item.type, body: item.body, options: JSON.parse(JSON.stringify(item.options)), answer: item.answer, points: 0, answer_lines: 0 } : newQuestion('multiple_choice');
        $('bk-title').textContent = item ? 'Soruyu Düzenle' : 'Yeni Soru';
        $('m-type').value = q.type; $('m-type').disabled = !!item;
        $('m-diff').value = item ? item.difficulty : 'orta';
        $('m-subject').value = item ? item.subject : (localStorage.getItem('bk_subject') || '');
        $('m-grade').value = item ? (item.grade_level || '') : (localStorage.getItem('bk_grade') || '');
        $('m-unit').value = item ? item.unit : ''; $('m-tags').value = item ? item.tags : '';
        $('m-shared').checked = item ? item.is_shared : false;
        $('bk-err').style.display = 'none';
        renderForm(); $('bk-modal').classList.add('open');
    }
    const closeModal = () => $('bk-modal').classList.remove('open');
    async function save() {
        if (!q.body.trim()) { showErr('Soru metni boş olamaz.'); return; }
        const body = { type: q.type, body: q.body, options: q.options, answer: answerOf(q), difficulty: $('m-diff').value, subject: $('m-subject').value, grade_level: $('m-grade').value || null, unit: $('m-unit').value, tags: $('m-tags').value, is_shared: $('m-shared').checked };
        try { localStorage.setItem('bk_subject', body.subject); localStorage.setItem('bk_grade', body.grade_level || ''); } catch (_) {}
        const res = await fetch(editing ? URLS.item + '/' + editing : URLS.store, { method: editing ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });
        if (!res.ok) { let m = 'Kaydedilemedi (' + res.status + ')'; try { const j = await res.json(); m = j.errors ? Object.values(j.errors)[0][0] : (j.message || m); } catch (_) {} showErr(m); return; }
        closeModal(); load(true);
    }
    function showErr(m) { const e = $('bk-err'); e.textContent = m; e.style.display = 'block'; }

    $('bk-new').addEventListener('click', () => openModal(null));
    $('bk-cancel').addEventListener('click', closeModal);
    $('bk-save').addEventListener('click', save);
    $('bk-modal').addEventListener('mousedown', (e) => { if (e.target === $('bk-modal')) closeModal(); });
    $('m-type').addEventListener('change', (e) => { q = newQuestion(e.target.value); renderForm(); });
    $('m-form').addEventListener('click', (e) => { if (applyClick(q, e.target)) renderForm(); });
    $('m-form').addEventListener('input', (e) => { applyInput(q, e.target, $('m-form')); });
    $('bk-more').addEventListener('click', () => { page++; load(false); });
    ['f-subject', 'f-grade', 'f-diff', 'f-type', 'f-scope'].forEach((id) => $(id).addEventListener('change', () => load(true)));
    $('f-q').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(true), 350); });
    $('bk-list').addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-act]'); if (!btn) return;
        const id = Number(btn.closest('.bk-card').dataset.id), item = items.find((x) => x.id === id);
        if (btn.dataset.act === 'edit') return openModal(item);
        if (armedId !== id) { armedId = id; clearTimeout(armTimer); armTimer = setTimeout(() => { armedId = null; render(); }, 3500); return render(); }
        armedId = null;
        const res = await fetch(URLS.item + '/' + id, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });
        if (res.ok) load(true);
    });
    $('bk-export').addEventListener('click', (e) => { e.preventDefault(); window.location.href = @json(route('exams.bank.export')) + '?' + params(); });
    $('bk-import').addEventListener('click', () => { $('bk-file').value = ''; $('bk-file').click(); });
    $('bk-file').addEventListener('change', async () => {
        const f = $('bk-file').files[0]; if (!f) return;
        const box = $('bk-result'); box.className = 'bk-result'; box.style.display = 'block'; box.textContent = 'Yükleniyor…';
        const fd = new FormData(); fd.append('file', f);
        try {
            const res = await fetch(@json(route('exams.bank.import')), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: fd });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) { box.className = 'bk-result err'; box.textContent = j.message || (j.errors ? Object.values(j.errors)[0][0] : 'Yükleme başarısız (' + res.status + ')'); return; }
            box.className = 'bk-result' + (j.skipped ? ' warn' : '');
            box.innerHTML = '✔ ' + j.imported + ' soru havuza eklendi' + (j.skipped ? ', ' + j.skipped + ' satır atlandı:' : '.')
                + (j.errors.length ? '<ul>' + j.errors.map((m) => '<li>' + esc(m) + '</li>').join('') + '</ul>' : '');
            load(true);
        } catch (_) { box.className = 'bk-result err'; box.textContent = 'Yükleme sırasında bağlantı hatası oluştu.'; }
    });
    load(true);
})();
</script>
@endpush
