@php
    $meta = is_array($slide['layout_meta'] ?? null) ? $slide['layout_meta'] : [];
    $left = is_array($meta['left'] ?? null) ? $meta['left'] : [];
    $right = is_array($meta['right'] ?? null) ? $meta['right'] : [];

    $normalizeMediaUrl = static function (string $url): string {
        $raw = trim($url);
        if ($raw === '') {
            return '';
        }
        if (preg_match('#^(?:data:|blob:|https?://)#i', $raw)) {
            return $raw;
        }
        $raw = str_replace('\\', '/', $raw);
        $raw = ltrim($raw, '/');
        $raw = preg_replace('#^public/#i', '', $raw) ?? $raw;
        $raw = preg_replace('#^storage/app/public/#i', '', $raw) ?? $raw;
        $raw = preg_replace('#^storage/#i', '', $raw) ?? $raw;
        if (str_starts_with($raw, 'course-covers/')) {
            $raw = 'kapak-gorseli/' . substr($raw, strlen('course-covers/'));
        }
        if (str_starts_with($raw, 'kapak-gorseli/')) {
            return asset($raw);
        }
        return asset(ltrim($raw, '/'));
    };

    $leftType = (string) ($left['type'] ?? 'text');
    $rightType = (string) ($right['type'] ?? 'image');

    $leftText = (string) ($left['text'] ?? $slide['content'] ?? '');
    $rightText = (string) ($right['text'] ?? '');
    $leftImage = $normalizeMediaUrl((string) ($left['image_url'] ?? $slide['image_url'] ?? ''));
    $rightImage = $normalizeMediaUrl((string) ($right['image_url'] ?? ''));
    $leftVideo = (string) ($left['video_url'] ?? '');
    $rightVideo = (string) ($right['video_url'] ?? '');
    $splitRatio = (string) ($meta['split_ratio'] ?? '50-50');
    $splitColumns = $splitRatio === '30-70' ? '30% 70%' : ($splitRatio === '70-30' ? '70% 30%' : '1fr 1fr');
    $renderRichText = static function ($value): string {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        return html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $buildCodeSrcdoc = static function (string $rawCode): string {
        $code = html_entity_decode(trim($rawCode), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($code === '') {
            return '';
        }
        $hasHtmlShell = (bool) preg_match('/<\s*(?:html|head|body|!doctype)\b/i', $code);
        $helper = '<meta name="viewport" content="width=device-width, initial-scale=1"><style>html,body{margin:0;padding:0;min-height:100%;width:100%;overflow:auto;background:#fff}img,video,canvas,svg,table,pre,code{max-width:100%}</style>';
        if ($hasHtmlShell) {
            return $helper . $code;
        }
        return '<!doctype html><html><head>' . $helper . '</head><body>' . $code . '</body></html>';
    };
    $codeSrcdoc = $buildCodeSrcdoc((string) ($slide['code'] ?? ''));
@endphp

<div class="lesson-split" style="display:grid;grid-template-columns:{{ $splitColumns }};gap:18px">
    @if($codeSrcdoc !== '')
        <div class="lesson-card" style="grid-column:1 / -1">
            <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
        </div>
    @endif
    <div class="lesson-card lesson-split-card">
        <div class="lesson-split-body">
            @if($leftType === 'image' && $leftImage !== '')
                <div class="lesson-split-media-wrap">
                    <button
                        type="button"
                        class="lesson-image-zoom-trigger"
                        data-image-preview-src="{{ $leftImage }}"
                        aria-label="Görseli büyüt"
                    >
                        <img src="{{ $leftImage }}" alt="slide görsel" class="lesson-image lesson-split-media">
                    </button>
                </div>
            @elseif($leftType === 'video' && $leftVideo !== '')
                <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" src="{{ $leftVideo }}"></iframe>
            @elseif($leftType === 'code' && $codeSrcdoc !== '')
                <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
            @else
                @if($leftText !== '')
                    <div class="lesson-paragraph lesson-rich-text">{!! $renderRichText($leftText) !!}</div>
                @endif
            @endif
        </div>
    </div>

    <div class="lesson-card lesson-split-card">
        <div class="lesson-split-body lesson-split-body--center">
            @if($rightType === 'image' && $rightImage !== '')
                <div class="lesson-split-media-wrap">
                    <button
                        type="button"
                        class="lesson-image-zoom-trigger"
                        data-image-preview-src="{{ $rightImage }}"
                        aria-label="Görseli büyüt"
                    >
                        <img src="{{ $rightImage }}" alt="slide görsel" class="lesson-image lesson-split-media">
                    </button>
                </div>
            @elseif($rightType === 'video' && $rightVideo !== '')
                <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" src="{{ $rightVideo }}"></iframe>
            @elseif($rightType === 'code' && $codeSrcdoc !== '')
                <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
            @else
                @if($rightText !== '')
                    <div class="lesson-paragraph lesson-rich-text">{!! $renderRichText($rightText) !!}</div>
                @endif
            @endif
            @if($rightType !== 'code' && !empty($slide['file_url']))
                <a class="lesson-link" href="{{ $slide['file_url'] }}" target="_blank">Ek Kaynak</a>
            @endif
        </div>
    </div>
</div>

<div id="lesson-image-preview-modal" class="lesson-image-preview-modal" aria-hidden="true">
    <button type="button" class="lesson-image-preview-backdrop" data-close-image-preview aria-label="Önizlemeyi kapat"></button>
    <div class="lesson-image-preview-dialog" role="dialog" aria-modal="true">
        <button type="button" class="lesson-image-preview-close" data-close-image-preview aria-label="Önizlemeyi kapat">×</button>
        <img id="lesson-image-preview-modal-img" alt="Büyütülmüş görsel" class="lesson-image-preview-img" loading="eager" decoding="async">
    </div>
</div>

<style>
.lesson-image-zoom-trigger{
    all:unset;
    cursor:zoom-in;
    display:block;
    width:100%;
    height:100%;
    max-width:100%;
}
@media (max-width:768px){
    .lesson-split{
        grid-template-columns:1fr !important;
        gap:12px !important;
    }
    .lesson-split-card{
        width:100%;
        min-width:0;
    }
    .lesson-split-body{
        width:100%;
        min-width:0;
    }
    .lesson-split-media-wrap{
        width:100%;
        aspect-ratio:16 / 9;
        display:flex;
        align-items:flex-start;
        justify-content:center;
        overflow:hidden;
        background:#fff;
        border-radius:16px;
    }
    .lesson-split-media{
        width:100% !important;
        max-width:100% !important;
        height:100% !important;
        object-fit:contain !important;
        object-position:top center !important;
        display:block;
        background:#fff;
        border-radius:16px;
    }
    .lesson-paragraph{
        max-width:100% !important;
        word-break:break-word;
        overflow-wrap:anywhere;
        hyphens:auto;
    }
}
.lesson-image-preview-modal{
    display:none;
    position:fixed;
    inset:0;
    z-index:9999;
    align-items:center;
    justify-content:center;
    padding:18px;
    background:rgba(2,6,23,.78);
}
.lesson-image-preview-backdrop{
    position:absolute;
    inset:0;
    border:0;
    background:transparent;
    cursor:zoom-out;
}
.lesson-image-preview-dialog{
    position:relative;
    z-index:1;
    max-width:min(96vw,1400px);
    max-height:92vh;
    display:grid;
    place-items:center;
}
.lesson-image-preview-close{
    position:absolute;
    top:-14px;
    right:-14px;
    width:38px;
    height:38px;
    border:0;
    border-radius:9999px;
    background:#fff;
    color:#0f172a;
    font-size:24px;
    line-height:1;
    font-weight:800;
    cursor:pointer;
    box-shadow:0 12px 28px rgba(15,23,42,.24);
}
.lesson-image-preview-img{
    width:min(92vw,1200px);
    max-width:92vw;
    max-height:88vh;
    object-fit:contain;
    display:block;
    border-radius:18px;
    background:#fff;
    box-shadow:0 24px 60px rgba(0,0,0,.38);
}
</style>

<script>
(function () {
    const modal = document.getElementById('lesson-image-preview-modal');
    const modalImg = document.getElementById('lesson-image-preview-modal-img');
    if (!modal || !modalImg) return;

    window.openLessonImagePreview = function (src) {
        if (!src) return;
        modalImg.src = src;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    };

    window.closeLessonImagePreview = function () {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modalImg.removeAttribute('src');
    };

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest ? event.target.closest('[data-image-preview-src]') : null;
        if (trigger) {
            event.preventDefault();
            window.openLessonImagePreview(trigger.getAttribute('data-image-preview-src'));
            return;
        }
        if (event.target.closest && event.target.closest('[data-close-image-preview]')) {
            event.preventDefault();
            window.closeLessonImagePreview();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.style.display === 'flex') {
            window.closeLessonImagePreview();
        }
    });
})();
</script>
</style>
