@extends('layout.app')
@section('title','Sinif Detayi')
@section('content')
<div class="card"><h1>Sinif Detayi</h1>
<p>ID: {{ $classroom->id }}</p><p>Ad: {{ $classroom->name }}</p><p>Sube: {{ $classroom->section }}</p>
</div>
@endsection


