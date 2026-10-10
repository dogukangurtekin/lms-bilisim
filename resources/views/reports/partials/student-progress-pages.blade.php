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
    // Dönem gelişimi: son 4 ay (16 hafta), haftalık etkinlik yoğunluğu (ders, ödev, oyun, quiz, yarışma).
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
    $monthNames = [1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'];
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

    $termWeekCount = 16;
    $thisMonday = \Carbon\Carbon::today()->startOfWeek();
    $termWeeks = collect(range($termWeekCount - 1, 0))->map(function (int $ago) use ($activityRows, $kindToGroup, $weekKinds, $thisMonday) {
        $start = (clone $thisMonday)->subWeeks($ago)->startOfDay();
        $end = (clone $start)->addDays(6)->endOfDay();
        $rows = $activityRows->filter(fn ($row) => $row['date']->betweenIncluded($start, $end));
        $byGroup = [];
        foreach (array_keys($weekKinds) as $key) {
            $byGroup[$key] = $rows->filter(fn ($row) => $kindToGroup($row['kind']) === $key)->count();
        }
        return [
            'start' => $start,
            'label' => $start->format('d.m'),
            'range' => $start->format('d.m') . ' - ' . $end->format('d.m'),
            'month' => (int) $start->format('n'),
            'year' => (int) $start->format('Y'),
            'total' => $rows->count(),
            'xp' => (int) $rows->sum('xp'),
            'groups' => $byGroup,
        ];
    })->values();
    $termMax = max(1, (int) $termWeeks->max('total'));
    $termTotal = (int) $termWeeks->sum('total');
    $termXp = (int) $termWeeks->sum('xp');
    $termActiveWeeks = $termWeeks->filter(fn ($w) => $w['total'] > 0)->count();
    // Ay başlıkları: ardışık haftaları başlangıç ayına göre grupla.
    $termMonthSegments = [];
    foreach ($termWeeks as $w) {
        $key = $w['year'] . '-' . $w['month'];
        if (! empty($termMonthSegments) && end($termMonthSegments)['key'] === $key) {
            $termMonthSegments[array_key_last($termMonthSegments)]['span']++;
        } else {
            $termMonthSegments[] = ['key' => $key, 'name' => $monthNames[$w['month']], 'span' => 1];
        }
    }
    // Birikimli XP çizgisi (SVG, 0-100 koordinat uzayı)
    $cum = 0;
    $cumPoints = [];
    foreach ($termWeeks as $i => $w) {
        $cum += $w['xp'];
        $cumPoints[] = ['x' => ($i + 0.5) / $termWeekCount * 100, 'v' => $cum];
    }
    $cumMax = max(1, $cum);
    $cumPolyline = collect($cumPoints)->map(fn ($p) => round($p['x'], 2) . ',' . round(100 - ($p['v'] / $cumMax * 96) - 2, 2))->implode(' ');
    // Trend: son 8 hafta ile önceki 8 haftanın karşılaştırması
    $firstHalf = (int) $termWeeks->take(8)->sum('total');
    $lastHalf = (int) $termWeeks->slice(8)->sum('total');
    if ($firstHalf > 0) {
        $trendPct = (int) round((($lastHalf - $firstHalf) / $firstHalf) * 100);
        $trendDir = $trendPct > 0 ? 'up' : ($trendPct < 0 ? 'down' : 'flat');
        $trendText = ($trendPct > 0 ? '▲ %' : ($trendPct < 0 ? '▼ %' : '● %')) . abs($trendPct);
    } elseif ($lastHalf > 0) {
        $trendDir = 'up';
        $trendText = '▲ Yeni ivme';
    } else {
        $trendDir = 'flat';
        $trendText = '-';
    }
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
        <article class="panel task-summary">
            <h3>Görev Özeti</h3>
            @php
                $ringPct = max(0, min(100, $donePct));
                $ringR = 46;
                $ringC = 2 * M_PI * $ringR;
                $ringDash = round($ringC * $ringPct / 100, 2);
                [$ringFrom, $ringTo] = $ringPct >= 75 ? ['#22c55e', '#0d9488'] : ($ringPct >= 40 ? ['#3b82f6', '#6366f1'] : ['#f59e0b', '#ef4444']);
                $ringLabel = $ringPct >= 75 ? 'Çok iyi' : ($ringPct >= 40 ? 'Gelişiyor' : 'Destek gerekli');
            @endphp
            <div class="task-summary-body">
                <div class="task-ring" role="img" aria-label="Görev tamamlama oranı %{{ $ringPct }}">
                    <svg viewBox="0 0 120 120" width="100%" height="100%">
                        <defs>
                            <linearGradient id="taskRingGrad{{ $ringPct }}" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="{{ $ringFrom }}"/>
                                <stop offset="100%" stop-color="{{ $ringTo }}"/>
                            </linearGradient>
                        </defs>
                        <circle cx="60" cy="60" r="{{ $ringR }}" fill="none" stroke="#e8edf7" stroke-width="11"/>
                        @if($ringPct > 0)
                            <circle cx="60" cy="60" r="{{ $ringR }}" fill="none" stroke="url(#taskRingGrad{{ $ringPct }})" stroke-width="11" stroke-linecap="round" stroke-dasharray="{{ $ringDash }} {{ round($ringC, 2) }}" transform="rotate(-90 60 60)"/>
                        @endif
                    </svg>
                    <div class="task-ring-center">
                        <strong>%{{ $ringPct }}</strong>
                        <span>{{ $completed }} / {{ $total }} görev</span>
                    </div>
                </div>
                <div class="task-stats">
                    <div class="task-stat task-stat--done">
                        <svg viewBox="0 0 20 20" width="15" height="15" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#dcfce7" stroke="#4ade80" stroke-width="1.2"/><path d="M5.8 10.4l2.8 2.8 5.6-6" fill="none" stroke="#15803d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <b>{{ $completed }}</b><span>Tamamlanan</span>
                    </div>
                    <div class="task-stat task-stat--wait">
                        <svg viewBox="0 0 20 20" width="15" height="15" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#fef3c7" stroke="#fbbf24" stroke-width="1.2"/><path d="M10 5.6V10l3 1.8" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <b>{{ $pending }}</b><span>Bekleyen</span>
                    </div>
                    <div class="task-stat task-stat--badge">
                        <svg viewBox="0 0 20 20" width="15" height="15" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="#ede9fe" stroke="#a78bfa" stroke-width="1.2"/><path d="M10 4.8l1.5 3.1 3.4.5-2.5 2.4.6 3.4-3-1.6-3 1.6.6-3.4-2.5-2.4 3.4-.5z" fill="#7c3aed"/></svg>
                        <b>{{ data_get($report, 'kpi.badge_count', 0) }}</b><span>Rozet</span>
                    </div>
                    <div class="task-level" style="--c1: {{ $ringFrom }}; --c2: {{ $ringTo }}">{{ $ringLabel }}</div>
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

    <article class="panel term-panel">
        <div class="term-head">
            <h3>Dönem Gelişim Grafiği <small>Son 4 ay · haftalık etkinlik yoğunluğu</small></h3>
            <div class="term-legend">
                @foreach($weekKinds as $kindMeta)
                    <span style="--dot: {{ $kindMeta['color'] }}">{{ $kindMeta['label'] }}</span>
                @endforeach
                <span class="is-line" style="--dot: #e11d48">Birikimli XP</span>
            </div>
        </div>

        <div class="term-kpis">
            <div class="term-kpi"><span>Dönem Etkinliği</span><strong>{{ $termTotal }}</strong></div>
            <div class="term-kpi"><span>Dönem XP</span><strong>{{ $termXp }}</strong></div>
            <div class="term-kpi"><span>Aktif Hafta</span><strong>{{ $termActiveWeeks }}<small> / {{ $termWeekCount }}</small></strong></div>
            <div class="term-kpi term-kpi--{{ $trendDir }}"><span>Dönem Trendi</span><strong>{{ $trendText }}</strong><em>son 8 hafta / önceki 8 hafta</em></div>
        </div>

        <div class="term-chart" aria-label="Son dört aylık haftalık etkinlik grafiği">
            <div class="term-yaxis">
                <span>{{ $termMax }}</span>
                <span>{{ (int) round($termMax / 2) }}</span>
                <span>0</span>
            </div>
            <div class="term-plot-wrap">
                <div class="term-months" style="grid-template-columns: repeat({{ $termWeekCount }}, minmax(0, 1fr))">
                    @foreach($termMonthSegments as $seg)
                        <span style="grid-column: span {{ $seg['span'] }}">{{ $seg['name'] }}</span>
                    @endforeach
                </div>
                <div class="term-plot" style="grid-template-columns: repeat({{ $termWeekCount }}, minmax(0, 1fr))">
                    @foreach($termWeeks as $w)
                        <div class="term-col{{ $w['total'] === 0 ? ' is-empty' : '' }}">
                            <div class="term-count">{{ $w['total'] > 0 ? $w['total'] : '' }}</div>
                            <div class="term-bar" title="{{ $w['range'] }}: {{ $w['total'] }} etkinlik, {{ $w['xp'] }} XP">
                                @foreach(array_reverse(array_keys($weekKinds)) as $gk)
                                    @if($w['groups'][$gk] > 0)
                                        <i style="height: {{ round($w['groups'][$gk] / $termMax * 100, 1) }}%; background: {{ $weekKinds[$gk]['color'] }}" title="{{ $weekKinds[$gk]['label'] }}: {{ $w['groups'][$gk] }}"></i>
                                    @endif
                                @endforeach
                            </div>
                            <div class="term-week">{{ $w['label'] }}</div>
                        </div>
                    @endforeach
                    @if($termXp > 0)
                        <svg class="term-line" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                            <polyline points="{{ $cumPolyline }}" fill="none" stroke="#e11d48" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                        </svg>
                        <span class="term-line-end">{{ $termXp }} XP</span>
                    @endif
                </div>
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
