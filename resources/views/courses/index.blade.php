@extends('layout.app')
@section('title','Dersler')
@section('content')
@php
    $categories = ['Tumu', 'Kodlama', 'Tasarim', 'Elektrik', 'Robotik', 'Teorik', 'Oyun', 'Yapay Zeka'];
    $activeCategory = request('category', 'Tumu');
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
        display: grid;
        gap: .75rem;
        grid-template-columns: minmax(0, 1fr);
    }
    .course-search-layout input[type="text"] {
        height: 3.5rem;
        border-radius: 14px;
        border: 1px solid #d1d5db;
        background: #fff;
        padding: 0 1rem;
        font-size: 1.05rem;
        color: #1f2937;
        outline: none;
    }
    .course-search-layout input[type="text"]:focus {
        border-color: #4c1d95;
        box-shadow: 0 0 0 3px rgba(76,29,149,.12);
    }
    .course-action-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .course-action-grid a,
    .course-action-grid button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 3.5rem;
        border-radius: 14px;
        font-size: 1.05rem;
        font-weight: 700;
        color: #fff;
        text-decoration: none;
        border: 0;
        cursor: pointer;
    }
    .btn-create { background: #5b21b6; }
    .btn-upload { background: #0f766e; }
    .btn-download { background: #2563eb; }
    .btn-delete { background: #ef4444; }
    .course-cards-grid {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    @media (min-width: 768px) {
        .course-search-layout {
            grid-template-columns: minmax(0, 1fr) auto;
        }
        .course-action-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (min-width: 640px) {
        .course-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .course-cards-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
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
            <div class="course-action-grid">
                <a href="{{ route('courses.create') }}" class="btn-create">Ders Oluştur</a>
                <button id="course-import-open" type="button" class="btn-upload">Yükle</button>
                <a href="{{ route('courses.export-all') }}" class="btn-download">İndir</a>
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
    @if(auth()->user()?->hasRole('admin'))
        <form id="course-destroy-all-form" method="POST" action="{{ route('courses.destroy-all') }}" data-confirm="Tüm dersler ve bağlı ödevler sistemden kaldırılsın mı?" style="display:none;">
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
                $thumb = (string) ($item->coverImageUrl() ?: data_get($firstSlide, 'image_url') ?: '');
                $difficulty = (string) (data_get($item->lesson_payload, 'difficulty') ?: (((int) ($item->weekly_hours ?? 0) >= 4) ? 'Orta' : 'Kolay'));
                $age = ((int) ($item->schoolClass?->name ?? 6) + 5) . '+';
            @endphp
            <x-course-card
                :title="$item->name"
                :description="$desc"
                :image="$thumb"
                :logo="url('/public/logo.png')"
                :age="$age"
                :difficulty="$difficulty"
                :content-url="route('course.detail', ['id' => $item->id])"
                :primary-url="route('courses.edit', $item)"
                primary-label="Düzenle"
                :download-url="route('courses.export', $item)"
                :delete-url="auth()->user()?->hasRole('student') ? null : url('/courses/delete/' . $item->id)"
                :assign-enabled="auth()->user()?->hasRole('admin','teacher')"
                :assign-course-id="$item->id"
                :assign-course-name="$item->name"
                :assign-current-teacher="(int) ($item->teacher_id ?? 0)"
            />
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                Henüz ders eklenmedi.
            </div>
        @endforelse
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
        <h3 style="margin:0 0 8px;font-size:20px;font-weight:800;color:#111827;">Dersi Öğretmene Ata</h3>
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
                    <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Öğretmen #'.$teacher->id) }}</option>
                @endforeach
            </select>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" id="course-assign-cancel" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
        <form id="course-assign-form-class" method="POST" data-assign-panel="class" style="display:none">
            @csrf
            <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Sınıflar</label>
            <div style="max-height:220px;overflow:auto;border:1px solid #cbd5e1;border-radius:10px;padding:8px;">
                @php $allClasses = \App\Models\SchoolClass::orderBy('grade_level')->orderBy('name')->orderBy('section')->get(); @endphp
                @foreach($allClasses as $class)
                    <label style="display:flex;gap:8px;align-items:center;margin:4px 0">
                        <input type="checkbox" name="class_ids[]" value="{{ $class->id }}">
                        <span>{{ $class->name }}/{{ $class->section }}</span>
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
            <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Kademe</label>
            <select name="grade_level" style="width:100%;height:42px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;">
                @for($g = 1; $g <= 12; $g++)
                    <option value="{{ $g }}">{{ $g }}. Kademe</option>
                @endfor
            </select>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
                <button type="button" class="course-assign-cancel-x" style="height:42px;padding:0 14px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
                <button type="submit" style="height:42px;padding:0 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Ata</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('course-delete-modal');
    const deleteCancel = document.getElementById('course-delete-cancel');
    const deleteConfirm = document.getElementById('course-delete-confirm');
    let pendingDeleteUrl = '';

    document.addEventListener('click', function (e) {
        const deleteLink = e.target.closest('.course-delete-link');
        if (deleteLink) {
            e.preventDefault();
            pendingDeleteUrl = deleteLink.dataset.deleteUrl || deleteLink.getAttribute('href') || '';
            if (pendingDeleteUrl && deleteModal) deleteModal.style.display = 'flex';
            return;
        }

        const assignBtn = e.target.closest('[data-assign-course-id]');
        if (!assignBtn) return;
        const courseId = assignBtn.getAttribute('data-assign-course-id');
        const courseName = assignBtn.getAttribute('data-assign-course-name') || '';
        const currentTeacher = assignBtn.getAttribute('data-assign-current-teacher') || '';
        const assignModal = document.getElementById('course-assign-modal');
        const assignTitle = document.getElementById('course-assign-title');
        const assignFormTeacher = document.getElementById('course-assign-form-teacher');
        const assignFormClass = document.getElementById('course-assign-form-class');
        const assignFormLevel = document.getElementById('course-assign-form-level');
        const assignTeacher = document.getElementById('course-assign-teacher');
        if (!courseId || !assignModal || !assignFormTeacher || !assignFormClass || !assignFormLevel) return;
        const teacherRoute = @json(route('courses.assign-teacher', ['course' => '__COURSE_ID__']));
        const classRoute = @json(route('courses.assign-classes', ['course' => '__COURSE_ID__']));
        const levelRoute = @json(route('courses.assign-level', ['course' => '__COURSE_ID__']));
        assignFormTeacher.action = String(teacherRoute).replace('__COURSE_ID__', String(courseId));
        assignFormClass.action = String(classRoute).replace('__COURSE_ID__', String(courseId));
        assignFormLevel.action = String(levelRoute).replace('__COURSE_ID__', String(courseId));
        if (assignTitle) assignTitle.textContent = 'Ders: ' + courseName;
        if (assignTeacher) assignTeacher.value = String(currentTeacher) === '0' ? '' : String(currentTeacher);
        assignFormTeacher.style.display = '';
        assignFormClass.style.display = 'none';
        assignFormLevel.style.display = 'none';
        assignModal.style.display = 'flex';
    });

    deleteCancel?.addEventListener('click', function () {
        pendingDeleteUrl = '';
        if (deleteModal) deleteModal.style.display = 'none';
    });
    deleteConfirm?.addEventListener('click', function () {
        if (!pendingDeleteUrl) return;
        const url = pendingDeleteUrl;
        pendingDeleteUrl = '';
        if (deleteModal) deleteModal.style.display = 'none';
        window.location.assign(url);
    });

    document.querySelectorAll('.course-assign-cancel-x, #course-assign-cancel').forEach((btn) => {
        btn.addEventListener('click', function () {
            const assignModal = document.getElementById('course-assign-modal');
            if (assignModal) assignModal.style.display = 'none';
        });
    });

    document.querySelectorAll('[data-assign-tab]').forEach((btn) => btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-assign-tab');
        document.querySelectorAll('[data-assign-panel]').forEach((panel) => {
            panel.style.display = panel.getAttribute('data-assign-panel') === tab ? '' : 'none';
        });
    }));

    deleteModal?.addEventListener('click', function (e) {
        if (e.target === deleteModal) {
            pendingDeleteUrl = '';
            deleteModal.style.display = 'none';
        }
    });
    const assignModal = document.getElementById('course-assign-modal');
    assignModal?.addEventListener('click', function (e) {
        if (e.target === assignModal) assignModal.style.display = 'none';
    });

    const importOpen = document.getElementById('course-import-open');
    const importFile = document.getElementById('course-import-file');
    importOpen?.addEventListener('click', () => importFile?.click());
    importFile?.addEventListener('change', () => {
        const form = document.getElementById('course-import-form');
        if (form && importFile.files && importFile.files.length) form.submit();
    });
});
</script>
@endsection
