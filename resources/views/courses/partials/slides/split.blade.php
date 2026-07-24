@php
    $meta = is_array($slide['layout_meta'] ?? null) ? $slide['layout_meta'] : [];
    $left = is_array($meta['left'] ?? null) ? $meta['left'] : [];
    $right = is_array($meta['right'] ?? null) ? $meta['right'] : [];
    $leftType = (string) ($left['type'] ?? 'text');
    $rightType = (string) ($right['type'] ?? 'image');
    $leftText = (string) ($left['text'] ?? $slide['content'] ?? '');
    $rightText = (string) ($right['text'] ?? '');
    $leftImage = (string) ($left['image_url'] ?? $slide['image_url'] ?? '');
    $rightImage = (string) ($right['image_url'] ?? '');
    $leftVideo = (string) ($left['video_url'] ?? '');
    $rightVideo = (string) ($right['video_url'] ?? '');
@endphp

<div class="lesson-split">
    <div class="lesson-card">
        @if($leftType === 'image' && $leftImage !== '')
            <img src="{{ $leftImage }}" alt="slide görsel" class="lesson-image">
        @elseif($leftType === 'video' && $leftVideo !== '')
            <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" src="{{ $leftVideo }}"></iframe>
        @elseif($leftType === 'code' && $codeSrcdoc !== '')
            <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
        @else
            @if($leftText !== '')
                <p class="lesson-paragraph">{!! nl2br(e($leftText)) !!}</p>
            @endif
        @endif
    </div>
    <div class="lesson-media-stack">
        @if($rightType === 'image' && $rightImage !== '')
            <img src="{{ $rightImage }}" alt="slide görsel" class="lesson-image">
        @elseif($rightType === 'video' && $rightVideo !== '')
            <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" src="{{ $rightVideo }}"></iframe>
        @elseif($rightType === 'code' && $codeSrcdoc !== '')
            <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
        @else
            @if($rightText !== '')
                <p class="lesson-paragraph">{!! nl2br(e($rightText)) !!}</p>
            @endif
        @endif
        @if($rightType !== 'code' && !empty($slide['file_url']))
            <a class="lesson-link" href="{{ $slide['file_url'] }}" target="_blank">Ek Kaynak</a>
        @endif
    </div>
</div>
