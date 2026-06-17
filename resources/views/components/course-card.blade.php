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

<article class="group relative flex h-full flex-col overflow-hidden rounded-[30px] bg-white shadow-[0_14px_40px_rgba(15,23,42,.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_26px_55px_rgba(15,23,42,.14)]">
    <div class="relative">
        <div class="relative h-52 overflow-hidden bg-slate-100">
            @if($hasCover)
                <div class="absolute inset-0 bg-cover bg-center transition duration-500 group-hover:scale-[1.03]" style="background-image:url('{{ $image }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-slate-900/15 to-transparent"></div>
                <div class="absolute left-4 top-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/95 shadow-lg">
                        <img src="{{ $logo }}" alt="logo" class="h-8 w-8 object-contain">
                    </div>
                    <div class="rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-700 shadow-sm">
                        {{ $age }}
                    </div>
                </div>
            @else
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,29,149,.18),_transparent_35%),linear-gradient(135deg,#e2e8f0_0%,#f8fafc_100%)]"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-[28px] bg-white shadow-xl">
                        <span class="text-3xl">📘</span>
                    </div>
                </div>
            @endif

            @if(!empty($downloadUrl))
                <a href="{{ $downloadUrl }}" title="Dersi indir" aria-label="Dersi indir"
                   class="absolute right-4 top-4 z-20 inline-flex h-11 w-11 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 transition hover:scale-105 hover:bg-emerald-600">
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
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-4 p-5 pt-4">
        <p class="line-clamp-3 min-h-[5rem] text-[15px] leading-7 text-slate-600">
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
        <div class="mt-auto grid gap-3" style="grid-template-columns:repeat({{ $btnCols }},minmax(0,1fr));">
            <a href="{{ $contentUrl }}"
               style="display:inline-flex;align-items:center;justify-content:center;height:48px;border-radius:999px;background:linear-gradient(135deg,#1f2937,#0f172a);color:#fff;font-size:15px;font-weight:800;text-decoration:none;box-shadow:0 10px 20px rgba(15,23,42,.12);transition:transform .15s ease,filter .15s ease;">
                İçerik
            </a>

            <a href="{{ $primaryUrl }}"
               style="display:inline-flex;align-items:center;justify-content:center;height:48px;border-radius:999px;background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;font-size:15px;font-weight:800;text-decoration:none;box-shadow:0 12px 24px rgba(124,58,237,.18);transition:transform .15s ease,filter .15s ease;">
                {{ $primaryLabel }}
            </a>

            @if(!empty($deleteUrl))
                <a
                    href="{{ $deleteUrl }}"
                    class="course-delete-link"
                    data-delete-url="{{ $deleteUrl }}"
                    style="display:inline-flex;align-items:center;justify-content:center;height:48px;border-radius:999px;background:linear-gradient(135deg,#fb7185,#e11d48);color:#fff;font-size:15px;font-weight:800;text-decoration:none;box-shadow:0 12px 24px rgba(225,29,72,.18);transition:transform .15s ease,filter .15s ease;"
                >
                    Dersi Sil
                </a>
            @endif

            @if($assignEnabled && !empty($assignCourseId))
                <button
                    type="button"
                    style="display:inline-flex;align-items:center;justify-content:center;height:48px;border-radius:999px;background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;font-size:15px;font-weight:800;text-decoration:none;border:0;box-shadow:0 12px 24px rgba(249,115,22,.18);cursor:pointer;transition:transform .15s ease,filter .15s ease;"
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
