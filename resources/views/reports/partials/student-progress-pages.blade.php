@php
    $completed = (int) data_get($report, 'kpi.completed_total', 0);
    $total = max(1, (int) data_get($report, 'kpi.total_assignments', 0));
    $donePct = (int) round(($completed / $total) * 100);
    $pending = max(0, $total - $completed);
    $doneAngle = max(0, min(360, (int) round(($completed / $total) * 360)));
    $taskTrend = collect((array) data_get($report, 'task_trend', []))->values();
    $appTrend = collect((array) data_get($report, 'app_trend', []))->values();
    $trendMax = max(1, (int) max(
        $taskTrend->max(fn ($item) => (int) data_get($item, 'value', 0)) ?? 0,
        $appTrend->max(fn ($item) => (int) data_get($item, 'value', 0)) ?? 0
    ));
    // Son 7 günlük çalışma ritmi: tüm etkinlik kayıtlarından (ders, ödev, oyun, quiz, yarışma) günlük özet.
    $weekKinds = [
        'ders'  => ['label' => 'Ders', 'color' => '#2457d6'],
        'odev'  => ['label' => 'Ödev', 'color' => '#f59e0b'],
        'oyun'  => ['label' => 'Oyun / Uygulama', 'color' => '#19a69a'],
        'yaris' => ['label' => 'Quiz / Yarışma', 'color' => '#8b5cf6'],
    ];
    $kindToGroup = function (string $kind): string {
        if ($kind === 'Ders') {
            return 'ders';
        }
        if (in_array($kind, ['Ders Ödevi', 'Ödev'], true)) {
            return 'odev';
        }
        if (in_array($kind, ['Canlı Quiz', 'Canlı Yarışma', 'Klavye Yarışı'], true)) {
            return 'yaris';
        }
        return 'oyun';
    };
    $weekdayNames = [1 => 'Pzt', 2 => 'Sal', 3 => 'Çar', 4 => 'Per', 5 => 'Cum', 6 => 'Cmt', 7 => 'Paz'];
    $weekdayLong = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
    $activityRows = collect(data_get($report, 'activity_log', []))->map(function ($row) {
        try {
            $raw = $row['sort_date'] ?? null;
            $date = $raw instanceof \Carbon\CarbonInterface
                ? \Carbon\Carbon::instance($raw)
                : (! empty($raw) ? \Carbon\Carbon::parse((string) $raw) : null);
        } catch (\Throwable $e) {
            $date = null;
        }
        return ['date' => $date, 'kind' => (string) ($row['kind'] ?? ''), 'xp' => (int) ($row['xp'] ?? 0)];
    })->filter(fn ($row) => $row['date'] !== null);
    $weekDays = collect(range(6, 0))->map(function (int $ago) use ($activityRows, $kindToGroup, $weekKinds, $weekdayNames, $weekdayLong) {
        $day = \Carbon\Carbon::today()->subDays($ago);
        $rows = $activityRows->filter(fn ($row) => $row['date']->isSameDay($day));
        $byGroup = [];
        foreach (array_keys($weekKinds) as $key) {
            $byGroup[$key] = $rows->filter(fn ($row) => $kindToGroup($row['kind']) === $key)->count();
        }
        return [
            'short' => $weekdayNames[$day->dayOfWeekIso],
            'long' => $weekdayLong[$day->dayOfWeekIso],
            'date' => $day->format('d.m'),
            'is_today' => $ago === 0,
            'total' => $rows->count(),
            'xp' => (int) $rows->sum('xp'),
            'groups' => $byGroup,
        ];
    });
    $weekMax = max(1, (int) $weekDays->max('total'));
    $weekTotal = (int) $weekDays->sum('total');
    $weekXp = (int) $weekDays->sum('xp');
    $weekActiveDays = $weekDays->filter(fn ($d) => $d['total'] > 0)->count();
    $weekBest = $weekDays->sortByDesc('total')->first();
    $fmtDate = function ($value): string {
        if (! $value) {
            return '-';
        }
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('d.m.Y');
        }
        try {
            return \Carbon\Carbon::parse((string) $value)->format('d.m.Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };
@endphp

<section class="report-page">
    <div class="report-course-heading">Bilişim Teknolojileri ve Yazılım Dersi</div>
    <div class="hero">
        <div class="hero-left">
            <img src="{{ \App\Support\Brand::logoUrl() }}" alt="Logo" class="brand-logo">
            @if($student->currentAvatar)
                <img src="{{ asset($student->currentAvatar->image_path) }}" alt="Avatar" style="width:72px;height:72px;object-fit:cover;border-radius:16px;border:2px solid #e2e8f0;flex:0 0 auto">
            @endif
            <div>
                <p class="report-eyebrow">ÖĞRENCİ GELİŞİM RAPORU</p>
                <h1>{{ $student->user?->name }}</h1>
                <p class="subtitle">{{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }} · Rapor tarihi {{ now()->format('d.m.Y') }}</p>
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <article class="kpi-card"><span>Toplam XP</span><strong>{{ data_get($report, 'kpi.total_xp', 0) }}</strong></article>
        <article class="kpi-card"><span>Tamamlanan Görev</span><strong>{{ $completed }}</strong></article>
        <article class="kpi-card">
            <span>Okul / Sınıf Sırası</span>
            <strong>{{ data_get($report, 'kpi.school_rank', '-') }} / {{ data_get($report, 'kpi.class_rank', '-') }}</strong>
        </article>
        <article class="kpi-card">
            <span>Quiz Verisi</span>
            <strong class="small">Katıldığı Quiz: {{ data_get($report, 'kpi.quiz_joined_count', 0) }}</strong>
            <strong class="small">Quiz Puanı: {{ data_get($report, 'kpi.quiz_total_xp', 0) }}</strong>
        </article>
        <article class="kpi-card">
            <span>Başarı Oranı</span>
            <strong>{{ $donePct }}%</strong>
        </article>
        <article class="kpi-card"><span>Sistemde Geçen Süre</span><strong class="small">{{ data_get($report, 'kpi.time_text', '-') }}</strong></article>
    </div>

    <div class="content-grid">
        <article class="panel">
            <h3>Görev Özeti</h3>
            <div class="donut-wrap" style="align-items:center;gap:18px;flex-wrap:wrap">
                <div style="width:132px;height:132px;display:grid;place-items:center;flex:0 0 auto;">
                    <div style="width:132px;height:132px;border-radius:50%;background:conic-gradient(#16a34a 0 {{ $doneAngle }}deg,#e5e7eb {{ $doneAngle }}deg 360deg);position:relative;box-shadow:inset 0 0 0 1px rgba(15,23,42,.04);">
                        <div style="position:absolute;inset:16px;border-radius:50%;background:#fff;display:grid;place-items:center;text-align:center;">
                            <div>
                                <div style="font-size:24px;font-weight:900;line-height:1;color:#0f172a;">{{ $completed }}</div>
                                <div style="font-size:12px;color:#475569;font-weight:700;">/{{ $total }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="min-width:180px;flex:1">
                    <p style="margin:0 0 10px;"><b>{{ $completed }}</b> görev tamamlandı</p>
                    <p style="margin:0 0 10px;"><b>{{ $pending }}</b> görev bekliyor</p>
                    <p style="margin:0;"><b>{{ data_get($report, 'kpi.badge_count', 0) }}</b> rozet kazanıldı</p>
                </div>
            </div>
        </article>

        <article class="panel">
            <h3>Analiz Özeti</h3>
            <ul class="bullet-list">
                @foreach((array) data_get($report, 'analysis', []) as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        </article>
    </div>

    <div class="panel">
        <h3>Kategori Bazlı Tamamlama Oranı</h3>
        @php
            $categoryItems = collect(data_get($report, 'category_chart', []));
        @endphp
        <div class="category-chart">
            <div class="category-grid">
                @for($i = 0; $i <= 10; $i++)
                    <span style="bottom: {{ $i * 10 }}%;"></span>
                @endfor
            </div>
            <div class="category-y">
                @for($i = 10; $i >= 0; $i--)
                    <em>{{ $i * 10 }}%</em>
                @endfor
            </div>
            <div class="category-bars">
                @foreach((array) data_get($report, 'category_chart', []) as $item)
                    <div class="category-col">
                        <div class="category-bar-wrap">
                            <span class="category-bar" style="height: {{ max(2, (int) data_get($item, 'value', 0)) }}%; background: {{ data_get($item, 'color', '#3b82f6') }};"></span>
                        </div>
                        <small style="font-size:12px;line-height:1.1;text-align:center;display:block;max-width:100%;word-break:break-word;">{{ data_get($item, 'label', '-') }}</small>
                        <small style="font-size:12px;line-height:1.1;text-align:center;display:block;max-width:100%;color:#475569;">{{ (int) data_get($item, 'done', 0) }}/{{ (int) data_get($item, 'total', 0) }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="parent-insight-grid">
        <article class="parent-insight parent-insight--blue">
            <span>Akademik Ortalama</span>
            <strong>%{{ $donePct }}</strong>
            <small>Genel ilerleme oranıyla eşleştirildi</small>
        </article>
        <article class="parent-insight parent-insight--amber">
            <span>Günlük Egzersiz Başarısı</span>
            <strong>%{{ (int) data_get($report, 'kpi.daily_success_rate', 0) }}</strong>
            <small>{{ (int) data_get($report, 'kpi.daily_correct_count', 0) }} doğru · {{ (int) data_get($report, 'kpi.daily_wrong_count', 0) }} yanlış</small>
        </article>
        <article class="parent-insight parent-insight--rose">
            <span>Canlı Yarışma Katılımı</span>
            <strong>{{ (int) data_get($report, 'kpi.competition_joined_count', 0) }}</strong>
            <small>{{ (int) data_get($report, 'kpi.competition_total_xp', 0) }} XP kazanıldı</small>
        </article>
        <article class="parent-insight parent-insight--green">
            <span>Canlı Quiz Katılımı</span>
            <strong>{{ (int) data_get($report, 'kpi.quiz_joined_count', 0) }}</strong>
            <small>{{ (int) data_get($report, 'kpi.quiz_total_xp', 0) }} XP kazanıldı</small>
        </article>
    </div>

    <article class="panel weekly-trend-panel">
        <div class="weekly-trend-head">
            <h3>Son 7 Günlük Çalışma Ritmi</h3>
            <div class="weekly-trend-legend">
                @foreach($weekKinds as $kindMeta)
                    <span style="--dot: {{ $kindMeta['color'] }}">{{ $kindMeta['label'] }}</span>
                @endforeach
            </div>
        </div>

        <div class="weekly-kpis">
            <div class="weekly-kpi"><span>Toplam Etkinlik</span><strong>{{ $weekTotal }}</strong></div>
            <div class="weekly-kpi"><span>Kazanılan XP</span><strong>{{ $weekXp }}</strong></div>
            <div class="weekly-kpi"><span>Aktif Gün</span><strong>{{ $weekActiveDays }}<small> / 7</small></strong></div>
            <div class="weekly-kpi"><span>En Yoğun Gün</span><strong>@if($weekTotal > 0){{ $weekBest['long'] }}<small> · {{ $weekBest['total'] }}</small>@else - @endif</strong></div>
        </div>

        <div class="weekly-chart" aria-label="Son yedi günlük etkinlik sayısı grafiği">
            <div class="weekly-yaxis">
                <span>{{ $weekMax }}</span>
                <span>{{ (int) round($weekMax / 2) }}</span>
                <span>0</span>
            </div>
            <div class="weekly-plot">
                @foreach($weekDays as $wd)
                    <div class="weekly-col{{ $wd['is_today'] ? ' is-today' : '' }}{{ $wd['total'] === 0 ? ' is-empty' : '' }}">
                        <div class="weekly-count">{{ $wd['total'] > 0 ? $wd['total'] : '' }}</div>
                        <div class="weekly-bar" title="{{ $wd['long'] }} {{ $wd['date'] }}: {{ $wd['total'] }} etkinlik, {{ $wd['xp'] }} XP">
                            @foreach(array_reverse(array_keys($weekKinds)) as $gk)
                                @if($wd['groups'][$gk] > 0)
                                    <i style="height: {{ round($wd['groups'][$gk] / $weekMax * 100, 1) }}%; background: {{ $weekKinds[$gk]['color'] }}" title="{{ $weekKinds[$gk]['label'] }}: {{ $wd['groups'][$gk] }}"></i>
                                @endif
                            @endforeach
                        </div>
                        <div class="weekly-day"><b>{{ $wd['short'] }}</b><small>{{ $wd['date'] }}</small></div>
                        <div class="weekly-xp">{{ $wd['xp'] > 0 ? $wd['xp'].' XP' : '–' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </article>

    <article class="panel">
        <h3>Rozetler</h3>
        <div class="badge-wrap">
            @forelse($student->badges as $badge)
                @php
                    $name = (string) ($badge->name ?? 'Rozet');
                    $iconMap = [
                        'Ilk Adim' => '🚀', 'Odev Ustasi' => '📝', 'Oyun Avcisi' => '🎮', 'Ders Kesifi' => '📚',
                        'XP 100' => '⭐', 'XP 300' => '💎', 'Maratoncu' => '⏱️', 'Sinif Birincisi' => '🥇',
                        'Okul Birincisi' => '🏆', 'Efsane Tamamlayici' => '🌟', 'Gorev Serisi 10' => '🔥',
                        'Gorev Serisi 25' => '🏅', 'Ders Ustasi' => '🧠', 'Ders Efsanesi' => '🎓',
                        'Oyun Uzmani' => '🕹️', 'Oyun Sampiyonu' => '🎯', 'XP 500' => '🌟', 'XP 1000' => '🚀',
                        'Disiplinli Calisma' => '🗃️', 'Panel Ustasi' => '📈', 'Istikrar Madalyasi' => '🥈',
                        'Tamamlama Zirvesi' => '🏔️',
                    ];
                    $rawIcon = trim((string) ($badge->icon ?? ''));
                    $safeIcon = $iconMap[$name] ?? ($rawIcon !== '' && ! preg_match('/[\/\.]/', $rawIcon) ? $rawIcon : '🏅');
                    $desc = trim((string) ($badge->description ?? ''));
                    if ($desc === '' && (int) ($badge->xp_threshold ?? 0) > 0) {
                        $desc = $badge->xp_threshold.' XP değerine ulaşınca kazanılır.';
                    }
                @endphp
                <span class="badge-item" style="display:inline-flex;align-items:flex-start;gap:8px;text-align:left;max-width:100%">
                    <span style="font-size:22px;line-height:1.1">{{ $safeIcon }}</span>
                    <span><strong style="display:block">{{ $name }}</strong>@if($desc !== '')<small style="display:block;opacity:.75;font-weight:400">{{ $desc }}</small>@endif</span>
                </span>
            @empty
                <span class="badge-item">Henüz rozet kazanılmadı</span>
            @endforelse
        </div>
    </article>

    <div class="page-no">Sayfa 1 / 2</div>
</section>

<section class="report-page page-break">
    <div class="hero compact">
        <div class="hero-left">
            <img src="{{ \App\Support\Brand::logoUrl() }}" alt="Logo" class="brand-logo small">
            <div>
                <p class="report-eyebrow">{{ $student->user?->name }} · {{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }}</p>
                <h2>Detaylı Görev Raporu</h2>
                <p class="subtitle">Tüm etkinlikler, kazanımlar ve tarihler</p>
            </div>
        </div>
    </div>

    <article class="panel">
        <h3>Tüm Etkinlikler (Ders, Ödev, Oyun, Quiz, Yarışma)</h3>
        <div class="activity-columns">
            <div>
                <h4 class="activity-col-title">Dersler</h4>
                @include('reports.partials.activity-log-grouped', ['log' => data_get($report, 'activity_log', []), 'tableClass' => 'report-table', 'only' => 'lessons'])
            </div>
            <div>
                <h4 class="activity-col-title">Oyun, Uygulama ve Diğer Etkinlikler</h4>
                @include('reports.partials.activity-log-grouped', ['log' => data_get($report, 'activity_log', []), 'tableClass' => 'report-table', 'only' => 'others'])
            </div>
        </div>
    </article>

    <div class="page-no">Sayfa 2 / 2</div>
</section>
