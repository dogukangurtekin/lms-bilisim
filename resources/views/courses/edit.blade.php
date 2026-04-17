@extends('layout.app')
@section('title','Ders Oluşturucu (Düzenle)')
@section('content')
<div class="top"><h1>Dersi Düzenle</h1></div>
<div class="card">
    <form id="delete-course-form" method="POST" action="{{ route('courses.destroy', $course) }}" style="display:none">
        @csrf
        @method('DELETE')
    </form>
    <form method="POST" action="{{ route('courses.update', $course) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('courses.partials.builder-form')
    </form>
</div>
@endsection
