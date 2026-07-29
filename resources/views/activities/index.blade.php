@extends('layout.app')

@section('title', 'Oyun ve Etkinlikler')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = (bool) ($user?->hasRole('admin') ?? false);
    $isTeacher = (bool) ($user?->hasRole('teacher') ?? false);
@endphp

<div class="top">
    <h1>Oyun ve Etkinlikler</h1>
    @if($isAdmin)
        <span class="muted">Tum oyunlar ve etkinlikler burada listelenir.</span>
    @elseif($isTeacher)
        <span class="muted">Sadece size atanan oyun ve etkinlikler gosterilir.</span>
    @else
        <span class="muted">Ogrenci modunda oynanabilir oyunlar gosterilir.</span>
    @endif
</div>

<div class="card">
    @if($isAdmin)
        <p>Aşağıdaki oyunlar seviye tabanlı ilerleme ve ödevleme için hazırdır.</p>
        <div style="margin:18px 0;padding:18px;border:1px solid #dbe7ff;border-radius:18px;background:#f8fbff;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                <div>
                    <h3 style="margin:0 0 6px;">Öğretmene Ata</h3>
                    <p style="margin:0;color:#64748b;">Seçtiğiniz oyun ve etkinlikleri tek seferde bir öğretmene atayın.</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <span style="font-size:13px;font-weight:700;color:#1d4ed8;background:#dbeafe;padding:8px 12px;border-radius:999px;">Toplu atama</span>
                    <button type="button" id="activity-bulk-unassign-open" class="btn" style="height:40px;padding:0 14px;">Seçili öğretmenden tüm atanmışları kaldır</button>
                </div>
            </div>
            <form method="POST" action="{{ route('activities.assign.teacher.bulk') }}" style="margin-top:16px;">
                @csrf
                <div style="display:grid;grid-template-columns:minmax(220px,320px) 1fr;gap:14px;align-items:start;">
                    <div>
                        <label style="display:block;font-weight:700;margin-bottom:8px;">Öğretmen Seç</label>
                        <select name="teacher_id" id="activity-teacher-select" required style="width:100%;padding:14px 16px;border:1px solid #cfe0ff;border-radius:14px;background:#fff;font-size:15px;">
                            <option value="">Öğretmen seçin</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? ('Öğretmen #' . $teacher->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;flex-wrap:wrap;">
                            <label style="display:flex;align-items:center;gap:8px;font-weight:700;">
                                <input type="checkbox" id="activities-select-all" style="width:18px;height:18px;">
                                Tümünü Seç
                            </label>
                            <button type="submit" class="btn">Öğretmene Ata</button>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;max-height:280px;overflow:auto;padding-right:4px;">
                            @foreach($games as $slug => $game)
                                <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid #dbe7ff;border-radius:14px;background:#fff;cursor:pointer;">
                                    <input type="checkbox" name="game_slugs[]" value="{{ $slug }}" class="activity-game-check" style="width:18px;height:18px;">
                                    <span style="font-weight:600;">{{ $game['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
            <form method="POST" action="{{ route('activities.unassign.teacher.bulk') }}" id="activity-bulk-unassign-form" style="display:none;margin-top:10px;">
                @csrf
                <input type="hidden" name="teacher_id" id="activity-unassign-teacher-id">
            </form>
        </div>
        <div class="activity-grid">
            <article class="activity-item">
                <img src="{{ asset('quiz.png') }}" alt="Canli Quiz">
                <div class="activity-body">
                    <h3>Canli Quiz</h3>
                    <div class="actions">
                        <a class="btn" href="{{ route('live-quiz.index') }}">Oyunu Aç</a>
                    </div>
                </div>
            </article>

            <article class="activity-item">
                <img src="{{ asset('flowchart.png') }}" alt="Flowchart Programming">
                <div class="activity-body">
                    <h3>Flowchart Programming</h3>
                    <div class="actions">
                        <a class="btn" href="{{ route('flowchart.editor') }}">Uygulamayı Aç</a>
                    </div>
                </div>
            </article>

            @foreach($games as $slug => $game)
                <article class="activity-item">
                    <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}">
                    <div class="activity-body">
                        <h3>{{ $game['name'] }}</h3>
                        <div class="actions">
                            <a class="btn" href="{{ url($game['url']) }}" target="_blank" rel="noopener">Oyunu Aç</a>
                            <a class="btn" href="{{ route('activities.assignments.create', $slug) }}">Ödevi Oluştur</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @elseif($isTeacher)
        <p>Atanan oyun ve etkinlikleriniz aşağıdadır. Sadece sizin sınıflarınıza uygun içerikler görünür.</p>
        @php
            $assigned = collect($assignedGameActivities ?? []);
        @endphp
        @if($assigned->isEmpty())
            <div style="padding:18px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;color:#475569;">
                Henüz size atanmış bir oyun veya etkinlik yok.
            </div>
        @else
            <div class="activity-grid">
                @foreach($assigned as $assignment)
                    @php
                        $game = $games[$assignment->game_slug] ?? null;
                        $gameUrl = $game ? url($game['url'] . '?role=teacher') : '#';
                        $classText = collect($assignment->classes ?? [])
                            ->map(fn ($class) => trim((string) ($class->name ?? '') . '/' . strtoupper((string) ($class->section ?? ''))))
                            ->filter()
                            ->unique()
                            ->values()
                            ->implode(', ');
                    @endphp
                    <article class="activity-item">
                        <img src="{{ asset($game['image'] ?? 'quiz.png') }}" alt="{{ $assignment->game_name }}">
                        <div class="activity-body">
                            <h3>{{ $assignment->game_name }}</h3>
                            <p style="margin:6px 0 0;color:#475569;font-size:14px;font-weight:600;">{{ $assignment->title }}</p>
                            <p style="margin:6px 0 0;color:#64748b;font-size:12px;line-height:1.35;">Sınıf: {{ $classText !== '' ? $classText : '-' }}</p>
                            <div class="actions">
                                <a class="btn" href="{{ $gameUrl }}" target="_blank" rel="noopener">Oyunu Aç</a>
                                <a class="btn" href="{{ route('activities.assignments.create', $assignment->game_slug) }}">Ödevi Oluştur</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @else
        <p>Aşağıdaki oyunlar seviye tabanlı ilerleme ve ödevleme için hazırdır.</p>
        @if(auth()->user()?->hasRole('student'))
            <p style="margin-top:8px;color:#475569">
                Öğrenci modu: Oyunlarda varsayılan olarak sadece <b>1-2. seviyeler</b> açıktır.
                Üst seviyeler, öğretmen ödev atadığında görünür.
            </p>
        @endif
        @if(($games ?? []) === [])
            <div style="padding:18px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;color:#475569;">
                Henüz sınıfınıza atanmış bir oyun veya etkinlik yok.
            </div>
        @endif
        <div class="activity-grid">
            @foreach($games as $slug => $game)
                <article class="activity-item">
                    <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}">
                    <div class="activity-body">
                        <h3>{{ $game['name'] }}</h3>
                        <div class="actions">
                            @if(auth()->user()?->hasRole('student') && !in_array($slug, ['keyboard-race', 'block-builder-studio', 'flamestone-game'], true))
                                <a class="btn" href="{{ route('runner.open', ['slug' => $slug, 'from' => 1, 'to' => 2]) }}">Oyunu Aç (L1-L2)</a>
                            @else
                                <a class="btn" href="{{ url($game['url']) }}" target="_blank" rel="noopener">Oyunu Aç</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@if($isAdmin)
    <script>
        (function () {
            const assignedByTeacher = @json($teacherGameAssignmentsByTeacher ?? []);
            const selectAll = document.getElementById('activities-select-all');
            const checks = Array.from(document.querySelectorAll('.activity-game-check'));
            const teacherSelect = document.getElementById('activity-teacher-select');
            const unassignOpenBtn = document.getElementById('activity-bulk-unassign-open');
            const unassignForm = document.getElementById('activity-bulk-unassign-form');
            const unassignTeacherId = document.getElementById('activity-unassign-teacher-id');

            const syncChecksFromTeacher = () => {
                const teacherId = teacherSelect?.value || '';
                const slugs = assignedByTeacher[teacherId] || [];
                checks.forEach((check) => {
                    check.checked = slugs.includes(check.value);
                });
                if (selectAll) {
                    selectAll.checked = checks.length > 0 && checks.every((check) => check.checked);
                }
            };

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    checks.forEach((check) => {
                        check.checked = selectAll.checked;
                    });
                });
            }

            teacherSelect?.addEventListener('change', syncChecksFromTeacher);
            syncChecksFromTeacher();

            unassignOpenBtn?.addEventListener('click', () => {
                const teacherId = teacherSelect?.value || '';
                if (!teacherId) {
                    alert('Lütfen önce bir öğretmen seçin.');
                    return;
                }
                if (!confirm('Seçili öğretmenden tüm atanmış oyun ve etkinlikler kaldırılacak. Devam edilsin mi?')) {
                    return;
                }
                if (unassignTeacherId) {
                    unassignTeacherId.value = teacherId;
                }
                unassignForm?.submit();
            });
        })();
    </script>
@endif
@endsection
