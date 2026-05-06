@extends('layout.app')

@section('title', 'Oyun ve Etkinlikler')

@section('content')
<div class="top">
    <h1>Oyun ve Etkinlikler</h1>
</div>

<div class="card">
    <p>Aşağıdaki oyunlar seviye tabanlı ilerleme ve ödevleme için hazırdır.</p>
    @if(auth()->user()?->hasRole('student'))
        <p style="margin-top:8px;color:#475569">
            Öğrenci modu: Oyunlarda varsayılan olarak sadece <b>1-2. seviyeler</b> açıktır.
            Üst seviyeler, öğretmen ödev atadığında görünür.
        </p>
    @endif
    <div class="activity-grid">
        <article class="activity-item">
            <img src="{{ asset('quiz.png') }}" alt="Canli Quiz">
            <div class="activity-body">
                <h3>Canli Quiz</h3>
                <div class="actions">
                    @if(auth()->user()?->hasRole('student'))
                        <a class="btn" href="{{ route('student.live-quiz.join.form') }}">Oyunu Aç</a>
                    @else
                        <a class="btn" href="{{ route('live-quiz.index') }}">Oyunu Aç</a>
                    @endif
                </div>
            </div>
        </article>

        <article class="activity-item">
            <img src="{{ asset('flowchart.png') }}" alt="Flowchart Programming">
            <div class="activity-body">
                <h3>Flowchart Programming</h3>
                <div class="actions">
                    <a class="btn" href="{{ route('flowchart.editor') }}">Uygulamayı Aç</a>
                    @if(auth()->user()?->hasRole('admin', 'teacher'))
                        <a class="btn" href="{{ route('flowchart.editor') }}">Ödevi Hazırla</a>
                    @endif
                </div>
            </div>
        </article>

        @foreach($games as $slug => $game)
            <article class="activity-item">
                <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}">
                <div class="activity-body">
                    <h3>{{ $game['name'] }}</h3>
                    <div class="actions">
                        @if(auth()->user()?->hasRole('student') && !in_array($slug, ['keyboard-race', 'block-builder-studio', 'flamestone-game'], true))
                            <a class="btn" href="{{ route('runner.open', ['slug' => $slug, 'from' => 1, 'to' => 2]) }}">Oyunu Aç (L1-L2)</a>
                        @else
                            @php
                                $gameUrl = auth()->user()?->hasRole('admin', 'teacher')
                                    ? url($game['url'] . '?role=' . (auth()->user()?->hasRole('admin') ? 'admin' : 'teacher'))
                                    : url($game['url']);
                            @endphp
                            <a class="btn" href="{{ $gameUrl }}" target="_blank">Oyunu Aç</a>
                        @endif

                        @if(auth()->user()?->hasRole('admin', 'teacher'))
                            <a class="btn" href="{{ route('activities.assignments.create', $slug) }}">Ödevi Oluştur</a>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
