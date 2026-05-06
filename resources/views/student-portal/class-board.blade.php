@extends('layout.app')
@section('title','Sınıf Panosu')
@section('content')
<div class="top"><h1>Sınıf Panosu</h1></div>

<style>
    .board-message-form{
        display:flex;
        gap:10px;
        align-items:center;
    }
    .board-message-select{
        flex:1;
        border:1px solid #dbe5f2;
        border-radius:10px;
        background:#fff;
        color:#0f172a;
        padding:10px 12px;
        font-size:14px;
    }
    .board-posts{
        display:grid;
        grid-template-columns:repeat(5,minmax(0,1fr));
        gap:12px;
    }
    .board-post{
        border:1px solid #dbe5f2;
        border-radius:12px;
        background:#fff;
        padding:10px;
        min-height:210px;
        display:flex;
        flex-direction:column;
        gap:8px;
    }
    .board-post-head{
        display:flex;
        align-items:center;
        gap:8px;
    }
    .board-post-head img{
        width:44px;
        height:44px;
        border-radius:10px;
        object-fit:cover;
        background:#f8fafc;
    }
    .board-post-name{
        font-weight:700;
        line-height:1.2;
    }
    .board-post-time{
        margin-top:auto;
        color:#64748b;
        font-size:12px;
    }
    @media (max-width: 1200px){
        .board-posts{grid-template-columns:repeat(3,minmax(0,1fr));}
    }
    @media (max-width: 960px){
        .board-message-form{flex-direction:column;align-items:stretch;}
        .board-posts{grid-template-columns:repeat(2,minmax(0,1fr));}
    }
    @media (max-width: 560px){
        .board-posts{grid-template-columns:1fr;}
    }
</style>

@if(session('ok'))
    <div class="card" style="border-color:#86efac;background:#f0fdf4;color:#166534">{{ session('ok') }}</div>
@endif

<div class="card">
    <h3 style="margin-top:0">Hazır Mesajlar (15)</h3>
    <p style="margin-top:0;color:#475569">Açılır listeden bir mesaj seçip sınıf panosunda paylaşabilirsin.</p>
    <form method="POST" action="{{ route('student.portal.class-board.store') }}" class="board-message-form">
        @csrf
        <select class="board-message-select" name="message_key" required>
            <option value="">Mesaj seçin...</option>
            @foreach($messages as $key => $text)
                <option value="{{ $key }}">{{ $text }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Paylaş</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Sınıf Paylaşımları</h3>
    @if($posts->isEmpty())
        <p>Sınıf panosunda henüz mesaj yok. İlk mesajı sen paylaş.</p>
    @else
        <div class="board-posts">
            @foreach($posts as $post)
                <article class="board-post">
                    <div class="board-post-head">
                        <img src="{{ $post['avatar_path'] ? asset($post['avatar_path']) : asset('logo192.png') }}" alt="{{ $post['first_name'] }} {{ $post['last_name'] }}">
                        <div>
                            <div class="board-post-name">{{ $post['first_name'] }} {{ $post['last_name'] }}</div>
                        </div>
                    </div>
                    <div>{{ $post['message'] }}</div>
                    <div class="board-post-time">{{ $post['shared_at'] }}</div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
