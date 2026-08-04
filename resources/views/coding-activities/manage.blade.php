@extends('layout.app')
@section('title','Günlük Çalışmalar Yönetimi')
@section('content')
@style
<style>
  .cam-wrap{max-width:1280px;margin:0 auto;padding:16px}
  .cam-hero{border-radius:18px;padding:18px 20px;color:#fff;background:linear-gradient(120deg,#0ea5e9,#2563eb);display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start}
  .cam-stack{display:grid;gap:16px;margin-top:16px}
  .cam-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px;min-width:0}
  .cam-title{font-size:20px;font-weight:800}
  .cam-inp,.cam-sel,.cam-txt{width:100%;border:1px solid #cbd5e1;border-radius:12px;padding:11px 12px;box-sizing:border-box;background:#fff}
  .cam-txt{min-height:92px;resize:vertical}
  .cam-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .cam-btn{border:0;border-radius:12px;padding:11px 14px;color:#fff;font-weight:700;background:linear-gradient(90deg,#2563eb,#0ea5e9)}
  .cam-item{display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px;min-width:0}
  .btn-lite{padding:8px 10px;border-radius:10px;border:1px solid #cbd5e1;background:#fff}
  .icon-btn{width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid #cbd5e1;background:#fff;flex:0 0 auto}
  .icon-btn svg{width:20px;height:20px;display:block}
  .icon-btn.edit{color:#2563eb}.icon-btn.delete{color:#dc2626;border-color:#fecaca}
  .q-stack{display:grid;gap:10px}
  .qcard{border:1px solid #dbeafe;border-radius:12px;padding:12px;background:#f8fbff}
  .qgrid{display:grid;grid-template-columns:1.2fr .7fr auto;gap:8px}
  .activity-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;align-items:center}
  @media(max-width: 900px){
    .cam-row,.qgrid{grid-template-columns:1fr}
  }
</style>
@endstyle
@php
  $initialQuestions = old('questions', $editingActivity
    ? $editingActivity->questions->map(fn ($q) => [
        'prompt' => $q->prompt,
        'question_type' => $q->question_type,
        'points' => $q->points,
        'answer' => data_get($q->answer_key, 'answer', ''),
        'options' => $q->options->pluck('label')->values()->all(),
        'correct_options' => $q->options->where('is_correct', true)->pluck('option_key')->values()->all(),
      ])->values()->all()
    : []);
@endphp

<div class="cam-wrap" data-question-builder data-initial-questions='@json($initialQuestions)'>
  <div class="cam-hero">
    <div>
      <div style="font-size:13px">Admin / Öğretmen Paneli</div>
      <h1 style="margin:4px 0 0;font-size:30px">Günlük Çalışmalar Yönetimi</h1>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;align-items:center;">
      @if(auth()->user()?->hasRole('admin'))
        <a class="btn-lite" href="{{ route('coding.activities.export.all') }}" style="height:44px;padding:0 16px;border-color:rgba(255,255,255,.45);background:rgba(255,255,255,.14);color:#fff;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;">Tümünü İndir</a>
        <form id="daily-import-form" method="POST" action="{{ route('coding.activities.import') }}" enctype="multipart/form-data" style="display:inline-flex;align-items:center;gap:8px;">
          @csrf
          <input id="daily-import-input" type="file" name="activity_json[]" accept=".json,.txt,application/json,text/plain" multiple style="display:none">
          <button type="button" id="daily-import-open" class="btn-lite" style="height:44px;padding:0 16px;border-color:rgba(255,255,255,.45);background:rgba(255,255,255,.14);color:#fff;font-weight:800;">Yükle</button>
        </form>
      @endif
    </div>
  </div>

  <div class="cam-stack">
    @if(auth()->user()?->hasRole('admin'))
      <section class="cam-card">
        <div class="cam-title">Yeni Etkinlik Oluştur</div>
        <form method="POST" action="{{ $editingActivity ? route('coding.activities.update',$editingActivity) : route('coding.activities.store') }}">
          @csrf
          @if($editingActivity) @method('PUT') @endif
          <div class="cam-row" style="margin-top:12px">
            <div>
              <label style="display:block;font-weight:700;margin-bottom:6px">Etkinlik Başlığı</label>
              <input name="title" class="cam-inp" placeholder="Etkinlik başlığı" value="{{ old('title',$editingActivity->title ?? '') }}" required>
            </div>
            <div>
              <label style="display:block;font-weight:700;margin-bottom:6px">Tür</label>
              <select name="type" class="cam-sel">
                @foreach(['daily_task'=>'Günlük Görev','quiz'=>'Quiz','race'=>'Yarış','live_quiz'=>'Canlı Quiz'] as $k=>$v)
                  <option value="{{ $k }}" @selected(old('type',$editingActivity->type ?? 'daily_task')===$k)>{{ $v }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div style="margin-top:12px">
            <label style="display:block;font-weight:700;margin-bottom:6px">Kısa Konu Özeti</label>
            <textarea name="instruction" class="cam-txt" placeholder="Kısa konu özeti">{{ old('instruction',$editingActivity->instruction ?? '') }}</textarea>
          </div>

          @php($lp = old('lesson_pages', $editingActivity->lesson_pages ?? ['', '', '']))
          <div class="cam-row" style="margin-top:12px">
            <div>
              <label style="display:block;font-weight:700;margin-bottom:6px">Hap Bilgi 1</label>
              <textarea name="lesson_pages[]" class="cam-txt">{{ $lp[0] ?? '' }}</textarea>
            </div>
            <div>
              <label style="display:block;font-weight:700;margin-bottom:6px">Hap Bilgi 2</label>
              <textarea name="lesson_pages[]" class="cam-txt">{{ $lp[1] ?? '' }}</textarea>
            </div>
          </div>
          <div class="cam-row" style="margin-top:12px">
            <div>
              <label style="display:block;font-weight:700;margin-bottom:6px">Hap Bilgi 3</label>
              <textarea name="lesson_pages[]" class="cam-txt">{{ $lp[2] ?? '' }}</textarea>
            </div>
            <div style="display:grid;gap:10px;align-content:start">
              <div>
                <label style="display:block;font-weight:700;margin-bottom:6px">Başlangıç XP</label>
                <input type="number" name="base_xp" class="cam-inp" value="{{ old('base_xp',$editingActivity->base_xp ?? 20) }}">
              </div>
              <label style="display:flex;align-items:center;gap:8px">
                <input type="checkbox" name="is_random_pool" value="1" @checked(old('is_random_pool',$editingActivity->is_random_pool ?? true))>
                Random havuza dahil et
              </label>
            </div>
          </div>

          <div style="margin-top:12px" class="cam-title">Sorular</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin:10px 0">
            <button type="button" class="btn-lite" id="addQuestionBtn">+ Soru Ekle</button>
          </div>
          <div id="questionsContainer" class="q-stack"></div>
          <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
            <button type="submit" class="cam-btn">{{ $editingActivity ? 'Güncelle' : 'Kaydet' }}</button>
          </div>
        </form>
      </section>
    @endif

    <section class="cam-card">
      <div class="cam-title">Eklenmiş Günlük Çalışmalar</div>
      <div style="display:grid;gap:10px;margin-top:12px">
        @forelse($activities as $activity)
          <div class="cam-item">
            <div style="font-weight:700;min-width:0;word-break:break-word">{{ $activity->title }}</div>
            <div class="activity-actions">
              <a class="icon-btn edit" href="{{ route('coding.activities.manage',['edit'=>$activity->id]) }}" title="Düzenle" aria-label="Düzenle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
              </a>
              @if(auth()->user()?->hasRole('admin') || (auth()->user()?->hasRole('teacher') && !empty($activity->teacher_id) && (int) optional(auth()->user()?->teacher)->id === (int) $activity->teacher_id))
                <form method="POST" action="{{ route('coding.activities.destroy', $activity) }}" data-confirm="Bu etkinliği silmek istiyor musunuz?">@csrf @method('DELETE') <button class="icon-btn delete" type="submit" title="Sil" aria-label="Sil"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button></form>
              @endif
            </div>
          </div>
        @empty
          <div style="padding:16px;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;color:#475569;">Henüz günlük çalışma eklenmemiş.</div>
        @endforelse
      </div>
      <div style="margin-top:12px">{{ $activities->links() }}</div>
    </section>
  </div>
</div>

<script>
(() => {
  const root = document.querySelector('[data-question-builder]');
  if (!root) return;
  const container = document.getElementById('questionsContainer');
  const addBtn = document.getElementById('addQuestionBtn');
  const initial = JSON.parse(root.dataset.initialQuestions || '[]');
  const questions = Array.isArray(initial) && initial.length ? initial : [];

  const renderQuestion = (index, q = {}) => {
    const wrap = document.createElement('div');
    wrap.className = 'qcard';
    wrap.innerHTML = `
      <div class="qgrid">
        <div>
          <label style="display:block;font-weight:700;margin-bottom:6px">Soru</label>
          <input class="cam-inp" name="questions[${index}][prompt]" value="${String(q.prompt ?? '').replaceAll('"','&quot;')}" placeholder="Soru metni">
        </div>
        <div>
          <label style="display:block;font-weight:700;margin-bottom:6px">Soru Türü</label>
          <select class="cam-sel" name="questions[${index}][question_type]">
            <option value="single_choice" ${q.question_type === 'single_choice' ? 'selected' : ''}>Tek Seçim</option>
            <option value="multi_choice" ${q.question_type === 'multi_choice' ? 'selected' : ''}>Çoklu Seçim</option>
            <option value="short_text" ${q.question_type === 'short_text' ? 'selected' : ''}>Kısa Metin</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-weight:700;margin-bottom:6px">Puan</label>
          <input type="number" class="cam-inp" name="questions[${index}][points]" value="${Number(q.points ?? 10)}">
        </div>
      </div>
      <div style="margin-top:10px">
        <label style="display:block;font-weight:700;margin-bottom:6px">Beklenen Cevap</label>
        <input class="cam-inp" name="questions[${index}][answer]" value="${String(q.answer ?? '').replaceAll('"','&quot;')}" placeholder="Beklenen cevap">
      </div>
    `;
    return wrap;
  };

  const refresh = () => {
    if (!container) return;
    container.innerHTML = '';
    const list = questions.length ? questions : [{}];
    list.forEach((q, index) => container.appendChild(renderQuestion(index, q)));
  };

  addBtn?.addEventListener('click', () => {
    questions.push({});
    refresh();
  });

  refresh();
})();
</script>
@endsection
