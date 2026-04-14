@extends('layout.app')
@section('title','Ogrenciyi Duzenle')
@section('content')
<div class="card">
    <h1>Ogrenciyi Duzenle</h1>
    @php
        $fullName = trim((string) ($student->user?->name ?? ''));
        $parts = preg_split('/\s+/', $fullName, 2) ?: ['', ''];
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';
    @endphp
    <form method="POST" action="{{ route('students.update', $student) }}">
        @csrf
        @method('PUT')

        <label>Ad</label>
        <input name="first_name" value="{{ old('first_name', $firstName) }}" required>

        <label>Soyad</label>
        <input name="last_name" value="{{ old('last_name', $lastName) }}" required>

        <label>Ogrenci No</label>
        <input name="student_no" value="{{ old('student_no', $student->student_no) }}" required>

        <label>Sinif / Sube</label>
        <select name="school_class_id" required>
            <option value="">Seciniz</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected((string) old('school_class_id', $student->school_class_id) === (string) $class->id)>
                    {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                </option>
            @endforeach
        </select>

        <label>Yeni Sifre (bos birakilirsa degismez)</label>
        <input type="password" name="password" autocomplete="new-password">

        <label>Yeni Sifre Tekrar</label>
        <input type="password" name="password_confirmation" autocomplete="new-password">

        <button class="btn" type="submit">Guncelle</button>
    </form>
</div>
@endsection

