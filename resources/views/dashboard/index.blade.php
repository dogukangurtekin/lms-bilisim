@extends('layout.app')
@section('title','Öğretmen Paneli')
@section('content')
<div class="teacher-v2 teacher-v2-compact">
    <div class="teacher-v2-layout">
        <div class="teacher-v2-main">
            <section class="v2-hero card soft-surface soft-surface-blue">
                <div>
                    <h1>{{ $dashboard['headline_name'] }} | Öğretmen Paneli</h1>
                    <p>Öğrenci, sınıf ve ders verilerinin anlık özeti</p>
                </div>
                <div class="v2-rates">
                    <div class="v2-rate-item">
                        <div class="v2-rate-head">
                            <span>Katılım</span>
                            <strong>%{{ $dashboard['summary']['participation'] }}</strong>
                        </div>
                        <div class="v2-rate-bar">
                            <i style="width: {{ max(0, min(100, (int) ($dashboard['summary']['participation'] ?? 0))) }}%"></i>
                        </div>
                    </div>
                    <div class="v2-rate-item">
                        <div class="v2-rate-head">
                            <span>İlerleme</span>
                            <strong>%{{ $dashboard['summary']['progress'] }}</strong>
                        </div>
                        <div class="v2-rate-bar">
                            <i style="width: {{ max(0, min(100, (int) ($dashboard['summary']['progress'] ?? 0))) }}%"></i>
                        </div>
                    </div>
                </div>
            </section>

            <section class="v2-metrics">
                <article class="card soft-surface soft-surface-mint"><span>Toplam Öğrenci</span><strong>{{ $dashboard['summary']['total_students'] }}</strong></article>
                <article class="card soft-surface soft-surface-peach"><span>Aktif Öğrenci</span><strong>{{ $dashboard['summary']['active_students'] }}</strong></article>
                <article class="card soft-surface soft-surface-lilac"><span>Sınıf Sayısı</span><strong>{{ $dashboard['summary']['total_classes'] }}</strong></article>
                <article class="card soft-surface soft-surface-sky"><span>Ders Sayısı</span><strong>{{ $dashboard['summary']['total_courses'] }}</strong></article>
                <article class="card soft-surface soft-surface-yellow"><span>Ortalama Not</span><strong>%{{ $dashboard['summary']['avg_completion'] }}</strong></article>
                <article class="card soft-surface soft-surface-rose"><span>Toplam XP</span><strong>{{ $dashboard['summary']['total_xp'] }}</strong></article>
            </section>

            <div class="v2-grid">
                <section class="card soft-surface soft-surface-sky">
                    <h2>Sınıf Sinyalleri</h2>
                    <div class="signal-list">
                        <div><span>Destek Gereken Sınıf</span><strong>{{ $dashboard['signals']['support'] }}</strong></div>
                        <div><span>Motivasyon Lideri</span><strong>{{ $dashboard['signals']['xp_leader'] }}</strong></div>
                        <div><span>Günün Odağı</span><strong>{{ $dashboard['signals']['focus'] }}</strong></div>
                        <div><span>Durum</span><strong>{{ $dashboard['signals']['status'] }}</strong></div>
                    </div>
                    <div class="signal-list" style="margin-top:10px; border-top:1px solid #e5e7eb; padding-top:10px;">
                        <div><span>Haftanın En Aktifi</span><strong>{{ $dashboard['weekly']['most_active'] }}</strong></div>
                        <div><span>En İyi Tamamlama</span><strong>{{ $dashboard['weekly']['best_completion'] }}</strong></div>
                        <div><span>Düşük Aktiflik</span><strong>{{ $dashboard['weekly']['low_activity'] }}</strong></div>
                    </div>
                </section>

                <section class="card soft-surface soft-surface-lilac">
                    <h2>Öğretmen Notları</h2>
                    <div class="note-list">
                        <article><span>Odak</span><p>{{ $dashboard['highlights']['focus_title'] }}: {{ $dashboard['highlights']['focus_desc'] }}</p></article>
                        <article><span>Güç</span><p>{{ $dashboard['highlights']['power_title'] }}: {{ $dashboard['highlights']['power_desc'] }}</p></article>
                        <article><span>Ritim</span><p>{{ $dashboard['highlights']['rhythm_title'] }}: {{ $dashboard['highlights']['rhythm_desc'] }}</p></article>
                        <article><span>Devamsızlık</span><p>Bugün {{ $dashboard['summary']['absent_today'] }} öğrenci devamsız görünüyor.</p></article>
                        <article><span>XP Yoğunluğu</span><p>Öğrenci başına ortalama {{ $dashboard['signals']['xp_per_student'] }} XP birikmiş durumda.</p></article>
                    </div>
                </section>

                <section class="card teacher-top10 soft-surface soft-surface-peach">
                    <h2>İlk 5 Öğrenci Başarı Listesi</h2>
                    <div class="teacher-top10-list">
                        @forelse(collect($dashboard['top_students'] ?? [])->take(5) as $row)
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
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
