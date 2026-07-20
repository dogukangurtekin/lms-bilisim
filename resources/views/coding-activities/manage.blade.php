@extends('layout.app')
@section('title','Günlük Çalışmalar Yönetimi')
@section('content')
<style>
.cam-wrap{max-width:1280px;margin:0 auto;padding:16px}.cam-hero{border-radius:18px;padding:18px 20px;color:#fff;background:linear-gradient(120deg,#0ea5e9,#2563eb)}.cam-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:16px;margin-top:16px}.cam-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px}.cam-title{font-size:20px;font-weight:800}.cam-inp,.cam-sel,.cam-txt{width:100%;border:1px solid #cbd5e1;border-radius:12px;padding:11px 12px}.cam-txt{min-height:92px}.cam-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}.cam-btn{border:0;border-radius:12px;padding:11px 14px;color:#fff;font-weight:700;background:linear-gradient(90deg,#2563eb,#0ea5e9)}.cam-item{display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px}.cam-pill{font-size:12px;padding:3px 8px;border-radius:999px;background:#f1f5f9}.btn-lite{padding:8px 10px;border-radius:10px;border:1px solid #cbd5e1;background:#fff}.qcard{border:1px solid #dbeafe;border-radius:12px;padding:12px;margin-top:10px;background:#f8fbff}.qsub{margin-top:10px;padding:12px;border:1px dashed #cbd5e1;border-radius:12px;background:#fff}.qopt{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center;margin-top:8px}.qopt label{display:flex;gap:8px;align-items:center;font-size:14px}.qopt input[type=text]{width:100%}.q-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}.muted{color:#64748b;font-size:13px}.hidden{display:none}.q-stack{display:grid;gap:10px}.q-type-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.q-type-tab{padding:8px 10px;border:1px solid #cbd5e1;border-radius:999px;background:#fff;cursor:pointer}.q-type-tab.active{background:#eff6ff;border-color:#60a5fa;color:#1d4ed8}.correct-badge{padding:4px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:700}.wrong-badge{padding:4px 8px;border-radius:999px;background:#fee2e2;color:#991b1b;font-size:12px;font-weight:700}@media(max-width:1024px){.cam-grid{grid-template-columns:1fr}.cam-row{grid-template-columns:1fr}}</style>
@php
    $initialQuestions = old('questions', $editingActivity
        ? $editingActivity->questions->map(function ($q) {
            return [
                'prompt' => $q->prompt,
                'question_type' => $q->question_type,
                'points' => $q->points,
                'answer' => data_get($q->answer_key, 'answer', ''),
                'options' => $q->options->pluck('label')->values()->all(),
                'correct_options' => $q->options->where('is_correct', true)->pluck('option_key')->values()->all(),
            ];
        })->values()->all()
        : []
    );
@endphp
<div class="cam-wrap" data-question-builder data-initial-questions='@json($initialQuestions)'>
<div class="cam-hero"><div style="font-size:13px">Admin / Öğretmen Paneli</div><h1 style="margin:4px 0 0;font-size:30px">Günlük Çalışmalar Yönetimi</h1></div>
<div class="cam-grid">
<section class="cam-card">
<div class="cam-title">{{ $editingActivity ? 'Etkinlik Düzenle' : 'Yeni Etkinlik Oluştur' }}</div>
@if($editingActivity)
<div style="display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 0">
    <a class="btn-lite" href="{{ route('coding.activities.manage') }}">Yeni Kayıt Moduna Dön</a>
    <form method="POST" action="{{ route('coding.activities.destroy', $editingActivity) }}" data-confirm="Bu etkinliği silmek istiyor musunuz?">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-lite" style="border-color:#fecaca;color:#991b1b">Sil</button>
    </form>
</div>
@endif
<form method="POST" action="{{ $editingActivity ? route('coding.activities.update',$editingActivity) : route('coding.activities.store') }}" class="space-y-3">
@csrf
@if($editingActivity) @method('PUT') @endif
<div class="cam-row">
<input name="title" class="cam-inp" placeholder="Etkinlik başlığı" value="{{ old('title',$editingActivity->title ?? '') }}" required>
<select name="type" class="cam-sel">
@foreach(['daily_task'=>'Günlük Görev','quiz'=>'Quiz','race'=>'Yarış','live_quiz'=>'Canlı Quiz'] as $k=>$v)
<option value="{{ $k }}" @selected(old('type',$editingActivity->type ?? 'daily_task')===$k)>{{ $v }}</option>
@endforeach
</select></div>
<textarea name="instruction" class="cam-txt" placeholder="Kısa konu özeti">{{ old('instruction',$editingActivity->instruction ?? '') }}</textarea>
@php($lp = old('lesson_pages', $editingActivity->lesson_pages ?? ['', '', '']))
<div class="cam-row"><textarea name="lesson_pages[]" class="cam-txt" placeholder="Hap bilgi sayfa 1">{{ $lp[0] ?? '' }}</textarea><textarea name="lesson_pages[]" class="cam-txt" placeholder="Hap bilgi sayfa 2">{{ $lp[1] ?? '' }}</textarea></div>
<div class="cam-row"><textarea name="lesson_pages[]" class="cam-txt" placeholder="Hap bilgi sayfa 3">{{ $lp[2] ?? '' }}</textarea><div style="display:grid;gap:10px;align-content:start"><input type="number" name="base_xp" class="cam-inp" value="{{ old('base_xp',$editingActivity->base_xp ?? 20) }}"><label><input type="checkbox" name="is_random_pool" value="1" @checked(old('is_random_pool',$editingActivity->is_random_pool ?? true))> Random havuza dahil et</label></div></div>

<div class="cam-title" style="margin-top:12px">Sorular</div>
<div class="q-actions">
    <button type="button" class="btn-lite" id="addQuestionBtn">+ Soru Ekle</button>
</div>
<div id="questionsContainer" class="q-stack"></div>
<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
    <button type="submit" class="cam-btn">{{ $editingActivity ? 'Güncelle' : 'Kaydet' }}</button>
</div>
</form>
</section>
<section class="cam-card">
<div class="cam-title">Bugünkü Atama</div>
<p>{{ $todayAssignment?->activity?->title ? 'Atanan içerik: '.$todayAssignment->activity->title : 'Henüz atama yok.' }}</p>
<div style="display:grid;gap:10px">
@foreach($activities as $activity)
<div class="cam-item"><div><div style="font-weight:700">{{ $activity->title }}</div><div style="margin-top:4px"><span class="cam-pill">{{ strtoupper($activity->type) }}</span> <span class="cam-pill">{{ $activity->questions_count ?? 0 }} soru</span></div></div><div style="display:flex;gap:8px;flex-wrap:wrap"><a class="btn-lite" href="{{ route('coding.activities.manage',['edit'=>$activity->id]) }}">Düzenle</a><form method="POST" action="{{ route('coding.activities.assign.today', $activity) }}">@csrf <button class="cam-btn" type="submit" style="padding:8px 10px">Bugüne Ata</button></form><form method="POST" action="{{ route('coding.activities.destroy', $activity) }}" data-confirm="Bu etkinliği silmek istiyor musunuz?">@csrf @method('DELETE') <button class="btn-lite" type="submit" style="border-color:#fecaca;color:#991b1b">Sil</button></form></div></div>
@endforeach
</div>
<div style="margin-top:10px">{{ $activities->links() }}</div>
</section>
</div></div>
<script>
(() => {
  const root = document.querySelector('[data-question-builder]');
  if (!root) return;
  const container = document.getElementById('questionsContainer');
  const addBtn = document.getElementById('addQuestionBtn');
  const initial = JSON.parse(root.dataset.initialQuestions || '[]');
  const questions = Array.isArray(initial) ? initial : [];

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));

  const getTypeHtml = (q, qi) => {
    const type = q.question_type || 'single_choice';
    if (type === 'single_choice' || type === 'multi_choice') {
      const options = Array.isArray(q.options) && q.options.length ? q.options : ['', '', '', ''];
      return `
        <div class="qsub">
          <div class="muted">Şıkları ekle, ardından doğru şıkları işaretle.</div>
          <div data-options-area="${qi}"></div>
          <div class="q-actions">
            <button type="button" class="btn-lite" data-add-option="${qi}">+ Şık Ekle</button>
          </div>
          ${ (q.correct_options || []).map((co) => `<input type="hidden" name="questions[${qi}][correct_options][]" value="${escapeHtml(co)}">`).join('') }
        </div>`;
    }
    if (type === 'short_text' || type === 'code_output') {
      return `
        <div class="qsub">
          <div class="muted">Beklenen cevap alanını doldur.</div>
          <input class="cam-inp" name="questions[${qi}][answer]" value="${escapeHtml(q.answer || '')}" placeholder="Beklenen cevap">
        </div>`;
    }
    return '';
  };

  const renderOptions = (q, qi) => {
    const area = container.querySelector(`[data-options-area="${qi}"]`);
    if (!area) return;
    const options = Array.isArray(q.options) && q.options.length ? q.options : ['', '', '', ''];
    area.innerHTML = options.map((opt, oi) => {
      const key = String.fromCharCode(65 + oi);
      const checked = (q.correct_options || []).includes(key) ? 'checked' : '';
      const inputType = q.question_type === 'single_choice' ? 'radio' : 'checkbox';
      return `
        <div class="qopt">
          <input class="cam-inp" name="questions[${qi}][options][${oi}]" value="${escapeHtml(opt)}" placeholder="Şık ${key}">
          <label>
            <input type="${inputType}" data-correct-option="${qi}-${oi}" ${checked}>
            Doğru
          </label>
        </div>`;
    }).join('');
  };

  const syncQuestionFromDom = (qi) => {
    const card = container.querySelector(`[data-question-card="${qi}"]`);
    if (!card || !questions[qi]) return;
    const prompt = card.querySelector(`input[name="questions[${qi}][prompt]"]`);
    const type = card.querySelector(`select[name="questions[${qi}][question_type]"]`);
    const points = card.querySelector(`input[name="questions[${qi}][points]"]`);
    questions[qi].prompt = prompt?.value ?? questions[qi].prompt ?? '';
    questions[qi].question_type = type?.value ?? questions[qi].question_type ?? 'single_choice';
    questions[qi].points = points?.value ?? questions[qi].points ?? 10;

    if (questions[qi].question_type === 'short_text' || questions[qi].question_type === 'code_output') {
      const answer = card.querySelector(`input[name="questions[${qi}][answer]"]`);
      questions[qi].answer = answer?.value ?? questions[qi].answer ?? '';
      questions[qi].options = [];
      questions[qi].correct_options = [];
      return;
    }

    const optionInputs = Array.from(card.querySelectorAll(`input[name^="questions[${qi}][options]"]`));
    questions[qi].options = optionInputs.map((input) => input.value ?? '');
  };

  const syncAllQuestionsFromDom = () => {
    questions.forEach((_, qi) => syncQuestionFromDom(qi));
  };

  const render = () => {
    container.innerHTML = questions.map((q, qi) => `
      <div class="qcard" data-question-card="${qi}">
        <div class="cam-row">
          <input class="cam-inp" name="questions[${qi}][prompt]" value="${escapeHtml(q.prompt || '')}" placeholder="Soru metni">
          <select class="cam-sel" name="questions[${qi}][question_type]" data-question-type="${qi}">
            <option value="single_choice" ${q.question_type === 'single_choice' ? 'selected' : ''}>Tek Seçim</option>
            <option value="multi_choice" ${q.question_type === 'multi_choice' ? 'selected' : ''}>Çoklu Seçim</option>
            <option value="short_text" ${q.question_type === 'short_text' ? 'selected' : ''}>Kısa Cevap</option>
            <option value="code_output" ${q.question_type === 'code_output' ? 'selected' : ''}>Kod Çıktısı</option>
          </select>
        </div>
        <div class="cam-row" style="margin-top:8px">
          <input class="cam-inp" type="number" name="questions[${qi}][points]" value="${escapeHtml(q.points ?? 10)}" min="1" placeholder="Puan">
          <button type="button" class="btn-lite" data-remove-question="${qi}">Soruyu Sil</button>
        </div>
        ${getTypeHtml(q, qi)}
      </div>
    `).join('');

    questions.forEach((q, qi) => renderOptions(q, qi));
  };

  const syncCorrectHidden = (qi) => {
    const card = container.querySelector(`[data-question-card="${qi}"]`);
    if (!card) return;
    card.querySelectorAll('input[type="hidden"][name^="questions[' + qi + '][correct_options]"]').forEach((el) => el.remove());
    (questions[qi].correct_options || []).forEach((co) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `questions[${qi}][correct_options][]`;
      input.value = co;
      card.querySelector('.qsub').appendChild(input);
    });
  };

  const addQuestion = () => {
    syncAllQuestionsFromDom();
    questions.push({ prompt: '', question_type: 'single_choice', points: 10, answer: '', options: ['', '', '', ''], correct_options: [] });
    render();
  };

  addBtn.addEventListener('click', addQuestion);

  container.addEventListener('change', (event) => {
    const typeSelect = event.target.closest('[data-question-type]');
    if (typeSelect) {
      const qi = Number(typeSelect.dataset.questionType);
      syncQuestionFromDom(qi);
      questions[qi].question_type = typeSelect.value;
      if (typeSelect.value === 'short_text' || typeSelect.value === 'code_output') {
        questions[qi].correct_options = [];
      }
      render();
      return;
    }

    const correct = event.target.closest('[data-correct-option]');
    if (correct) {
      const qi = Number(correct.dataset.correctOption.split('-')[0]);
      syncQuestionFromDom(qi);
      const [, oiStr] = correct.dataset.correctOption.split('-');
      const oi = Number(oiStr);
      const key = String.fromCharCode(65 + oi);
      const type = questions[qi].question_type;
      if (type === 'single_choice') {
        questions[qi].correct_options = [key];
      } else {
        const list = questions[qi].correct_options || [];
        const idx = list.indexOf(key);
        if (idx === -1) list.push(key);
        else list.splice(idx, 1);
        questions[qi].correct_options = list;
      }
      render();
    }
  });

  container.addEventListener('input', (event) => {
    const promptInput = event.target.closest('input[name$="[prompt]"]');
    const pointsInput = event.target.closest('input[name$="[points]"]');
    const answerInput = event.target.closest('input[name$="[answer]"]');
    const optionInput = event.target.closest('input[name*="[options]"]');
    if (!promptInput && !pointsInput && !answerInput && !optionInput) return;

    const match = (promptInput || pointsInput || answerInput || optionInput).name.match(/^questions\[(\d+)\]/);
    if (!match) return;
    const qi = Number(match[1]);
    syncQuestionFromDom(qi);
  });

  container.addEventListener('click', (event) => {
    const removeBtn = event.target.closest('[data-remove-question]');
    if (removeBtn) {
      const qi = Number(removeBtn.dataset.removeQuestion);
      questions.splice(qi, 1);
      render();
      return;
    }

    const addOptionBtn = event.target.closest('[data-add-option]');
    if (addOptionBtn) {
      const qi = Number(addOptionBtn.dataset.addOption);
      questions[qi].options = questions[qi].options || [];
      questions[qi].options.push('');
      render();
    }
  });

  render();
})();
</script>
@endsection
