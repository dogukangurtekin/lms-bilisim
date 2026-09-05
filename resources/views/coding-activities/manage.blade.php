@extends('layout.app')
@section('title','Günlük Çalışmalar Yönetimi')
@section('content')
<style>
  .panel-section{border:1px solid var(--line,#E4E1D8);border-radius:14px;padding:16px;margin-bottom:16px;background:var(--surface,#fff)}
  .panel-section:last-child{margin-bottom:0}
  .panel-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:0}
  .panel-section-head h3{margin:0;font-family:var(--font-display);font-size:15px;color:var(--ink,#16182B)}
  .panel-section-head p{margin:2px 0 0;font-size:12.5px;color:var(--ink-soft,#585A72)}
  .cam-wrap{max-width:1280px;margin:0 auto;padding:16px}
  .cam-stack{display:grid;gap:16px;margin-top:16px}
  .cam-card{background:var(--surface,#fff);border:1px solid var(--line,#E4E1D8);border-radius:14px;padding:16px;min-width:0}
  .cam-title{font-size:16px;font-weight:700;font-family:var(--font-display);color:var(--ink,#16182B)}
  .cam-inp,.cam-sel,.cam-txt{width:100%;border:1px solid var(--line,#E4E1D8);border-radius:10px;padding:11px 12px;box-sizing:border-box;background:var(--surface,#fff);color:var(--ink,#16182B)}
  .cam-txt{min-height:92px;resize:vertical}
  .cam-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .cam-btn{border:1px solid var(--violet,#5B3DF5);border-radius:8px;padding:11px 14px;color:#fff;font-weight:600;font-family:var(--font-display);background:var(--violet,#5B3DF5)}
  .cam-btn,
  .btn-lite{
    cursor:pointer;
    transition:filter .15s ease;
  }
  .cam-btn:hover,
  .btn-lite:hover{
    filter:brightness(.9);
  }
  .cam-item{display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid var(--line,#E4E1D8);border-radius:12px;padding:10px 12px;min-width:0}
  .cam-layout{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(320px,.95fr);gap:16px;align-items:start;margin-top:16px}
  .btn-lite{padding:8px 10px;border-radius:8px;border:1px solid var(--line,#E4E1D8);background:var(--surface,#fff);color:var(--ink,#16182B);font-family:var(--font-display)}
  .icon-btn{width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;border:1px solid var(--line,#E4E1D8);background:var(--surface,#fff);flex:0 0 auto}
  .icon-btn svg{width:20px;height:20px;display:block}
  .icon-btn.edit{color:var(--violet,#5B3DF5)}.icon-btn.delete{color:var(--signal,#FF7A45);border-color:var(--signal-tint,#FFEEE4)}
  .q-stack{display:grid;gap:10px}
  .qcard{border:1px solid var(--line,#E4E1D8);border-radius:12px;padding:12px;background:var(--violet-tint,#EEEBFD)}
  .qgrid{display:grid;grid-template-columns:1.2fr .7fr auto;gap:8px}
  .activity-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;align-items:center}
  .bulk-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  .bulk-modal{position:fixed;inset:0;z-index:3000;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,.55)}
  .bulk-modal__panel{width:min(96vw,1200px);max-height:92vh;overflow:hidden;background:var(--surface,#fff);border-radius:18px;padding:18px;box-shadow:0 20px 50px rgba(0,0,0,.18);display:grid;grid-template-rows:auto auto minmax(0,1fr) auto;gap:14px}
  .bulk-chip{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;border:1px solid var(--line,#E4E1D8);background:var(--surface,#fff);font-weight:700;font-family:var(--font-display);cursor:pointer}
  @media(max-width: 900px){
    .cam-row,.qgrid{grid-template-columns:1fr}
  }
  @media(max-width: 768px){
    .cam-wrap{padding:10px}
    .cam-layout{grid-template-columns:1fr;gap:12px}
    .cam-card{padding:14px}
    .cam-title{font-size:15px}
    .cam-item{align-items:flex-start}
    .activity-actions{justify-content:flex-start}
    .bulk-modal{padding:10px}
    .bulk-modal__panel{width:100%;max-height:94vh;padding:14px}
    .bulk-modal__panel > div:last-child{flex-direction:column;align-items:stretch}
    .bulk-modal__panel form{width:100%;flex-wrap:wrap}
    .bulk-modal__panel form button{flex:1 1 auto}
  }
  @media(max-width: 560px){
    .top a,.top button{width:100%;justify-content:center}
    .cam-row{gap:8px}
    .cam-item{padding:12px}
    .cam-item > div:first-child{max-width:100%}
    .cam-item > .activity-actions{width:100%;display:flex;justify-content:flex-start}
    .icon-btn{width:40px;height:40px}
    .cam-sel,.cam-inp,.cam-txt{font-size:16px}
    .bulk-chip{width:100%;justify-content:center}
    .bulk-modal__panel{gap:12px}
    .bulk-modal__panel > div:first-child{flex-direction:column}
    .bulk-modal__panel > div:first-child button{align-self:flex-start}
  }
</style>
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
  <div class="top" style="flex-wrap:wrap;gap:12px">
    <div>
      <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-soft,#585A72)">Admin / Öğretmen Paneli</div>
      <h1 style="margin:4px 0 0">Günlük Çalışmalar Yönetimi</h1>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;align-items:center;">
      @if(auth()->user()?->hasRole('admin'))
        <a class="btn-lite" href="{{ route('coding.activities.export.all') }}" style="height:44px;padding:0 16px;text-decoration:none;display:inline-flex;align-items:center;">Tümünü İndir</a>
        <form id="daily-import-form" method="POST" action="{{ route('coding.activities.import') }}" enctype="multipart/form-data" style="display:inline-flex;align-items:center;gap:8px;">
          @csrf
          <input id="daily-import-input" type="file" name="activity_json[]" accept=".json,.txt,application/json,text/plain" multiple style="display:none">
          <button type="button" id="daily-import-open" class="btn-lite" style="height:44px;padding:0 16px;">Yükle</button>
        </form>
      @endif
      @if($isAdmin || $isTeacher)
        <button type="button" class="cam-btn" id="daily-exercise-assign-open" style="height:44px;background:var(--signal,#FF7A45);border-color:var(--signal,#FF7A45);">Egzersiz Ata</button>
      @endif
    </div>
  </div>

  <div id="daily-bulk-modal" class="bulk-modal" aria-hidden="true">
    <div class="bulk-modal__panel">
      <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start">
        <div>
          <h3 style="margin:0;font-size:22px;font-weight:800;color:#111827;">Toplu Günlük Çalışma Atama</h3>
          <p style="margin:6px 0 0;color:#475569;">Derslerdeki atama mantığıyla aynı şekilde çalışır.</p>
        </div>
        <button type="button" class="btn-lite" id="daily-bulk-close" style="height:40px;padding:0 14px;font-weight:700;">Kapat</button>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        @if($isAdmin)
          <button type="button" class="bulk-chip" data-daily-tab="teacher">Öğretmene Atama</button>
        @endif
        <button type="button" class="bulk-chip" data-daily-tab="class">Sınıfa Atama</button>
      </div>
      <div style="display:grid;grid-template-columns:1fr;gap:12px;align-items:start;">
        @if($isAdmin)
          <div id="daily-bulk-teacher-wrap">
            <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Öğretmen Seç</label>
            <select id="daily-bulk-teacher" class="cam-sel">
              <option value="">Öğretmen seçiniz</option>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Öğretmen #'.$teacher->id) }}</option>
              @endforeach
            </select>
          </div>
        @endif
        <div id="daily-bulk-class-wrap">
          <label style="display:block;margin-bottom:6px;font-weight:700;color:#0f172a">Sınıf Seç</label>
          <div style="max-height:280px;overflow:auto;border:1px solid #cbd5e1;border-radius:14px;padding:10px;background:#f8fafc;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;">
              @foreach($availableClasses as $class)
                <label style="display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;cursor:pointer;min-height:72px;">
                  <input type="checkbox" class="daily-class-checkbox" value="{{ $class->id }}" style="margin-top:3px;width:auto;">
                  <span style="display:grid;gap:2px;">
                    <strong style="color:#0f172a;">{{ $class->name }}/{{ $class->section }}</strong>
                    <small style="color:#64748b;">{{ $class->grade_level }}. sınıf</small>
                  </span>
                </label>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      <div id="daily-bulk-list" style="overflow:auto;border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#f8fafc;min-height:0;max-height:40vh;">
        <div style="display:flex;justify-content:flex-end;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap;">
          <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff;white-space:nowrap;">
            <input type="checkbox" id="daily-select-all" style="width:auto;margin:0">
            <span>Tümünü seç</span>
          </label>
        </div>
        <div style="display:grid;gap:10px;">
          @foreach($bulkActivities as $activity)
            <label style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
              <input type="checkbox" class="daily-activity-checkbox" value="{{ $activity->id }}" data-activity-teacher="{{ (int) ($activity->teacher_id ?? 0) }}" style="width:auto;margin:0;">
              <span style="flex:1 1 auto;font-weight:700;color:#111827;min-width:0;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">{{ $activity->title }}</span>
              <span style="flex:0 0 auto;padding:5px 10px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;">{{ (int) ($activity->teacher_id ?? 0) > 0 ? 'Atanmış' : 'Boş' }}</span>
            </label>
          @endforeach
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;align-items:center;flex-wrap:nowrap;white-space:nowrap;">
        <form id="daily-bulk-assign-form" method="POST" action="{{ route('coding.activities.assign-teacher.bulk') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin:0;">
          @csrf
          <input type="hidden" name="teacher_id" id="daily-bulk-teacher-input">
          <div id="daily-bulk-class-hidden-inputs"></div>
          <div id="daily-bulk-activity-hidden-inputs"></div>
          <button type="button" id="daily-bulk-cancel" class="btn-lite" style="height:44px;padding:0 16px;font-weight:700;">İptal</button>
          <button type="submit" class="cam-btn" style="height:44px;padding:0 16px;">Atamayı Kaydet</button>
        </form>
        <form id="daily-bulk-unassign-form" method="POST" action="{{ route('coding.activities.unassign-teacher.bulk') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin:0;">
          @csrf
          <input type="hidden" name="teacher_id" id="daily-bulk-unassign-teacher-input">
          <div id="daily-bulk-unassign-hidden-inputs"></div>
          <button type="submit" style="height:44px;padding:0 16px;border:0;border-radius:12px;background:#f59e0b;color:#fff;font-weight:700;cursor:pointer;">Atamayı Kaldır</button>
        </form>
      </div>
    </div>
  </div>

  <div class="cam-layout">
    @if(auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('teacher'))
      <section class="cam-card" style="margin-top:0">
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

    <section class="cam-card" style="margin-top:0">
      <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
        <div class="cam-title">Eklenmiş Günlük Çalışmalar</div>
        @if($isAdmin)
          <form method="GET" action="{{ route('coding.activities.manage') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            @if(request('edit'))
              <input type="hidden" name="edit" value="{{ request('edit') }}">
            @endif
            <label style="font-weight:700;color:#0f172a;">Filtre</label>
            <select name="creator_id" class="cam-sel" style="min-width:220px;" onchange="this.form.submit()">
              <option value="">Tümü</option>
              @foreach($activityCreators as $creator)
                <option value="{{ $creator->id }}" @selected((int) request('creator_id') === (int) $creator->id)>{{ $creator->name }}</option>
              @endforeach
            </select>
            
          </form>
        @endif
      </div>
      <div style="display:grid;gap:10px;margin-top:12px">
        @forelse($activities as $activity)
          <div class="cam-item">
            <div style="display:grid;gap:4px;min-width:0;">
              <div style="font-weight:700;min-width:0;word-break:break-word">{{ $activity->title }}</div>
              <small style="color:#64748b;">
                Oluşturan:
                {{ $activity->creator?->name ?? 'Bilinmiyor' }}
              </small>
            </div>
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
  const isAdmin = @json($isAdmin);
  const bulkClassAssignmentMap = @json($bulkClassAssignmentMap ?? []);
  const bulkModal = document.getElementById('daily-bulk-modal');
  const bulkClose = document.getElementById('daily-bulk-close');
  const bulkCancel = document.getElementById('daily-bulk-cancel');
  const bulkTeacherWrap = document.getElementById('daily-bulk-teacher-wrap');
  const bulkClassWrap = document.getElementById('daily-bulk-class-wrap');
  const bulkTeacher = document.getElementById('daily-bulk-teacher');
  const bulkTeacherInput = document.getElementById('daily-bulk-teacher-input');
  const bulkUnassignTeacherInput = document.getElementById('daily-bulk-unassign-teacher-input');
  const bulkClassHidden = document.getElementById('daily-bulk-class-hidden-inputs');
  const bulkActivityHidden = document.getElementById('daily-bulk-activity-hidden-inputs');
  const bulkUnassignHidden = document.getElementById('daily-bulk-unassign-hidden-inputs');
  const bulkList = document.getElementById('daily-bulk-list');
  const selectAll = document.getElementById('daily-select-all');
  const openAssignBtn = document.getElementById('daily-exercise-assign-open');
  const bulkTabButtons = Array.from(document.querySelectorAll('[data-daily-tab]'));
  let currentTab = isAdmin ? 'teacher' : 'class';

  const syncHidden = () => {
    bulkActivityHidden && (bulkActivityHidden.innerHTML = '');
    bulkUnassignHidden && (bulkUnassignHidden.innerHTML = '');
    bulkClassHidden && (bulkClassHidden.innerHTML = '');
    document.querySelectorAll('.daily-activity-checkbox:checked').forEach((el) => {
      const a = document.createElement('input');
      a.type = 'hidden';
      a.name = 'activity_ids[]';
      a.value = el.value;
      bulkActivityHidden?.appendChild(a);
      const b = document.createElement('input');
      b.type = 'hidden';
      b.name = 'activity_ids[]';
      b.value = el.value;
      bulkUnassignHidden?.appendChild(b);
    });
    document.querySelectorAll('.daily-class-checkbox:checked').forEach((el) => {
      const a = document.createElement('input');
      a.type = 'hidden';
      a.name = 'class_ids[]';
      a.value = el.value;
      bulkClassHidden?.appendChild(a);
      const b = document.createElement('input');
      b.type = 'hidden';
      b.name = 'class_ids[]';
      b.value = el.value;
      bulkUnassignHidden?.appendChild(b);
    });
    if (bulkTeacherInput) bulkTeacherInput.value = bulkTeacher?.value || '';
    if (bulkUnassignTeacherInput) bulkUnassignTeacherInput.value = bulkTeacher?.value || '';
  };

  const autoSelectActivitiesForClasses = () => {
    const selectedClassIds = Array.from(document.querySelectorAll('.daily-class-checkbox:checked'))
      .map((el) => String(el.value));
    const activityIds = new Set();
    selectedClassIds.forEach((classId) => {
      (bulkClassAssignmentMap[classId] || []).forEach((activityId) => activityIds.add(String(activityId)));
    });
    document.querySelectorAll('.daily-activity-checkbox').forEach((el) => {
      el.checked = activityIds.has(String(el.value));
    });
    syncHidden();
  };

  const setTab = (tab) => {
    currentTab = tab === 'class' ? 'class' : 'teacher';
    if (bulkTeacherWrap) bulkTeacherWrap.style.display = currentTab === 'teacher' && isAdmin ? 'block' : 'none';
    if (bulkClassWrap) bulkClassWrap.style.display = currentTab === 'class' ? 'block' : 'none';
    if (currentTab === 'class') autoSelectActivitiesForClasses();
    bulkTabButtons.forEach((button) => {
      const isActive = (button.dataset.dailyTab || 'teacher') === currentTab;
      button.style.background = isActive ? '#5B3DF5' : '#fff';
      button.style.color = isActive ? '#fff' : '#111827';
      button.style.borderColor = isActive ? '#5B3DF5' : '#E4E1D8';
    });
    if (bulkModal) bulkModal.style.display = 'flex';
  };

  bulkTabButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const tab = button.dataset.dailyBulkOpen || button.dataset.dailyTab || 'teacher';
      setTab(tab);
    });
  });
  openAssignBtn?.addEventListener('click', () => {
    setTab(isAdmin ? 'teacher' : 'class');
  });
  bulkClose?.addEventListener('click', () => { if (bulkModal) bulkModal.style.display = 'none'; });
  bulkCancel?.addEventListener('click', () => { if (bulkModal) bulkModal.style.display = 'none'; });
  bulkModal?.addEventListener('click', (event) => {
    if (event.target === bulkModal) bulkModal.style.display = 'none';
  });
  bulkTeacher?.addEventListener('change', syncHidden);
  document.querySelectorAll('.daily-class-checkbox').forEach((el) => {
    el.addEventListener('change', autoSelectActivitiesForClasses);
  });
  bulkList?.addEventListener('change', syncHidden);
  selectAll?.addEventListener('change', () => {
    document.querySelectorAll('.daily-activity-checkbox').forEach((el) => { el.checked = selectAll.checked; });
    syncHidden();
  });
  document.getElementById('daily-bulk-assign-form')?.addEventListener('submit', (event) => {
    syncHidden();
    const selectedActivities = document.querySelectorAll('.daily-activity-checkbox:checked').length;
    const selectedClasses = document.querySelectorAll('.daily-class-checkbox:checked').length;
    if (selectedActivities === 0) {
      event.preventDefault();
      alert('Lütfen en az bir günlük çalışma seçin.');
      return;
    }
    if (currentTab === 'teacher') {
      if (!bulkTeacher?.value) {
        event.preventDefault();
        alert('Lütfen bir öğretmen seçin.');
        return;
      }
      event.currentTarget.action = '{{ route('coding.activities.assign-teacher.bulk') }}';
    } else {
      if (selectedClasses === 0) {
        event.preventDefault();
        alert('Lütfen en az bir sınıf seçin.');
        return;
      }
      event.currentTarget.action = '{{ route('coding.activities.assign-classes.bulk') }}';
    }
  });
  document.getElementById('daily-bulk-unassign-form')?.addEventListener('submit', (event) => {
    syncHidden();
    const selectedActivities = document.querySelectorAll('.daily-activity-checkbox:checked').length;
    const selectedClasses = document.querySelectorAll('.daily-class-checkbox:checked').length;
    if (selectedActivities === 0) {
      event.preventDefault();
      alert('Lütfen en az bir günlük çalışma seçin.');
      return;
    }
    if (currentTab === 'teacher') {
      if (!bulkTeacher?.value) {
        event.preventDefault();
        alert('Lütfen bir öğretmen seçin.');
        return;
      }
      event.currentTarget.action = '{{ route('coding.activities.unassign-teacher.bulk') }}';
    } else {
      if (selectedClasses === 0) {
        event.preventDefault();
        alert('Lütfen en az bir sınıf seçin.');
        return;
      }
      event.currentTarget.action = '{{ route('coding.activities.unassign-classes.bulk') }}';
    }
  });
  if (bulkModal) bulkModal.style.display = 'none';
  if (bulkTeacherWrap) bulkTeacherWrap.style.display = 'none';
  if (bulkClassWrap && isAdmin) bulkClassWrap.style.display = 'none';
  syncHidden();

  const renderQuestion = (index, q = {}) => {
    const wrap = document.createElement('div');
    wrap.className = 'qcard';
    wrap.innerHTML = `
      <div class="qgrid">
        <div>
          <label style="display:block;font-weight:700;margin-bottom:6px">Soru</label>
          <input class="cam-inp" name="questions[${index}][prompt]" value="${String(q.prompt ?? '').replaceAll('"','"')}" placeholder="Soru metni">
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
        <input class="cam-inp" name="questions[${index}][answer]" value="${String(q.answer ?? '').replaceAll('"','"')}" placeholder="Beklenen cevap">
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