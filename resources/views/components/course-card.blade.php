@props([
    'title' => '',
    'description' => '',
    'image' => '',
    'logo' => '',
    'age' => '11+',
    'difficulty' => 'Orta',
    'contentUrl' => '#',
    'primaryUrl' => '#',
    'primaryLabel' => 'Derse Başla',
    'deleteUrl' => null,
    'assignEnabled' => false,
    'assignCourseId' => null,
    'assignCourseName' => '',
    'assignCurrentTeacher' => 0,
    'isFavorite' => false,
    'downloadUrl' => null,
])

@php
    $hasCover = filled($image);
    $normalizedDescription = str_replace(["\\r\\n", "\\n", "\\r"], "\n", (string) $description);
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_10px_30px_rgba(15,23,42,.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_20px_45px_rgba(15,23,42,.14)]">
    <div class="relative">
        <div class="relative h-48 overflow-hidden bg-slate-100">
            @if($hasCover)
                <div class="absolute inset-0 bg-cover bg-center transition duration-500 group-hover:scale-[1.03]" style="background-image:url('{{ $image }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-slate-900/12 to-transparent"></div>
                <div class="absolute left-4 top-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/95 shadow-lg ring-1 ring-white/70">
                        <img src="{{ $logo }}" alt="logo" class="h-8 w-8 object-contain">
                    </div>
                    <div class="rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-700 shadow-sm">
                        {{ $age }}
                    </div>
                </div>
            @else
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,29,149,.18),_transparent_35%),linear-gradient(135deg,#e2e8f0_0%,#f8fafc_100%)]"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-xl">
                        <span class="text-3xl">📘</span>
                    </div>
                </div>
            @endif

            @if(!empty($downloadUrl))
                <a href="{{ $downloadUrl }}" title="Dersi indir" aria-label="Dersi indir"
                   class="absolute right-4 top-4 z-20 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 transition hover:scale-105 hover:bg-emerald-600">
                    <span class="text-lg leading-none">&#8681;</span>
                </a>
            @endif

            <div class="absolute bottom-4 left-4 right-4 z-10 flex items-end justify-between gap-4">
                <div class="max-w-[70%]">
                    <p class="mb-2 inline-flex items-center rounded-full bg-white/95 px-3 py-1 text-xs font-semibold tracking-wide text-violet-700 shadow-sm">
                        {{ $difficulty }}
                    </p>
                    <h4 class="text-2xl font-black leading-tight text-white drop-shadow-[0_2px_8px_rgba(15,23,42,.4)]">
                        {{ $title }}
                    </h4>
                </div>
                <div class="shrink-0 rounded-full bg-white/95 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-600 shadow-sm">
                    Ders Kartı
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-4 p-5">
        <p class="line-clamp-3 min-h-[5.5rem] text-[15px] leading-7 text-slate-600">
            {{ $normalizedDescription }}
        </p>

        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                Kapaklı içerik
            </span>
            <span class="inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                {{ $age }}
            </span>
        </div>

        @php
            $btnCount = 2 + (!empty($deleteUrl) ? 1 : 0) + ($assignEnabled ? 1 : 0);
            $btnCols = max(2, min(4, $btnCount));
        @endphp
        <div class="mt-auto grid gap-2" style="grid-template-columns:repeat({{ $btnCols }},minmax(0,1fr));">
            <a href="{{ $contentUrl }}"
               class="inline-flex h-12 items-center justify-center rounded-2xl border border-violet-200 bg-violet-50 px-4 text-sm font-bold text-violet-700 transition hover:bg-violet-100">
                İçerik
            </a>

            <a href="{{ $primaryUrl }}"
               class="inline-flex h-12 items-center justify-center rounded-2xl bg-gradient-to-r from-violet-700 to-indigo-600 px-4 text-sm font-bold text-white shadow-lg shadow-violet-500/20 transition hover:brightness-110">
                {{ $primaryLabel }}
            </a>

            @if(!empty($deleteUrl))
                <a
                    href="{{ $deleteUrl }}"
                    class="course-delete-link inline-flex h-12 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-4 text-sm font-bold text-rose-600 transition hover:bg-rose-100"
                    data-delete-url="{{ $deleteUrl }}"
                >
                    Dersi Sil
                </a>
            @endif

            @if($assignEnabled && !empty($assignCourseId))
                <button
                    type="button"
                    class="inline-flex h-12 items-center justify-center rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:brightness-110"
                    data-assign-course-id="{{ $assignCourseId }}"
                    data-assign-course-name="{{ $assignCourseName }}"
                    data-assign-current-teacher="{{ (int) $assignCurrentTeacher }}"
                >
                    Dersi Ata
                </button>
            @endif
        </div>
    </div>
</article>
