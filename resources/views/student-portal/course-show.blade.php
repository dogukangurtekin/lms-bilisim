@extends('layout.app')
@section('title','Ders Icerigi')
@section('content')
<div class="top" style="margin-bottom:10px">
    <a class="btn" href="{{ route('student.portal.courses') }}">Derslerime Geri Don</a>
</div>
<div class="card">
    @php $slides = $course->lesson_payload['slides'] ?? []; @endphp
    @php $globalThemeCss = $course->lesson_payload['global_theme_css'] ?? ''; @endphp
    @if($globalThemeCss)
        <style>{{ $globalThemeCss }}</style>
    @endif
    @if(empty($slides))
        <p>Ogretmen henuz bu ders icin slide paylasmadi.</p>
    @else
        <div style="display:grid;grid-template-columns:1fr auto auto;align-items:center;gap:10px;margin:0 0 10px">
            <p style="margin:0"><b>Ders:</b> {{ $course->name }}</p>
            <span id="student-course-counter" class="badge" style="justify-self:center;font-size:14px;padding:8px 14px">1 / {{ count($slides) }}</span>
            <div style="justify-self:end;display:flex;align-items:center;gap:10px">
                <button class="btn" type="button" id="student-course-prev" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    Geri
                </button>
                <button class="btn" type="button" id="student-course-next" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                    <span id="student-course-next-label">Ileri</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                </button>
            </div>
        </div>
        <div id="student-course-slide-stage" class="card slide-theme" style="min-height:80vh;overflow:hidden;margin:0 0 10px"></div>
        <form id="student-course-complete-form" method="POST" action="{{ route('student.portal.course.complete', $course) }}" style="display:none">
            @csrf
            <input type="hidden" name="earned_xp" id="student-course-earned-xp" value="0">
            <input type="hidden" name="duration_seconds" id="student-course-duration-seconds" value="0">
        </form>

        <template id="student-course-slide-templates">
            @foreach($slides as $i => $slide)
                <div data-slide-index="{{ $i }}" data-slide-title="{{ $slide['title'] ?? ('Sayfa '.($i+1)) }}" data-slide-xp="{{ (int) ($slide['xp'] ?? 0) }}">
                    @include('courses.partials.slide-render', ['slide' => $slide, 'hideSlideTitle' => true])
                </div>
            @endforeach
        </template>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const stage = document.getElementById('student-course-slide-stage');
                const prevBtn = document.getElementById('student-course-prev');
                const nextBtn = document.getElementById('student-course-next');
                const nextLabel = document.getElementById('student-course-next-label');
                const counter = document.getElementById('student-course-counter');
                const completeForm = document.getElementById('student-course-complete-form');
                const earnedXpInput = document.getElementById('student-course-earned-xp');
                const durationInput = document.getElementById('student-course-duration-seconds');
                const tmpl = document.getElementById('student-course-slide-templates');
                const slides = Array.from(tmpl.content.querySelectorAll('[data-slide-index]'));
                let idx = 0;
                const startedAt = Date.now();
                const totalXp = slides.reduce(function (sum, node) {
                    return sum + Math.max(0, Number(node?.dataset?.slideXp || 0));
                }, 0);

                function fitIframeToHolder(iframe, holder) {
                    if (!iframe || !holder) return;
                    iframe.style.width = '100%';
                    iframe.style.height = Math.max(620, holder.clientHeight - 8) + 'px';
                    iframe.style.minHeight = '0';

                    const applyScale = () => {
                        try {
                            const doc = iframe.contentDocument || iframe.contentWindow?.document;
                            if (!doc || !doc.documentElement || !doc.body) return;
                            const root = doc.documentElement;
                            const body = doc.body;
                            root.style.transform = '';
                            root.style.transformOrigin = 'top left';
                            root.style.width = '';
                            body.style.margin = body.style.margin || '0';
                            const frameW = Math.max(1, iframe.clientWidth);
                            const frameH = Math.max(1, iframe.clientHeight);
                            const contentW = Math.max(root.scrollWidth, body.scrollWidth, root.clientWidth, 1);
                            const contentH = Math.max(root.scrollHeight, body.scrollHeight, root.clientHeight, 1);
                            let scale = Math.min(frameW / contentW, frameH / contentH);
                            if (contentW < frameW * 0.72) scale = Math.min(1.45, frameW / contentW);
                            if (!Number.isFinite(scale) || scale <= 0) scale = 1;
                            if (Math.abs(scale - 1) > 0.02) {
                                root.style.transform = 'scale(' + scale + ')';
                                root.style.width = (100 / scale) + '%';
                            }
                        } catch (_) {}
                    };

                    iframe.onload = applyScale;
                    setTimeout(applyScale, 80);
                    setTimeout(applyScale, 260);
                }

                function fitStage() {
                    const holder = stage.querySelector('#student-course-fit');
                    if (!holder) return;
                    const iframe = holder.querySelector('iframe');
                    if (iframe) {
                        fitIframeToHolder(iframe, holder);
                    }
                }

                function render() {
                    const current = slides[idx];
                    stage.innerHTML = '<div id="student-course-fit" style="width:100%;height:100%;min-height:72vh;overflow:hidden;display:flex;align-items:stretch;justify-content:stretch"></div>';
                    const fit = document.getElementById('student-course-fit');
                    const node = current.cloneNode(true);
                    node.style.width = '100%';
                    node.style.height = '100%';
                    fit.appendChild(node);
                    fitStage();
                    counter.textContent = (idx + 1) + ' / ' + slides.length;
                    prevBtn.disabled = idx <= 0;
                    const isLast = idx >= slides.length - 1;
                    if (nextLabel) nextLabel.textContent = isLast ? 'Dersi Bitir' : 'Ileri';
                    bindQuestionInteractions();
                }

                function bindQuestionInteractions() {
                    const qRoot = stage.querySelector('[data-sqz-question]');
                    if (!qRoot) return;
                    const optionLabels = qRoot.querySelectorAll('[data-sqz-option]');
                    optionLabels.forEach((label) => {
                        const input = label.querySelector('input[type="radio"], input[type="checkbox"]');
                        if (!input) return;
                        const sync = () => {
                            if (input.type === 'radio') {
                                optionLabels.forEach((x) => x.classList.remove('selected'));
                                if (input.checked) label.classList.add('selected');
                            } else {
                                label.classList.toggle('selected', input.checked);
                            }
                        };
                        input.addEventListener('change', sync);
                        sync();
                    });
                }

                function isCurrentQuestionAnswered() {
                    const qRoot = stage.querySelector('[data-sqz-question]');
                    if (!qRoot) return true;
                    const type = String(qRoot.getAttribute('data-sqz-type') || 'none');
                    const inputs = Array.from(qRoot.querySelectorAll('[data-sqz-input]'));
                    if (!inputs.length) return false;
                    if (type === 'multiple_choice' || type === 'true_false') {
                        return !!qRoot.querySelector('input[type=\"radio\"][data-sqz-input]:checked');
                    }
                    if (type === 'checklist') {
                        return !!qRoot.querySelector('input[type=\"checkbox\"][data-sqz-input]:checked');
                    }
                    return inputs.every((el) => {
                        if (el.tagName === 'SELECT') return String(el.value || '').trim() !== '';
                        if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                        return String(el.value || '').trim() !== '';
                    });
                }

                prevBtn.addEventListener('click', function () {
                    if (idx <= 0) return;
                    idx -= 1;
                    render();
                });
                nextBtn.addEventListener('click', function () {
                    if (!isCurrentQuestionAnswered()) {
                        window.alert('Bu soruyu cevaplamadan ilerleyemezsin.');
                        return;
                    }
                    const isLast = idx >= slides.length - 1;
                    if (isLast) {
                        if (!completeForm) return;
                        if (earnedXpInput) earnedXpInput.value = String(totalXp);
                        if (durationInput) durationInput.value = String(Math.max(0, Math.round((Date.now() - startedAt) / 1000)));
                        completeForm.submit();
                        return;
                    }
                    idx += 1;
                    render();
                });
                window.addEventListener('resize', fitStage);
                render();
            });
        </script>
    @endif
</div>
@endsection
