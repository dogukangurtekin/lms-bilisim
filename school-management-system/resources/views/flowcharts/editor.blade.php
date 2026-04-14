@extends('layout.app')

@section('title', 'Flowchart Programming')

@section('content')
<div class="top">
    <h1>Flowchart Programming (Çalıştırılabilir Algoritma Editörü)</h1>
</div>

<div class="card">
    <div id="flowchart-editor-app"></div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/flowchart-editor-entry.js')
@endpush

