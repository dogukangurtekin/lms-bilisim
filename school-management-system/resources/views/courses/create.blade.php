@extends('layout.app')
@section('title','Ders Olusturucu')
@section('content')
<div class="top"><h1>Ders Olusturucu</h1></div>
<div class="card">
    <form method="POST" action="{{ route('courses.store') }}">
        @csrf
        @include('courses.partials.builder-form')
    </form>
</div>
@endsection
