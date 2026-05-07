@extends('layout.app')
@section('title','Ders Odevi Onizleme')
@section('content')
<div class="top">
    <h1>Ders Odevi Onizleme</h1>
    <a class="btn" href="{{ route('teacher.assignments.index') }}">Odevlere Don</a>
</div>

@php
    $isLessonPreview = (string) ($homework->assignment_type ?? 'lesson') === 'lesson' && !empty($homework->course?->lesson_payload['slides']);
    $slides = $isLessonPreview ? ($homework->course->lesson_payload['slides'] ?? []) : [];
    $globalThemeCss = $isLessonPreview ? ($homework->course->lesson_payload['global_theme_css'] ?? '') : '';
@endphp

@if($isLessonPreview)
    <div class="card" style="margin-bottom:10px">
        <div style="display:grid;grid-template-columns:1fr auto auto;align-items:center;gap:10px;margin:0 0 10px">
            <p style="margin:0">
                <b>Ders:</b> {{ $homework->course?->name ?? '-' }} |
                <b>Baslik:</b> {{ $homework->title }} |
                <b>Sinif:</b> {{ $homework->schoolClass?->name }}/{{ $homework->schoolClass?->section }}
            </p>
            <span id="teacher-lesson-counter" class="badge" style="justify-self:center;font-size:14px;padding:8px 14px">1 / {{ count($slides) }}</span>
            <div style="justify-self:end;display:flex;align-items:center;gap:10px">
                <button class="btn" type="button" id="teacher-lesson-prev" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    Geri
                </button>
                <button class="btn" type="button" id="teacher-lesson-next" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                    <span id="teacher-lesson-next-label">Ileri</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                </button>
            </div>
        </div>

        @if($globalThemeCss)
            <style>{{ $globalThemeCss }}</style>
        @endif

        <div id="teacher-lesson-slide-stage" class="card slide-theme" style="min-height:80vh;overflow:hidden;margin:0 0 10px"></div>

        <template id="teacher-lesson-slide-templates">
            @foreach($slides as $i => $slide)
                <div data-slide-index="{{ $i }}" data-slide-title="{{ $slide['title'] ?? ('Sayfa '.($i+1)) }}">
                    @include('courses.partials.slide-render', ['slide' => $slide, 'hideSlideTitle' => true])
                </div>
            @endforeach
        </template>
    </div>
@else
    <div class="card">
        <p><b>Ders:</b> {{ $homework->course?->name ?? '-' }}</p>
        <p><b>Baslik:</b> {{ $homework->title }}</p>
        <p><b>Sinif:</b> {{ $homework->schoolClass?->name }}/{{ $homework->schoolClass?->section }}</p>
        <p><b>Tip:</b> {{ strtoupper($homework->assignment_type) }}</p>
        <p><b>Icerik:</b> {{ $homework->target_slug ?? '-' }}</p>
        <p><b>Level:</b> {{ $homework->level_from ?? '-' }} - {{ $homework->level_to ?? '-' }}</p>
        <p><b>Teslim:</b> {{ $homework->due_date?->format('Y-m-d') ?? '-' }}</p>
        <p><b>Detay:</b> {{ $homework->details ?: '-' }}</p>
        @if($homework->attachment_path)
            <p><b>Ek Dosya:</b> <a href="{{ asset('storage/'.$homework->attachment_path) }}" target="_blank">{{ $homework->attachment_original_name ?? 'Dosyayi Ac' }}</a></p>
        @endif
    </div>
@endif

@if(!empty($gameUrl))
    <div class="card" style="padding:10px">
        <p><b>Ogretmen Onizleme:</b> Oyunu/uygulamayi ogrencideki gibi burada test edebilirsiniz.</p>
        <iframe
            id="teacher-homework-runner"
            allow="camera *; microphone *; fullscreen *"
            src="{{ $gameUrl }}"
            data-slug="{{ $gameSlug ?? '' }}"
            data-level-start="{{ (int) ($homework->level_from ?? 1) }}"
            data-level-end="{{ (int) ($homework->level_to ?? ($homework->level_from ?? 1)) }}"
            style="width:100%;height:calc(100vh - 260px);min-height:680px;border:1px solid #d1d5db;border-radius:10px;display:block"
        ></iframe>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lessonStage = document.getElementById('teacher-lesson-slide-stage');
    const lessonPrev = document.getElementById('teacher-lesson-prev');
    const lessonNext = document.getElementById('teacher-lesson-next');
    const lessonNextLabel = document.getElementById('teacher-lesson-next-label');
    const lessonCounter = document.getElementById('teacher-lesson-counter');
    const lessonTmpl = document.getElementById('teacher-lesson-slide-templates');

    if (lessonStage && lessonPrev && lessonNext && lessonCounter && lessonTmpl) {
        const lessonSlides = Array.from(lessonTmpl.content.querySelectorAll('[data-slide-index]'));
        let lessonIdx = 0;

        const fitIframeToHolder = (iframe, holder) => {
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
        };

        const fitLessonStage = () => {
            const holder = lessonStage.querySelector('#teacher-lesson-fit');
            if (!holder) return;
            const iframe = holder.querySelector('iframe');
            if (iframe) {
                fitIframeToHolder(iframe, holder);
            }
        };

        const renderLesson = () => {
            const current = lessonSlides[lessonIdx];
            if (!current) return;
            lessonStage.innerHTML = '<div id="teacher-lesson-fit" style="width:100%;height:100%;min-height:72vh;overflow:hidden;display:flex;align-items:stretch;justify-content:stretch"></div>';
            const fit = document.getElementById('teacher-lesson-fit');
            const node = current.cloneNode(true);
            node.style.width = '100%';
            node.style.height = '100%';
            fit.appendChild(node);
            fitLessonStage();
            lessonCounter.textContent = (lessonIdx + 1) + ' / ' + lessonSlides.length;
            lessonPrev.disabled = lessonIdx <= 0;
            const isLast = lessonIdx >= lessonSlides.length - 1;
            if (lessonNextLabel) lessonNextLabel.textContent = isLast ? 'Son' : 'Ileri';
        };

        lessonPrev.addEventListener('click', function () {
            if (lessonIdx <= 0) return;
            lessonIdx -= 1;
            renderLesson();
        });
        lessonNext.addEventListener('click', function () {
            if (lessonIdx >= lessonSlides.length - 1) return;
            lessonIdx += 1;
            renderLesson();
        });
        window.addEventListener('resize', fitLessonStage);
        renderLesson();
    }

    const iframe = document.getElementById('teacher-homework-runner');
    if (!iframe) return;
    const slug = String(iframe.dataset.slug || '');
    const levelStart = Math.max(1, Number(iframe.dataset.levelStart || 1));
    const levelEnd = Math.max(levelStart, Number(iframe.dataset.levelEnd || levelStart));
    const needsPostMessageLock = ['compute-it-runner', 'block-grid-runner', 'lightbot-runner'].includes(slug);
    if (!needsPostMessageLock) return;
    iframe.addEventListener('load', function () {
        const payload = { type: 'SET_LEVEL_RANGE', levelStart: levelStart, levelEnd: levelEnd };
        try {
            iframe.contentWindow.postMessage(payload, '*');
            setTimeout(function () { iframe.contentWindow.postMessage(payload, '*'); }, 150);
        } catch (e) {}
    });
});
</script>
@endpush
