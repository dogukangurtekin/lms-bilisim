@props([
    'title' => '',
    'description' => '',
    'image' => '',
    'logo' => '',
    'age' => '11+',
    'difficulty' => 'Orta',
    'contentUrl' => '#',
    'primaryUrl' => '#',
    'primaryLabel' => 'Derse Basla',
    'deleteUrl' => null,
    'assignEnabled' => false,
    'assignCourseId' => null,
    'assignCourseName' => '',
    'assignCurrentTeacher' => 0,
    'isFavorite' => false,
    'downloadUrl' => null,
])

<article class="group flex h-full w-full max-w-none flex-col rounded-2xl bg-white p-4 shadow-lg transition duration-300 hover:scale-[1.015] hover:shadow-2xl">
    <div class="relative overflow-hidden rounded-xl" style="height:14rem;background:linear-gradient(180deg,#f8fafc 0%,#eef2ff 100%);">
        @if(!empty($downloadUrl))
            <a href="{{ $downloadUrl }}" title="Dersi indir" aria-label="Dersi indir"
               style="position:absolute;right:10px;top:10px;z-index:30;width:38px;height:38px;border-radius:9999px;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 8px 18px rgba(2,6,23,.28);">
                <span style="font-size:18px;line-height:1;">&#8681;</span>
            </a>
        @endif
        @if(!empty($image))
            <img
                src="{{ $image }}"
                alt="{{ $title }}"
                loading="eager"
                decoding="async"
                onerror="this.style.display='none';"
                style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top;display:block;z-index:1;"
            >
            <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(15,23,42,.12) 0%,rgba(15,23,42,.02) 24%,rgba(255,255,255,0) 48%);z-index:2;pointer-events:none;"></div>
            <div style="position:absolute;left:18px;top:18px;z-index:20;width:64px;height:64px;display:flex;align-items:center;justify-content:center;border-radius:9999px;background:#fff;box-shadow:0 8px 20px rgba(15,23,42,.16);overflow:hidden;">
                <img src="{{ $logo }}" alt="logo" class="h-10 w-10 rounded-full object-contain" style="width:40px;height:40px;max-width:40px;max-height:40px;object-fit:contain;display:block;">
            </div>
        @else
            <div class="flex h-56 w-full items-center justify-center bg-gray-100 text-sm font-semibold text-gray-400">
                Kapak Gorseli Yok
            </div>
        @endif
    </div>

    <div class="mt-5 flex flex-1 flex-col">
        <div class="flex items-start justify-between gap-3">
            <h4 class="h-[78px] overflow-hidden break-words text-xl font-bold leading-9 text-gray-900">{{ $title }}</h4>
            <div class="shrink-0">
                <span class="inline-flex items-center rounded-full bg-purple-700 px-3 py-1 text-sm font-bold text-white">{{ $difficulty }}</span>
            </div>
        </div>

        @php
            $normalizedDescription = str_replace(["\\r\\n", "\\n", "\\r"], "\n", (string) $description);
        @endphp
        <p class="mt-3 h-[96px] min-h-[96px] max-h-[96px] w-full flex-none overflow-hidden break-words whitespace-pre-line text-base leading-8 text-gray-600 line-clamp-3">
            {{ $normalizedDescription }}
        </p>

        @php
            $btnCount = 2 + (!empty($deleteUrl) ? 1 : 0) + ($assignEnabled ? 1 : 0);
            $btnCols = max(2, min(4, $btnCount));
        @endphp
        <div class="mt-auto pt-4" style="display:grid;grid-template-columns:repeat({{ $btnCols }},minmax(0,1fr));gap:12px;">
            <a href="{{ $contentUrl }}" class="inline-flex h-12 flex-1 items-center justify-center rounded-xl border border-[#4c1d95] bg-white px-4 text-base font-semibold text-[#4c1d95] transition hover:bg-violet-50">
                Icerik
            </a>

            <a href="{{ $primaryUrl }}" class="inline-flex h-12 flex-1 items-center justify-center rounded-xl bg-[#4c1d95] px-4 text-base font-semibold text-white transition hover:bg-[#3b0764]">
                {{ $primaryLabel }}
            </a>

            @if(!empty($deleteUrl))
                        <a
                            href="{{ $deleteUrl }}"
                            class="course-delete-link"
                            data-delete-url="{{ $deleteUrl }}"
                            onmouseenter="this.style.backgroundColor='#b91c1c'"
                            onmouseleave="this.style.backgroundColor='#dc2626'"
                            style="display:inline-flex!important;width:100%;height:48px;align-items:center;justify-content:center;border:2px solid #b91c1c!important;border-radius:12px;background-color:#dc2626!important;color:#fff!important;font-size:16px;font-weight:600;cursor:pointer;text-decoration:none!important;transition:background-color .15s ease,transform .15s ease;box-shadow:0 8px 18px rgba(220,38,38,.28);position:relative;z-index:10;"
                        >
                            Dersi Sil
                        </a>
                    @endif

            @if($assignEnabled && !empty($assignCourseId))
                <button
                    type="button"
                    style="display:inline-flex !important;visibility:visible !important;opacity:1 !important;height:48px;min-height:48px;width:100%;align-items:center;justify-content:center;border:0;border-radius:12px;background:#f97316;color:#fff;font-size:16px;font-weight:700;cursor:pointer;"
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
