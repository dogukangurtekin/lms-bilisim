@extends('layout.app')

@section('title', 'Oyun ve Etkinlikler')

@section('content')
<div class="top">
    <h1>Oyun ve Etkinlikler</h1>
</div>

<div class="card">
    <p>Asagidaki oyunlar seviye tabanli ilerleme ve odevleme icin hazirdir.</p>
    @if(auth()->user()?->hasRole('student'))
        <p style="margin-top:8px;color:#475569">
            Ogrenci modu: Oyunlar varsayilan olarak sadece <b>1-2 level</b> aciktir.
            Ust level'ler ogretmen odev atadiginda gorunur.
        </p>
    @endif
    <div class="activity-grid">
        <article class="activity-item">
            <img src="{{ asset('quiz.png') }}" alt="Canli Quiz">
            <div class="activity-body">
                <h3>Canli Quiz</h3>
                <div class="actions">
                    @if(auth()->user()?->hasRole('student'))
                        <a class="btn" href="{{ route('student.live-quiz.join.form') }}">Oyunu Ac</a>
                    @else
                        <a class="btn" href="{{ route('live-quiz.index') }}">Oyunu Ac</a>
                    @endif
                </div>
            </div>
        </article>

        <article class="activity-item">
            <img src="{{ asset('flowchart.png') }}" alt="Flowchart Programming">
            <div class="activity-body">
                <h3>Flowchart Programming</h3>
                <div class="actions">
                    <a class="btn" href="{{ route('flowchart.editor') }}">Uygulamayi Ac</a>
                    @if(auth()->user()?->hasRole('admin', 'teacher'))
                        <a class="btn" href="{{ route('flowchart.editor') }}">Odevi Hazirla</a>
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
                            <a class="btn" href="{{ route('runner.open', ['slug' => $slug, 'from' => 1, 'to' => 2]) }}">Oyunu Ac (L1-L2)</a>
                        @else
                            <a class="btn" href="{{ url($game['url']) }}" target="_blank">Oyunu Ac</a>
                        @endif

                        @if(auth()->user()?->hasRole('admin', 'teacher'))
                            <a class="btn" href="{{ route('activities.assignments.create', $slug) }}">Odevi Olustur</a>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
