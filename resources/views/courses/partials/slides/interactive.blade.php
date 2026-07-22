<div class="lesson-card">
    @if(!empty($slide['content']))
        <p class="lesson-paragraph">{{ $slide['content'] }}</p>
    @endif
    @if($codeSrcdoc !== '')
        <iframe allow="camera *; microphone *; fullscreen *" class="lesson-code-frame" srcdoc="{{ $codeSrcdoc }}"></iframe>
    @endif
</div>
