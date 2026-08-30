@extends('layout.app')
@section('title','Oyun/Uygulama Odevi Guncelle')
@section('content')
<style>
    .assignment-class-checklist{
        display:grid;
        grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
        gap:8px;
        max-height:280px;
        overflow-y:auto;
        padding:10px;
        border:1px solid var(--line,#d1d9e6);
        border-radius:12px;
        background:var(--paper,#f8fafc);
    }
    .assignment-class-checklist label{
        display:flex;
        align-items:center;
        gap:8px;
        padding:8px 10px;
        border:1px solid var(--line,#e2e8f0);
        border-radius:10px;
        background:#fff;
        cursor:pointer;
        min-width:0;
        font-size:13.5px;
    }
    .assignment-class-checklist label:has(input:checked){
        border-color:var(--violet,#5B3DF5);
        background:var(--violet-tint,#EEEBFD);
    }
    .assignment-class-checklist input{
        width:16px;
        height:16px;
        flex:0 0 auto;
    }
</style>
<div class="top">
    <h1>Oyun/Uygulama Odevi Guncelle</h1>
    <a class="btn" href="{{ route('teacher.assignments.index') }}">Odevlere Don</a>
</div>
<div class="card">
    <form method="POST" action="{{ route('teacher.assignments.game.update', $assignment) }}">
        @csrf
        @method('PUT')
        <label>Odev Adi</label>
        <input name="title" value="{{ old('title', $assignment->title) }}" required>

        <label>Odev Teslim Tarihi</label>
        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', optional($assignment->due_date)->format('Y-m-d')) }}">

        <div class="actions">
            <div style="min-width:220px;flex:1">
                <label>Level Baslangic</label>
                <input type="number" name="level_from" id="level_from" min="1" value="{{ old('level_from', $assignment->level_from) }}">
            </div>
            <div style="min-width:220px;flex:1">
                <label>Level Bitis</label>
                <input type="number" name="level_to" id="level_to" min="1" value="{{ old('level_to', $assignment->level_to) }}">
            </div>
        </div>

        <label>Odev Verilecek Siniflar</label>
        @php $selectedClassIds = collect(old('class_ids', $assignment->classes->pluck('id')->all())); @endphp
        <div class="assignment-class-checklist">
            @foreach($classes as $class)
                <label>
                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" @checked($selectedClassIds->contains($class->id))>
                    {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                </label>
            @endforeach
        </div>

        <button class="btn" type="submit" style="margin-top:12px">Guncelle</button>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dueDate = document.getElementById('due_date');
    if (dueDate) {
        dueDate.addEventListener('click', function () {
            if (typeof dueDate.showPicker === 'function') {
                try { dueDate.showPicker(); } catch (_) {}
            }
        });
    }
});
</script>
@endsection