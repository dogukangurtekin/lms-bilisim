@extends('layout.app')
@section('title','Canli Quiz Oyna')
@section('content')
@php
    $questions = $session->quiz?->questions ?? collect();
    $q = $questions->get($session->current_index);
    $isLive = $session->status === 'live' && !$session->is_locked;
    $feedback = session('answer_feedback');
    $isAnsweredCurrent = (!empty($alreadyAnsweredCurrent))
        || (is_array($feedback) && (int) ($feedback['question_index'] ?? -1) === (int) $session->current_index);
    $left = (array) ($q?->options['left'] ?? []);
    $right = (array) ($q?->options['right'] ?? []);
    $opts = is_array($q?->options) ? $q->options : [];
@endphp
<style>
.lq-stage{border-radius:18px;padding:16px;background:linear-gradient(160deg,#4c1d95,#6d28d9 42%,#7c3aed);color:#fff;border:1px solid rgba(255,255,255,.18)}
.lq-header{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap}
.lq-title{margin:0;font-size:24px;font-weight:900}
.lq-badges{display:flex;gap:8px;flex-wrap:wrap}
.lq-badge{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);border-radius:999px;padding:6px 10px;font-weight:700;font-size:13px}
.lq-question-card{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);border-radius:14px;padding:14px;margin-bottom:12px}
.lq-question{margin:0;font-size:34px;line-height:1.2;font-weight:900;color:#fff;text-align:center}
.lq-meta{margin:10px 0 0;display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.lq-answer-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.lq-answer-btn{border:0;border-radius:12px;padding:18px 14px;color:#fff;font-weight:900;font-size:24px;line-height:1.1;display:grid;grid-template-columns:34px 1fr;align-items:center;gap:10px;cursor:pointer;text-align:left;box-shadow:inset 0 -4px 0 rgba(0,0,0,.16)}
.lq-answer-btn input{display:none}
.lq-answer-btn span{display:block;text-align:center}
.lq-shape{font-size:30px;text-align:center}
.lq-red{background:#ef4444}
.lq-blue{background:#2563eb}
.lq-yellow{background:#eab308}
.lq-green{background:#16a34a}
.lq-answer-btn.selected{outline:4px solid #fff}
.lq-submit-wrap{display:flex;justify-content:flex-end;margin-top:12px}
.lq-submit{min-width:220px;font-weight:800}
.lq-auto-note{margin-top:10px;font-size:13px;font-weight:700;opacity:.9}
.lq-answer-btn.is-locked{opacity:.65;pointer-events:none}
.lq-drag-list{display:grid;gap:8px}
.lq-drag-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.lq-drag-row .form-control{margin:0}
.lq-state{background:#fff;color:#0f172a;border-radius:12px;padding:16px;font-weight:700}
.lq-center-stage{min-height:420px;display:grid;place-items:center}
.lq-wait-box{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.35);border-radius:16px;padding:26px;min-width:min(560px,92vw);text-align:center}
.lq-wait-title{margin:0 0 8px;font-size:36px;font-weight:900}
.lq-wait-count{font-size:86px;line-height:1;font-weight:900;margin:10px 0}
.lq-result-title{font-size:42px;font-weight:900;margin:0}
.lq-result-good{color:#86efac}
.lq-result-bad{color:#fca5a5}
.lq-result-sub{margin:8px 0 0;font-size:24px;font-weight:800}
.lq-result-grid{margin-top:14px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.lq-result-box{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.35);border-radius:12px;padding:10px}
.lq-result-box span{display:block;font-size:12px;opacity:.9}
.lq-result-box strong{display:block;font-size:20px;margin-top:4px}
@media (max-width:900px){
  .lq-question{font-size:24px}
  .lq-answer-grid{grid-template-columns:1fr}
  .lq-answer-btn{font-size:20px}
  .lq-wait-title{font-size:28px}
  .lq-wait-count{font-size:64px}
  .lq-result-grid{grid-template-columns:1fr}
}
</style>

<div class="lq-stage">
    <div class="lq-header">
        <h1 class="lq-title">{{ $session->quiz?->title ?? 'Canli Quiz' }}</h1>
        <div class="lq-badges">
            <span class="lq-badge">Soru {{ $session->current_index + 1 }}/{{ $questions->count() }}</span>
            <span class="lq-badge">Durum: {{ $session->status }} {{ $session->is_locked ? '(Kilitli)' : '' }}</span>
            @if($session->status === 'live' && !$session->is_locked)
                <span class="lq-badge">Kalan Sure: <strong id="lq-countdown">--</strong> sn</span>
            @endif
        </div>
    </div>

    @if(!$q)
        <div class="lq-state">Bu oturumda soru bulunamadi.</div>
    @elseif($session->status !== 'live')
        <div class="lq-state">Quiz tamamlandi.</div>
    @elseif($isAnsweredCurrent)
        <div class="lq-center-stage" id="lqWaitingStage">
            <div class="lq-wait-box">
                <h3 class="lq-wait-title">Sonraki Soruya Hazirlaniyor</h3>
                <div class="lq-wait-count" id="lq-answer-countdown">--</div>
                <p class="lq-auto-note">Kalan sure bitince sonucun ve siralaman gosterilecek.</p>
            </div>
        </div>
        <div class="lq-center-stage" id="lqResultStage" style="display:none;">
            <div class="lq-wait-box">
                <h3 class="lq-result-title {{ !empty($feedback['is_correct']) ? 'lq-result-good' : 'lq-result-bad' }}">
                    {{ is_array($feedback) ? (!empty($feedback['is_correct']) ? 'Dogru Cevap' : 'Yanlis Cevap') : 'Cevap Kaydedildi' }}
                </h3>
                @if(is_array($feedback))
                    <div class="lq-result-grid">
                        <div class="lq-result-box">
                            <span>Ogrencinin Cevabi</span>
                            <strong>{{ (string) ($feedback['student_answer_text'] ?? '-') }}</strong>
                        </div>
                        <div class="lq-result-box">
                            <span>Dogru Cevap</span>
                            <strong>{{ (string) ($feedback['correct_answer_text'] ?? '-') }}</strong>
                        </div>
                        <div class="lq-result-box">
                            <span>Toplam Quiz XP</span>
                            <strong>{{ (int) ($feedback['session_total_xp'] ?? 0) }}</strong>
                        </div>
                        <div class="lq-result-box">
                            <span>Siralama</span>
                            <strong>{{ (int) ($feedback['rank'] ?? 0) }}/{{ (int) ($feedback['total'] ?? 0) }}</strong>
                        </div>
                    </div>
                @endif
                <p class="lq-auto-note">Sonraki soruya <strong id="lq-next-countdown">5</strong> saniye sonra geciliyor (5 4 3 2 1)...</p>
            </div>
        </div>
    @else
        <div class="lq-question-card">
            <h3 class="lq-question">{{ $q->question_text }}</h3>
            <div class="lq-meta">
                <span class="lq-badge">Sure: {{ $q->duration_sec }} sn</span>
                <span class="lq-badge">XP: {{ $q->xp }} {{ $q->double_xp ? '(2x aktif)' : '' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('student.live-quiz.answer', $session) }}">
            @csrf
            <input type="hidden" name="question_index" value="{{ $session->current_index }}">

            @if($q->type === 'truefalse')
                <div class="lq-answer-grid">
                    <label class="lq-answer-btn lq-blue" data-answer-btn>
                        <input type="radio" name="answer" value="A" required {{ $isLive ? '' : 'disabled' }}>
                        <i class="lq-shape">◆</i><span>Dogru</span>
                    </label>
                    <label class="lq-answer-btn lq-red" data-answer-btn>
                        <input type="radio" name="answer" value="B" required {{ $isLive ? '' : 'disabled' }}>
                        <i class="lq-shape">▲</i><span>Yanlis</span>
                    </label>
                </div>
            @elseif($q->type === 'dragdrop')
                <div class="lq-drag-list">
                    @foreach($left as $idx => $leftText)
                        <div class="lq-drag-row">
                            <div class="form-control" style="background:#fff">{{ $leftText }}</div>
                            <select class="form-control" name="dragdrop[{{ $idx }}]" required {{ $isLive ? '' : 'disabled' }}>
                                @foreach($right as $rIdx => $rightText)
                                    <option value="{{ $rIdx }}">{{ $rightText }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            @else
                @php
                    $palette = [
                        ['cls' => 'lq-red', 'shape' => '▲'],
                        ['cls' => 'lq-blue', 'shape' => '◆'],
                        ['cls' => 'lq-yellow', 'shape' => '●'],
                        ['cls' => 'lq-green', 'shape' => '■'],
                    ];
                @endphp
                <div class="lq-answer-grid">
                    @foreach($opts as $i => $opt)
                        @php $style = $palette[$i % 4]; @endphp
                        <label class="lq-answer-btn {{ $style['cls'] }}" data-answer-btn>
                            <input type="radio" name="answer" value="{{ chr(65 + $i) }}" required {{ $isLive ? '' : 'disabled' }}>
                            <i class="lq-shape">{{ $style['shape'] }}</i>
                            <span>{{ $opt }}</span>
                        </label>
                    @endforeach
                </div>
            @endif

            @if($q->type === 'dragdrop')
                <div class="lq-submit-wrap">
                    <button class="btn lq-submit" type="submit" {{ $isLive ? '' : 'disabled' }}>Cevabi Gonder</button>
                </div>
            @else
                <div class="lq-auto-note">Secenegi tiklayinca cevap otomatik gonderilir.</div>
            @endif
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    const formEl = document.querySelector('.lq-stage form');
    let autoSubmitted = false;

    const lockForm = () => {
        if (!formEl) return;
        document.querySelectorAll('[data-answer-btn]').forEach((x) => x.classList.add('is-locked'));
    };

    document.querySelectorAll('[data-answer-btn]').forEach((label) => {
        const input = label.querySelector('input[type="radio"]');
        if (!input) return;
        input.addEventListener('change', () => {
            document.querySelectorAll('[data-answer-btn]').forEach((x) => x.classList.remove('selected'));
            label.classList.add('selected');
            @if(in_array($q?->type, ['multiple', 'truefalse'], true))
            if (autoSubmitted) return;
            autoSubmitted = true;
            lockForm();
            window.setTimeout(() => formEl?.submit(), 0);
            @endif
        });
    });
    @if($session->status === 'live')
    const endsAtMs = {{ (int) ($session->ends_at_ms ?? 0) }};
    const countdownEl = document.getElementById('lq-countdown');
    const answerCountdownEl = document.getElementById('lq-answer-countdown');
    const waitingStage = document.getElementById('lqWaitingStage');
    const resultStage = document.getElementById('lqResultStage');
    const nextCountdownEl = document.getElementById('lq-next-countdown');
    let resultShown = false;
    let nextInterval = null;

    const startNextCountdown = () => {
        if (!nextCountdownEl || nextInterval) return;
        let sec = 5;
        nextCountdownEl.textContent = String(sec);
        nextInterval = window.setInterval(() => {
            sec -= 1;
            if (sec <= 0) {
                window.clearInterval(nextInterval);
                nextInterval = null;
                window.location.reload();
                return;
            }
            nextCountdownEl.textContent = String(sec);
        }, 1000);
    };

    const tick = () => {
        const leftMs = endsAtMs - Date.now();
        const leftSec = Math.max(0, Math.ceil(leftMs / 1000));
        if (countdownEl) countdownEl.textContent = String(leftSec);
        if (answerCountdownEl) answerCountdownEl.textContent = String(leftSec);
        if (leftSec <= 0) {
            if (waitingStage && resultStage && !resultShown) {
                waitingStage.style.display = 'none';
                resultStage.style.display = 'grid';
                resultShown = true;
                startNextCountdown();
                return;
            }
            window.location.reload();
        }
    };
    tick();
    setInterval(tick, 300);
    @endif
})();
</script>
@endpush
