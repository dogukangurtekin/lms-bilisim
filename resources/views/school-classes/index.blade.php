@extends('layout.app')
@section('title','Siniflar')
@section('content')
<div class="top">
    <h1>Siniflar</h1>
</div>
<div class="card">
    <form id="classes-filter-form" method="GET" class="actions" style="margin-bottom:10px;align-items:end;flex-wrap:wrap">
        <div style="min-width:220px">
            <label>Sinif</label>
            <input id="classes-class-name" name="class_name" value="{{ $className ?? request('class_name') }}" placeholder="Sinif adi...">
        </div>
        <div style="min-width:180px">
            <label>Sube</label>
            <input id="classes-section" name="section" value="{{ $section ?? request('section') }}" placeholder="Sube...">
        </div>
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
<script>
(() => {
    const form = document.getElementById('classes-filter-form');
    const className = document.getElementById('classes-class-name');
    const section = document.getElementById('classes-section');
    if (!form) return;
    let timer = null;
    const submitLater = () => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => form.submit(), 300);
    };
    className?.addEventListener('input', submitLater);
    section?.addEventListener('input', submitLater);
})();
</script>
@endsection

