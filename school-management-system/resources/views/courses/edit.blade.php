@extends('layout.app')
@section('title','Ders Olusturucu (Duzenle)')
@section('content')
<div class="top"><h1>Dersi Duzenle</h1></div>
<div class="card">
    <form method="POST" action="{{ route('courses.update', $course) }}">
        @csrf
        @method('PUT')
        @include('courses.partials.builder-form')
    </form>
</div>
@endsection
