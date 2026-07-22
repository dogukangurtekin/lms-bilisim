<div class="lesson-split">
    <div class="lesson-card">
        <p class="lesson-paragraph">{{ $slide['content'] ?? '' }}</p>
        @if(!empty($slide['file_url']))
            <a class="lesson-link" href="{{ $slide['file_url'] }}" target="_blank">Ek Kaynak</a>
        @endif
    </div>
    <div class="lesson-media-stack">
        @if(!empty($slide['image_url']))
            <img src="{{ $slide['image_url'] }}" alt="slide gorsel" class="lesson-image">
        @endif
        @if($codeSrcdoc !== '')
            <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
        @endif
    </div>
</div>
