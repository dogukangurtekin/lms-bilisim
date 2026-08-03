@extends('layout.app')
@section('title','Ödevlerim')
@section('content')
<div class="top"><h1>Ödevlerim</h1></div>
<style>
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
            @endphp
            <tr>
                <td data-label="Ders">{{ $c->name }}</td>
                <td data-label="Açıklama">{{ \Illuminate\Support\Str::limit($desc, 120) }}</td>
                <td data-label="Çözülen Soru">{{ $solvedQuestions > 0 ? $solvedQuestions : '-' }}</td>
                <td data-label="Durum">
                    <span class="badge">{{ $cp?->completed ? 'Tamamlandı' : 'Bekliyor' }}</span>
                </td>
                <td class="actions" data-label="İşlem">
                    <a class="btn" href="{{ route('course.detail', ['id' => $c->id]) }}">İçerik</a>
                    <a class="btn" href="{{ route('student.portal.course-show', $c) }}">{{ $cp?->completed ? 'Tamamlandı' : 'Derse Başla' }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Henüz atanmış ders bulunmuyor.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

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
            @php $p = $progress[$h->id] ?? null; @endphp
            <tr>
                <td data-label="Başlık">{{ $h->title }}</td>
                <td data-label="Teslim">{{ $h->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td data-label="Durum">
                    <span class="badge">{{ $p?->completed_at ? 'Tamamlandı' : ($p?->started_at ? 'Devam Ediyor' : 'Bekliyor') }}</span>
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
            @php $gp = $gameProgress[$a->id] ?? null; @endphp
            <tr>
                <td data-label="Uygulama">{{ $a->game_name }}</td>
                <td data-label="Ödev">{{ $a->title }}</td>
                <td data-label="Teslim">{{ $a->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td data-label="Level">{{ $a->level_from ?? '-' }} - {{ $a->level_to ?? '-' }}</td>
                <td data-label="Durum">
                    <span class="badge">{{ $gp?->completed_at ? 'Tamamlandı' : ($gp?->started_at ? 'Devam Ediyor' : 'Bekliyor') }}</span>
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
            <div>
                <b>Ödev Görseli:</b>
                <div style="margin-top:8px">
                    <a id="detail-image-link" href="#" target="_blank" style="display:none">
                        <img id="detail-image" alt="Ödev görseli" style="max-width:100%;max-height:260px;border-radius:12px;border:1px solid #dbe5f2;object-fit:contain;cursor:zoom-in;display:block">
                    </a>
                    <span id="detail-no-image">Görsel yok</span>
                </div>
            </div>
            <p><b>Ek Dosya:</b> <a id="detail-file" href="#" target="_blank" style="display:none">Dosyayı Aç</a><span id="detail-no-file">Yok</span></p>
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
<script>
(function () {
    const modal = document.getElementById('student-homework-detail-modal');
    const titleEl = document.getElementById('detail-title');
    const dueEl = document.getElementById('detail-due');
    const typeEl = document.getElementById('detail-type');
    const descEl = document.getElementById('detail-description');
    const courseWrapEl = document.getElementById('detail-course-wrap');
    const courseFrameEl = document.getElementById('detail-course-frame');
    const imageEl = document.getElementById('detail-image');
    const imageLinkEl = document.getElementById('detail-image-link');
    const noImageEl = document.getElementById('detail-no-image');
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

            if (this.dataset.imageUrl) {
                imageEl.src = this.dataset.imageUrl;
                imageLinkEl.href = this.dataset.imageUrl;
                imageLinkEl.style.display = 'inline-block';
                noImageEl.style.display = 'none';
            } else {
                imageEl.removeAttribute('src');
                imageLinkEl.removeAttribute('href');
                imageLinkEl.style.display = 'none';
                noImageEl.style.display = 'inline';
            }

            if (this.dataset.fileUrl) {
                fileEl.href = this.dataset.fileUrl;
                fileEl.textContent = this.dataset.fileName || 'Dosyayı Aç';
                fileEl.style.display = 'inline';
                noFileEl.style.display = 'none';
            } else {
                fileEl.removeAttribute('href');
                fileEl.style.display = 'none';
                noFileEl.style.display = 'inline';
            }

            openModal();
        });
    });

    if (imageLinkEl) {
        imageLinkEl.addEventListener('click', function (event) {
            event.preventDefault();
            const src = imageEl?.src || this.href;
            if (!src) return;
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.8);z-index:10000;display:flex;align-items:center;justify-content:center;padding:18px;';
            overlay.innerHTML = '<div style="position:relative;max-width:96vw;max-height:92vh"><button type="button" style="position:absolute;top:-12px;right:-12px;width:36px;height:36px;border:0;border-radius:9999px;background:#fff;font-size:22px;font-weight:700;cursor:pointer">×</button><img src="' + src + '" alt="" style="max-width:96vw;max-height:92vh;object-fit:contain;border-radius:12px;background:#fff;box-shadow:0 20px 50px rgba(0,0,0,.35)"></div>';
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay || e.target.tagName === 'BUTTON') overlay.remove();
            });
            document.body.appendChild(overlay);
        });
    }

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
