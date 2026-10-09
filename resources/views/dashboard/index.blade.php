@extends('layout.app')
@section('title','Öğretmen Paneli')
@section('body_class','dashboard-page')
@section('content')
@php
    $layout = $dashboardLayout ?? [];
    $selectedClassId = (int) ($dashboard['selected_class_id'] ?? 0);
    $selectedClassLabel = 'Genel görünüm';
    foreach (($dashboard['class_tabs'] ?? []) as $tab) {
        if ((int) ($tab['id'] ?? 0) === $selectedClassId) {
            $selectedClassLabel = $tab['label'] ?? $selectedClassLabel;
            break;
        }
    }
@endphp
<style>
    .dashboard-sidebar-stack{
        display:flex;
        flex-direction:column;
        gap:1.35rem;
    }
    .dashboard-widget-sidebar-grid{
        display:grid;
        grid-template-columns:1fr;
        gap:1.35rem;
        width:100%;
    }
    .dashboard-widget-sidebar-grid .dashboard-widget{
        width:100%;
        min-width:0;
        grid-column:1 / -1 !important;
    }
    .dashboard-leaderboard-panel{
        margin-top:0;
        max-height: none;
        overflow: visible;
        display: flex;
        flex-direction: column;
    }
    /* reverted widget sizing */
    .dashboard-leaderboard-panel .teacher-top10-list{
        display: grid;
        gap: .7rem;
        overflow: visible;
        padding-right: 0;
        max-height: none;
    }
    .dashboard-leaderboard-panel .teacher-top10-item{
        padding: .6rem .75rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        gap: .85rem;
        min-height: 62px;
    }
    .dashboard-leaderboard-panel .teacher-top10-rank{
        width: 34px;
        height: 34px;
        font-size: .95rem;
        flex: 0 0 34px;
        color: #fff;
        font-weight: 700;
    }
    .dashboard-leaderboard-panel .teacher-top10-main strong{
        font-size: 13px;
        line-height: 1.15;
        display: block;
        white-space: normal;
        overflow-wrap: anywhere;
        font-weight: 500;
    }
    .dashboard-leaderboard-panel .teacher-top10-main span{
        font-size: 11px;
        line-height: 1.1;
        display: block;
    }
    .dashboard-leaderboard-panel .teacher-top10-main{
        flex: 1 1 auto;
        min-width: 0;
    }
    .dashboard-leaderboard-panel .teacher-top10-xp{
        font-size: .82rem;
        padding: .32rem .5rem;
        white-space: nowrap;
    }
    .leaderboard-head-actions{
        display:flex;
        align-items:center;
        gap:8px;
        flex:0 0 auto;
    }
    .leaderboard-full-btn{
        display:inline-flex;
        align-items:center;
        gap:4px;
        font-size:11px;
        font-weight:600;
        color:#2563eb;
        background:#eff6ff;
        border:1px solid #bfdbfe;
        border-radius:999px;
        padding:5px 10px;
        cursor:pointer;
        white-space:nowrap;
        transition:background .15s ease;
    }
    .leaderboard-full-btn:hover{background:#dbeafe;}
    .dashboard-shell.is-editing .leaderboard-full-btn{display:none;}
    .leaderboard-modal-card{
        width:min(94vw,720px);
        max-width:720px;
        max-height:86vh;
        display:flex;
        flex-direction:column;
        padding:20px;
    }
    .leaderboard-modal-close{
        border:none;
        background:#f1f5f9;
        color:#475569;
        width:32px;
        height:32px;
        border-radius:10px;
        font-size:20px;
        line-height:1;
        cursor:pointer;
        display:grid;
        place-items:center;
        flex:0 0 auto;
    }
    .leaderboard-modal-close:hover{background:#e2e8f0;}
    .leaderboard-modal-body{
        margin-top:12px;
        overflow-y:auto;
        display:flex;
        flex-direction:column;
        gap:18px;
        padding-right:6px;
    }
    .leaderboard-class-group{
        border:1px solid #e2e8f0;
        border-radius:16px;
        padding:14px;
        background:#f8fafc;
    }
    .leaderboard-class-head{
        display:flex;
        align-items:baseline;
        justify-content:space-between;
        gap:10px;
        margin-bottom:10px;
        flex-wrap:wrap;
    }
    .leaderboard-class-head strong{font-size:15px;color:#0f172a;}
    .leaderboard-class-head span{font-size:12px;color:#64748b;font-weight:600;}
    .leaderboard-class-list{
        display:grid;
        gap:8px;
    }
    .leaderboard-class-row{
        grid-template-columns:28px 1fr auto;
        padding:8px 10px;
    }
    .xp-gift-widget{background:linear-gradient(135deg,#f0fdf4 0%,#ffffff 48%,#eff6ff 100%);border-color:#86efac!important}
    .xp-gift-form{display:grid;grid-template-columns:1.1fr 1.1fr .75fr 1.4fr auto;gap:12px;align-items:end;margin-top:12px}
    .xp-gift-field{display:grid;gap:6px;min-width:0}
    .xp-gift-field label{font-size:11px;font-weight:900;letter-spacing:.04em;text-transform:uppercase;color:#166534}
    .xp-gift-field input,.xp-gift-field select{width:100%;min-height:43px;padding:9px 11px;border:1.5px solid #bbf7d0;border-radius:12px;background:#fff;color:#0f172a;font:inherit;font-size:13px;box-sizing:border-box}
    .xp-gift-field select[multiple]{min-height:118px}
    .xp-gift-submit{min-height:43px;padding:10px 18px;border:0;border-radius:12px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;font-weight:900;cursor:pointer;box-shadow:0 10px 24px rgba(22,163,74,.24)}
    .xp-gift-note{grid-column:1/-1;margin:0;color:#475569;font-size:11px}
    .xp-gift-errors{grid-column:1/-1;padding:10px 12px;border-radius:12px;background:#fff1f2;color:#be123c;font-size:12px;font-weight:700}
    @media(max-width:1050px){.xp-gift-form{grid-template-columns:1fr 1fr}.xp-gift-submit{width:100%}}
    @media(max-width:680px){.xp-gift-form{grid-template-columns:1fr}}
</style>
<div class="dashboard-shell" data-dashboard-shell>
    <section class="class-tabs-strip" aria-label="Sınıf sekmeleri">
        <a class="class-tab {{ $selectedClassId === 0 ? 'active' : '' }}" href="{{ route('dashboard') }}">Tümü</a>
        @foreach(($dashboard['class_tabs'] ?? []) as $classTab)
            <a class="class-tab {{ $selectedClassId === (int) ($classTab['id'] ?? 0) ? 'active' : '' }}" href="{{ route('dashboard', ['class_id' => $classTab['id']]) }}">{{ $classTab['label'] }}</a>
        @endforeach
    </section>

    <section class="dashboard-main-layout">
        <div class="dashboard-center-column">
            <section class="dashboard-widget-grid" id="dashboard-widget-grid">
                <a class="dashboard-widget dashboard-widget-hero dashboard-widget-qr widget-span-12" data-widget-key="quick_qr" href="{{ route('qr.login.menu') }}" draggable="true">
                    <div class="widget-head">
                        <div>
                            <strong>Mobil QR Girişi</strong>
                            <span>Mobilde sabit</span>
                            <small class="widget-class-tag">{{ $selectedClassLabel }}</small>
                        </div>
                        <button type="button" class="widget-toggle" data-widget-toggle="quick_qr" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    <div class="qr-widget-body">
                        <div>
                            <small>QR giriş</small>
                            <h3>Hemen okut</h3>
                            <p>Mobilde açık kalır.</p>
                        </div>
                        <img src="{{ asset('qr-mini.svg') }}" alt="QR">
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </a>

                <article class="dashboard-widget widget-span-4" data-widget-key="students" draggable="true">
                    <div class="widget-head">
                        <div><strong>Toplam Öğrenci</strong><span>Aktif havuz</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="students" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    <div class="metric-card">
                        <strong>{{ $dashboard['summary']['total_students'] }}</strong>
                        <span>Toplam kayıt</span>
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

                <article class="dashboard-widget widget-span-4" data-widget-key="active_students" draggable="true">
                    <div class="widget-head">
                        <div><strong>Aktif Öğrenci</strong><span>Son 1 gün</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="active_students" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    @php
                        $activeTop3 = array_slice($dashboard['summary']['active_students_top3'] ?? [], 0, 3);
                    @endphp
                    @if($activeTop3 !== [])
                        <div class="metric-card metric-card--stacked">
                            <strong>{{ $dashboard['summary']['active_students'] }}</strong>
                            <span>Çevrimiçi</span>
                            <div class="active-top3-list">
                                @foreach($activeTop3 as $studentRow)
                                    <div class="active-top3-item">
                                        <strong>{{ $loop->iteration }}.</strong>
                                        <span>{{ $studentRow['name'] ?? '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="metric-card">
                            <strong>{{ $dashboard['summary']['active_students'] }}</strong>
                            <span>Çevrimiçi</span>
                        </div>
                    @endif
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

                <article class="dashboard-widget widget-span-4" data-widget-key="classes" draggable="true">
                    <div class="widget-head">
                        <div><strong>Sınıf Sayısı</strong><span>İzlenen sınıflar</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="classes" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    <div class="metric-card">
                        <strong>{{ $dashboard['summary']['total_classes'] }}</strong>
                        <span>Sistem genelinde</span>
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

                <article class="dashboard-widget widget-span-4" data-widget-key="courses" draggable="true">
                    <div class="widget-head">
                        <div><strong>Ders Sayısı</strong><span>Aktif içerik</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="courses" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    <div class="metric-card">
                        <strong>{{ $dashboard['summary']['total_courses'] }}</strong>
                        <span>Tanımlı ders</span>
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

                <article class="dashboard-widget widget-span-4" data-widget-key="avg_completion" draggable="true">
                    <div class="widget-head">
                        <div><strong>Ortalama Not</strong><span>Genel başarı</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="avg_completion" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    <div class="metric-card">
                        <strong>%{{ $dashboard['summary']['avg_completion'] }}</strong>
                        <span>Yüzdelik başarı</span>
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

                <article class="dashboard-widget widget-span-4" data-widget-key="xp" draggable="true">
                    <div class="widget-head">
                        <div><strong>Toplam XP</strong><span>Biriken puan</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="xp" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    <div class="metric-card">
                        <strong>{{ $dashboard['summary']['total_xp'] }}</strong>
                        <span>Öğrenci üretimi</span>
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

                @if(auth()->user()?->hasRole('admin'))
                    <article class="dashboard-widget widget-span-12 xp-gift-widget" data-widget-key="xp_gift" draggable="true">
                        <div class="widget-head">
                            <div><strong>XP Hediyesi Gönder</strong><span>Tüm öğrencilere, sınıfa veya seçilen öğrencilere XP yükle</span></div>
                            <button type="button" class="widget-toggle" data-widget-toggle="xp_gift" aria-label="Gizle" title="Gizle">-</button>
                        </div>
                        <form method="POST" action="{{ route('dashboard.xp-gifts.store') }}" class="xp-gift-form" data-xp-gift-form>
                            @csrf
                            @if($errors->any())
                                <div class="xp-gift-errors">{{ $errors->first() }}</div>
                            @endif
                            <div class="xp-gift-field">
                                <label for="xp-gift-scope">Hedef</label>
                                <select id="xp-gift-scope" name="target_scope" data-xp-gift-scope required>
                                    <option value="all" @selected(old('target_scope', 'all') === 'all')>Tüm öğrenciler</option>
                                    <option value="class" @selected(old('target_scope') === 'class')>Sınıf ve şube</option>
                                    <option value="students" @selected(old('target_scope') === 'students')>Öğrenci seç</option>
                                </select>
                            </div>
                            <div class="xp-gift-field" data-xp-gift-class hidden>
                                <label for="xp-gift-class">Sınıf / Şube</label>
                                <select id="xp-gift-class" name="class_id">
                                    <option value="">Sınıf seçin</option>
                                    @foreach($xpGiftClasses as $class)
                                        <option value="{{ $class->id }}" @selected((int) old('class_id') === (int) $class->id)>{{ $class->name }}/{{ $class->section }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="xp-gift-field" data-xp-gift-students hidden>
                                <label for="xp-gift-students">Öğrenciler</label>
                                <select id="xp-gift-students" name="student_ids[]" multiple>
                                    @foreach($xpGiftStudents as $student)
                                        <option value="{{ $student->id }}" @selected(in_array($student->id, array_map('intval', (array) old('student_ids', [])), true))>
                                            {{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }} — {{ $student->user?->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="xp-gift-field">
                                <label for="xp-gift-amount">XP Miktarı</label>
                                <input id="xp-gift-amount" type="number" name="amount" min="1" max="10000" value="{{ old('amount') }}" placeholder="Örn. 100" required>
                            </div>
                            <div class="xp-gift-field">
                                <label for="xp-gift-description">Hediye Açıklaması</label>
                                <input id="xp-gift-description" type="text" name="description" maxlength="255" value="{{ old('description') }}" placeholder="Örn. Dönem sonu başarı hediyesi" required>
                            </div>
                            <button type="submit" class="xp-gift-submit">XP Gönder</button>
                            <p class="xp-gift-note">Gönderim kalıcıdır ve açıklamasıyla birlikte işlem geçmişine kaydedilir. Çoklu öğrenci seçmek için Ctrl tuşunu kullanabilirsiniz.</p>
                        </form>
                        <span class="widget-resize-handle" aria-hidden="true"></span>
                    </article>
                @endif

                <article class="dashboard-widget widget-span-6" data-widget-key="active_classes" draggable="true">
                    <div class="widget-head">
                        <div><strong>Aktif Sınıflar</strong><span>Şu an sistemde olan sınıflar</span></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="active_classes" aria-label="Gizle" title="Gizle">-</button>
                    </div>
                    <div id="active-classes-list" style="display:grid;gap:8px;">
                        <p style="margin:0;color:#64748b;font-size:13px;">Yükleniyor...</p>
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

                <div class="modal" id="active-class-logout-modal">
                    <div class="modal-card">
                        <div class="modal-head"><strong id="active-class-logout-title">Sınıftan Çıkış Yaptır</strong></div>
                        <p id="active-class-logout-text" style="margin:0 0 14px;color:#475569;"></p>
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <button type="button" class="btn" id="active-class-logout-cancel">Vazgeç</button>
                            <button type="button" class="btn btn-danger" id="active-class-logout-confirm">Evet, Çıkış Yaptır</button>
                        </div>
                    </div>
                </div>

                <div class="modal" id="active-class-students-modal">
                    <div class="modal-card">
                        <div class="modal-head"><strong id="active-class-students-title">Aktif Öğrenciler</strong></div>
                        <div id="active-class-students-list" style="display:grid;gap:6px;max-height:320px;overflow-y:auto;margin-bottom:14px;">
                        </div>
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <button type="button" class="btn" id="active-class-students-close">Kapat</button>
                        </div>
                    </div>
                </div>

                <div class="modal leaderboard-modal" id="leaderboard-full-modal">
                    <div class="modal-card leaderboard-modal-card">
                        <div class="modal-head">
                            <strong>Tüm Sıralama</strong>
                            <button type="button" class="leaderboard-modal-close" id="leaderboard-full-close" aria-label="Kapat" title="Kapat">&times;</button>
                        </div>
                        {{-- Sınıf tabları --}}
                        <div id="lb-class-tabs" style="display:flex;gap:6px;flex-wrap:wrap;margin:10px 0 0;padding-bottom:10px;border-bottom:1px solid var(--app-border,#e2e8f0);">
                            @foreach(($dashboard['class_tabs'] ?? []) as $tab)
                                <button type="button"
                                    class="lb-tab-btn"
                                    data-class-id="{{ $tab['id'] }}"
                                    style="padding:5px 12px;border-radius:999px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;cursor:pointer;transition:background .15s;">
                                    {{ $tab['label'] }}
                                </button>
                            @endforeach
                        </div>
                        {{-- Sıralama içeriği --}}
                        <div class="leaderboard-modal-body" id="lb-modal-body">
                            <p style="color:#64748b;font-size:13px;text-align:center;padding:30px 0;">
                                Görüntülemek istediğiniz sınıfı seçin.
                            </p>
                        </div>
                    </div>
                </div>

                @foreach(($dashboard['chart_widgets'] ?? []) as $key => $chart)
                    @php
                        $chartItems = (array) ($chart['items'] ?? []);
                        if (($chart['type'] ?? '') === 'column' || in_array($key, ['chart_student_lesson_completion'], true)) {
                            $chartItems = array_slice($chartItems, 0, 5);
                        }
                    @endphp
                    <article
                        class="dashboard-widget widget-span-4 dashboard-chart-widget"
                        data-widget-key="chart_{{ $key }}"
                        draggable="true"
                        data-chart-type="{{ $chart['type'] ?? 'bar' }}"
                    >
                        <div class="widget-head">
                            <div>
                                <strong>{{ $chart['title'] ?? '-' }}</strong>
                                <span>{{ $chart['subtitle'] ?? '' }}</span>
                                <small class="widget-class-tag">{{ $selectedClassLabel }}</small>
                            </div>
                            <button type="button" class="widget-toggle" data-widget-toggle="chart_{{ $key }}" aria-label="Gizle" title="Gizle">-</button>
                        </div>
                        <div class="chart-widget-body">
                            @if(($chart['type'] ?? '') === 'donut')
                                @php
                                    $pieTotal = max(1, array_sum(array_map(fn ($i) => (int) ($i['percent'] ?? 0), $chartItems)));
                                    $pieCumulative = 0;
                                    $pieSlices = [];
                                    foreach ($chartItems as $pieItem) {
                                        $pieValue = (int) ($pieItem['percent'] ?? 0);
                                        $startAngle = ($pieCumulative / $pieTotal) * 360;
                                        $pieCumulative += $pieValue;
                                        $endAngle = ($pieCumulative / $pieTotal) * 360;
                                        // Tam daire (tek dilim %100) durumunda arc çizilemediği için ufak bir boşluk bırak.
                                        if ($endAngle - $startAngle >= 359.999) {
                                            $endAngle = $startAngle + 359.99;
                                        }
                                        $r = 46;
                                        $cx = 50;
                                        $cy = 50;
                                        $toRad = fn ($deg) => ($deg - 90) * M_PI / 180;
                                        $x1 = $cx + $r * cos($toRad($startAngle));
                                        $y1 = $cy + $r * sin($toRad($startAngle));
                                        $x2 = $cx + $r * cos($toRad($endAngle));
                                        $y2 = $cy + $r * sin($toRad($endAngle));
                                        $largeArc = ($endAngle - $startAngle) > 180 ? 1 : 0;
                                        $pieSlices[] = sprintf('M%s,%s L%s,%s A%s,%s 0 %d 1 %s,%s Z', $cx, $cy, round($x1, 3), round($y1, 3), $r, $r, $largeArc, round($x2, 3), round($y2, 3));
                                    }
                                @endphp
                                <div class="chart-pie">
                                    <svg viewBox="0 0 100 100" class="chart-pie-svg" role="img" aria-label="{{ $chart['title'] ?? 'Pasta grafiği' }}">
                                        @foreach($pieSlices as $slicePath)
                                            <path class="chart-pie-slice chart-pie-fill-{{ $loop->index + 1 }}" d="{{ $slicePath }}"></path>
                                        @endforeach
                                    </svg>
                                </div>
                                <div class="chart-legend">
                                    @foreach($chartItems as $item)
                                        <div class="chart-legend-item">
                                            <span class="chart-dot chart-dot-{{ $loop->index + 1 }}"></span>
                                            <div class="chart-legend-main">
                                                <strong>{{ $item['label'] ?? '-' }}</strong>
                                                <div class="chart-legend-bar"><i class="chart-legend-bar-fill-{{ $loop->index + 1 }}" style="width:{{ (int) ($item['percent'] ?? 0) }}%"></i></div>
                                            </div>
                                            <small>{{ (int) ($item['percent'] ?? 0) }}%</small>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(($chart['type'] ?? '') === 'radial')
                                <div class="chart-radial">
                                    @foreach($chartItems as $item)
                                        <div class="chart-radial-row">
                                            <div class="chart-radial-label">{{ $item['label'] ?? '-' }}</div>
                                            <div class="chart-radial-bar"><i style="width:{{ (int) ($item['percent'] ?? 0) }}%"></i></div>
                                            <div class="chart-radial-value">{{ (int) ($item['percent'] ?? 0) }}%</div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(($chart['type'] ?? '') === 'column')
                                @php
                                    $columnItems = $chartItems;
                                    $columnMax = max(1, max(array_map(fn ($item) => (int) ($item['value'] ?? $item['percent'] ?? 0), $columnItems ?: [[ 'value' => 0 ]])));
                                @endphp
                                <div class="chart-column">
                                    <div class="chart-column-bars">
                                        @foreach($columnItems as $item)
                                            @php
                                                $columnValue = (int) ($item['value'] ?? $item['percent'] ?? 0);
                                                $columnHeight = max(8, (int) round(($columnValue / $columnMax) * 100));
                                            @endphp
                                            <div class="chart-column-item">
                                                <div class="chart-column-value">{{ $columnValue }}</div>
                                                <div class="chart-column-track">
                                                    <i style="height:{{ $columnHeight }}%"></i>
                                                </div>
                                                <div class="chart-column-label">{{ $item['label'] ?? '-' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($key === 'student_lesson_completion')
                                @php
                                    $rankItems = (array) ($chart['items'] ?? []);
                                    $rankMax = max(1, (int) ($rankItems[0]['value'] ?? $rankItems[0]['percent'] ?? 1));
                                @endphp
                                <div class="chart-rank-list">
                                    @forelse($rankItems as $item)
                                        @php
                                            $rank = $loop->iteration;
                                            $rankValue = (int) ($item['value'] ?? $item['percent'] ?? 0);
                                            $rankPct = max(6, (int) round(($rankValue / $rankMax) * 100));
                                        @endphp
                                        <div class="chart-rank-row chart-rank-row--{{ $rank <= 3 ? $rank : 'default' }}">
                                            <span class="chart-rank-badge">{{ $rank }}</span>
                                            <div class="chart-rank-main">
                                                <strong>{{ $item['label'] ?? '-' }}</strong>
                                                <div class="chart-rank-track"><i style="width:{{ $rankPct }}%"></i></div>
                                            </div>
                                            <span class="chart-rank-value">{{ $rankValue }}</span>
                                        </div>
                                    @empty
                                        <p class="chart-rank-empty">Henüz veri yok.</p>
                                    @endforelse
                                </div>
                            @else
                                <div class="chart-bars">
                                    @foreach((array) ($chart['items'] ?? []) as $item)
                                        <div class="chart-bar-row">
                                            <div class="chart-bar-label">{{ $item['label'] ?? '-' }}</div>
                                            <div class="chart-bar-track"><i style="width:{{ (int) ($item['value'] ?? $item['percent'] ?? 0) }}%"></i></div>
                                            <div class="chart-bar-value">{{ (int) ($item['value'] ?? $item['percent'] ?? 0) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <span class="widget-resize-handle" aria-hidden="true"></span>
                    </article>
                @endforeach
            </section>
        </div>

        <aside class="dashboard-right-column">
            <section class="dashboard-sidebar-stack">
                <div class="dashboard-widget-sidebar-grid" id="dashboard-widget-sidebar-grid" style="grid-template-columns:1fr !important;width:100%;"></div>
                <article class="dashboard-widget dashboard-widget-wide widget-span-12 dashboard-leaderboard-panel" data-widget-key="leaderboard" draggable="true">
                    <div class="widget-head">
                        <div><strong>İlk 5 Öğrenci Başarı Listesi</strong><span>{{ $selectedClassId === 0 ? 'Tüm sınıflar genelinde' : 'Seçili sınıf' }}</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <div class="leaderboard-head-actions">
                            <button type="button" class="leaderboard-full-btn" data-leaderboard-full>Tüm Sıralamayı Göster</button>
                            <button type="button" class="widget-toggle" data-widget-toggle="leaderboard" aria-label="Gizle" title="Gizle">-</button>
                        </div>
                    </div>
                    <div class="teacher-top10-list">
                        @forelse(array_slice(($dashboard['top_students'] ?? []), 0, 5) as $row)
                            <div class="teacher-top10-item">
                                <div class="teacher-top10-rank rank-{{ (int) ($row['rank'] ?? 0) }}">{{ $row['rank'] }}</div>
                                <div class="teacher-top10-main">
                                    <strong>{{ $row['name'] }}</strong>
                                    <span>{{ $row['class_name'] }}</span>
                                </div>
                                <div class="teacher-top10-xp">{{ $row['xp'] }} XP</div>
                            </div>
                        @empty
                            <p>Henüz öğrenci verisi yok.</p>
                        @endforelse
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>
            </section>
        </aside>
    </section>

    <div class="dashboard-actions-bar">
        <button type="button" class="dash-edit-btn dash-edit-btn--open" data-open-widget-editor>
            <span class="dash-edit-btn-icon">✎</span> Widgetleri Düzenle
        </button>
        <button type="button" class="dash-edit-btn dash-edit-btn--save" id="dashboard-save-btn" hidden>
            <span class="dash-edit-btn-icon">💾</span> Düzeni Kaydet
        </button>
        <button type="button" class="dash-edit-btn dash-edit-btn--close" id="dashboard-close-edit-btn" hidden>
            <span class="dash-edit-btn-icon">✕</span> Düzenlemeyi Kapat
        </button>
    </div>

    <aside class="widget-library-panel" id="widget-library-panel" aria-hidden="true">
        <div class="widget-library-head">
            <div>
                <strong>Widget Kütüphanesi</strong>
                <span>Gizlenen widgetleri geri ekleyin</span>
            </div>
            <div class="widget-library-actions">
                <button type="button" class="widget-library-minimize" id="widget-library-minimize" aria-label="Küçült" title="Küçült">â–</button>
                <button type="button" class="widget-library-close" id="widget-library-close" aria-label="Kapat" title="Kapat">×</button>
            </div>
        </div>
        <div class="widget-library-list" id="dashboard-widget-library"></div>
        <button type="button" class="widget-library-fab" id="widget-library-fab" aria-label="Widget kütüphanesini aç" title="Widget kütüphanesini aç">âŠ</button>
    </aside>
</div>

@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-xp-gift-form]');
    if (!form) return;
    const scope = form.querySelector('[data-xp-gift-scope]');
    const classField = form.querySelector('[data-xp-gift-class]');
    const studentField = form.querySelector('[data-xp-gift-students]');
    const classSelect = classField?.querySelector('select');
    const studentSelect = studentField?.querySelector('select');
    const syncTargetFields = () => {
        const value = scope?.value || 'all';
        if (classField) classField.hidden = value !== 'class';
        if (studentField) studentField.hidden = value !== 'students';
        if (classSelect) classSelect.required = value === 'class';
        if (studentSelect) studentSelect.required = value === 'students';
    };
    scope?.addEventListener('change', syncTargetFields);
    syncTargetFields();
    form.addEventListener('submit', (event) => {
        const amount = Number(form.querySelector('[name="amount"]')?.value || 0);
        const targetLabel = scope?.selectedOptions?.[0]?.textContent?.trim() || 'seçilen hedefe';
        if (!window.confirm(`${targetLabel} için öğrenci başına ${amount} XP gönderilecek. Onaylıyor musunuz?`)) {
            event.preventDefault();
        }
    });
})();

(() => {
    const shell = document.querySelector('[data-dashboard-shell]');
    if (!shell) return;
    const grid = document.getElementById('dashboard-widget-grid');
    const sidebarGrid = document.getElementById('dashboard-widget-sidebar-grid');
    const library = document.getElementById('dashboard-widget-library');
    const libraryPanel = document.getElementById('widget-library-panel');
    const saveBtn = document.getElementById('dashboard-save-btn');
    const closeBtn = document.getElementById('dashboard-close-edit-btn');
    const libraryClose = document.getElementById('widget-library-close');
    const libraryMinimize = document.getElementById('widget-library-minimize');
    const libraryFab = document.getElementById('widget-library-fab');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const saveUrl = @json(route('dashboard.widget-layout.save'));
    const initialLayout = @json($layout);

    const defs = {
        quick_qr: { title: 'Mobil QR Girişi', span: 6, order: 10 },
        students: { title: 'Toplam Öğrenci', span: 4, order: 30 },
        active_students: { title: 'Aktif Öğrenci', span: 4, order: 40 },
        classes: { title: 'Sınıf Sayısı', span: 4, order: 50 },
        courses: { title: 'Ders Sayısı', span: 4, order: 60 },
        avg_completion: { title: 'Ortalama Not', span: 4, order: 70 },
        xp: { title: 'Toplam XP', span: 4, order: 80 },
        @if(auth()->user()?->hasRole('admin'))
        xp_gift: { title: 'XP Hediyesi', span: 12, order: 72 },
        @endif
        active_classes: { title: 'Aktif Sınıflar', span: 6, order: 75 },
        chart_success_distribution: { title: 'Başarı Dağılımı', span: 4, order: 85 },
        chart_student_lesson_completion: { title: 'Öğrenci Ders Tamamlama', span: 6, order: 95, zone: 'grid' },
        leaderboard: { title: 'Başarı Listesi', span: 6, order: 100 },
    };

    const state = {};
    Object.entries(defs).forEach(([key, def]) => {
        const saved = initialLayout[key] || {};
        const initialSpan = Number(saved.span || def.span);
        state[key] = {
            visible: saved.visible !== false,
            span: initialSpan,
            gridSpan: Number(saved.gridSpan || initialSpan),
            order: Number(saved.order || def.order),
            zone: saved.zone || (key === 'leaderboard' ? 'sidebar' : 'grid'),
        };
        if (key === 'chart_student_lesson_completion') {
            state[key].zone = 'grid';
            state[key].span = Math.max(6, Number(state[key].span || 6));
            state[key].gridSpan = Math.max(6, Number(state[key].gridSpan || state[key].span || 6));
        }
    });

    let editMode = false;
    let dirty = false;
    let dragKey = null;
    let resizeTarget = null;
    let startX = 0;
    let startWidth = 0;

    const clampSpan = (value) => Math.max(1, Math.min(12, Number(value || 4)));
    const notify = (message) => {
        if (window.appToast) window.appToast('info', message);
    };

    const setEditMode = (enabled) => {
        editMode = !!enabled;
        shell.classList.toggle('is-editing', editMode);
        libraryPanel?.classList.toggle('open', editMode);
        libraryPanel?.classList.toggle('collapsed', false);
        libraryPanel?.classList.toggle('fab-only', false);
        if (saveBtn) saveBtn.hidden = !editMode;
        if (closeBtn) closeBtn.hidden = !editMode;
        if (libraryPanel) libraryPanel.setAttribute('aria-hidden', editMode ? 'false' : 'true');
        render();
        notify(editMode ? 'Düzenleme açık' : 'Düzenleme kapatıldı');
    };

    const getMaxOrder = (zone = null) => Math.max(...Object.entries(state).filter(([, item]) => zone ? item.zone === zone : true).map(([, item]) => Number(item.order || 0)), 0);

    const renderLibrary = () => {
        if (!library) return;
        const hidden = Object.entries(state).filter(([, conf]) => !conf.visible);
        library.innerHTML = hidden.length
            ? hidden.map(([key]) => `<button type="button" class="widget-library-item" data-library-add="${key}"><strong>${defs[key].title}</strong><span>Geri ekle</span></button>`).join('')
            : '<p class="widget-library-empty">Gizli widget yok.</p>';
    };

    const allWidgetNodes = () => Array.from(grid.querySelectorAll('.dashboard-widget'));
    const sidebarWidgetNodes = () => Array.from(sidebarGrid?.querySelectorAll('.dashboard-widget') || []);
    const zoneNode = (zone) => zone === 'sidebar' ? sidebarGrid : grid;

    // FLIP tekniği: yeniden diz(il)me sırasında widget'lar birbirinin üstüne
    // "zıplamak" yerine eski konumundan yeni konumuna yumuşakça kayar.
    const captureRects = () => {
        const map = new Map();
        [...allWidgetNodes(), ...sidebarWidgetNodes()].forEach((card) => {
            if (card.style.display !== 'none') map.set(card, card.getBoundingClientRect());
        });
        return map;
    };
    const playFlip = (firstRects) => {
        [...allWidgetNodes(), ...sidebarWidgetNodes()].forEach((card) => {
            const first = firstRects.get(card);
            if (!first || card.style.display === 'none') return;
            const last = card.getBoundingClientRect();
            const dx = first.left - last.left;
            const dy = first.top - last.top;
            if (Math.abs(dx) < 1 && Math.abs(dy) < 1) return;
            card.style.transition = 'none';
            card.style.transform = `translate(${dx}px, ${dy}px)`;
            void card.offsetWidth;
            requestAnimationFrame(() => {
                card.style.transition = 'transform .38s cubic-bezier(.22,.9,.3,1.08)';
                card.style.transform = '';
                setTimeout(() => { card.style.transition = ''; }, 420);
            });
        });
    };
    const applyMasonrySpans = () => {
        if (!grid) return;
        const rowHeight = 10;
        const rowGap = 10;
        allWidgetNodes().forEach((card) => {
            if (!card || card.style.display === 'none') return;
            const height = Math.max(1, card.offsetHeight || card.getBoundingClientRect().height);
            const span = Math.max(1, Math.ceil((height + rowGap) / (rowHeight + rowGap)));
            card.style.setProperty('--widget-row-span', String(span));
            card.style.gridRowEnd = `span ${span}`;
        });
        if (sidebarGrid) {
            sidebarWidgetNodes().forEach((card) => {
                if (!card || card.style.display === 'none') return;
                card.style.gridRowEnd = 'auto';
            });
        }
    };

    const render = () => {
        if (!grid) {
            renderLibrary();
            return;
        }
        const flipFirstRects = captureRects();
        const cards = Object.entries(state).sort((a, b) => a[1].order - b[1].order);
        const gridOrder = [];
        const sidebarOrder = [];
        cards.forEach(([key, conf]) => {
            const card = shell.querySelector(`[data-widget-key="${key}"]`);
            if (!card) return;
            card.style.display = conf.visible ? '' : 'none';
            card.classList.remove('widget-span-1','widget-span-2','widget-span-3','widget-span-4','widget-span-5','widget-span-6','widget-span-7','widget-span-8','widget-span-9','widget-span-10','widget-span-11','widget-span-12');
            if (['students','active_students','classes','courses','avg_completion','xp'].includes(key) && window.innerWidth >= 1181) {
                conf.span = Math.max(4, Number(conf.span || 4));
            }
            if (key === 'chart_student_lesson_completion') {
                const effectiveSpan = conf.zone === 'grid' ? 6 : clampSpan(conf.span || 4);
                card.classList.add(`widget-span-${effectiveSpan}`);
            } else {
                card.classList.add(`widget-span-${clampSpan(conf.span)}`);
            }
            card.draggable = false;
            card.querySelectorAll('.widget-toggle').forEach((btn) => {
                btn.disabled = !editMode;
                btn.style.display = editMode ? 'inline-flex' : 'none';
            });
            const handle = card.querySelector('.widget-resize-handle');
            if (handle) handle.style.display = editMode ? 'block' : 'none';
            if (conf.zone === 'sidebar' && key !== 'leaderboard') {
                card.style.gridColumn = '1 / -1';
                sidebarOrder.push(card);
            }
            else gridOrder.push(card);
            if (conf.zone !== 'sidebar') {
                card.style.gridColumn = '';
            }
        });
        [...grid.querySelectorAll('.dashboard-widget')].forEach((el) => {
            if (el.dataset.widgetKey !== 'leaderboard') el.remove();
        });
        if (sidebarGrid) {
            [...sidebarGrid.querySelectorAll('.dashboard-widget')].forEach((el) => {
                if (el.dataset.widgetKey !== 'leaderboard') el.remove();
            });
            sidebarOrder
                .filter((card) => card.dataset.widgetKey !== 'leaderboard')
                .forEach((card) => sidebarGrid.appendChild(card));
        }
        gridOrder
            .filter((card) => card.dataset.widgetKey !== 'leaderboard')
            .forEach((card) => grid.appendChild(card));
        const leaderboard = shell.querySelector('[data-widget-key="leaderboard"]');
        if (leaderboard && sidebarGrid) {
            leaderboard.style.gridColumn = '1 / -1';
            sidebarGrid.appendChild(leaderboard);
        }
        if (window.innerWidth <= 640) {
            const qrCard = shell.querySelector('[data-widget-key="quick_qr"]');
            if (qrCard) grid.prepend(qrCard);
        }
        renderLibrary();
        requestAnimationFrame(() => {
            applyMasonrySpans();
            requestAnimationFrame(applyMasonrySpans);
            setTimeout(() => {
                applyMasonrySpans();
                playFlip(flipFirstRects);
            }, 120);
        });
    };

    const payload = () => {
        const data = {};
        Object.entries(state).forEach(([key, conf], index) => {
            data[key] = {
                visible: !!conf.visible,
                span: clampSpan(conf.span),
                order: Number(conf.order || (index + 1) * 10),
                zone: conf.zone || 'grid',
            };
        });
        return data;
    };

    let dragSource = null;
    let dragPlaceholder = null;
    const clearDragState = () => {
        dragSource?.classList.remove('is-dragging');
        dragSource = null;
        dragPlaceholder?.remove();
        dragPlaceholder = null;
        dragKey = null;
    };
    const getVisibleOrder = () => [...allWidgetNodes(), ...sidebarWidgetNodes()].filter((el) => el.style.display !== 'none');
    const syncOrdersFromDom = () => {
        getVisibleOrder().forEach((card, index) => {
            const key = card.dataset.widgetKey;
            if (state[key]) state[key].order = (index + 1) * 10;
        });
        dirty = true;
    };
    const updateZoneFromContainer = (container) => {
        const zone = container?.id === 'dashboard-widget-sidebar-grid' ? 'sidebar' : 'grid';
        if (dragKey && state[dragKey]) {
            state[dragKey].zone = zone;
            if (dragKey === 'chart_student_lesson_completion' && zone === 'grid') {
                state[dragKey].span = state[dragKey].gridSpan || state[dragKey].span || 6;
            }
            if (dragKey === 'chart_student_lesson_completion' && zone === 'sidebar') {
                state[dragKey].gridSpan = state[dragKey].span || state[dragKey].gridSpan || 6;
            }
        }
    };
    const moveDragPlaceholder = (target, clientX, clientY) => {
        const container = target.closest('#dashboard-widget-sidebar-grid') ? sidebarGrid : grid;
        if (!dragPlaceholder || !container || !dragSource || !target || target === dragSource) return;
        const rect = target.getBoundingClientRect();
        const centerY = rect.top + rect.height / 2;
        let before;
        if (container === sidebarGrid) {
            // Kenar çubuğu tek sütunlu; konum sadece dikey eksene göre belirlenir.
            before = clientY < centerY;
        } else {
            // Ana grid çok sütunlu ve satırlar değişken yükseklikte (masonry);
            // imleç hedefin belirgin şekilde üstünde/altındaysa dikey, aynı
            // satırdaysa yatay eksene göre karar ver. Böylece "önce/sonra"
            // kararı gerçek konuma göre tutarlı olur.
            const dy = clientY - centerY;
            if (Math.abs(dy) > rect.height * 0.32) {
                before = dy < 0;
            } else {
                before = clientX < rect.left + rect.width / 2;
            }
        }
        container.insertBefore(dragPlaceholder, before ? target : target.nextSibling);
        updateZoneFromContainer(container);
    };
    shell.addEventListener('pointerdown', (e) => {
        if (!editMode) return;
        const widget = e.target.closest('.dashboard-widget');
        if (!widget || widget.style.display === 'none') return;
        if (e.target.closest('.widget-toggle') || e.target.closest('.widget-resize-handle')) return;
        dragSource = widget;
        dragKey = widget.dataset.widgetKey;
        widget.classList.add('is-dragging');
        dragPlaceholder = document.createElement('div');
        dragPlaceholder.className = 'dashboard-widget widget-drag-placeholder widget-drop-preview';
        dragPlaceholder.style.gridColumn = `span ${clampSpan(state[dragKey]?.span || 3)}`;
        dragPlaceholder.style.minHeight = `${Math.max(140, widget.getBoundingClientRect().height)}px`;
        widget.parentNode.insertBefore(dragPlaceholder, widget.nextSibling);
        updateZoneFromContainer(widget.parentNode);
        widget.setPointerCapture?.(e.pointerId);
    });
    shell.addEventListener('pointermove', (e) => {
        if (!editMode || !dragSource) return;
        const target = document.elementFromPoint(e.clientX, e.clientY)?.closest('.dashboard-widget');
        if (!target || target === dragSource || target.classList.contains('widget-drag-placeholder')) return;
        shell.querySelectorAll('.widget-drop-active').forEach((el) => el.classList.remove('widget-drop-active'));
        target.classList.add('widget-drop-active');
        moveDragPlaceholder(target, e.clientX, e.clientY);
    });
    shell.addEventListener('pointerup', () => {
        if (!editMode || !dragSource) return;
        if (dragPlaceholder && dragSource.parentNode) {
            dragPlaceholder.parentNode?.insertBefore(dragSource, dragPlaceholder);
        }
        shell.querySelectorAll('.widget-drop-active').forEach((el) => el.classList.remove('widget-drop-active'));
        updateZoneFromContainer(dragSource.parentNode);
        syncOrdersFromDom();
        clearDragState();
        render();
    });
    shell.addEventListener('pointercancel', () => {
        shell.querySelectorAll('.widget-drop-active').forEach((el) => el.classList.remove('widget-drop-active'));
        clearDragState();
        render();
    });

    shell.addEventListener('click', (e) => {
        if (!editMode) return;
        const toggle = e.target.closest('[data-widget-toggle]');
        if (toggle) {
            const key = toggle.dataset.widgetToggle;
            if (state[key]) {
                state[key].visible = !state[key].visible;
                dirty = true;
                render();
            }
        }
    });

    library?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-library-add]');
        if (!btn || !editMode) return;
        const key = btn.dataset.libraryAdd;
        state[key].visible = true;
        state[key].zone = 'grid';
        state[key].order = getMaxOrder('grid') + 10;
        dirty = true;
        render();
    });

    const save = async () => {
        if (!dirty) {
            notify('Değişiklik yok');
            return;
        }
        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ layout: payload() }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.ok) {
            notify(data.message || 'Kaydedilemedi');
            return;
        }
        dirty = false;
        notify('Widget düzeni kaydedildi');
        setEditMode(false);
    };

    saveBtn?.addEventListener('click', save);
    closeBtn?.addEventListener('click', () => setEditMode(false));
    document.querySelector('[data-open-widget-editor]')?.addEventListener('click', () => setEditMode(true));
    libraryClose?.addEventListener('click', () => setEditMode(false));
    libraryMinimize?.addEventListener('click', () => {
        libraryPanel?.classList.add('fab-only');
    });
    libraryFab?.addEventListener('click', () => {
        libraryPanel?.classList.remove('fab-only');
        libraryPanel?.classList.add('open');
        libraryPanel?.setAttribute('aria-hidden', 'false');
    });

    let resizeBadge = null;
    const startResize = (e) => {
        if (!editMode) return;
        const handle = e.target.closest('.widget-resize-handle');
        if (!handle) return;
        resizeTarget = e.target.closest('.dashboard-widget');
        if (!resizeTarget) return;
        startX = e.clientX;
        startWidth = resizeTarget.getBoundingClientRect().width;
        resizeTarget.classList.add('is-resizing');
        resizeBadge = document.createElement('div');
        resizeBadge.className = 'widget-resize-badge';
        document.body.appendChild(resizeBadge);
        e.preventDefault();
    };
    const positionResizeBadge = () => {
        if (!resizeBadge || !resizeTarget) return;
        const rect = resizeTarget.getBoundingClientRect();
        resizeBadge.style.left = (rect.left + rect.width / 2) + 'px';
        resizeBadge.style.top = (rect.top + rect.height / 2) + 'px';
    };
    const moveResize = (e) => {
        if (!editMode || !resizeTarget || !grid) return;
        const delta = e.clientX - startX;
        const width = Math.max(260, startWidth + delta);
        const gridWidth = grid.getBoundingClientRect().width;
        const span = Math.max(1, Math.min(12, Math.round((width / gridWidth) * 12)));
        const key = resizeTarget.dataset.widgetKey;
        state[key].span = span;
        dirty = true;
        if (resizeBadge) resizeBadge.textContent = span + ' / 12 sütun';
        render();
        positionResizeBadge();
    };
    const endResize = () => {
        resizeTarget?.classList.remove('is-resizing');
        resizeTarget = null;
        resizeBadge?.remove();
        resizeBadge = null;
    };
    shell.addEventListener('mousedown', startResize);
    window.addEventListener('mousemove', moveResize);
    window.addEventListener('mouseup', endResize);

    window.addEventListener('resize', () => render());

    const masonryObserver = new ResizeObserver(() => {
        requestAnimationFrame(applyMasonrySpans);
    });
    masonryObserver.observe(grid);
    allWidgetNodes().forEach((card) => masonryObserver.observe(card));

    render();
})();

(() => {
    const listEl = document.getElementById('active-classes-list');
    if (!listEl) return;
    const activeClassesUrl = @json(route('dashboard.active-classes'));
    const activeStudentsUrlTemplate = @json(route('dashboard.class.active-students', ['class' => '__CLASS_ID__']));
    const studentLogoutUrlTemplate = @json(route('dashboard.student.force-logout', ['class' => '__CLASS_ID__', 'student' => '__STUDENT_ID__']));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const modal = document.getElementById('active-class-logout-modal');
    const studentsModal = document.getElementById('active-class-students-modal');
    // Onay pop-up'i, ogrenci listesi pop-up'inin (ve tum widget'larin) arkasinda
    // kaliyordu: ikisi de ayni stacking context icinde oldugu icin DOM'da sonra
    // gelen ogrenci listesi pop-up'i uste cikiyordu. Bu yuzden ikisi de body'ye
    // tasinir ve onay pop-up'i her zaman daha yuksek z-index alir.
    const STUDENTS_MODAL_Z = 10000;
    const CONFIRM_MODAL_Z = 10010;
    const bringToFront = (el, zIndex) => {
        if (!el) return;
        if (el.parentElement !== document.body) document.body.appendChild(el);
        el.style.zIndex = String(zIndex);
    };
    bringToFront(studentsModal, STUDENTS_MODAL_Z);
    bringToFront(modal, CONFIRM_MODAL_Z);
    const modalTitle = document.getElementById('active-class-logout-title');
    const modalText = document.getElementById('active-class-logout-text');
    const cancelBtn = document.getElementById('active-class-logout-cancel');
    const confirmBtn = document.getElementById('active-class-logout-confirm');
    let pendingLogout = null;

    const closeModal = () => {
        modal?.classList.remove('open');
        pendingLogout = null;
        if (modalTitle) modalTitle.textContent = 'Sınıftan Çıkış Yaptır';
    };
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    const studentsTitle = document.getElementById('active-class-students-title');
    const studentsList = document.getElementById('active-class-students-list');
    const studentsCloseBtn = document.getElementById('active-class-students-close');

    const closeStudentsModal = () => { studentsModal?.classList.remove('open'); };
    studentsCloseBtn?.addEventListener('click', closeStudentsModal);
    studentsModal?.addEventListener('click', (e) => { if (e.target === studentsModal) closeStudentsModal(); });
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    })[char]);

    async function openStudentsModal(classId, className) {
        if (studentsTitle) studentsTitle.textContent = `${className} - Aktif Öğrenciler`;
        if (studentsList) studentsList.innerHTML = '<p style="margin:0;color:#64748b;font-size:13px;">Yükleniyor...</p>';
        studentsModal?.classList.add('open');
        try {
            const studentsUrl = activeStudentsUrlTemplate.replace('__CLASS_ID__', encodeURIComponent(classId));
            const res = await fetch(studentsUrl, { headers: { 'Accept': 'application/json' } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                if (studentsList) studentsList.innerHTML = '<p style="margin:0;color:#dc2626;font-size:13px;">Liste yüklenemedi.</p>';
                return;
            }
            const students = data.students || [];
            if (!students.length) {
                if (studentsList) studentsList.innerHTML = '<p style="margin:0;color:#64748b;font-size:13px;">Şu an aktif öğrenci yok.</p>';
                return;
            }
            if (studentsList) {
                studentsList.innerHTML = students.map((s) => `
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;">
                        <div style="display:grid;gap:2px;min-width:0;">
                            <strong style="font-size:13px;overflow-wrap:anywhere;">${escapeHtml(s.name)}</strong>
                            <span style="font-size:12px;color:#64748b;">${escapeHtml(s.student_no)}</span>
                        </div>
                        <button type="button" class="btn btn-danger active-student-logout-btn" data-student-id="${Number(s.student_id)}" data-student-name="${escapeHtml(s.name)}" style="padding:5px 9px;font-size:12px;white-space:nowrap;">Çıkış Yap</button>
                    </div>
                `).join('');
                studentsList.querySelectorAll('.active-student-logout-btn').forEach((button) => {
                    button.addEventListener('click', () => {
                        pendingLogout = {
                            type: 'student',
                            classId: String(classId),
                            className,
                            studentId: button.dataset.studentId,
                            studentName: button.dataset.studentName,
                        };
                        if (modalTitle) modalTitle.textContent = 'Öğrenciden Çıkış Yaptır';
                        if (modalText) modalText.textContent = `${button.dataset.studentName} öğrencisinin oturumu kapatılacak. Emin misiniz?`;
                        bringToFront(studentsModal, STUDENTS_MODAL_Z);
                        bringToFront(modal, CONFIRM_MODAL_Z);
                        modal?.classList.add('open');
                    });
                });
            }
        } catch (e) {
            if (studentsList) studentsList.innerHTML = '<p style="margin:0;color:#dc2626;font-size:13px;">Bağlantı hatası oluştu.</p>';
        }
    }

    function render(classes) {
        if (!classes.length) {
            listEl.innerHTML = '<p style="margin:0;color:#64748b;font-size:13px;">Şu an sistemde aktif öğrencisi olan bir sınıf yok.</p>';
            return;
        }
        listEl.innerHTML = classes.map((c) => `
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;">
                <div>
                    <strong style="font-size:14px;">${c.class_name}</strong>
                    <span style="display:block;font-size:12px;color:#64748b;">${c.active_count} öğrenci aktif</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn active-class-detail-link" data-class-id="${c.class_id}" data-class-name="${c.class_name}" style="padding:6px 12px;font-size:13px;">Detay</button>
                    <button type="button" class="btn btn-danger active-class-logout-btn" data-class-id="${c.class_id}" data-class-name="${c.class_name}" style="padding:6px 12px;font-size:13px;">Çıkış Yap</button>
                </div>
            </div>
        `).join('');
        listEl.querySelectorAll('.active-class-logout-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                pendingLogout = {
                    type: 'class',
                    classId: btn.dataset.classId,
                    className: btn.dataset.className,
                };
                if (modalTitle) modalTitle.textContent = 'Sınıftan Çıkış Yaptır';
                if (modalText) modalText.textContent = `${btn.dataset.className} sınıfındaki tüm öğrenci hesaplarından şu an çıkış yaptırılacak. Diğer sınıflar etkilenmez. Emin misiniz?`;
                bringToFront(studentsModal, STUDENTS_MODAL_Z);
                bringToFront(modal, CONFIRM_MODAL_Z);
                modal?.classList.add('open');
            });
        });
        listEl.querySelectorAll('.active-class-detail-link').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                openStudentsModal(link.dataset.classId, link.dataset.className);
            });
        });
    }

    async function load() {
        try {
            const res = await fetch(activeClassesUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            render(data.classes || []);
        } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
    }

    confirmBtn?.addEventListener('click', async () => {
        if (!pendingLogout) return;
        const action = { ...pendingLogout };
        confirmBtn.disabled = true;
        try {
            const endpoint = action.type === 'student'
                ? studentLogoutUrlTemplate
                    .replace('__CLASS_ID__', encodeURIComponent(action.classId))
                    .replace('__STUDENT_ID__', encodeURIComponent(action.studentId))
                : @json(route('dashboard.class.force-logout', ['class' => '__CLASS_ID__']))
                    .replace('__CLASS_ID__', encodeURIComponent(action.classId));
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                closeModal();
                load();
                if (action.type === 'student') openStudentsModal(action.classId, action.className);
            } else {
                window.alert(data.message || 'Işlem basarisiz oldu.');
            }
        } catch (e) {
            window.alert('Baglanti hatasi olustu.');
        } finally {
            confirmBtn.disabled = false;
        }
    });

    load();
    setInterval(load, 20000);
})();

// Başarı listesi: "Tüm Sıralamayı Göster" pop-up'ı
(() => {
    const modal   = document.getElementById('leaderboard-full-modal');
    const openBtn = document.querySelector('[data-leaderboard-full]');
    const closeBtn = document.getElementById('leaderboard-full-close');
    const body    = document.getElementById('lb-modal-body');
    if (!modal || !openBtn) return;

    const RANK_URL = '{{ route("dashboard.ranking-by-class") }}';
    let activeTab  = null;

    const open  = () => modal.classList.add('open');
    const close = () => modal.classList.remove('open');

    function renderRows(students) {
        if (!students || students.length === 0) {
            return '<p style="margin:0;color:#64748b;font-size:13px;text-align:center;padding:20px;">Bu sınıfta öğrenci verisi yok.</p>';
        }
        return students.map(r => `
            <div class="teacher-top10-item leaderboard-class-row">
                <div class="teacher-top10-rank rank-${r.rank}">${r.rank}</div>
                <div class="teacher-top10-main">
                    <strong>${r.name}</strong>
                    <span>${r.class_name}</span>
                </div>
                <div class="teacher-top10-xp">${r.xp} XP</div>
            </div>
        `).join('');
    }

    async function loadClass(classId, btn) {
        if (activeTab === classId) return;
        activeTab = classId;

        // Tab aktif stili
        document.querySelectorAll('.lb-tab-btn').forEach(b => {
            b.style.background = '#eff6ff';
            b.style.color      = '#1d4ed8';
            b.style.borderColor = '#bfdbfe';
        });
        if (btn) {
            btn.style.background  = '#1d4ed8';
            btn.style.color       = '#fff';
            btn.style.borderColor = '#1d4ed8';
        }

        body.innerHTML = '<p style="text-align:center;padding:30px 0;color:#64748b;">Yükleniyor...</p>';

        try {
            const res  = await fetch(`${RANK_URL}?class_id=${classId}`);
            const data = await res.json();
            body.innerHTML = `<section class="leaderboard-class-group"><div class="leaderboard-class-list">${renderRows(data.students)}</div></section>`;
        } catch(e) {
            body.innerHTML = '<p style="text-align:center;padding:20px;color:#ef4444;">Veri yüklenemedi.</p>';
        }
    }

    // Tab click
    document.querySelectorAll('.lb-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => loadClass(parseInt(btn.dataset.classId), btn));
    });

    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        open();
        // Açılışta aktif sınıfı otomatik seç
        const selectedClassId = {{ $selectedClassId ?? 0 }};
        const firstTab = document.querySelector('.lb-tab-btn');
        if (selectedClassId > 0) {
            const activeTabBtn = document.querySelector(`.lb-tab-btn[data-class-id="${selectedClassId}"]`);
            if (activeTabBtn) { loadClass(selectedClassId, activeTabBtn); return; }
        }
        if (firstTab) loadClass(parseInt(firstTab.dataset.classId), firstTab);
    });

    closeBtn?.addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('open')) close();
    });
})();
</script>
@endpush
@endsection
