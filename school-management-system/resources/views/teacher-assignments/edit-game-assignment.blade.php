@extends('layout.app')
@section('title','Oyun/Uygulama Odevi Guncelle')
@section('content')
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
        <input type="date" name="due_date" value="{{ old('due_date', optional($assignment->due_date)->format('Y-m-d')) }}">

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
        <select name="class_ids[]" multiple required size="8">
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($selectedClassIds->contains($class->id))>
                    {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                </option>
            @endforeach
        </select>

        <div id="level-points-box" style="margin-top:10px"></div>
        <button class="btn" type="submit">Guncelle</button>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const levelFrom = document.getElementById('level_from');
    const levelTo = document.getElementById('level_to');
    const box = document.getElementById('level-points-box');
    const existing = @json($assignment->levels->pluck('points', 'level'));
    function drawLevelPoints() {
        const from = parseInt(levelFrom.value || '0', 10);
        const to = parseInt(levelTo.value || '0', 10);
        box.innerHTML = '';
        if (!from || !to || to < from) return;
        const title = document.createElement('h4');
        title.textContent = 'Level Bazli Puanlar';
        box.appendChild(title);
        for (let i = from; i <= to; i++) {
            const wrap = document.createElement('div');
            wrap.className = 'actions';
            wrap.style.marginBottom = '6px';
            const val = Number(existing[String(i)] || 0);
            wrap.innerHTML = '<label style="min-width:150px">Level ' + i + ' Puani</label><input type="number" name="points[' + i + ']" min="0" value="' + val + '">';
            box.appendChild(wrap);
        }
    }
    levelFrom.addEventListener('input', drawLevelPoints);
    levelTo.addEventListener('input', drawLevelPoints);
    drawLevelPoints();
});
</script>
@endsection

