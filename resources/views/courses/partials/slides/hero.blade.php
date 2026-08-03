@php
    $meta = is_array($slide['layout_meta'] ?? null) ? $slide['layout_meta'] : [];
    $media = is_array($meta['media'] ?? null) ? $meta['media'] : [];
    $content = (string) ($media['text'] ?? $slide['content'] ?? 'Bu slaytta ana konu öne çıkarılır.');
    $imageUrl = (string) ($media['image_url'] ?? $slide['image_url'] ?? '');
    $renderRichText = static function ($value): string {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        return html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
@endphp

<div class="lesson-hero-card">
    @if($imageUrl !== '')
        <img src="{{ $imageUrl }}" alt="hero görsel" class="lesson-image" style="margin-bottom:12px">
    @endif
    <div class="lesson-paragraph lesson-rich-text">{!! $renderRichText($content) !!}</div>
</div>
