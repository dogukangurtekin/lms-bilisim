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
    if (!openBtn || !input || !form) return;

    openBtn.addEventListener('click', () => {
        input.value = '';
        input.click();
    });

    input.addEventListener('change', () => {
        if (input.files && input.files.length > 0) {
            form.submit();
        }
    });
});
</script>
@endpush
@endif
@endsection