@extends('layout.app')
@section('title','QR Giris')
@section('content')
<div class="top"><h1>QR Giris</h1></div>
<div class="card">
    <h2>Siniflar</h2>
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px">
        @foreach($classes as $class)
            <a class="btn" href="{{ route('qr.login.menu', ['class_id' => $class->id]) }}" style="display:block;text-align:left;padding:10px">
                <b>{{ $class->name }}/{{ $class->section }}</b><br>
                <small>{{ (int) $class->students_count }} ogrenci</small>
            </a>
        @endforeach
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

