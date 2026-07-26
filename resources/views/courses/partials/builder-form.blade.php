@php
    $isEdit = isset($course);
    $initialPayload = old('lesson_payload');
    if ($initialPayload === null) {
        $payloadSeed = $isEdit ? (array) ($course->lesson_payload ?? []) : [];
        $legacySlides = [];
        $legacyPages = [];
        if ($isEdit) {
            $legacyPages = array_values(array_filter((array) (
                data_get($payloadSeed, 'lesson_pages')
                ?? data_get($payloadSeed, 'pages')
                ?? data_get($payloadSeed, 'sections')
                ?? data_get($payloadSeed, 'modules')
                ?? data_get($payloadSeed, 'contents')
                ?? []
            ), fn ($item) => trim((string) $item) !== ''));

            if (!empty($legacyPages) && empty(data_get($payloadSeed, 'slides'))) {
                foreach ($legacyPages as $idx => $pageText) {
                    $legacySlides[] = [
                        'title' => 'Sayfa ' . ($idx + 1),
                        'xp' => 0,
                        'kind' => 'topic',
                        'interaction_type' => 'none',
                        'points' => 5,
                        'time_limit' => 10,
                        'double_points' => false,
                        'content' => (string) $pageText,
                        'instructions' => '',
                        'image_url' => '',
                        'video_url' => '',
                        'file_url' => '',
                        'code' => '',
                        'question_prompt' => '',
                        'question' => ['options' => [], 'pairs' => [], 'items' => []],
                    ];
                }
            }

            $singleContent = trim((string) (
                data_get($payloadSeed, 'content')
                ?: data_get($payloadSeed, 'text')
                ?: data_get($payloadSeed, 'body')
                ?: data_get($payloadSeed, 'description')
                ?: ''
            ));
            $singleCode = trim((string) (
                data_get($payloadSeed, 'code')
                ?: data_get($payloadSeed, 'html')
                ?: data_get($payloadSeed, 'source_code')
                ?: data_get($payloadSeed, 'script')
                ?: ''
            ));
            $singleQuestion = trim((string) (
                data_get($payloadSeed, 'question_prompt')
                ?: data_get($payloadSeed, 'prompt')
                ?: data_get($payloadSeed, 'questionText')
                ?: ''
            ));
            if (empty($legacyPages) && empty(data_get($payloadSeed, 'slides')) && ($singleContent !== '' || $singleCode !== '' || $singleQuestion !== '')) {
                $legacySlides[] = [
                    'title' => (string) (data_get($payloadSeed, 'title') ?: data_get($payloadSeed, 'lesson_title') ?: 'Sayfa 1'),
                    'xp' => (int) (data_get($payloadSeed, 'xp') ?: 0),
                    'kind' => (string) (data_get($payloadSeed, 'kind') ?: 'topic'),
                    'interaction_type' => (string) (data_get($payloadSeed, 'interaction_type') ?: 'none'),
                    'points' => (int) (data_get($payloadSeed, 'points') ?: 5),
                    'time_limit' => (int) (data_get($payloadSeed, 'time_limit') ?: 10),
                    'double_points' => (bool) data_get($payloadSeed, 'double_points', false),
                    'content' => $singleContent,
                    'instructions' => (string) (data_get($payloadSeed, 'instructions') ?: ''),
                    'image_url' => (string) (data_get($payloadSeed, 'image_url') ?: ''),
                    'video_url' => (string) (data_get($payloadSeed, 'video_url') ?: ''),
                    'file_url' => (string) (data_get($payloadSeed, 'file_url') ?: ''),
                    'code' => $singleCode,
                    'question_prompt' => $singleQuestion,
                    'question' => data_get($payloadSeed, 'question') ?: ['options' => [], 'pairs' => [], 'items' => []],
                ];
            }
        }
        $payloadSeed = array_replace_recursive([
            'slides' => [],
            'curriculum' => [],
            'lesson_description' => '',
            'category' => '',
            'difficulty' => '',
            'cover_image' => '',
        ], $payloadSeed);
        if (!empty($legacySlides) && empty($payloadSeed['slides'])) {
            $payloadSeed['slides'] = $legacySlides;
        }
        if ($isEdit) {
            $payloadSeed['slides'] = array_values(array_filter((array) ($payloadSeed['slides'] ?? []), fn ($slide) => is_array($slide)));
            $payloadSeed['curriculum'] = is_array($payloadSeed['curriculum'] ?? null) ? $payloadSeed['curriculum'] : [];
            $payloadSeed['lesson_description'] = (string) ($payloadSeed['lesson_description'] ?? '');
            $payloadSeed['category'] = (string) ($payloadSeed['category'] ?? '');
            $payloadSeed['difficulty'] = (string) ($payloadSeed['difficulty'] ?? '');
            if (empty($payloadSeed['cover_image'])) {
                $payloadSeed['cover_image'] = $course->coverImageUrl() ?: '';
            }
        }
        $initialPayload = json_encode($payloadSeed, JSON_UNESCAPED_UNICODE);
    }
    $existingCoverUrl = $isEdit ? ($course->coverImageUrl() ?: '') : '';
    $selectedClass = old('school_class_id', $isEdit ? $course->school_class_id : '__ALL__');
    $defaultTeacherId = old('teacher_id', $isEdit ? $course->teacher_id : ($teachers->first()->id ?? null));
    $defaultWeeklyHours = old('weekly_hours', $isEdit ? $course->weekly_hours : 2);
    $defaultCode = old('code', $isEdit ? $course->code : '');
    $defaultLessonTitle = old('name', $isEdit ? $course->name : 'Örnek Ders: Layout Galerisi');
@endphp

