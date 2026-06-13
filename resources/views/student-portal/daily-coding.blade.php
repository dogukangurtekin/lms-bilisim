@extends('layout.app')
@section('title','Günlük Çalışma')
@section('content')
@php($activity = $activities->first())
@php($lessonSlides = $activity ? array_values(array_filter(array_merge([$activity->instruction ?: 'Konu anlatımı hazırlanıyor.'], $activity->lesson_pages ?? []))) : [])
<div class="dc-wrap" data-dc-root data-lesson-slides='@json($lessonSlides)' data-question-count="{{ $activity?->questions?->count() ?? 0 }}">
  <div class="dc-shell">
    <div class="dc-top">
      <div>
        <div style="font-size:12px;opacity:.9">Bölüm 1 • Günlük Çalışma</div>
        <div style="font-size:24px;font-weight:800">Kodlama Etkinlikleri</div>
      </div>
      <div style="font-size:13px;text-align:right">Seri: <b>{{ $streak?->current_streak ?? 0 }} gün</b><br>Bugün XP: <b>{{ $todayXp }}</b></div>
    </div>

    @if($activity)
    <div class="dc-main">
      <aside class="dc-left">
        <h3 style="margin:0 0 10px;font-size:22px">Görev Haritası</h3>
        <button class="dc-map-btn active" type="button">
          <div style="display:flex;justify-content:space-between;gap:8px"><b>{{ $activity->title }}</b><span style="font-size:12px;color:#475569">{{ strtoupper($activity->type) }}</span></div>
          <div style="margin-top:6px;color:#64748b;font-size:13px">{{ $activity->questions->count() }} soru • {{ $activity->base_xp }} XP</div>
        </button>
      </aside>

      <section class="dc-right">
        @if(session('wrong_details'))
          <div class="dc-review-card">
            <h3 class="dc-review-title">Yanlış Cevap Özeti</h3>
            <p class="dc-review-note">Aşağıda yanlış işaretlenen sorular, öğrencinin verdiği cevap ve beklenen doğru cevap yer alır.</p>
            <div class="dc-review-list">
              @foreach((array) session('wrong_details') as $item)
                <article class="dc-review-item">
                  <div class="dc-review-q">{{ $item['question'] ?? '-' }}</div>
                  <div class="dc-review-grid">
                    <div><span>Senin cevabın</span><strong>{{ $item['given'] ?? '-' }}</strong></div>
                    <div><span>Doğru cevap</span><strong>{{ $item['expected'] ?? '-' }}</strong></div>
                    <div><span>Soru tipi</span><strong>{{ $item['type'] ?? '-' }}</strong></div>
                  </div>
                </article>
              @endforeach
            </div>
          </div>
        @endif

        <div id="lessonStage">
          <h2 class="dc-title">{{ $activity->title }}</h2>
          <div class="dc-step">
            <div class="dc-step-badge">Hap Bilgi</div>
            <div class="dc-step-meta">Adım <span id="lessonStepIndex">1</span> / <span id="lessonStepTotal">{{ max(1, count($lessonSlides)) }}</span></div>
          </div>
          <div class="dc-lesson-card">
            <p class="dc-text" id="lessonText">{{ $lessonSlides[0] ?? 'Konu anlatımı hazırlanıyor.' }}</p>
          </div>
          <div class="dc-row">
            <button type="button" class="dc-btn" id="lessonPrevBtn" disabled>Geri</button>
            <button type="button" class="dc-cta" id="lessonNextBtn">İlerle</button>
          </div>
        </div>

        <div id="quizStage" class="dc-stage-hidden">
          <h2 class="dc-title">Sorular</h2>
          <form method="POST" action="{{ route('student.coding.submit', $activity) }}" id="quizForm">
            @csrf
            @foreach($activity->questions as $qIndex => $q)
              <div class="dc-q" data-question-card>
                <div style="font-weight:800;color:#0f172a;margin-bottom:10px">{{ $qIndex + 1 }}. {{ $q->prompt }}</div>
                @if(in_array($q->question_type, ['single_choice','multi_choice']))
                  <div class="dc-choice-grid" data-choice-group="{{ $q->id }}">
                    @foreach($q->options as $optIndex => $opt)
                      <label class="dc-choice-card" data-choice-card>
                        <input class="dc-choice-input" {{ $q->question_type === 'multi_choice' ? 'type=checkbox name=answers['.$q->id.'][]' : 'type=radio name=answers['.$q->id.']' }} value="{{ $opt->option_key }}">
                        <span class="dc-choice-key">{{ chr(65 + $optIndex) }}</span>
                        <span class="dc-choice-text">{{ $opt->label }}</span>
                      </label>
                    @endforeach
                  </div>
                @else
                  <input class="dc-input" name="answers[{{ $q->id }}]" placeholder="Cevabını yaz">
                @endif
              </div>
            @endforeach
            <input type="hidden" name="duration_seconds" id="durationSecondsInput" value="0">
            <div class="dc-bottom"><button class="dc-cta" type="submit">Gönder / Devam Et</button></div>
          </form>
        </div>

        <div id="emptyQuizStage" class="dc-stage-hidden">
          <div class="dc-empty">Bu etkinlik için soru tanımlanmamış. Konu anlatımı tamamlandı.</div>
        </div>
      </section>
    </div>
    @else
      <div class="dc-empty-panel">Bugün için atanmış günlük çalışma yok.</div>
    @endif
  </div>
