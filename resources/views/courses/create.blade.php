@extends('layout.app')
@section('title','Ders Oluşturucu')
@section('content')
<div class="top"><h1>Ders Oluşturucu</h1></div>
<div class="card">
    <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">
        @csrf
        @include('courses.partials.builder-form')
    </form>
</div>
@endsection
