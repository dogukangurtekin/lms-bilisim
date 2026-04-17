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
    'isFavorite' => false,
])

<article class="group flex h-full w-full max-w-none flex-col rounded-2xl bg-white p-4 shadow-lg transition duration-300 hover:scale-[1.015] hover:shadow-2xl">
    <div class="relative overflow-hidden rounded-xl">
        @if(!empty($image))
            <img src="{{ $image }}" alt="{{ $title }}" class="h-56 w-full bg-gray-100 object-contain">
            <div style="position:absolute;left:0;top:0;bottom:0;width:120px;background:#4c1d95;z-index:10;clip-path:polygon(0 0,100% 0,58% 100%,0 100%);"></div>
            <div style="position:absolute;left:24px;top:24px;z-index:20;" class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-md">
                <img src="{{ $logo }}" alt="logo" class="h-10 w-10 rounded-full object-contain">
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

        <div class="mt-auto pt-4" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
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
                    style="display:inline-flex;width:100%;height:48px;align-items:center;justify-content:center;border:0;border-radius:12px;background:#dc2626;color:#fff;font-size:16px;font-weight:700;cursor:pointer;text-decoration:none;transition:background-color .15s ease;"
                >
                    Dersi Sil
                </a>
            @endif
        </div>
    </div>
</article>
