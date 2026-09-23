@extends('layout.app')
@section('title','Odayı Sil')
@section('content')
<div class="top"><h1>Odayı Sil</h1></div>
<div class="card" style="max-width:520px;">
    <p><strong>{{ $room->game_name }}</strong> ({{ $room->join_code }}) odasını kalıcı olarak silmek üzeresiniz.
       Bu odaya ait tüm katılımcı kayıtları da birlikte silinecek. Bu işlem geri alınamaz.</p>
    <div style="display:flex;gap:8px;margin-top:16px;">
        <form method="POST" action="{{ route('competitions.room.destroy', $room) }}" style="margin:0">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit">Evet, Kalıcı Olarak Sil</button>
        </form>
        <a class="btn" href="{{ route('competitions.index') }}">Vazgeç</a>
    </div>
</div>
@endsection
