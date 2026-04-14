@extends('layout.app')
@section('title','Dersler')
@section('content')
<div class="top">
    <h1>Dersler</h1>
    <div class="actions">
        <a class="btn" href="{{ route('courses.create') }}">Ders Olustur</a>
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
        <thead><tr><th>ID</th><th>Ad</th><th>Kod</th><th>Islem</th></tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->id }}</td><td>{{ $item->name }}</td><td>{{ $item->code }}</td>
                <td class="actions">
                    <a class="btn" href="{{ route('courses.homeworks.create', $item) }}">Odev Ver</a>
                    <a class="btn" href="{{ route('courses.show', $item) }}">Goster</a>
                    <a class="btn" href="{{ route('courses.edit', $item) }}">Duzenle</a>
                    <form id="delete-{{ '$' }}item->id" method="POST" action="{{ route('courses.destroy', $item) }}">@csrf @method('DELETE')</form>
                    <button type="button" class="btn btn-danger" data-delete-form="delete-{{ '$' }}item->id">Sil</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>
@endsection

