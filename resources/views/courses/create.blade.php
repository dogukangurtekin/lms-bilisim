@extends('layout.app')
@section('title', isset($parentCourse) && $parentCourse ? 'Alt Ders Oluşturucu' : 'Ders Oluşturucu')
@section('content')
<div class="top">
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;justify-content:space-between">
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;justify-content:flex-end">
            @if(!empty($parentCourse))
                <div class="badge" style="display:inline-flex;gap:8px;align-items:center">
                    Ana ders: <strong>{{ $parentCourse->name }}</strong>
                </div>
            @endif
        </div>
    </div>
</div>

@if(!empty($parentCourse))
<form id="sub-course-import-form" method="POST" action="{{ route('courses.import') }}" enctype="multipart/form-data" style="display:none">
    @csrf
    <input type="hidden" name="parent_course_id" value="{{ $parentCourse->id }}">
    <input id="sub-course-import-input" type="file" name="course_json[]" accept=".coursepkg,.json,application/json,text/plain,application/octet-stream" multiple hidden>
</form>

<div id="sub-course-import-progress-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:4000;">
    <div style="width:min(92vw,420px);background:#fff;border-radius:16px;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,.2);text-align:center;">
        <h3 style="margin:0 0 6px;font-size:18px;font-weight:800;color:#111827;">Ders Yükleniyor</h3>
        <p style="margin:0 0 16px;color:#64748b;font-size:13px;">Lütfen bekleyin, dosyanız sunucuya aktarılıyor...</p>
        <div style="height:14px;border-radius:999px;background:#e2e8f0;overflow:hidden;">
            <div id="sub-course-import-progress-bar" style="height:100%;width:0%;border-radius:999px;background:linear-gradient(90deg,#2563eb,#22c55e);transition:width .15s ease;"></div>
        </div>
        <div id="sub-course-import-progress-text" style="margin-top:10px;font-weight:700;color:#2563eb;font-size:14px;">%0</div>
    </div>
</div>
@endif

<div class="card">
    @include('courses.partials.theme-css')
    <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="parent_course_id" value="{{ old('parent_course_id', $parentCourse->id ?? '') }}">
        @include('courses.partials.builder-form')
    </form>
</div>

@if(!empty($parentCourse))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.getElementById('sub-course-import-open');
    const input = document.getElementById('sub-course-import-input');
    const form = document.getElementById('sub-course-import-form');
    const progressModal = document.getElementById('sub-course-import-progress-modal');
    const progressBar = document.getElementById('sub-course-import-progress-bar');
    const progressText = document.getElementById('sub-course-import-progress-text');
    if (!openBtn || !input || !form) return;

    function setProgress(pct) {
        if (!progressBar || !progressText) return;
        const clamped = Math.max(0, Math.min(100, Math.round(pct)));
        progressBar.style.width = clamped + '%';
        progressText.textContent = '%' + clamped;
    }

    openBtn.addEventListener('click', () => {
        input.value = '';
        input.click();
    });

    input.addEventListener('change', () => {
        if (!input.files || input.files.length === 0) return;

        const formData = new FormData(form);
        setProgress(0);
        if (progressModal) progressModal.style.display = 'flex';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);

        xhr.upload.addEventListener('progress', (ev) => {
            if (!ev.lengthComputable) return;
            // Yükleme baytları %90'a kadar; kalan %10 sunucunun kaydedip
            // yönlendirme yapması için ayrılır.
            setProgress((ev.loaded / ev.total) * 90);
        });

        xhr.addEventListener('load', () => {
            if (xhr.status < 200 || xhr.status >= 400) {
                if (progressModal) progressModal.style.display = 'none';
                alert('Ders yüklenirken bir hata oluştu (HTTP ' + xhr.status + '). Lütfen tekrar deneyin.');
                return;
            }
            setProgress(100);
            const fallback = @json(!empty($parentCourse) ? route('course.detail', ['id' => $parentCourse->id]) : route('courses.index'));
            const target = (xhr.responseURL && xhr.responseURL !== form.action) ? xhr.responseURL : fallback;
            setTimeout(() => { window.location.href = target; }, 200);
        });

        xhr.addEventListener('error', () => {
            if (progressModal) progressModal.style.display = 'none';
            alert('Ders yüklenirken bir hata oluştu. Lütfen tekrar deneyin.');
        });

        xhr.send(formData);
    });
});
</script>
@endpush
@endif
@endsection