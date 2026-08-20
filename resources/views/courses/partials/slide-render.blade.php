@php
    $slide = $slide ?? [];
    $question = is_array($slide['question'] ?? null) ? $slide['question'] : [];
    $hideSlideTitle = $hideSlideTitle ?? false;
    $isSummarySlide = !empty($slide['__summary']);
    $layout = (string) ($slide['layout'] ?? ($isSummarySlide ? 'summary' : 'text'));
    $blocks = (array) ($slide['presentation_blocks'] ?? []);

    $normalizeText = static function ($value): string {
        $text = trim((string) $value);
        return $text === '' ? '' : html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
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
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return trim(strip_tags($decoded));
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
        $themeCss = trim((string) view('courses.partials.lesson-theme-css')->render());
        $responsiveHelper = <<<'HTML'
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
html,body{margin:0;padding:0;min-height:100%;width:100%;overflow:auto;background:#fff}
img,video,canvas,svg,table,pre,code{max-width:100%}
</style>
<script>
(function () {
  function fit() {
    var docEl = document.documentElement;
    var body = document.body;
    if (!docEl || !body) return;
    docEl.style.transform = '';
    docEl.style.transformOrigin = 'top left';
    docEl.style.width = '';
    var vw = window.innerWidth || 1;
    var contentWidth = Math.max(docEl.scrollWidth, body.scrollWidth, docEl.clientWidth, 1);
    var scale = 1;
    if (contentWidth > vw) scale = vw / contentWidth;
    else if (contentWidth < vw * 0.72) scale = Math.min(1.45, vw / contentWidth);
    if (!Number.isFinite(scale) || scale <= 0) scale = 1;
    if (Math.abs(scale - 1) > 0.01) {
      docEl.style.transform = 'scale(' + scale + ')';
      docEl.style.width = (100 / scale) + '%';
    }
  }
  window.addEventListener('load', fit);
  window.addEventListener('resize', fit);
  setTimeout(fit, 60);
  setTimeout(fit, 240);
})();
</script>
HTML;

        if ($hasHtmlShell) {
            return $responsiveHelper . ($themeCss !== '' ? '<style>' . $themeCss . '</style>' : '') . $code;
        }

        return '<!doctype html><html><head>' . $responsiveHelper . ($themeCss !== '' ? '<style>' . $themeCss . '</style>' : '') . '</head><body>' . $code . '</body></html>';
    };
    $pickValue = static function (array $source, array $keys) use ($normalizeText): string {
        foreach ($keys as $key) {
            $text = $normalizeText(data_get($source, $key));
            if ($text !== '') {
                return $text;
            }
        }
        return '';
    };
    $isTruthyCorrect = static function ($item): bool {
        if (!is_array($item)) {
            return false;
        }
        foreach (['correct', 'is_correct', 'answer_correct'] as $flagKey) {
            if (array_key_exists($flagKey, $item)) {
                return filter_var($item[$flagKey], FILTER_VALIDATE_BOOLEAN);
            }
        }
        return false;
    };
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
        return asset(ltrim($raw, '/'));
    };

    $slide['content'] = $pickValue($slide, ['content', 'text', 'body', 'description', 'lesson_content', 'lesson_text', 'markdown', 'html_content']);
    $slide['instructions'] = $pickValue($slide, ['instructions', 'instruction', 'note', 'guide', 'direction']);
    $slide['image_url'] = $normalizeMediaUrl($pickValue($slide, ['image_url', 'imageUrl', 'image', 'cover_image', 'media_url', 'mediaUrl']));
    $slide['video_url'] = $pickValue($slide, ['video_url', 'videoUrl', 'video', 'media_video', 'mediaVideo']);
    $slide['file_url'] = $pickValue($slide, ['file_url', 'fileUrl', 'file', 'attachment_url', 'attachmentUrl']);
    $background = is_array($slide['slide_background'] ?? null) ? (array) $slide['slide_background'] : [];
    if (empty($background) && is_array(data_get($slide, 'layout_meta.background', null))) {
        $background = (array) data_get($slide, 'layout_meta.background');
    }
    $backgroundType = (string) ($background['type'] ?? 'none');
    $backgroundValue = (string) ($background['value'] ?? '');
    $backgroundStyle = 'background:linear-gradient(180deg,#ffffff,#f8fbff);';
    if ($backgroundType !== 'none' && $backgroundValue !== '') {
        if ($backgroundType === 'color' || $backgroundType === 'gradient') {
            $backgroundStyle = 'background:' . $backgroundValue . ';';
        } elseif ($backgroundType === 'image') {
            $backgroundStyle = 'background-image:url("' . e($backgroundValue) . '");background-size:cover;background-position:center;background-repeat:no-repeat;';
        }
    }
    $suspiciousText = implode(' ', [
        (string) ($slide['title'] ?? ''),
        (string) ($slide['subtitle'] ?? ''),
        (string) ($slide['instructions'] ?? ''),
        (string) ($slide['content'] ?? ''),
        (string) ($slide['code'] ?? ''),
    ]);
    if (
        preg_match('/^\s*@php\b/i', $suspiciousText)
        || str_contains($suspiciousText, "@include('courses.partials.theme-css')")
        || str_contains($suspiciousText, "route('student.portal.courses')")
        || str_contains($suspiciousText, '$payload =')
        || str_contains($suspiciousText, '$finalSummarySlide')
        || str_contains($suspiciousText, 'lesson_total_xp')
        || str_contains($suspiciousText, '$slides[] = $finalSummarySlide')
    ) {
        $slide['title'] = 'Ders Slaytı';
        $slide['content'] = '';
        $slide['subtitle'] = '';
        $slide['instructions'] = '';
        // Kodlu slaytlarda kullanıcı kodunu koru; yanlış pozitif temizleme sadece ders şablonu metnine uygulanır.
        if ($layout !== 'code' && $layout !== 'interactive') {
            $slide['code'] = '';
        }
        $slide['image_url'] = '';
        $slide['video_url'] = '';
        $slide['file_url'] = '';
    } elseif ($slide['content'] !== '' && preg_match('/^\s*@php\b/i', $slide['content']) && (
        str_contains($slide['content'], '$payload') ||
        str_contains($slide['content'], '$finalSummarySlide') ||
        str_contains($slide['content'], '$curriculum') ||
        str_contains($slide['content'], '$slides[] = $finalSummarySlide')
    )) {
        $slide['content'] = '';
    }

    $responsiveHelper = <<<'HTML'
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
html,body{margin:0;padding:0;min-height:100%;width:100%;overflow:auto}
img,video,canvas,svg,table,pre,code{max-width:100%}
</style>
<script>
(function () {
  function fit() {
    var docEl = document.documentElement;
    var body = document.body;
    if (!docEl || !body) return;
    docEl.style.transform = '';
    docEl.style.transformOrigin = 'top left';
    docEl.style.width = '';
    var vw = window.innerWidth || 1;
    var contentWidth = Math.max(docEl.scrollWidth, body.scrollWidth, docEl.clientWidth, 1);
    var scale = 1;
    if (contentWidth > vw) scale = vw / contentWidth;
    else if (contentWidth < vw * 0.72) scale = Math.min(1.6, vw / contentWidth);
    if (Math.abs(scale - 1) > 0.01) {
      docEl.style.transform = 'scale(' + scale + ')';
      docEl.style.width = (100 / scale) + '%';
    }
  }
  window.addEventListener('load', fit);
  window.addEventListener('resize', fit);
  setTimeout(fit, 60);
})();
</script>
HTML;

    $codeSrcdoc = $buildCodeSrcdoc((string) ($slide['code'] ?? ''));
    $interactionType = (string) ($slide['interaction_type'] ?? 'none');
    if ($codeSrcdoc !== '' && $layout !== 'code' && $layout !== 'split' && $layout !== 'interactive') {
        $layout = 'code';
    }
