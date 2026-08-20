@extends('layout.app')
@section('title','Ders Olusturucu (Duzenle)')
@section('content')
<div class="top"><h1>Dersi Duzenle</h1></div>
<div class="card">
    @include('courses.partials.theme-css')
    <form method="POST" action="{{ route('courses.update.post', $course) }}" enctype="multipart/form-data">
        @csrf
        @include('courses.partials.builder-form')
    </form>
</div>
@endsection
