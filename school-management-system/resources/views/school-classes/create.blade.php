@extends('layout.app')
@section('title','Sinif Olustur')
@section('content')
<div class="card"><h1>Sinif Olustur</h1>
<form method="POST" action="{{ route('classes.store') }}">@csrf
<label>Ad</label><input name='name' value='{{ old('name') }}'><label>Sube</label><input name='section' value='{{ old('section') }}'><label>Grade Level</label><input name='grade_level' value='{{ old('grade_level') }}'><label>Ogretmen ID</label><input name='teacher_id' value='{{ old('teacher_id') }}'><label>Academic Year</label><input name='academic_year' value='{{ old('academic_year','2026-2027') }}'>
<button class="btn" type="submit">Kaydet</button>
</form></div>
@endsection

