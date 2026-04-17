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
    const lessonCategory = document.getElementById('lesson_category');
    const lessonDifficulty = document.getElementById('lesson_difficulty');
    const lessonDescription = document.getElementById('lesson_description');
    const coverImageFile = document.getElementById('cover_image_file');
    const coverImagePreview = document.getElementById('cover_image_preview');
    const coverImageRemove = document.getElementById('cover_image_remove');
    const coverCropModal = document.getElementById('cover-crop-modal');
    const coverCropImage = document.getElementById('cover-crop-image');
    const coverCropViewport = document.getElementById('cover-crop-viewport');
    const coverCropSelection = document.getElementById('cover-crop-selection');
    const coverCropZoom = document.getElementById('cover-crop-zoom');
    const coverCropApply = document.getElementById('cover-crop-apply');
    const coverCropCancel = document.getElementById('cover-crop-cancel');

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
    const curriculum = {
        title: document.getElementById('curriculum_title'),
        lessonNumber: document.getElementById('curriculum_lesson_number'),
        topic: document.getElementById('curriculum_topic'),
        outcomes: document.getElementById('curriculum_outcomes'),
        activities: document.getElementById('curriculum_activities'),
        progress: document.getElementById('curriculum_progress'),
    };
    const questionEditor = document.getElementById('question_editor');
    const currentSlideXpBadge = document.getElementById('current_slide_xp_badge');

    let state;
    try { state = JSON.parse(payloadInput.value || '{"slides":[]}'); } catch (e) { state = {slides: []}; }
    const draftKey = 'lesson_builder_draft_0';
    const shouldPersistDraft = 0;
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
    const coverCropState = {
        objectUrl: '',
        imgNaturalW: 0,
        imgNaturalH: 0,
        zoom: 1,
        imgX: 0,
        imgY: 0,
        imgW: 0,
        imgH: 0,
        selection: { x: 0, y: 0, w: 0, h: 0 },
        dragMode: '',
        dragStartX: 0,
        dragStartY: 0,
        startSelection: { x: 0, y: 0, w: 0, h: 0 },
    };

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
            const shapes = ['â–²','â—†','â—','â– ','â¬Ÿ','â¬¢'];
            const colors = ['sq-red','sq-blue','sq-yellow','sq-green','sq-blue','sq-red'];
            questionEditor.innerHTML = box(`<div class="sq-answers">${options.map((opt, i) => `
                <div data-q-row="mc" class="sq-answer-card">
                    <span class="sq-shape ${colors[i]}">${shapes[i]}</span>
                    <input data-role="text" type="text" placeholder="SeÃ§enek ${i + 1}" value="${escapeHtml(opt.text || '')}">
                    <input type="radio" name="q_mc_correct" ${opt.correct ? 'checked' : ''} style="width:18px;height:18px">
                </div>
            `).join('')}</div>`);
            return;
        }
        if (type === 'true_false') {
            const correctTrue = (question.options || []).find((o) => o.text === 'Dogru')?.correct ?? true;
            questionEditor.innerHTML = box(`
                <div class="sq-answers">
                    <label class="sq-answer-card" style="cursor:pointer">
                        <span class="sq-shape sq-blue">â—†</span>
                        <span>DoÄŸru</span>
                        <input type="radio" name="q_tf_correct" value="true" ${correctTrue ? 'checked' : ''} style="width:18px;height:18px">
                    </label>
                    <label class="sq-answer-card" style="cursor:pointer">
                        <span class="sq-shape sq-red">â–²</span>
                        <span>YanlÄ±ÅŸ</span>
                        <input type="radio" name="q_tf_correct" value="false" ${!correctTrue ? 'checked' : ''} style="width:18px;height:18px">
                    </label>
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
        if (!hTeacher.value) hTeacher.value = '0';
        if (!hWeekly.value) hWeekly.value = '0';
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
        state.category = lessonCategory.value || 'Kodlama';
        state.difficulty = lessonDifficulty.value || 'Kolay';
        state.lesson_description = lessonDescription?.value || '';
        state.cover_image = state.cover_image || '';
        state.global_theme_css = globalThemeCss.value || '';
        state.curriculum = {
            title: curriculum.title.value || '',
            lesson_number: Math.max(1, parseInt(curriculum.lessonNumber.value || '1', 10) || 1),
            konu: curriculum.topic.value || '',
            kazanimlar: (curriculum.outcomes.value || '').split(/\r?\n/).map(v => v.trim()).filter(Boolean),
            etkinlikler: (curriculum.activities.value || '').split(/\r?\n/).map(v => v.trim()).filter(Boolean),
            progress: Math.max(0, Math.min(100, parseInt(curriculum.progress.value || '0', 10) || 0)),
        };
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
        lessonCategory.value = state.category || 'Kodlama';
        lessonDifficulty.value = state.difficulty || 'Kolay';
        if (lessonDescription) lessonDescription.value = state.lesson_description || '';
        const url = state.cover_image || '';
        if (coverImagePreview) {
            coverImagePreview.src = url;
            coverImagePreview.style.display = url ? 'block' : 'none';
        }
        if (coverImageRemove) {
            coverImageRemove.style.display = url ? 'inline-flex' : 'none';
        }
        globalThemeCss.value = state.global_theme_css || '';
        const c = state.curriculum || {};
        curriculum.title.value = c.title || '';
        curriculum.lessonNumber.value = Number.isFinite(Number(c.lesson_number)) ? Number(c.lesson_number) : 1;
        curriculum.topic.value = c.konu || '';
        const outcomes = c.kazanimlar ?? c['kazanımlar'] ?? c['kazanÄ±mlar'] ?? [];
        curriculum.outcomes.value = Array.isArray(outcomes) ? outcomes.join('\n') : '';
        curriculum.activities.value = Array.isArray(c.etkinlikler) ? c.etkinlikler.join('\n') : '';
        curriculum.progress.value = Number.isFinite(Number(c.progress)) ? Number(c.progress) : 0;
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
        if (s.code) {
            const mergedCode = (themeCss ? ('<style>' + themeCss + '</style>') : '') + String(s.code || '');
            html += '<iframe id="preview_code_iframe" style="width:100%;height:100%;min-height:58vh;border:1px solid #d1d5db;border-radius:8px;margin-top:6px" srcdoc="' + escapeHtml(mergedCode) + '"></iframe>';
        }
        if (themeCss) {
            html += '</div>';
        }
        previewStage.innerHTML = '<div id="preview-slide-fit" style="width:100%;height:100%;min-height:66vh;overflow:hidden;display:flex;align-items:flex-start;justify-content:center">' + html + '</div>';
        fitPreviewContent();
        previewCounter.textContent = (previewIndex + 1) + ' / ' + state.slides.length;
        previewPrev.disabled = previewIndex <= 0;
        previewNext.disabled = previewIndex >= state.slides.length - 1;
    }
    function fitIframeInHolder(iframe, holder) {
        if (!iframe || !holder) return;
        iframe.style.width = '100%';
        iframe.style.height = Math.max(520, holder.clientHeight - 10) + 'px';

        const applyScale = () => {
            try {
                const doc = iframe.contentDocument || iframe.contentWindow?.document;
                if (!doc || !doc.documentElement || !doc.body) return;
                const root = doc.documentElement;
                const body = doc.body;
                root.style.transform = '';
                root.style.transformOrigin = 'top left';
                root.style.width = '';
                body.style.margin = body.style.margin || '0';

                const frameW = Math.max(1, iframe.clientWidth);
                const frameH = Math.max(1, iframe.clientHeight);
                const contentW = Math.max(root.scrollWidth, body.scrollWidth, root.clientWidth, 1);
                const contentH = Math.max(root.scrollHeight, body.scrollHeight, root.clientHeight, 1);

                let scale = Math.min(frameW / contentW, frameH / contentH);
                if (contentW < frameW * 0.72) {
                    scale = Math.min(1.45, frameW / contentW);
                }
                if (!Number.isFinite(scale) || scale <= 0) scale = 1;

                if (Math.abs(scale - 1) > 0.02) {
                    root.style.transform = 'scale(' + scale + ')';
                    root.style.width = (100 / scale) + '%';
                }
            } catch (_) {}
        };

        iframe.onload = applyScale;
        setTimeout(applyScale, 80);
        setTimeout(applyScale, 260);
    }

    function fitPreviewContent() {
        const holder = document.getElementById('preview-slide-fit');
        if (!holder) return;
        const iframe = document.getElementById('preview_code_iframe');
        if (iframe) {
            fitIframeInHolder(iframe, holder);
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

    function renderCropUi() {
        if (!coverCropImage || !coverCropSelection) return;
        coverCropImage.style.left = coverCropState.imgX + 'px';
        coverCropImage.style.top = coverCropState.imgY + 'px';
        coverCropImage.style.width = coverCropState.imgW + 'px';
        coverCropImage.style.height = coverCropState.imgH + 'px';
        coverCropSelection.style.left = coverCropState.selection.x + 'px';
        coverCropSelection.style.top = coverCropState.selection.y + 'px';
        coverCropSelection.style.width = coverCropState.selection.w + 'px';
        coverCropSelection.style.height = coverCropState.selection.h + 'px';
    }

    function clampSelection(sel) {
        const minW = 120;
        const maxW = coverCropState.imgW;
        sel.w = Math.max(minW, Math.min(maxW, sel.w));
        sel.h = sel.w * 9 / 16;
        const maxX = coverCropState.imgX + coverCropState.imgW - sel.w;
        const maxY = coverCropState.imgY + coverCropState.imgH - sel.h;
        sel.x = Math.max(coverCropState.imgX, Math.min(maxX, sel.x));
        sel.y = Math.max(coverCropState.imgY, Math.min(maxY, sel.y));
    }

    function fitCropImage() {
        const vw = coverCropViewport.clientWidth || 1;
        const vh = coverCropViewport.clientHeight || 1;
        const base = Math.min(vw / coverCropState.imgNaturalW, vh / coverCropState.imgNaturalH);
        const scale = base * coverCropState.zoom;
        coverCropState.imgW = coverCropState.imgNaturalW * scale;
        coverCropState.imgH = coverCropState.imgNaturalH * scale;
        coverCropState.imgX = (vw - coverCropState.imgW) / 2;
        coverCropState.imgY = (vh - coverCropState.imgH) / 2;
        const s = coverCropState.selection;
        if (s.w <= 0 || s.h <= 0) {
            s.w = Math.min(coverCropState.imgW * 0.7, 520);
            s.h = s.w * 9 / 16;
            s.x = coverCropState.imgX + (coverCropState.imgW - s.w) / 2;
            s.y = coverCropState.imgY + (coverCropState.imgH - s.h) / 2;
        }
        clampSelection(s);
        renderCropUi();
    }

    function openCoverCropModal(file) {
        if (!coverCropModal || !coverCropImage || !coverCropViewport) return;
        if (coverCropState.objectUrl) URL.revokeObjectURL(coverCropState.objectUrl);
        coverCropState.objectUrl = URL.createObjectURL(file);
        coverCropState.zoom = 1;
        coverCropZoom.value = '1';
        coverCropState.selection = { x: 0, y: 0, w: 0, h: 0 };
        coverCropImage.onload = () => {
            coverCropState.imgNaturalW = coverCropImage.naturalWidth || 1;
            coverCropState.imgNaturalH = coverCropImage.naturalHeight || 1;
            fitCropImage();
        };
        coverCropImage.src = coverCropState.objectUrl;
        coverCropModal.classList.add('open');
    }

    async function applyCoverCrop() {
        const outW = 1600;
        const outH = 900;
        const sx = (coverCropState.selection.x - coverCropState.imgX) * (coverCropState.imgNaturalW / coverCropState.imgW);
        const sy = (coverCropState.selection.y - coverCropState.imgY) * (coverCropState.imgNaturalH / coverCropState.imgH);
        const sw = coverCropState.selection.w * (coverCropState.imgNaturalW / coverCropState.imgW);
        const sh = coverCropState.selection.h * (coverCropState.imgNaturalH / coverCropState.imgH);

        const canvas = document.createElement('canvas');
        canvas.width = outW;
        canvas.height = outH;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, outW, outH);
        ctx.drawImage(coverCropImage, sx, sy, sw, sh, 0, 0, outW, outH);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', 0.9));
        if (!blob) return;
        const croppedFile = new File([blob], `cover-${Date.now()}.webp`, { type: 'image/webp' });
        const dt = new DataTransfer();
        dt.items.add(croppedFile);
        coverImageFile.files = dt.files;

        const previewUrl = URL.createObjectURL(blob);
        if (coverImagePreview) {
            coverImagePreview.src = previewUrl;
            coverImagePreview.style.display = 'block';
        }
        if (coverImageRemove) {
            coverImageRemove.style.display = 'inline-flex';
        }
        coverCropModal.classList.remove('open');
        saveCurrent();
    }

    if (coverCropZoom) {
        coverCropZoom.addEventListener('input', () => {
            coverCropState.zoom = Math.max(1, Number(coverCropZoom.value || 1));
            fitCropImage();
        });
    }
    if (coverCropSelection) {
        coverCropSelection.addEventListener('mousedown', (e) => {
            e.preventDefault();
            const handle = e.target?.dataset?.handle || 'move';
            coverCropState.dragMode = handle;
            coverCropState.dragStartX = e.clientX;
            coverCropState.dragStartY = e.clientY;
            coverCropState.startSelection = { ...coverCropState.selection };
        });
    }
    window.addEventListener('mousemove', (e) => {
        if (!coverCropState.dragMode) return;
        const dx = e.clientX - coverCropState.dragStartX;
        const dy = e.clientY - coverCropState.dragStartY;
        const s = { ...coverCropState.startSelection };
        const mode = coverCropState.dragMode;
        const ratio = 16 / 9;
        if (mode === 'move') {
            s.x += dx;
            s.y += dy;
        } else {
            let w = s.w;
            if (mode === 'se' || mode === 'ne') w += dx;
            if (mode === 'sw' || mode === 'nw') w -= dx;
            w = Math.max(120, w);
            const h = w / ratio;
            if (mode === 'nw' || mode === 'sw') s.x = coverCropState.startSelection.x + (coverCropState.startSelection.w - w);
            if (mode === 'nw' || mode === 'ne') s.y = coverCropState.startSelection.y + (coverCropState.startSelection.h - h);
            s.w = w;
            s.h = h;
        }
        clampSelection(s);
        coverCropState.selection = s;
        renderCropUi();
    });
    window.addEventListener('mouseup', () => {
        coverCropState.dragMode = '';
    });
    coverCropCancel?.addEventListener('click', () => coverCropModal?.classList.remove('open'));
    coverCropModal?.addEventListener('click', (e) => {
        if (e.target === coverCropModal) coverCropModal.classList.remove('open');
    });
    coverCropApply?.addEventListener('click', applyCoverCrop);

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
        const scrollY = window.scrollY || window.pageYOffset || 0;
        document.body.dataset.builderScrollY = String(scrollY);
        document.body.style.overflow = 'hidden';
        previewIndex = 0;
        renderPreviewSlide();
        previewModal.classList.add('open');
    });
    previewPrev.addEventListener('click', () => { previewIndex--; renderPreviewSlide(); });
    previewNext.addEventListener('click', () => { previewIndex++; renderPreviewSlide(); });
    window.addEventListener('resize', fitPreviewContent);

    function restoreAfterPreviewClose() {
        if (previewModal.classList.contains('open')) return;
        document.body.style.overflow = '';
        const y = Number(document.body.dataset.builderScrollY || 0);
        if (Number.isFinite(y)) window.scrollTo(0, y);
    }
    previewModal.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => setTimeout(restoreAfterPreviewClose, 0));
    });
    previewModal.addEventListener('click', (e) => {
        if (e.target === previewModal) setTimeout(restoreAfterPreviewClose, 0);
    });

    Object.values(fields).forEach(el => el.addEventListener('input', () => { saveCurrent(); renderList(); }));
    lessonTitle.addEventListener('input', () => { saveCurrent(); });
    topClassSelect.addEventListener('change', saveCurrent);
    lessonCategory?.addEventListener('change', saveCurrent);
    lessonDifficulty?.addEventListener('change', saveCurrent);
    lessonDescription?.addEventListener('input', saveCurrent);
    globalThemeCss.addEventListener('input', saveCurrent);
    if (coverImageFile) {
        coverImageFile.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('Lutfen gecerli bir gorsel dosyasi secin.');
                return;
            }
            openCoverCropModal(file);
        });
    }
    coverImageRemove?.addEventListener('click', () => {
        if (coverImageFile) {
            coverImageFile.value = '';
        }
        state.cover_image = '';
        if (coverImagePreview) {
            coverImagePreview.src = '';
            coverImagePreview.style.display = 'none';
        }
        coverImageRemove.style.display = 'none';
        saveCurrent();
    });
    Object.values(curriculum).forEach(el => el.addEventListener('input', saveCurrent));
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

