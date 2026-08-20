@php
    $renderPlainText = static function ($value): string {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        return trim(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    };
    $buildCodeSrcdoc = static function (string $rawCode): string {
        $code = trim($rawCode);
        $decodedBase64 = base64_decode($code, true);
        if (is_string($decodedBase64) && $decodedBase64 !== '' && base64_encode($decodedBase64) === preg_replace('/\s+/', '', $code)) {
            $code = $decodedBase64;
        }
        $code = html_entity_decode($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
<div class="lesson-card">
    @if(!empty($slide['content']) && $codeSrcdoc === '')
        <p class="lesson-paragraph">{{ $renderPlainText($slide['content']) }}</p>
    @endif
    @if($codeSrcdoc !== '')
        <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
    @elseif(!empty($slide['content']))
        <p class="lesson-paragraph">{{ $renderPlainText($slide['content']) }}</p>
    @endif
</div>
