@extends('layout.app')
@section('title','Siniflar')
@section('content')
<div class="top">
    <h1>Siniflar</h1>
    <div class="actions">
        <a class="btn" href="{{ route('classes.create') }}">Yeni Sayfa</a>
        <button class="btn" data-open-modal="quick-create-modal">Hizli Ekle (AJAX)</button>
    </div>
</div>
<div class="card">
    <form method="GET" class="actions" style="margin-bottom:10px">
        <input name="q" value="{{ $q ?? request('q') }}" placeholder="Ara...">
        <select name="sort">
            <option value="id">id</option>
            <option value="created_at">created_at</option>
        </select>
        <select name="dir">
            <option value="desc">desc</option>
            <option value="asc">asc</option>
        </select>
        <button class="btn" type="submit">Filtrele</button>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Ad</th><th>Sube</th><th>Islem</th></tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->id }}</td><td>{{ $item->name }}</td><td>{{ $item->section }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('classes.show', $item) }}">Goster</a>
                    <a class="btn" href="{{ route('classes.edit', $item) }}">Duzenle</a>
                    <form id="delete-{{ '$' }}item->id" method="POST" action="{{ route('classes.destroy', $item) }}">@csrf @method('DELETE')</form>
                    <button type="button" class="btn btn-danger" data-delete-form="delete-{{ '$' }}item->id">Sil</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>

<div id="quick-create-modal" class="modal">
    <div class="modal-card">
        <div class="modal-head"><strong>Hizli Ekle</strong><button class="btn" type="button" data-close-modal>Kapat</button></div>
        <form method="POST" action="{{ route('classes.store') }}" data-ajax="true">
            @csrf
            <label>Ad</label><input name='name'><label>Sube</label><input name='section'><label>Grade Level</label><input name='grade_level'><label>Academic Year</label><input name='academic_year'>
            <button class="btn" type="submit">Kaydet</button>
        </form>
    </div>
</div>
@endsection

