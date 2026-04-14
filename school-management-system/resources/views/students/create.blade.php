@extends('layout.app')
@section('title','Ogrenci Olustur')
@section('content')
<div class="card"><h1>Ogrenci Olustur</h1>
<form method="POST" action="{{ route('students.store') }}">@csrf
<label>Kullanici ID</label><input name='user_id' value='{{ old('user_id') }}'><label>Student No</label><input name='student_no' value='{{ old('student_no') }}'><label>Sinif ID</label><input name='school_class_id' value='{{ old('school_class_id') }}'><label>Veli Adi</label><input name='parent_name' value='{{ old('parent_name') }}'>
<button class="btn" type="submit">Kaydet</button>
</form></div>
@endsection

