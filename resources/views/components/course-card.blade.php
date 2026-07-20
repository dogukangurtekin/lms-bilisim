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
    $difficultyValue = trim((string) $difficulty);
    $difficultyStyle = match (mb_strtolower($difficultyValue)) {
        'kolay' => 'background:#16a34a;color:#fff;',
        'orta' => 'background:#2563eb;color:#fff;',
        'zor' => 'background:#ef4444;color:#fff;',
        default => 'background:#6d28d9;color:#fff;',
    };
@endphp

<article
    class="group relative flex h-full flex-col overflow-hidden bg-white shadow-[0_16px_42px_rgba(15,23,42,.11)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_60px_rgba(91,33,182,.16)]"
    style="border:1.5px solid rgba(124,58,237,.18);border-radius:24px;box-shadow:0 16px 42px rgba(15,23,42,.11), 0 0 0 1px rgba(167,139,250,.12) inset;"
>
    <div class="relative">
        <div class="relative h-56 overflow-hidden bg-slate-100">
            @if($hasCover)
                <div
                    class="absolute inset-0 bg-no-repeat bg-center transition duration-500 group-hover:scale-[1.01]"
                    style="background-image:url('{{ $image }}');background-size:contain;background-color:#f8fafc;"
                ></div>
            @else
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,29,149,.14),_transparent_32%),linear-gradient(135deg,#eef2ff_0%,#f8fafc_100%)]"></div>
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

            <div class="absolute left-4 top-4 flex items-center gap-3">
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-white shadow-[0_10px_24px_rgba(15,23,42,.12)]">
                    <img src="{{ $logo }}" alt="logo" class="h-10 w-10 object-contain">
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-4 p-5 pt-4">
        <div class="flex items-start justify-between gap-4">
            <h4 class="text-[32px] font-black leading-tight tracking-tight text-slate-900">
                {{ $title }}
            </h4>
            <span class="inline-flex shrink-0 items-center rounded-full px-4 py-2 text-sm font-bold shadow-sm" style="{{ $difficultyStyle }}">
                {{ $difficultyValue !== '' ? $difficultyValue : 'Kolay' }}
            </span>
        </div>

        <p class="min-h-[5rem] text-[17px] leading-8 text-slate-600">
            {{ $normalizedDescription }}
        </p>

        @php
            $btnCount = 2 + (!empty($downloadUrl) ? 1 : 0) + (!empty($deleteUrl) ? 1 : 0) + ($assignEnabled ? 1 : 0);
            $btnCols = max(2, min(4, $btnCount));
        @endphp
        <div class="mt-auto grid gap-3" style="grid-template-columns:repeat({{ $btnCols }},minmax(0,1fr));">
            <a href="{{ $contentUrl }}"
               style="display:inline-flex;align-items:center;justify-content:center;height:54px;border-radius:999px;border:1px solid #7c3aed;background:#fff;color:#5b21b6;font-size:15px;font-weight:800;text-decoration:none;box-shadow:0 10px 20px rgba(15,23,42,.06);transition:transform .15s ease,filter .15s ease;">
                İçerik
            </a>

            <a href="{{ $primaryUrl }}"
               style="display:inline-flex;align-items:center;justify-content:center;height:54px;border-radius:999px;background:#5b21b6;color:#fff;font-size:15px;font-weight:800;text-decoration:none;box-shadow:0 12px 24px rgba(91,33,182,.18);transition:transform .15s ease,filter .15s ease;">
                {{ $primaryLabel }}
            </a>

            @if(!empty($downloadUrl))
                <a
                    href="{{ $downloadUrl }}"
                    title="Dersi indir"
                    aria-label="Dersi indir"
                    style="display:inline-flex;align-items:center;justify-content:center;height:54px;border-radius:999px;background:#0f766e;color:#fff;font-size:15px;font-weight:800;text-decoration:none;box-shadow:0 12px 24px rgba(15,118,110,.18);transition:transform .15s ease,filter .15s ease;"
                >
                    İndir
                </a>
            @endif

            @if(!empty($deleteUrl))
                <a
                    href="{{ $deleteUrl }}"
                    class="course-delete-link"
                    data-delete-url="{{ $deleteUrl }}"
                    style="display:inline-flex;align-items:center;justify-content:center;height:54px;border-radius:999px;background:#ef4444;color:#fff;font-size:15px;font-weight:800;text-decoration:none;box-shadow:0 12px 24px rgba(239,68,68,.18);transition:transform .15s ease,filter .15s ease;"
                >
                    Dersi Sil
                </a>
            @endif

            @if($assignEnabled && !empty($assignCourseId))
                <button
                    type="button"
                    style="display:inline-flex;align-items:center;justify-content:center;height:54px;border-radius:999px;background:#f97316;color:#fff;font-size:15px;font-weight:800;text-decoration:none;border:0;box-shadow:0 12px 24px rgba(249,115,22,.18);cursor:pointer;transition:transform .15s ease,filter .15s ease;"
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
