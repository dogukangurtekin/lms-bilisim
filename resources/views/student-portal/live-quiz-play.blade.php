@extends('layout.app')
@section('title','Canli Quiz Oyna')
@section('content')
@php
    $questions = $session->quiz?->questions ?? collect();
    $q = $questions->get($session->current_index);
    $isLive = $session->status === 'live' && $session->phase === 'question' && !$session->is_locked;
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
.lq-question-card{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);border-radius:14px;padding:14px;margin-bottom:12px;animation:lqQuestionIn .55s cubic-bezier(.2,.8,.2,1) both}
.lq-answer-grid,.lq-drag-list{animation:lqAnswersIn .65s .12s both}
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
.lq-student-list{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-top:16px}
.lq-student-chip{padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);font-weight:800}
.lq-student-empty{font-size:13px;font-weight:700;opacity:.85}
.lq-result-title{font-size:42px;font-weight:900;margin:0}
.lq-result-good{color:#86efac}
.lq-result-bad{color:#fca5a5}
.lq-result-sub{margin:8px 0 0;font-size:24px;font-weight:800}
.lq-result-grid{margin-top:14px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.lq-result-box{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.35);border-radius:12px;padding:10px}
.lq-result-box span{display:block;font-size:12px;opacity:.9}
.lq-result-box strong{display:block;font-size:20px;margin-top:4px}
.lq-top-five-title{margin:22px 0 10px;font-size:24px;font-weight:900}
.lq-top-five{display:flex;flex-wrap:wrap;justify-content:center;align-items:flex-end;gap:10px}
.lq-winner{min-width:130px;padding:12px;border-radius:14px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);animation:lqWinnerIn .55s both;animation-delay:calc(var(--rank) * .12s)}
.lq-winner:first-child{background:linear-gradient(145deg,#f59e0b,#f97316);padding:18px;min-width:170px}
.lq-winner-rank{font-size:23px;font-weight:900}.lq-winner-name{font-size:22px;font-weight:900;overflow-wrap:anywhere}.lq-winner-state{margin-top:4px;font-size:12px;font-weight:800}
.lq-double-stage{text-align:center;animation:lqQuestionIn .5s both}.lq-double-icon{font-size:84px;font-weight:950;animation:lqDoublePulse .7s ease-in-out infinite alternate}.lq-double-title{font-size:48px;font-weight:950;margin:10px 0;text-shadow:0 6px 20px rgba(0,0,0,.35)}
@keyframes lqQuestionIn{from{opacity:0;transform:translateX(55px) scale(.96)}to{opacity:1;transform:translateX(0) scale(1)}}
@keyframes lqAnswersIn{from{opacity:0;transform:translateY(35px)}to{opacity:1;transform:translateY(0)}}
@keyframes lqDoublePulse{from{transform:scale(.82) rotate(-6deg)}to{transform:scale(1.13) rotate(6deg)}}
@keyframes lqWinnerIn{from{opacity:0;transform:translateY(45px) scale(.8)}to{opacity:1;transform:translateY(0) scale(1)}}
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
            @if($session->status === 'live' && $session->phase === 'question')
                <span class="lq-badge">Kalan Sure: <strong id="lq-countdown">--</strong> sn</span>
            @endif
        </div>
    </div>

    @if($session->status === 'lobby')
        <div class="lq-center-stage">
            <div class="lq-wait-box">
                <h3 class="lq-wait-title">Ogretmen Baslatmasini Bekliyorsun</h3>
                <p>Katilim kodu ile lobiye girdin. Ogretmen "Herkese Baslat" dedigi an ilk soru
                   herkes icin ayni anda baslayacak.</p>
                <div class="lq-wait-count" id="lqLobbyCount">{{ $joinedCount ?? 0 }}</div>
                <p class="lq-auto-note">Lobide bekleyen ogrenci sayisi</p>
                <div class="lq-student-list" id="lqStudentParticipantList">
                    @forelse(($participantRows ?? []) as $participant)
                        <span class="lq-student-chip">{{ $participant['student_name'] }}</span>
                    @empty
                        <span class="lq-student-empty">Henüz katılan öğrenci yok.</span>
                    @endforelse
                </div>
            </div>
        </div>
    @elseif(!$q)
        <div class="lq-state">Bu oturumda soru bulunamadi.</div>
    @elseif($session->status !== 'live')
        <div class="lq-state">Quiz tamamlandi.</div>
    @elseif($session->phase === 'intro')
        <div class="lq-center-stage lq-double-stage">
            <div class="lq-wait-box">
                <div class="lq-double-icon">⚡ 2X</div>
                <h3 class="lq-double-title">2 Kat Puanlı Soru!</h3>
                <p>Hazır ol! Bu sorunun XP ödülü iki katına çıkıyor.</p>
                <div class="lq-wait-count" id="lq-next-countdown">3</div>
            </div>
        </div>
    @elseif($session->phase === 'results')
        <div class="lq-center-stage">
            <div class="lq-wait-box">
                @php
                    $answered = $currentAnswer && $currentAnswer->selected_answer !== null && $currentAnswer->selected_answer !== '';
                    $correct = $answered && (bool) $currentAnswer->is_correct;
                @endphp
                <h3 class="lq-result-title {{ $correct ? 'lq-result-good' : 'lq-result-bad' }}">
                    {{ !$answered ? 'Cevaplamadın' : ($correct ? 'Doğru Cevap!' : 'Yanlış Cevap') }}
                </h3>
                <p class="lq-result-sub">{{ $correct ? '+' . (int) $currentAnswer->xp_earned . ' XP kazandın' : 'Bu sorudan XP kazanamadın' }}</p>
                <div class="lq-top-five-title">Bu Sorunun İlk 5'i</div>
                <div class="lq-top-five">
                    @forelse($topFive as $student)
                        <div class="lq-winner" style="--rank:{{ $student['rank'] }}">
                            <div class="lq-winner-rank">#{{ $student['rank'] }}</div>
                            <div class="lq-winner-name">{{ $student['student_name'] }}</div>
                            <div class="lq-winner-state">{{ $student['answered'] ? ($student['is_correct'] ? 'Doğru' : 'Yanlış') : 'Cevaplamadı' }}</div>
                        </div>
                    @empty
                        <strong>Henüz sonuç bulunmuyor.</strong>
                    @endforelse
                </div>
                <p class="lq-auto-note">Sıradaki soru <strong id="lq-next-countdown">5</strong> saniye sonra tüm öğrencilerde aynı anda açılacak.</p>
            </div>
        </div>
    @elseif($isAnsweredCurrent)
        <div class="lq-center-stage" id="lqWaitingStage">
            <div class="lq-wait-box">
                <h3 class="lq-wait-title">Cevabın Kaydedildi</h3>
                <div class="lq-wait-count" id="lq-answer-countdown">--</div>
                <p class="lq-auto-note">Süre bittiğinde doğru/yanlış sonucun ve ilk 5 öğrenci gösterilecek.</p>
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
    const statusUrl = @json(route('student.live-quiz.status', $session));
    let clockOffsetMs = 0;

    @if($session->status === 'lobby')
    const lobbyCountEl = document.getElementById('lqLobbyCount');
    const participantListEl = document.getElementById('lqStudentParticipantList');
    const renderParticipants = (participants) => {
        if (!participantListEl || !Array.isArray(participants)) return;
        participantListEl.replaceChildren();
        if (participants.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'lq-student-empty';
            empty.textContent = 'Henüz katılan öğrenci yok.';
            participantListEl.appendChild(empty);
            return;
        }
        participants.forEach((participant) => {
            const chip = document.createElement('span');
            chip.className = 'lq-student-chip';
            chip.textContent = participant.student_name || 'Öğrenci';
            participantListEl.appendChild(chip);
        });
    };
    const pollLobby = async () => {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            clockOffsetMs = data.server_now_ms - Date.now();
            if (lobbyCountEl && typeof data.joined_count === 'number') {
                lobbyCountEl.textContent = String(data.joined_count);
            }
            renderParticipants(data.participants);
            if (data.status === 'live') {
                // Ogretmen "Herkese Baslat" dedi: soru ekranina gecmek icin yenile.
                window.location.reload();
            }
        } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
    };
    pollLobby();
    setInterval(pollLobby, 1500);
    @endif

    @if($session->status === 'live')
    let endsAtMs = {{ (int) ($session->ends_at_ms ?? 0) }};
    const initialIndex = {{ (int) $session->current_index }};
    const initialPhase = @json((string) $session->phase);
    const countdownEl = document.getElementById('lq-countdown');
    const answerCountdownEl = document.getElementById('lq-answer-countdown');
    const nextCountdownEl = document.getElementById('lq-next-countdown');

    // Ogrencinin cihaz saati sunucudan farkli olabilir; periyodik senkronizasyon
    // ile sayacin gercek kalan sureyi gostermesi saglanir (Kahoot'taki gibi
    // herkeste ayni anda sifirlanmasi icin).
    const syncClock = async () => {
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            clockOffsetMs = data.server_now_ms - Date.now();
            if (typeof data.ends_at_ms === 'number' && data.ends_at_ms > 0) {
                endsAtMs = data.ends_at_ms;
            }
            if (data.status === 'finished') {
                window.location.reload();
                return;
            }
            if (Number(data.current_index) !== initialIndex || String(data.phase || '') !== initialPhase) {
                window.location.reload();
            }
        } catch (e) { /* bir sonraki denemede tekrar denenecek */ }
    };
    syncClock();
    setInterval(syncClock, 1000);

    const tick = () => {
        const leftMs = endsAtMs - (Date.now() + clockOffsetMs);
        const leftSec = Math.max(0, Math.ceil(leftMs / 1000));
        if (countdownEl) countdownEl.textContent = String(leftSec);
        if (answerCountdownEl) answerCountdownEl.textContent = String(leftSec);
        if (nextCountdownEl) nextCountdownEl.textContent = String(leftSec);
    };
    tick();
    setInterval(tick, 300);
    @endif
})();
</script>
@endpush
