@extends('layout.app')
@section('title','Dersler')
@section('content')
@php
    $categories = ['Tumu', 'Kodlama', 'Tasarim', 'Elektrik', 'Robotik', 'Teorik', 'Oyun', 'Yapay Zeka'];
    $activeCategory = request('category', 'Tumu');
    $allClasses = \App\Models\SchoolClass::orderBy('grade_level')->orderBy('name')->orderBy('section')->get();
    $bulkAssignableClasses = ($isTeacher ?? false)
        ? ($teacherVisibleClasses ?? collect())
        : $allClasses;
@endphp

<style>
    .course-topbar {
        display: grid;
        gap: 0.9rem;
    }
    .course-category-strip {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.15rem;
        scrollbar-width: none;
    }
    .course-category-strip::-webkit-scrollbar {
        display: none;
    }
    .course-category-pill {
        flex: 0 0 auto;
        border-radius: 16px;
        padding: 0.85rem 1.1rem;
        font-size: 1.05rem;
        line-height: 1;
        color: #475569;
        text-decoration: none;
        transition: .15s ease;
    }
    .course-category-pill.active {
        background: #ede9fe;
        color: #4c1d95;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(76,29,149,.10);
    }
    .course-search-layout {
        display: flex;
        gap: .5rem;
        align-items: stretch;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: .15rem;
    }
    .course-search-layout input[type="text"] {
        height: 3.25rem;
        flex: 1 1 320px;
        min-width: 260px;
        border-radius: 14px;
        border: 1px solid #d1d5db;
        background: #fff;
        padding: 0 .9rem;
        font-size: .98rem;
        color: #1f2937;
        outline: none;
    }
    .course-search-layout select {
        height: 3.25rem;
        flex: 0 0 240px;
        min-width: 240px;
        border-radius: 14px;
        border: 1px solid #d1d5db;
        background: #fff;
        padding: 0 .9rem;
        font-size: .98rem;
        color: #1f2937;
        outline: none;
    }
    .course-search-layout select.course-select-narrow {
        flex: 0 0 130px;
        min-width: 130px;
    }
    .course-search-layout select.course-select-fit {
        flex: 0 1 auto;
        width: auto;
        min-width: 0;
        padding-right: 2.2rem;
    }
    .course-search-layout input[type="text"]:focus {
        border-color: #4c1d95;
        box-shadow: 0 0 0 3px rgba(76,29,149,.12);
    }
    .course-favorites-filter-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 3.25rem;
        width: 3.25rem;
        flex: 0 0 3.25rem;
        border-radius: 14px;
        border: 1px solid #d1d5db;
        background: #fff;
        font-size: 1.15rem;
        font-weight: 700;
        color: #1f2937;
        cursor: pointer;
        user-select: none;
    }
    .course-favorites-filter-toggle:has(input:checked) {
        border-color: #ef4444;
        background: #fef2f2;
        color: #b91c1c;
    }
    .course-favorites-filter-toggle input[type="checkbox"] {
        width: 0;
        height: 0;
        opacity: 0;
        position: absolute;
    }
    .course-favorites-filter-toggle span {
        color: #ef4444;
        font-size: 1.05rem;
        line-height: 1;
    }
    .course-action-grid {
        display: flex;
        gap: .35rem;
        flex: 0 0 auto;
        flex-wrap: nowrap;
        align-items: stretch;
    }
    .course-action-grid a,
    .course-action-grid button {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.35rem;
        padding: .3rem .65rem;
        border-radius: 10px;
        font-size: .78rem;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        border: 0;
        cursor: pointer;
        text-align: center;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(15,23,42,.12);
        transition: filter .15s ease, transform .15s ease;
    }
    .course-action-grid a:hover,
    .course-action-grid button:hover {
        filter: brightness(1.06);
        transform: translateY(-1px);
    }
    .course-action-grid .btn-create,
    .course-action-grid .btn-upload,
    .course-action-grid .btn-download {
        min-width: 88px;
    }
    .course-action-grid .btn {
        min-width: 80px;
    }
    .btn-create { background: #7c4fe0; }
    .btn-upload { background: #14a394; }
    .btn-download { background: #4f8cf0; }
    .btn-delete { background: #f3665a; }
    .course-cards-grid {
        display: flex !important;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: stretch;
        width: 100%;
        justify-content: flex-start;
    }
    .course-card-item {
        flex: 0 0 calc(25% - 1.125rem);
        max-width: calc(25% - 1.125rem);
        min-width: 0;
        display: flex;
    }
    .course-card-item > * {
        width: 100%;
        min-width: 0;
    }
    @media (min-width: 768px) {
        .course-action-grid {
            justify-content: flex-start;
        }
    }
    @media (max-width: 767px) {
        .course-search-layout {
            flex-wrap: wrap;
            overflow-x: visible;
        }
        .course-search-layout > * {
            min-width: 0;
        }
        .course-search-layout input[type="text"] {
            width: 100%;
            flex-basis: 100%;
            box-sizing: border-box;
        }
        .course-search-layout select {
            box-sizing: border-box;
        }
        /* Filtre butonları (seviye/kademe/favori/öğretmen) yan yana kalsın */
        .course-search-layout select.course-select-narrow,
        .course-favorites-filter-toggle {
            flex: 1 1 calc(33.333% - .35rem);
            min-width: 0;
        }
        .course-search-layout select.course-select-fit {
            flex: 1 1 100%;
        }
        /* Sağdaki eylem butonları (Ders Oluştur...Tüm Dersleri Sil) da yan yana kalsın */
        .course-action-grid {
            width: 100%;
            flex-wrap: wrap;
        }
        .course-action-grid a,
        .course-action-grid button {
            flex: 1 1 calc(33.333% - .3rem);
            min-width: 0;
            width: auto;
            max-width: 100%;
            box-sizing: border-box;
        }
        .course-action-grid .btn-create,
        .course-action-grid .btn-upload,
        .course-action-grid .btn-download,
        .course-action-grid .btn {
            min-width: 0;
        }
        .course-cards-grid {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }
        .course-card-item {
            flex: 1 1 100%;
            max-width: 100%;
            width: 100%;
        }
    }
    @media (min-width: 640px) {
        .course-card-item {
            flex-basis: calc(50% - .75rem);
            max-width: calc(50% - .75rem);
        }
    }
    @media (min-width: 1024px) {
        .course-card-item {
            flex-basis: calc(25% - 1.125rem);
            max-width: calc(25% - 1.125rem);
        }
    }
    .course-cards-wrap {
        width: 100%;
        margin: 0;
        display: block;
    }
</style>

<section class="space-y-5">
    <div class="course-topbar">
        <div class="overflow-x-auto">
            <div class="inline-flex min-w-max items-center gap-2 rounded-2xl bg-gray-100 p-1">
                @foreach($categories as $category)
                    <a
                        href="{{ route('courses.index', array_merge(request()->except('page'), ['category' => $category])) }}"
                        class="course-category-pill {{ $activeCategory === $category ? 'active' : '' }}"
                    >
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        </div>

        <form method="GET" class="course-search-layout">
            <input type="hidden" name="category" value="{{ $activeCategory }}">
            <input
                type="text"
                name="q"
                value="{{ $q ?? request('q') }}"
                placeholder="Ders başlığını aratmak için yazınız."
            >
            <select name="difficulty" id="course-difficulty-filter" class="course-select-narrow" onchange="this.form.submit()">
                <option value="Tumu" @selected(($difficulty ?? '') === '' || ($difficulty ?? '') === 'Tumu')>Seviye</option>
                <option value="Kolay" @selected(($difficulty ?? '') === 'Kolay')>Kolay</option>
                <option value="Orta" @selected(($difficulty ?? '') === 'Orta')>Orta</option>
                <option value="Zor" @selected(($difficulty ?? '') === 'Zor')>Zor</option>
            </select>
            <select name="education_stage" id="course-stage-filter" class="course-select-narrow" onchange="this.form.submit()">
                <option value="Tumu" @selected(($educationStage ?? '') === '' || ($educationStage ?? '') === 'Tumu')>Kademe</option>
                <option value="ilkokul" @selected(($educationStage ?? '') === 'ilkokul')>İlkokul</option>
                <option value="ortaokul" @selected(($educationStage ?? '') === 'ortaokul')>Ortaokul</option>
                <option value="lise" @selected(($educationStage ?? '') === 'lise')>Lise</option>
            </select>
            <label class="course-favorites-filter-toggle" title="Favorileri Göster">
                <input type="checkbox" name="favorites_only" value="1" @checked($favoritesOnly ?? false) onchange="this.form.submit()">
                <span aria-hidden="true">♥</span>
            </label>
            @if(($isAdmin ?? false) && !empty($courseOwners ?? []))
                <select
                    name="owner"
                    id="course-owner-filter"
                    class="course-select-fit"
                >
                    @foreach($courseOwners as $owner)
                        <option value="{{ $owner['value'] }}" @selected((string) ($ownerFilter ?? 'all') === (string) $owner['value'])>
                            {{ $owner['label'] }}
                        </option>
                    @endforeach
                </select>
            @elseif(($isTeacher ?? false))
                <input type="hidden" name="owner" value="{{ $ownerFilter ?? auth()->id() }}">
            @endif
            <div class="course-action-grid">
                <a href="{{ route('courses.create') }}" class="btn-create">Ders Oluştur</a>
                <button id="course-import-open" type="button" class="btn-upload">Yükle</button>
                <a href="{{ route('courses.export-all') }}" class="btn-download">İndir</a>
                @if(auth()->user()?->hasRole('admin','teacher'))
                    <button type="button" id="course-bulk-assign-open" class="btn" style="background:#fb923c;color:#fff">Ders Ata</button>
                @endif
                @if(auth()->user()?->hasRole('admin'))
                    <button type="submit" form="course-destroy-all-form" class="btn-delete">Tüm Dersleri Sil</button>
                @endif
            </div>
        </form>
    </div>

    <form id="course-import-form" method="POST" action="{{ route('courses.import') }}" enctype="multipart/form-data" style="display:none;">
        @csrf
        <input id="course-import-file" type="file" name="course_json[]" accept=".coursepkg,.json,application/json,text/plain,application/octet-stream" multiple style="display:none;">
    </form>

    <div id="course-import-progress-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:4000;">
        <div style="width:min(92vw,420px);background:#fff;border-radius:16px;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,.2);text-align:center;">
            <h3 style="margin:0 0 6px;font-size:18px;font-weight:800;color:#111827;">Ders Yükleniyor</h3>
            <p style="margin:0 0 16px;color:#64748b;font-size:13px;">Lütfen bekleyin, dosyalarınız sunucuya aktarılıyor...</p>
            <div style="height:14px;border-radius:999px;background:#e2e8f0;overflow:hidden;">
                <div id="course-import-progress-bar" style="height:100%;width:0%;border-radius:999px;background:linear-gradient(90deg,#2563eb,#0ea5e9);transition:width .15s ease;"></div>
            </div>
            <div id="course-import-progress-text" style="margin-top:10px;font-weight:700;color:#2563eb;font-size:14px;">%0</div>
        </div>
    </div>
    @if(auth()->user()?->hasRole('admin'))
        <form id="course-destroy-all-form" method="POST" action="{{ route('courses.destroy-all') }}" data-confirm="Tüm dersler ve bağlı ödevler sistemden kaldırılsın mı?" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <div class="course-cards-wrap">
        <div class="course-cards-grid">
            @forelse($items as $item)
            @continue(!empty($item->parent_course_id))
            <div class="course-card-item">
            @php
                $slides = (array) data_get($item->lesson_payload, 'slides', []);
                $firstSlide = $slides[0] ?? [];
                $desc = trim((string) data_get($item->lesson_payload, 'lesson_description', ''));
                if ($desc === '') $desc = trim((string) data_get($firstSlide, 'description', ''));
                if ($desc === '') $desc = $item->name . ' dersi için hazırlanan konu anlatımı ve etkinlik içerikleri.';
                $thumb = (string) ($item->coverImageUrl() ?: data_get($firstSlide, 'image_url') ?: '');
                $difficulty = (string) (data_get($item->lesson_payload, 'difficulty') ?: (((int) ($item->weekly_hours ?? 0) >= 4) ? 'Orta' : 'Kolay'));
                $className = (string) ($item->schoolClass?->name ?? '6');
                $classNumber = (int) preg_replace('/\D+/', '', $className);
                $age = (($classNumber > 0 ? $classNumber : 6) + 5) . '+';
                $creatorLabel = trim((string) ($item->creator?->name ?? ''));
                $courseTitle = \App\Support\Utf8Text::normalize($item->name);
                $courseDesc = \App\Support\Utf8Text::normalize($desc);
                $courseCreator = \App\Support\Utf8Text::normalize($creatorLabel);
                $courseDifficulty = \App\Support\Utf8Text::normalize($difficulty);
                $canEditCourse = (bool) ($isAdmin ?? false) || ((bool) ($isTeacher ?? false) && (int) ($item->created_by ?? 0) === (int) auth()->id());
                $canCreateSubCourse = (bool) ($isAdmin ?? false) || ((bool) ($isTeacher ?? false) && (int) ($item->created_by ?? 0) === (int) auth()->id());
            @endphp
            <x-course-card
                :title="$courseTitle"
                :description="$courseDesc"
                :image="$thumb"
                :logo="url('/public/logo.png')"
                :age="$age"
                :difficulty="$courseDifficulty"
                :primary-url="route('course.detail', ['id' => $item->id])"
                primary-label="Dersi Aç"
                :download-url="route('courses.export', $item)"
                :sub-course-url="$canCreateSubCourse ? route('courses.create', ['parent_course_id' => $item->id]) : null"
                :delete-url="auth()->user()?->hasRole('student') ? null : url('/courses/delete/' . $item->id)"
                :assign-enabled="auth()->user()?->hasRole('admin','teacher')"
                :assign-course-id="$item->id"
                :assign-course-name="$item->name"
                :assign-current-teacher="(int) ($item->teacher_id ?? 0)"
                :creator-label="$courseCreator"
                :course-id="$item->id"
                :is-favorite="in_array($item->id, $favoriteCourseIds ?? [])"
            />
            </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                    Henüz ders eklenmedi.
                </div>
            @endforelse
        </div>
    </div>

    <div>
        {{ $items->links() }}
    </div>
</section>

<div id="course-delete-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;z-index:3000;">
    <div style="width:min(92vw,420px);background:#fff;border-radius:14px;padding:18px;box-shadow:0 20px 50px rgba(0,0,0,.18);">
        <h3 style="margin:0 0 8px;font-size:20px;font-weight:800;color:#111827;">Dersi Sil</h3>
        <p style="margin:0 0 14px;color:#334155;">Bu dersi silmek istediğinize emin misiniz?</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" id="course-delete-cancel" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
            <button type="button" id="course-delete-confirm" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#dc2626;color:#fff;font-weight:700;cursor:pointer;">Evet, Sil</button>
        </div>
    </div>
</div>
<div id="course-assign-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;z-index:3000;">
    <div style="width:min(92vw,460px);background:#fff;border-radius:14px;padding:18px;box-shadow:0 20px 50px rgba(0,0,0,.18);">
        <h3 style="margin:0 0 8px;font-size:20px;font-weight:800;color:#111827;">Dersi Sınıfa Ata</h3>
        <p id="course-assign-title" style="margin:0 0 14px;color:#334155;"></p>
        <div style="display:flex;gap:8px;margin-bottom:10px">
            <button type="button" class="btn" data-assign-tab="teacher">Öğretmene Ata</button>
            <button type="button" class="btn" data-assign-tab="class">Sınıf Bazlı Ata</button>
            <button type="button" class="btn" data-assign-tab="level">Kademe Bazlı Ata</button>
        </div>
        <form id="course-assign-form-teacher" method="POST" data-assign-panel="teacher">
            @csrf
            <label for="course-assign-teacher" style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Öğretmen</label>
            <select id="course-assign-teacher" name="teacher_id" style="width:100%;height:42px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;">
                <option value="">Öğretmen Seçiniz</option>
                @foreach(($teachers ?? collect()) as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Öğretmen #' . $teacher->id) }}</option>
                @endforeach
            </select>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" id="course-assign-cancel" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
        <div id="course-assign-unassign-wrap" style="margin-top:12px;display:none;">
            <form method="POST" id="course-unassign-form" data-confirm="Bu dersin öğretmen ataması kaldırılacak. Devam edilsin mi?">
                @csrf
                <button type="submit" style="width:100%;height:42px;padding:0 14px;border:0;border-radius:10px;background:#f59e0b;color:#fff;font-weight:700;cursor:pointer;">Atamayı Kaldır</button>
            </form>
        </div>
        <form id="course-assign-form-class" method="POST" data-assign-panel="class" style="display:none">
            @csrf
            <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Sınıflar</label>
            <div style="max-height:260px;overflow:auto;border:1px solid #cbd5e1;border-radius:14px;padding:10px;background:#f8fafc;">
                @foreach($bulkAssignableClasses as $class)
                    <label style="display:flex;gap:10px;align-items:flex-start;margin:6px 0;padding:10px 12px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;cursor:pointer;">
                        <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" style="margin-top:3px;width:auto;">
                        <span style="display:grid;gap:2px;">
                            <strong style="color:#0f172a;">{{ $class->name }}/{{ $class->section }}</strong>
                            <small style="color:#64748b;">{{ $class->grade_level }}. sınıf</small>
                        </span>
                    </label>
                @endforeach
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" class="course-assign-cancel-x" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
        <form id="course-assign-form-level" method="POST" data-assign-panel="level" style="display:none">
            @csrf
            <label for="course-assign-level" style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Kademe</label>
            <select id="course-assign-level" name="grade_level" style="width:100%;height:42px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ $i }}. Sınıf</option>
                @endfor
            </select>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" class="course-assign-cancel-x" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
    </div>
</div>

<div id="course-bulk-assign-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:3050;padding:16px;">
    <div style="width:min(96vw,1200px);max-height:92vh;overflow:hidden;background:#fff;border-radius:18px;padding:18px;box-shadow:0 20px 50px rgba(0,0,0,.18);display:grid;grid-template-rows:auto auto minmax(0,1fr) auto;gap:14px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
            <div>
                <h3 style="margin:0;font-size:22px;font-weight:800;color:#111827;">Toplu Ders Atama</h3>
                <p id="course-bulk-assign-title" style="margin:6px 0 0;color:#475569;">Bir öğretmen seçin ve dersleri topluca atayın.</p>
            </div>
            <button type="button" id="course-bulk-assign-close" style="height:40px;padding:0 14px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">Kapat</button>
        </div>
        @if(($isAdmin ?? false))
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <button type="button" class="btn" data-bulk-tab="teacher">Öğretmene Atama</button>
                <button type="button" class="btn" data-bulk-tab="class">Sınıfa Atama</button>
            </div>
        @endif
        <div style="display:grid;grid-template-columns:{{ ($isAdmin ?? false) ? '1fr 1.15fr' : '1fr' }};gap:12px;align-items:start;">
            @if(($isAdmin ?? false))
                <div id="bulk-course-teacher-wrap">
                    <label for="bulk-course-teacher" style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Öğretmen Seç</label>
                    <select id="bulk-course-teacher" style="width:100%;height:44px;border:1px solid #cbd5e1;border-radius:12px;padding:0 12px;">
                        <option value="">Öğretmen seçiniz</option>
                        @foreach(($teachers ?? collect()) as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Öğretmen #' . $teacher->id) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div id="bulk-course-class-wrap" style="grid-column:{{ ($isAdmin ?? false) ? 'auto' : '1 / -1' }};display:{{ ($isAdmin ?? false) ? 'block' : 'block' }};">
                <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Sınıf Seç</label>
                <div id="bulk-course-classes" style="border:1px solid #cbd5e1;border-radius:14px;padding:10px;background:#f8fafc;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;">
                        @foreach($bulkAssignableClasses as $class)
                            <label style="display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;cursor:pointer;min-width:0;min-height:72px;">
                                <input type="checkbox" class="bulk-course-class-checkbox" value="{{ $class->id }}" style="margin-top:3px;width:auto;">
                                <span style="display:grid;gap:2px;min-width:0;">
                                    <strong style="color:#0f172a;">{{ $class->name }}/{{ $class->section }}</strong>
                                    <small style="color:#64748b;">{{ $class->grade_level }}. sınıf</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @if(($isAdmin ?? false))
        <div id="bulk-course-list" style="overflow:auto;border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#f8fafc;min-height:0;max-height:38vh;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff;white-space:nowrap;">
                    <input type="checkbox" id="bulk-course-select-all-classes" style="width:auto;margin:0">
                    <span>Sınıfları tümünü seç</span>
                </label>
            </div>
            <div id="bulk-course-panel-teacher" style="display:grid;gap:10px;">
                @foreach(($assignableCourses ?? collect()) as $course)
                    <label style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
                        <input type="checkbox" class="bulk-course-checkbox" value="{{ $course->id }}" data-course-teacher="{{ (int) ($course->teacher_id ?? 0) }}" style="width:auto;margin:0;">
                        <span style="flex:1 1 auto;font-weight:700;color:#111827;min-width:0;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">{{ $course->name }}</span>
                        <span style="flex:0 0 auto;padding:5px 10px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;">{{ (int) ($course->teacher_id ?? 0) > 0 ? 'Atanmış' : 'Boş' }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:nowrap;white-space:nowrap;">
            <form id="course-bulk-assign-form" method="POST" action="{{ route('courses.assign-teacher.bulk') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin:0;">
                @csrf
                <input type="hidden" name="teacher_id" id="bulk-course-teacher-input">
                <div id="bulk-course-class-hidden-inputs"></div>
                <div id="bulk-course-hidden-inputs"></div>
                <button type="button" id="course-bulk-assign-cancel" style="height:44px;padding:0 16px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
                <button type="submit" style="height:44px;padding:0 16px;border:0;border-radius:12px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Atamayı Kaydet</button>
            </form>
            <form id="course-bulk-unassign-form" method="POST" action="{{ route('courses.unassign-teacher.bulk') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin:0;">
                @csrf
                <input type="hidden" name="teacher_id" id="bulk-course-unassign-teacher-input">
                <div id="bulk-course-unassign-hidden-inputs"></div>
                <button type="submit" style="height:44px;padding:0 16px;border:0;border-radius:12px;background:#f59e0b;color:#fff;font-weight:700;cursor:pointer;">Atamayı Kaldır</button>
            </form>
        </div>
        @else
        <div id="bulk-course-list" style="overflow:auto;border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#f8fafc;min-height:0;max-height:52vh;">
            <div style="display:flex;justify-content:flex-end;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff;white-space:nowrap;">
                    <input type="checkbox" id="bulk-course-select-all-classes" style="width:auto;margin:0">
                    <span>Sınıfları tümünü seç</span>
                </label>
            </div>
            <div style="display:grid;gap:10px;">
                @foreach(($assignableCourses ?? collect()) as $course)
                    <label style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
                        <input type="checkbox" class="bulk-course-checkbox" value="{{ $course->id }}" data-course-teacher="{{ (int) ($course->teacher_id ?? 0) }}" style="width:auto;margin:0;">
                        <span style="flex:1 1 auto;font-weight:700;color:#111827;min-width:0;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">{{ $course->name }}</span>
                        <span style="flex:0 0 auto;padding:5px 10px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;">{{ (int) ($course->teacher_id ?? 0) > 0 ? 'Atanmış' : 'Boş' }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:nowrap;white-space:nowrap;">
            <form id="course-bulk-assign-form" method="POST" action="{{ route('courses.assign-classes.bulk') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin:0;">
                @csrf
                <div id="bulk-course-class-hidden-inputs"></div>
                <div id="bulk-course-hidden-inputs"></div>
                <button type="button" id="course-bulk-assign-cancel" style="height:44px;padding:0 16px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
                <button type="submit" style="height:44px;padding:0 16px;border:0;border-radius:12px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Atamayı Kaydet</button>
            </form>
            <form id="course-bulk-unassign-form" method="POST" action="{{ route('courses.unassign-classes.bulk') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin:0;">
                @csrf
                <div id="bulk-course-unassign-hidden-inputs"></div>
                <button type="submit" style="height:44px;padding:0 16px;border:0;border-radius:12px;background:#f59e0b;color:#fff;font-weight:700;cursor:pointer;">Atamayı Kaldır</button>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const importOpenBtn = document.getElementById('course-import-open');
    const importForm = document.getElementById('course-import-form');
    const importFile = document.getElementById('course-import-file');
    const assignModal = document.getElementById('course-assign-modal');
    const bulkAssignModal = document.getElementById('course-bulk-assign-modal');
    const assignTitle = document.getElementById('course-assign-title');
    const bulkAssignOpen = document.getElementById('course-bulk-assign-open');
    const bulkAssignClose = document.getElementById('course-bulk-assign-close');
    const bulkAssignCancel = document.getElementById('course-bulk-assign-cancel');
    const bulkAssignTeacher = document.getElementById('bulk-course-teacher');
    const bulkAssignTeacherInput = document.getElementById('bulk-course-teacher-input');
    const bulkUnassignTeacherInput = document.getElementById('bulk-course-unassign-teacher-input');
    const bulkAssignClasses = document.getElementById('bulk-course-classes');
    const bulkAssignClassHiddenInputs = document.getElementById('bulk-course-class-hidden-inputs');
    const bulkAssignList = document.getElementById('bulk-course-list');
    const bulkCourseHiddenInputs = document.getElementById('bulk-course-hidden-inputs');
    const bulkCourseUnassignHiddenInputs = document.getElementById('bulk-course-unassign-hidden-inputs');
    const bulkSelectAllCourses = document.getElementById('bulk-course-select-all-courses');
    const bulkSelectAllClasses = document.getElementById('bulk-course-select-all-classes');
    const bulkCourseTeacherWrap = document.getElementById('bulk-course-teacher-wrap');
    const bulkCourseClassWrap = document.getElementById('bulk-course-class-wrap');
    const bulkTabs = Array.from(document.querySelectorAll('[data-bulk-tab]'));
    const assignForms = {
        teacher: document.getElementById('course-assign-form-teacher'),
        class: document.getElementById('course-assign-form-class'),
        level: document.getElementById('course-assign-form-level'),
    };
    const assignTeacherSelect = document.getElementById('course-assign-teacher');
    const assignLevelSelect = document.getElementById('course-assign-level');
    const assignUnassignWrap = document.getElementById('course-assign-unassign-wrap');
    const assignUnassignForm = document.getElementById('course-unassign-form');
    const assignTabs = Array.from(document.querySelectorAll('[data-assign-tab]'));
    const assignPanels = Array.from(document.querySelectorAll('[data-assign-panel]'));
    const courseIdsByTeacher = @json($courseIdsByTeacher ?? []);
    const courseIdsByClass = @json($courseIdsByClass ?? []);
    const currentTeacherId = @json((int) (optional(auth()->user()?->teacher)->id ?? 0));
    const isAdminAccount = @json((bool) ($isAdmin ?? false));
    let currentAssignCourse = null;
    let currentBulkTab = isAdminAccount ? 'teacher' : 'class';

    const setBulkTab = (target) => {
        currentBulkTab = target === 'class' ? 'class' : 'teacher';
        if (bulkCourseTeacherWrap) {
            bulkCourseTeacherWrap.style.display = currentBulkTab === 'teacher' && isAdminAccount ? 'block' : 'none';
        }
        if (bulkCourseClassWrap) {
            bulkCourseClassWrap.style.display = currentBulkTab === 'class' || !isAdminAccount ? 'block' : 'none';
            bulkCourseClassWrap.style.gridColumn = currentBulkTab === 'class' || !isAdminAccount ? '1 / -1' : 'auto';
        }
        if (bulkAssignList) {
            bulkAssignList.style.maxHeight = currentBulkTab === 'teacher' ? '38vh' : '52vh';
        }
        if (bulkAssignTeacherInput) {
            bulkAssignTeacherInput.value = currentBulkTab === 'teacher' ? (bulkAssignTeacher?.value || String(currentTeacherId || '')) : '';
        }
        if (bulkUnassignTeacherInput) {
            bulkUnassignTeacherInput.value = currentBulkTab === 'teacher' ? (bulkAssignTeacher?.value || String(currentTeacherId || '')) : '';
        }
        const assignForm = document.getElementById('course-bulk-assign-form');
        if (assignForm) {
            assignForm.action = currentBulkTab === 'teacher'
                ? @json(route('courses.assign-teacher.bulk'))
                : @json(route('courses.assign-classes.bulk'));
        }
        const unassignForm = document.getElementById('course-bulk-unassign-form');
        if (unassignForm) {
            unassignForm.style.display = 'flex';
            unassignForm.action = currentBulkTab === 'teacher'
                ? @json(route('courses.unassign-teacher.bulk'))
                : @json(route('courses.unassign-classes.bulk'));
        }
        if (bulkSelectAllCourses) {
            bulkSelectAllCourses.closest('label')?.style && (bulkSelectAllCourses.closest('label').style.display = 'flex');
        }
        if (bulkSelectAllClasses) {
            bulkSelectAllClasses.closest('label')?.style && (bulkSelectAllClasses.closest('label').style.display = 'flex');
        }
    };

    const openAssignModal = (btn) => {
        if (!assignModal) return;
        currentAssignCourse = {
            id: btn.dataset.assignCourseId || '',
            name: btn.dataset.assignCourseName || '',
            teacherUrl: btn.dataset.assignTeacherUrl || '',
            classesUrl: btn.dataset.assignClassesUrl || '',
            levelUrl: btn.dataset.assignLevelUrl || '',
            teacherId: btn.dataset.assignCurrentTeacher || '',
        };
        if (assignTitle) assignTitle.textContent = currentAssignCourse.name ? `"${currentAssignCourse.name}" dersini atayın.` : 'Dersi atayın.';
        if (assignTeacherSelect) assignTeacherSelect.value = currentAssignCourse.teacherId || '';
        if (assignLevelSelect) assignLevelSelect.value = '1';
        if (assignForms.teacher) assignForms.teacher.action = currentAssignCourse.teacherUrl || '';
        if (assignForms.class) assignForms.class.action = currentAssignCourse.classesUrl || '';
        if (assignForms.level) assignForms.level.action = currentAssignCourse.levelUrl || '';
        if (assignUnassignForm) assignUnassignForm.action = currentAssignCourse.teacherUrl ? currentAssignCourse.teacherUrl.replace('/assign-teacher', '/unassign-teacher') : '';
        if (assignUnassignWrap) assignUnassignWrap.style.display = currentAssignCourse.teacherId ? 'block' : 'none';
        assignPanels.forEach((panel) => panel.style.display = panel.dataset.assignPanel === 'class' ? 'block' : 'none');
        assignModal.style.display = 'flex';
    };

    const closeAssignModal = () => {
        if (!assignModal) return;
        assignModal.style.display = 'none';
    };

    const openBulkAssignModal = () => {
        if (!bulkAssignModal) return;
        setBulkTab(isAdminAccount ? 'teacher' : 'class');
        bulkAssignModal.style.display = 'flex';
    };

    const closeBulkAssignModal = () => {
        if (!bulkAssignModal) return;
        bulkAssignModal.style.display = 'none';
    };

    const syncBulkHiddenInputs = () => {
        if (!bulkCourseHiddenInputs) return;
        bulkCourseHiddenInputs.innerHTML = '';
        if (bulkCourseUnassignHiddenInputs) {
            bulkCourseUnassignHiddenInputs.innerHTML = '';
        }
        if (bulkAssignClassHiddenInputs) {
            bulkAssignClassHiddenInputs.innerHTML = '';
        }
        const selectedTeacherId = bulkAssignTeacher ? (bulkAssignTeacher.value || '') : String(currentTeacherId || '');
        if (bulkAssignTeacherInput) {
            bulkAssignTeacherInput.value = selectedTeacherId;
        }
        if (bulkUnassignTeacherInput) {
            bulkUnassignTeacherInput.value = selectedTeacherId;
        }
        Array.from(document.querySelectorAll('.bulk-course-checkbox:checked')).forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'course_ids[]';
            input.value = checkbox.value;
            bulkCourseHiddenInputs.appendChild(input);
            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'course_ids[]';
            input2.value = checkbox.value;
            bulkCourseUnassignHiddenInputs?.appendChild(input2);
        });
        Array.from(document.querySelectorAll('.bulk-course-class-checkbox:checked')).forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'class_ids[]';
            input.value = checkbox.value;
            bulkAssignClassHiddenInputs?.appendChild(input);
            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'class_ids[]';
            input2.value = checkbox.value;
            bulkCourseUnassignHiddenInputs?.appendChild(input2);
        });
    };

    const syncBulkCourseSelectionState = () => {
        const total = document.querySelectorAll('.bulk-course-checkbox').length;
        const checked = document.querySelectorAll('.bulk-course-checkbox:checked').length;
        if (bulkSelectAllCourses) {
            bulkSelectAllCourses.checked = total > 0 && total === checked;
        }
    };

    const syncBulkClassSelectionState = () => {
        const total = document.querySelectorAll('.bulk-course-class-checkbox').length;
        const checked = document.querySelectorAll('.bulk-course-class-checkbox:checked').length;
        if (bulkSelectAllClasses) {
            bulkSelectAllClasses.checked = total > 0 && total === checked;
        }
    };

    const syncBulkCoursesByTeacher = () => {
        const selectedTeacherId = bulkAssignTeacher ? (bulkAssignTeacher.value || String(currentTeacherId || '')) : String(currentTeacherId || '');
        const assignedCourseIds = selectedTeacherId ? (courseIdsByTeacher[selectedTeacherId] || []) : [];
        document.querySelectorAll('.bulk-course-checkbox').forEach((checkbox) => {
            checkbox.checked = assignedCourseIds.includes(Number(checkbox.value));
        });
        syncBulkCourseSelectionState();
        syncBulkHiddenInputs();
    };

    const syncBulkCoursesByClass = () => {
        const selectedClassIds = Array.from(document.querySelectorAll('.bulk-course-class-checkbox:checked'))
            .map((checkbox) => Number(checkbox.value))
            .filter((id) => Number.isFinite(id) && id > 0);
        const assignedCourseSet = new Set();
        selectedClassIds.forEach((classId) => {
            (courseIdsByClass[classId] || []).forEach((courseId) => assignedCourseSet.add(Number(courseId)));
        });
        document.querySelectorAll('.bulk-course-checkbox').forEach((checkbox) => {
            checkbox.checked = assignedCourseSet.has(Number(checkbox.value));
        });
        syncBulkCourseSelectionState();
        syncBulkHiddenInputs();
    };

    const importProgressModal = document.getElementById('course-import-progress-modal');
    const importProgressBar = document.getElementById('course-import-progress-bar');
    const importProgressText = document.getElementById('course-import-progress-text');

    function setImportProgress(pct) {
        const clamped = Math.max(0, Math.min(100, Math.round(pct)));
        if (importProgressBar) importProgressBar.style.width = clamped + '%';
        if (importProgressText) importProgressText.textContent = '%' + clamped;
    }

    if (importOpenBtn && importForm && importFile) {
        importOpenBtn.addEventListener('click', () => {
            importFile.value = '';
            importFile.click();
        });

        importFile.addEventListener('change', () => {
            if (!importFile.files || importFile.files.length === 0) {
                return;
            }

            const formData = new FormData(importForm);
            setImportProgress(0);
            if (importProgressModal) importProgressModal.style.display = 'flex';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', importForm.action, true);
            // Not: X-Requested-With gönderilmiyor; Laravel'in bu isteği normal
            // (redirect + flash mesajlı) bir form gönderimi gibi işlemesini
            // istiyoruz, AJAX/JSON hata yanıtına dönüşmesin.

            xhr.upload.addEventListener('progress', (e) => {
                if (!e.lengthComputable) return;
                // Yükleme baytları %90'a kadar; kalan %10 sunucunun
                // dosyaları işleyip yönlendirme yapması için ayrılır.
                setImportProgress((e.loaded / e.total) * 90);
            });

            xhr.addEventListener('load', () => {
                if (xhr.status < 200 || xhr.status >= 400) {
                    if (importProgressModal) importProgressModal.style.display = 'none';
                    alert('Ders yüklenirken bir hata oluştu (HTTP ' + xhr.status + '). Lütfen tekrar deneyin.');
                    return;
                }
                setImportProgress(100);
                // XHR, sunucunun yönlendirdiği son (GET) adresi responseURL'de verir.
                // Bu boş/hatalı gelirse asla POST-only import adresine (importForm.action)
                // geri dönmeyelim, bunun yerine güvenli varsayılan olan ders listesine gidelim.
                const target = (xhr.responseURL && xhr.responseURL !== importForm.action)
                    ? xhr.responseURL
                    : @json(route('courses.index'));
                setTimeout(() => { window.location.href = target; }, 200);
            });

            xhr.addEventListener('error', () => {
                if (importProgressModal) importProgressModal.style.display = 'none';
                alert('Ders yüklenirken bir hata oluştu. Lütfen tekrar deneyin.');
            });

            xhr.send(formData);
        });
    }

    document.querySelectorAll('[data-assign-course-id]').forEach((btn) => {
        btn.addEventListener('click', () => openAssignModal(btn));
    });

    bulkAssignOpen?.addEventListener('click', openBulkAssignModal);
    bulkAssignClose?.addEventListener('click', closeBulkAssignModal);
    bulkAssignCancel?.addEventListener('click', closeBulkAssignModal);
    bulkAssignModal?.addEventListener('click', (event) => {
        if (event.target === bulkAssignModal) closeBulkAssignModal();
    });
    bulkTabs.forEach((tab) => {
        tab.addEventListener('click', () => setBulkTab(tab.dataset.bulkTab || 'teacher'));
    });

    document.getElementById('course-owner-filter')?.addEventListener('change', (event) => {
        const form = event.target.closest('form');
        if (form) form.submit();
    });

    bulkAssignTeacher?.addEventListener('change', () => {
        syncBulkCoursesByTeacher();
    });
    bulkAssignClasses?.addEventListener('change', () => {
        syncBulkClassSelectionState();
        if (currentBulkTab === 'class') {
            syncBulkCoursesByClass();
        }
        syncBulkHiddenInputs();
    });
    bulkSelectAllCourses?.addEventListener('change', () => {
        const checked = bulkSelectAllCourses.checked;
        document.querySelectorAll('.bulk-course-checkbox').forEach((checkbox) => {
            checkbox.checked = checked;
        });
        syncBulkCourseSelectionState();
        syncBulkHiddenInputs();
    });
    bulkSelectAllClasses?.addEventListener('change', () => {
        const checked = bulkSelectAllClasses.checked;
        document.querySelectorAll('.bulk-course-class-checkbox').forEach((checkbox) => {
            checkbox.checked = checked;
        });
        syncBulkClassSelectionState();
        syncBulkHiddenInputs();
    });
    bulkAssignList?.addEventListener('change', (event) => {
        if (event.target.closest('.bulk-course-checkbox')) {
            syncBulkHiddenInputs();
            syncBulkCourseSelectionState();
        }
        if (event.target.closest('.bulk-course-class-checkbox')) {
            syncBulkHiddenInputs();
            syncBulkClassSelectionState();
            if (currentBulkTab === 'class') {
                syncBulkCoursesByClass();
            }
        }
    });
    document.getElementById('course-bulk-assign-form')?.addEventListener('submit', (event) => {
        syncBulkHiddenInputs();
        const selectedClasses = document.querySelectorAll('.bulk-course-class-checkbox:checked');
        const selectedCourses = document.querySelectorAll('.bulk-course-checkbox:checked').length;
        const isTeacherAccount = !bulkAssignTeacher;
        const effectiveTeacherId = bulkAssignTeacher ? bulkAssignTeacher.value : String(currentTeacherId || '');
        if (selectedCourses === 0) {
            event.preventDefault();
            alert(isTeacherAccount ? 'Lütfen en az bir ders seçin.' : 'Lütfen en az bir ders seçin.');
            return;
        }
        if (isTeacherAccount && selectedClasses.length === 0) {
            event.preventDefault();
            alert('Lütfen en az bir sınıf seçin.');
            return;
        }
        if (!isTeacherAccount && !effectiveTeacherId && selectedClasses.length === 0) {
            event.preventDefault();
            alert('Lütfen bir öğretmen seçin.');
            return;
        }
        if (selectedClasses.length > 0) {
            event.currentTarget.action = '{{ route('courses.assign-classes.bulk') }}';
        } else {
            event.currentTarget.action = '{{ route('courses.assign-teacher.bulk') }}';
        }
    });

    document.getElementById('course-bulk-unassign-form')?.addEventListener('submit', (event) => {
        if (!isAdminAccount && currentBulkTab === 'teacher') {
            event.preventDefault();
            return;
        }
        syncBulkHiddenInputs();
        const selectedCourses = document.querySelectorAll('.bulk-course-checkbox:checked').length;
        const selectedClasses = document.querySelectorAll('.bulk-course-class-checkbox:checked').length;
        const teacherId = bulkAssignTeacher ? (bulkAssignTeacher.value || String(currentTeacherId || '')) : String(currentTeacherId || '');
        if (selectedCourses === 0) {
            event.preventDefault();
            alert('Lütfen en az bir ders seçin.');
            return;
        }
        if (currentBulkTab === 'teacher') {
            if (!teacherId) {
                event.preventDefault();
                alert('Lütfen bir öğretmen seçin.');
                return;
            }
            event.currentTarget.action = '{{ route('courses.unassign-teacher.bulk') }}';
        } else {
            if (selectedClasses.length === 0) {
                event.preventDefault();
                alert('Lütfen en az bir sınıf seçin.');
                return;
            }
            event.currentTarget.action = '{{ route('courses.unassign-classes.bulk') }}';
        }
    });

    if (bulkAssignTeacher?.value || currentTeacherId) {
        if (bulkAssignTeacher && !bulkAssignTeacher.value && currentTeacherId) {
            bulkAssignTeacher.value = String(currentTeacherId);
        }
        syncBulkCoursesByTeacher();
    }

    setBulkTab(isAdminAccount ? 'teacher' : 'class');

    if (currentBulkTab === 'class') {
        syncBulkCoursesByClass();
    }

    assignTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.assignTab;
            assignPanels.forEach((panel) => {
                panel.style.display = panel.dataset.assignPanel === target ? 'block' : 'none';
            });
        });
    });

    document.getElementById('course-assign-cancel')?.addEventListener('click', closeAssignModal);
    document.querySelectorAll('.course-assign-cancel-x').forEach((btn) => btn.addEventListener('click', closeAssignModal));
    assignModal?.addEventListener('click', (event) => {
        if (event.target === assignModal) closeAssignModal();
    });

    assignForms.teacher?.addEventListener('submit', (event) => {
        if (!currentAssignCourse?.teacherUrl) {
            event.preventDefault();
        }
    });
    assignForms.class?.addEventListener('submit', (event) => {
        if (!currentAssignCourse?.classesUrl) {
            event.preventDefault();
        }
    });
    assignForms.level?.addEventListener('submit', (event) => {
        if (!currentAssignCourse?.levelUrl) {
            event.preventDefault();
        }
    });
});
</script>
@endsection