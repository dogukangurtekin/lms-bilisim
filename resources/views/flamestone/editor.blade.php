@extends('layout.app')
@section('title', 'Flamestone Editor')
@section('content')
<div class="top"><h1>Flamestone Level Editor</h1></div>
<x-level-editor />
<script type="module" src="{{ asset('js/flamestone/ui.js') }}"></script>
@endsection
