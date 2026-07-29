@extends('layout.app')
@section('title','Günlük Çalışmalar Yönetimi')
@section('content')
<style>
.cam-wrap{max-width:1280px;margin:0 auto;padding:16px}.cam-hero{border-radius:18px;padding:18px 20px;color:#fff;background:linear-gradient(120deg,#0ea5e9,#2563eb)}.cam-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:16px;margin-top:16px}.cam-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px}.cam-title{font-size:20px;font-weight:800}.cam-inp,.cam-sel,.cam-txt{width:100%;border:1px solid #cbd5e1;border-radius:12px;padding:11px 12px}.cam-txt{min-height:92px}.cam-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}.cam-btn{border:0;border-radius:12px;padding:11px 14px;color:#fff;font-weight:700;background:linear-gradient(90deg,#2563eb,#0ea5e9)}.cam-item{display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px}.cam-pill{font-size:12px;padding:3px 8px;border-radius:999px;background:#f1f5f9}.btn-lite{padding:8px 10px;border-radius:10px;border:1px solid #cbd5e1;background:#fff}.icon-btn{width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid #cbd5e1;background:#fff;flex:0 0 auto}.icon-btn svg{width:20px;height:20px;display:block}.icon-btn.edit{color:#2563eb}.icon-btn.play{color:#0f766e}.icon-btn.delete{color:#dc2626;border-color:#fecaca}.activity-actions{display:flex;gap:8px;flex-wrap:nowrap;align-items:center;justify-content:flex-end;white-space:nowrap;flex-shrink:0}.activity-meta{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:4px;min-width:0}.activity-left{min-width:0;flex:1 1 auto}.activity-title{font-weight:700;white-space:normal;word-break:break-word}.qcard{border:1px solid #dbeafe;border-radius:12px;padding:12px;margin-top:10px;background:#f8fbff}.qsub{margin-top:10px;padding:12px;border:1px dashed #cbd5e1;border-radius:12px;background:#fff}.qopt{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center;margin-top:8px}.qopt label{display:flex;gap:8px;align-items:center;font-size:14px}.qopt input[type=text]{width:100%}.q-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}.muted{color:#64748b;font-size:13px}.hidden{display:none}.q-stack{display:grid;gap:10px}.q-type-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.q-type-tab{padding:8px 10px;border:1px solid #cbd5e1;border-radius:999px;background:#fff;cursor:pointer}.q-type-tab.active{background:#eff6ff;border-color:#60a5fa;color:#1d4ed8}.correct-badge{padding:4px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:700}.wrong-badge{padding:4px 8px;border-radius:999px;background:#fee2e2;color:#991b1b;font-size:12px;font-weight:700}@media(max-width:1024px){.cam-grid{grid-template-columns:1fr}.cam-row{grid-template-columns:1fr}}</style>
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
<div class="cam-hero" style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
    <div>
        <div style="font-size:13px">Admin / Öğretmen Paneli</div>
        <h1 style="margin:4px 0 0;font-size:30px">Günlük Çalışmalar Yönetimi</h1>
    </div>
    @if(auth()->user()?->hasRole('admin'))
    <button type="button" id="activity-bulk-assign-open" class="btn-lite" style="height:44px;padding:0 16px;border-color:rgba(255,255,255,.45);background:rgba(255,255,255,.14);color:#fff;font-weight:800;">Öğretmene Ata</button>
    @endif
</div>
<div class="cam-grid">
<section class="cam-card">
<div class="cam-title">{{ $editingActivity ? 'Etkinlik Düzenle' : 'Yeni Etkinlik Oluştur' }}</div>
@if($editingActivity)
<div style="display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 0">
    <a class="btn-lite" href="{{ route('coding.activities.manage') }}">Yeni Kayıt Moduna Dön</a>
    @php($editingIsLocked = (bool) ($editingActivity->admin_locked ?? false))
    @if(!auth()->user()?->hasRole('admin') || (empty($editingActivity->teacher_id) && ! $editingIsLocked))
        <form method="POST" action="{{ route('coding.activities.destroy', $editingActivity) }}" data-confirm="Bu etkinliği silmek istiyor musunuz?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-lite" style="border-color:#fecaca;color:#991b1b">Sil</button>
        </form>
    @endif
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
@php($activityLocked = (bool) ($activity->admin_locked ?? false))
<div class="cam-item"><div><div style="font-weight:700">{{ $activity->title }}</div></div><div class="activity-actions"><a class="icon-btn edit" href="{{ route('coding.activities.manage',['edit'=>$activity->id]) }}" title="Düzenle" aria-label="Düzenle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a><form method="POST" action="{{ route('coding.activities.assign.today', $activity) }}">@csrf <button class="icon-btn play" type="submit" title="Bugüne Ata" aria-label="Bugüne Ata"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button></form>@if(!auth()->user()?->hasRole('admin') || (empty($activity->teacher_id) && ! $activityLocked))
            <form method="POST" action="{{ route('coding.activities.destroy', $activity) }}" data-confirm="Bu etkinliği silmek istiyor musunuz?">@csrf @method('DELETE') <button class="icon-btn delete" type="submit" title="Sil" aria-label="Sil"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button></form>
        @endif</div></div>
@endforeach
</div>
<div style="margin-top:10px">{{ $activities->links() }}</div>
</section>
</div></div>

@if(auth()->user()?->hasRole('admin'))
<div id="activity-bulk-assign-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:3200;padding:16px;">
    <div style="width:min(96vw,920px);max-height:88vh;overflow:hidden;background:#fff;border-radius:18px;padding:18px;box-shadow:0 20px 50px rgba(0,0,0,.18);display:grid;grid-template-rows:auto auto 1fr auto;gap:14px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
            <div>
                <h3 style="margin:0;font-size:22px;font-weight:800;color:#111827;">Toplu Öğretmen Atama</h3>
                <p style="margin:6px 0 0;color:#475569;">Bir öğretmen seçin ve günlük çalışmaları topluca atayın.</p>
            </div>
            <button type="button" id="activity-bulk-assign-close" style="height:40px;padding:0 14px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">Kapat</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end;">
            <div>
                <label for="bulk-activity-teacher" style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Öğretmen Seç</label>
                <select id="bulk-activity-teacher" style="width:100%;height:44px;border:1px solid #cbd5e1;border-radius:12px;padding:0 12px;">
                    <option value="">Öğretmen seçiniz</option>
                    @foreach(($teachers ?? collect()) as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Öğretmen #' . $teacher->id) }}</option>
                    @endforeach
                </select>
            </div>
            <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff;white-space:nowrap;">
                <input type="checkbox" id="bulk-activity-select-all" style="width:auto;margin:0">
                <span>Tümünü seç</span>
            </label>
        </div>
        <div id="bulk-activity-list" style="overflow:auto;border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#f8fafc;">
            <div style="display:grid;gap:10px;">
                @foreach(($assignableActivities ?? collect()) as $activity)
                    <label data-teacher-id="{{ (int) ($activity->teacher_id ?? 0) }}" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
                        <input type="checkbox" class="bulk-activity-checkbox" value="{{ $activity->id }}" style="width:auto;margin:0;">
                        <span style="flex:1 1 auto;font-weight:700;color:#111827;min-width:0;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">{{ $activity->title }}</span>
                        <span style="flex:0 0 auto;padding:5px 10px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;">{{ (int) ($activity->teacher_id ?? 0) > 0 ? 'Atanmış' : 'Boş' }}</span>
                        @if((bool) ($activity->admin_locked ?? false))
                            <span style="flex:0 0 auto;padding:5px 10px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:12px;font-weight:700;">Kilitli</span>
                        @endif
                    </label>
                @endforeach
            </div>
        </div>
        <form id="activity-bulk-assign-form" method="POST" action="{{ route('coding.activities.assign.teacher.bulk') }}" style="display:flex;justify-content:flex-end;gap:10px;align-items:center;">
            @csrf
            <input type="hidden" name="teacher_id" id="bulk-activity-teacher-input">
            <div id="bulk-activity-hidden-inputs"></div>
            <button type="button" id="activity-bulk-assign-cancel" style="height:44px;padding:0 16px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;">İptal</button>
            <button type="submit" style="height:44px;padding:0 16px;border:0;border-radius:12px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">Atamayı Kaydet</button>
        </form>
        <form id="activity-bulk-unassign-form" method="POST" action="{{ route('coding.activities.unassign.teacher.bulk') }}" style="display:none;">
            @csrf
            <input type="hidden" name="teacher_id" id="bulk-activity-unassign-teacher-input">
        </form>
    </div>
</div>
@endif

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

  @if(auth()->user()?->hasRole('admin'))
  const bulkOpenBtn = document.getElementById('activity-bulk-assign-open');
  const bulkModal = document.getElementById('activity-bulk-assign-modal');
  const bulkCloseBtn = document.getElementById('activity-bulk-assign-close');
  const bulkCancelBtn = document.getElementById('activity-bulk-assign-cancel');
  const bulkTeacherSelect = document.getElementById('bulk-activity-teacher');
  const bulkTeacherInput = document.getElementById('bulk-activity-teacher-input');
  const bulkList = document.getElementById('bulk-activity-list');
  const bulkHiddenInputs = document.getElementById('bulk-activity-hidden-inputs');
  const bulkSelectAll = document.getElementById('bulk-activity-select-all');
  const bulkForm = document.getElementById('activity-bulk-assign-form');
  const bulkUnassignForm = document.getElementById('activity-bulk-unassign-form');
  const bulkUnassignTeacherInput = document.getElementById('bulk-activity-unassign-teacher-input');
  const bulkUnassignOpenBtn = document.getElementById('activity-bulk-unassign-open');

  const syncBulkHiddenInputs = () => {
    if (bulkTeacherInput) {
      bulkTeacherInput.value = bulkTeacherSelect?.value || '';
    }
    if (bulkHiddenInputs) {
      bulkHiddenInputs.innerHTML = '';
      document.querySelectorAll('.bulk-activity-checkbox:checked').forEach((checkbox) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'activity_ids[]';
        input.value = checkbox.value;
        bulkHiddenInputs.appendChild(input);
      });
    }
  };

  const syncBulkSelectionFromTeacher = () => {
    const teacherId = bulkTeacherSelect?.value || '';
    document.querySelectorAll('.bulk-activity-checkbox').forEach((checkbox) => {
      const label = checkbox.closest('label');
      const assignedTeacherId = label ? String(label.dataset.teacherId || '') : '';
      checkbox.checked = teacherId !== '' && assignedTeacherId === teacherId;
    });
    if (bulkSelectAll) {
      const total = document.querySelectorAll('.bulk-activity-checkbox').length;
      const checked = document.querySelectorAll('.bulk-activity-checkbox:checked').length;
      bulkSelectAll.checked = total > 0 && total === checked;
    }
    syncBulkHiddenInputs();
  };

  bulkOpenBtn?.addEventListener('click', () => {
    bulkModal.style.display = 'flex';
  });
  const closeBulkModal = () => {
    if (bulkModal) bulkModal.style.display = 'none';
  };
  bulkCloseBtn?.addEventListener('click', closeBulkModal);
  bulkCancelBtn?.addEventListener('click', closeBulkModal);
  bulkModal?.addEventListener('click', (event) => {
    if (event.target === bulkModal) closeBulkModal();
  });
  bulkTeacherSelect?.addEventListener('change', syncBulkHiddenInputs);
  bulkTeacherSelect?.addEventListener('change', syncBulkSelectionFromTeacher);
  syncBulkSelectionFromTeacher();
  bulkSelectAll?.addEventListener('change', () => {
    const checked = !!bulkSelectAll.checked;
    document.querySelectorAll('.bulk-activity-checkbox').forEach((checkbox) => {
      checkbox.checked = checked;
    });
    syncBulkHiddenInputs();
  });
  bulkList?.addEventListener('change', (event) => {
    if (event.target.closest('.bulk-activity-checkbox')) {
      syncBulkHiddenInputs();
      const total = document.querySelectorAll('.bulk-activity-checkbox').length;
      const checked = document.querySelectorAll('.bulk-activity-checkbox:checked').length;
      if (bulkSelectAll) bulkSelectAll.checked = total > 0 && total === checked;
    }
  });
  bulkForm?.addEventListener('submit', (event) => {
    syncBulkHiddenInputs();
    if (!bulkTeacherSelect?.value || document.querySelectorAll('.bulk-activity-checkbox:checked').length === 0) {
      event.preventDefault();
      alert('Lütfen bir öğretmen ve en az bir günlük çalışma seçin.');
    }
  });

  bulkUnassignOpenBtn?.addEventListener('click', () => {
    const teacherId = bulkTeacherSelect?.value || '';
    if (!teacherId) {
      alert('Lütfen önce bir öğretmen seçin.');
      return;
    }
    if (!confirm('Seçili öğretmenden tüm atanmış günlük çalışmalar kaldırılacak. Devam edilsin mi?')) {
      return;
    }
    if (bulkUnassignTeacherInput) {
      bulkUnassignTeacherInput.value = teacherId;
    }
    bulkUnassignForm?.submit();
  });
  @endif

  render();
})();
</script>
@endsection

