@extends('layout.app')
@section('title','Ödevlerim')
@section('content')
<div class="top"><h1>Ödevlerim</h1></div>
<style>
    .student-assignment-table tbody tr.assignment-pending{background:linear-gradient(90deg,#fff7ed 0%,#fffbeb 100%);box-shadow:inset 5px 0 0 #f97316}
    .student-assignment-table tbody tr.assignment-in-progress{background:#eff6ff;box-shadow:inset 5px 0 0 #3b82f6}
    .assignment-status{display:inline-flex;align-items:center;gap:9px;min-height:44px;padding:8px 13px;border-radius:12px;font-size:14px;font-weight:900;letter-spacing:.01em;white-space:nowrap}
    .assignment-status svg{width:25px;height:25px;flex:0 0 auto;fill:none;stroke:currentColor;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
    .assignment-status--pending{color:#9a3412;background:#ffedd5;border:2px solid #fb923c;box-shadow:0 6px 15px rgba(249,115,22,.18)}
    .assignment-status--progress{color:#1d4ed8;background:#dbeafe;border:2px solid #60a5fa}
    .assignment-status--completed{color:#166534;background:#dcfce7;border:2px solid #4ade80}
    .assignment-new-label{display:inline-flex;align-items:center;margin-left:3px;padding:3px 7px;border-radius:999px;background:#ea580c;color:#fff;font-size:10px;font-weight:900;letter-spacing:.08em;animation:assignment-pulse 1.8s ease-in-out infinite}
    .assignment-title-cell strong{display:block;color:#0f172a;font-size:15px}
    .assignment-title-cell small{display:block;margin-top:4px;color:#c2410c;font-weight:800}
    @keyframes assignment-pulse{0%,100%{box-shadow:0 0 0 0 rgba(234,88,12,.28)}50%{box-shadow:0 0 0 7px rgba(234,88,12,0)}}
    @media (max-width:768px){
        .student-assignment-table thead{display:none}
        .student-assignment-table,
        .student-assignment-table tbody,
        .student-assignment-table tr,
        .student-assignment-table td{display:block;width:100%}
        .student-assignment-table tr{
            border:1px solid #dbe5f2;
            border-radius:12px;
            padding:10px;
            margin-bottom:10px;
            background:#fff;
        }
        .student-assignment-table tr.assignment-pending{border:2px solid #fb923c;box-shadow:inset 5px 0 0 #f97316,0 8px 18px rgba(249,115,22,.12)}
        .student-assignment-table td{
            border-bottom:1px dashed #e2e8f0;
            padding:8px 0;
            text-align:left;
        }
        .student-assignment-table td:last-child{border-bottom:0}
        .student-assignment-table td::before{
            content:attr(data-label);
            display:block;
            font-size:12px;
            font-weight:700;
            color:#475569;
            margin-bottom:4px;
        }
        .student-assignment-table td.actions{
            display:grid;
            gap:8px;
        }
        .student-assignment-table td.actions .btn,
        .student-assignment-table td.actions .badge{
            width:100%;
            text-align:center;
            justify-content:center;
        }
    }
</style>

<div class="card">
    <h3>Derslerim</h3>
    <table class="student-assignment-table">
        <thead>
            <tr>
                <th>Ders</th>
                <th>Açıklama</th>
                <th>Çözülen Soru</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
        @forelse($courses as $c)
            @php
                $cp = $courseProgress['course-'.$c->id] ?? null;
                $isCourseCompleted = (bool) ($courseCompletion[$c->id] ?? false);
                $slides = (array) data_get($c->lesson_payload, 'slides', []);
                $firstSlide = $slides[0] ?? [];
                $desc = trim((string) data_get($c->lesson_payload, 'lesson_description', ''));
                if ($desc === '') {
                    $desc = trim((string) data_get($firstSlide, 'description', ''));
                }
                if ($desc === '') {
                    $desc = $c->name . ' dersi için hazırlanan konu anlatımı ve etkinlik içerikleri.';
                }
                $solvedQuestions = (int) data_get($cp?->payload, 'solved_questions', 0);
                $isCompleted = $isCourseCompleted;
                $assignedAt = $c->student_assigned_at ? \Carbon\Carbon::parse($c->student_assigned_at) : $c->created_at;
                $isNew = ! $isCompleted && $assignedAt?->gte(now()->subDays(7));
            @endphp
            <tr class="{{ $isCompleted ? '' : 'assignment-pending' }}">
                <td data-label="Ders" class="assignment-title-cell">
                    <strong>{{ $c->name }}</strong>
                    @if($isNew)<small>Yeni verilen ders</small>@endif
                </td>
                <td data-label="Açıklama">{{ \Illuminate\Support\Str::limit($desc, 120) }}</td>
                <td data-label="Çözülen Soru">{{ $solvedQuestions > 0 ? $solvedQuestions : '-' }}</td>
                <td data-label="Durum">
                    <span class="assignment-status {{ $isCompleted ? 'assignment-status--completed' : 'assignment-status--pending' }}">
                        @if($isCompleted)
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> Tamamlandı
                        @else
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            Bekliyor @if($isNew)<span class="assignment-new-label">YENİ</span>@endif
                        @endif
                    </span>
                </td>
                <td class="actions" data-label="İşlem">
                    <a class="btn" href="{{ route('course.detail', ['id' => $c->id]) }}">İçerik</a>
                    <a class="student-course-cta student-course-start-link {{ $isCompleted ? 'is-completed' : '' }}" href="{{ route('student.portal.course-show', $c) }}">{{ $isCompleted ? 'Tamamlandı' : 'Derse Başla' }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Henüz atanmış ders bulunmuyor.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.student-course-start-link').forEach((link) => {
        if (link.dataset.fullscreenBound === '1') return;
        link.dataset.fullscreenBound = '1';
        link.addEventListener('click', () => {
            try {
                sessionStorage.setItem('course_auto_fullscreen', '1');
            } catch (_) {}
        });
    });
});
</script>

<div class="card">
    <h3>Ödevlerim</h3>
    <table class="student-assignment-table">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Teslim</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
        @forelse($courseHomeworks as $h)
            @php
                $p = $progress[$h->id] ?? null;
                $state = $p?->completed_at ? 'completed' : ($p?->started_at ? 'progress' : 'pending');
                $isNew = $state === 'pending' && $h->created_at?->gte(now()->subDays(7));
            @endphp
            <tr class="assignment-{{ $state === 'progress' ? 'in-progress' : $state }}">
                <td data-label="Başlık" class="assignment-title-cell">
                    <strong>{{ $h->title }}</strong>
                    @if($isNew)<small>Yeni verilen ödev</small>@endif
                </td>
                <td data-label="Teslim">{{ $h->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td data-label="Durum">
                    <span class="assignment-status assignment-status--{{ $state }}">
                        @if($state === 'completed')
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> Tamamlandı
                        @elseif($state === 'progress')
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M14 7l5 5-5 5"/></svg> Devam Ediyor
                        @else
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            Bekliyor @if($isNew)<span class="assignment-new-label">YENİ</span>@endif
                        @endif
                    </span>
                </td>
                <td class="actions" data-label="İşlem">
                    <button
                        class="btn btn-detail"
                        type="button"
                        data-homework-detail
                        data-title="{{ e($h->title) }}"
                        data-due="{{ e($h->due_date?->format('Y-m-d') ?? '-') }}"
                        data-type="{{ e(strtoupper($h->assignment_type ?? 'lesson')) }}"
                        data-description="{{ e($h->details ?: 'Açıklama yok') }}"
                        data-complete-url="{{ e(route('student.portal.homework.complete', $h)) }}"
                        data-course-url="{{ $h->course_id ? e(route('student.portal.course-show', $h->course_id)) : '' }}"
                        data-image-url="{{ $h->attachment_path ? e(asset('storage/'.$h->attachment_path)) : '' }}"
                        data-file-url="{{ $h->attachment_path ? e(asset('storage/'.$h->attachment_path)) : '' }}"
                        data-file-name="{{ e($h->attachment_original_name ?? '') }}"
                    >Ödeve Başla</button>

                    @if($p?->completed_at)
                        <span class="badge">Tamamlandı</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Ödev bulunmuyor.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $courseHomeworks->links('partials.pagination') }}
</div>

<div class="card">
    <h3>Oyun ve Etkinlik Ödevleri</h3>
    <table class="student-assignment-table">
        <thead>
            <tr>
                <th>Uygulama</th>
                <th>Ödev</th>
                <th>Teslim</th>
                <th>Level</th>
                <th>Durum</th>
                <th>Başla</th>
            </tr>
        </thead>
        <tbody>
        @forelse($assignments as $a)
            @php
                $gp = $gameProgress[$a->id] ?? null;
                $state = $gp?->completed_at ? 'completed' : ($gp?->started_at ? 'progress' : 'pending');
                $isNew = $state === 'pending' && $a->created_at?->gte(now()->subDays(7));
            @endphp
            <tr class="assignment-{{ $state === 'progress' ? 'in-progress' : $state }}">
                <td data-label="Uygulama">{{ $a->game_name }}</td>
                <td data-label="Ödev" class="assignment-title-cell">
                    <strong>{{ $a->title }}</strong>
                    @if($isNew)<small>Yeni verilen oyun / etkinlik</small>@endif
                </td>
                <td data-label="Teslim">{{ $a->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td data-label="Level">{{ $a->level_from ?? '-' }} - {{ $a->level_to ?? '-' }}</td>
                <td data-label="Durum">
                    <span class="assignment-status assignment-status--{{ $state }}">
                        @if($state === 'completed')
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> Tamamlandı
                        @elseif($state === 'progress')
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M14 7l5 5-5 5"/></svg> Devam Ediyor
                        @else
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            Bekliyor @if($isNew)<span class="assignment-new-label">YENİ</span>@endif
                        @endif
                    </span>
                </td>
                <td data-label="Başla">
                    @if($gp?->completed_at)
                        <span class="badge">Tamamlandı</span>
                    @else
                        <a class="btn" href="{{ route('student.portal.game-assignment.open', $a) }}">Ödeve Başla</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Oyun/etkinlik ödevi yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $assignments->links('partials.pagination') }}
</div>

<div id="student-homework-detail-modal" class="modal">
    <div class="modal-card">
        <div class="modal-head">
            <strong>Ödev Detayı</strong>
            <button class="btn" type="button" data-close-modal>Kapat</button>
        </div>
        <div class="detail-grid">
            <p><b>Başlık:</b> <span id="detail-title">-</span></p>
            <p><b>Son Teslim:</b> <span id="detail-due">-</span></p>
            <p><b>Tür:</b> <span id="detail-type">-</span></p>
            <p><b>Açıklama:</b></p>
            <p id="detail-description" style="white-space:pre-wrap">-</p>
            <div id="detail-course-wrap" style="display:none">
                <b>Ders İçeriği:</b>
                <iframe id="detail-course-frame" src="about:blank" style="width:100%;min-height:72vh;border:1px solid #dbe5f2;border-radius:12px;display:block;margin-top:8px"></iframe>
            </div>
            <div id="detail-file-wrap" style="display:none">
                <b>Ödev Dosyası:</b> <small id="detail-file-name" style="color:#64748b"></small>
                <div id="detail-preview" class="hw-preview" style="margin-top:8px"><div class="sh-msg">Dosya yükleniyor…</div></div>
                <a id="detail-file" class="hw-open" href="#" target="_blank" rel="noopener">⛶ Yeni sekmede aç</a>
            </div>
            <p id="detail-no-file"><b>Ödev Dosyası:</b> Yok</p>
        </div>
        <div style="margin-top:14px">
            <label for="homework-note" style="display:block;font-weight:700;margin-bottom:6px">Öğrenci Notu</label>
            <textarea id="homework-note" rows="4" style="width:100%;border:1px solid #cbd5e1;border-radius:12px;padding:10px;resize:vertical" placeholder="Ödevle ilgili kısa notunuzu yazın..."></textarea>
            <p style="margin:6px 0 0;color:#64748b;font-size:13px">Bu not, ödevi tamamladığınızda öğretmen ekranına düşer.</p>
        </div>
        <form id="homework-complete-form" method="POST" action="" style="margin-top:14px">
            @csrf
            <input type="hidden" name="reached_level" id="detail-reached-level" value="">
            <input type="hidden" name="earned_xp" id="detail-earned-xp" value="15">
            <input type="hidden" name="duration_seconds" id="detail-duration-seconds" value="0">
            <input type="hidden" name="completed_level_ids" id="detail-completed-level-ids" value="">
            <input type="hidden" name="exit_to_panel" value="1">
            <input type="hidden" name="student_note" id="detail-student-note" value="">
            <button type="submit" class="btn" style="width:100%">Ödevi Tamamla</button>
        </form>
    </div>
</div>

@push('scripts')
<style>
    /* Ödev detayı: herkese açık ödev sayfasındaki gibi geniş önizleme */
    #student-homework-detail-modal{padding:12px;overflow:auto}
    #student-homework-detail-modal .modal-card{max-width:min(1100px,100%);max-height:96vh;overflow:auto}
    .hw-preview{background:#fff;border:1px solid #dbe5f2;border-radius:14px;overflow:auto;min-height:120px;width:100%}
    .hw-preview img{display:block;width:100%;max-width:100%;height:auto}
    .hw-preview iframe{display:block;width:100%;height:78vh;min-height:420px;border:0}
    .hw-open{display:inline-block;margin:10px 0 0;font-size:14px;font-weight:600}
    @media (max-width:560px){.hw-preview{border-radius:10px}.hw-preview iframe{height:70vh;min-height:340px}}
    .hw-preview .sh-msg{padding:28px 18px;text-align:center;color:#64748b}
    .hw-preview .sh-pager{position:sticky;top:0;z-index:2;display:flex;align-items:center;justify-content:center;gap:12px;padding:8px;background:#eef2ff;border-bottom:1px solid #dbe5f2}
    .hw-preview .sh-pager button,.hw-preview .sh-sheet-tabs button{font:inherit;font-size:14px;padding:6px 12px;border:1px solid #dbe5f2;border-radius:8px;background:#fff;color:inherit;cursor:pointer}
    .hw-preview .sh-pager button:disabled{opacity:.45;cursor:default}
    .hw-preview .sh-pager-info{font-size:14px;color:#64748b}
    .hw-preview .sh-docxhost{padding:0;overflow:auto;background:#eceae3}
    .hw-preview .docx-wrapper{padding:12px!important}
    .hw-preview .sh-docxhost section.docx{margin:0 auto 12px!important;box-shadow:0 2px 10px rgba(22,24,43,.12)}
    .hw-preview .sh-docx-page{padding:20px 24px;max-width:820px;margin:0 auto}
    .hw-preview .sh-sheet-tabs{display:flex;flex-wrap:wrap;gap:6px;padding:8px;background:#eef2ff;border-bottom:1px solid #dbe5f2;position:sticky;top:0;z-index:2}
    .hw-preview .sh-sheet-tabs button.on{background:#5b3df5;border-color:#5b3df5;color:#fff}
    .hw-preview .sh-sheet{overflow:auto;padding:10px}
    .hw-preview .sh-sheet table{width:auto;min-width:100%;font-size:13.5px}
    .hw-preview .sh-sheet-imgs{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}
    .hw-preview .sh-sheet-imgs-t{width:100%;font-weight:600}
    .hw-preview .sh-sheet-imgs img{max-width:100%;height:auto;border:1px solid #dbe5f2}
</style>
<script src="{{ asset('js/file-preview.js') }}?v={{ @filemtime(public_path('js/file-preview.js')) ?: 1 }}"></script>
<script>
(function () {
    const modal = document.getElementById('student-homework-detail-modal');
    const titleEl = document.getElementById('detail-title');
    const dueEl = document.getElementById('detail-due');
    const typeEl = document.getElementById('detail-type');
    const descEl = document.getElementById('detail-description');
    const courseWrapEl = document.getElementById('detail-course-wrap');
    const courseFrameEl = document.getElementById('detail-course-frame');
    const fileWrapEl = document.getElementById('detail-file-wrap');
    const fileNameEl = document.getElementById('detail-file-name');
    const previewEl = document.getElementById('detail-preview');
    const fileEl = document.getElementById('detail-file');
    const noFileEl = document.getElementById('detail-no-file');
    const noteEl = document.getElementById('homework-note');
    const completeForm = document.getElementById('homework-complete-form');
    const noteSubmitEl = document.getElementById('detail-student-note');
    const closeButtons = document.querySelectorAll('[data-close-modal]');

    function openModal() {
        modal.classList.add('open');
    }

    function closeModal() {
        modal.classList.remove('open');
        if (previewEl) previewEl.innerHTML = '';
    }

    // Word sayfaları ve Excel tabloları kutu genişliğine göre ölçeklenir (herkese açık ödev sayfasıyla aynı davranış).
    function fitPreview() {
        const w = previewEl.clientWidth;
        if (!w) return;
        const doc = previewEl.querySelector('.docx-wrapper');
        if (doc) {
            doc.style.zoom = 1;
            doc.style.zoom = Math.max(0.3, Math.min(1.6, w / (doc.scrollWidth || w)));
        }
        const sheet = previewEl.querySelector('.sh-sheet');
        const tbl = sheet && sheet.querySelector('table');
        if (tbl) {
            sheet.style.zoom = 1;
            sheet.style.zoom = Math.max(0.6, Math.min(1.4, w / (tbl.scrollWidth + 24)));
        }
    }
    let fitRaf = 0;
    function scheduleFit() { cancelAnimationFrame(fitRaf); fitRaf = requestAnimationFrame(fitPreview); }
    if (previewEl) {
        new MutationObserver(scheduleFit).observe(previewEl, { childList: true, subtree: true });
        window.addEventListener('resize', scheduleFit);
    }

    document.querySelectorAll('[data-homework-detail]').forEach((btn) => {
        btn.addEventListener('click', function () {
            titleEl.textContent = this.dataset.title || '-';
            dueEl.textContent = this.dataset.due || '-';
            typeEl.textContent = this.dataset.type || '-';
            descEl.textContent = this.dataset.description || '-';
            if (noteEl) noteEl.value = '';
            if (noteSubmitEl) noteSubmitEl.value = '';
            if (completeForm) completeForm.action = this.dataset.completeUrl || '';

            if (courseFrameEl) {
                if (this.dataset.courseUrl) {
                    courseFrameEl.src = this.dataset.courseUrl;
                    courseWrapEl.style.display = 'block';
                } else {
                    courseFrameEl.src = 'about:blank';
                    courseWrapEl.style.display = 'none';
                }
            }

            if (this.dataset.fileUrl) {
                fileEl.href = this.dataset.fileUrl;
                fileNameEl.textContent = this.dataset.fileName ? '· ' + this.dataset.fileName : '';
                fileWrapEl.style.display = 'block';
                noFileEl.style.display = 'none';
                previewEl.innerHTML = '<div class="sh-msg">Dosya yükleniyor…</div>';
                if (window.FilePreview) {
                    window.FilePreview.render(previewEl, this.dataset.fileUrl, this.dataset.fileName || this.dataset.fileUrl).then(scheduleFit);
                }
            } else {
                fileEl.removeAttribute('href');
                fileWrapEl.style.display = 'none';
                noFileEl.style.display = 'block';
                previewEl.innerHTML = '';
            }

            openModal();
        });
    });

    closeButtons.forEach((button) => button.addEventListener('click', closeModal));
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    if (completeForm) {
        completeForm.addEventListener('submit', function () {
            const noteValue = noteEl ? noteEl.value : '';
            if (noteSubmitEl) noteSubmitEl.value = noteValue;
        });
    }
})();
</script>
@endpush
@endsection
