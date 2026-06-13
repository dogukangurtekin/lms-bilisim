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
                <a class="card soft-surface soft-surface-sky qr-mobile-widget" href="{{ route('qr.login.menu') }}">
                    <div class="qr-mobile-widget-text">
                        <span>QR Giri&#351; Yap</span>
                        <strong>Hemen okut</strong>
                        <small>Mobil cihazda a&#231;.</small>
                    </div>
                    <img class="qr-mobile-widget-img" src="{{ asset('qr-mini.svg') }}" alt="QR">
                </a>
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
<style>
.qr-mobile-widget {
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    text-decoration: none;
    border: 1px solid #60a5fa;
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
    padding: 16px 18px;
    border-radius: 18px;
}
.qr-mobile-widget-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    flex: 1 1 auto;
    justify-content: center;
    text-align: center;
}
.qr-mobile-widget span {
    font-size: 13px;
    font-weight: 700;
    color: #2563eb;
    letter-spacing: .02em;
}
.qr-mobile-widget strong {
    font-size: 22px;
    line-height: 1.1;
    color: #0f172a;
}
.qr-mobile-widget small {
    font-size: 12px;
    color: #475569;
}
.qr-mobile-widget-img {
    width: 72px;
    height: 72px;
    flex: 0 0 72px;
    object-fit: contain;
}
@media (max-width: 767px) {
    .qr-mobile-widget {
        display: flex;
        margin-bottom: 12px;
    }
    .qr-mobile-widget-text {
        align-items: center;
    }
}
@media (min-width: 768px) {
    .qr-mobile-widget {
        display: none !important;
    }
}
</style>
@endsection
