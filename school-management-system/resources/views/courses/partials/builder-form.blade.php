@php
    $isEdit = isset($course);
    $initialPayload = old('lesson_payload');
    if ($initialPayload === null) {
        $initialPayload = $isEdit ? json_encode($course->lesson_payload ?? ['slides' => []], JSON_UNESCAPED_UNICODE) : json_encode(['slides' => []], JSON_UNESCAPED_UNICODE);
    }
    $selectedClass = old('school_class_id', $isEdit ? $course->school_class_id : '__ALL__');
    $defaultTeacherId = old('teacher_id', $isEdit ? $course->teacher_id : ($teachers->first()->id ?? null));
    $defaultWeeklyHours = old('weekly_hours', $isEdit ? $course->weekly_hours : 2);
    $defaultCode = old('code', $isEdit ? $course->code : '');
@endphp

<div class="lesson-builder">
    <div class="lesson-builder-top">
        <div style="display:grid;grid-template-columns:1fr 300px;gap:10px;width:100%">
            <input type="text" id="lesson_title" placeholder="Ders basligi" value="{{ old('name', $isEdit ? $course->name : '') }}">
            <select id="top_class_select">
                <option value="__ALL__" @selected($selectedClass === '__ALL__')>Tum Siniflar</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected((string)$selectedClass === (string)$class->id)>
                        {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="actions">
            <button class="btn" type="button" id="builder_preview_btn">Onizleme</button>
            <button class="btn" type="submit">{{ $isEdit ? 'Degisiklikleri Kaydet' : 'Dersi Kaydet' }}</button>
            <button class="btn btn-danger" type="button" id="remove_slide_btn">Slide Sil</button>
        </div>
    </div>

    <div class="lesson-builder-grid">
        <aside class="builder-left">
            <h4>Ders Sayfalari</h4>
            <button class="btn" type="button" id="add_slide_btn">+ Sayfa Ekle</button>
            <div id="slide_list"></div>
        </aside>

        <section class="builder-center">
            <div class="builder-tabs">
                <button type="button" class="tab-btn" data-tab="text">Yazi Ekle</button>
                <button type="button" class="tab-btn" data-tab="media">Gorsel/Video</button>
                <button type="button" class="tab-btn active" data-tab="code">Kod Ekle</button>
                <button type="button" class="tab-btn" data-tab="question">Soru Ekle</button>
            </div>

            <div class="builder-panel" data-panel="text" style="display:none">
                <label>Slide Basligi</label>
                <input type="text" id="slide_title">
                <label>Sayfa XP</label>
                <input type="number" id="slide_xp" min="0" max="500" value="0">
                <label>Konu Anlatimi / Aciklama</label>
                <textarea id="slide_content" rows="6"></textarea>
                <label>Ogrenci Yonlendirme Notu</label>
                <textarea id="slide_instructions" rows="3" placeholder="Bu sayfada ogrenci ne yapmali?"></textarea>
            </div>

            <div class="builder-panel" data-panel="media" style="display:none">
                <label>Gorsel URL</label>
                <input type="text" id="slide_image_url" placeholder="https://...">
                <label>Video URL</label>
                <input type="text" id="slide_video_url" placeholder="https://youtube.com/...">
                <label>Ek Kaynak URL</label>
                <input type="text" id="slide_file_url" placeholder="https://.../pdf">
            </div>

            <div class="builder-panel" data-panel="code">
                <label>HTML/CSS/JS Kodu</label>
                <textarea id="slide_code" rows="9" placeholder="<div>...</div> <style>...</style> <script>...</script>"></textarea>
            </div>

            <div class="builder-panel" data-panel="question" style="display:none">
                <label>Icerik Tipi</label>
                <select id="slide_kind">
                    <option value="topic">Konu Anlatimi</option>
                    <option value="question">Soru Sayfasi</option>
                    <option value="task">Gorev Sayfasi</option>
                    <option value="summary">Ozet Sayfasi</option>
                </select>
                <label>Etkilesim Tipi</label>
                <select id="slide_interaction_type">
                    <option value="none">Yok</option>
                    <option value="multiple_choice">Coktan Secmeli</option>
                    <option value="true_false">Dogru Yanlis</option>
                    <option value="matching">Eslestirme</option>
                    <option value="drag_drop">Surukle Birak</option>
                    <option value="short_answer">Kisa Cevap</option>
                    <option value="checklist">Kontrol Listesi</option>
                </select>
                <label>Soru Metni</label>
                <textarea id="slide_question_prompt" rows="2"></textarea>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
                    <div>
                        <label>Puan</label>
                        <select id="slide_points">
                            @for($p=5;$p<=20;$p++)
                                <option value="{{ $p }}">{{ $p }} Puan</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label>Sure</label>
                        <select id="slide_time_limit">
                            @for($s=10;$s<=60;$s+=5)
                                <option value="{{ $s }}">{{ $s }} sn</option>
                            @endfor
                        </select>
                    </div>
                    <div style="display:flex;align-items:end;padding-bottom:8px">
                        <label style="display:flex;align-items:center;gap:6px;margin:0">
                            <input type="checkbox" id="slide_double_points" style="width:auto;margin:0">
                            2 Kat Puan
                        </label>
                    </div>
                </div>
                <div id="question_editor"></div>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:10px">
                <div id="current_slide_xp_badge" class="badge">Slide XP: 0</div>
            </div>
        </section>

        <aside class="builder-right">
            <h4>Ders Ayarlari</h4>
            <label>Global Tema CSS</label>
            <textarea id="global_theme_css" rows="7" placeholder=".slide-theme{background:#0f172a;color:#f8fafc} .slide-theme h3{color:#f8fafc}"></textarea>
        </aside>
    </div>
</div>

<input type="hidden" id="lesson_payload" name="lesson_payload" value='{{ $initialPayload }}'>
<input type="hidden" id="course_name_hidden" name="name" value="{{ old('name', $isEdit ? $course->name : '') }}">
<input type="hidden" id="course_code_hidden" name="code" value="{{ $defaultCode }}">
<input type="hidden" id="teacher_id_hidden" name="teacher_id" value="{{ $defaultTeacherId }}">
<input type="hidden" id="school_class_id_hidden" name="school_class_id" value="{{ old('school_class_id', $isEdit ? $course->school_class_id : '') }}">
<input type="hidden" id="weekly_hours_hidden" name="weekly_hours" value="{{ $defaultWeeklyHours }}">

<div id="builder-preview-modal" class="modal">
    <div class="modal-card" style="width:min(96vw,1500px);max-width:96vw;max-height:92vh;display:flex;flex-direction:column">
        <div class="modal-head">
            <strong>Ders Onizleme</strong>
            <button class="btn" type="button" data-close-modal>Kapat</button>
        </div>
        <div id="preview_slide_stage" class="card" style="min-height:70vh;max-height:74vh;overflow:hidden;margin:0 0 10px"></div>
        <div class="actions" style="justify-content:space-between">
            <button class="btn" type="button" id="preview_prev_btn">Geri</button>
            <span id="preview_slide_counter" class="badge">1 / 1</span>
            <button class="btn" type="button" id="preview_next_btn">Ileri</button>
        </div>
    </div>
</div>

@if($errors->any())
    <div style="color:#b91c1c;margin:8px 0">{{ $errors->first() }}</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const builderForm = document.querySelector('.lesson-builder')?.closest('form');
    const payloadInput = document.getElementById('lesson_payload');
    const list = document.getElementById('slide_list');
    const addBtn = document.getElementById('add_slide_btn');
    const removeBtn = document.getElementById('remove_slide_btn');
    const previewBtn = document.getElementById('builder_preview_btn');
    const previewModal = document.getElementById('builder-preview-modal');
    const previewStage = document.getElementById('preview_slide_stage');
    const previewCounter = document.getElementById('preview_slide_counter');
    const previewPrev = document.getElementById('preview_prev_btn');
    const previewNext = document.getElementById('preview_next_btn');

    const lessonTitle = document.getElementById('lesson_title');
    const topClassSelect = document.getElementById('top_class_select');
    const globalThemeCss = document.getElementById('global_theme_css');

    const hName = document.getElementById('course_name_hidden');
    const hCode = document.getElementById('course_code_hidden');
    const hTeacher = document.getElementById('teacher_id_hidden');
    const hClass = document.getElementById('school_class_id_hidden');
    const hWeekly = document.getElementById('weekly_hours_hidden');

    const fields = {
        title: document.getElementById('slide_title'),
        xp: document.getElementById('slide_xp'),
        content: document.getElementById('slide_content'),
        instructions: document.getElementById('slide_instructions'),
        image_url: document.getElementById('slide_image_url'),
        video_url: document.getElementById('slide_video_url'),
        file_url: document.getElementById('slide_file_url'),
        code: document.getElementById('slide_code'),
        kind: document.getElementById('slide_kind'),
        interaction_type: document.getElementById('slide_interaction_type'),
        question_prompt: document.getElementById('slide_question_prompt'),
        points: document.getElementById('slide_points'),
        time_limit: document.getElementById('slide_time_limit'),
        double_points: document.getElementById('slide_double_points'),
    };
    const questionEditor = document.getElementById('question_editor');
    const currentSlideXpBadge = document.getElementById('current_slide_xp_badge');

    let state;
    try { state = JSON.parse(payloadInput.value || '{"slides":[]}'); } catch (e) { state = {slides: []}; }
    const draftKey = 'lesson_builder_draft_{{ $isEdit ? 'edit_' . $course->id : 'create' }}';
    const shouldPersistDraft = {{ $isEdit ? 'true' : 'false' }};
    if ((!state.slides || state.slides.length === 0) && shouldPersistDraft) {
        try {
            const draft = localStorage.getItem(draftKey);
            if (draft) {
                const parsed = JSON.parse(draft);
                if (parsed && Array.isArray(parsed.slides) && parsed.slides.length) state = parsed;
            }
        } catch (_) {}
    }
    if (!Array.isArray(state.slides)) state.slides = [];
    let active = 0;
    let previewIndex = 0;

    function ensureSlide() {
        if (state.slides.length === 0) {
            state.slides.push({title: 'Basliksiz Slide', xp: 0, kind: 'topic', interaction_type: 'none', points: 5, time_limit: 10, double_points: false, question: {options: [], pairs: [], items: []}});
        }
    }
    function escapeHtml(v) {
        return (v || '').replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; });
    }
    function readQuestionFromUI(type) {
        if (!questionEditor) return { options: [] };
        if (type === 'true_false') {
            const val = questionEditor.querySelector('input[name="q_tf_correct"]:checked')?.value || 'true';
            return { options: [{ text: 'Dogru', correct: val === 'true' }, { text: 'Yanlis', correct: val === 'false' }] };
        }
        if (type === 'multiple_choice') {
            const rows = Array.from(questionEditor.querySelectorAll('[data-q-row="mc"]'));
            return {
                options: rows
                    .map((row, i) => ({
                        text: row.querySelector('input[data-role="text"]')?.value?.trim() || '',
                        correct: row.querySelector('input[name="q_mc_correct"]')?.checked || false,
                        index: i,
                    }))
                    .filter((r) => r.text !== ''),
            };
        }
        if (type === 'matching') {
            return {
                pairs: Array.from(questionEditor.querySelectorAll('[data-q-row="match"]')).map((row) => ({
                    left: row.querySelector('input[data-role="left"]')?.value?.trim() || '',
                    right: row.querySelector('input[data-role="right"]')?.value?.trim() || '',
                })).filter((p) => p.left && p.right),
            };
        }
        if (type === 'drag_drop') {
            return {
                items: Array.from(questionEditor.querySelectorAll('[data-q-row="drag"]')).map((row) => ({
                    text: row.querySelector('input[data-role="text"]')?.value?.trim() || '',
                    target: row.querySelector('input[data-role="target"]')?.value?.trim() || '',
                })).filter((x) => x.text && x.target),
            };
        }
        if (type === 'short_answer') {
            return { answer: questionEditor.querySelector('#q_short_answer')?.value?.trim() || '' };
        }
        if (type === 'checklist') {
            return {
                items: Array.from(questionEditor.querySelectorAll('[data-q-row="check"]')).map((row) => ({
                    text: row.querySelector('input[data-role="text"]')?.value?.trim() || '',
                    correct: row.querySelector('input[data-role="correct"]')?.checked || false,
                })).filter((x) => x.text),
            };
        }
        return { options: [] };
    }
    function renderQuestionEditor(type, q) {
        if (!questionEditor) return;
        const question = q || {};
        const box = (inner) => `<div style="border:1px solid #e2e8f0;border-radius:8px;padding:8px;margin-top:8px">${inner}</div>`;
        if (type === 'multiple_choice') {
            const options = (question.options && question.options.length ? question.options : [{ text: '' }, { text: '' }, { text: '' }, { text: '' }]).slice(0, 6);
            questionEditor.innerHTML = box(options.map((opt, i) => `
                <div data-q-row="mc" style="display:grid;grid-template-columns:26px 1fr;gap:8px;align-items:center;margin-bottom:6px">
                    <input type="radio" name="q_mc_correct" ${opt.correct ? 'checked' : ''} style="width:18px;height:18px">
                    <input data-role="text" type="text" placeholder="Sik ${i + 1}" value="${escapeHtml(opt.text || '')}">
                </div>
            `).join(''));
            return;
        }
        if (type === 'true_false') {
            const correctTrue = (question.options || []).find((o) => o.text === 'Dogru')?.correct ?? true;
            questionEditor.innerHTML = box(`
                <div style="display:grid;grid-template-columns:26px 1fr;gap:8px;align-items:center;margin-bottom:6px">
                    <input type="radio" name="q_tf_correct" value="true" ${correctTrue ? 'checked' : ''} style="width:18px;height:18px">
                    <div style="padding:8px;border:1px solid #e2e8f0;border-radius:8px">Dogru</div>
                </div>
                <div style="display:grid;grid-template-columns:26px 1fr;gap:8px;align-items:center">
                    <input type="radio" name="q_tf_correct" value="false" ${!correctTrue ? 'checked' : ''} style="width:18px;height:18px">
                    <div style="padding:8px;border:1px solid #e2e8f0;border-radius:8px">Yanlis</div>
                </div>
            `);
            return;
        }
        if (type === 'matching') {
            const pairs = (question.pairs && question.pairs.length ? question.pairs : [{ left: '', right: '' }, { left: '', right: '' }, { left: '', right: '' }]).slice(0, 6);
            questionEditor.innerHTML = box(pairs.map((p, i) => `
                <div data-q-row="match" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:6px">
                    <input data-role="left" type="text" placeholder="Sol ${i + 1}" value="${escapeHtml(p.left || '')}">
                    <input data-role="right" type="text" placeholder="Sag ${i + 1}" value="${escapeHtml(p.right || '')}">
                </div>
            `).join(''));
            return;
        }
        if (type === 'drag_drop') {
            const items = (question.items && question.items.length ? question.items : [{ text: '', target: '' }, { text: '', target: '' }, { text: '', target: '' }]).slice(0, 6);
            questionEditor.innerHTML = box(items.map((item, i) => `
                <div data-q-row="drag" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:6px">
                    <input data-role="text" type="text" placeholder="Parca ${i + 1}" value="${escapeHtml(item.text || '')}">
                    <input data-role="target" type="text" placeholder="Hedef ${i + 1}" value="${escapeHtml(item.target || '')}">
                </div>
            `).join(''));
            return;
        }
        if (type === 'short_answer') {
            questionEditor.innerHTML = box(`<input id="q_short_answer" type="text" placeholder="Dogru cevap" value="${escapeHtml(question.answer || '')}">`);
            return;
        }
        if (type === 'checklist') {
            const items = (question.items && question.items.length ? question.items : [{ text: '' }, { text: '' }, { text: '' }]).slice(0, 8);
            questionEditor.innerHTML = box(items.map((item, i) => `
                <div data-q-row="check" style="display:grid;grid-template-columns:26px 1fr;gap:8px;align-items:center;margin-bottom:6px">
                    <input data-role="correct" type="checkbox" ${item.correct ? 'checked' : ''} style="width:18px;height:18px">
                    <input data-role="text" type="text" placeholder="Madde ${i + 1}" value="${escapeHtml(item.text || '')}">
                </div>
            `).join(''));
            return;
        }
        questionEditor.innerHTML = box('<div style="color:#64748b">Bu soru tipinde ek ayar yok.</div>');
    }
    const slugify = (value) => (value || '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 20);

    const ensureCourseCode = () => {
        if (hCode.value && hCode.value.trim() !== '') return;
        const base = slugify(lessonTitle.value) || 'ders';
        const stamp = String(Date.now()).slice(-5);
        hCode.value = (base + '-' + stamp).toUpperCase();
    };

    function syncHiddenInputs() {
        hName.value = lessonTitle.value || '';
        if (!hTeacher.value) hTeacher.value = '{{ $defaultTeacherId }}';
        if (!hWeekly.value) hWeekly.value = '{{ $defaultWeeklyHours }}';
        ensureCourseCode();
        if (topClassSelect.value === '__ALL__') {
            const first = Array.from(topClassSelect.options).find(o => o.value !== '__ALL__');
            hClass.value = first ? first.value : '';
            state.target_scope = 'all_classes';
        } else {
            hClass.value = topClassSelect.value || '';
            state.target_scope = 'single_class';
        }
    }
    function saveCurrent() {
        ensureSlide();
        const s = state.slides[active];
        s.title = fields.title.value || 'Basliksiz Slide';
        s.xp = Math.max(0, Math.min(500, parseInt(fields.xp.value || '0', 10) || 0));
        s.content = fields.content.value || '';
        s.instructions = fields.instructions.value || '';
        s.image_url = fields.image_url.value || '';
        s.video_url = fields.video_url.value || '';
        s.file_url = fields.file_url.value || '';
        s.code = fields.code.value || '';
        s.kind = fields.kind.value;
        s.interaction_type = fields.interaction_type.value;
        s.question_prompt = fields.question_prompt.value || '';
        s.points = parseInt(fields.points.value || '5', 10);
        s.time_limit = parseInt(fields.time_limit.value || '10', 10);
        s.double_points = !!fields.double_points.checked;
        s.question = readQuestionFromUI(s.interaction_type);
        if (currentSlideXpBadge) currentSlideXpBadge.textContent = 'Slide XP: ' + s.xp;
        state.lesson_title = lessonTitle.value || '';
        state.global_theme_css = globalThemeCss.value || '';
        syncHiddenInputs();
        payloadInput.value = JSON.stringify(state);
        if (shouldPersistDraft) {
            try {
                localStorage.setItem(draftKey, payloadInput.value);
            } catch (_) {}
        }
    }
    function loadCurrent() {
        ensureSlide();
        const s = state.slides[active];
        fields.title.value = s.title || '';
        fields.xp.value = Number.isFinite(Number(s.xp)) ? Number(s.xp) : 0;
        fields.content.value = s.content || '';
        fields.instructions.value = s.instructions || '';
        fields.image_url.value = s.image_url || '';
        fields.video_url.value = s.video_url || '';
        fields.file_url.value = s.file_url || '';
        fields.code.value = s.code || '';
        fields.kind.value = s.kind || 'topic';
        fields.interaction_type.value = s.interaction_type || 'none';
        fields.question_prompt.value = s.question_prompt || '';
        fields.points.value = s.points || 5;
        fields.time_limit.value = s.time_limit || 10;
        fields.double_points.checked = !!s.double_points;
        renderQuestionEditor(fields.interaction_type.value, s.question || {});
        globalThemeCss.value = state.global_theme_css || '';
        if (currentSlideXpBadge) currentSlideXpBadge.textContent = 'Slide XP: ' + Number(s.xp || 0);
    }
    function renderList() {
        list.innerHTML = '';
        state.slides.forEach((s, i) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'slide-list-item' + (i === active ? ' active' : '');
            b.textContent = (i + 1) + '. ' + (s.title || 'Basliksiz Slide');
            b.addEventListener('click', () => { saveCurrent(); active = i; loadCurrent(); renderList(); });
            list.appendChild(b);
        });
    }
    function renderPreviewSlide() {
        ensureSlide();
        previewIndex = Math.max(0, Math.min(previewIndex, state.slides.length - 1));
        const s = state.slides[previewIndex] || {};
        let html = '<h3>' + escapeHtml(s.title || 'Basliksiz Slide') + ' <span style="font-size:13px;color:#334155">| XP: ' + Number(s.xp || 0) + '</span></h3>';
        const themeCss = state.global_theme_css || '';
        if (themeCss) {
            html = '<style>' + themeCss + '</style><div class="slide-theme">' + html;
        }
        if (s.instructions) html += '<p><b>Yonlendirme:</b> ' + escapeHtml(s.instructions) + '</p>';
        if (s.content) html += '<p>' + escapeHtml(s.content) + '</p>';
        if (s.image_url) html += '<img src="' + s.image_url + '" style="max-width:100%;border:1px solid #e5e7eb;border-radius:8px">';
        if (s.video_url) html += '<p><a href="' + s.video_url + '" target="_blank">Video baglantisi</a></p>';
        if (s.question_prompt) html += '<div class="card"><b>Soru:</b> ' + escapeHtml(s.question_prompt) + '</div>';
        if (s.interaction_type && s.interaction_type !== 'none') {
            const p = Number(s.points || 5) * (s.double_points ? 2 : 1);
            html += '<p><b>Puan:</b> ' + p + ' | <b>Sure:</b> ' + Number(s.time_limit || 10) + ' sn</p>';
        }
        if (s.code) html += '<iframe id="preview_code_iframe" style="width:100%;height:100%;min-height:58vh;border:1px solid #d1d5db;border-radius:8px;margin-top:6px" srcdoc="' + escapeHtml(s.code) + '"></iframe>';
        if (themeCss) {
            html += '</div>';
        }
        previewStage.innerHTML = '<div id="preview-slide-fit" style="width:100%;height:100%;min-height:66vh;overflow:hidden;display:flex;align-items:flex-start;justify-content:center">' + html + '</div>';
        fitPreviewContent();
        previewCounter.textContent = (previewIndex + 1) + ' / ' + state.slides.length;
        previewPrev.disabled = previewIndex <= 0;
        previewNext.disabled = previewIndex >= state.slides.length - 1;
    }
    function fitPreviewContent() {
        const holder = document.getElementById('preview-slide-fit');
        if (!holder) return;
        const iframe = document.getElementById('preview_code_iframe');
        if (iframe) {
            iframe.style.height = Math.max(520, holder.clientHeight - 10) + 'px';
            return;
        }
        const first = holder.firstElementChild;
        if (!first) return;
        first.style.transform = '';
        first.style.transformOrigin = 'top center';
        const wScale = holder.clientWidth / Math.max(first.scrollWidth, 1);
        const hScale = holder.clientHeight / Math.max(first.scrollHeight, 1);
        const scale = Math.min(1, wScale, hScale);
        first.style.transform = 'scale(' + scale + ')';
    }

    addBtn.addEventListener('click', () => {
        saveCurrent();
        state.slides.push({title: 'Yeni Slide', xp: 0, kind: 'topic', interaction_type: 'none', points: 5, time_limit: 10, double_points: false, question: {options: [], pairs: [], items: []}});
        active = state.slides.length - 1;
        loadCurrent(); renderList(); saveCurrent();
    });
    removeBtn.addEventListener('click', () => {
        if (state.slides.length <= 1) return;
        state.slides.splice(active, 1);
        active = Math.max(0, active - 1);
        loadCurrent(); renderList(); saveCurrent();
    });

    previewBtn.addEventListener('click', () => {
        saveCurrent();
        previewIndex = 0;
        renderPreviewSlide();
        previewModal.classList.add('open');
    });
    previewPrev.addEventListener('click', () => { previewIndex--; renderPreviewSlide(); });
    previewNext.addEventListener('click', () => { previewIndex++; renderPreviewSlide(); });
    window.addEventListener('resize', fitPreviewContent);

    Object.values(fields).forEach(el => el.addEventListener('input', () => { saveCurrent(); renderList(); }));
    lessonTitle.addEventListener('input', () => { saveCurrent(); });
    topClassSelect.addEventListener('change', saveCurrent);
    globalThemeCss.addEventListener('input', saveCurrent);
    fields.interaction_type.addEventListener('change', () => {
        renderQuestionEditor(fields.interaction_type.value, {});
        saveCurrent();
    });
    questionEditor.addEventListener('input', saveCurrent);
    questionEditor.addEventListener('change', saveCurrent);

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const tab = btn.dataset.tab;
            document.querySelectorAll('.builder-panel').forEach(p => p.style.display = (p.dataset.panel === tab ? 'block' : 'none'));
        });
    });

    ensureSlide();
    loadCurrent();
    renderList();
    saveCurrent();

    if (!shouldPersistDraft) {
        try { localStorage.removeItem(draftKey); } catch (_) {}
    }

    if (builderForm) {
        builderForm.addEventListener('submit', () => {
            saveCurrent();
            // Basarili kayittan sonra taslak temizlensin.
            if (shouldPersistDraft) {
                setTimeout(() => {
                    try { localStorage.removeItem(draftKey); } catch (_) {}
                }, 300);
            }
        });
    }
});
</script>
