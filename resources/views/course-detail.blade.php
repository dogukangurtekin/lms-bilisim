@extends('layout.app')
@section('title', $title)
@section('content')
@php
    $normalizeText = static function ($value): string {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $converted = @mb_convert_encoding($decoded, 'UTF-8', ['UTF-8', 'Windows-1254', 'ISO-8859-9', 'ISO-8859-1', 'Latin1']);

        return trim(strip_tags($converted !== false ? $converted : $decoded));
    };

    $subCourses = collect($subCourses ?? []);
    $subCourseProgress = collect($subCourseProgress ?? []);
    $mainCompleted = (bool) ($isCompleted ?? false);
    $safeTitle = $normalizeText($title);
    $safeDetailTitle = $normalizeText($detailTitle);
    $safeKonu = $normalizeText($konu);
    $safeKazanimlar = collect($kazanimlar ?? [])->map(fn ($item) => $normalizeText($item))->filter()->values();
    $safeEtkinlikler = collect($etkinlikler ?? [])->map(fn ($item) => $normalizeText($item))->filter()->values();
@endphp

<section class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900">{{ $safeTitle }}</h1>
    </div>

    <article class="w-full rounded-2xl bg-white p-6 shadow-lg" style="border:1px solid #e5eef9">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#FDBA12] text-lg font-bold text-white">
                    {{ $lessonNumber }}
                </span>
                <h2 class="text-2xl font-bold text-gray-900">{{ $safeDetailTitle }}</h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->check() && (auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('teacher')))
                    <a href="{{ route('courses.edit', $course) }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Düzenle
                    </a>
                @endif
                @if($mainCompleted)
                    <span class="inline-flex h-12 items-center justify-center rounded-xl bg-emerald-600 px-6 text-base font-semibold text-white">
                        Tamamlandı
                    </span>
                @else
                    <a href="{{ $startUrl ?? '#' }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#FDBA12] px-6 text-base font-semibold text-white transition hover:brightness-95">
                        Derse Başla
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-6 space-y-6 text-gray-700">
            @if($safeKonu !== '')
                <section>
                    <h3 class="text-lg font-bold text-gray-900">Konu:</h3>
                    <p class="mt-2 text-base leading-relaxed">{{ $safeKonu }}</p>
                </section>
            @endif

            @if($safeKazanimlar->isNotEmpty())
                <section>
                    <h3 class="text-lg font-bold text-gray-900">Kazanımlar:</h3>
                    <ul class="mt-2 list-disc space-y-1 pl-6 text-base">
                        @foreach($safeKazanimlar as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if($safeEtkinlikler->isNotEmpty())
                <section>
                    <h3 class="text-lg font-bold text-gray-900">Etkinlikler:</h3>
                    <ul class="mt-2 list-disc space-y-1 pl-6 text-base">
                        @foreach($safeEtkinlikler as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </article>

    @if($subCourses->isNotEmpty())
        <section class="space-y-4">
            @foreach($subCourses as $subCourse)
                @php
                    $subProgress = $subCourseProgress->get('course-' . $subCourse->id);
                    $subCompleted = (bool) data_get($subProgress, 'completed', false);
                    $subKonu = $normalizeText(data_get($subCourse->lesson_payload, 'curriculum.topic', data_get($subCourse->lesson_payload, 'lesson_description', '')));
                    $subKazanimlar = collect(data_get($subCourse->lesson_payload, 'curriculum.outcomes', []))
                        ->concat((array) data_get($subCourse->lesson_payload, 'curriculum.kazanımlar', []))
                        ->concat((array) data_get($subCourse->lesson_payload, 'curriculum.kazanimlar', []))
                        ->map(fn ($item) => $normalizeText($item))
                        ->filter()
                        ->values();
                    $subEtkinlikler = collect(data_get($subCourse->lesson_payload, 'curriculum.activities', []))
                        ->map(fn ($item) => $normalizeText($item))
                        ->filter()
                        ->values();
                @endphp

                <article class="w-full rounded-2xl bg-white p-6 shadow-lg" style="border:1px solid #e5eef9">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#FDBA12] text-lg font-bold text-white">
                                {{ $loop->iteration }}
                            </span>
                            <h2 class="text-2xl font-bold text-gray-900">{{ $normalizeText($subCourse->name) }}</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if(auth()->check() && (auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('teacher')))
                                <a href="{{ route('courses.edit', $subCourse) }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Düzenle
                                </a>
                            @endif
                            @if($subCompleted)
                                <span class="inline-flex h-12 items-center justify-center rounded-xl bg-emerald-600 px-6 text-base font-semibold text-white">
                                    Tamamlandı
                                </span>
                            @else
                                <a href="{{ route('student.portal.course-show', $subCourse) }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#FDBA12] px-6 text-base font-semibold text-white transition hover:brightness-95">
                                    Derse Başla
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 space-y-6 text-gray-700">
                        @if($subKonu !== '')
                            <section>
                                <h3 class="text-lg font-bold text-gray-900">Konu:</h3>
                                <p class="mt-2 text-base leading-relaxed">{{ $subKonu }}</p>
                            </section>
                        @endif

                        @if($subKazanimlar->isNotEmpty())
                            <section>
                                <h3 class="text-lg font-bold text-gray-900">Kazanımlar:</h3>
                                <ul class="mt-2 list-disc space-y-1 pl-6 text-base">
                                    @foreach($subKazanimlar as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        @if($subEtkinlikler->isNotEmpty())
                            <section>
                                <h3 class="text-lg font-bold text-gray-900">Etkinlikler:</h3>
                                <ul class="mt-2 list-disc space-y-1 pl-6 text-base">
                                    @foreach($subEtkinlikler as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</section>
@endsection
