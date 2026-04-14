@extends('layout.app')
@section('title','Ders Odevi Ver')
@section('content')
<div class="top">
    <h1>{{ $course->name }} - Odev Ver</h1>
    <a class="btn" href="{{ route('courses.index') }}">Derslere Don</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('courses.homeworks.store', $course) }}">
        @csrf
        <label>Odev Basligi</label>
        <input name="title" value="{{ old('title') }}" required>

        <label>Son Teslim Tarihi</label>
        <input type="date" name="due_date" value="{{ old('due_date') }}">

        <label>Hangi Siniflara Verilecek</label>
        @php $oldClassIds = collect(old('class_ids', [$course->school_class_id]))->map(fn ($id) => (int) $id)->all(); @endphp
        <select name="class_ids[]" multiple required size="8">
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(in_array((int) $class->id, $oldClassIds, true))>
                    {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                </option>
            @endforeach
        </select>
        <small style="display:block;color:#64748b;margin-top:4px">Birden fazla sinif secmek icin Ctrl/Command tusu ile secim yapabilirsiniz.</small>

        <label>Odev Tipi</label>
        <select name="assignment_type" id="assignment_type">
            <option value="lesson">Ders</option>
            <option value="game">Oyun</option>
            <option value="application">Uygulama</option>
        </select>

        <div id="content_target_box" style="display:none">
            <label>Icerik Secimi (Oyun/Uygulama)</label>
            <select name="target_slug">
                <option value="">Seciniz</option>
                @foreach($games as $slug => $g)
                    <option value="{{ $slug }}">{{ $g['name'] }}</option>
                @endforeach
            </select>
            <div class="actions">
                <div style="flex:1">
                    <label>Level Baslangic</label>
                    <input type="number" min="1" name="level_from" id="level_from" value="{{ old('level_from') }}">
                </div>
                <div style="flex:1">
                    <label>Level Bitis</label>
                    <input type="number" min="1" name="level_to" id="level_to" value="{{ old('level_to') }}">
                </div>
            </div>
            <div id="level_points_box"></div>
        </div>

        <label>Odev Detayi</label>
        <textarea name="details" rows="5" placeholder="Ogrencinin ne yapacagini yazin...">{{ old('details') }}</textarea>

        @if($errors->any())
            <div style="color:#b91c1c;margin:8px 0">{{ $errors->first() }}</div>
        @endif
        <button class="btn" type="submit">Odevi Kaydet</button>
    </form>
</div>

<div class="card">
    <h3>Bu Derste Verilen Son Odevler</h3>
    <table>
        <thead><tr><th>Baslik</th><th>Sinif</th><th>Teslim</th><th>Detay</th></tr></thead>
        <tbody>
        @forelse($homeworks as $hw)
            <tr>
                <td>{{ $hw->title }}</td>
                <td>{{ $hw->schoolClass?->name }}/{{ $hw->schoolClass?->section }}</td>
                <td>{{ $hw->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ strtoupper($hw->assignment_type) }} {{ $hw->target_slug ? '| ' . $hw->target_slug : '' }} {{ $hw->level_from ? '| L'.$hw->level_from.'-'.$hw->level_to : '' }}<br>{{ $hw->details ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Henuz odev yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('assignment_type');
    const box = document.getElementById('content_target_box');
    const from = document.getElementById('level_from');
    const to = document.getElementById('level_to');
    const pointsBox = document.getElementById('level_points_box');
    function toggleBox() {
        box.style.display = (type.value === 'game' || type.value === 'application') ? 'block' : 'none';
    }
    function drawPoints() {
        pointsBox.innerHTML = '';
        const f = parseInt(from.value || '0', 10);
        const t = parseInt(to.value || '0', 10);
        if (!f || !t || t < f) return;
        const title = document.createElement('label');
        title.textContent = 'Level Bazli Puanlar';
        pointsBox.appendChild(title);
        for (let i = f; i <= t; i++) {
            const row = document.createElement('div');
            row.className = 'actions';
            row.style.marginBottom = '6px';
            row.innerHTML = '<span style="min-width:140px">Level ' + i + ' Puani</span><input type="number" min="0" name="level_points[' + i + ']" value="10">';
            pointsBox.appendChild(row);
        }
    }
    type.addEventListener('change', toggleBox);
    from.addEventListener('input', drawPoints);
    to.addEventListener('input', drawPoints);
    toggleBox();
    drawPoints();
});
</script>
@endsection
