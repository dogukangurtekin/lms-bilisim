@extends('layout.app')
@section('title','Derslerim')
@section('content')
<div class="top"><h1>Derslerim</h1></div>

<div class="lesson-card-grid">
    @forelse($courses as $c)
        @php
            $cp = $courseProgress['course-'.$c->id] ?? null;
            $slides = (array) data_get($c->lesson_payload, 'slides', []);
            $firstSlide = $slides[0] ?? [];
            $desc = trim((string) data_get($firstSlide, 'description', ''));
            if ($desc === '') {
                $desc = trim((string) data_get($firstSlide, 'title', ''));
            }
            if ($desc === '') {
                $desc = $c->name . ' dersi için hazırlanan konu anlatımı ve etkinlik içerikleri.';
            }
            $thumb = data_get($c->lesson_payload, 'cover_image')
                ?: data_get($firstSlide, 'image')
                ?: asset('logo.png');
            $slideCount = count($slides);
            $difficulty = ((int) ($c->weekly_hours ?? 0) >= 4) ? 'Orta' : 'Kolay';
            $age = ((int) ($c->schoolClass?->name ?? 5) + 5) . '+';
        @endphp
        <article class="lesson-tile card">
            <div class="lesson-media">
                <span class="lesson-corner"></span>
                <img src="{{ $thumb }}" alt="{{ $c->name }}">
                <div class="lesson-badges">
                    <span class="lesson-badge age">{{ $age }}</span>
                    <span class="lesson-badge level">{{ $difficulty }}</span>
                </div>
                <span class="lesson-pill">{{ $slideCount }}/{{ max(1, $slideCount) }}</span>
            </div>
            <div class="lesson-content">
                <h3>{{ $c->name }}</h3>
                <p>{{ \Illuminate\Support\Str::limit($desc, 140) }}</p>
                <div class="lesson-actions">
                    @if($cp?->completed)
                        <span class="lesson-complete">✓ Tamamlandı</span>
                    @else
                        <a class="btn" href="{{ route('student.portal.course-show', $c) }}">Derse Başla</a>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="card">Henüz ders bulunmuyor.</div>
    @endforelse
</div>
@endsection
