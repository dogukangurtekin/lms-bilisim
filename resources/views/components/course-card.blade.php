@props([
    'title' => '',
    'description' => '',
    'image' => '',
    'logo' => '',
    'age' => '11+',
    'difficulty' => 'Orta',
    'contentUrl' => '#',
    'previewUrl' => null,
    'primaryUrl' => '#',
    'primaryLabel' => 'Derse Başla',
    'previewLabel' => 'Önizle',
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
    $normalizedDescription = str_replace(["\r\n", "\n", "\r"], "\n", (string) $description);
    $difficultyValue = trim((string) $difficulty);
    $difficultyStyle = match (mb_strtolower($difficultyValue)) {
        'kolay' => 'background:#16a34a;color:#fff;',
        'orta' => 'background:#2563eb;color:#fff;',
        'zor' => 'background:#ef4444;color:#fff;',
        default => 'background:#6d28d9;color:#fff;',
    };
    $hasPreview = filled($previewUrl);
@endphp

<article class="group relative flex h-full flex-col overflow-hidden bg-white shadow-[0_16px_42px_rgba(15,23,42,.11)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_60px_rgba(91,33,182,.16)]" style="border:1.5px solid rgba(124,58,237,.18);border-radius:24px;box-shadow:0 16px 42px rgba(15,23,42,.11), 0 0 0 1px rgba(167,139,250,.12) inset;">
    <div class="relative">
        <div class="relative h-56 overflow-hidden bg-slate-100">
            @if($hasCover)
                <img
                    src="{{ $image }}"
                    alt="kapak görseli"
                    class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-[1.02]"
                    loading="lazy"
                >
            @else
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,29,149,.14),_transparent_32%),linear-gradient(135deg,#eef2ff_0%,#f8fafc_100%)]"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-[28px] bg-white shadow-xl">
                        <span class="text-3xl">?</span>
                    </div>
                </div>
            @endif

            <div style="position:absolute;top:14px;right:14px;left:auto;z-index:60;display:flex;gap:10px;align-items:center;pointer-events:auto;">
                @if(!empty($downloadUrl))
                    <a href="{{ $downloadUrl }}" title="Dersi indir" aria-label="Dersi indir" style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:999px;background:#10b981;color:#fff;box-shadow:0 12px 28px rgba(16,185,129,.28);border:2px solid rgba(255,255,255,.92);text-decoration:none;transition:transform .2s ease,box-shadow .2s ease,background .2s ease;position:relative;z-index:61;">
                        <svg viewBox="0 0 24 24" aria-hidden="true" style="width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round">
                            <path d="M12 3v10"/>
                            <path d="m8 11 4 4 4-4"/>
                            <path d="M5 17.5V19a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1.5"/>
                        </svg>
                    </a>
                @endif
                @if($hasPreview)
                    <a href="{{ $previewUrl }}" title="Önizle" aria-label="Önizle" style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:999px;background:#3b82f6;color:#fff;box-shadow:0 12px 28px rgba(59,130,246,.28);border:2px solid rgba(255,255,255,.92);text-decoration:none;transition:transform .2s ease,box-shadow .2s ease,background .2s ease;position:relative;z-index:61;">
                        <svg viewBox="0 0 24 24" aria-hidden="true" style="width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2.1;stroke-linecap:round;stroke-linejoin:round">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                            <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/>
                        </svg>
                    </a>
                @endif
            </div>

            <div class="absolute left-4 top-16 z-20 flex items-center gap-3 pointer-events-none">
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-white shadow-[0_10px_24px_rgba(15,23,42,.12)]">
                    <img src="{{ $logo }}" alt="logo" class="h-10 w-10 object-contain">
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-4 p-5 pt-4">
        <div class="flex items-start justify-between gap-4">
            <h4 class="text-[32px] font-black leading-tight tracking-tight text-slate-900">{{ $title }}</h4>
            <span class="inline-flex shrink-0 items-center rounded-full px-4 py-2 text-sm font-semibold shadow-sm" style="{{ $difficultyStyle }}">{{ $difficultyValue !== '' ? $difficultyValue : 'Kolay' }}</span>
        </div>

        <p class="min-h-[5rem] text-[17px] leading-8 text-slate-600">{{ $normalizedDescription }}</p>

        @php
            $btnCount = 3 + (!empty($deleteUrl) ? 1 : 0) + ($assignEnabled ? 1 : 0);
            $btnCols = max(3, min(4, $btnCount));
        @endphp
        <div class="mt-auto grid gap-2.5" style="grid-template-columns:repeat({{ $btnCols }},minmax(0,1fr));">
            <a href="{{ $contentUrl }}" style="display:inline-flex;align-items:center;justify-content:center;height:50px;border-radius:999px;border:1px solid #7c3aed;background:#fff;color:#5b21b6;font-size:15px;font-weight:500;text-decoration:none;box-shadow:0 10px 20px rgba(15,23,42,.06);transition:transform .15s ease,filter .15s ease;">İçerik</a>
            <a href="{{ $primaryUrl }}" style="display:inline-flex;align-items:center;justify-content:center;height:50px;border-radius:999px;background:#5b21b6;color:#fff;font-size:15px;font-weight:500;text-decoration:none;box-shadow:0 12px 24px rgba(91,33,182,.18);transition:transform .15s ease,filter .15s ease;">{{ $primaryLabel }}</a>
            @if(!empty($deleteUrl))
                <a href="{{ $deleteUrl }}" class="course-delete-link" data-delete-url="{{ $deleteUrl }}" style="display:inline-flex;align-items:center;justify-content:center;height:50px;border-radius:999px;background:#ef4444;color:#fff;font-size:15px;font-weight:500;text-decoration:none;box-shadow:0 12px 24px rgba(239,68,68,.18);transition:transform .15s ease,filter .15s ease;">Dersi Sil</a>
            @endif
            @if($assignEnabled && !empty($assignCourseId))
                <button type="button" style="display:inline-flex;align-items:center;justify-content:center;height:50px;border-radius:999px;background:#f97316;color:#fff;font-size:15px;font-weight:500;text-decoration:none;border:0;box-shadow:0 12px 24px rgba(249,115,22,.18);cursor:pointer;transition:transform .15s ease,filter .15s ease;" data-assign-course-id="{{ $assignCourseId }}" data-assign-course-name="{{ $assignCourseName }}" data-assign-current-teacher="{{ (int) $assignCurrentTeacher }}">Dersi Ata</button>
            @endif
        </div>
    </div>
</article>
