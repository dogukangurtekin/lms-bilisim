@extends('layout.app')
@section('title','Ogretmene Sinif Ata')
@section('content')
<div class="top"><h1>Ogretmen Sinif Atama</h1></div>
<div class="card">
<h3>Kademe Bazli Atama</h3>
<form method="POST" action="{{ route('users.teachers.classes.assign-level', $teacher) }}" class="actions" style="align-items:end;flex-wrap:wrap">@csrf
<div><label>Kademe</label><select name="grade_level" required>@foreach($levels as $lvl)<option value="{{ $lvl }}">{{ $lvl }}. Sinif</option>@endforeach</select></div>
<div><button class="btn" type="submit">Kademeyi Ata</button></div>
</form>
<h3>Sinif Sinif Atama</h3>
<form method="POST" action="{{ route('users.teachers.classes.assign-classes', $teacher) }}">@csrf
<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px">@foreach($classes as $class)<label style="display:flex;gap:8px;align-items:center;border:1px solid #e5e7eb;padding:8px;border-radius:8px"><input type="checkbox" name="class_ids[]" value="{{ $class->id }}" @checked(in_array($class->id, $assignedClassIds, true))><span>{{ $class->name }} / {{ $class->section }} ({{ $class->grade_level }}. sinif)</span></label>@endforeach</div>
<div style="margin-top:10px"><button class="btn" type="submit">Sinif Atamalarini Kaydet</button></div>
</form>
</div>
@endsection
