@extends('layout.app')
@section('title','Ders Odevi Guncelle')
@section('content')
<div class="top">
    <h1>Ders Ödevi Güncelle</h1>
    <a class="btn" href="{{ route('teacher.assignments.index') }}">Ödevlere Dön</a>
</div>
<div class="card">
    <form method="POST" action="{{ route('teacher.assignments.course.update', $homework) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <label>Ödev Başlığı</label>
        <input name="title" value="{{ old('title', $homework->title) }}" required>

        <label>Son Teslim Tarihi</label>
        <input type="date" name="due_date" value="{{ old('due_date', optional($homework->due_date)->format('Y-m-d')) }}">

        <label>Hangi Sınıfa Verilecek</label>
        <select name="school_class_id" required>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(old('school_class_id', $homework->school_class_id) == $class->id)>
                    {{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}
                </option>
            @endforeach
        </select>

        <label>Ödev Tipi</label>
        <select name="assignment_type" id="assignment_type">
            <option value="lesson" @selected(old('assignment_type', $homework->assignment_type) === 'lesson')>Ders</option>
            <option value="game" @selected(old('assignment_type', $homework->assignment_type) === 'game')>Oyun</option>
            <option value="application" @selected(old('assignment_type', $homework->assignment_type) === 'application')>Uygulama</option>
            <option value="homework" @selected(old('assignment_type', $homework->assignment_type) === 'homework')>Ödev</option>
        </select>

        <div id="content_target_box" style="display:none">
            <label>İçerik Seçimi (Oyun/Uygulama)</label>
            <select name="target_slug">
                <option value="">Seçiniz</option>
                @foreach($games as $slug => $g)
                    <option value="{{ $slug }}" @selected(old('target_slug', $homework->target_slug) === $slug)>{{ $g['name'] }}</option>
                @endforeach
            </select>
            <div class="actions">
                <div style="flex:1">
                    <label>Level Başlangıç</label>
                    <input type="number" min="1" name="level_from" id="level_from" value="{{ old('level_from', $homework->level_from) }}">
                </div>
                <div style="flex:1">
                    <label>Level Bitiş</label>
                    <input type="number" min="1" name="level_to" id="level_to" value="{{ old('level_to', $homework->level_to) }}">
                </div>
            </div>
            <div id="level_points_box"></div>
        </div>

        <label>Ödev Detayı</label>
        <textarea name="details" rows="5">{{ old('details', $homework->details) }}</textarea>
        <label>Belge / Resim Yükleme</label>
        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp">
        @if($homework->attachment_path)
            <p style="margin-top:-6px;margin-bottom:10px;color:#475569;font-size:13px">
                Mevcut dosya: <a href="{{ asset('storage/'.$homework->attachment_path) }}" target="_blank">{{ $homework->attachment_original_name ?? 'Ek dosya' }}</a>
            </p>
        @endif
        <button class="btn" type="submit">Güncelle</button>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('assignment_type');
    const box = document.getElementById('content_target_box');
    const from = document.getElementById('level_from');
    const to = document.getElementById('level_to');
    const pointsBox = document.getElementById('level_points_box');
    const oldPoints = @json($homework->level_points ?? []);
    function toggleBox() {
        box.style.display = (type.value === 'game' || type.value === 'application') ? 'block' : 'none';
    }
    function drawPoints() {
        pointsBox.innerHTML = '';
        const f = parseInt(from.value || '0', 10);
        const t = parseInt(to.value || '0', 10);
        if (!f || !t || t < f) return;
        const title = document.createElement('label');
        title.textContent = 'Level Bazlı Puanlar';
        pointsBox.appendChild(title);
        for (let i = f; i <= t; i++) {
            const row = document.createElement('div');
            row.className = 'actions';
            row.style.marginBottom = '6px';
            const value = Number(oldPoints[String(i)] || 0);
            row.innerHTML = '<span style="min-width:140px">Level ' + i + ' Puanı</span><input type="number" min="0" name="level_points[' + i + ']" value="' + value + '">';
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
