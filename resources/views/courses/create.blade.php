@extends('layout.app')
@section('title', isset($parentCourse) && $parentCourse ? 'Alt Ders Oluşturucu' : 'Ders Oluşturucu')
@section('content')
<div class="top">
    <h1>{{ isset($parentCourse) && $parentCourse ? 'Alt Ders Oluşturucu' : 'Ders Oluşturucu' }}</h1>
    @if(!empty($parentCourse))
        <div class="badge" style="margin-top:8px;display:inline-flex;gap:8px;align-items:center">
            Ana ders: <strong>{{ $parentCourse->name }}</strong>
        </div>
    @endif
</div>
<div class="card">
    @include('courses.partials.theme-css')
    <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="parent_course_id" value="{{ old('parent_course_id', $parentCourse->id ?? '') }}">
        @include('courses.partials.builder-form')
    </form>
</div>
@endsection
