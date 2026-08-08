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
    $courseName = trim((string) ($course->name ?? 'Ders İçeriği'));
    $courseLogo = url('/public/logo.png');
    $slideCount = count($slides);
    $totalXpPreview = array_sum(array_map(static fn ($slide) => max(0, (int) ($slide['xp'] ?? 0)), $slides));
    $questionCountPreview = 0;
    foreach ($slides as $slideItem) {
        if (!empty($slideItem['__summary'])) {
            continue;
        }
        if (!empty($slideItem['question_prompt'])) {
            $questionCountPreview++;
        }
    }
@endphp

<style>
    .course-show-shell{
        --shell-border:rgba(37,99,235,.12);
        --shell-surface:rgba(255,255,255,.84);
        --shell-text:#0f172a;
        overflow:hidden;
        max-width:100vw;
        padding:12px !important;
        position:relative;
        background:
            radial-gradient(circle at 10% 10%, rgba(255, 209, 102, .18), transparent 18%),
            radial-gradient(circle at 90% 18%, rgba(59, 130, 246, .12), transparent 22%),
            linear-gradient(180deg, #f7fbff 0%, #edf4ff 100%);
    }
    .course-show-shell::before,
    .course-show-shell::after{
        content:"";
        position:absolute;
        inset:auto;
        pointer-events:none;
        opacity:.5;
        border-radius:999px;
        filter:blur(1px);
    }
    .course-show-shell::before{
        width:260px;height:260px;left:-120px;top:88px;
        background:radial-gradient(circle, rgba(147,197,253,.18), transparent 70%);
    }
    .course-show-shell::after{
        width:220px;height:220px;right:-100px;bottom:42px;
        background:radial-gradient(circle, rgba(196,181,253,.16), transparent 70%);
    }
    .course-show-shell *{box-sizing:border-box}
    .course-show-page{position:relative;z-index:1;display:grid;gap:14px}
    .course-show-topbar{
        display:grid;
        grid-template-columns:auto minmax(0,1fr) auto;
        align-items:center;
        gap:14px;
        padding:14px 16px;
        border-radius:24px;
        background:rgba(255,255,255,.84);
        border:1px solid var(--shell-border);
        box-shadow:0 18px 40px rgba(15,23,42,.06);
        backdrop-filter:blur(14px);
    }
    .course-show-brand{
        display:flex;
        align-items:center;
        gap:12px;
        min-width:0;
    }
    .course-show-brand img{
        width:54px;
        height:54px;
        object-fit:contain;
        flex:0 0 auto;
        filter:drop-shadow(0 10px 20px rgba(37,99,235,.12));
    }
    .course-show-brand-text{
        display:grid;
        gap:2px;
        min-width:0;
    }
    .course-show-kicker{
        margin:0;
        font-size:12px;
        font-weight:900;
        letter-spacing:.18em;
        text-transform:uppercase;
        color:#3b82f6;
    }
    .course-show-title{
        margin:0;
        font-size:18px;
        font-weight:900;
        color:var(--shell-text);
        min-width:0;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .course-show-metrics{
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:10px;
        flex-wrap:wrap;
    }
    .course-show-metric{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:10px 14px;
        border-radius:999px;
        background:#fff;
        border:1px solid rgba(37,99,235,.12);
        color:#0f172a;
        font-size:13px;
        font-weight:800;
        box-shadow:0 8px 18px rgba(15,23,42,.04);
        white-space:nowrap;
    }
    .course-show-metric strong{
        color:#1d4ed8;
        font-size:14px;
    }
    .course-show-stage{
        min-height:clamp(62vh,72vh,78vh);
        overflow:hidden;
        margin:0;
        background:transparent;
        max-width:100%;
        border:0;
        box-shadow:none;
    }
    .course-show-stage-frame{
        position:relative;
        height:100%;
        min-height:inherit;
        border-radius:30px;
        padding:14px;
        background:linear-gradient(135deg, rgba(255,255,255,.92), rgba(248,251,255,.82));
        border:1px solid rgba(37,99,235,.10);
        box-shadow:0 26px 56px rgba(15,23,42,.08);
        overflow:hidden;
    }
    .course-show-stage-frame::before,
    .course-show-stage-frame::after{
        content:"";
        position:absolute;
        inset:auto;
        pointer-events:none;
        border-radius:999px;
    }
    .course-show-stage-frame::before{
        width:160px;height:160px;left:-54px;top:-54px;
        background:radial-gradient(circle, rgba(255,255,255,.95), rgba(191,219,254,.22), transparent 70%);
    }
    .course-show-stage-frame::after{
        width:180px;height:180px;right:-72px;bottom:-72px;
        background:radial-gradient(circle, rgba(191,219,254,.18), transparent 68%);
    }
    .course-show-stage-inner{
        position:relative;
        z-index:1;
        height:100%;
        min-height:inherit;
        border-radius:24px;
        overflow:hidden;
        background:rgba(255,255,255,.72);
    }
    .course-show-bottom{
        display:grid;
        grid-template-columns:1fr auto;
        align-items:end;
        gap:12px;
    }
    .course-show-bottom-note{
        min-height:54px;
    }
    .course-show-bottom-note .badge{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:10px 14px;
        border-radius:999px;
        background:#fff;
        border:1px solid rgba(37,99,235,.12);
        box-shadow:0 10px 22px rgba(15,23,42,.04);
        font-size:13px;
        font-weight:800;
    }
    .course-show-nav{
        display:flex;
        justify-content:flex-end;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }
    .course-show-shell img,.course-show-shell video,.course-show-shell iframe,.course-show-shell table{max-width:100%}
    .course-show-shell .lesson-slide,.course-show-shell .lesson-slide-shell,.course-show-shell .lesson-card,.course-show-shell .lesson-split,.course-show-shell .lesson-split-card,.course-show-shell .lesson-split-body,.course-show-shell .lesson-image-focus,.course-show-shell .lesson-grid-cards,.course-show-shell .sqz-wrap{min-width:0;max-width:100%}
    .course-show-shell .sqz-opt.is-correct{outline:3px solid rgba(34,197,94,.98) !important;box-shadow:0 0 0 5px rgba(34,197,94,.18),inset 0 -4px 0 rgba(0,0,0,.16) !important}
    .course-show-shell .sqz-opt.is-wrong{outline:3px solid rgba(239,68,68,.98) !important;box-shadow:0 0 0 5px rgba(239,68,68,.18),inset 0 -4px 0 rgba(0,0,0,.16) !important}
    .course-show-shell .sqz-row.is-correct input,.course-show-shell .sqz-row.is-correct select{border-color:#22c55e !important;box-shadow:0 0 0 3px rgba(34,197,94,.16) !important}
    .course-show-shell .sqz-row.is-wrong input,.course-show-shell .sqz-row.is-wrong select{border-color:#ef4444 !important;box-shadow:0 0 0 3px rgba(239,68,68,.16) !important}
    .course-show-shell .sqz-feedback{margin-top:14px;padding:14px 16px;border-radius:16px;font-weight:900;font-size:16px;display:none;letter-spacing:-.01em;transform-origin:top center;animation:none;transition:transform .28s ease,opacity .28s ease,filter .28s ease;overflow:hidden}
    .course-show-shell .sqz-feedback.is-correct{display:block;background:linear-gradient(135deg,#0f9d58 0%,#22c55e 55%,#86efac 100%);border:1px solid rgba(16,185,129,.88);color:#fff;box-shadow:0 16px 34px rgba(34,197,94,.24)}
    .course-show-shell .sqz-feedback.is-wrong{display:block;background:linear-gradient(135deg,#ef4444 0%,#fb7185 54%,#fecaca 100%);border:1px solid rgba(239,68,68,.9);color:#fff;box-shadow:0 16px 34px rgba(239,68,68,.24)}
    .course-show-shell .sqz-feedback.is-animate{animation:sqzFeedbackIn .38s cubic-bezier(.2,.8,.2,1) both}
    @keyframes sqzFeedbackIn{
        0%{opacity:0;transform:translateY(10px) scale(.96);filter:blur(3px)}
        70%{opacity:1;transform:translateY(-2px) scale(1.01);filter:blur(0)}
        100%{opacity:1;transform:translateY(0) scale(1);filter:blur(0)}
    }
    @media (max-width:768px){
        .course-show-shell{padding:10px !important}
        .course-show-topbar{grid-template-columns:1fr;gap:12px;padding:12px}
        .course-show-brand{width:100%}
        .course-show-brand img{width:46px;height:46px}
        .course-show-title{white-space:normal;overflow:visible;text-overflow:clip;font-size:15px;line-height:1.35}
        .course-show-metrics{justify-content:flex-start}
        .course-show-stage{min-height:auto}
        .course-show-stage-frame{padding:10px;border-radius:22px}
        .course-show-bottom{grid-template-columns:1fr;align-items:start}
        .course-show-nav{justify-content:space-between}
        .course-show-shell .lesson-slide-title{font-size:clamp(22px,6vw,34px);line-height:1.1;word-break:break-word;overflow-wrap:anywhere}
        .course-show-shell .lesson-slide-subtitle,.course-show-shell .lesson-paragraph{font-size:clamp(14px,3.8vw,17px);line-height:1.6;word-break:break-word;overflow-wrap:anywhere;hyphens:auto}
        .course-show-shell .lesson-grid-cards,.course-show-shell .lesson-split{grid-template-columns:1fr !important}
        .course-show-shell .lesson-split-media,.course-show-shell .lesson-image,.course-show-shell .lesson-code-frame,.course-show-shell .lesson-media-figure,.course-show-shell .lesson-image-focus img,.course-show-shell .lesson-image-focus iframe{width:100% !important;max-width:100% !important}
        .course-show-shell .lesson-image-focus{gap:12px !important;min-height:auto !important}
        .course-show-shell .lesson-image-focus figure{width:100% !important;min-width:0 !important}
        .course-show-shell .lesson-image-focus figure > div{width:100% !important}
        .course-show-shell .sqz-q{font-size:clamp(20px,5vw,26px) !important;line-height:1.25}
        .course-show-shell .sqz-opt{font-size:clamp(15px,4vw,18px) !important;padding:14px 12px !important}
        .course-show-shell .sqz-grid,.course-show-shell .sqz-row{grid-template-columns:1fr !important}
        .course-show-shell .lesson-split-card,.course-show-shell .lesson-card{width:100%}
        .course-show-shell .lesson-split-body{width:100%}
    }
</style>

<div class="card course-show-shell" style="padding:16px">
    @include('courses.partials.theme-css')

    @if(empty($slides))
        <div style="padding:18px;border-radius:18px;background:#fff">
            <strong>Bu ders için henüz slide paylaşılmadı.</strong>
        </div>
    @else
        <div class="course-show-page">
            <div class="course-show-topbar">
                <div class="course-show-brand" aria-label="Derslerime dön">
                    <img src="{{ $courseLogo }}" alt="Logo">
                    <div class="course-show-brand-text">
                        <p class="course-show-kicker">{{ $previewMode ? 'Önizleme' : 'Ders İçeriği' }}</p>
                        <h1 class="course-show-title">{{ $courseName }}</h1>
                    </div>
                </div>

                <div class="course-show-metrics">
                    <span class="course-show-metric">Slayt <strong>{{ $slideCount }}</strong></span>
                    <span class="course-show-metric">Soru <strong>{{ $questionCountPreview }}</strong></span>
                    <span class="course-show-metric">XP <strong>{{ $totalXpPreview }}</strong></span>
                    <a
                        class="btn"
                        href="{{ route('student.portal.courses') }}"
                        style="display:inline-flex;align-items:center;gap:8px;font-size:15px;font-weight:800;padding:10px 16px;border-radius:16px;background:#f59e0b;border-color:#f59e0b;color:#fff;text-decoration:none;white-space:nowrap;"
                        aria-label="Derslerime dön"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H19z"/></svg>
                        Derslerime Dön
                    </a>
                </div>
            </div>

            <div class="course-show-stage">
                <div class="course-show-stage-frame">
                    <div id="student-course-slide-stage" class="course-show-stage-inner slide-theme"></div>
                </div>
            </div>

            <div class="course-show-bottom">
                <div class="course-show-bottom-note">
                    <span id="student-course-counter" class="badge">1 / {{ count($slides) }}</span>
                </div>
                <div class="course-show-nav">
                    <button class="btn" type="button" id="student-course-prev" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px;border-radius:16px">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                        Geri
                    </button>

                    <button class="btn" type="button" id="student-course-next" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px;border-radius:16px">
                        <span id="student-course-next-label">İleri</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m8.59 16.59 4.58-4.59-4.58-4.59L10 6l6 6-6 6z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        @if(empty($previewMode))
            <form id="student-course-complete-form" method="POST" action="{{ route('student.portal.course.complete', $course) }}" style="display:none">
                @csrf
                <input type="hidden" name="earned_xp" id="student-course-earned-xp" value="0">
                <input type="hidden" name="duration_seconds" id="student-course-duration-seconds" value="0">
                <input type="hidden" name="solved_questions" id="student-course-solved-questions" value="0">
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
                try {
                    sessionStorage.removeItem('course_auto_fullscreen');
                    const fullscreenTarget = document.querySelector('.course-show-shell') || document.documentElement;
                    const requestFullscreen = fullscreenTarget.requestFullscreen
                        || fullscreenTarget.webkitRequestFullscreen
                        || fullscreenTarget.msRequestFullscreen;
                    if (requestFullscreen) {
                        Promise.resolve(requestFullscreen.call(fullscreenTarget)).catch(() => {});
                    }
                } catch (_) {}

                const stage = document.getElementById('student-course-slide-stage');
                const prevBtn = document.getElementById('student-course-prev');
                const nextBtn = document.getElementById('student-course-next');
                const nextLabel = document.getElementById('student-course-next-label');
                const counter = document.getElementById('student-course-counter');
                const completeForm = document.getElementById('student-course-complete-form');
                const previewMode = {{ !empty($previewMode) ? 'true' : 'false' }};
                const earnedXpInput = document.getElementById('student-course-earned-xp');
                const durationInput = document.getElementById('student-course-duration-seconds');
                const solvedQuestionsInput = document.getElementById('student-course-solved-questions');
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
                let solvedQuestionsTotal = 0;
                const awardedSlideIndexes = new Set();
                const solvedQuestionIndexes = new Set();
                let nextAdvanceTimer = null;
                const totalXp = slides.reduce((sum, node) => sum + Math.max(0, Number(node?.dataset?.slideXp || 0)), 0);
                const totalQuestions = slides.reduce((sum, node) => sum + ((String(node?.dataset?.slideSummary || '0') === '1') ? 0 : ((node.querySelector('[data-sqz-question]') ? 1 : 0))), 0);

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

                function markSolvedCurrentSlide() {
                    const current = slides[idx];
                    if (!current) return;
                    const isSummary = String(current?.dataset?.slideSummary || '0') === '1';
                    if (isSummary || solvedQuestionIndexes.has(idx)) return;
                    const qRoot = current.querySelector('[data-sqz-question]');
                    if (!qRoot) return;
                    solvedQuestionIndexes.add(idx);
                    solvedQuestionsTotal += 1;
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

                    stage.innerHTML = '<div id="student-course-fit" style="width:100%;height:100%;min-height:62vh;overflow:hidden;display:flex;align-items:stretch;justify-content:stretch"></div>';
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
                        const solvedEl = stage.querySelector('[data-summary-solved-questions]');
                        if (solvedEl) {
                            solvedEl.textContent = totalQuestions > 0
                                ? 'Çözülen soru sayısı: ' + solvedQuestionsTotal + ' / ' + totalQuestions
                                : 'Çözülen soru sayısı: 0';
                        }
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
                    const normalize = (value) => String(value || '')
                        .replace(/<[^>]*>/g, ' ')
                        .replace(/&nbsp;/gi, ' ')
                        .replace(/&amp;/gi, '&')
                        .replace(/&lt;/gi, '<')
                        .replace(/&gt;/gi, '>')
                        .replace(/&quot;/gi, '"')
                        .replace(/&#39;/gi, '\'')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .toLowerCase();
                    const setOptionState = (label, state) => {
                        if (!label) return;
                        label.classList.remove('is-correct', 'is-wrong');
                        if (state) label.classList.add(state);
                    };
                    const correctMessage = (baseMessage) => {
                        const xpText = currentXp > 0 ? ` +${currentXp} XP` : '';
                        return `${baseMessage}${xpText}`;
                    };
                    const showFeedback = (isCorrect, message, autoAdvance = false) => {
                        if (!feedbackEl) return;
                        feedbackEl.classList.remove('is-correct', 'is-wrong');
                        feedbackEl.classList.remove('is-animate');
                        feedbackEl.textContent = message || '';
                        if (!message) {
                            feedbackEl.style.display = 'none';
                            return;
                        }
                        feedbackEl.classList.add(isCorrect ? 'is-correct' : 'is-wrong');
                        feedbackEl.style.display = 'block';
                        void feedbackEl.offsetWidth;
                        feedbackEl.classList.add('is-animate');
                        if (isCorrect && autoAdvance) {
                            markSolvedCurrentSlide();
                            if (nextAdvanceTimer) clearTimeout(nextAdvanceTimer);
                            nextAdvanceTimer = setTimeout(() => {
                                awardCurrentSlideXp();
                                if (idx >= slides.length - 1 || isSummary) {
                                    if (previewMode || !completeForm) return;
                                    if (earnedXpInput) earnedXpInput.value = String(Math.max(earnedXpTotal, totalXp));
                                    if (durationInput) durationInput.value = String(Math.max(0, Math.round((Date.now() - startedAt) / 1000)));
                                    if (solvedQuestionsInput) solvedQuestionsInput.value = String(Math.min(solvedQuestionsTotal, totalQuestions));
                                    completeForm.submit();
                                    return;
                                }
                                idx += 1;
                                render();
                            }, 900);
                        }
                    };

                    const evaluateSelectedOption = () => {
                        const selected = Array.from(optionLabels).find((x) => {
                            const i = x.querySelector('input[type="radio"], input[type="checkbox"]');
                            return i && i.checked;
                        });
                        optionLabels.forEach((label) => setOptionState(label, null));
                        if (!selected) {
                            showFeedback(null, '');
                            return;
                        }
                        const isCorrect = String(selected.getAttribute('data-sqz-correct') || '0') === '1';
                        setOptionState(selected, isCorrect ? 'is-correct' : 'is-wrong');
                        showFeedback(isCorrect, isCorrect ? correctMessage('Doğru cevap.') : 'Yanlış cevap.', isCorrect);
                        if (isCorrect) markSolvedCurrentSlide();
                    };

                    const evaluateRowInputs = () => {
                        const rows = Array.from(qRoot.querySelectorAll('[data-sqz-row]'));
                        if (!rows.length) return false;
                        let allCorrect = true;
                        let hasAnyValue = false;
                        let allAnswered = true;
                        rows.forEach((row) => {
                            row.classList.remove('is-correct', 'is-wrong');
                            const expected = normalize(row.getAttribute('data-sqz-answer'));
                            const input = row.querySelector('input[data-sqz-input], select[data-sqz-input]');
                            if (!input) return;
                            const value = normalize(input.value);
                            if (!value) {
                                allAnswered = false;
                                return;
                            }
                            hasAnyValue = true;
                            const isCorrect = expected !== '' && value === expected;
                            row.classList.add(isCorrect ? 'is-correct' : 'is-wrong');
                            if (!isCorrect) allCorrect = false;
                        });
                        if (!hasAnyValue || !allAnswered) {
                            showFeedback(null, '');
                            return true;
                        }
                        showFeedback(allCorrect, allCorrect ? correctMessage('Doğru cevap.') : 'Yanlış cevap.', allCorrect);
                        if (allCorrect) markSolvedCurrentSlide();
                        return true;
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
                                evaluateSelectedOption();
                            }
                        };
                        input.addEventListener('change', sync);
                        sync();
                    });

                    if (String(qRoot.getAttribute('data-sqz-type') || 'none') === 'short_answer') {
                        const input = qRoot.querySelector('input[data-sqz-input]');
                        const correctAnswer = normalize(qRoot.getAttribute('data-sqz-answer') || qRoot.querySelector('input[data-sqz-answer]')?.value || '');
                        const onInput = () => {
                            const value = normalize(input?.value || '');
                            if (!value) {
                                showFeedback(null, '');
                                return;
                            }
                            const isCorrect = correctAnswer !== '' && value === correctAnswer;
                            showFeedback(isCorrect, isCorrect ? correctMessage('Doğru cevap.') : 'Yanlış cevap.', isCorrect);
                            if (isCorrect) markSolvedCurrentSlide();
                        };
                        input?.addEventListener('input', onInput);
                        onInput();
                    }

                    if (String(qRoot.getAttribute('data-sqz-type') || 'none') === 'checklist') {
                        const inputs = Array.from(qRoot.querySelectorAll('input[type="checkbox"][data-sqz-input]'));
                        const onChange = () => {
                            const optionLabels = Array.from(qRoot.querySelectorAll('[data-sqz-option]'));
                            let hasAnswered = false;
                            let allCorrect = true;
                            optionLabels.forEach((label) => {
                                const input = label.querySelector('input[type="checkbox"][data-sqz-input]');
                                if (!input) return;
                                const shouldBeChecked = String(label.getAttribute('data-sqz-correct') || '0') === '1';
                                const isChecked = !!input.checked;
                                label.classList.remove('is-correct', 'is-wrong');
                                if (isChecked) hasAnswered = true;
                                if (isChecked === shouldBeChecked) {
                                    if (shouldBeChecked) label.classList.add('is-correct');
                                } else {
                                    if (isChecked) label.classList.add('is-wrong');
                                    allCorrect = false;
                                }
                            });
                            if (!hasAnswered) {
                                showFeedback(null, '');
                                return;
                            }
                            showFeedback(allCorrect, allCorrect ? correctMessage('Doğru cevap.') : 'Yanlış cevap.', allCorrect);
                            if (allCorrect) markSolvedCurrentSlide();
                        };
                        inputs.forEach((input) => input.addEventListener('change', onChange));
                        onChange();
                    }

                    if (String(qRoot.getAttribute('data-sqz-type') || 'none') === 'drag_drop' || String(qRoot.getAttribute('data-sqz-type') || 'none') === 'matching') {
                        const inputs = Array.from(qRoot.querySelectorAll('[data-sqz-row] input[data-sqz-input], [data-sqz-row] select[data-sqz-input]'));
                        const onChange = () => {
                            evaluateRowInputs();
                        };
                        inputs.forEach((input) => input.addEventListener('change', onChange));
                        inputs.forEach((input) => input.addEventListener('input', onChange));
                        onChange();
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
                        if (solvedQuestionsInput) solvedQuestionsInput.value = String(Math.min(solvedQuestionsTotal, totalQuestions));
                        completeForm.submit();
                        return;
                    }
                    lastDirection = 1;
                    awardCurrentSlideXp();
                    idx += 1;
                    render();
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        if (idx > 0) prevBtn.click();
                    }
                    if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        nextBtn.click();
                    }
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
            .course-show-stage-inner .lesson-slide{
                height:100%;
            }
            .course-show-stage-inner .lesson-slide-shell{
                min-height:100%;
                padding:clamp(14px,2.4vw,28px);
                display:grid;
                align-content:start;
                gap:14px;
            }
            .course-show-stage-inner .lesson-slide-title{
                font-size:clamp(26px,3.5vw,52px);
            }
            .course-show-stage-inner .lesson-slide-subtitle{
                font-size:clamp(16px,1.55vw,22px);
            }
            .course-show-stage-inner .lesson-card,
            .course-show-stage-inner .lesson-split-card,
            .course-show-stage-inner .lesson-hero-card,
            .course-show-stage-inner .sqz-wrap{
                box-shadow:0 14px 34px rgba(15,23,42,.08);
            }
        </style>
    @endif
</div>
@endsection




