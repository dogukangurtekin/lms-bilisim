@php
    $meta = is_array($slide['layout_meta'] ?? null) ? $slide['layout_meta'] : [];
    $media = is_array($meta['media'] ?? null) ? $meta['media'] : [];
    $imageUrl = (string) ($media['image_url'] ?? $slide['image_url'] ?? '');
    $videoUrl = (string) ($media['video_url'] ?? $slide['video_url'] ?? '');
    $text = (string) ($media['text'] ?? $slide['content'] ?? '');
@endphp

@if($imageUrl !== '')
    <figure class="lesson-media-figure">
        <img src="{{ $imageUrl }}" alt="slide görsel" class="lesson-image">
        @if($slide['instructions'] ?? false)
            <figcaption>{{ $slide['instructions'] }}</figcaption>
        @endif
    </figure>
@endif
@if($videoUrl !== '')
    <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" src="{{ $videoUrl }}"></iframe>
@endif
@if($text !== '')
    <p class="lesson-paragraph">{!! nl2br(e($text)) !!}</p>
@endif
