@extends('layout.app')
@section('title','Derslerim')
@section('content')
@php
    $normalizeText = static fn ($value): string => trim((string) \App\Support\Utf8Text::normalize($value));

    $categories = ['Tümü', 'Kodlama', 'Tasarım', 'Elektrik', 'Robotik', 'Teorik', 'Oyun', 'Yapay Zeka'];
    $activeCategory = request('category', 'Tümü');
@endphp
<style>
    .course-search-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .course-search-layout input[name="q"] {
        flex: 1 1 320px;
        min-width: 260px;
    }
    .course-search-layout select {
        height: 3.5rem;
        flex: 0 0 220px;
        border-radius: 0.75rem;
        border: 1px solid #d1d5db;
        background: #fff;
        padding: 0 .9rem;
        font-size: 1.05rem;
        color: #1f2937;
        outline: none;
    }
    .course-search-layout select.course-select-narrow {
        flex: 0 0 130px;
    }
    .course-favorites-filter-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 3.5rem;
        width: 3.5rem;
        flex: 0 0 3.5rem;
        border-radius: 0.75rem;
        border: 1px solid #d1d5db;
        background: #fff;
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2937;
        cursor: pointer;
        user-select: none;
    }
    .course-favorites-filter-toggle:has(input:checked) {
        border-color: #ef4444;
        background: #fef2f2;
        color: #b91c1c;
    }
    .course-favorites-filter-toggle input[type="checkbox"] {
        width: 0;
        height: 0;
        opacity: 0;
        position: absolute;
    }
    .course-favorites-filter-toggle span {
        color: #ef4444;
        font-size: 1.15rem;
        line-height: 1;
    }
    .course-cards-grid {
        display: grid;
        width: 100%;
        gap: 1.5rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        justify-items: start;
        align-items: start;
        grid-auto-flow: row;
    }
    .course-card-cell {
        width: 100%;
        min-width: 0;
    }
    @media (min-width: 640px) {
        .course-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .course-cards-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
</style>

<section class="space-y-5">
    <div class="overflow-x-auto">
        <div class="inline-flex min-w-max items-center gap-2 rounded-2xl bg-gray-100 p-1">
            @foreach($categories as $category)
                <a
                    href="{{ route('student.portal.courses', array_merge(request()->except('page'), ['category' => $category])) }}"
                    class="rounded-xl px-4 py-2 text-lg transition {{ $activeCategory === $category ? 'bg-[#ede9fe] font-semibold text-[#4c1d95] shadow' : 'text-gray-600 hover:bg-white/70' }}"
                >
                    {{ $category }}
                </a>
            @endforeach
        </div>
    </div>

    <form method="GET" class="course-search-layout">
        <input type="hidden" name="category" value="{{ $activeCategory }}">
        <input
            name="q"
            value="{{ $q ?? request('q') }}"
            class="h-14 rounded-xl border border-gray-300 bg-white px-5 text-lg text-gray-800 outline-none ring-[#4c1d95] placeholder:text-gray-400 focus:ring-2"
            placeholder="Ders başlığını aratmak için yazınız."
        >
        <select name="difficulty" class="course-select-narrow" onchange="this.form.submit()">
            <option value="Tumu" @selected(($difficulty ?? '') === '' || ($difficulty ?? '') === 'Tumu')>Seviye</option>
            <option value="Kolay" @selected(($difficulty ?? '') === 'Kolay')>Kolay</option>
            <option value="Orta" @selected(($difficulty ?? '') === 'Orta')>Orta</option>
            <option value="Zor" @selected(($difficulty ?? '') === 'Zor')>Zor</option>
        </select>
        <select name="education_stage" class="course-select-narrow" onchange="this.form.submit()">
            <option value="Tumu" @selected(($educationStage ?? '') === '' || ($educationStage ?? '') === 'Tumu')>Kademe</option>
            <option value="ilkokul" @selected(($educationStage ?? '') === 'ilkokul')>İlkokul</option>
            <option value="ortaokul" @selected(($educationStage ?? '') === 'ortaokul')>Ortaokul</option>
            <option value="lise" @selected(($educationStage ?? '') === 'lise')>Lise</option>
        </select>
        <label class="course-favorites-filter-toggle" title="Favorileri Göster">
            <input type="checkbox" name="favorites_only" value="1" @checked($favoritesOnly ?? false) onchange="this.form.submit()">
            <span aria-hidden="true">♥</span>
        </label>
    </form>

    <div class="course-cards-grid">
        @forelse($courses as $c)
            @php
                $cp = $courseProgress['course-'.$c->id] ?? null;
                $slides = (array) data_get($c->lesson_payload, 'slides', []);
                $firstSlide = $slides[0] ?? [];
                $desc = trim((string) data_get($c->lesson_payload, 'lesson_description', ''));
                if ($desc === '') {
                    $desc = trim((string) data_get($firstSlide, 'description', ''));
                }
                if ($desc === '') {
                    $desc = $normalizeText($c->name) . ' dersi için hazırlanan konu anlatımı ve etkinlik içerikleri.';
                }
                $thumb = (string) ($c->coverImageUrl() ?: data_get($firstSlide, 'image_url') ?: '');
                $difficulty = (string) (data_get($c->lesson_payload, 'difficulty') ?: (((int) ($c->weekly_hours ?? 0) >= 4) ? 'Orta' : 'Kolay'));
                $age = ((int) ($c->schoolClass?->name ?? 5) + 5) . '+';
            @endphp
            <div class="course-card-cell">
                <x-course-card
                    :title="$normalizeText($c->name)"
                    :description="$normalizeText($desc)"
                    :image="$thumb"
                    :logo="url('/public/logo.png')"
                    :age="$age"
                    :difficulty="$difficulty"
                    :primary-url="route('course.detail', ['id' => $c->id])"
                    :primary-label="'Derse Git'"
                    primary-variant="success"
                    :course-id="$c->id"
                    :is-favorite="in_array($c->id, $favoriteCourseIds ?? [])"
                />
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                Henüz atanmış ders bulunmuyor.
            </div>
        @endforelse
    </div>

    <div>
        {{ $courses->links() }}
    </div>
</section>

@endsection