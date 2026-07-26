@php
    $meta = is_array($slide['layout_meta'] ?? null) ? $slide['layout_meta'] : [];
    $media = is_array($meta['media'] ?? null) ? $meta['media'] : [];
    $imageUrl = (string) ($media['image_url'] ?? $slide['image_url'] ?? '');
    $videoUrl = (string) ($media['video_url'] ?? $slide['video_url'] ?? '');
    $text = (string) ($media['text'] ?? $slide['content'] ?? '');
    $html = (string) ($media['html'] ?? '');
    $mediaOrder = (string) ($media['order'] ?? 'image-text');
@endphp

<div class="lesson-image-focus" style="display:flex;flex-direction:column;justify-content:flex-start;align-items:center;gap:18px;min-height:auto;width:100%;max-width:100%;overflow:hidden">
    @if($mediaOrder === 'text-image')
        @if($html !== '')
            <div class="lesson-paragraph" style="max-width:1100px;text-align:center">{!! $html !!}</div>
        @elseif($text !== '')
            <p class="lesson-paragraph" style="max-width:900px;text-align:center">{!! nl2br(e($text)) !!}</p>
        @endif
    @endif

    @if($imageUrl !== '')
        <figure class="lesson-media-figure" style="width:100%;max-width:min(100%,1380px);min-width:0;margin:0 auto;display:grid;gap:10px;justify-items:center">
            <div style="width:min(100%,50%);aspect-ratio:16/6;overflow:hidden;border-radius:24px;border:1px solid #dbe5f2;background:#fff;box-shadow:0 18px 40px rgba(15,23,42,.08)">
                <img src="{{ $imageUrl }}" alt="slide görsel" class="lesson-image" style="width:100%;height:100%;object-fit:cover;display:block">
            </div>
            @if($slide['instructions'] ?? false)
                <figcaption style="font-size:14px;line-height:1.6;color:#475569;text-align:center">{{ $slide['instructions'] }}</figcaption>
            @endif
        </figure>
    @endif

    @if($videoUrl !== '')
        <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" src="{{ $videoUrl }}" style="width:min(100%,960px);min-height:360px;border-radius:18px"></iframe>
    @endif

    @if($mediaOrder !== 'text-image')
        @if($html !== '')
            <div class="lesson-paragraph" style="max-width:1100px;text-align:center">{!! $html !!}</div>
        @elseif($text !== '')
            <p class="lesson-paragraph" style="max-width:900px;text-align:center">{!! nl2br(e($text)) !!}</p>
        @endif
    @endif
</div>

<style>
@media (max-width:768px){
    .lesson-image-focus{
        min-height:auto !important;
        gap:12px !important;
        align-items:stretch !important;
        width:100% !important;
        max-width:100% !important;
        overflow:hidden !important;
    }
    .lesson-image-focus figure{
        width:100% !important;
        min-width:0 !important;
        max-width:100% !important;
    }
    .lesson-image-focus figure > div{
        width:100% !important;
        aspect-ratio:16/9 !important;
    }
    .lesson-image-focus img,
    .lesson-image-focus iframe{
        width:100% !important;
        max-width:100% !important;
    }
    .lesson-paragraph{
        max-width:100% !important;
        word-break:break-word;
        overflow-wrap:anywhere;
        hyphens:auto;
    }
}
</style>
