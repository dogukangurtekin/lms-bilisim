@extends('layout.app')

@section('title', 'Etkinlik Ödevi Ver')

@section('content')
@php
    $ownerFilter = $ownerFilter ?? 'teacher';
    $ownerLabels = $ownerLabels ?? [
        'admin' => 'Admin ödevleri',
        'teacher' => 'Öğretmen ödevleri',
        'all' => 'Tüm ödevler',
    ];
    $isAdmin = auth()->user()?->hasRole('admin') === true;
@endphp

<style>
    .assignment-form-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:12px;
    }
    .assignment-form-grid .field{
        min-width:0;
    }
    .assignment-form-grid input,
    .assignment-form-grid select,
    .assignment-form-grid textarea{
        width:100%;
        box-sizing:border-box;
    }
    .assignment-meta-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }
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
    @media (max-width: 760px){
        .assignment-form-grid,
        .assignment-meta-grid{
            grid-template-columns:1fr;
        }
        .top{
            gap:8px;
        }
    }
</style>

<div class="top">
    <h1>{{ $game['name'] }} - Ödev Ver</h1>
    <a class="btn" href="{{ route('activities.index') }}">Etkinliklere Dön</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('activities.assignments.store', $gameSlug) }}" id="assignment-form">
        @csrf
        <div class="assignment-form-grid">
            <div class="field">
                <label>Ödev Adı</label>
                <input name="title" value="{{ old('title') }}" required>
            </div>
            <div class="field">
                <label>Ödev Teslim Tarihi</label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}">
            </div>
        </div>

        <div class="assignment-meta-grid" style="margin-top:12px;">
            <div class="field">
                <label>Level Başlangıç</label>
                <input type="number" name="level_from" id="level_from" min="1" value="{{ old('level_from') }}">
            </div>
            <div class="field">
                <label>Level Bitiş</label>
                <input type="number" name="level_to" id="level_to" min="1" value="{{ old('level_to') }}">
            </div>
        </div>

        <div class="field" style="margin-top:12px;">
            <label>Ödev Verilecek Sınıflar</label>
            <div class="assignment-class-checklist">
                @foreach($classes as $class)
                    <label>
                        <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" @checked(collect(old('class_ids', []))->contains($class->id))>
                        {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                    </label>
                @endforeach
            </div>
        </div>

        @if($errors->any())
            <div style="color:#b91c1c;margin:10px 0">{{ $errors->first() }}</div>
        @endif

        <button class="btn" type="submit">Ödevi Kaydet</button>
    </form>
</div>

<div class="card">
    <h3>Son Oluşturulan Ödevler</h3>
    @if($isAdmin)
        <form method="GET" action="{{ route('activities.assignments.create', $gameSlug) }}" style="margin:0 0 12px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div>
                <label style="display:block;margin-bottom:6px;font-weight:700;">Gösterim Filtresi</label>
                <select name="owner" onchange="this.form.submit()" style="min-width:220px;height:42px;padding:0 12px;border:1px solid #cfd8e3;border-radius:12px;background:#fff;">
                    <option value="admin" @selected($ownerFilter === 'admin')>{{ $ownerLabels['admin'] }}</option>
                    <option value="teacher" @selected($ownerFilter === 'teacher')>{{ $ownerLabels['teacher'] }}</option>
                    <option value="all" @selected($ownerFilter === 'all')>{{ $ownerLabels['all'] }}</option>
                </select>
            </div>
        </form>
    @endif
    <table>
        <thead>
        <tr><th>Ödev</th><th>Teslim</th><th>Level Aralığı</th><th>Sınıflar</th><th>Veren</th><th>Puanlar</th></tr>
        </thead>
        <tbody>
        @forelse($recentAssignments as $assignment)
            <tr>
                <td>{{ $assignment->title }}</td>
                <td>{{ $assignment->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ $assignment->level_from ?? '-' }} - {{ $assignment->level_to ?? '-' }}</td>
                <td>{{ $assignment->classes->map(fn($c) => $c->name . '/' . $c->section)->implode(', ') }}</td>
                <td>{{ $assignment->creator?->name ?? '-' }}</td>
                <td>{{ $assignment->levels->map(fn($l) => 'L' . $l->level . ':' . $l->points)->implode(', ') }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Henüz ödev yok.</td></tr>
        @endforelse
        </tbody>
    </table>
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