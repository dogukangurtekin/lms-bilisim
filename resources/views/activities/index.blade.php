@extends('layout.app')

@section('title', 'Oyun ve Etkinlikler')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = (bool) ($user?->hasRole('admin') ?? false);
    $isTeacher = (bool) ($user?->hasRole('teacher') ?? false);
@endphp

<style>
    .activities-page{
        display:grid;
        gap:1rem;
    }
    .activities-top{
        display:flex;
        flex-wrap:wrap;
        gap:.5rem .75rem;
        align-items:flex-end;
        justify-content:space-between;
    }
    .activities-panel{
        padding:18px;
        border:1px solid #dbe7ff;
        border-radius:18px;
        background:#f8fbff;
        min-width:0;
    }
    .activities-panel__head{
        display:flex;
        gap:16px;
        align-items:flex-start;
        justify-content:space-between;
        flex-wrap:wrap;
    }
    .activities-form-grid{
        display:grid;
        grid-template-columns:minmax(220px,320px) minmax(0,1fr);
        gap:14px;
        align-items:start;
    }
    .activities-games-grid{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:10px;
        max-height:280px;
        overflow:auto;
        padding-right:4px;
    }
    .activity-card{
        display:flex;
        flex-direction:column;
        gap:12px;
        min-width:0;
        overflow:hidden;
    }
    .activity-card img{
        width:100%;
        height:180px;
        object-fit:cover;
        object-position:center;
        border-radius:16px;
        background:#fff;
        flex:0 0 auto;
    }
    .activity-card .activity-body{
        min-width:0;
        flex:1 1 auto;
        display:flex;
        flex-direction:column;
        gap:10px;
    }
    .activity-card .actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:auto;
    }
    .activity-card .actions .btn{
        flex:1 1 140px;
        min-width:0;
        text-align:center;
    }
    .activity-card h3{
        margin:0;
        line-height:1.35;
    }
    .activity-grid{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
        gap:16px;
        margin-top:16px;
    }
    .activity-item{
        padding:16px;
        border:1px solid #dbe7ff;
        border-radius:18px;
        background:#fff;
        box-shadow:0 8px 24px rgba(37,99,235,.06);
        min-width:0;
    }
    @media (max-width: 760px){
        .activities-form-grid{
            grid-template-columns:1fr;
        }
        .activities-games-grid{
            max-height:none;
            overflow:visible;
        }
        .activities-panel__head{
            flex-direction:column;
        }
        .activity-card{
            padding:14px;
        }
        .activity-card .actions .btn{
            width:100%;
            flex-basis:100%;
        }
        .activity-card img{
            height:160px;
        }
    }
</style>