<style>
    :root{
        --builder-bg:#f4f7fb;
        --builder-surface:#ffffff;
        --builder-surface-soft:#f8fbff;
        --builder-border:#d8e3f2;
        --builder-border-strong:#bfd4f1;
        --builder-text:#0f172a;
        --builder-muted:#64748b;
        --builder-primary:#2563eb;
        --builder-primary-strong:#1d4ed8;
        --builder-primary-soft:#dbeafe;
        --builder-danger:#ef4444;
        --builder-danger-soft:#fee2e2;
        --builder-success:#16a34a;
        --builder-shadow:0 14px 34px rgba(15,23,42,.08);
        --builder-shadow-strong:0 18px 42px rgba(37,99,235,.12);
        --builder-radius:22px;
    }
    .lesson-builder{
        overflow:visible;
        position:relative;
        color:var(--builder-text);
        padding:6px 0 24px;
    }
    .lesson-builder *{
        box-sizing:border-box;
    }
    .lesson-builder :where(input,select,textarea,button){
        font:inherit;
    }
    .lesson-builder :where(input:not([type=checkbox]):not([type=radio]),select,textarea){
        width:100%;
        background:#fff;
        border:1px solid var(--builder-border);
        border-radius:16px;
        color:var(--builder-text);
        padding:13px 16px;
        box-shadow:0 1px 0 rgba(255,255,255,.8) inset;
        transition:border-color .18s ease, box-shadow .18s ease, transform .18s ease, background .18s ease;
    }
    .lesson-builder :where(input:not([type=checkbox]):not([type=radio]),select,textarea)::placeholder{
        color:#94a3b8;
    }
    .lesson-builder :where(input:not([type=checkbox]):not([type=radio]),select,textarea):focus{
        outline:none;
        border-color:var(--builder-primary);
        box-shadow:0 0 0 4px rgba(37,99,235,.12);
        background:#fff;
    }
    .lesson-builder textarea{
        resize:vertical;
        min-height:120px;
    }
    .lesson-builder label{
        display:block;
        font-size:13px;
        font-weight:700;
        letter-spacing:.01em;
        color:#1e293b;
        margin:0 0 8px;
    }
    .lesson-builder small{
        color:var(--builder-muted);
        line-height:1.55;
    }
    .lesson-builder .badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:32px;
        padding:.25rem .8rem;
        border-radius:999px;
        background:var(--builder-primary-soft);
        color:var(--builder-primary-strong);
        font-weight:800;
        box-shadow:none;
    }
    .lesson-builder .btn{
        min-height:44px;
        padding:0 16px;
        border-radius:14px;
        border:1px solid transparent;
        background:linear-gradient(180deg,#ffffff,#f3f7ff);
        color:#1e3a8a;
        font-weight:700;
        box-shadow:0 8px 18px rgba(37,99,235,.08);
        transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
    }
    .lesson-builder .btn:hover{
        transform:translateY(-1px);
        box-shadow:0 14px 28px rgba(37,99,235,.12);
        border-color:#cfe0ff;
    }
    .lesson-builder .btn:active{
        transform:translateY(0);
    }
    .lesson-builder .btn.btn-danger,
    .lesson-builder .btn-danger{
        background:linear-gradient(180deg,#fff5f5,#fee2e2);
        color:#b91c1c;
        border-color:#fecaca;
    }
    .lesson-builder .btn.btn-danger:hover,
    .lesson-builder .btn-danger:hover{
        border-color:#fca5a5;
        box-shadow:0 14px 28px rgba(239,68,68,.12);
    }
    .lesson-builder-top{
        display:grid;
        grid-template-columns:minmax(0,1fr) auto;
        gap:16px;
        align-items:center;
        padding:18px;
        margin-bottom:18px;
        border:1px solid var(--builder-border);
        border-radius:28px;
        background:linear-gradient(180deg,rgba(255,255,255,.98),#f8fbff);
        box-shadow:var(--builder-shadow);
    }
    .lesson-builder-top .actions{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        align-items:center;
        gap:10px;
    }
    .lesson-builder-top .actions .btn{
        white-space:nowrap;
    }
    .lesson-builder-top > div:first-child{
        grid-template-columns:minmax(0,1fr) minmax(220px,300px) !important;
        gap:12px !important;
    }
    .lesson-builder-grid{
        display:grid;
        grid-template-columns:minmax(250px,.9fr) minmax(0,1.8fr) minmax(240px,.95fr);
        gap:18px;
        align-items:start;
    }
    .lesson-builder-grid > :where(.builder-left,.builder-center,.builder-right){
        position:relative;
        min-width:0;
        padding:18px;
        border:1px solid var(--builder-border);
        border-radius:28px;
        background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(250,252,255,.98));
        box-shadow:var(--builder-shadow);
    }
    .lesson-builder-grid > :where(.builder-left,.builder-center,.builder-right) h4{
        margin:0 0 14px;
        font-size:15px;
        font-weight:800;
        letter-spacing:-.01em;
        color:#0f172a;
    }
    .builder-left{
        display:flex;
        flex-direction:column;
        gap:12px;
    }
    .builder-left #slide_list{
        display:grid;
        gap:10px;
        min-height:220px;
    }
    .builder-left :where(button,#slide_list button){
        width:100%;
    }
    .builder-center{
        display:flex;
        flex-direction:column;
        gap:16px;
    }
    .builder-tabs{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        padding:10px;
        border:1px solid var(--builder-border);
        border-radius:20px;
        background:linear-gradient(180deg,#f8fbff,#ffffff);
        box-shadow:inset 0 1px 0 rgba(255,255,255,.85);
    }
    .builder-tabs .tab-btn{
        min-height:46px;
        padding:0 18px;
        border-radius:14px;
        border:1px solid #d7e4f6;
        background:#f8fbff;
        color:#334155;
        font-weight:800;
        transition:all .18s ease;
    }
    .builder-tabs .tab-btn:hover{
        border-color:#b9cff1;
        box-shadow:0 10px 20px rgba(37,99,235,.08);
        transform:translateY(-1px);
    }
    .builder-tabs .tab-btn.active{
        background:linear-gradient(180deg,#2f6af1,#2554d8);
        color:#fff;
        border-color:transparent;
        box-shadow:0 14px 28px rgba(37,99,235,.22);
    }
    .builder-panel{
        padding:18px;
        border:1px solid var(--builder-border);
        border-radius:24px;
        background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,251,255,.92));
        box-shadow:var(--builder-shadow);
    }
    .builder-panel > label + :where(input,select,textarea){
        margin-bottom:16px;
    }
    .builder-panel [style*="border-top"]{
        margin-top:18px !important;
        padding-top:18px !important;
        border-top-color:#dbe5f2 !important;
    }
    .builder-panel [style*="layout_help_panel"],
    #layout_help_panel,
    #layout_editor_panel{
        border-radius:18px !important;
        border-color:var(--builder-border) !important;
        background:linear-gradient(180deg,#f8fbff,#ffffff) !important;
    }
    #layout_editor_fields > *{
        margin-bottom:12px;
    }
    .builder-right{
        display:flex;
        flex-direction:column;
        gap:12px;
    }
    .builder-right #cover_image_preview_box{
        border-radius:18px !important;
        border:1px solid var(--builder-border) !important;
        box-shadow:0 12px 26px rgba(15,23,42,.08);
    }
    .builder-right #cover_image_preview{
        object-fit:cover;
    }
    #course-upload-progress{
        border-color:#d8e3f2 !important;
        background:linear-gradient(180deg,#ffffff,#f8fbff) !important;
    }
    #preview_slide_stage,
    .modal-card{
        border-radius:28px;
    }
    .modal-card{
        border:1px solid var(--builder-border);
        background:linear-gradient(180deg,#ffffff,#f8fbff);
        box-shadow:0 24px 60px rgba(15,23,42,.18);
    }
    .modal-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding-bottom:12px;
        margin-bottom:12px;
        border-bottom:1px solid #e2e8f0;
    }
    .actions{
        gap:10px;
    }
    .actions .btn{
        min-height:44px;
    }
    .sq-answers{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:12px;
    }
    .sq-answer-card{
        display:grid;
        grid-template-columns:40px minmax(0,1fr) 28px;
        gap:10px;
        align-items:center;
        background:linear-gradient(180deg,#ffffff,#f8fbff);
        border:1px solid var(--builder-border);
        border-radius:16px;
        padding:10px 12px;
        box-shadow:0 10px 24px rgba(15,23,42,.04);
    }
    .sq-shape{
        width:32px;
        height:32px;
        border-radius:10px;
        display:grid;
        place-items:center;
        color:#fff;
        font-weight:800;
        box-shadow:0 8px 18px rgba(15,23,42,.08);
    }
    .sq-red{background:#ef4444}
    .sq-blue{background:#3b82f6}
    .sq-yellow{background:#eab308}
    .sq-green{background:#22c55e}
    .sq-answer-card input[type="text"]{
        margin:0;
        min-width:0;
    }
    .text-toolbar{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin:10px 0 12px;
    }
    .text-toolbar button,
    .split-text-toolbar button,
    [data-rich-toolbar] button{
        height:42px;
        min-width:42px;
        padding:0 14px;
        border:1px solid #d7e4f6;
        border-radius:14px;
        background:linear-gradient(180deg,#ffffff,#f7fbff);
        color:#0f172a;
        font-weight:800;
        cursor:pointer;
        transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .text-toolbar button:hover,
    .split-text-toolbar button:hover,
    [data-rich-toolbar] button:hover{
        border-color:#9ec3ff;
        box-shadow:0 12px 24px rgba(37,99,235,.1);
        transform:translateY(-1px);
    }
    .lesson-builder [data-rich-toolbar]{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin:10px 0 12px;
        padding:10px;
        border:1px solid var(--builder-border);
        border-radius:18px;
        background:linear-gradient(180deg,#ffffff,#f8fbff);
    }
    .lesson-builder [data-rich-toolbar] select,
    .lesson-builder [data-rich-toolbar] input[type="color"]{
        height:42px;
        min-width:110px;
        border-radius:12px;
    }
    .lesson-builder [data-rich-toolbar] input[type="color"]{
        padding:3px;
    }
    .lesson-builder [contenteditable="true"]{
        min-height:180px;
        padding:16px;
        border:1px solid var(--builder-border);
        border-radius:18px;
        background:#fff;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.8);
    }
    .lesson-builder [contenteditable="true"]:focus{
        outline:none;
        border-color:var(--builder-primary);
        box-shadow:0 0 0 4px rgba(37,99,235,.12);
    }
    .lesson-builder #question_editor{
        padding:10px 0 0;
    }
    .lesson-builder .card,
    .lesson-builder .badge{
        border-radius:18px;
    }
    .lesson-builder .btn,
    .lesson-builder .tab-btn{
        letter-spacing:-.01em;
    }
    .lesson-builder .builder-left #slide_list > *{
        border-radius:16px;
    }
    .lesson-builder #slide_list .active,
    .lesson-builder #slide_list [aria-current="true"]{
        box-shadow:0 0 0 4px rgba(37,99,235,.12);
    }
    .lesson-builder .builder-panel [style*="display:grid"][style*="grid-template-columns:1fr 1fr 1fr"]{
        gap:12px !important;
    }
    .lesson-builder #current_slide_xp_badge{
        margin-left:auto;
    }
    .lesson-builder .builder-panel textarea,
    .lesson-builder .builder-panel input,
    .lesson-builder .builder-panel select{
        background:#fff;
    }
    @media (max-width:1280px){
        .lesson-builder-grid{
            grid-template-columns:minmax(0,1fr);
        }
        .lesson-builder-top{
            grid-template-columns:minmax(0,1fr);
        }
        .lesson-builder-top > div:first-child{
            grid-template-columns:minmax(0,1fr) minmax(220px,260px) !important;
        }
        .lesson-builder-top .actions{
            justify-content:flex-start;
        }
    }
    @media (max-width:980px){
        .sq-answers{
            grid-template-columns:1fr;
        }
        .lesson-builder-top > div:first-child{
            grid-template-columns:1fr !important;
        }
        .builder-tabs{
            gap:8px;
        }
        .builder-tabs .tab-btn{
            flex:1 1 calc(50% - 8px);
        }
    }
    @media (max-width:640px){
        .lesson-builder{
            padding-top:0;
        }
        .lesson-builder-top,
        .lesson-builder-grid > :where(.builder-left,.builder-center,.builder-right),
        .builder-panel,
        .modal-card{
            border-radius:20px;
        }
        .lesson-builder-top{
            padding:14px;
        }
        .lesson-builder .btn,
        .lesson-builder .tab-btn,
        .text-toolbar button,
        .split-text-toolbar button,
        [data-rich-toolbar] button{
            min-height:44px;
        }
        .lesson-builder-top .actions{
            width:100%;
        }
        .lesson-builder-top .actions .btn{
            flex:1 1 100%;
        }
        .builder-tabs .tab-btn{
            flex:1 1 100%;
        }
        .sq-answer-card{
            grid-template-columns:34px minmax(0,1fr) 24px;
            padding:10px;
        }
    }
</style>

<div class="lesson-builder">
    <div id="course-upload-progress" style="display:none;position:sticky;top:0;z-index:20;margin-bottom:12px;background:#fff;border:1px solid #dbe5f2;border-radius:12px;padding:10px 12px;box-shadow:0 10px 24px rgba(15,23,42,.08);">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:8px;">
            <strong style="color:#0f172a;font-size:14px;">Ders yükleniyor</strong>
            <span id="course-upload-progress-text" style="color:#2563eb;font-weight:700;font-size:13px;">%0</span>
        </div>
        <div style="height:10px;border-radius:999px;background:#e2e8f0;overflow:hidden;">
            <div id="course-upload-progress-bar" style="height:100%;width:0%;border-radius:999px;background:linear-gradient(90deg,#2563eb,#22c55e);transition:width .15s ease;"></div>
        </div>
    </div>

    <div class="lesson-builder-top">
        <div style="display:grid;grid-template-columns:1fr 300px;gap:10px;width:100%">
            <input type="text" id="lesson_title" placeholder="Ders başlığı" value="{{ $defaultLessonTitle }}">
            <select id="top_class_select">
                <option value="__ALL__" @selected($selectedClass === '__ALL__')>Tüm Sınıflar</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected((string)$selectedClass === (string)$class->id)>
                        {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('courses.index') }}">Derslerime Geri Dön</a>
            <button class="btn" type="button" id="builder_preview_btn">Önizleme</button>
            <button class="btn" type="submit">{{ $isEdit ? 'Değişiklikleri Kaydet' : 'Dersi Kaydet' }}</button>
            <button class="btn btn-danger" type="button" id="remove_slide_btn">Slaytı Sil</button>
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
                <button type="button" class="tab-btn" data-tab="text">Yazı Ekle</button>
                <button type="button" class="tab-btn active" data-tab="code">Kod Ekle</button>
                <button type="button" class="tab-btn" data-tab="question">Soru Ekle</button>
            </div>

            <div class="builder-panel" data-panel="text" style="display:none">
                <label>Slide Basligi</label>
                <input type="text" id="slide_title">
                <label>Slide Layout</label>
                <select id="slide_layout">
                    <option value="split">Split Content - İki sütun</option>
                    <option value="image">Image Focus - Görsel odaklı</option>
                </select>
                <div id="layout_help_panel" style="margin:12px 0 14px;padding:14px;border:1px solid #dbe5f2;border-radius:14px;background:linear-gradient(180deg,#f8fbff,#ffffff)">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px">
                        <strong id="layout_help_title" style="font-size:14px;color:#0f172a">Auto</strong>
                        <span id="layout_help_badge" class="badge">İçeriğe göre</span>
                    </div>
                    <p id="layout_help_desc" style="margin:0 0 12px;color:#475569;font-size:13px;line-height:1.6">Slide içeriğine göre otomatik yerleşim seçilir.</p>
                    <div id="layout_wireframe" style="display:grid;gap:8px">
                        <div style="height:16px;border-radius:8px;background:#dbeafe"></div>
                        <div style="height:14px;border-radius:8px;background:#eff6ff;width:78%"></div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                            <div style="height:58px;border-radius:12px;background:#eff6ff"></div>
                            <div style="height:58px;border-radius:12px;background:#eff6ff"></div>
                        </div>
                    </div>
                </div>
                <div id="layout_editor_panel" style="margin:0 0 12px;padding:14px;border:1px solid #dbe5f2;border-radius:14px;background:#fff">
                    <strong style="display:block;margin-bottom:10px;font-size:14px;color:#0f172a">Layout İçerikleri</strong>
                    <div id="layout_editor_hint" style="color:#64748b;font-size:13px;line-height:1.6;margin-bottom:10px">Seçtiğiniz layout’a göre aşağıdaki alanlar aktif olur.</div>
                    <div id="layout_editor_fields"></div>
                </div>
                <label>Sayfa XP</label>
                <input type="number" id="slide_xp" min="0" max="500" value="0">
                <div style="display:none" aria-hidden="true">
                    <label>Öğrenci Yönlendirme Notu</label>
                    <textarea id="slide_instructions" rows="3" placeholder="Bu sayfada öğrenci ne yapmalı?"></textarea>
                </div>
            </div>

            <div class="builder-panel" data-panel="code">
                <label>HTML/CSS/JS Kodu</label>
                <textarea id="slide_code" rows="9" placeholder="<div>...</div> <style>...</style> <script>...</script>"></textarea>
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid #dbe5f2">
                    <h4 style="margin:0 0 12px">Müfredat Bilgileri</h4>
                    <label>Müfredat Başlığı</label>
                    <input type="text" id="curriculum_title" placeholder="Mobil Dünyaya İlk Adım: Arayüzü Keşfediyorum">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div style="display:none" aria-hidden="true">
                            <label>Ders No</label>
                            <input type="number" id="curriculum_lesson_number" min="1" value="1" style="display:none" aria-hidden="true">
                        </div>
                        <div style="display:none" aria-hidden="true">
                            <label>İlerleme (0-100)</label>
                            <input type="number" id="curriculum_progress" min="0" max="100" value="0" style="display:none" aria-hidden="true">
                        </div>
                    </div>
                    <label>Konu</label>
                    <textarea id="curriculum_topic" rows="4" placeholder="Bu derste..."></textarea>
                    <label>Kazanımlar (Her satır bir madde)</label>
                    <textarea id="curriculum_outcomes" rows="5" placeholder="Kazanım 1&#10;Kazanım 2"></textarea>
                    <div style="display:none" aria-hidden="true">
                    <div style="display:none" aria-hidden="true">
                        <label>Etkinlikler (Her satır bir madde)</label>
                        <textarea id="curriculum_activities" rows="5" placeholder="Etkinlik 1&#10;Etkinlik 2"></textarea>
                    </div>
                    </div>
                </div>
                <h4 style="margin:16px 0 10px;display:none">Tema Şablonları</h4>
                <div style="display:none" aria-hidden="true">
                <label>Global Tema Şablonu</label>
                <select id="theme_template_select">
                    <option value="none">Temasız</option>
                    <option value="aurora">Aurora</option>
                    <option value="paper">Paper</option>
                    <option value="midnight">Midnight</option>
                    <option value="playful">Playful</option>
                    <option value="academy">Academy</option>
                </select>
                <small style="display:block;margin:8px 0 12px;color:#64748b">
                    Bu şablon; yazı tipi, başlık, paragraf, kart, kod ve tablo görünümünü ders boyunca sabitler.
                </small>
                <label style="margin-top:10px;display:block">Global Tema CSS</label>
                <textarea id="global_theme_css" rows="7" placeholder=".slide-theme{background:#0f172a;color:#f8fafc} .slide-theme h3{color:#f8fafc}"></textarea>
                </div>
            </div>

            <div class="builder-panel" data-panel="question" style="display:none">
                <label>İçerik Tipi</label>
                <select id="slide_kind">
                    <option value="topic">Konu Anlatımı</option>
                    <option value="question">Soru Sayfası</option>
                    <option value="task">Görev Sayfası</option>
                    <option value="summary">Özet Sayfası</option>
                </select>
                <label>Etkilesim Tipi</label>
                <select id="slide_interaction_type">
                    <option value="none">Yok</option>
                    <option value="multiple_choice">Çoktan Seçmeli</option>
                    <option value="true_false">Doğru Yanlış</option>
                    <option value="matching">Eşleştirme</option>
                    <option value="drag_drop">Sürükle Bırak</option>
                    <option value="short_answer">Kısa Cevap</option>
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
                        <label>Süre</label>
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
            <label>Ders Kategorisi</label>
            <select id="lesson_category">
                <option value="Kodlama">Kodlama</option>
                <option value="Tasarim">Tasarim</option>
                <option value="Elektrik">Elektrik</option>
                <option value="Robotik">Robotik</option>
                <option value="Teorik">Teorik</option>
                <option value="Oyun">Oyun</option>
                <option value="Yapay Zeka">Yapay Zeka</option>
            </select>
            <label>Ders Zorluğu</label>
            <select id="lesson_difficulty">
                <option value="Kolay">Kolay</option>
                <option value="Orta">Orta</option>
                <option value="Zor">Zor</option>
            </select>
            <label>Ders Açıklaması</label>
            <textarea id="lesson_description" rows="3" placeholder="Ders başlığı altında gösterilecek açıklama"></textarea>
            <label>Kapak Görseli Yükle</label>
            <input type="file" id="cover_image_file" name="cover_image_file" accept="image/*">
            <small style="color:#64748b">Maksimum 3 MB (jpg, jpeg, png, webp)</small>
            <div id="cover_image_path_label" style="font-size:12px;color:#475569;line-height:1.5;word-break:break-all"></div>
            <div id="cover_image_preview_box" data-cover-url="{{ $existingCoverUrl }}" style="display:{{ $existingCoverUrl ? 'block' : 'none' }};width:100%;aspect-ratio:16/9;border-radius:10px;border:1px solid #e2e8f0;margin-top:6px;background:#f1f5f9;overflow:hidden">
                <img id="cover_image_preview" alt="Kapak önizleme" src="{{ $existingCoverUrl }}" style="width:100%;height:100%;object-fit:cover;display:block;background:#f1f5f9">
            </div>
            <button class="btn btn-danger" type="button" id="cover_image_remove" style="margin-top:8px;display:none">Kapağı Sil</button>
        </aside>
    </div>
</div>

<textarea id="lesson_payload" name="lesson_payload" style="display:none">{{ $initialPayload }}</textarea>
<input type="hidden" id="course_name_hidden" name="name" value="{{ $defaultLessonTitle }}">
<input type="hidden" id="course_code_hidden" name="code" value="{{ $defaultCode }}">
<input type="hidden" id="teacher_id_hidden" name="teacher_id" value="{{ $defaultTeacherId }}">
<input type="hidden" id="school_class_id_hidden" name="school_class_id" value="{{ old('school_class_id', $isEdit ? $course->school_class_id : '') }}">
<input type="hidden" id="weekly_hours_hidden" name="weekly_hours" value="{{ $defaultWeeklyHours }}">
<input type="hidden" id="cover_image_data" name="cover_image_data" value="">

<div id="builder-preview-modal" class="modal">
    <div class="modal-card" style="width:min(96vw,1500px);max-width:96vw;max-height:92vh;display:flex;flex-direction:column">
        <div class="modal-head">
            <strong>Ders Önizleme</strong>
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

<div id="cover-crop-modal" class="modal">
    <div class="modal-card" style="width:min(92vw,980px);max-width:980px">
        <div class="modal-head">
            <strong>Kapak Görseli Kırp (16:9)</strong>
            <button class="btn" type="button" id="cover-crop-cancel">İptal</button>
        </div>
        <div style="display:grid;gap:10px">
            <div id="cover-crop-viewport" style="position:relative;width:100%;aspect-ratio:16/9;overflow:hidden;border-radius:12px;border:1px solid #cbd5e1;background:#0f172a">
                <img id="cover-crop-image" alt="Kirpma" style="position:absolute;left:0;top:0;user-select:none;max-width:none;">
                <div id="cover-crop-selection" style="position:absolute;border:2px solid #fff;box-shadow:0 0 0 9999px rgba(0,0,0,.35);cursor:move;">
                    <span data-handle="nw" style="position:absolute;left:-6px;top:-6px;width:12px;height:12px;background:#fff;border-radius:999px;cursor:nwse-resize"></span>
                    <span data-handle="ne" style="position:absolute;right:-6px;top:-6px;width:12px;height:12px;background:#fff;border-radius:999px;cursor:nesw-resize"></span>
                    <span data-handle="sw" style="position:absolute;left:-6px;bottom:-6px;width:12px;height:12px;background:#fff;border-radius:999px;cursor:nesw-resize"></span>
                    <span data-handle="se" style="position:absolute;right:-6px;bottom:-6px;width:12px;height:12px;background:#fff;border-radius:999px;cursor:nwse-resize"></span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center">
                <label style="display:grid;gap:6px;margin:0">
                    <span style="font-size:12px;color:#64748b">Zoom</span>
                    <input id="cover-crop-zoom" type="range" min="1" max="3" step="0.01" value="1">
                </label>
                <button class="btn" type="button" id="cover-crop-apply">Kırpmayı Uygula</button>
            </div>
        </div>
    </div>
</div>

@if(isset($errors) && $errors->any())
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
    const lessonCategory = document.getElementById('lesson_category');
    const lessonDifficulty = document.getElementById('lesson_difficulty');
    const lessonDescription = document.getElementById('lesson_description');
    const coverImageFile = document.getElementById('cover_image_file');
    const coverImagePreview = document.getElementById('cover_image_preview');
    const coverImagePreviewBox = document.getElementById('cover_image_preview_box');
    const coverImageRemove = document.getElementById('cover_image_remove');
    const coverImagePathLabel = document.getElementById('cover_image_path_label');
    const coverCropModal = document.getElementById('cover-crop-modal');
    const coverCropImage = document.getElementById('cover-crop-image');
    const coverCropViewport = document.getElementById('cover-crop-viewport');
    const coverCropSelection = document.getElementById('cover-crop-selection');
    const coverCropZoom = document.getElementById('cover-crop-zoom');
    const coverCropApply = document.getElementById('cover-crop-apply');
    const coverCropCancel = document.getElementById('cover-crop-cancel');
    const appToast = window.appToast;
    const appToastDismiss = window.appToastDismiss;
    const uploadProgressWrap = document.getElementById('course-upload-progress');
    const uploadProgressBar = document.getElementById('course-upload-progress-bar');
    const uploadProgressText = document.getElementById('course-upload-progress-text');

    const hName = document.getElementById('course_name_hidden');
    const hCode = document.getElementById('course_code_hidden');
    const hTeacher = document.getElementById('teacher_id_hidden');
    const hClass = document.getElementById('school_class_id_hidden');
    const hWeekly = document.getElementById('weekly_hours_hidden');
    const coverImageData = document.getElementById('cover_image_data');

    const fields = {
        title: document.getElementById('slide_title'),
        layout: document.getElementById('slide_layout'),
        xp: document.getElementById('slide_xp'),
        content: document.getElementById('slide_content'),
        instructions: document.getElementById('slide_instructions'),
        code: document.getElementById('slide_code'),
        kind: document.getElementById('slide_kind'),
        interaction_type: document.getElementById('slide_interaction_type'),
        question_prompt: document.getElementById('slide_question_prompt'),
        points: document.getElementById('slide_points'),
        time_limit: document.getElementById('slide_time_limit'),
        double_points: document.getElementById('slide_double_points'),
    };
    const contentEditor = document.getElementById('slide_content_editor');
    const layoutHelpTitle = document.getElementById('layout_help_title');
    const layoutHelpBadge = document.getElementById('layout_help_badge');
    const layoutHelpDesc = document.getElementById('layout_help_desc');
    const layoutWireframe = document.getElementById('layout_wireframe');
    const layoutEditorFields = document.getElementById('layout_editor_fields');
    const layoutEditorHint = document.getElementById('layout_editor_hint');
    const layoutFileMap = new Map();
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
    const globalThemeCss = document.getElementById('global_theme_css');
    const themeTemplateSelect = document.getElementById('theme_template_select');

    const themeTemplates = {
        default: `
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#eef6ff 100%);color:#0f172a;--theme-accent:#0f766e;--theme-accent-2:#2563eb;--theme-bg:#f8fbff;--theme-panel:#ffffff;--theme-border:#bfdbfe}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.025em;line-height:1.12;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.82;color:#334155}
.slide-theme :where(strong,b){color:#0f172a;font-weight:800}
.slide-theme :where(a){color:#0f766e;text-decoration:none;border-bottom:1px solid rgba(15,118,110,.2)}
.slide-theme :where(code,pre,kbd,samp){background:#dbeafe;color:#0f172a;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #0f766e;background:#ecfeff;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#dbeafe;color:#0f172a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #bfdbfe;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#475569;text-align:center}
.slide-theme :where(section,article,aside,main,header,footer,nav,div){border-radius:16px}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(14,116,144,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.9))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#0f172a;border-radius:999px;padding:.15rem .55rem;font-weight:800}
`,
        none: '',
        aurora: `
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(135deg,#f0f9ff 0%,#eef2ff 48%,#f5f3ff 100%);color:#1e293b;--theme-accent:#2563eb;--theme-accent-2:#7c3aed;--theme-bg:rgba(255,255,255,.58);--theme-panel:#ffffff;--theme-border:rgba(37,99,235,.16)}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.02em;line-height:1.15;font-weight:800;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.8;color:#334155}
.slide-theme :where(strong,b){color:#111827;font-weight:800}
.slide-theme :where(a){color:#1d4ed8;text-decoration:none;border-bottom:1px solid rgba(29,78,216,.25)}
.slide-theme :where(code,pre,kbd,samp){background:#e0f2fe;color:#0f172a;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #3b82f6;background:#eff6ff;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 10px 24px rgba(15,23,42,.08)}
.slide-theme :where(th){background:#dbeafe;color:#0f172a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #bfdbfe;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#64748b;text-align:center}
.slide-theme :where(section,article,aside,main,header,footer,nav,div){border-radius:16px}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(37,99,235,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.72))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#1e3a8a;border-radius:999px;padding:.15rem .55rem;font-weight:700}
`,
        paper: `
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:"Georgia",serif;background:linear-gradient(180deg,#fffdf8 0%,#fbf7ef 100%);color:#2f2a23;--theme-accent:#7c2d12;--theme-accent-2:#b45309;--theme-bg:#fffaf0;--theme-panel:#fffef8;--theme-border:#e7c89b}
.slide-theme :where(h1,h2,h3,h4,h5,h6){font-family:"Trebuchet MS",system-ui,sans-serif;color:#3b2f2a;letter-spacing:0;line-height:1.12;font-weight:800;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:19px;line-height:1.85;color:#40352e}
.slide-theme :where(a){color:#92400e;text-decoration:underline}
.slide-theme :where(code,pre,kbd,samp){background:#f5e7d6;color:#4b2e1a;border-radius:10px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #b45309;background:#fff4e6;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fffdf9;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#f7d9b5;color:#4b2e1a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #e7c89b;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#8a5a2b;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(180,83,9,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.82))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#f5e7d6;color:#7c2d12;border-radius:999px;padding:.15rem .55rem;font-weight:700}
`,
        midnight: `
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:radial-gradient(circle at top,#1f2937 0,#0f172a 55%,#020617 100%);color:#e5e7eb;--theme-accent:#38bdf8;--theme-accent-2:#a78bfa;--theme-bg:rgba(15,23,42,.8);--theme-panel:rgba(15,23,42,.92);--theme-border:rgba(125,211,252,.22)}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#f8fafc;letter-spacing:-.03em;line-height:1.1;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.85;color:#cbd5e1}
.slide-theme :where(a){color:#7dd3fc;text-decoration:none;border-bottom:1px solid rgba(125,211,252,.3)}
.slide-theme :where(code,pre,kbd,samp){background:#111827;color:#f8fafc;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #38bdf8;background:rgba(56,189,248,.1);padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:rgba(15,23,42,.85);border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#1e293b;color:#f8fafc;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid rgba(148,163,184,.2);padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#94a3b8;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(0,0,0,.18);background:linear-gradient(180deg,var(--theme-panel),rgba(15,23,42,.74))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:rgba(56,189,248,.14);color:#e0f2fe;border-radius:999px;padding:.15rem .55rem;font-weight:700}
`,
        playful: `
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:"Trebuchet MS",system-ui,sans-serif;background:linear-gradient(135deg,#fff7ed 0%,#fef3c7 35%,#ecfeff 100%);color:#1f2937;--theme-accent:#f97316;--theme-accent-2:#06b6d4;--theme-bg:#fff8ed;--theme-panel:#ffffff;--theme-border:#fdba74}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.02em;line-height:1.14;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.8;color:#334155}
.slide-theme :where(a){color:#ea580c;text-decoration:none;border-bottom:1px dashed rgba(234,88,12,.35)}
.slide-theme :where(code,pre,kbd,samp){background:#ffedd5;color:#7c2d12;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #f97316;background:#fff7ed;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#fed7aa;color:#7c2d12;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #fdba74;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#c2410c;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:20px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(249,115,22,.09);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.82))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#ffedd5;color:#9a3412;border-radius:999px;padding:.15rem .55rem;font-weight:800}
`,
        academy: `
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#eef6ff 100%);color:#0f172a;--theme-accent:#0f766e;--theme-accent-2:#2563eb;--theme-bg:#f8fbff;--theme-panel:#ffffff;--theme-border:#bfdbfe}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.025em;line-height:1.12;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.82;color:#334155}
.slide-theme :where(a){color:#0f766e;text-decoration:none;border-bottom:1px solid rgba(15,118,110,.2)}
.slide-theme :where(code,pre,kbd,samp){background:#dbeafe;color:#0f172a;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #0f766e;background:#ecfeff;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#dbeafe;color:#0f172a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #bfdbfe;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#475569;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(14,116,144,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.9))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#0f172a;border-radius:999px;padding:.15rem .55rem;font-weight:800}
`,
    };

    function applyThemePreset(preset) {
        const key = String(preset || 'default');
        const css = themeTemplates[key] ?? '';
        if (globalThemeCss) globalThemeCss.value = css.trim();
        if (themeTemplateSelect) themeTemplateSelect.value = key in themeTemplates ? key : 'none';
        state.theme_template = key in themeTemplates ? key : 'default';
    }

    let state;
    try { state = JSON.parse(payloadInput.value || '{"slides":[]}'); } catch (e) { state = {slides: []}; }
    const existingCoverUrl = @json($existingCoverUrl);
    const appBaseUrl = @json(url('/'));
    const isEditMode = {{ $isEdit ? 'true' : 'false' }};
    const draftKey = 'lesson_builder_draft_{{ $isEdit ? 'edit_' . $course->id : 'create' }}';
    const shouldPersistDraft = isEditMode;
    if (isEditMode && (!state.slides || state.slides.length === 0) && shouldPersistDraft) {
        try {
            const draft = localStorage.getItem(draftKey);
            if (draft) {
                const parsed = JSON.parse(draft);
                if (parsed && Array.isArray(parsed.slides) && parsed.slides.length) state = parsed;
            }
        } catch (_) {}
    }
    if (!Array.isArray(state.slides)) state.slides = [];
    state.slides = state.slides
        .filter((slide) => slide && typeof slide === 'object')
        .map((slide) => ({
            ...slide,
            title: String(slide.title || slide.baslik || slide.name || 'Basliksiz Slide'),
            xp: Number.isFinite(Number(slide.xp)) ? Number(slide.xp) : Number(slide.point || slide.points || 0),
            kind: String(slide.kind || slide.type || 'topic'),
            interaction_type: String(slide.interaction_type || slide.interactionType || 'none'),
            content: String(slide.content || slide.text || slide.body || slide.description || ''),
            instructions: String(slide.instructions || slide.instruction || ''),
            image_url: String(slide.image_url || slide.imageUrl || slide.image || ''),
            video_url: String(slide.video_url || slide.videoUrl || slide.video || ''),
            file_url: String(slide.file_url || slide.fileUrl || slide.file || ''),
            code: String(slide.code || slide.html || slide.source_code || slide.script || ''),
            question_prompt: String(slide.question_prompt || slide.prompt || slide.questionText || ''),
            points: Number.isFinite(Number(slide.points)) ? Number(slide.points) : 5,
            time_limit: Number.isFinite(Number(slide.time_limit)) ? Number(slide.time_limit) : 10,
            double_points: !!(slide.double_points ?? slide.doublePoints),
            question: slide.question && typeof slide.question === 'object'
                ? slide.question
                : (slide.question_data && typeof slide.question_data === 'object'
                    ? slide.question_data
                    : (slide.quiz && typeof slide.quiz === 'object'
                        ? slide.quiz
                        : { options: [], pairs: [], items: [] })),
        }));
    if (!state.theme_template) state.theme_template = 'default';
    if (!state.cover_image && existingCoverUrl) state.cover_image = existingCoverUrl;
    if (state.cover_image_data) state.cover_image_data = '';
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
        title: 'Kapak Görseli Kırp',
        aspectRatio: 16 / 9,
        onApply: null,
        target: { hiddenId: '', previewId: '', sourceInputId: '' },
    };

    function ensureSlide() {
        if (state.slides.length === 0) {
            state.slides.push({
                title: 'Basliksiz Slide',
                layout: 'auto',
                xp: 0,
                kind: 'topic',
                interaction_type: 'none',
                points: 5,
                time_limit: 10,
                double_points: false,
                content: '',
                instructions: '',
                image_url: '',
                video_url: '',
                file_url: '',
                code: '',
                question_prompt: '',
                layout_meta: {},
                question: { options: [], pairs: [], items: [] }
            });
        }
    }
    function escapeHtml(v) {
        return (v || '').replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; });
    }
    function insertTextAtCursor(textarea, before, after = '', fallback = '') {
        if (!textarea) return;
        const value = textarea.value || '';
        const start = textarea.selectionStart ?? value.length;
        const end = textarea.selectionEnd ?? value.length;
        const selected = value.slice(start, end);
        const insert = selected ? before + selected + after : (fallback || (before + after));
        textarea.value = value.slice(0, start) + insert + value.slice(end);
        const caret = start + insert.length;
        textarea.focus();
        textarea.setSelectionRange(caret, caret);
    }
    function bindRichTextToolbar(toolbarSelector, editorId, hiddenId, options = {}) {
        const toolbar = typeof toolbarSelector === 'string' ? document.querySelector(toolbarSelector) : toolbarSelector;
        const editor = typeof editorId === 'string' ? document.getElementById(editorId) : editorId;
        const hidden = hiddenId ? document.getElementById(hiddenId) : null;
        if (!toolbar || !editor) return;
        const sync = () => {
            if (hidden) hidden.value = editor.innerHTML || '';
            saveCurrent();
        };
        const applyStyle = (tag, value) => {
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;
            document.execCommand(tag, false, value || null);
            sync();
        };
        toolbar.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-text-action]');
            if (!btn) return;
            e.preventDefault();
            const action = String(btn.getAttribute('data-text-action') || '');
            editor.focus();
            if (action === 'bold') return applyStyle('bold');
            if (action === 'italic') return applyStyle('italic');
            if (action === 'underline') return applyStyle('underline');
            if (action === 'heading') return applyStyle('formatBlock', 'h1');
            if (action === 'subheading') return applyStyle('formatBlock', 'h2');
            if (action === 'bullet') return applyStyle('insertUnorderedList');
            if (action === 'numbered') return applyStyle('insertOrderedList');
            if (action === 'quote') return applyStyle('formatBlock', 'blockquote');
            if (action === 'divider') return applyStyle('insertHorizontalRule');
        });
        const fontSelect = options.fontSelectId ? document.getElementById(options.fontSelectId) : null;
        const sizeSelect = options.sizeSelectId ? document.getElementById(options.sizeSelectId) : null;
        const colorInput = options.colorInputId ? document.getElementById(options.colorInputId) : null;
        fontSelect?.addEventListener('change', () => {
            editor.focus();
            document.execCommand('fontName', false, fontSelect.value);
            sync();
        });
        sizeSelect?.addEventListener('change', () => {
            editor.focus();
            const sizeMap = { '12px': '2', '14px': '3', '16px': '4', '18px': '5', '20px': '6', '24px': '7', '28px': '7' };
            document.execCommand('fontSize', false, sizeMap[sizeSelect.value] || '3');
            sync();
        });
        colorInput?.addEventListener('input', () => {
            editor.focus();
            document.execCommand('foreColor', false, colorInput.value);
            sync();
        });
        editor.addEventListener('input', sync);
        editor.addEventListener('blur', sync);
    }
    function sanitizeForSave(value) {
        if (Array.isArray(value)) {
            return value.map((item) => sanitizeForSave(item));
        }
        if (!value || typeof value !== 'object') {
            return value;
        }
        const out = {};
        Object.entries(value).forEach(([key, item]) => {
            out[key] = sanitizeForSave(item);
        });
        return out;
    }
    function dataUrlToFile(dataUrl, filename = 'media.png') {
        const raw = String(dataUrl || '').trim();
        const match = raw.match(/^data:(image\/(png|jpeg|jpg|webp));base64,(.*)$/i);
        if (!match) return null;
        const mime = String(match[1] || 'image/png').toLowerCase();
        const base64 = String(match[3] || '');
        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i += 1) {
            bytes[i] = binary.charCodeAt(i);
        }
        try {
            return new File([bytes], filename, { type: mime });
        } catch (_) {
            return new Blob([bytes], { type: mime });
        }
    }
    async function uploadImageDataUrl(dataUrl, filename = 'media.png') {
        const raw = String(dataUrl || '').trim();
        if (!/^data:image\/(png|jpeg|jpg|webp);base64,/i.test(raw)) {
            return raw;
        }
        const mediaFile = dataUrlToFile(raw, filename);
        if (!mediaFile) {
            return raw;
        }
        const formData = new FormData();
        formData.append('media', mediaFile, filename || 'media.png');
        const response = await fetch(@json(route('courses.upload-media')), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: formData,
        });
        if (!response.ok) {
            let detail = '';
            try {
                const json = await response.json();
                detail = json?.message || Object.values(json?.errors || {}).flat().join(' ') || '';
            } catch (_) {}
            throw new Error(detail || 'Görsel sunucuya yüklenemedi.');
        }
        const json = await response.json();
        return String(json.url || json.path || raw);
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
            const shapes = ['▲','◆','●','■','⬟','•'];
            const colors = ['sq-red','sq-blue','sq-yellow','sq-green','sq-blue','sq-red'];
            questionEditor.innerHTML = box(`<div class="sq-answers">${options.map((opt, i) => `
                <div data-q-row="mc" class="sq-answer-card">
                    <span class="sq-shape ${colors[i]}">${shapes[i]}</span>
                    <input data-role="text" type="text" placeholder="Seçenek ${i + 1}" value="${escapeHtml(opt.text || '')}">
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
                        <span class="sq-shape sq-blue">◆</span>
                        <span>Doğru</span>
                        <input type="radio" name="q_tf_correct" value="true" ${correctTrue ? 'checked' : ''} style="width:18px;height:18px">
                    </label>
                    <label class="sq-answer-card" style="cursor:pointer">
                        <span class="sq-shape sq-red">▲</span>
                        <span>Yanlış</span>
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
        if (lessonTitle && !lessonTitle.value.trim()) {
            lessonTitle.value = 'Örnek Ders: Layout Galerisi';
        }
        hName.value = lessonTitle.value || 'Örnek Ders: Layout Galerisi';
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
    function getFieldValue(field, fallback = '') {
        return field && typeof field.value !== 'undefined' ? (field.value || fallback) : fallback;
    }
    function setFieldValue(field, value) {
        if (field && typeof field.value !== 'undefined') field.value = value;
    }
    function saveCurrent() {
        ensureSlide();
        const s = state.slides[active];
        s.title = getFieldValue(fields.title, 'Basliksiz Slide');
        s.layout = getFieldValue(fields.layout, 'auto');
        s.xp = Math.max(0, Math.min(500, parseInt(getFieldValue(fields.xp, '0') || '0', 10) || 0));
        s.content = contentEditor ? contentEditor.innerHTML || '' : getFieldValue(fields.content, '');
        s.instructions = getFieldValue(fields.instructions, '');
        s.code = getFieldValue(fields.code, '');
        s.kind = getFieldValue(fields.kind, 'topic');
        s.interaction_type = getFieldValue(fields.interaction_type, 'none');
        s.question_prompt = getFieldValue(fields.question_prompt, '');
        s.points = parseInt(getFieldValue(fields.points, '5') || '5', 10);
        s.time_limit = parseInt(getFieldValue(fields.time_limit, '10') || '10', 10);
        s.double_points = !!(fields.double_points && fields.double_points.checked);
        s.layout_meta = readLayoutMeta();
        s.question = readQuestionFromUI(s.interaction_type);
        if (currentSlideXpBadge) currentSlideXpBadge.textContent = 'Slide XP: ' + s.xp;
        state.lesson_title = lessonTitle.value || '';
        state.category = lessonCategory.value || 'Kodlama';
        state.difficulty = lessonDifficulty.value || 'Kolay';
        state.lesson_description = lessonDescription?.value || '';
        state.theme_template = themeTemplateSelect ? (themeTemplateSelect.value || 'default') : (state.theme_template || 'default');
        state.global_theme_css = globalThemeCss ? globalThemeCss.value || '' : (state.global_theme_css || '');
        state.cover_image = normalizeCoverStoragePath(state.cover_image || '');
        delete state.cover_image_data;
        state.curriculum = {
            title: curriculum.title.value || '',
            lesson_number: Math.max(1, parseInt(curriculum.lessonNumber.value || '1', 10) || 1),
            konu: curriculum.topic.value || '',
            kazanimlar: (curriculum.outcomes.value || '').split(/\r?\n/).map(v => v.trim()).filter(Boolean),
            etkinlikler: (curriculum.activities.value || '').split(/\r?\n/).map(v => v.trim()).filter(Boolean),
            progress: Math.max(0, Math.min(100, parseInt(curriculum.progress.value || '0', 10) || 0)),
        };
        syncHiddenInputs();
        const serializableState = sanitizeForSave(state);
        payloadInput.value = JSON.stringify(serializableState);
        if (shouldPersistDraft) {
            try {
                localStorage.setItem(draftKey, payloadInput.value);
            } catch (_) {}
        }
    }
    function loadCurrent() {
        ensureSlide();
        const s = state.slides[active];
        setFieldValue(fields.title, s.title || '');
        setFieldValue(fields.layout, s.layout || 'auto');
        setFieldValue(fields.xp, Number.isFinite(Number(s.xp)) ? Number(s.xp) : 0);
        if (contentEditor) contentEditor.innerHTML = s.content || '';
        if (fields.content) fields.content.value = s.content || '';
        setFieldValue(fields.instructions, s.instructions || '');
        setFieldValue(fields.code, s.code || '');
        setFieldValue(fields.kind, s.kind || 'topic');
        setFieldValue(fields.interaction_type, s.interaction_type || 'none');
        setFieldValue(fields.question_prompt, s.question_prompt || '');
        setFieldValue(fields.points, s.points || 5);
        setFieldValue(fields.time_limit, s.time_limit || 10);
        if (fields.double_points) fields.double_points.checked = !!s.double_points;
        renderLayoutHelp(getFieldValue(fields.layout, 'auto'));
        renderLayoutEditor(getFieldValue(fields.layout, 'auto'), s.layout_meta || {});
        renderQuestionEditor(getFieldValue(fields.interaction_type, 'none'), s.question || {});
        setActiveTab(inferTabForSlide(s));
        lessonCategory.value = state.category || 'Kodlama';
        lessonDifficulty.value = state.difficulty || 'Kolay';
        if (lessonDescription) lessonDescription.value = state.lesson_description || '';
        if (themeTemplateSelect) {
            const templateKey = state.theme_template || 'default';
            themeTemplateSelect.value = templateKey;
        }
        if (globalThemeCss) {
            globalThemeCss.value = state.global_theme_css || '';
        }
        const url = normalizeCoverUrl(state.cover_image || '');
        if (coverImagePreviewBox) {
            coverImagePreviewBox.style.display = url ? 'block' : 'none';
            coverImagePreviewBox.style.backgroundImage = 'none';
        }
        if (coverImagePreview) {
            coverImagePreview.src = url;
            coverImagePreview.style.display = url ? 'block' : 'none';
            coverImagePreview.alt = url ? 'Kapak önizleme' : 'Kapak önizleme yok';
            coverImagePreview.onerror = () => {
                if (coverImagePreviewBox) {
                    coverImagePreviewBox.style.display = 'none';
                }
            };
        }
        if (coverImagePathLabel) {
            coverImagePathLabel.textContent = url ? ('Kapak yolu: ' + String(state.cover_image || url)) : 'Kapak yolu henüz yok.';
        }
        if (coverImageRemove) {
            coverImageRemove.style.display = url ? 'inline-flex' : 'none';
        }
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
    function normalizeCoverUrl(url) {
        const raw = String(url || '').trim();
        if (!raw) return '';
        if (raw.startsWith('blob:')) return raw;
        if (/^https?:\/\//i.test(raw)) return raw;
        const base = String(appBaseUrl || '').replace(/\/+$/, '');
        if (raw.startsWith('/kapak-gorseli/')) return base + raw;
        if (raw.startsWith('kapak-gorseli/')) return base + '/' + raw;
        if (raw.startsWith('/course-covers/')) return base + '/kapak-gorseli/' + raw.replace(/^\/course-covers\//i, '');
        if (raw.startsWith('course-covers/')) return base + '/kapak-gorseli/' + raw.replace(/^course-covers\//i, '');
        if (raw.startsWith('/storage/course-covers/')) return base + '/kapak-gorseli/' + raw.replace(/^\/storage\/course-covers\//i, '');
        if (raw.startsWith('storage/course-covers/')) return base + '/kapak-gorseli/' + raw.replace(/^storage\/course-covers\//i, '');
        if (raw.startsWith('storage/')) return base + '/' + raw.replace(/^storage\//, '');
        return raw;
    }
    function normalizeCoverStoragePath(url) {
        const raw = String(url || '').trim();
        if (!raw) return '';
        if (raw.startsWith('blob:')) return raw;
        let value = raw;
        if (/^https?:\/\//i.test(value)) {
            try {
                const parsed = new URL(value);
                value = parsed.pathname || '';
            } catch (_) {}
        }
        value = value.replace(/^\/+/g, '');
        value = value.replace(/^storage\//i, '');
        const match = value.match(/(?:^|\/)(?:course-covers|kapak-gorseli)\/([^/?#]+)/i);
        if (match) return 'kapak-gorseli/' + match[1];
        if (value.startsWith('kapak-gorseli/')) return value;
        if (value.startsWith('course-covers/')) return value.replace(/^course-covers\//i, 'kapak-gorseli/');
        return value;
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
    function layoutDefinition(layout) {
        const defs = {
            split: {
                title: 'Split',
                badge: '2 alan',
                desc: 'Ekran ikiye bölünür. Sol ve sağ alanlara ayrı ayrı metin, görsel, video veya kod yerleştirilir.',
                wireframe: '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px"><div style="height:120px;border-radius:12px;background:#dbeafe"></div><div style="height:120px;border-radius:12px;background:#bfdbfe"></div></div>',
            },
            image: {
                title: 'Image Focus',
                badge: 'Görsel',
                desc: 'Görsel ana öğedir. Yanında kısa metin ve açıklama kutuları bulunur.',
                wireframe: '<div style="height:120px;border-radius:14px;background:#bfdbfe"></div><div style="height:14px;border-radius:8px;background:#eff6ff;width:60%;margin-top:10px"></div>',
            },
        };
        return defs[layout] || defs.split;
    }
    function renderLayoutHelp(layout) {
        const def = layoutDefinition(layout);
        if (layoutHelpTitle) layoutHelpTitle.textContent = def.title;
        if (layoutHelpBadge) layoutHelpBadge.textContent = def.badge;
        if (layoutHelpDesc) layoutHelpDesc.textContent = def.desc;
        if (layoutWireframe) layoutWireframe.innerHTML = def.wireframe;
    }
    function readLayoutMeta() {
        const layout = String(fields.layout?.value || 'auto');
        const meta = { kind: layout };
        if (layout === 'split') {
            const splitRatio = String(document.getElementById('layout_split_ratio')?.value || '50-50');
            meta.left = {
                type: document.getElementById('layout_left_type')?.value || 'text',
                text: document.getElementById('layout_left_text')?.value || '',
                image_url: document.getElementById('layout_left_image')?.value || '',
                video_url: document.getElementById('layout_left_video')?.value || '',
            };
            meta.right = {
                type: document.getElementById('layout_right_type')?.value || 'image',
                text: document.getElementById('layout_right_text')?.value || '',
                image_url: document.getElementById('layout_right_image')?.value || '',
                video_url: document.getElementById('layout_right_video')?.value || '',
            };
            meta.split_ratio = splitRatio;
        } else if (layout === 'hero' || layout === 'image') {
            meta.media = {
                type: document.getElementById('layout_media_type')?.value || 'image',
                order: document.getElementById('layout_media_order')?.value || 'image-text',
                text: document.getElementById('layout_media_text')?.value || '',
                html: document.getElementById('layout_media_html')?.value || '',
                image_url: document.getElementById('layout_media_image')?.value || '',
                video_url: document.getElementById('layout_media_video')?.value || '',
            };
        }
        return meta;
    }
    function renderLayoutEditor(layout, meta) {
        const def = layoutDefinition(layout);
        if (!layoutEditorHint) return;
        if (layout !== 'split') {
            if (layout === 'hero' || layout === 'image') {
                layoutEditorHint.textContent = def.desc + ' Görsel için dosya seçebilir, zengin metin için araç çubuğunu kullanabilirsiniz.';
                const media = meta?.media || {};
                layoutEditorFields.innerHTML = `
                    <div style="border:1px solid #dbe5f2;border-radius:12px;padding:12px;background:#f8fbff">
                        <strong style="display:block;margin-bottom:8px">Ana İçerik</strong>
                        <label>Alan Tipi</label>
                        <select id="layout_media_type">
                            <option value="image" ${media.type === 'image' ? 'selected' : ''}>Görsel</option>
                            <option value="video" ${media.type === 'video' ? 'selected' : ''}>Video</option>
                            <option value="text" ${media.type === 'text' ? 'selected' : ''}>Metin</option>
                        </select>
                        <label style="margin-top:8px">Yerleşim Sırası</label>
                        <select id="layout_media_order">
                            <option value="image-text" ${String(media.order || 'image-text') === 'image-text' ? 'selected' : ''}>Görsel üstte, metin altta</option>
                            <option value="text-image" ${String(media.order || 'image-text') === 'text-image' ? 'selected' : ''}>Metin üstte, görsel altta</option>
                        </select>
                        <div class="rich-toolbar" data-rich-toolbar="media" style="display:flex;flex-wrap:wrap;gap:8px;margin:10px 0 8px;padding:10px;border:1px solid #dbe5f2;border-radius:14px;background:#fff">
                            <button type="button" class="btn" data-text-action="bold"><strong>B</strong></button>
                            <button type="button" class="btn" data-text-action="italic"><em>I</em></button>
                            <button type="button" class="btn" data-text-action="underline"><u>U</u></button>
                            <select id="layout_media_font" style="min-width:170px">
                                <option value="inherit">Yazı Tipi</option>
                                <option value="'Inter', sans-serif">Inter</option>
                                <option value="Georgia, serif">Georgia</option>
                                <option value="'Times New Roman', serif">Times New Roman</option>
                                <option value="Consolas, monospace">Consolas</option>
                                <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                            </select>
                            <select id="layout_media_size" style="min-width:120px">
                                <option value="">Boyut</option>
                                <option value="12px">12</option>
                                <option value="14px">14</option>
                                <option value="16px">16</option>
                                <option value="18px">18</option>
                                <option value="20px">20</option>
                                <option value="24px">24</option>
                                <option value="28px">28</option>
                            </select>
                            <input id="layout_media_color" type="color" value="#0f172a" title="Yazı rengi">
                            <button type="button" class="btn" data-text-action="heading">H1</button>
                            <button type="button" class="btn" data-text-action="subheading">H2</button>
                            <button type="button" class="btn" data-text-action="bullet">•</button>
                            <button type="button" class="btn" data-text-action="numbered">1.</button>
                            <button type="button" class="btn" data-text-action="quote">“”</button>
                        </div>
                        <div id="layout_media_text" contenteditable="true" spellcheck="false" style="min-height:160px;padding:14px;border:1px solid #dbe5f2;border-radius:12px;background:#fff;line-height:1.7;color:#0f172a;font-family:inherit">${media.html || escapeHtml(media.text || '')}</div>
                        <input id="layout_media_html" type="hidden" value="${escapeHtml(media.html || '')}">
                        <label style="margin-top:8px">Görsel Dosyası</label>
                        <input id="layout_media_image_file" type="file" accept="image/*">
                        <input id="layout_media_image" type="hidden" value="${escapeHtml(media.image_url || '')}">
                        <div id="layout_media_image_preview" style="margin-top:8px;${media.image_url ? '' : 'display:none;'}">
                            <img src="${escapeHtml(media.image_url || '')}" alt="" style="width:100%;max-height:220px;object-fit:cover;border-radius:12px;border:1px solid #dbe5f2">
                        </div>
                        <label style="margin-top:8px">Video Dosyası / URL</label>
                        <input id="layout_media_video_file" type="file" accept="video/*">
                        <input id="layout_media_video" type="hidden" value="${escapeHtml(media.video_url || '')}">
                        <small style="display:block;color:#64748b;margin-top:6px">Video seçildiğinde dosya yüklenir; URL girmenize gerek yoktur.</small>
                    </div>
                `;
                bindLayoutMediaFiles();
                bindRichTextToolbar('[data-rich-toolbar="media"]', 'layout_media_text', 'layout_media_html', {
                    fontSelectId: 'layout_media_font',
                    sizeSelectId: 'layout_media_size',
                    colorInputId: 'layout_media_color',
                });
                return;
            }
            layoutEditorHint.textContent = def.desc + ' Bu düzen için özel alan açılmaz.';
            layoutEditorFields.innerHTML = '';
            return;
        }
        layoutEditorHint.textContent = 'Split seçildiğinde her iki taraf ayrı blok olarak düzenlenir. Metin alanları için araç çubuğu, görsel alanları için kırpma desteği bulunur.';
        const left = meta?.left || {};
        const right = meta?.right || {};
        const splitRatio = String(meta?.split_ratio || '50-50');
        layoutEditorFields.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap">
                <label style="margin:0">Alan Oranı</label>
                <select id="layout_split_ratio" style="max-width:240px">
                    <option value="30-70" ${splitRatio === '30-70' ? 'selected' : ''}>Sol %30 - Sağ %70</option>
                    <option value="50-50" ${splitRatio === '50-50' ? 'selected' : ''}>Sol %50 - Sağ %50</option>
                    <option value="70-30" ${splitRatio === '70-30' ? 'selected' : ''}>Sol %70 - Sağ %30</option>
                </select>
            </div>
            <div class="split-layout-editor" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:14px;align-items:stretch">
                <div class="split-layout-panel" style="border:1px solid #dbe5f2;border-radius:18px;padding:14px;background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%);display:flex;flex-direction:column;min-height:420px">
                    <label>Alan Tipi</label>
                    <select id="layout_left_type">
                        <option value="text" ${left.type === 'text' ? 'selected' : ''}>Metin</option>
                        <option value="image" ${left.type === 'image' ? 'selected' : ''}>Görsel</option>
                        <option value="video" ${left.type === 'video' ? 'selected' : ''}>Video</option>
                        <option value="code" ${left.type === 'code' ? 'selected' : ''}>Kod</option>
                    </select>
                    <div class="split-text-toolbar" data-split-toolbar="left" style="display:${left.type === 'text' ? 'flex' : 'none'};flex-wrap:wrap;gap:8px;margin:10px 0 8px;padding:10px;border:1px solid #dbe5f2;border-radius:14px;background:#f8fbff">
                        <button type="button" class="btn" data-text-action="bold"><strong>B</strong></button>
                        <button type="button" class="btn" data-text-action="italic"><em>I</em></button>
                        <button type="button" class="btn" data-text-action="heading">H1</button>
                        <button type="button" class="btn" data-text-action="subheading">H2</button>
                        <button type="button" class="btn" data-text-action="bullet">•</button>
                        <button type="button" class="btn" data-text-action="numbered">1.</button>
                        <button type="button" class="btn" data-text-action="quote">“”</button>
                        <button type="button" class="btn" data-text-action="divider">---</button>
                    </div>
                    <label style="margin-top:8px">Metin</label>
                    <textarea id="layout_left_text" rows="8" style="min-height:160px;flex:1">${escapeHtml(left.text || '')}</textarea>
                    <div class="split-media-actions" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:10px">
                        <button type="button" class="btn" data-split-media-target="left" data-split-media-kind="image">Görsel Seç</button>
                        <button type="button" class="btn" data-split-media-target="left" data-split-media-kind="video">Video Seç</button>
                        <button type="button" class="btn" data-split-media-target="left" data-split-media-kind="clear">Temizle</button>
                    </div>
                    <input id="layout_left_image_file" type="file" accept="image/*" style="display:none">
                    <input id="layout_left_video_file" type="file" accept="video/*" style="display:none">
                    <input id="layout_left_image" type="hidden" value="${escapeHtml(left.image_url || '')}">
                    <input id="layout_left_video" type="hidden" value="${escapeHtml(left.video_url || '')}">
                    <div id="layout_left_image_preview" style="margin-top:12px;${left.image_url ? '' : 'display:none;'}">
                        <img src="${escapeHtml(left.image_url || '')}" alt="" style="width:100%;max-height:240px;object-fit:cover;border-radius:16px;border:1px solid #dbe5f2">
                    </div>
                    <div id="layout_left_video_preview" style="margin-top:12px;${left.video_url ? '' : 'display:none;'}">
                        <video src="${escapeHtml(left.video_url || '')}" controls style="width:100%;max-height:240px;border-radius:16px;border:1px solid #dbe5f2;background:#000"></video>
                    </div>
                </div>
                <div class="split-layout-panel" style="border:1px solid #dbe5f2;border-radius:18px;padding:14px;background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%);display:flex;flex-direction:column;min-height:420px">
                    <label>Alan Tipi</label>
                    <select id="layout_right_type">
                        <option value="text" ${right.type === 'text' ? 'selected' : ''}>Metin</option>
                        <option value="image" ${right.type === 'image' ? 'selected' : ''}>Görsel</option>
                        <option value="video" ${right.type === 'video' ? 'selected' : ''}>Video</option>
                        <option value="code" ${right.type === 'code' ? 'selected' : ''}>Kod</option>
                    </select>
                    <div class="split-text-toolbar" data-split-toolbar="right" style="display:${right.type === 'text' ? 'flex' : 'none'};flex-wrap:wrap;gap:8px;margin:10px 0 8px;padding:10px;border:1px solid #dbe5f2;border-radius:14px;background:#f8fbff">
                        <button type="button" class="btn" data-text-action="bold"><strong>B</strong></button>
                        <button type="button" class="btn" data-text-action="italic"><em>I</em></button>
                        <button type="button" class="btn" data-text-action="heading">H1</button>
                        <button type="button" class="btn" data-text-action="subheading">H2</button>
                        <button type="button" class="btn" data-text-action="bullet">•</button>
                        <button type="button" class="btn" data-text-action="numbered">1.</button>
                        <button type="button" class="btn" data-text-action="quote">“”</button>
                        <button type="button" class="btn" data-text-action="divider">---</button>
                    </div>
                    <label style="margin-top:8px">Metin</label>
                    <textarea id="layout_right_text" rows="8" style="min-height:160px;flex:1">${escapeHtml(right.text || '')}</textarea>
                    <div class="split-media-actions" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:10px">
                        <button type="button" class="btn" data-split-media-target="right" data-split-media-kind="image">Görsel Seç</button>
                        <button type="button" class="btn" data-split-media-target="right" data-split-media-kind="video">Video Seç</button>
                        <button type="button" class="btn" data-split-media-target="right" data-split-media-kind="clear">Temizle</button>
                    </div>
                    <input id="layout_right_image_file" type="file" accept="image/*" style="display:none">
                    <input id="layout_right_video_file" type="file" accept="video/*" style="display:none">
                    <input id="layout_right_image" type="hidden" value="${escapeHtml(right.image_url || '')}">
                    <input id="layout_right_video" type="hidden" value="${escapeHtml(right.video_url || '')}">
                    <div id="layout_right_image_preview" style="margin-top:12px;${right.image_url ? '' : 'display:none;'}">
                        <img src="${escapeHtml(right.image_url || '')}" alt="" style="width:100%;max-height:240px;object-fit:cover;border-radius:16px;border:1px solid #dbe5f2">
                    </div>
                    <div id="layout_right_video_preview" style="margin-top:12px;${right.video_url ? '' : 'display:none;'}">
                        <video src="${escapeHtml(right.video_url || '')}" controls style="width:100%;max-height:240px;border-radius:16px;border:1px solid #dbe5f2;background:#000"></video>
                    </div>
                </div>
            </div>
        `;
        bindLayoutSplitFiles();
        bindMarkdownToolbar('[data-split-toolbar="left"]', 'layout_left_text');
        bindMarkdownToolbar('[data-split-toolbar="right"]', 'layout_right_text');
        updateSplitPanelVisibility('left');
        updateSplitPanelVisibility('right');
        document.getElementById('layout_split_ratio')?.addEventListener('change', saveCurrent);
    }
    bindRichTextToolbar('[data-rich-toolbar="content"]', 'slide_content_editor', 'slide_content', {
        fontSelectId: 'slide_content_font',
        sizeSelectId: 'slide_content_size',
        colorInputId: 'slide_content_color',
    });
    function syncHiddenPreview(hiddenId, previewId, value) {
        const hidden = document.getElementById(hiddenId);
        const preview = document.getElementById(previewId);
        if (hidden) hidden.value = value || '';
        if (preview) {
            if (value) {
                preview.style.display = 'block';
                preview.innerHTML = '<img src="' + String(value).replace(/"/g, '&quot;') + '" alt="" style="width:100%;max-height:220px;object-fit:cover;border-radius:12px;border:1px solid #dbe5f2">';
            } else {
                preview.style.display = 'none';
                preview.innerHTML = '';
            }
        }
    }
    function bindMarkdownToolbar(toolbarSelector, textareaId) {
        const toolbar = typeof toolbarSelector === 'string' ? document.querySelector(toolbarSelector) : toolbarSelector;
        const textarea = typeof textareaId === 'string' ? document.getElementById(textareaId) : textareaId;
        if (!toolbar || !textarea) return;
        toolbar.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-text-action]');
            if (!btn) return;
            e.preventDefault();
            const action = String(btn.getAttribute('data-text-action') || '');
            if (action === 'bold') return insertTextAtCursor(textarea, '**', '**', '**Kalın metin**');
            if (action === 'italic') return insertTextAtCursor(textarea, '*', '*', '*Vurgulu metin*');
            if (action === 'heading') return insertTextAtCursor(textarea, '# ', '', '# Başlık');
            if (action === 'subheading') return insertTextAtCursor(textarea, '## ', '', '## Alt Başlık');
            if (action === 'bullet') return insertTextAtCursor(textarea, '- ', '', '- Madde');
            if (action === 'numbered') return insertTextAtCursor(textarea, '1. ', '', '1. Madde');
            if (action === 'quote') return insertTextAtCursor(textarea, '> ', '', '> Alıntı');
            if (action === 'code') return insertTextAtCursor(textarea, '```\n', '\n```', '```\nkod\n```');
            if (action === 'divider') return insertTextAtCursor(textarea, '\n---\n', '', '\n---\n');
        });
    }
    function setImageFromFile(fileInputId, hiddenId, previewId, options = {}) {
        const input = document.getElementById(fileInputId);
        if (!input) return;
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            if (options.crop) {
                openCoverCropModal(file, {
                    title: options.title || 'Görseli Kırp',
                    aspectRatio: options.aspectRatio || 16 / 9,
                    target: { hiddenId, previewId, sourceInputId: fileInputId },
                    onApply: async (dataUrl) => {
                        syncHiddenPreview(hiddenId, previewId, dataUrl);
                        const uploadedUrl = await uploadImageDataUrl(dataUrl, file.name || 'media.png');
                        syncHiddenPreview(hiddenId, previewId, uploadedUrl);
                        saveCurrent();
                    },
                });
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const data = String(reader.result || '');
                syncHiddenPreview(hiddenId, previewId, data);
                saveCurrent();
            };
            reader.readAsDataURL(file);
        });
    }
    function setVideoFromFile(fileInputId, hiddenId) {
        const input = document.getElementById(fileInputId);
        const hidden = document.getElementById(hiddenId);
        if (!input) return;
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            if (hidden) hidden.value = url;
            saveCurrent();
        });
    }
    function updateSplitPanelVisibility(prefix) {
        const type = document.getElementById(`layout_${prefix}_type`)?.value || 'text';
        const textWrap = document.querySelector(`[data-split-toolbar="${prefix}"]`);
        const textArea = document.getElementById(`layout_${prefix}_text`);
        const imagePreview = document.getElementById(`layout_${prefix}_image_preview`);
        const videoPreview = document.getElementById(`layout_${prefix}_video_preview`);
        if (textWrap) textWrap.style.display = type === 'text' ? 'flex' : 'none';
        if (textArea) textArea.style.display = type === 'text' ? 'block' : 'none';
        if (imagePreview) imagePreview.style.display = type === 'image' ? 'block' : 'none';
        if (videoPreview) videoPreview.style.display = type === 'video' ? 'block' : 'none';
    }
    function bindLayoutSplitFiles() {
        setImageFromFile('layout_left_image_file', 'layout_left_image', 'layout_left_image_preview', { crop: true, title: 'Sol Alan Görseli Kırp', aspectRatio: 1 });
        setVideoFromFile('layout_left_video_file', 'layout_left_video');
        setImageFromFile('layout_right_image_file', 'layout_right_image', 'layout_right_image_preview', { crop: true, title: 'Sağ Alan Görseli Kırp', aspectRatio: 1 });
        setVideoFromFile('layout_right_video_file', 'layout_right_video');
        ['left', 'right'].forEach((prefix) => {
            const select = document.getElementById(`layout_${prefix}_type`);
            select?.addEventListener('change', () => {
                updateSplitPanelVisibility(prefix);
                saveCurrent();
            });
            const textArea = document.getElementById(`layout_${prefix}_text`);
            textArea?.addEventListener('input', saveCurrent);
            const imageBtn = document.querySelector(`[data-split-media-target="${prefix}"][data-split-media-kind="image"]`);
            const videoBtn = document.querySelector(`[data-split-media-target="${prefix}"][data-split-media-kind="video"]`);
            const clearBtn = document.querySelector(`[data-split-media-target="${prefix}"][data-split-media-kind="clear"]`);
            imageBtn?.addEventListener('click', () => {
                if (select) select.value = 'image';
                updateSplitPanelVisibility(prefix);
                document.getElementById(`layout_${prefix}_image_file`)?.click();
            });
            videoBtn?.addEventListener('click', () => {
                if (select) select.value = 'video';
                updateSplitPanelVisibility(prefix);
                document.getElementById(`layout_${prefix}_video_file`)?.click();
            });
            clearBtn?.addEventListener('click', () => {
                syncHiddenPreview(`layout_${prefix}_image`, `layout_${prefix}_image_preview`, '');
                syncHiddenPreview(`layout_${prefix}_video`, `layout_${prefix}_video_preview`, '');
                if (select) select.value = 'text';
                const imageFile = document.getElementById(`layout_${prefix}_image_file`);
                const videoFile = document.getElementById(`layout_${prefix}_video_file`);
                if (imageFile) imageFile.value = '';
                if (videoFile) videoFile.value = '';
                updateSplitPanelVisibility(prefix);
                saveCurrent();
            });
            updateSplitPanelVisibility(prefix);
        });
    }
    function bindLayoutMediaFiles() {
        setImageFromFile('layout_media_image_file', 'layout_media_image', 'layout_media_image_preview', { crop: true, title: 'Ana Görseli Kırp', aspectRatio: 16 / 9 });
        setVideoFromFile('layout_media_video_file', 'layout_media_video');
    }
    function renderPreviewSlide() {
        ensureSlide();
        previewIndex = Math.max(0, Math.min(previewIndex, state.slides.length - 1));
        const s = state.slides[previewIndex] || {};
        const layout = String(s.layout || 'auto');
        const meta = s.layout_meta || {};
        const codeSrcdoc = s.code ? String(s.code) : '';
        const layoutPreview = (() => {
            if (layout === 'split') {
                const left = meta.left || {};
                const right = meta.right || {};
                const splitRatio = String(meta.split_ratio || '50-50');
                const splitColumns = splitRatio === '30-70'
                    ? '30% 70%'
                    : splitRatio === '70-30'
                        ? '70% 30%'
                        : '1fr 1fr';
                const normalizeMediaUrl = (value) => {
                    const raw = String(value || '').trim();
                    if (!raw) return '';
                    if (/^(?:data:|blob:|https?:\/\/)/i.test(raw)) return raw;
                    return String(appBaseUrl || '').replace(/\/+$/, '') + '/' + raw.replace(/^\/+/, '').replace(/^storage\//i, '');
                };
                const renderSplitPanel = (panel, accent, label) => {
                    const type = String(panel.type || 'text');
                    const text = escapeHtml(String(panel.text || ''));
                    const imageUrl = escapeHtml(normalizeMediaUrl(String(panel.image_url || '')));
                    const videoUrl = escapeHtml(String(panel.video_url || ''));
                    let body = '';
                    if (type === 'image' && imageUrl) {
                        body = `<div style="display:grid;gap:12px;align-content:center;width:100%;min-height:260px"><img src="${imageUrl}" alt="${label}" style="width:100%;max-height:56vh;object-fit:cover;border-radius:16px;border:1px solid ${accent}22"></div>`;
                    } else if (type === 'video' && videoUrl) {
                        body = `<div style="display:grid;gap:12px;align-content:center;width:100%;min-height:260px"><video src="${videoUrl}" controls style="width:100%;max-height:56vh;border-radius:16px;border:1px solid ${accent}22;background:#000"></video></div>`;
                    } else if (type === 'code' && codeSrcdoc !== '') {
                        body = `<iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="${codeSrcdoc}" style="min-height:260px;height:100%;max-height:56vh"></iframe>`;
                    } else {
                        body = `<div style="display:grid;gap:10px;align-content:center;min-height:260px;padding:8px 2px">${text ? `<div style="white-space:pre-wrap;line-height:1.8">${text}</div>` : `<div style="height:14px;border-radius:999px;background:${accent}22;width:82%"></div><div style="height:14px;border-radius:999px;background:${accent}18;width:68%"></div><div style="height:14px;border-radius:999px;background:${accent}14;width:54%"></div>`}</div>`;
                    }
                    return body;
                };
                return `
                    <div class="lesson-split" style="display:grid;margin:14px auto 0;grid-template-columns:${splitColumns};gap:18px">
                        <div class="lesson-card lesson-split-card" style="min-height:auto">
                            <div class="lesson-split-body">
                                ${renderSplitPanel(left, '#2563eb', 'Sol Alan')}
                            </div>
                        </div>
                        <div class="lesson-card lesson-split-card" style="min-height:auto">
                            <div class="lesson-split-body lesson-split-body--center">
                                ${renderSplitPanel(right, '#7c3aed', 'Sağ Alan')}
                            </div>
                        </div>
                    </div>
                `;
            }
            if (layout === 'hero') {
                const media = meta.media || {};
                return `
                    <div style="margin:14px 0;padding:18px;border-radius:18px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe">
                        <div style="height:18px;width:56%;border-radius:999px;background:#1d4ed8;margin-bottom:10px"></div>
                        <div style="height:12px;width:38%;border-radius:999px;background:#60a5fa;margin-bottom:18px"></div>
                        ${media.image_url ? `<img src="${escapeHtml(media.image_url)}" style="width:100%;height:130px;object-fit:cover;border-radius:16px;border:1px solid #bfdbfe">` : `<div style="height:130px;border-radius:16px;background:#93c5fd"></div>`}
                    </div>
                `;
            }
            if (layout === 'image') {
                const media = meta.media || {};
                const mediaOrder = String(media.order || 'image-text');
                const imageBlock = media.image_url ? `<img src="${escapeHtml(media.image_url)}" style="width:100%;height:100%;object-fit:cover;display:block">` : `<div style="width:100%;height:100%;background:#bfdbfe"></div>`;
                const textBlock = media.html ? `<div style="max-width:1100px;text-align:center;line-height:1.7">${media.html}</div>` : (media.text ? `<div style="max-width:900px;text-align:center;line-height:1.7">${escapeHtml(media.text)}</div>` : '');
                return `
                    <div style="margin:14px auto 0;max-width:min(92vw,1380px);min-width:320px;min-height:min(72vh,760px);padding:16px;border-radius:18px;background:#f8fbff;border:1px solid #dbe5f2;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:12px">
                        ${mediaOrder === 'text-image' ? textBlock : ''}
                        <div style="width:50%;aspect-ratio:16/6;overflow:hidden;border-radius:16px;border:1px solid #bfdbfe;background:#fff">
                            ${imageBlock}
                        </div>
                        ${mediaOrder === 'image-text' ? textBlock : ''}
                    </div>
                `;
            }
            return '';
        })();
        let html = '<h3>' + escapeHtml(s.title || 'Basliksiz Slide') + ' <span style="font-size:13px;color:#334155">| XP: ' + Number(s.xp || 0) + '</span></h3>';
        html += '<div style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-weight:800;font-size:13px;margin:6px 0 2px">' + escapeHtml((layout || 'auto').replace(/[-_]/g, ' ')) + '</div>';
        html += layoutPreview;
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
            html += '<p><b>Puan:</b> ' + p + ' | <b>Süre:</b> ' + Number(s.time_limit || 10) + ' sn</p>';
        }
        if (s.code) {
            const mergedCode = (themeCss ? ('<style>' + themeCss + '</style>') : '') + String(s.code || '');
            html += '<iframe id="preview_code_iframe" allow="camera *; microphone *; fullscreen *" style="width:100%;height:100%;min-height:58vh;border:1px solid #d1d5db;border-radius:8px;margin-top:6px" srcdoc="' + escapeHtml(mergedCode) + '"></iframe>';
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
        sel.h = sel.w / (coverCropState.aspectRatio || (16 / 9));
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
            s.h = s.w / (coverCropState.aspectRatio || (16 / 9));
            s.x = coverCropState.imgX + (coverCropState.imgW - s.w) / 2;
            s.y = coverCropState.imgY + (coverCropState.imgH - s.h) / 2;
        }
        clampSelection(s);
        renderCropUi();
    }

    function openCoverCropModal(file, options = {}) {
        if (!coverCropModal || !coverCropImage || !coverCropViewport) return;
        coverCropState.title = options.title || 'Kapak Görseli Kırp';
        coverCropState.aspectRatio = Number(options.aspectRatio) || (16 / 9);
        coverCropState.onApply = typeof options.onApply === 'function' ? options.onApply : null;
        coverCropState.target = options.target || { hiddenId: '', previewId: '', sourceInputId: '' };
        const modalTitle = coverCropModal.querySelector('.modal-head strong');
        if (modalTitle) modalTitle.textContent = coverCropState.title;
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
        const outH = Math.round(outW / (coverCropState.aspectRatio || (16 / 9)));
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

        let blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', 0.82));
        if (!blob) {
            blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.84));
        }
        if (!blob) return;
        const previewUrl = URL.createObjectURL(blob);
        const dataUrl = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(String(reader.result || ''));
            reader.onerror = () => reject(new Error('Kırpılmış görsel okunamadı.'));
            reader.readAsDataURL(blob);
        });
        const extension = blob.type === 'image/jpeg' ? 'jpg' : 'webp';
        const coverFile = new File([blob], 'cover.' + extension, { type: blob.type || 'image/webp' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(coverFile);

        if (coverCropState.onApply) {
            await coverCropState.onApply(dataUrl, blob, dataTransfer.files);
        } else {
            state.cover_image = '';
            if (coverImageData) {
                coverImageData.value = '';
            }
            if (coverImageFile) {
                coverImageFile.files = dataTransfer.files;
            }
            if (coverImagePreviewBox) {
                coverImagePreviewBox.style.display = 'block';
                coverImagePreviewBox.style.backgroundImage = 'none';
            }
            if (coverImagePreview) {
                coverImagePreview.src = previewUrl;
                coverImagePreview.style.display = 'block';
                coverImagePreview.onerror = () => {
                    if (coverImagePreviewBox) {
                        coverImagePreviewBox.style.display = 'none';
                    }
                };
            }
            if (coverImagePathLabel) {
                coverImagePathLabel.textContent = 'Kapak önizleme hazır. Kaydet dediğinde dosya yüklenecek.';
            }
            if (coverImageRemove) {
                coverImageRemove.style.display = 'inline-flex';
            }
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
        const ratio = coverCropState.aspectRatio || (16 / 9);
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
    fields.layout?.addEventListener('change', () => {
        renderLayoutHelp(fields.layout.value);
        renderLayoutEditor(fields.layout.value, readLayoutMeta());
        saveCurrent();
    });

    addBtn.addEventListener('click', () => {
        saveCurrent();
        state.slides.push({title: 'Yeni Slide', layout: 'image', layout_meta: {}, xp: 0, kind: 'topic', interaction_type: 'none', points: 5, time_limit: 10, double_points: false, question: {options: [], pairs: [], items: []}});
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

    function restoreAfterPreviewClose() {
        if (previewModal.classList.contains('open')) return;
        document.body.style.overflow = '';
    }
    previewModal.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => setTimeout(restoreAfterPreviewClose, 0));
    });
    previewModal.addEventListener('click', (e) => {
        if (e.target === previewModal) setTimeout(restoreAfterPreviewClose, 0);
    });
    document.body.style.overflow = '';

    Object.values(fields).filter(Boolean).forEach(el => el.addEventListener('input', () => { saveCurrent(); renderList(); }));
    lessonTitle.addEventListener('input', () => { saveCurrent(); });
    topClassSelect.addEventListener('change', saveCurrent);
    lessonCategory?.addEventListener('change', saveCurrent);
    lessonDifficulty?.addEventListener('change', saveCurrent);
    lessonDescription?.addEventListener('input', saveCurrent);
    themeTemplateSelect?.addEventListener('change', () => {
        applyThemePreset(themeTemplateSelect.value);
        saveCurrent();
        renderList();
    });
    globalThemeCss?.addEventListener('input', saveCurrent);
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
        if (coverImageData) {
            coverImageData.value = '';
        }
        state.cover_image = '';
        if (coverImagePreview) {
            coverImagePreview.src = '';
        }
        if (coverImagePreviewBox) {
            coverImagePreviewBox.style.display = 'none';
            coverImagePreviewBox.style.backgroundImage = 'none';
        }
        coverImageRemove.style.display = 'none';
        saveCurrent();
    });    fields.interaction_type.addEventListener('change', () => {
        renderQuestionEditor(fields.interaction_type.value, {});
        saveCurrent();
    });
    questionEditor.addEventListener('input', saveCurrent);
    questionEditor.addEventListener('change', saveCurrent);

    const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
    const tabPanels = Array.from(document.querySelectorAll('.builder-panel'));
    let activeTab = 'text';

    function setActiveTab(tab) {
        const targetTab = ['text', 'code', 'question'].includes(tab) ? tab : 'text';
        activeTab = targetTab;
        tabButtons.forEach((b) => {
            b.classList.toggle('active', String(b.dataset.tab || '') === targetTab);
        });
        tabPanels.forEach((p) => {
            p.style.display = (p.dataset.panel === targetTab ? 'block' : 'none');
        });
    }

    function inferTabForSlide(slide) {
        const s = slide || {};
        if ((String(s.interaction_type || '') !== 'none') || String(s.question_prompt || '').trim() !== '') {
            return 'question';
        }
        if (String(s.layout || '') === 'code' || String(s.code || '').trim() !== '') {
            return 'code';
        }
        return 'text';
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            setActiveTab(btn.dataset.tab);
        });
    });

    if (!isEditMode) {
        const defaultTab = tabButtons.find((btn) => btn.dataset.tab === 'text');
        if (defaultTab) {
            setActiveTab('text');
        }
    }

    try {
        ensureSlide();
        loadCurrent();
        renderList();        saveCurrent();
    } catch (e) {
        console.error('Builder init failed:', e);
    }

    setActiveTab(inferTabForSlide(state.slides[active] || {}));

    if (!shouldPersistDraft) {
        try { localStorage.removeItem(draftKey); } catch (_) {}
    }

    if (builderForm) {
        builderForm.addEventListener('submit', (e) => {
            const title = (lessonTitle?.value || '').trim();
            if (!title) {
                e.preventDefault();
                alert('Ders başlığı boş olamaz. Lütfen bir ders adı girin.');
                lessonTitle?.focus();
                return;
            }
            saveCurrent();
            const showUploadProgress = (percent, label) => {
                if (!uploadProgressWrap || !uploadProgressBar || !uploadProgressText) return;
                uploadProgressWrap.style.display = 'block';
                uploadProgressBar.style.width = Math.max(0, Math.min(100, percent)) + '%';
                uploadProgressText.textContent = label || ('%' + Math.max(0, Math.min(100, percent)));
            };
            const hideUploadProgress = () => {
                if (!uploadProgressWrap || !uploadProgressBar || !uploadProgressText) return;
                uploadProgressWrap.style.display = 'none';
                uploadProgressBar.style.width = '0%';
                uploadProgressText.textContent = '%0';
            };
            if (builderForm.method && builderForm.method.toLowerCase() === 'post' && typeof window.XMLHttpRequest !== 'undefined') {
                e.preventDefault();
                const formData = new FormData(builderForm);
                let notice = null;
                const clearNotice = () => {
                    if (notice && typeof appToastDismiss === 'function') {
                        appToastDismiss(notice);
                    }
                    notice = null;
                };
                const setNotice = (text) => {
                    if (typeof appToast !== 'function') return;
                    if (notice && typeof appToastDismiss === 'function') {
                        appToastDismiss(notice);
                    }
                    notice = appToast('warning', text, { sticky: true });
                };
                showUploadProgress(0, '%0');
                setNotice('Ders yükleniyor: %0');
                const xhr = new XMLHttpRequest();
                xhr.open((builderForm.getAttribute('method') || 'POST').toUpperCase(), builderForm.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.upload.addEventListener('progress', (evt) => {
                    if (!evt.lengthComputable) {
                        showUploadProgress(0, 'Yükleniyor...');
                        setNotice('Ders yükleniyor...');
                        return;
                    }
                    const percent = Math.max(0, Math.min(100, Math.round((evt.loaded / evt.total) * 100)));
                    showUploadProgress(percent, '%' + percent);
                    setNotice(`Ders yükleniyor: %${percent}`);
                });
                xhr.addEventListener('load', () => {
                    clearNotice();
                    hideUploadProgress();
                    if (xhr.status >= 200 && xhr.status < 400) {
                        window.location.href = @json(route('courses.index'));
                        return;
                    }
                    alert('Ders kaydedilemedi. Lütfen tekrar deneyin.');
                });
                xhr.addEventListener('error', () => {
                    clearNotice();
                    hideUploadProgress();
                    alert('Ders yüklenirken ağ hatası oluştu.');
                });
                xhr.addEventListener('abort', () => {
                    clearNotice();
                    hideUploadProgress();
                });
                xhr.send(formData);
                return;
            }
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
