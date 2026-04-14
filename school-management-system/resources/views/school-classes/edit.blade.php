@extends('layout.app')
@section('title','Sinifi Duzenle')
@section('content')
<div class="card"><h1>Sinifi Duzenle</h1>
<form method="POST" action="{{ route('classes.update', $classroom) }}">@csrf @method('PUT')
<label>Ad</label><input name='name' value='{{ old('name', $classroom->name) }}'><label>Sube</label><input name='section' value='{{ old('section', $classroom->section) }}'><label>Grade Level</label><input name='grade_level' value='{{ old('grade_level', $classroom->grade_level) }}'><label>Ogretmen ID</label><input name='teacher_id' value='{{ old('teacher_id', $classroom->teacher_id) }}'><label>Academic Year</label><input name='academic_year' value='{{ old('academic_year', $classroom->academic_year) }}'>
<button class="btn" type="submit">Guncelle</button>
</form></div>
@endsection

