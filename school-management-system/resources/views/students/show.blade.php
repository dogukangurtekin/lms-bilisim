@extends('layout.app')
@section('title','Ogrenci Detayi')
@section('content')
<div class="card"><h1>Ogrenci Detayi</h1>
<p>ID: {{ $student->id }}</p><p>No: {{ $student->student_no }}</p><p>Kullanici: {{ $student->user?->name }}</p>
</div>
@endsection