@endphp
<style>
.slide-render{
    width:100%;
    max-width:100%;
    overflow:hidden;
}
.lesson-slide-shell{
    width:100%;
    max-width:100%;
    overflow:hidden;
}
.lesson-slide-shell,
.lesson-grid-cards,
.lesson-card,
.lesson-split,
.lesson-split-card,
.lesson-split-body,
.lesson-image-focus{
    min-width:0;
}
.lesson-layout-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;font-size:13px;font-weight:800;letter-spacing:.01em;background:rgba(37,99,235,.08);color:#1d4ed8;border:1px solid rgba(37,99,235,.14)}
.sqz-wrap{margin-top:10px;border-radius:18px;padding:14px;background:linear-gradient(160deg,#4c1d95,#6d28d9 42%,#7c3aed);color:#fff;border:1px solid rgba(255,255,255,.18)}
.sqz-qcard{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);border-radius:14px;padding:14px;margin-bottom:12px}
 .sqz-q{margin:0;font-size:34px;line-height:1.2;font-weight:900;color:#fff;text-align:center}
 .sqz-q, .sqz-q *{color:#fff !important}
.sqz-meta{margin:10px 0 0;display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.sqz-badge{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);border-radius:999px;padding:6px 10px;font-weight:700;font-size:13px}
.sqz-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
 .sqz-opt{border:0;border-radius:12px;padding:18px 14px;color:#fff;font-weight:900;font-size:24px;line-height:1.1;display:grid;grid-template-columns:34px 1fr;align-items:center;gap:10px;cursor:pointer;text-align:left;box-shadow:inset 0 -4px 0 rgba(0,0,0,.16)}
 .sqz-opt, .sqz-opt *{color:#fff !important}
.sqz-opt input{display:none}
.sqz-shape{font-size:28px;text-align:center}
.sqz-red{background:#ef4444}.sqz-blue{background:#2563eb}.sqz-yellow{background:#eab308}.sqz-green{background:#16a34a}
.sqz-opt.selected{outline:4px solid #fff}
.sqz-feedback{margin-top:12px;padding:12px 14px;border-radius:12px;font-weight:800;font-size:16px;display:none}
.sqz-feedback.is-correct{display:block;background:#dcfce7;border:1px solid #22c55e;color:#166534}
.sqz-feedback.is-wrong{display:block;background:#fee2e2;border:1px solid #ef4444;color:#991b1b}
.sqz-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.sqz-stack{display:grid;gap:8px}
.sqz-row .form-control,.sqz-row select,.sqz-row input{margin:0;border-radius:10px}
@media (max-width:900px){
    .sqz-grid{grid-template-columns:1fr}
    .sqz-q{font-size:24px}
    .sqz-opt{font-size:20px}
}
@media (max-width:768px){
    .lesson-slide{
        padding-inline:0;
    }
    .lesson-slide-shell{
        padding-inline:0;
    }
    .lesson-slide-shell > *{
        max-width:100%;
    }
    .lesson-split{
        grid-template-columns:1fr !important;
        gap:12px !important;
    }
    .lesson-split-card{
        width:100%;
    }
    .lesson-split-body{
        width:100%;
        display:flex;
        flex-direction:column;
        align-items:stretch;
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
    .lesson-split-media,
    .lesson-image,
    .lesson-code-frame,
    .lesson-media-figure,
    .lesson-image-focus img{
        width:100% !important;
        max-width:100% !important;
    }
    .lesson-split-media{
        height:100% !important;
        object-fit:contain !important;
        object-position:top center !important;
        background:#fff;
    }
    .lesson-image-focus{
        gap:12px !important;
        min-height:auto !important;
        padding-inline:0;
    }
    .lesson-image-focus figure{
        width:100% !important;
        min-width:0 !important;
    }
    .lesson-image-focus figure > div{
        width:100% !important;
        aspect-ratio:16/9 !important;
    }
    .lesson-paragraph{
        max-width:100% !important;
        word-break:break-word;
        overflow-wrap:anywhere;
        hyphens:auto;
    }
    .lesson-grid-cards{
        grid-template-columns:1fr !important;
    }
    .sqz-row{
        grid-template-columns:1fr !important;
    }
}
</style>
<div class="slide-render lesson-slide lesson-slide--{{ $layout }}" style="{{ $backgroundStyle }}">
    @if($isSummarySlide)
        @include('courses.partials.slides.summary', ['summary' => (array) ($slide['summary'] ?? [])])
    @else
        @php $heroText = (string) ($slide['subtitle'] ?? $slide['instructions'] ?? ''); @endphp
        <div class="lesson-slide-shell">
            @if(!$hideSlideTitle)
                <div class="lesson-slide-eyebrow">{{ strtoupper(str_replace(['_', '-'], ' ', $layout)) }}</div>
                <h3 class="lesson-slide-title">{{ $slide['title'] ?? 'Başlıksız Slide' }}</h3>
            @endif
            @if($heroText !== '')
                <p class="lesson-slide-subtitle">{{ $heroText }}</p>
            @endif

            @if($layout === 'text')
                <div class="lesson-card lesson-text-only">
                    @if($codeSrcdoc !== '')
                        <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
                    @elseif(!empty($slide['content']))
                        <div class="lesson-paragraph lesson-rich-text">{!! $renderRichText($slide['content']) !!}</div>
                    @elseif(!empty(data_get($slide, 'layout_meta.text.html')))
                        <div class="lesson-paragraph lesson-rich-text">{!! $renderRichText(data_get($slide, 'layout_meta.text.html')) !!}</div>
                    @elseif(!empty(data_get($slide, 'layout_meta.text.text')))
                        <div class="lesson-paragraph lesson-rich-text">{!! $renderRichText(data_get($slide, 'layout_meta.text.text')) !!}</div>
                    @endif
                </div>
            @elseif($layout === 'hero' || $layout === 'section')
                @include('courses.partials.slides.hero', ['slide' => $slide])
            @elseif($layout === 'split')
                @include('courses.partials.slides.split', ['slide' => $slide, 'codeSrcdoc' => $codeSrcdoc])
            @elseif($layout === 'image')
                @include('courses.partials.slides.image', ['slide' => $slide])
            @elseif($layout === 'features' || $layout === 'timeline' || $layout === 'steps')
                <div class="lesson-grid-cards">
                    @foreach($blocks as $block)
                        @if(($block['type'] ?? '') === 'paragraph')
                            <article class="lesson-card"><div class="lesson-paragraph lesson-rich-text">{!! $renderRichText($block['text'] ?? '') !!}</div></article>
                        @elseif(($block['type'] ?? '') === 'bullets')
                            <article class="lesson-card">
                                <ol class="lesson-list">
                                    @foreach((array) ($block['items'] ?? []) as $item)
                                        <li>{!! $renderRichText($item) !!}</li>
                                    @endforeach
                                </ol>
                            </article>
                        @endif
                    @endforeach
                </div>
            @elseif($layout === 'code')
                @include('courses.partials.slides.code', ['slide' => $slide, 'codeSrcdoc' => $codeSrcdoc])
            @elseif($layout === 'interactive')
                @include('courses.partials.slides.interactive', ['slide' => $slide, 'codeSrcdoc' => $codeSrcdoc])
            @else
                @if($codeSrcdoc !== '')
                    <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
                @endif
                @if($codeSrcdoc === '' && !empty($slide['content']))
                    <div class="lesson-paragraph lesson-rich-text">{!! $renderRichText($slide['content']) !!}</div>
                @endif
                @if($codeSrcdoc === '' && !empty($slide['image_url']))
                    <img src="{{ $slide['image_url'] }}" alt="slide görsel" class="lesson-image">
                @endif
                @if($codeSrcdoc === '' && !empty($slide['video_url']))
                    <p><a href="{{ $slide['video_url'] }}" target="_blank" rel="noopener">Video Bağlantısı</a></p>
                @endif
                @if($codeSrcdoc === '' && !empty($slide['file_url']))
                    <p><a href="{{ $slide['file_url'] }}" target="_blank" rel="noopener">Ek Kaynak</a></p>
                @endif
            @endif

            @if(!empty($slide['question_prompt']))
                @php
                    $palette = [
                        ['cls' => 'sqz-red', 'shape' => 'A'],
                        ['cls' => 'sqz-blue', 'shape' => 'B'],
                        ['cls' => 'sqz-yellow', 'shape' => 'C'],
                        ['cls' => 'sqz-green', 'shape' => 'D'],
                    ];
                    $rawOpts = (array) ($question['options'] ?? []);
                    $opts = [];
                    foreach ($rawOpts as $opt) {
                        if (is_array($opt)) {
                            $opts[] = [
                                'text' => $normalizeText($opt['text'] ?? $opt['label'] ?? $opt['value'] ?? ''),
                                'correct' => $isTruthyCorrect($opt),
                            ];
                        } else {
                            $opts[] = ['text' => $normalizeText($opt), 'correct' => false];
                        }
                    }
                    $opts = array_values(array_filter($opts, fn ($v) => trim((string) ($v['text'] ?? '')) !== ''));
                    if ($interactionType === 'multiple_choice') {
                        while (count($opts) < 4) {
                            $opts[] = ['text' => 'Seçenek ' . (count($opts) + 1), 'correct' => false];
                        }
                    }
                    if ($opts !== [] && !collect($opts)->contains(fn ($opt) => !empty($opt['correct'])) && isset($question['correct_index'])) {
                        $correctIndex = max(0, (int) $question['correct_index']);
                        if (isset($opts[$correctIndex])) {
                            $opts[$correctIndex]['correct'] = true;
                        }
                    }
                    if ($opts !== [] && !collect($opts)->contains(fn ($opt) => !empty($opt['correct'])) && !empty($question['answer'])) {
                        $answer = mb_strtolower($normalizeText($question['answer']));
                        foreach ($opts as &$optItem) {
                            if (mb_strtolower($normalizeText($optItem['text'])) === $answer) {
                                $optItem['correct'] = true;
                                break;
                            }
                        }
                        unset($optItem);
                    }
                    if ($interactionType === 'multiple_choice' && $opts === []) {
                        $opts = [
                            ['text' => 'Seçenek 1', 'correct' => false],
                            ['text' => 'Seçenek 2', 'correct' => false],
                            ['text' => 'Seçenek 3', 'correct' => false],
                            ['text' => 'Seçenek 4', 'correct' => false],
                        ];
                    }
                    $pairs = (array) ($question['pairs'] ?? []);
                    $items = (array) ($question['items'] ?? []);
                    $dragTargets = [];
                    foreach ($items as $it) {
                        if (is_array($it) && !empty($it['target'])) {
                            $dragTargets[] = (string) $it['target'];
                        }
                    }
                    $dragTargets = array_values(array_unique(array_filter($dragTargets, fn ($v) => trim($v) !== '')));
                    $inputName = 'sqz-opt-' . md5((string) ($slide['title'] ?? '') . '|' . (string) ($slide['question_prompt'] ?? ''));
                @endphp
                <div class="sqz-wrap lesson-interactive-panel" data-sqz-question data-sqz-type="{{ $interactionType }}" @if($interactionType === 'short_answer' && !empty($question['answer'])) data-sqz-answer="{{ $question['answer'] }}" @endif>
                    @if($interactionType === 'short_answer' && !empty($question['answer']))
                        <input type="hidden" data-sqz-answer value="{{ $question['answer'] }}">
                    @endif
                    <div class="sqz-qcard">
                        <p class="sqz-q">{{ $slide['question_prompt'] }}</p>
                        <div class="sqz-meta">
                            <span class="sqz-badge">Puan: {{ (int) ($slide['points'] ?? 5) }}</span>
                        </div>
                    </div>
                    @if($interactionType === 'multiple_choice')
                        <div class="sqz-grid">
                            @foreach($opts as $i => $optText)
                                @php $style = $palette[$i % 4]; @endphp
                                <label class="sqz-opt {{ $style['cls'] }}" data-sqz-option data-sqz-correct="{{ !empty($optText['correct']) ? '1' : '0' }}">
                                    <input type="radio" name="{{ $inputName }}" value="{{ $i }}" data-sqz-input>
                                    <span class="sqz-shape">{{ $style['shape'] }}</span>
                                    <span>{{ $optText['text'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($interactionType === 'true_false')
                        @php
                            $trueOption = collect($question['options'] ?? [])->first(fn ($opt) => is_array($opt) && $isTruthyCorrect($opt));
                            $trueCorrect = is_array($trueOption) ? true : (!empty($question['correct_index']) ? ((int) $question['correct_index'] === 0) : true);
                        @endphp
                        <div class="sqz-grid">
                            <label class="sqz-opt sqz-blue" data-sqz-option data-sqz-correct="{{ $trueCorrect ? '1' : '0' }}">
                                <input type="radio" name="{{ $inputName }}" value="A" data-sqz-input>
                                <span class="sqz-shape">A</span>
                                <span>Doğru</span>
                            </label>
                            <label class="sqz-opt sqz-red" data-sqz-option data-sqz-correct="{{ $trueCorrect ? '0' : '1' }}">
                                <input type="radio" name="{{ $inputName }}" value="B" data-sqz-input>
                                <span class="sqz-shape">B</span>
                                <span>Yanlış</span>
                            </label>
                        </div>
                    @elseif($interactionType === 'drag_drop')
                        <div class="sqz-stack">
                            @foreach($items as $it)
                                @php
                                    $txt = is_array($it) ? (string) ($it['text'] ?? '') : (string) $it;
                                    $expected = is_array($it) ? (string) ($it['target'] ?? ($it['answer'] ?? '')) : '';
                                @endphp
                                <div class="sqz-row" data-sqz-row data-sqz-answer="{{ $expected }}">
                                    <input class="form-control" type="text" readonly value="{{ $txt }}">
                                    <select class="form-control" data-sqz-input>
                                        <option value="">Eşleştir...</option>
                                        @foreach($dragTargets as $target)
                                            <option value="{{ $target }}">{{ $target }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    @elseif($interactionType === 'matching')
                        <div class="sqz-stack">
                            @foreach($pairs as $pair)
                                @php $expected = (string) ($pair['right'] ?? ($pair['answer'] ?? '')); @endphp
                                <div class="sqz-row" data-sqz-row data-sqz-answer="{{ $expected }}">
                                    <input class="form-control" type="text" readonly value="{{ (string) ($pair['left'] ?? '') }}">
                                    <input class="form-control" type="text" value="{{ (string) ($pair['right'] ?? '') }}" data-sqz-input>
                                </div>
                            @endforeach
                        </div>
                    @elseif($interactionType === 'short_answer')
                        <div class="sqz-row" style="grid-template-columns:1fr">
                            <input class="form-control" type="text" placeholder="Cevabını yaz..." data-sqz-input>
                        </div>
                    @elseif($interactionType === 'checklist')
                        <div class="sqz-grid">
                            @foreach($items as $i => $it)
                                @php
                                    $txt = is_array($it) ? (string) ($it['text'] ?? '') : (string) $it;
                                    $style = $palette[$i % 4];
                                    $isCorrect = is_array($it) ? $isTruthyCorrect($it) : false;
                                @endphp
                                <label class="sqz-opt {{ $style['cls'] }}" style="font-size:18px" data-sqz-option data-sqz-correct="{{ $isCorrect ? '1' : '0' }}">
                                    <input type="checkbox" data-sqz-input>
                                    <span class="sqz-shape">{{ $style['shape'] }}</span>
                                    <span>{{ $txt }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <div class="sqz-feedback" data-sqz-feedback aria-live="polite"></div>
                </div>
            @endif
        </div>
    @endif
</div>
