@extends('layout.app')
@section('title','Ders Detayi')
@section('content')
<div class="top" style="margin-bottom:10px">
    <a class="btn" href="{{ route('courses.index') }}">Derslerime Geri Don</a>
</div>
<div class="card">
    @php $slides = $course->lesson_payload['slides'] ?? []; @endphp
    @php $globalThemeCss = $course->lesson_payload['global_theme_css'] ?? ''; @endphp
    @if($globalThemeCss)
        <style>{{ $globalThemeCss }}</style>
    @endif
    @if(empty($slides))
        <p>Bu ders icin henuz slide icerigi olusturulmamis.</p>
    @else
        <div style="display:grid;grid-template-columns:1fr auto auto;align-items:center;gap:10px;margin:0 0 10px">
            <p style="margin:0"><b>Ders:</b> {{ $course->name }} | <b>Kod:</b> {{ $course->code }} | <b>Haftalik Saat:</b> {{ $course->weekly_hours }}</p>
            <span id="course-show-counter" class="badge" style="justify-self:center;font-size:14px;padding:8px 14px">1 / {{ count($slides) }}</span>
            <div style="justify-self:end;display:flex;align-items:center;gap:10px">
                <button class="btn" type="button" id="course-show-prev" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    Geri
                </button>
                <button class="btn" type="button" id="course-show-next" style="display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:800;padding:10px 16px">
                    <span id="course-show-next-label">Ileri</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                </button>
            </div>
        </div>
        <div id="course-show-slide-stage" class="card slide-theme" style="min-height:80vh;overflow:hidden;margin:0 0 10px"></div>

        <template id="course-show-slide-templates">
            @foreach($slides as $i => $slide)
                <div data-slide-index="{{ $i }}" data-slide-title="{{ $slide['title'] ?? ('Sayfa '.($i+1)) }}">
                    @include('courses.partials.slide-render', ['slide' => $slide, 'hideSlideTitle' => true])
                </div>
            @endforeach
        </template>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const stage = document.getElementById('course-show-slide-stage');
                const prevBtn = document.getElementById('course-show-prev');
                const nextBtn = document.getElementById('course-show-next');
                const nextLabel = document.getElementById('course-show-next-label');
                const counter = document.getElementById('course-show-counter');
                const tmpl = document.getElementById('course-show-slide-templates');
                const slides = Array.from(tmpl.content.querySelectorAll('[data-slide-index]'));
                let idx = 0;

                function fitStage() {
                    const holder = stage.querySelector('#course-show-fit');
                    if (!holder) return;
                    const iframe = holder.querySelector('iframe');
                    if (iframe) {
                        iframe.style.width = '100%';
                        iframe.style.height = Math.max(620, holder.clientHeight - 8) + 'px';
                        iframe.style.minHeight = '0';
                    }
                }

                function render() {
                    const current = slides[idx];
                    stage.innerHTML = '<div id="course-show-fit" style="width:100%;height:100%;min-height:72vh;overflow:hidden;display:flex;align-items:stretch;justify-content:stretch"></div>';
                    const fit = document.getElementById('course-show-fit');
                    const node = current.cloneNode(true);
                    node.style.width = '100%';
                    node.style.height = '100%';
                    fit.appendChild(node);
                    fitStage();
                    counter.textContent = (idx + 1) + ' / ' + slides.length;
                    prevBtn.disabled = idx <= 0;
                    const isLast = idx >= slides.length - 1;
                    if (nextLabel) nextLabel.textContent = isLast ? 'Son' : 'Ileri';
                }

                prevBtn.addEventListener('click', function () {
                    if (idx <= 0) return;
                    idx -= 1;
                    render();
                });
                nextBtn.addEventListener('click', function () {
                    if (idx >= slides.length - 1) return;
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
