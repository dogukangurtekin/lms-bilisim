@if(!empty($slide['image_url']))
    <figure class="lesson-media-figure">
        <img src="{{ $slide['image_url'] }}" alt="slide gorsel" class="lesson-image">
        @if($slide['instructions'] ?? false)
            <figcaption>{{ $slide['instructions'] }}</figcaption>
        @endif
    </figure>
@endif
@if(!empty($slide['content']))
    <p class="lesson-paragraph">{{ $slide['content'] }}</p>
@endif
