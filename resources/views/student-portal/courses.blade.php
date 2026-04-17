@extends('layout.app')
@section('title','Derslerim')
@section('content')
@php
    $categories = ['Tumu', 'Kodlama', 'Tasarim', 'Elektrik', 'Robotik', 'Teorik', 'Oyun', 'Yapay Zeka'];
    $activeCategory = request('category', 'Tumu');
@endphp
<style>
    .course-search-layout {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: minmax(0, 1fr);
    }
    .course-cards-grid {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
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
            placeholder="Ders basligini aratmak icin yaziniz."
        >
    </form>

    <div class="course-cards-grid">
        @forelse($courses as $c)
            @php
                $cp = $courseProgress['course-'.$c->id] ?? null;
                $slides = (array) data_get($c->lesson_payload, 'slides', []);
                $firstSlide = $slides[0] ?? [];
                $desc = trim((string) data_get($c->lesson_payload, 'lesson_description', ''));
                if ($desc === '') $desc = trim((string) data_get($firstSlide, 'description', ''));
                if ($desc === '') $desc = $c->name . ' dersi icin hazirlanan konu anlatimi ve etkinlik icerikleri.';
                $thumb = data_get($c->lesson_payload, 'cover_image')
                    ?: data_get($firstSlide, 'image_url')
                    ?: '';
                if ($thumb !== '') {
                    $thumb = preg_replace('#^https?://[^/]+/[^/]+/public/storage/#i', '', $thumb);
                    $thumb = preg_replace('#^https?://[^/]+/public/storage/#i', '', $thumb);
                    $thumb = preg_replace('#^https?://[^/]+/storage/#i', '', $thumb);
                    $thumb = preg_replace('#^/?storage/#i', '', $thumb);
                    $thumb = preg_replace('#^/?course-covers/#i', '', $thumb);
                    if (!preg_match('#^https?://#i', $thumb)) {
                        $thumb = route('courses.cover', ['path' => ltrim($thumb, '/')]);
                    }
                }
                $difficulty = (string) (data_get($c->lesson_payload, 'difficulty') ?: (((int) ($c->weekly_hours ?? 0) >= 4) ? 'Orta' : 'Kolay'));
                $age = ((int) ($c->schoolClass?->name ?? 5) + 5) . '+';
            @endphp
            <x-course-card
                :title="$c->name"
                :description="$desc"
                :image="$thumb"
                :logo="asset('logo.png')"
                :age="$age"
                :difficulty="$difficulty"
                :content-url="route('course.detail', ['id' => $c->id])"
                :primary-url="route('student.portal.course-show', $c)"
                :primary-label="$cp?->completed ? 'Tamamlandı' : 'Derse Başla'"
            />
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                Henuz atanmis ders bulunmuyor.
            </div>
        @endforelse
    </div>

    <div>
        {{ $courses->links() }}
    </div>
</section>
@endsection
