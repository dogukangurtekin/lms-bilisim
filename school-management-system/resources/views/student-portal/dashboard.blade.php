@extends('layout.app')
@section('title','Panelim')
@section('content')
<div class="student-v2-compact">
    <section class="student-hero-layout">
        <article class="card student-hero-panel">
            @if($pendingAssignments > 0)
                <section class="student-assignment-alert is-urgent">
                    <div class="student-assignment-alert-icon" aria-hidden="true">!</div>
                    <div class="student-assignment-alert-content">
                        <p class="student-assignment-alert-eyebrow">Bekleyen Ödev Bildirimi</p>
                        <h3>{{ $pendingAssignments }} adet bekleyen ödevin var</h3>
                        <p>
                            Ders/Slayt: <strong>{{ $pendingCourses }}</strong> •
                            Oyun/Uygulama: <strong>{{ $pendingGameApps }}</strong>
                        </p>
                    </div>
                    <div class="student-assignment-alert-action">
                        @if($pendingGameApps > 0)
                            <a class="btn" href="{{ route('student.portal.assignments') }}">Ödevlerime Git</a>
                        @endif
                        @if($pendingCourses > 0)
                            <a class="btn" href="{{ route('student.portal.courses') }}">Derslerime Git</a>
                        @endif
                    </div>
                </section>
            @endif

            <div class="student-hero-pill">SEVIYE HARITAN HAZIR</div>
            <h1>{{ $student->user?->name ?? 'Öğrenci' }}, bugün yeni bir adım atmaya hazır mısın?</h1>
            <p class="student-hero-sub">Görevlerini tamamlamaya devam et, istatistiklerini takip et ve liderlikte yerini koru.</p>

            <div class="student-hero-kpis">
                <article><span>Toplam XP</span><strong>{{ $xp }} XP</strong></article>
                <article><span>Tamamlanan</span><strong>{{ $completedAssignments }}</strong></article>
                <article><span>Bekleyen</span><strong>{{ $pendingAssignments }}</strong></article>
                <article><span>Sınıf Sıralaması</span><strong>{{ $gradeRank ?? '-' }}</strong></article>
                <article><span>Okul Sıralaması</span><strong>{{ $schoolRank ?? '-' }}</strong></article>
            </div>

            <div class="student-progress-box">
                <div class="student-progress-head">
                    <span>Genel İlerleme</span>
                    <strong>%{{ $overallProgress }}</strong>
                </div>
                <div class="compact-progress-track">
                    <div style="height:100%;width:{{ $overallProgress }}%;background:linear-gradient(90deg,#22c55e,#3b82f6)"></div>
                </div>
                <p>İlerleme alanın görevlerine göre otomatik güncellenir.</p>
            </div>
        </article>

        <aside class="card student-leaderboard-panel">
            <h3>Öğrenci Başarı Listesi (İlk 7)</h3>
            <div class="student-leaderboard-list">
                @foreach($topGrade->take(7) as $i => $item)
                    <div class="student-leaderboard-item">
                        <div class="rank-badge">{{ $i + 1 }}</div>
                        <div class="leader-user">
                            @if(!empty($item['avatar']))
                                <img src="{{ asset($item['avatar']) }}" alt="avatar">
                            @endif
                            <span>{{ $item['name'] ?? '-' }}</span>
                        </div>
                        <div class="xp-pill">{{ $item['xp'] ?? 0 }} XP</div>
                    </div>
                @endforeach
            </div>
        </aside>
    </section>

    <div class="v2-grid student-rank-grid">
        <section class="card student-flow-card">
            <h3>Sistem Özeti</h3>
            <div class="signal-list">
                <div><span>Tamamlanan Oyun/Uygulama</span><strong>{{ $completedGameApps }}</strong></div>
                <div><span>Bekleyen Oyun/Uygulama</span><strong>{{ $pendingGameApps }}</strong></div>
                <div><span>Bekleyen Ders/Slayt Ödevi</span><strong>{{ $pendingCourses }}</strong></div>
                <div><span>Aktif Toplam Ödev</span><strong>{{ $totalAssignments }}</strong></div>
            </div>
        </section>
    </div>

    <div class="v2-grid student-action-grid">
        <section class="card student-goal-card">
            <h3>Bugün / Hafta Hedefi</h3>
            <div class="goal-row">
                <div class="goal-meta">
                    <span>Bugün {{ $dailyGoalTarget }} görev</span>
                    <strong>Tamamlanan: {{ $todayCompleted }}</strong>
                </div>
            </div>
            <div class="goal-row">
                <div class="goal-meta">
                    <span>Hafta hedefi {{ $weeklyGoalTarget }} görev</span>
                    <strong>Kalan: {{ $weekRemaining }}</strong>
                </div>
                <div class="goal-track goal-track-week"><i style="width:{{ $weeklyGoalPct }}%"></i></div>
            </div>
            <p class="goal-foot">Hedefe kalan XP: <b>{{ $xpToGoal }}</b> • Bu hafta kazanılan: <b>{{ $xpWeekEarned }}</b></p>
        </section>

        <section class="card student-next-card student-heatmap-card">
            <h3>Son 7 Gün Isı Haritası</h3>
            <div class="heatmap-grid">
                @foreach($heatmapDays as $day)
                    <div class="heatmap-cell level-{{ $day['level'] }}" title="{{ $day['label'] }} • {{ $day['completed'] }} görev • {{ $day['xp'] }} XP">
                        <span>{{ $day['label'] }}</span>
                        <strong>{{ $day['completed'] }}</strong>
                    </div>
                @endforeach
            </div>
            <p class="heatmap-legend">Renk yoğunluğu aktiviteye göre artar (boş/az/orta/çok).</p>
        </section>
    </div>
</div>
@endsection
