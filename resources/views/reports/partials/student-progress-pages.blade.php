@php
    $completed = (int) data_get($report, 'kpi.completed_total', 0);
    $total = max(1, (int) data_get($report, 'kpi.total_assignments', 0));
    $donePct = (int) round(($completed / $total) * 100);
    $pending = max(0, $total - $completed);
    $doneAngle = max(0, min(360, (int) round(($completed / $total) * 360)));
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
    <div class="hero">
        <div class="hero-left">
            <img src="{{ url('/public/logo.png') }}" alt="Logo" class="brand-logo">
            <div>
                <h1>Öğrenci Gelişim Raporu</h1>
                <p class="subtitle">{{ $student->user?->name }} · {{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }} · {{ now()->format('d.m.Y') }}</p>
            </div>
        </div>
        <div class="hero-right">
            <div class="score-pill">Genel İlerleme %{{ $donePct }}</div>
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
            $fullCount = $categoryItems
                ->filter(fn ($item) => (int) data_get($item, 'total', 0) > 0 && (int) data_get($item, 'done', 0) >= (int) data_get($item, 'total', 0))
                ->sum(fn ($item) => (int) data_get($item, 'done', 0));
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
        <p class="chart-note">Bu grafikte %100 tamamlanan kategori/ödev sayısı: <strong>{{ $fullCount }}</strong></p>
    </div>

    <div class="page-no">Sayfa 1 / 2</div>
</section>

<section class="report-page page-break">
    <div class="hero compact">
        <div class="hero-left">
            <img src="{{ url('/public/logo.png') }}" alt="Logo" class="brand-logo small">
            <div>
                <h2>Detaylı Görev Raporu</h2>
                <p class="subtitle">Ödevler, oyunlar, kazanımlar ve tarihler</p>
            </div>
        </div>
    </div>

    <article class="panel">
        <h3>Ders Ödevleri / Slayt Görevleri</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th>XP</th>
                </tr>
            </thead>
            <tbody>
            @php
                $courseItems = collect(data_get($report, 'course_items', []))
                    ->filter(function ($item) {
                        $courseName = trim((string) data_get($item, 'course_name', ''));
                        $title = trim((string) data_get($item, 'display_title', data_get($item, 'title', '')));
                        return $courseName !== '' || $title !== '';
                    })
                    ->values();
            @endphp
            @forelse($courseItems as $item)
                <tr>
                    <td>{{ trim((string) data_get($item, 'course_name', '')) !== '' ? data_get($item, 'course_name') : data_get($item, 'display_title', data_get($item, 'title', '-')) }}</td>
                    <td>{{ $fmtDate(data_get($item, 'sort_date')) }}</td>
                    <td>{{ data_get($item, 'status', 'Tamamlandı') }}</td>
                    <td>{{ (int) data_get($item, 'xp', 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Bu öğrenci için raporlanacak ders görevi bulunmuyor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>

    <article class="panel">
        <h3>Oyun / Uygulama Ödevleri</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th>XP</th>
                </tr>
            </thead>
            <tbody>
            @php
                $gameAssignments = collect(data_get($report, 'game_assignments', []))
                    ->filter(function ($item) {
                        return trim((string) data_get($item, 'game_name', '')) !== ''
                            || trim((string) data_get($item, 'title', '')) !== '';
                    })
                    ->values();
            @endphp
            @forelse($gameAssignments as $a)
                @php
                    $aid = data_get($a, 'id');
                    $p = data_get($report, 'game_progress.' . $aid);
                    $gameName = trim((string) data_get($a, 'game_name', ''));
                    $title = trim((string) data_get($a, 'title', ''));
                    $sortDate = data_get($a, 'sort_date', data_get($p, 'completed_at', data_get($p, 'started_at')));
                @endphp
                <tr>
                    <td>{{ $gameName !== '' ? $gameName : ($title !== '' ? $title : '-') }}</td>
                    <td>{{ $fmtDate($sortDate) }}</td>
                    <td>{{ data_get($p, 'completed_at') ? 'Tamamlandı' : (data_get($p, 'started_at') ? 'Devam Ediyor' : 'Bekliyor') }}</td>
                    <td>{{ (int) data_get($p, 'xp_awarded', 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Bu öğrenci için raporlanacak oyun/uygulama görevi bulunmuyor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>

    <article class="panel">
        <h3>Rozetler</h3>
        <div class="badge-wrap">
            @forelse($student->badges as $badge)
                @php
                    $name = (string) ($badge->name ?? 'Rozet');
                    $safeIconMap = [
                        'Ilk Adim' => '🚀',
                        'Odev Ustasi' => '📝',
                        'Oyun Avcisi' => '🎮',
                        'Ders Kesifi' => '📚',
                        'XP 100' => '⭐',
                        'XP 300' => '💎',
                        'Maratoncu' => '⏱️',
                        'Sinif Birincisi' => '🥇',
                        'Okul Birincisi' => '🏆',
                        'Efsane Tamamlayici' => '🌟',
                        'Gorev Serisi 10' => '🔥',
                        'Gorev Serisi 25' => '🏅',
                        'Ders Ustasi' => '🧠',
                        'Ders Efsanesi' => '🎓',
                        'Oyun Uzmani' => '🕹️',
                        'Oyun Sampiyonu' => '🎯',
                        'XP 500' => '🌟',
                        'XP 1000' => '🚀',
                        'Disiplinli Calisma' => '🗃️',
                        'Panel Ustasi' => '📈',
                        'Istikrar Madalyasi' => '🥈',
                        'Tamamlama Zirvesi' => '🏔️',
                    ];
                    $safeIcon = $safeIconMap[$name] ?? '🏅';
                @endphp
                <span class="badge-item">{{ $safeIcon }} {{ $name }}</span>
            @empty
                <span class="badge-item">Henüz rozet kazanılmadı</span>
            @endforelse
        </div>
    </article>

    <div class="page-no">Sayfa 2 / 2</div>
</section>
