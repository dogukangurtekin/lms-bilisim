@extends('layout.app')
@section('title','Dersler')
@section('content')
@php
    $categories = ['Tumu', 'Kodlama', 'Tasarim', 'Elektrik', 'Robotik', 'Teorik', 'Oyun', 'Yapay Zeka'];
    $activeCategory = request('category', 'Tumu');
@endphp
<style>
    .course-search-layout {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: minmax(0, 1fr);
    }
    .course-cards-grid {
        display: grid !important;
        width: 100%;
        gap: 1.5rem;
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }
    @media (min-width: 768px) {
        .course-search-layout {
            grid-template-columns: minmax(0, 1fr) auto;
        }
    }
    @media (min-width: 640px) {
        .course-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
    @media (min-width: 1024px) {
        .course-cards-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }
</style>

<section class="space-y-5">
    <div class="overflow-x-auto">
        <div class="inline-flex min-w-max items-center gap-2 rounded-2xl bg-gray-100 p-1">
            @foreach($categories as $category)
                <a
                    href="{{ route('courses.index', array_merge(request()->except('page'), ['category' => $category])) }}"
                    class="rounded-xl px-4 py-2 text-lg transition {{ $activeCategory === $category ? 'bg-[#ede9fe] font-semibold text-[#4c1d95] shadow' : 'text-gray-600 hover:bg-white/70' }}"
                >
                    {{ $category }}
                </a>
            @endforeach
        </div>
    </div>

    <form method="GET" class="course-search-layout">
        <input type="hidden" name="category" value="{{ $activeCategory }}">
        <input
            name="q"
            value="{{ $q ?? request('q') }}"
            class="h-14 rounded-xl border border-gray-300 bg-white px-5 text-lg text-gray-800 outline-none ring-[#4c1d95] placeholder:text-gray-400 focus:ring-2"
            placeholder="Ders basligini aratmak icin yaziniz."
        >
        <a href="{{ route('courses.create') }}" class="inline-flex h-14 items-center justify-center rounded-xl bg-[#4c1d95] px-6 text-lg font-semibold text-white hover:bg-[#3b0764]">Ders Oluştur</a>
    </form>

    <div class="course-cards-grid">
        @forelse($items as $item)
            @php
                $slides = (array) data_get($item->lesson_payload, 'slides', []);
                $firstSlide = $slides[0] ?? [];
                $desc = trim((string) data_get($item->lesson_payload, 'lesson_description', ''));
                if ($desc === '') $desc = trim((string) data_get($firstSlide, 'description', ''));
                if ($desc === '') $desc = $item->name . ' dersi icin hazirlanan konu anlatimi ve etkinlik icerikleri.';
                $thumb = (string) (data_get($item->lesson_payload, 'cover_image') ?: data_get($firstSlide, 'image_url') ?: '');
                if ($thumb !== '') {
                    $thumb = preg_replace('#^https?://[^/]+/[^/]+/public/storage/#i', '', $thumb);
                    $thumb = preg_replace('#^https?://[^/]+/public/storage/#i', '', $thumb);
                    $thumb = preg_replace('#^https?://[^/]+/storage/#i', '', $thumb);
                    $thumb = preg_replace('#^/?storage/#i', '', $thumb);
                    $thumb = preg_replace('#^/?course-covers/#i', '', $thumb);
                    if (!preg_match('#^https?://#i', $thumb)) {
                        $thumb = route('courses.cover', ['path' => ltrim($thumb, '/')]);
                    }
                }
                $difficulty = (string) (data_get($item->lesson_payload, 'difficulty') ?: (((int) ($item->weekly_hours ?? 0) >= 4) ? 'Orta' : 'Kolay'));
                $age = ((int) ($item->schoolClass?->name ?? 6) + 5) . '+';
            @endphp
            <x-course-card
                :title="$item->name"
                :description="$desc"
                :image="$thumb"
                :logo="asset('logo.png')"
                :age="$age"
                :difficulty="$difficulty"
                :content-url="route('course.detail', ['id' => $item->id])"
                :primary-url="route('courses.edit', $item)"
                :delete-url="auth()->user()?->hasRole('student') ? null : url('/courses/delete/' . $item->id)"
                primary-label="Düzenle"
                :assign-enabled="auth()->user()?->hasRole('admin','teacher')"
                :assign-course-id="$item->id"
                :assign-course-name="$item->name"
                :assign-current-teacher="(int) ($item->teacher_id ?? 0)"
            />
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                Henuz ders eklenmedi.
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
        <form id="course-assign-form" method="POST">
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
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
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
    const assignForm = document.getElementById('course-assign-form');
    const assignTeacher = document.getElementById('course-assign-teacher');
    const assignCancel = document.getElementById('course-assign-cancel');
    const assignRouteTemplate = @json(route('courses.assign-teacher', ['course' => '__COURSE_ID__']));

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-assign-course-id]');
        if (!btn) return;
        const courseId = btn.getAttribute('data-assign-course-id');
        const courseName = btn.getAttribute('data-assign-course-name') || '';
        const currentTeacher = btn.getAttribute('data-assign-current-teacher') || '';
        if (!courseId || !assignForm || !assignModal) return;
        assignForm.action = String(assignRouteTemplate).replace('__COURSE_ID__', String(courseId));
        if (assignTitle) assignTitle.textContent = `Ders: ${courseName}`;
        if (assignTeacher) assignTeacher.value = String(currentTeacher) === '0' ? '' : String(currentTeacher);
        assignModal.style.display = 'flex';
    });

    assignCancel?.addEventListener('click', function () {
        if (assignModal) assignModal.style.display = 'none';
    });
    assignModal?.addEventListener('click', function (e) {
        if (e.target === assignModal) assignModal.style.display = 'none';
    });
});
</script>
@endsection
