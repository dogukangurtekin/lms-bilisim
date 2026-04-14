@php
    $slide = $slide ?? [];
    $question = $slide['question'] ?? [];
    $hideSlideTitle = $hideSlideTitle ?? false;
@endphp
<div class="slide-render">
    @if(!$hideSlideTitle)
        <h3>{{ $slide['title'] ?? 'Basliksiz Slide' }}</h3>
    @endif
    @if(!empty($slide['instructions']))
        <p><b>Yonlendirme:</b> {{ $slide['instructions'] }}</p>
    @endif
    @if(!empty($slide['content']))
        <p>{{ $slide['content'] }}</p>
    @endif
    @if(!empty($slide['image_url']))
        <img src="{{ $slide['image_url'] }}" alt="slide gorsel" style="max-width:100%;border:1px solid #e5e7eb;border-radius:8px">
    @endif
    @if(!empty($slide['video_url']))
        <p><a href="{{ $slide['video_url'] }}" target="_blank">Video Baglantisi</a></p>
    @endif
    @if(!empty($slide['file_url']))
        <p><a href="{{ $slide['file_url'] }}" target="_blank">Ek Kaynak</a></p>
    @endif
    @if(!empty($slide['code']))
        <iframe style="width:100%;min-height:58vh;border:1px solid #d1d5db;border-radius:8px;margin-top:8px" srcdoc="{{ $slide['code'] }}"></iframe>
    @endif

    @if(!empty($slide['question_prompt']))
        <div class="card" style="margin-top:8px">
            <b>Soru:</b> {{ $slide['question_prompt'] }}
            @if(($slide['interaction_type'] ?? '') === 'multiple_choice')
                <ul>
                    @foreach(($question['options'] ?? []) as $opt)
                        <li>
                            @if(is_array($opt))
                                {{ $opt['text'] ?? '' }} @if(!empty($opt['correct'])) <b>(Dogru)</b> @endif
                            @else
                                {{ $opt }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @elseif(($slide['interaction_type'] ?? '') === 'true_false')
                <p>Tip: Dogru / Yanlis</p>
            @elseif(($slide['interaction_type'] ?? '') === 'matching')
                <ul>@foreach(($question['pairs'] ?? []) as $pair)<li>{{ $pair['left'] ?? '' }} - {{ $pair['right'] ?? '' }}</li>@endforeach</ul>
            @elseif(($slide['interaction_type'] ?? '') === 'drag_drop')
                <p>Tip: Surukle Birak</p>
            @elseif(($slide['interaction_type'] ?? '') === 'short_answer')
                <p>Dogru Cevap: {{ $question['answer'] ?? '-' }}</p>
            @elseif(($slide['interaction_type'] ?? '') === 'checklist')
                <ul>
                    @foreach(($question['items'] ?? []) as $item)
                        <li>
                            @if(is_array($item))
                                {{ $item['text'] ?? '' }} @if(!empty($item['correct'])) <b>(Dogru)</b> @endif
                            @else
                                {{ $item }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
