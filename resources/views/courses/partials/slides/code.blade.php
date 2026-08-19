@php
    $renderPlainText = static function ($value): string {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        return trim(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    };
@endphp
<div class="lesson-card">
    @if(!empty($slide['content']))
        <p class="lesson-paragraph">{{ $renderPlainText($slide['content']) }}</p>
    @endif
    @if($codeSrcdoc !== '')
        <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ e($codeSrcdoc) }}"></iframe>
    @endif
</div>
