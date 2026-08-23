@props([
    'title' => '',
    'description' => '',
    'image' => '',
    'logo' => '',
    'age' => '11+',
    'difficulty' => 'Orta',
    'primaryUrl' => '#',
    'primaryLabel' => 'Derse Başla',
    'primaryVariant' => 'default',
    'deleteUrl' => null,
    'subCourseUrl' => null,
    'assignEnabled' => false,
    'assignCourseId' => null,
    'assignCourseName' => '',
    'assignCurrentTeacher' => 0,
    'assignCurrentClass' => 0,
    'isFavorite' => false,
    'downloadUrl' => null,
    'creatorLabel' => '',
])

@php
    $utf8 = \App\Support\Utf8Text::class;
    $normalizeText = static function ($value): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Only re-encode if the string is NOT already valid UTF-8.
        // mb_convert_encoding on valid UTF-8 strings corrupts Turkish chars (ı→ı, ş→Å, etc.)
        if (! mb_check_encoding($decoded, 'UTF-8')) {
            $converted = @mb_convert_encoding($decoded, 'UTF-8', 'Windows-1254');
            $decoded = (is_string($converted) && $converted !== '') ? $converted : $decoded;
        }

        return trim(strip_tags($decoded));
    };

    $hasCover = filled($image);
    $safeTitle = $normalizeText($utf8::normalize($title));
    $normalizedDescription = $normalizeText($utf8::normalize(str_replace(["\r\n", "\n", "\r"], "\n", (string) $description)));
    $difficultyValue = $normalizeText($utf8::normalize($difficulty));
    $primaryLabelValue = $normalizeText($utf8::normalize($primaryLabel));
    $creatorLabelValue = $normalizeText($utf8::normalize($creatorLabel));
    $confirmMessage = $normalizeText($utf8::normalize('Bu dersi silmek istediğinize emin misiniz?'));
    $difficultyStyle = match (mb_strtolower($difficultyValue)) {
        'kolay' => 'background:#16a34a;color:#fff;',
        'orta' => 'background:#2563eb;color:#fff;',
        'zor' => 'background:#ef4444;color:#fff;',
        default => 'background:#6d28d9;color:#fff;',
    };
    $launchUrl = filled($primaryUrl) && $primaryUrl !== '#' ? $primaryUrl : '#';
@endphp

<article class="group relative flex h-full w-full min-w-0 flex-col overflow-hidden bg-white shadow-[0_16px_42px_rgba(15,23,42,.11)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_60px_rgba(91,33,182,.16)]" style="box-sizing:border-box;border:1.5px solid rgba(124,58,237,.18);border-radius:24px;box-shadow:0 16px 42px rgba(15,23,42,.11), 0 0 0 1px rgba(167,139,250,.12) inset;">
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
            </div>

            <div class="absolute left-4 top-14 z-30 flex h-full items-start gap-3 pointer-events-none">
                <div class="course-card-logo-accent"></div>
                <div class="course-card-logo-shell">
                    <div class="course-card-logo-inner">
                <img src="{{ $logo }}" alt="logo" class="h-[42px] w-[42px] object-contain">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-4 p-5 pt-4">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h4 class="course-card-title text-[17px] md:text-[18px] font-bold leading-snug tracking-tight text-slate-900">{{ $safeTitle }}</h4>
                @if(trim((string) $creatorLabelValue) !== '')
                    <p class="mt-2 text-[12px] font-semibold uppercase tracking-[0.12em] text-slate-500">Yükleyen: {{ $creatorLabelValue }}</p>
                @endif
                @if($normalizedDescription !== '')
                    <p class="course-card-description mt-2 text-[14px] leading-6 text-slate-600">{{ $normalizedDescription }}</p>
                @else
                    <p class="course-card-description mt-2 text-[14px] leading-6 text-slate-600"> </p>
                @endif
            </div>
            <span class="inline-flex shrink-0 items-center rounded-full px-4 py-2 text-sm font-semibold shadow-sm" style="{{ $difficultyStyle }}">{{ $difficultyValue !== '' ? $difficultyValue : 'Kolay' }}</span>
        </div>

        @php
            $visibleButtons = 1 + (!empty($subCourseUrl) ? 1 : 0) + (!empty($deleteUrl) ? 1 : 0);
            $btnCols = max(1, min(4, $visibleButtons));
        @endphp
        <div class="mt-auto grid gap-2.5" style="grid-template-columns:repeat({{ $btnCols }},minmax(0,1fr));">
            <a href="{{ $launchUrl }}" class="course-card-action course-card-action--launch">{{ $primaryLabelValue }}</a>
            @if(!empty($subCourseUrl))
                <a href="{{ $subCourseUrl }}" class="course-card-action course-card-action--sub">Alt Ders Oluştur</a>
            @endif
            @if(!empty($deleteUrl))
                <a href="{{ $deleteUrl }}" class="course-card-action course-card-action--delete course-delete-link" data-delete-url="{{ $deleteUrl }}" data-confirm="{{ $confirmMessage }}">Dersi Sil</a>
            @endif
        </div>
    </div>
