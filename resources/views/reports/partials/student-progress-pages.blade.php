@php
    $completed = (int) ($report['kpi']['completed_total'] ?? 0);
    $total = max(1, (int) ($report['kpi']['total_assignments'] ?? 0));
    $remaining = max(0, $total - $completed);
    $donePct = (int) round(($completed / $total) * 100);
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
            <img src="{{ asset('logo.png') }}" alt="Logo" class="brand-logo">
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
        <article class="kpi-card"><span>Toplam XP</span><strong>{{ $report['kpi']['total_xp'] ?? 0 }}</strong></article>
        <article class="kpi-card"><span>Tamamlanan Görev</span><strong>{{ $completed }}/{{ $total }}</strong></article>
        <article class="kpi-card"><span>Bekleyen Görev</span><strong>{{ $remaining }}</strong></article>
        <article class="kpi-card">
            <span>Quiz Verisi</span>
            <strong class="small">Katıldığı Quiz: {{ (int) ($report['kpi']['quiz_joined_count'] ?? 0) }}</strong>
            <strong class="small">Quiz Puanı: {{ (int) ($report['kpi']['quiz_total_xp'] ?? 0) }}</strong>
        </article>
        <article class="kpi-card"><span>Okul / Sınıf Sırası</span><strong>{{ $report['kpi']['school_rank'] ?? '-' }} / {{ $report['kpi']['class_rank'] ?? '-' }}</strong></article>
        <article class="kpi-card"><span>Sistemde Geçen Süre</span><strong class="small">{{ $report['kpi']['time_text'] ?? '-' }}</strong></article>
    </div>

    <div class="content-grid">
        <article class="panel">
            <h3>Görev Dağılımı</h3>
            <div class="donut-wrap">
                <div class="donut" style="background: conic-gradient(#2563eb 0 {{ $donePct }}%, #dbeafe {{ $donePct }}% 100%);"></div>
                <div>
                    <p><b>{{ $completed }}</b> görev tamamlandı</p>
                    <p><b>{{ $remaining }}</b> görev beklemede</p>
                    <p><b>{{ $report['kpi']['badge_count'] ?? 0 }}</b> rozet kazanıldı</p>
                </div>
            </div>
        </article>

        <article class="panel">
            <h3>Analiz Özeti</h3>
            <ul class="bullet-list">
                @foreach(($report['analysis'] ?? []) as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        </article>
    </div>

    <div class="panel">
        <h3>Kategori Bazlı Tamamlama Oranı</h3>
        @php
            $categoryItems = collect($report['category_chart'] ?? []);
            $fullCount = $categoryItems->filter(fn ($item) => (int) ($item['value'] ?? 0) >= 100)->count();
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
                @foreach(($report['category_chart'] ?? []) as $item)
                    <div class="category-col">
                        <div class="category-bar-wrap">
                            <span class="category-bar" style="height: {{ max(2, (int) ($item['value'] ?? 0)) }}%; background: {{ $item['color'] ?? '#3b82f6' }};"></span>
                        </div>
                        <small>{{ $item['label'] ?? '-' }}</small>
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
            <img src="{{ asset('logo.png') }}" alt="Logo" class="brand-logo small">
            <div>
                <h2>Detaylı Görev Raporu</h2>
                <p class="subtitle">Ödevler, oyunlar, teslim tarihleri ve kazanımlar</p>
            </div>
        </div>
    </div>

    <article class="panel">
        <h3>Ders Ödevleri / Slayt Görevleri</h3>
        <table class="report-table">
            <thead><tr><th>Ders</th><th>Ödev</th><th>Teslim</th><th>Durum</th><th>XP</th></tr></thead>
            <tbody>
            @forelse(($report['course_items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['course_name'] ?? '-' }}</td>
                    <td>{{ $item['title'] ?? '-' }}</td>
                    <td>{{ isset($item['due_date']) && $item['due_date'] ? $fmtDate($item['due_date']) : '-' }}</td>
                    <td>{{ $item['status'] ?? '-' }}</td>
                    <td>{{ (int) ($item['xp'] ?? 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Ders ödevi bulunmuyor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </article>

    <article class="panel">
        <h3>Oyun / Uygulama Ödevleri</h3>
        <table class="report-table">
            <thead><tr><th>İçerik</th><th>Ödev</th><th>Seviye</th><th>Teslim</th><th>Durum</th><th>XP</th></tr></thead>
            <tbody>
            @forelse(($report['game_assignments'] ?? []) as $a)
                @php
                    $aid = data_get($a, 'id');
                    $p = data_get($report, 'game_progress.' . $aid);
                @endphp
                <tr>
                    <td>{{ data_get($a, 'game_name', '-') }}</td>
                    <td>{{ data_get($a, 'title', '-') }}</td>
                    <td>{{ data_get($a, 'level_from', '-') }} - {{ data_get($a, 'level_to', '-') }}</td>
                    <td>{{ $fmtDate(data_get($a, 'due_date')) }}</td>
                    <td>{{ data_get($p, 'completed_at') ? 'Tamamlandı' : (data_get($p, 'started_at') ? 'Devam Ediyor' : 'Bekliyor') }}</td>
                    <td>{{ (int) data_get($p, 'xp_awarded', 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Oyun/uygulama ödevi bulunmuyor.</td></tr>
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
                        'Odev Ustasi' => '📘',
                        'Oyun Avcisi' => '🎮',
                        'Ders Kesifi' => '📚',
                        'XP 100' => '⭐',
                        'XP 300' => '💎',
                        'Maratoncu' => '⏱️',
                        'Sinif Birincisi' => '🥇',
                        'Okul Birincisi' => '🏆',
                        'Efsane Tamamlayici' => '👑',
                        'Gorev Serisi 10' => '🔥',
                        'Gorev Serisi 25' => '🏅',
                        'Ders Ustasi' => '🧠',
                        'Ders Efsanesi' => '🎓',
                        'Oyun Uzmani' => '🕹️',
                        'Oyun Sampiyonu' => '🎯',
                        'XP 500' => '🌟',
                        'XP 1000' => '🚀',
                        'Disiplinli Calisma' => '🗂️',
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

    <article class="panel">
        <h3>Gelişim Önerileri</h3>
        <ul class="bullet-list">
            @foreach(($report['recommendations'] ?? []) as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </article>

    <div class="page-no">Sayfa 2 / 2</div>
</section>