</div>
<style>
.dc-stage-hidden{display:none}.dc-wrap{max-width:1360px;margin:0 auto;padding:14px}[x-cloak]{display:none!important}.dc-shell{background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;box-shadow:0 18px 36px rgba(15,23,42,.08)}.dc-top{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:linear-gradient(90deg,#0284c7,#2563eb);color:#fff}.dc-main{display:grid;grid-template-columns:340px 1fr;min-height:72vh}.dc-left{border-right:1px solid #e2e8f0;background:#fff;padding:16px}.dc-right{padding:18px}.dc-map-btn{width:100%;text-align:left;border:1px solid #cbd5e1;border-radius:12px;padding:11px;background:#fff;margin-bottom:10px}.dc-map-btn.active{border-color:#0ea5e9;background:#f0f9ff}.dc-title{font-size:32px;font-weight:800;margin-bottom:12px}.dc-step{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:12px}.dc-step-badge{background:#e0f2fe;color:#0369a1;padding:8px 12px;border-radius:9999px;font-weight:800}.dc-step-meta{color:#475569;font-weight:600}.dc-lesson-card{background:#fff;border:1px solid #bfdbfe;border-radius:16px;padding:16px;min-height:220px;display:flex;align-items:flex-start}.dc-text{font-size:18px;line-height:1.7;white-space:pre-wrap;margin:0}.dc-q{background:#fff;border:1px solid #dbeafe;border-radius:16px;padding:16px;margin-bottom:12px}.dc-choice-grid{display:grid;gap:12px;margin-top:12px}.dc-choice-card{display:grid;grid-template-columns:28px 30px 1fr;align-items:center;gap:14px;min-height:64px;padding:14px 16px;border:1px solid #dbeafe;border-radius:16px;background:linear-gradient(180deg,#ffffff,#f8fbff);cursor:pointer;transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease,background .15s ease}.dc-choice-card:hover{transform:translateY(-1px);border-color:#60a5fa;box-shadow:0 8px 18px rgba(37,99,235,.08)}.dc-choice-card.is-selected{border-color:#2563eb;background:linear-gradient(180deg,#eff6ff,#ffffff);box-shadow:0 10px 20px rgba(37,99,235,.10)}.dc-choice-input{width:22px;height:22px;margin:0;accent-color:#2563eb;justify-self:center}.dc-choice-key{width:30px;height:30px;border-radius:9999px;background:#e0f2fe;color:#0369a1;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:13px}.dc-choice-text{font-size:16px;line-height:1.45;color:#0f172a;font-weight:600}.dc-input{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:12px}.dc-bottom{position:sticky;bottom:0;background:#f8fafc;padding-top:10px}.dc-cta{width:100%;border:0;border-radius:14px;padding:14px 16px;color:#fff;font-weight:800;background:linear-gradient(90deg,#2563eb,#0ea5e9)}.dc-row{display:flex;gap:8px;margin-top:12px}.dc-btn{flex:1;border:1px solid #cbd5e1;background:#fff;border-radius:12px;padding:12px}.dc-empty,.dc-empty-panel{background:#fff;border:1px dashed #cbd5e1;border-radius:16px;padding:20px;color:#334155}.dc-empty-panel{margin:18px}.dc-q label span{display:block}.dc-review-card{background:#fff;border:1px solid #fecaca;border-radius:16px;padding:16px;margin-bottom:16px;box-shadow:0 10px 24px rgba(239,68,68,.08)}.dc-review-title{font-size:22px;font-weight:800;color:#991b1b;margin:0 0 6px}.dc-review-note{margin:0 0 12px;color:#7f1d1d;font-size:14px}.dc-review-list{display:grid;gap:10px}.dc-review-item{border:1px solid #fca5a5;border-radius:14px;padding:12px;background:#fff7f7}.dc-review-q{font-weight:800;color:#111827;margin-bottom:8px}.dc-review-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}.dc-review-grid span{display:block;font-size:12px;color:#991b1b;text-transform:uppercase;letter-spacing:.04em}.dc-review-grid strong{display:block;font-size:15px;color:#111827;word-break:break-word}@media (max-width:1024px){.dc-main{grid-template-columns:1fr}.dc-left{border-right:0;border-bottom:1px solid #e2e8f0}.dc-choice-card{grid-template-columns:24px 26px 1fr;min-height:58px;padding:12px 14px}.dc-choice-text{font-size:15px}.dc-review-grid{grid-template-columns:1fr}}
</style>
<script>
(() => {
  const root = document.querySelector('[data-dc-root]');
  if (!root) return;

  const slides = JSON.parse(root.dataset.lessonSlides || '[]').filter(Boolean);
  const questionCount = Number(root.dataset.questionCount || 0);
  const lessonStage = document.getElementById('lessonStage');
  const quizStage = document.getElementById('quizStage');
  const emptyQuizStage = document.getElementById('emptyQuizStage');
  const lessonText = document.getElementById('lessonText');
  const lessonStepIndex = document.getElementById('lessonStepIndex');
  const lessonStepTotal = document.getElementById('lessonStepTotal');
  const lessonPrevBtn = document.getElementById('lessonPrevBtn');
  const lessonNextBtn = document.getElementById('lessonNextBtn');
  const quizForm = document.getElementById('quizForm');
  const durationInput = document.getElementById('durationSecondsInput');
  const quizInputs = quizForm ? Array.from(quizForm.querySelectorAll('input, textarea, select')) : [];

  const state = { index: 0, startedAt: Date.now(), mode: 'lesson' };

  const render = () => {
    if (!lessonText || !lessonStepIndex || !lessonStepTotal || !lessonPrevBtn || !lessonNextBtn) return;

    if (slides.length === 0) {
      lessonText.textContent = 'Konu anlat?m? haz?rlan?yor.';
      lessonStepIndex.textContent = '1';
      lessonStepTotal.textContent = '1';
      lessonPrevBtn.disabled = true;
      lessonNextBtn.textContent = questionCount > 0 ? 'Sorulara Ge&ccedil;' : 'Konu Tamamland?';
      return;
    }

    lessonText.textContent = slides[state.index] || slides[slides.length - 1];
    lessonStepIndex.textContent = String(state.index + 1);
    lessonStepTotal.textContent = String(slides.length);
    lessonPrevBtn.disabled = state.index === 0;
    lessonNextBtn.textContent = state.index < slides.length - 1 ? '?lerle' : (questionCount > 0 ? 'Sorulara Ge&ccedil;' : 'Konu Tamamland?');
  };

  const showQuiz = () => {
    state.mode = 'quiz';
    if (lessonStage) lessonStage.classList.add('dc-stage-hidden');
    if (questionCount > 0 && quizStage) {
      quizStage.classList.remove('dc-stage-hidden');
      if (emptyQuizStage) emptyQuizStage.classList.add('dc-stage-hidden');
      const firstInput = quizForm?.querySelector('input, textarea, select');
      if (firstInput) firstInput.focus();
      return;
    }

    if (emptyQuizStage) {
      emptyQuizStage.classList.remove('dc-stage-hidden');
    } else if (quizStage) {
      quizStage.classList.remove('dc-stage-hidden');
    }
  };

  const goNext = () => {
    if (state.index < slides.length - 1) {
      state.index += 1;
      render();
      return;
    }
    if (questionCount > 0) {
      showQuiz();
    } else if (lessonNextBtn) {
      lessonNextBtn.disabled = true;
    }
  };

  lessonPrevBtn?.addEventListener('click', () => {
    if (state.index > 0) {
      state.index -= 1;
      render();
    }
  });

  lessonNextBtn?.addEventListener('click', goNext);

  document.querySelectorAll('[data-choice-group]').forEach((group) => {
    group.addEventListener('change', () => {
      group.querySelectorAll('[data-choice-card]').forEach((card) => {
        const input = card.querySelector('input');
        card.classList.toggle('is-selected', !!input?.checked);
      });
    });
    group.addEventListener('click', (event) => {
      const card = event.target.closest('[data-choice-card]');
      const input = card?.querySelector('input');
      if (input && event.target !== input) input.click();
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    const activeTag = (document.activeElement?.tagName || '').toLowerCase();
    if (state.mode === 'lesson') {
      if (activeTag === 'textarea') return;
      event.preventDefault();
      goNext();
      return;
    }
    if (state.mode === 'quiz' && quizForm) {
      if (activeTag === 'textarea') return;
      event.preventDefault();
      const focusable = quizInputs.filter((el) => !el.disabled && el.offsetParent !== null);
      const index = focusable.indexOf(document.activeElement);
      if (index >= 0 && index < focusable.length - 1) {
        focusable[index + 1].focus();
        return;
      }
      quizForm.requestSubmit();
    }
  });

  if (quizForm && durationInput) {
    quizForm.addEventListener('submit', () => {
      durationInput.value = String(Math.floor((Date.now() - state.startedAt) / 1000));
    });
  }

  render();
})();
</script>
@endsection
