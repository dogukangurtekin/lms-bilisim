@extends('layout.app')
@section('title','Ödevler')
@section('content')
<div class="top">
    <h1>Ödevler</h1>
    <button class="btn" type="button" data-open-modal="homework-create-modal">Ödev Ver</button>
</div>

<div class="card">
    <h3>ÖDEV</h3>
    @php
        $groupedCourseHomeworks = $courseHomeworks->getCollection()->groupBy(function ($h) {
            return implode('|', [
                (string) ($h->course_id ?? 0),
                (string) ($h->title ?? ''),
                (string) optional($h->due_date)->format('Y-m-d'),
                (string) ($h->assignment_type ?? 'lesson'),
                (string) ($h->target_slug ?? ''),
                (string) ($h->level_from ?? ''),
                (string) ($h->level_to ?? ''),
                md5((string) ($h->details ?? '')),
            ]);
        });
    @endphp
    <table>
        <thead><tr><th>Ders</th><th>Başlık</th><th>Sınıf</th><th>Teslim</th><th>Tür</th><th>İşlemler</th></tr></thead>
        <tbody>
        @forelse($groupedCourseHomeworks as $rows)
            @php
                $h = $rows->first();
                $classText = $rows
                    ->filter(fn ($row) => $row->schoolClass)
                    ->groupBy(fn ($row) => (string) $row->schoolClass->name)
                    ->map(function ($sameGradeRows, $grade) {
                        $sections = $sameGradeRows
                            ->map(fn ($row) => strtoupper((string) $row->schoolClass->section))
                            ->filter()
                            ->unique()
                            ->sort()
                            ->values();
                        return $sections->isNotEmpty() ? ($grade . '/' . $sections->implode('-')) : $grade;
                    })
                    ->values()
                    ->implode(', ');
            @endphp
            <tr>
                <td>{{ $h->course?->name ?? '-' }}</td>
                <td>{{ $h->title }}</td>
                <td>{{ $classText !== '' ? $classText : '-' }}</td>
                <td>{{ $h->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ strtoupper($h->assignment_type ?? 'lesson') }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('teacher.assignments.course.show', $h) }}">Önizle</a>
                    <a class="btn" href="{{ route('teacher.assignments.course.edit', $h) }}">Güncelle</a>
                    <form method="POST" action="{{ route('teacher.assignments.course.destroy', $h) }}" data-confirm="Bu ödevi silmek istediğinize emin misiniz? Öğrenci kayıtları korunur.">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Sil</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Ödev bulunmuyor.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $courseHomeworks->links('partials.pagination') }}
</div>

<div class="card">
    <h3>Oyun ve Uygulama Ödevleri</h3>
    <table>
        <thead><tr><th>İçerik</th><th>Başlık</th><th>Teslim</th><th>Level</th><th>İşlemler</th></tr></thead>
        <tbody>
        @forelse($gameAssignments as $a)
            <tr>
                <td>{{ $a->game_name }}</td>
                <td>{{ $a->title }}</td>
                <td>{{ $a->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ $a->level_from ?? '-' }} - {{ $a->level_to ?? '-' }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('teacher.assignments.game.show', $a) }}">Önizle</a>
                    <a class="btn" href="{{ route('teacher.assignments.game.edit', $a) }}">Güncelle</a>
                    <form method="POST" action="{{ route('teacher.assignments.game.destroy', $a) }}" data-confirm="Bu ödevi silmek istediğinize emin misiniz? Öğrenci kayıtları korunur.">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Sil</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Oyun/uygulama ödevi yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $gameAssignments->links('partials.pagination') }}
</div>

<div id="homework-create-modal" class="modal">
    <div class="modal-card">
        <div class="modal-head">
            <strong>Ödev Ver</strong>
            <button class="btn" type="button" data-close-modal>Kapat</button>
        </div>
        <form method="POST" action="{{ route('teacher.assignments.homework.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Ödev Başlığı</label>
            <input name="title" value="{{ old('title') }}" required>

            <label>Sınıf / Şube</label>
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                <input type="checkbox" id="all_classes_toggle" name="all_classes" value="1" @checked(old('all_classes')) style="width:auto;margin:0">
                <span>Tüm Sınıflara Ver</span>
            </label>
            @php $oldClassIds = collect(old('class_ids', []))->map(fn ($id) => (int) $id)->all(); @endphp
            <select name="class_ids[]" id="homework_class_ids" multiple size="8" style="min-height:180px" @disabled(old('all_classes'))>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(in_array((int) $class->id, $oldClassIds, true))>{{ $class->name }}/{{ $class->section }} - {{ $class->academic_year }}</option>
                @endforeach
            </select>
            <small style="display:block;color:#6b7280;margin-top:-6px;margin-bottom:10px">Tek, çoklu veya tüm sınıfları seçebilirsiniz.</small>

            <label>Son Teslim Tarihi</label>
            <input type="date" name="due_date" value="{{ old('due_date') }}">

            <label>Ödev Açıklaması</label>
            <textarea name="details" rows="4">{{ old('details') }}</textarea>

            <label>Belge / Resim Yükleme</label>
            <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp">

            <button class="btn" type="submit">Ödevi Kaydet</button>
        </form>
    </div>
</div>
@push('scripts')
<script>
(() => {
    const allToggle = document.getElementById('all_classes_toggle');
    const classSelect = document.getElementById('homework_class_ids');
    if (!allToggle || !classSelect) return;

    const syncClassSelectionState = () => {
        classSelect.disabled = allToggle.checked;
    };

    allToggle.addEventListener('change', syncClassSelectionState);
    syncClassSelectionState();
})();
</script>
@endpush
@endsection
