@php
    $meta = is_array($slide['layout_meta'] ?? null) ? $slide['layout_meta'] : [];
    $media = is_array($meta['media'] ?? null) ? $meta['media'] : [];
    $renderRichText = static function ($value): string {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        return html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $renderPlainText = static function ($value): string {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        return trim(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    };
    $imageUrl = (string) ($media['image_url'] ?? $slide['image_url'] ?? '');
    $videoUrl = (string) ($media['video_url'] ?? $slide['video_url'] ?? '');
    $text = (string) ($media['text'] ?? $slide['content'] ?? '');
    $html = (string) ($media['html'] ?? '');
    $mediaOrder = (string) ($media['order'] ?? 'image-text');
@endphp

<div class="lesson-image-focus">
    @if(!empty($codeSrcdoc))
        <div class="lesson-code-block" style="width:100%;max-width:1100px">
            <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
        </div>
    @endif
    @if($mediaOrder === 'text-image')
        @if($html !== '')
            <div class="lesson-paragraph lesson-paragraph--compact">{!! $renderRichText($html) !!}</div>
        @elseif($text !== '')
            <div class="lesson-paragraph lesson-paragraph--compact lesson-rich-text">{!! $renderRichText($text) !!}</div>
        @endif
    @endif

    @if($imageUrl !== '')
        <figure class="lesson-media-figure">
            <button
                type="button"
                class="lesson-image-zoom-trigger"
                data-image-preview-src="{{ $imageUrl }}"
                onclick="window.openLessonImagePreview && window.openLessonImagePreview(this.getAttribute('data-image-preview-src'))"
                aria-label="Görseli büyüt"
            >
                <img src="{{ $imageUrl }}" alt="slide görsel" class="lesson-image lesson-image--compact">
            </button>
        </figure>
    @endif

    @if($videoUrl !== '')
        <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" src="{{ $videoUrl }}"></iframe>
    @endif

    @if($mediaOrder !== 'text-image')
        @if($html !== '')
            <div class="lesson-paragraph lesson-paragraph--compact">{!! $renderRichText($html) !!}</div>
        @elseif($text !== '')
            <div class="lesson-paragraph lesson-paragraph--compact lesson-rich-text">{!! $renderRichText($text) !!}</div>
        @endif
    @endif
</div>

<div id="lesson-image-preview-modal" class="lesson-image-preview-modal" aria-hidden="true">
    <button type="button" class="lesson-image-preview-backdrop" data-close-image-preview aria-label="Önizlemeyi kapat"></button>
    <div class="lesson-image-preview-dialog" role="dialog" aria-modal="true">
        <button type="button" class="lesson-image-preview-close" data-close-image-preview aria-label="Önizlemeyi kapat">×</button>
        <img id="lesson-image-preview-modal-img" alt="Büyütülmüş görsel" class="lesson-image-preview-img" loading="eager" decoding="async">
    </div>
</div>

<style>
.lesson-image-focus{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:flex-start;
    gap:8px;
    width:100%;
    max-width:100%;
    min-height:auto;
    overflow:hidden;
}
.lesson-media-figure{
    width:100%;
    max-width:100%;
    aspect-ratio:16 / 9;
    margin:0 auto;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    background:#fff;
    border-radius:16px;
}
.lesson-image-zoom-trigger{
    all:unset;
    cursor:zoom-in;
    display:block;
    max-width:100%;
}
.lesson-image--compact{
    width:100%;
    height:100%;
    object-fit:contain;
    object-position:top center;
    display:block;
    background:#fff;
    max-width:100%;
    max-height:100%;
}
.lesson-paragraph--compact{
    max-width:1100px;
    text-align:center;
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
@media (max-width:768px){
    .lesson-image-focus{
        gap:6px !important;
        align-items:stretch !important;
        width:100% !important;
        max-width:100% !important;
    }
    .lesson-media-figure{
        width:100% !important;
        min-width:0 !important;
        max-width:100% !important;
        justify-content:center !important;
    }
    .lesson-image--compact{
        width:100% !important;
        height:100% !important;
        max-width:100% !important;
        max-height:100% !important;
        object-fit:contain !important;
        object-position:top center !important;
    }
    .lesson-image-preview-dialog{
        max-width:96vw;
        width:96vw;
    }
    .lesson-image-preview-img{
        max-width:96vw;
        max-height:82vh;
    }
    .lesson-paragraph--compact{
        max-width:100% !important;
        word-break:break-word;
        overflow-wrap:anywhere;
        hyphens:auto;
    }
}
</style>

<script>
(function () {
    const modal = document.getElementById('lesson-image-preview-modal');
    const modalImg = document.getElementById('lesson-image-preview-modal-img');
    if (!modal || !modalImg) return;

    window.openLessonImagePreview = (src) => {
        if (!src) return;
        modalImg.src = src;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    };

    window.closeLessonImagePreview = () => {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modalImg.removeAttribute('src');
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest?.('[data-image-preview-src]');
        if (trigger) {
            event.preventDefault();
            window.openLessonImagePreview(trigger.getAttribute('data-image-preview-src'));
            return;
        }
        if (event.target.closest?.('[data-close-image-preview]')) {
            event.preventDefault();
            window.closeLessonImagePreview();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.style.display === 'flex') {
            window.closeLessonImagePreview();
        }
    });
})();
</script>