</article>

<style>
    .course-card-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: transform .15s ease, filter .15s ease, background .15s ease, color .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .course-card-action--launch {
        border: 1px solid #f59e0b;
        background: #f59e0b;
        color: #fff;
        box-shadow: 0 12px 24px rgba(245,158,11,.18);
    }
    .course-card-action--launch:hover {
        filter: brightness(.96);
        box-shadow: 0 16px 28px rgba(245,158,11,.24);
        transform: translateY(-1px);
    }
    .course-card-action--sub {
        background: #0f766e;
        color: #fff;
        box-shadow: 0 12px 24px rgba(15,118,110,.18);
        font-size: 12px;
        line-height: 1.15;
        white-space: normal;
        padding: 0 10px;
        min-height: 56px;
        text-align: center;
        word-break: break-word;
    }
    .course-card-action--sub:hover {
        filter: brightness(.96);
        box-shadow: 0 16px 28px rgba(15,118,110,.24);
        transform: translateY(-1px);
    }
    .course-card-action--delete {
        background: #ef4444;
        color: #fff;
        box-shadow: 0 12px 24px rgba(239,68,68,.18);
    }
    .course-card-action--delete:hover {
        filter: brightness(.96);
        box-shadow: 0 16px 28px rgba(239,68,68,.24);
        transform: translateY(-1px);
    }

    .course-delete-link {
        cursor: pointer;
    }

    .course-card-description {
        min-height: calc(1.5rem * 2);
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .course-card-title {
        min-height: calc(1.375rem * 2);
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .course-card-logo-accent {
        position: absolute;
        left: -70px;
        top: 50%;
        width: 132px;
        height: 100%;
        transform: translateY(-50%) skewX(-18deg);
        border-radius: 12px;
        background: linear-gradient(180deg, #7c3aed 0%, #5b21b6 52%, #4c1d95 100%);
        box-shadow: 0 18px 36px rgba(76,29,149,.34);
        z-index: 1;
        opacity: .91;
    }

    .course-card-logo-accent::before {
        content: none;
    }

    .course-card-logo-shell {
        position: relative;
        z-index: 5;
        display: flex;
        width: 69px;
        height: 69px;
        align-items: center;
        justify-content: center;
        overflow: visible;
        transform: translateY(15px);
    }

    .course-card-logo-inner {
        position: relative;
        z-index: 6;
        display: flex;
        width: 65px;
        height: 65px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15,23,42,.12);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.course-delete-link').forEach((link) => {
        if (link.dataset.bound === '1') return;
        link.dataset.bound = '1';
        link.addEventListener('click', async (event) => {
            event.preventDefault();
            const message = link.dataset.confirm || 'Bu dersi silmek istediğinize emin misiniz?';
            const ok = window.AppDialog && typeof window.AppDialog.confirm === 'function'
                ? await window.AppDialog.confirm(message)
                : window.confirm(message);
            if (ok) {
                window.location.href = link.href;
            }
        });
    });
});
</script>
