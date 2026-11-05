@extends('layout.app')

@section('title', 'Kodlama Uygulamaları')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = (bool) ($user?->hasRole('admin') ?? false);
    $isTeacher = (bool) ($user?->hasRole('teacher') ?? false);
@endphp

<style>
.act-notice{display:flex;gap:10px;align-items:flex-start;margin:0 0 14px;padding:12px 14px;border-radius:12px;background:#fff7ed;border:1px solid #fdba74;border-left:5px solid #f97316;color:#9a3412;font-size:14px;line-height:1.5}
.act-notice p{margin:0}
.act-notice-icon{font-size:18px;line-height:1.3}
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
        position:relative;
        padding:14px;
        border:1px solid #dbe7ff;
        border-radius:20px;
        background:linear-gradient(180deg,#ffffff 0%,#f7faff 100%);
        box-shadow:0 8px 24px rgba(37,99,235,.06);
        min-width:0;
        transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease;
    }
    .activity-item:hover{
        transform:translateY(-4px);
        border-color:#93b4ff;
        box-shadow:0 16px 34px rgba(37,99,235,.16);
    }
    .activity-card img{
        aspect-ratio:16/9;
        height:auto !important;
        box-shadow:inset 0 0 0 1px rgba(15,23,42,.06);
        transition:transform .25s ease;
    }
    .activity-item:hover img{transform:scale(1.02)}
    .activity-card h3{font-size:17px;font-weight:800;color:#0f172a}
    .activity-card .actions .btn + .btn{
        background:#fff;
        color:#1d4ed8;
        border:1.5px solid #bfd3ff;
    }
    .activity-card .actions .btn + .btn:hover{background:#eff6ff}
    .act-dd{position:relative;min-width:0}
    .act-dd > summary{
        list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:10px;
        padding:14px 16px;border:1px solid #cfe0ff;border-radius:14px;background:#fff;font-size:15px;font-weight:600;
    }
    .act-dd > summary::-webkit-details-marker{display:none}
    .act-dd > summary::after{content:'▾';color:#64748b;margin-left:auto}
    .act-dd[open] > summary{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
    .act-dd-count{min-width:26px;text-align:center;padding:2px 8px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:800}
    .act-dd-menu{
        position:absolute;z-index:20;left:0;right:0;top:calc(100% + 6px);padding:10px;border:1px solid #cfe0ff;border-radius:14px;
        background:#fff;box-shadow:0 18px 40px rgba(15,23,42,.18);
    }
    .act-dd-menu input[type=search]{width:100%;padding:10px 12px;border:1px solid #cfe0ff;border-radius:10px;margin-bottom:8px;box-sizing:border-box}
    .act-dd-all{display:flex;align-items:center;gap:8px;font-weight:800;padding:6px 8px;border-bottom:1px solid #e2e8f0;margin-bottom:6px}
    .act-dd-list{max-height:280px;overflow:auto;display:grid;gap:2px}
    .act-dd-list label{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;cursor:pointer;min-width:0}
    .act-dd-list label:hover{background:#eff6ff}
    .act-dd-list label[hidden]{display:none}
    .act-dd-list input,.act-dd-all input{width:18px;height:18px;flex:0 0 auto;margin:0}
    @media (max-width: 760px){
        .act-dd-list{max-height:50vh}
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
        .activity-card .actions{
            flex-direction:column;
            flex-wrap:nowrap;
        }
        .activity-card .actions .btn{
            width:100%;
            flex:0 0 auto;
            font-size:13.5px;
            padding:11px 12px;
            white-space:normal;
            line-height:1.3;
        }
        .activity-card img{
            height:160px;
        }
    }
</style>

<div class="activities-page">
    <div class="act-notice" role="note">
        <span class="act-notice-icon" aria-hidden="true">⚠️</span>
        <p>Oyun ve Etkinlikler sayfa ismi yanlış anlaşılmalara sebebiyet verdiğinden ötürü Kodlama Uygulamaları olarak güncellenmiştir. İçeriklerimizin hepsi Kodlama Etkinlikleridir <strong>OYUN DEĞİLDİR</strong>. Bilgilerinize.<br><strong>Bilişim Kod Yapay Zeka ve Kodlama Sistemleri</strong></p>
    </div>
    <div class="activities-top top">
        <h1>Kodlama Uygulamaları</h1>
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
                            <label style="display:block;font-weight:700;margin-bottom:8px;">Kodlama Uygulamaları</label>
                            <details class="act-dd" id="act-dd">
                                <summary><span id="act-dd-label">Oyun ve etkinlik seçin</span><span class="act-dd-count" id="act-dd-count">0</span></summary>
                                <div class="act-dd-menu">
                                    <input type="search" id="act-dd-search" placeholder="Oyun ara…" autocomplete="off">
                                    <label class="act-dd-all">
                                        <input type="checkbox" id="activities-select-all">
                                        Tümünü Seç
                                    </label>
                                    <div class="act-dd-list">
                                        @foreach($games as $slug => $game)
                                            <label data-name="{{ \Illuminate\Support\Str::lower($game['name']) }}">
                                                <input type="checkbox" name="game_slugs[]" value="{{ $slug }}" class="activity-game-check">
                                                <span style="font-weight:600;min-width:0;overflow-wrap:anywhere;">{{ $game['name'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </details>
                            <button type="submit" class="btn" style="margin-top:12px">Öğretmene Ata</button>
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
                    <img src="{{ asset('klavye.jpg') }}?v={{ @filemtime(public_path('klavye.jpg')) ?: 1 }}" alt="Canlı Yarışmalar">
                    <div class="activity-body">
                        <h3>Canlı Yarışmalar</h3>
                        <div class="actions">
                            <a class="btn" href="{{ route('competitions.index') }}">Yarışma Aç</a>
                        </div>
                    </div>
                </article>

                @foreach($games as $slug => $game)
                    <article class="activity-item activity-card">
                        <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}">
                        <div class="activity-body">
                            <h3>{{ $game['name'] }}</h3>
                            <div class="actions">
                                @if($slug === 'live-quiz')
                                    <a class="btn" href="{{ route('live-quiz.index') }}">Oyunu Aç</a>
                                @elseif($slug === 'keyboard-race')
                                    <a class="btn" href="{{ route('keyboard-race.index') }}">Yarışma Aç</a>
                                @else
                                    <a class="btn" href="{{ route('activities.play', ['target' => $game['url']]) }}">Oyunu Aç</a>
                                    <a class="btn" href="{{ route('activities.assignments.create', $slug) }}">Ödevi Oluştur</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @elseif($isTeacher)
            <div class="activity-grid" style="margin-bottom:14px;">
                <article class="activity-item activity-card">
                    <img src="{{ asset('klavye.jpg') }}?v={{ @filemtime(public_path('klavye.jpg')) ?: 1 }}" alt="Canlı Yarışmalar">
                    <div class="activity-body">
                        <h3>Canlı Yarışmalar</h3>
                        <div class="actions">
                            <a class="btn" href="{{ route('competitions.index') }}">Yarışma Aç</a>
                        </div>
                    </div>
                </article>
            </div>
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
                            $gameUrl = $assignment->game_slug === 'live-quiz'
                                ? route('live-quiz.index')
                                : ($assignment->game_slug === 'keyboard-race' ? route('keyboard-race.index') : ($game ? route('activities.play', ['target' => $game['url'] . '?role=teacher']) : '#'));
                            $assignedByName = trim((string) ($assignment->assignedBy?->name ?? ''));
                        @endphp
                        <article class="activity-item activity-card">
                            <img src="{{ asset($game['image'] ?? 'quiz.png') }}" alt="{{ $assignment->game_name }}">
                            <div class="activity-body">
                                <h3>{{ $assignment->game_name }}</h3>
                                <div class="actions">
                                    <a class="btn" href="{{ $gameUrl }}">Oyunu Aç</a>
                                    @if(! in_array($assignment->game_slug, ['live-quiz', 'keyboard-race'], true))
                                        <a class="btn" href="{{ route('activities.assignments.create', $assignment->game_slug) }}">Ödevi Oluştur</a>
                                    @endif
                                </div>
                                <p style="margin:6px 0 0;color:#64748b;font-size:12px;line-height:1.35;">Atayan: {{ $assignedByName !== '' ? $assignedByName : '-' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        @else
            <div class="activity-grid" style="margin-bottom:14px;">
                <article class="activity-item activity-card">
                    <img src="{{ asset('klavye.jpg') }}?v={{ @filemtime(public_path('klavye.jpg')) ?: 1 }}" alt="Canlı Yarışmalar">
                    <div class="activity-body">
                        <h3>Canlı Yarışmalar</h3>
                        <p style="margin:0 0 8px;color:#64748b;font-size:13px">Canlı quiz, oyun yarışması ve klavye yarışması için tek giriş.</p>
                        <div class="actions">
                            <a class="btn" href="{{ route('student.competitions.join.form') }}">Kod ile Katıl</a>
                        </div>
                    </div>
                </article>
            </div>
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
                    @continue($slug === 'live-quiz')
                    <article class="activity-item activity-card">
                        <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}">
                        <div class="activity-body">
                            <h3>{{ $game['name'] }}</h3>
                            <div class="actions">
                                @if(auth()->user()?->hasRole('student') && !in_array($slug, ['keyboard-race', 'block-builder-studio', 'flamestone-game'], true))
                                    <a class="btn" href="{{ route('activities.play', ['target' => route('runner.open', ['slug' => $slug, 'from' => 1, 'to' => 2], false)]) }}">Oyunu Aç (L1-L2)</a>
                                @else
                                    <a class="btn" href="{{ route('activities.play', ['target' => $game['url']]) }}">Oyunu Aç</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
@if(auth()->user()?->hasRole('student'))
    <script>
        (function () {
            // Ogrenci katildigi bir Canli Yarisma odasinin baslamasini
            // beklerken Oyun ve Etkinlikler sayfasinda da olsa, oda canliya
            // gectigi an otomatik olarak oyuna yonlendirilsin diye periyodik
            // olarak yokluyoruz.
            const activeUrl = @json(route('student.competitions.active'));
            let redirected = false;
            let timer = null;
            const poll = async () => {
                if (redirected || document.hidden) return;
                try {
                    const res = await fetch(activeUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.active && data.status === 'live' && data.room_id) {
                        redirected = true;
                        if (timer) clearInterval(timer);
                        // Yarışma başlayınca hepsi aynı saniyede yönlenmesin: 0–1,5 sn rastgele gecikme.
                        setTimeout(() => { window.location.href = '/ogrenci/canli-yarismalar/' + data.room_id; }, Math.floor(Math.random() * 1500));
                        return;
                    }
                    // Katıldığı ve henüz başlamamış (lobide) bir oda yoksa yoklamayı tamamen durdur:
                    // yarışmaya katılmayan hiçbir öğrenci bu sayfada sürekli istek atmaz.
                    if (!data.active && timer) { clearInterval(timer); timer = null; }
                } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
            };
            poll();
            timer = setInterval(poll, 8000);
        })();
    </script>
@endif
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
                updateDd();
            };

            const dd = document.getElementById('act-dd');
            const ddLabel = document.getElementById('act-dd-label');
            const ddCount = document.getElementById('act-dd-count');
            const ddSearch = document.getElementById('act-dd-search');
            const updateDd = () => {
                const picked = checks.filter((c) => c.checked);
                if (ddCount) ddCount.textContent = String(picked.length);
                if (ddLabel) {
                    ddLabel.textContent = picked.length === 0 ? 'Oyun ve etkinlik seçin'
                        : (picked.length === 1 ? picked[0].closest('label').textContent.trim() : picked.length + ' oyun/etkinlik seçildi');
                }
                if (selectAll) selectAll.checked = checks.length > 0 && picked.length === checks.length;
            };
            checks.forEach((c) => c.addEventListener('change', updateDd));
            ddSearch?.addEventListener('input', () => {
                const q = ddSearch.value.trim().toLocaleLowerCase('tr');
                checks.forEach((c) => { const l = c.closest('label'); l.hidden = q !== '' && !(l.dataset.name || '').includes(q); });
            });
            document.addEventListener('click', (e) => { if (dd && dd.open && !dd.contains(e.target)) dd.open = false; });

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    checks.forEach((check) => {
                        check.checked = selectAll.checked;
                    });
                    updateDd();
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
