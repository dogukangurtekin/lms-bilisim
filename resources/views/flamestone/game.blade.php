@extends('layout.app')
@section('title', 'Flamestone Oyun')
@section('content')
<div class="top"><h1>Flamestone Oyun</h1></div>
<x-game-canvas />
<script type="module" src="{{ asset('js/flamestone/ui.js') }}"></script>
@endsection