<div class="activities-page">
    <div class="activities-top top">
        <h1>Oyun ve Etkinlikler</h1>
        @if($isAdmin)
            <span class="muted">Tüm oyunlar ve etkinlikler burada listelenir.</span>
        @elseif($isTeacher)
            <span class="muted">Sadece size atanan oyun ve etkinlikler gösterilir.</span>
        @else
            <span class="muted">Öğrenci modunda oynanabilir oyunlar gösterilir.</span>
        @endif
    </div>

    <div class="card">
        @if($isAdmin)
            <p>Aşağıdaki oyunlar seviye tabanlı ilerleme ve ödevleme için hazırdır.</p>
            <div class="activities-panel" style="margin-top:18px;">
                <div class="activities-panel__head">
                    <div>
                        <h3 style="margin:0 0 6px;">Öğretmene Ata</h3>
                        <p style="margin:0;color:#64748b;">Seçtiğiniz oyun ve etkinlikleri tek seferde bir öğretmene atayın.</p>
                    </div>
                    <span style="font-size:13px;font-weight:700;color:#1d4ed8;background:#dbeafe;padding:8px 12px;border-radius:999px;">Toplu atama</span>
                </div>

                <form method="POST" action="{{ route('activities.assign.teacher.bulk') }}" style="margin-top:16px;">
                    @csrf
                    <div class="activities-form-grid">
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
                            <div class="activities-games-grid">
                                @foreach($games as $slug => $game)
                                    <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid #dbe7ff;border-radius:14px;background:#fff;cursor:pointer;min-width:0;">
                                        <input type="checkbox" name="game_slugs[]" value="{{ $slug }}" class="activity-game-check" style="width:18px;height:18px;flex:0 0 auto;">
                                        <span style="font-weight:600;min-width:0;overflow-wrap:anywhere;">{{ $game['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('activities.unassign.teacher.bulk') }}" id="activity-bulk-unassign-form" data-confirm="Seçili öğretmenden tüm atanmış oyun ve etkinlikler kaldırılacak. Devam edilsin mi?" style="display:flex;justify-content:flex-end;gap:12px;align-items:center;margin-top:12px;flex-wrap:wrap;">
                    @csrf
                    <select name="teacher_id" id="activity-unassign-teacher-select" required style="min-width:240px;height:40px;padding:0 12px;border:1px solid #cfe0ff;border-radius:14px;background:#fff;font-size:15px;">
                        <option value="">Kaldırılacak öğretmeni seçin</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? ('Öğretmen #' . $teacher->id) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn" style="height:40px;padding:0 14px;">Seçili öğretmenden tüm atanmışları kaldır</button>
                </form>
            </div>

            <div class="activity-grid">
                <article class="activity-item activity-card">
                    <img src="{{ asset('quiz.png') }}" alt="Canlı Quiz">
                    <div class="activity-body">
                        <h3>Canlı Quiz</h3>
                        <div class="actions">
                            <a class="btn" href="{{ route('live-quiz.index') }}">Oyunu Aç</a>
                        </div>
                    </div>
                </article>

                <article class="activity-item activity-card">
                    <img src="{{ asset('flowchart.png') }}" alt="Flowchart Programming">
                    <div class="activity-body">
                        <h3>Flowchart Programming</h3>
                        <div class="actions">
                            <a class="btn" href="{{ route('flowchart.editor') }}">Uygulamayı Aç</a>
                        </div>
                    </div>
                </article>

                @foreach($games as $slug => $game)
                    <article class="activity-item activity-card">
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
            <p>Admin tarafından size atanan oyun ve etkinlikler aşağıdadır.</p>
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
                            $assignedByName = trim((string) ($assignment->assignedBy?->name ?? ''));
                        @endphp
                        <article class="activity-item activity-card">
                            <img src="{{ asset($game['image'] ?? 'quiz.png') }}" alt="{{ $assignment->game_name }}">
                            <div class="activity-body">
                                <h3>{{ $assignment->game_name }}</h3>
                                <div class="actions">
                                    <a class="btn" href="{{ $gameUrl }}" target="_blank" rel="noopener">Oyunu Aç</a>
                                </div>
                                <p style="margin:6px 0 0;color:#64748b;font-size:12px;line-height:1.35;">Atayan: {{ $assignedByName !== '' ? $assignedByName : '-' }}</p>
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
                    <article class="activity-item activity-card">
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
</div>
@if($isAdmin)
    <script>
        (function () {
            const assignedByTeacher = @json($teacherGameAssignmentsByTeacher ?? []);
            const selectAll = document.getElementById('activities-select-all');
            const checks = Array.from(document.querySelectorAll('.activity-game-check'));
            const teacherSelect = document.getElementById('activity-teacher-select');
            const unassignTeacherSelect = document.getElementById('activity-unassign-teacher-select');

            const syncUnassignTeacher = () => {
                if (!teacherSelect || !unassignTeacherSelect) return;
                if (teacherSelect.value) {
                    unassignTeacherSelect.value = teacherSelect.value;
                }
            };

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
            teacherSelect?.addEventListener('change', syncUnassignTeacher);
            syncChecksFromTeacher();
            syncUnassignTeacher();
        })();
    </script>
@endif
@endsection