@extends('layout.app')
@section('title', 'Ders İçeriği')
@section('body_class', 'play-compact')

@section('content')
@php
    $previewMode = (bool) ($previewMode ?? false);
    $slides = array_values(array_filter((array) ($slides ?? []), fn ($slide) => is_array($slide)));
    if (!empty($summarySlide) && is_array($summarySlide)) {
        $slides[] = $summarySlide;
    }
@endphp

<div class="card" style="padding:16px">
    @include('courses.partials.theme-css')

    @if(empty($slides))
        <div style="padding:18px;border-radius:18px;background:#fff">
            <strong>Bu ders için henüz slide paylaşılmadı.</strong>
        </div>
    @else
        <div style="display:grid;grid-template-columns:auto 1fr auto auto auto;align-items:center;gap:12px;margin:0 0 12px">
            <a class="btn" href="{{ url('/courses') }}" style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;font-size:15px;font-weight:700">
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                Derslerime Dön
            </a>

            <p style="margin:0;font-size:16px;font-weight:700;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                Ders: {{ $course->name }}
            </p>

            <span id="student-course-counter" class="badge" style="justify-self:end;font-size:14px;padding:8px 14px">1 / {{ count($slides) }}</span>

            <button class="btn" type="button" id="student-course-prev" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                Geri
            </button>

            <button class="btn" type="button" id="student-course-next" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                <span id="student-course-next-label">İleri</span>
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m8.59 16.59 4.58-4.59-4.58-4.59L10 6l6 6-6 6z"/></svg>
            </button>
        </div>

        <div id="student-course-slide-stage" class="card slide-theme" style="min-height:80vh;overflow:hidden;margin:0 0 12px;background:#fff"></div>

        @if(empty($previewMode))
            <form id="student-course-complete-form" method="POST" action="{{ route('student.portal.course.complete', $course) }}" style="display:none">
                @csrf
                <input type="hidden" name="earned_xp" id="student-course-earned-xp" value="0">
                <input type="hidden" name="duration_seconds" id="student-course-duration-seconds" value="0">
            </form>
        @endif

        <template id="student-course-slide-templates">
            @foreach($slides as $i => $slide)
                @php
                    $slideXp = (int) ($slide['xp'] ?? 0);
                    if ($slideXp <= 0 && !empty($slide['question_prompt'])) {
                        $slideXp = max(1, (int) ($slide['points'] ?? 5));
                    }
                    if ($slideXp <= 0) {
                        $slideXp = 2;
                    }
                @endphp
                <div
                    data-slide-index="{{ $i }}"
                    data-slide-title="{{ $slide['title'] ?? ('Sayfa ' . ($i + 1)) }}"
                    data-slide-xp="{{ $slideXp }}"
                    data-slide-summary="{{ !empty($slide['__summary']) ? '1' : '0' }}"
                >
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
                const previewMode = {{ !empty($previewMode) ? 'true' : 'false' }};
                const earnedXpInput = document.getElementById('student-course-earned-xp');
                const durationInput = document.getElementById('student-course-duration-seconds');
                const tmpl = document.getElementById('student-course-slide-templates');
                const slides = Array.from(tmpl.content.querySelectorAll('[data-slide-index]'));

                if (!stage || !prevBtn || !nextBtn || !counter || slides.length === 0) {
                    return;
                }

                let idx = 0;
                let lastDirection = 1;
                let touchStartX = 0;
                let touchStartY = 0;
                const startedAt = Date.now();
                let earnedXpTotal = 0;
                const awardedSlideIndexes = new Set();
                let nextAdvanceTimer = null;
                const totalXp = slides.reduce((sum, node) => sum + Math.max(0, Number(node?.dataset?.slideXp || 0)), 0);

                function awardCurrentSlideXp() {
                    const current = slides[idx];
                    if (!current) return 0;
                    const isSummary = String(current?.dataset?.slideSummary || '0') === '1';
                    if (isSummary || awardedSlideIndexes.has(idx)) return 0;
                    const xp = Math.max(0, Number(current.dataset.slideXp || 0));
                    awardedSlideIndexes.add(idx);
                    earnedXpTotal += xp;
                    return xp;
                }

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
                    if (iframe) fitIframeToHolder(iframe, holder);
                }

                function render() {
                    const current = slides[idx];
                    const previous = stage.querySelector('#student-course-fit');
                    if (previous) {
                        previous.style.transition = 'opacity .18s ease, transform .18s ease';
                        previous.style.opacity = '0';
                        previous.style.transform = 'translateX(' + (lastDirection > 0 ? '-18px' : '18px') + ') scale(.985)';
                        setTimeout(() => previous.remove(), 180);
                    }

                    stage.innerHTML = '<div id="student-course-fit" style="width:100%;height:100%;min-height:72vh;overflow:hidden;display:flex;align-items:stretch;justify-content:stretch"></div>';
                    const fit = document.getElementById('student-course-fit');
                    fit.style.opacity = '0';
                    fit.style.transform = 'translateX(' + (lastDirection > 0 ? '18px' : '-18px') + ') scale(.985)';
                    fit.style.transition = 'opacity .22s ease, transform .22s ease';

                    const node = current.cloneNode(true);
                    node.style.width = '100%';
                    node.style.height = '100%';
                    fit.appendChild(node);

                    requestAnimationFrame(() => {
                        fit.style.opacity = '1';
                        fit.style.transform = 'translateX(0) scale(1)';
                    });

                    fitStage();
                    if (String(current?.dataset?.slideSummary || '0') === '1') {
                        const earnedEl = stage.querySelector('[data-summary-earned-xp]');
                        if (earnedEl) earnedEl.textContent = 'Kazanılan XP: ' + Math.max(earnedXpTotal, totalXp);
                    }
                    counter.textContent = (idx + 1) + ' / ' + slides.length;
                    prevBtn.disabled = idx <= 0;
                    nextBtn.disabled = false;
                    const isSummary = String(current?.dataset?.slideSummary || '0') === '1';
                    if (nextLabel) nextLabel.textContent = isSummary ? 'Dersi Bitir' : 'İleri';
                    bindQuestionInteractions();
                }

                function bindQuestionInteractions() {
                    const qRoot = stage.querySelector('[data-sqz-question]');
                    if (!qRoot) return;
                    const feedbackEl = qRoot.querySelector('[data-sqz-feedback]');
                    const optionLabels = qRoot.querySelectorAll('[data-sqz-option]');
                    const currentXp = Math.max(0, Number(slides[idx]?.dataset?.slideXp || 0));
                    const isSummary = String(slides[idx]?.dataset?.slideSummary || '0') === '1';
                    const showFeedback = (isCorrect, message, autoAdvance = false) => {
                        if (!feedbackEl) return;
                        feedbackEl.classList.remove('is-correct', 'is-wrong');
                        feedbackEl.textContent = message || '';
                        if (!message) {
                            feedbackEl.style.display = 'none';
                            return;
                        }
                        feedbackEl.classList.add(isCorrect ? 'is-correct' : 'is-wrong');
                        feedbackEl.style.display = 'block';
                        if (isCorrect && autoAdvance) {
                            const existing = stage.querySelector('[data-sqz-celebrate]');
                            if (existing) existing.remove();
                            const celebrate = document.createElement('div');
                            celebrate.setAttribute('data-sqz-celebrate', '1');
                            celebrate.style.cssText = 'margin-top:12px;padding:14px 16px;border-radius:16px;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;font-weight:900;box-shadow:0 16px 32px rgba(22,163,74,.28);animation:sqzPop .55s ease-out both;';
                            celebrate.innerHTML = 'Doğru cevap! <span style="opacity:.92">+' + currentXp + ' XP</span>';
                            feedbackEl.insertAdjacentElement('afterend', celebrate);
                            if (nextAdvanceTimer) clearTimeout(nextAdvanceTimer);
                            nextAdvanceTimer = setTimeout(() => {
                                awardCurrentSlideXp();
                                if (idx >= slides.length - 1 || isSummary) {
                                    if (previewMode || !completeForm) return;
                                    if (earnedXpInput) earnedXpInput.value = String(Math.max(earnedXpTotal, totalXp));
                                    if (durationInput) durationInput.value = String(Math.max(0, Math.round((Date.now() - startedAt) / 1000)));
                                    completeForm.submit();
                                    return;
                                }
                                idx += 1;
                                render();
                            }, 900);
                        }
                    };

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
                            const type = String(qRoot.getAttribute('data-sqz-type') || 'none');
                            if (type === 'multiple_choice' || type === 'true_false') {
                                const selected = Array.from(optionLabels).find((x) => {
                                    const i = x.querySelector('input[type="radio"], input[type="checkbox"]');
                                    return i && i.checked;
                                });
                                if (!selected) {
                                    showFeedback(null, '');
                                    return;
                                }
                                const isCorrect = String(selected.getAttribute('data-sqz-correct') || '0') === '1';
                                showFeedback(isCorrect, isCorrect ? 'Doğru cevap.' : 'Yanlış cevap.', isCorrect);
                            }
                        };
                        input.addEventListener('change', sync);
                        sync();
                    });

                    if (String(qRoot.getAttribute('data-sqz-type') || 'none') === 'short_answer') {
                        const input = qRoot.querySelector('input[data-sqz-input]');
                        const correctAnswer = String(qRoot.getAttribute('data-sqz-answer') || '').trim().toLowerCase();
                        const onInput = () => {
                            const value = String(input?.value || '').trim().toLowerCase();
                            if (!value) {
                                showFeedback(null, '');
                                return;
                            }
                            const isCorrect = correctAnswer !== '' && value === correctAnswer;
                            showFeedback(isCorrect, isCorrect ? 'Doğru cevap.' : 'Yanlış cevap.', isCorrect);
                        };
                        input?.addEventListener('input', onInput);
                        onInput();
                    }

                    if (String(qRoot.getAttribute('data-sqz-type') || 'none') === 'checklist') {
                        const inputs = Array.from(qRoot.querySelectorAll('input[type="checkbox"][data-sqz-input]'));
                        const onChange = () => {
                            const allChecked = inputs.length > 0 && inputs.every((el) => el.checked);
                            showFeedback(allChecked, allChecked ? 'Doğru cevap.' : 'Yanlış cevap.', allChecked);
                        };
                        inputs.forEach((input) => input.addEventListener('change', onChange));
                    }
                }

                function isCurrentQuestionAnswered() {
                    const qRoot = stage.querySelector('[data-sqz-question]');
                    if (!qRoot) return true;
                    const type = String(qRoot.getAttribute('data-sqz-type') || 'none');
                    const inputs = Array.from(qRoot.querySelectorAll('[data-sqz-input]'));
                    if (!inputs.length) return false;
                    if (type === 'multiple_choice' || type === 'true_false') {
                        return !!qRoot.querySelector('input[type="radio"][data-sqz-input]:checked');
                    }
                    if (type === 'checklist') {
                        return !!qRoot.querySelector('input[type="checkbox"][data-sqz-input]:checked');
                    }
                    return inputs.every((el) => {
                        if (el.tagName === 'SELECT') return String(el.value || '').trim() !== '';
                        if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                        return String(el.value || '').trim() !== '';
                    });
                }

                prevBtn.addEventListener('click', function () {
                    if (idx <= 0) return;
                    lastDirection = -1;
                    idx -= 1;
                    render();
                });

                nextBtn.addEventListener('click', function () {
                    if (!isCurrentQuestionAnswered()) {
                        window.alert('Bu soruyu cevaplamadan ilerleyemezsin.');
                        return;
                    }
                    const isSummary = String(slides[idx]?.dataset?.slideSummary || '0') === '1';
                    if (isSummary) {
                        if (!completeForm) return;
                        if (earnedXpInput) earnedXpInput.value = String(Math.max(earnedXpTotal, totalXp));
                        if (durationInput) durationInput.value = String(Math.max(0, Math.round((Date.now() - startedAt) / 1000)));
                        completeForm.submit();
                        return;
                    }
                    lastDirection = 1;
                    awardCurrentSlideXp();
                    idx += 1;
                    render();
                });

                stage.addEventListener('touchstart', (e) => {
                    const t = e.changedTouches && e.changedTouches[0];
                    if (!t) return;
                    touchStartX = t.clientX;
                    touchStartY = t.clientY;
                }, { passive: true });

                stage.addEventListener('touchend', (e) => {
                    const t = e.changedTouches && e.changedTouches[0];
                    if (!t) return;
                    const dx = t.clientX - touchStartX;
                    const dy = t.clientY - touchStartY;
                    if (Math.abs(dx) < 50 || Math.abs(dx) < Math.abs(dy)) return;
                    if (dx < 0) nextBtn.click();
                    else prevBtn.click();
                }, { passive: true });

                window.addEventListener('resize', fitStage);
                render();
            });
        </script>
        <style>
            @keyframes sqzPop {
                0% { transform: scale(.82); opacity: 0; }
                100% { transform: scale(1); opacity: 1; }
            }
        </style>
    @endif
</div>
@endsection
