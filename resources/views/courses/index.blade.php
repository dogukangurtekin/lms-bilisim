@extends('layout.app')
@section('title','Dersler')
@section('content')
@php
    $categories = ['Tumu', 'Kodlama', 'Tasarim', 'Elektrik', 'Robotik', 'Teorik', 'Oyun', 'Yapay Zeka'];
    $activeCategory = request('category', 'Tumu');
    $totalItems = method_exists($items, 'total') ? (int) $items->total() : count($items);
    $currentQuery = trim((string) request('q', ''));
@endphp

<style>
    .courses-shell {
        display: grid;
        gap: 1.25rem;
    }
    .courses-hero {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        background:
            radial-gradient(circle at top right, rgba(99,102,241,.18), transparent 30%),
            radial-gradient(circle at bottom left, rgba(14,165,233,.16), transparent 28%),
            linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #334155 100%);
        color: #fff;
        padding: 1.5rem;
        box-shadow: 0 18px 45px rgba(15,23,42,.16);
    }
    .courses-hero-grid {
        display: grid;
        gap: 1rem;
    }
    .courses-toolbar {
        display: grid;
        gap: .85rem;
        grid-template-columns: 1fr;
    }
    .courses-search {
        display: grid;
        gap: .75rem;
        grid-template-columns: 1fr;
    }
    .courses-search input {
        height: 3.25rem;
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,.35);
        background: rgba(255,255,255,.96);
        padding: 0 1rem;
        font-size: 1rem;
        color: #0f172a;
        outline: none;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }
    .courses-search input:focus {
        border-color: rgba(99,102,241,.8);
        box-shadow: 0 0 0 4px rgba(99,102,241,.14);
    }
    .courses-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .7rem;
    }
    .courses-actions a,
    .courses-actions button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 3.1rem;
        border-radius: 18px;
        font-weight: 800;
        font-size: .95rem;
        letter-spacing: .01em;
        border: 0;
        text-decoration: none;
        cursor: pointer;
    }
    .courses-actions .primary {
        background: linear-gradient(135deg, #7c3aed 0%, #4338ca 100%);
        color: #fff;
        box-shadow: 0 12px 24px rgba(99,102,241,.25);
    }
    .courses-actions .ghost {
        background: rgba(255,255,255,.95);
        color: #0f172a;
        border: 1px solid rgba(148,163,184,.4);
    }
    .courses-actions .teal {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: #fff;
    }
    .courses-actions .blue {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        color: #fff;
    }
    .courses-actions .red {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #fff;
    }
    .category-strip {
        display: flex;
        gap: .5rem;
        overflow-x: auto;
        padding-bottom: .25rem;
        scrollbar-width: none;
    }
    .category-strip::-webkit-scrollbar { display: none; }
    .category-pill {
        flex: 0 0 auto;
        border-radius: 999px;
        padding: .75rem 1rem;
        font-size: .95rem;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(255,255,255,.08);
        color: rgba(255,255,255,.9);
    }
    .category-pill.active {
        background: #fff;
        color: #312e81;
        box-shadow: 0 12px 26px rgba(15,23,42,.18);
    }
    .courses-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }
    .meta-card {
        border-radius: 22px;
        padding: 1rem;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.12);
        backdrop-filter: blur(8px);
    }
    .course-cards-grid {
        display: grid !important;
        gap: 1.25rem;
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }
    @media (min-width: 640px) {
        .course-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .courses-search { grid-template-columns: minmax(0, 1fr) auto; }
        .courses-actions { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .courses-hero-grid { grid-template-columns: minmax(0, 1.2fr) minmax(260px, .8fr); align-items: end; }
    }
    @media (min-width: 1024px) {
        .course-cards-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        .courses-hero { padding: 1.9rem; }
    }
</style>

<section class="courses-shell">
    <div class="courses-hero">
        <div class="courses-hero-grid">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-sm font-semibold text-white/90 ring-1 ring-white/10">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Ders Kataloğu
                </div>
                <div class="space-y-2">
                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Dersler</h1>
                    <p class="max-w-2xl text-sm leading-7 text-slate-200 sm:text-base">
                        Kapak görselleri, hızlı filtreler ve modern aksiyon butonları ile derslerini daha net yönet.
                    </p>
                </div>
                <div class="courses-meta">
                    <div class="meta-card">
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-300">Toplam Ders</div>
                        <div class="mt-1 text-2xl font-black">{{ $totalItems }}</div>
                    </div>
                    <div class="meta-card">
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-300">Filtre</div>
                        <div class="mt-1 text-lg font-bold">{{ $activeCategory }}</div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="category-strip">
                    @foreach($categories as $category)
                        <a
                            href="{{ route('courses.index', array_merge(request()->except('page'), ['category' => $category])) }}"
                            class="category-pill {{ $activeCategory === $category ? 'active' : '' }}"
                        >
                            {{ $category }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" class="courses-search">
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                    <input
                        name="q"
                        value="{{ $q ?? request('q') }}"
                        placeholder="Ders ara..."
                    >
                    <div class="courses-actions">
                        <a href="{{ route('courses.create') }}" class="primary">+ Ders Oluştur</a>
                        <button id="course-import-open" type="button" class="teal">Yükle</button>
                        <a href="{{ route('courses.export-all') }}" class="blue">İndir</a>
                        @if(auth()->user()?->hasRole('admin'))
                            <button type="submit" form="course-destroy-all-form" class="red">Sil</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="course-import-form" method="POST" action="{{ route('courses.import') }}" enctype="multipart/form-data" style="display:none;">
        @csrf
        <input id="course-import-file" type="file" name="course_json[]" accept=".json,application/json,text/plain" multiple style="display:none;">
    </form>
    @if(auth()->user()?->hasRole('admin'))
        <form id="course-destroy-all-form" method="POST" action="{{ route('courses.destroy-all') }}" data-confirm="Tum dersler ve bagli odevler sistemden kaldirilsin mi?" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <div class="course-cards-grid">
        @forelse($items as $item)
            @php
                $slides = (array) data_get($item->lesson_payload, 'slides', []);
                $firstSlide = $slides[0] ?? [];
                $desc = trim((string) data_get($item->lesson_payload, 'lesson_description', ''));
                if ($desc === '') $desc = trim((string) data_get($firstSlide, 'description', ''));
                if ($desc === '') $desc = $item->name . ' dersi icin hazirlanan konu anlatimi ve etkinlik icerikleri.';
                $difficulty = (string) (data_get($item->lesson_payload, 'difficulty') ?: (((int) ($item->weekly_hours ?? 0) >= 4) ? 'Orta' : 'Kolay'));
                $age = ((int) ($item->schoolClass?->name ?? 6) + 5) . '+';
            @endphp
            <x-course-card
                :title="$item->name"
                :description="$desc"
                :image="(string) ($item->coverImageUrl() ?: data_get($firstSlide, 'image_url') ?: '')"
                :logo="asset('logo.png')"
                :age="$age"
                :difficulty="$difficulty"
                :content-url="route('course.detail', ['id' => $item->id])"
                :primary-url="route('courses.edit', $item)"
                :delete-url="($canManageCourses ?? false) ? url('/courses/delete/' . $item->id) : null"
                primary-label="Düzenle"
                :assign-enabled="($canAssignCourses ?? false)"
                :assign-course-id="$item->id"
                :assign-course-name="$item->name"
                :assign-current-teacher="(int) ($item->teacher_id ?? 0)"
                :download-url="route('courses.export', $item)"
            />
        @empty
            <div class="col-span-full rounded-[28px] border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 shadow-sm">
                Henuz ders eklenmedi.
            </div>
        @endforelse
    </div>

    <div class="rounded-2xl bg-white/80 p-3 shadow-sm ring-1 ring-slate-200">
        {{ $items->links() }}
    </div>
</section>

<div id="course-delete-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;z-index:3000;">
    <div style="width:min(92vw,420px);background:#fff;border-radius:14px;padding:18px;box-shadow:0 20px 50px rgba(0,0,0,.18);">
        <h3 style="margin:0 0 8px;font-size:20px;font-weight:800;color:#111827;">Dersi Sil</h3>
        <p style="margin:0 0 14px;color:#334155;">Bu dersi silmek istediginize emin misiniz?</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" id="course-delete-cancel" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">Iptal</button>
            <button type="button" id="course-delete-confirm" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#dc2626;color:#fff;font-weight:700;cursor:pointer;">Evet, Sil</button>
        </div>
    </div>
</div>
<div id="course-assign-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;z-index:3000;">
    <div style="width:min(92vw,460px);background:#fff;border-radius:14px;padding:18px;box-shadow:0 20px 50px rgba(0,0,0,.18);">
        <h3 style="margin:0 0 8px;font-size:20px;font-weight:800;color:#111827;">Dersi Ogretmene Ata</h3>
        <p id="course-assign-title" style="margin:0 0 14px;color:#334155;"></p>
        <div style="display:flex;gap:8px;margin-bottom:10px">
            <button type="button" class="btn" data-assign-tab="teacher">Ogretmene Ata</button>
            <button type="button" class="btn" data-assign-tab="class">Sinif Bazli Ata</button>
            <button type="button" class="btn" data-assign-tab="level">Kademe Bazli Ata</button>
        </div>
        <form id="course-assign-form-teacher" method="POST" data-assign-panel="teacher">
            @csrf
            <label for="course-assign-teacher" style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Ogretmen</label>
            <select id="course-assign-teacher" name="teacher_id" style="width:100%;height:42px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;">
                <option value="">Ogretmen Seciniz</option>
                @foreach(($teachers ?? collect()) as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Ogretmen #'.$teacher->id) }}</option>
                @endforeach
            </select>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" id="course-assign-cancel" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">Iptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
        <form id="course-assign-form-class" method="POST" data-assign-panel="class" style="display:none">
            @csrf
            <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Siniflar</label>
            <div style="max-height:220px;overflow:auto;border:1px solid #cbd5e1;border-radius:10px;padding:8px;">
                @foreach(($teachers ?? collect()) as $noop)@endforeach
                @php $allClasses = \App\Models\SchoolClass::orderBy('grade_level')->orderBy('name')->orderBy('section')->get(); @endphp
                @foreach($allClasses as $class)
                    <label style="display:flex;gap:8px;align-items:center;margin:4px 0">
                        <input type="checkbox" name="class_ids[]" value="{{ $class->id }}">
                        <span>{{ $class->name }}/{{ $class->section }}</span>
                    </label>
                @endforeach
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" class="course-assign-cancel-x" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">Iptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
        <form id="course-assign-form-level" method="POST" data-assign-panel="level" style="display:none">
            @csrf
            <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Kademe</label>
            <select name="grade_level" style="width:100%;height:42px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;">
                @for($lvl=1;$lvl<=12;$lvl++)
                    <option value="{{ $lvl }}">{{ $lvl }}. Sinif</option>
                @endfor
            </select>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" class="course-assign-cancel-x" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">Iptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const importForm = document.getElementById('course-import-form');
    const importFile = document.getElementById('course-import-file');
    const importOpen = document.getElementById('course-import-open');
    importOpen?.addEventListener('click', () => importFile?.click());
    importFile?.addEventListener('change', () => {
        if (!importFile.files || importFile.files.length < 1) return;
        importForm?.submit();
    });

    const modal = document.getElementById('course-delete-modal');
    const cancelBtn = document.getElementById('course-delete-cancel');
    const confirmBtn = document.getElementById('course-delete-confirm');
    let pendingUrl = '';

    document.addEventListener('click', function (e) {
        const link = e.target.closest('.course-delete-link');
        if (!link) return;
        e.preventDefault();
        pendingUrl = link.dataset.deleteUrl || link.getAttribute('href') || '';
        if (!pendingUrl || !modal) return;
        modal.style.display = 'flex';
    });

    cancelBtn?.addEventListener('click', function () {
        pendingUrl = '';
        if (modal) modal.style.display = 'none';
    });

    confirmBtn?.addEventListener('click', function () {
        if (!pendingUrl) return;
        const url = pendingUrl;
        pendingUrl = '';
        if (modal) modal.style.display = 'none';
        window.location.assign(url);
    });

    modal?.addEventListener('click', function (e) {
        if (e.target === modal) {
            pendingUrl = '';
            modal.style.display = 'none';
        }
    });

    const assignModal = document.getElementById('course-assign-modal');
    const assignTitle = document.getElementById('course-assign-title');
    const assignForm = document.getElementById('course-assign-form-teacher');
    const assignFormClass = document.getElementById('course-assign-form-class');
    const assignFormLevel = document.getElementById('course-assign-form-level');
    const assignTeacher = document.getElementById('course-assign-teacher');
    const assignCancel = document.getElementById('course-assign-cancel');
    const assignRouteTemplate = @json(route('courses.assign-teacher', ['course' => '__COURSE_ID__']));
    const assignClassRouteTemplate = @json(route('courses.assign-classes', ['course' => '__COURSE_ID__']));
    const assignLevelRouteTemplate = @json(route('courses.assign-level', ['course' => '__COURSE_ID__']));

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-assign-course-id]');
        if (!btn) return;
        const courseId = btn.getAttribute('data-assign-course-id');
        const courseName = btn.getAttribute('data-assign-course-name') || '';
        const currentTeacher = btn.getAttribute('data-assign-current-teacher') || '';
        if (!courseId || !assignForm || !assignModal) return;
        assignForm.action = String(assignRouteTemplate).replace('__COURSE_ID__', String(courseId));
        assignFormClass.action = String(assignClassRouteTemplate).replace('__COURSE_ID__', String(courseId));
        assignFormLevel.action = String(assignLevelRouteTemplate).replace('__COURSE_ID__', String(courseId));
        if (assignTitle) assignTitle.textContent = `Ders: ${courseName}`;
        if (assignTeacher) assignTeacher.value = String(currentTeacher) === '0' ? '' : String(currentTeacher);
        assignModal.style.display = 'flex';
    });

    assignCancel?.addEventListener('click', function () {
        if (assignModal) assignModal.style.display = 'none';
    });
    document.querySelectorAll('.course-assign-cancel-x').forEach((btn) => btn.addEventListener('click', () => {
        if (assignModal) assignModal.style.display = 'none';
    }));
    document.querySelectorAll('[data-assign-tab]').forEach((btn) => btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-assign-tab');
        document.querySelectorAll('[data-assign-panel]').forEach((p) => p.style.display = (p.getAttribute('data-assign-panel') === tab ? '' : 'none'));
    }));
    assignModal?.addEventListener('click', function (e) {
        if (e.target === assignModal) assignModal.style.display = 'none';
    });
});
</script>
@endsection
