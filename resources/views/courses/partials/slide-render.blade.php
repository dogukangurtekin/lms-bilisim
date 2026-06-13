@php
    $slide = $slide ?? [];
    $question = $slide['question'] ?? [];
    $hideSlideTitle = $hideSlideTitle ?? false;
    $globalThemeCss = $globalThemeCss ?? '';
    $isSummarySlide = !empty($slide['__summary']);
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
    if (contentWidth > vw) {
      scale = vw / contentWidth;
    } else if (contentWidth < vw * 0.72) {
      scale = Math.min(1.6, vw / contentWidth);
    }
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
    $codeSrcdoc = (string) ($slide['code'] ?? '');
    if ($codeSrcdoc !== '') {
        $codeSrcdoc = $responsiveHelper
            . (trim((string) $globalThemeCss) !== '' ? ('<style>' . $globalThemeCss . '</style>') : '')
            . $codeSrcdoc;
    }
    $interactionType = (string) ($slide['interaction_type'] ?? 'none');
@endphp
<style>
.sqz-wrap{margin-top:10px;border-radius:18px;padding:14px;background:linear-gradient(160deg,#4c1d95,#6d28d9 42%,#7c3aed);color:#fff;border:1px solid rgba(255,255,255,.18)}
.sqz-qcard{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);border-radius:14px;padding:14px;margin-bottom:12px}
.sqz-q{margin:0;font-size:34px;line-height:1.2;font-weight:900;color:#fff;text-align:center}
.sqz-meta{margin:10px 0 0;display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.sqz-badge{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);border-radius:999px;padding:6px 10px;font-weight:700;font-size:13px}
.sqz-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.sqz-opt{border:0;border-radius:12px;padding:18px 14px;color:#fff;font-weight:900;font-size:24px;line-height:1.1;display:grid;grid-template-columns:34px 1fr;align-items:center;gap:10px;cursor:pointer;text-align:left;box-shadow:inset 0 -4px 0 rgba(0,0,0,.16)}
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
@media (max-width:900px){.sqz-grid{grid-template-columns:1fr}.sqz-q{font-size:24px}.sqz-opt{font-size:20px}}
</style>
<div class="slide-render">
    @if($isSummarySlide)
        @php
            $summary = (array) ($slide['summary'] ?? []);
            $outcomes = array_values(array_filter((array) ($summary['outcomes'] ?? []), fn ($item) => trim((string) $item) !== ''));
            $activities = array_values(array_filter((array) ($summary['activities'] ?? []), fn ($item) => trim((string) $item) !== ''));
        @endphp
        <div style="min-height:72vh;display:flex;align-items:center;justify-content:center;padding:20px">
            <div style="width:min(100%,980px);border-radius:28px;padding:28px;background:linear-gradient(135deg,#eff6ff 0%,#f8fafc 48%,#ecfeff 100%);border:1px solid #bfdbfe;box-shadow:0 22px 50px rgba(37,99,235,.12)">
                        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px">
                    <div>
                        <p style="margin:0 0 8px;font-size:14px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#2563eb">Son Sayfa</p>
                        <h2 style="margin:0;font-size:34px;line-height:1.1;font-weight:900;color:#0f172a">{{ $summary['lesson_title'] ?? 'Ders Özeti' }}</h2>
                    </div>
                    <div style="display:grid;gap:8px;justify-items:end">
                        <span data-summary-earned-xp style="display:inline-flex;align-items:center;padding:10px 16px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:900">Kazanılan XP: {{ (int) ($summary['lesson_total_xp'] ?? 0) }}</span>
                        <span style="display:inline-flex;align-items:center;padding:10px 16px;border-radius:999px;background:#ede9fe;color:#5b21b6;font-weight:900">Ders No: {{ (int) ($summary['lesson_number'] ?? 1) }}</span>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1.25fr .95fr;gap:16px">
                    <div style="padding:20px;border-radius:22px;background:#fff;border:1px solid #dbeafe">
                        <h3 style="margin:0 0 12px;font-size:22px;font-weight:900;color:#111827">Bu derste ne öğrendin?</h3>
                        <p style="margin:0 0 14px;font-size:18px;line-height:1.8;color:#334155">{{ $summary['topic'] ?: 'Bu bölümde temel konular ve örnekler işlendi.' }}</p>
                        @if($outcomes !== [])
                            <ul style="margin:0;padding-left:20px;display:grid;gap:10px;color:#334155">
                                @foreach($outcomes as $outcome)
                                    <li style="font-size:17px;line-height:1.7">{{ $outcome }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p style="margin:0;color:#64748b">Henüz kazanım girilmedi.</p>
                        @endif
                    </div>
                    <div style="padding:20px;border-radius:22px;background:#fff;border:1px solid #dbeafe;display:grid;gap:14px">
                        <div>
                            <h3 style="margin:0 0 10px;font-size:22px;font-weight:900;color:#111827">Sonraki çalışma</h3>
                            <p style="margin:0;font-size:17px;line-height:1.8;color:#334155">Kısa tekrar yap, örnekleri bir kez daha incele ve kazanımları kendi cümlelerinle anlatmaya çalış.</p>
                        </div>
                        <div>
                            <h4 style="margin:0 0 8px;font-size:18px;font-weight:900;color:#111827">Yapılacaklar</h4>
                            @if($activities !== [])
                                <ul style="margin:0;padding-left:20px;display:grid;gap:8px;color:#334155">
                                    @foreach($activities as $activity)
                                        <li style="font-size:16px;line-height:1.6">{{ $activity }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p style="margin:0;color:#64748b">Bu konuyla ilgili kısa tekrar ve uygulama önerilir.</p>
                            @endif
                        </div>
                        <div style="padding:14px 16px;border-radius:18px;background:linear-gradient(135deg,#2563eb,#0ea5e9);color:#fff;font-weight:800;line-height:1.7">
                            Toplam slayt sayısı: {{ (int) ($summary['slide_count'] ?? 0) }} <br>
                            Ders tamamlandığında bu özet, öğrenci gelişim raporuna işlenecek.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
    @if(!$hideSlideTitle)
        <h3>{{ $slide['title'] ?? 'Basliksiz Slide' }}</h3>
    @endif
    @if(!empty($slide['instructions']))
        <p><b>Yonlendirme:</b> {{ $slide['instructions'] }}</p>
    @endif
    @if(!empty($slide['content']))
        <p>{{ $slide['content'] }}</p>
    @endif
    @if(!empty($slide['image_url']))
        <img src="{{ $slide['image_url'] }}" alt="slide gorsel" style="max-width:100%;border:1px solid #e5e7eb;border-radius:8px">
    @endif
    @if(!empty($slide['video_url']))
        <p><a href="{{ $slide['video_url'] }}" target="_blank">Video Baglantisi</a></p>
    @endif
    @if(!empty($slide['file_url']))
        <p><a href="{{ $slide['file_url'] }}" target="_blank">Ek Kaynak</a></p>
    @endif
    @if($codeSrcdoc !== '')
        <iframe allow="camera *; microphone *; fullscreen *" style="width:100%;min-height:58vh;border:1px solid #d1d5db;border-radius:8px;margin-top:8px" srcdoc="{{ $codeSrcdoc }}"></iframe>
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
                $opts[] = is_array($opt) ? [
                    'text' => (string) ($opt['text'] ?? ''),
                    'correct' => (bool) ($opt['correct'] ?? false),
                ] : [
                    'text' => (string) $opt,
                    'correct' => false,
                ];
            }
            $opts = array_values(array_filter($opts, fn ($v) => trim((string) ($v['text'] ?? '')) !== ''));
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
                if (is_array($it) && !empty($it['target'])) $dragTargets[] = (string) $it['target'];
            }
            $dragTargets = array_values(array_unique(array_filter($dragTargets, fn ($v) => trim($v) !== '')));
            $inputName = 'sqz-opt-' . md5((string) ($slide['title'] ?? '') . '|' . (string) ($slide['question_prompt'] ?? ''));
        @endphp
            <div class="sqz-wrap" data-sqz-question data-sqz-type="{{ $interactionType }}">
                @if($interactionType === 'short_answer' && !empty($question['answer']))
                    <input type="hidden" data-sqz-answer value="{{ $question['answer'] }}">
                @endif
                <div class="sqz-qcard">
                    <p class="sqz-q">{{ $slide['question_prompt'] }}</p>
                    <div class="sqz-meta">
                    <span class="sqz-badge">Puan: {{ (int) ($slide['points'] ?? 5) }}</span>
                    <span class="sqz-badge">Sure: {{ (int) ($slide['time_limit'] ?? 10) }} sn</span>
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
                <div class="sqz-grid">
                    @php
                        $trueOption = collect($question['options'] ?? [])->firstWhere('text', 'Dogru');
                        $trueCorrect = is_array($trueOption) ? (bool) ($trueOption['correct'] ?? true) : true;
                    @endphp
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
                        @php $txt = is_array($it) ? (string) ($it['text'] ?? '') : (string) $it; @endphp
                        <div class="sqz-row">
                            <input class="form-control" type="text" readonly value="{{ $txt }}">
                            <select class="form-control" data-sqz-input>
                                <option value="">Eslestir...</option>
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
                        <div class="sqz-row">
                            <input class="form-control" type="text" readonly value="{{ (string) ($pair['left'] ?? '') }}">
                            <input class="form-control" type="text" value="{{ (string) ($pair['right'] ?? '') }}" data-sqz-input>
                        </div>
                    @endforeach
                </div>
            @elseif($interactionType === 'short_answer')
                <div class="sqz-row" style="grid-template-columns:1fr">
                    <input class="form-control" type="text" placeholder="Cevabini yaz..." data-sqz-input>
                </div>
            @elseif($interactionType === 'checklist')
                <div class="sqz-grid">
                    @foreach($items as $i => $it)
                        @php
                            $txt = is_array($it) ? (string) ($it['text'] ?? '') : (string) $it;
                            $style = $palette[$i % 4];
                        @endphp
                        <label class="sqz-opt {{ $style['cls'] }}" style="font-size:18px" data-sqz-option>
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
    @endif
</div>
