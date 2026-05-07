@extends('layout.app')
@section('title','QR Giris')
@section('content')
<div class="top"><h1>QR Giris</h1></div>
<div class="card" style="position:sticky;top:70px;z-index:50">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
        <h2 style="margin:0">Siniflar</h2>
        @if($selectedClass)
            <a class="btn" href="{{ route('qr.login.menu') }}">Siniflara Geri Don</a>
        @endif
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:8px;margin-top:8px">
        <select class="form-control" onchange="if(this.value){window.location.href=this.value}">
            <option value="{{ route('qr.login.menu') }}" {{ $selectedClass ? '' : 'selected' }}>Sinif secin...</option>
            @foreach($classes as $class)
                <option value="{{ route('qr.login.menu', ['class_id' => $class->id]) }}" {{ (int) $selectedClassId === (int) $class->id ? 'selected' : '' }}>
                    {{ $class->name }}/{{ $class->section }} ({{ (int) $class->students_count }} ogrenci)
                </option>
            @endforeach
        </select>
        @if($selectedClass)
            <div style="font-size:13px;color:#334155">
                Secili sinif: <b>{{ $selectedClass->name }}/{{ $selectedClass->section }}</b>
            </div>
        @endif
    </div>
</div>
<div class="card">
    <h2>Ogrenciler</h2>
    @if($students->isEmpty())
        <p>Sinif secin.</p>
    @else
        <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px">
            @foreach($students as $student)
                <a class="btn" href="{{ route('qr.login.scanner', $student) }}" style="display:block;text-align:left;padding:10px">
                    <b>{{ $student->user?->name ?? ('Ogrenci #'.$student->id) }}</b><br>
                    <small>No: {{ $student->student_no ?: '-' }}</small>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
