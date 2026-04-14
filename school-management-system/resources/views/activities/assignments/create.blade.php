@extends('layout.app')

@section('title', 'Etkinlik Odevi Ver')

@section('content')
<div class="top">
    <h1>{{ $game['name'] }} - Odev Ver</h1>
    <a class="btn" href="{{ route('activities.index') }}">Etkinliklere Don</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('activities.assignments.store', $gameSlug) }}" id="assignment-form">
        @csrf
        <label>Odev Adi</label>
        <input name="title" value="{{ old('title') }}" required>

        <label>Odev Teslim Tarihi</label>
        <input type="date" name="due_date" value="{{ old('due_date') }}">

        <div class="actions">
            <div style="min-width:220px;flex:1">
                <label>Level Baslangic</label>
                <input type="number" name="level_from" id="level_from" min="1" value="{{ old('level_from') }}">
            </div>
            <div style="min-width:220px;flex:1">
                <label>Level Bitis</label>
                <input type="number" name="level_to" id="level_to" min="1" value="{{ old('level_to') }}">
            </div>
        </div>

        <label>Odev Verilecek Siniflar</label>
        <select name="class_ids[]" multiple required size="8">
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(collect(old('class_ids', []))->contains($class->id))>
                    {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                </option>
            @endforeach
        </select>

        <div id="level-points-box" style="margin-top:10px"></div>
        @if($errors->any())
            <div style="color:#b91c1c;margin:10px 0">{{ $errors->first() }}</div>
        @endif

        <button class="btn" type="submit">Odevi Kaydet</button>
    </form>
</div>

<div class="card">
    <h3>Son Olusturulan Odevler</h3>
    <table>
        <thead>
        <tr><th>Odev</th><th>Teslim</th><th>Level Araligi</th><th>Siniflar</th><th>Puanlar</th></tr>
        </thead>
        <tbody>
        @forelse($recentAssignments as $assignment)
            <tr>
                <td>{{ $assignment->title }}</td>
                <td>{{ $assignment->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ $assignment->level_from ?? '-' }} - {{ $assignment->level_to ?? '-' }}</td>
                <td>{{ $assignment->classes->map(fn($c) => $c->name . '/' . $c->section)->implode(', ') }}</td>
                <td>{{ $assignment->levels->map(fn($l) => 'L' . $l->level . ':' . $l->points)->implode(', ') }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Henuz odev yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const levelFrom = document.getElementById('level_from');
    const levelTo = document.getElementById('level_to');
    const box = document.getElementById('level-points-box');

    function drawLevelPoints() {
        const from = parseInt(levelFrom.value || '0', 10);
        const to = parseInt(levelTo.value || '0', 10);
        box.innerHTML = '';

        if (!from || !to || to < from) {
            return;
        }

        const title = document.createElement('h4');
        title.textContent = 'Level Bazli Puanlar';
        box.appendChild(title);

        for (let i = from; i <= to; i++) {
            const wrap = document.createElement('div');
            wrap.className = 'actions';
            wrap.style.marginBottom = '6px';

            const label = document.createElement('label');
            label.textContent = 'Level ' + i + ' Puani';
            label.style.minWidth = '150px';

            const input = document.createElement('input');
            input.type = 'number';
            input.name = 'points[' + i + ']';
            input.min = '0';
            input.value = '{{ old('points') ? '' : '10' }}';

            wrap.appendChild(label);
            wrap.appendChild(input);
            box.appendChild(wrap);
        }
    }

    levelFrom.addEventListener('input', drawLevelPoints);
    levelTo.addEventListener('input', drawLevelPoints);
    drawLevelPoints();
});
</script>
@endsection

