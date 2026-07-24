@php
    $meta = is_array($slide['layout_meta'] ?? null) ? $slide['layout_meta'] : [];
    $media = is_array($meta['media'] ?? null) ? $meta['media'] : [];
    $content = (string) ($media['text'] ?? $slide['content'] ?? 'Bu slaytta ana konu öne çıkarılır.');
    $imageUrl = (string) ($media['image_url'] ?? $slide['image_url'] ?? '');
@endphp

<div class="lesson-hero-card">
    @if($imageUrl !== '')
        <img src="{{ $imageUrl }}" alt="hero görsel" class="lesson-image" style="margin-bottom:12px">
    @endif
    <p class="lesson-paragraph">{!! nl2br(e($content)) !!}</p>
</div>
