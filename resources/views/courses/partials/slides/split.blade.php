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
@endphp

<div class="lesson-split" style="display:grid;grid-template-columns:{{ $splitColumns }};gap:18px">
    <div class="lesson-card lesson-split-card">
        <div class="lesson-split-body">
            @if($leftType === 'image' && $leftImage !== '')
                <img src="{{ $leftImage }}" alt="slide görsel" class="lesson-image lesson-split-media">
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
    </div>

    <div class="lesson-card lesson-split-card">
        <div class="lesson-split-body lesson-split-body--center">
            @if($rightType === 'image' && $rightImage !== '')
                <img src="{{ $rightImage }}" alt="slide görsel" class="lesson-image lesson-split-media">
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
</div>
