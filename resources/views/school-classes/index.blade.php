@extends('layout.app')
@section('title','Siniflar')
@section('content')
<div class="top"><h1>Sınıflar</h1></div>
<style>
    .classes-form .field-wrap{min-width:180px}
    .classes-filter .field-wrap{min-width:180px}
    .classes-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
    @media (max-width: 768px){
        .classes-form,.classes-filter{display:grid !important;grid-template-columns:1fr;gap:10px !important;align-items:stretch !important}
        .classes-form .field-wrap,.classes-filter .field-wrap{min-width:0;width:100%}
        .classes-form input,.classes-filter input{width:100%}
        .classes-form .btn{justify-self:start}
        .classes-table-wrap table{min-width:560px}
    }
</style>
<div class="card">
    <form method="POST" action="{{ route('classes.store') }}" class="actions classes-form" style="margin-bottom:14px;align-items:end;flex-wrap:wrap;gap:10px">
        @csrf
        <div class="field-wrap"><label>Sınıf Adı</label><input name="name" value="{{ old('name') }}" placeholder="Örn: 5"></div>
        <div class="field-wrap"><label>Şube</label><input name="section" value="{{ old('section') }}" placeholder="Örn: A"></div>
        <button class="btn" type="submit">Sınıf Ekle</button>
    </form>

    <form id="classes-filter-form" method="GET" class="actions classes-filter" style="margin-bottom:10px;align-items:end;flex-wrap:wrap">
        <div class="field-wrap" style="min-width:220px"><label>Sınıf</label><input id="classes-class-name" name="class_name" value="{{ $className ?? request('class_name') }}" placeholder="Sınıf adı..."></div>
        <div class="field-wrap"><label>Şube</label><input id="classes-section" name="section" value="{{ $section ?? request('section') }}" placeholder="Şube..."></div>
    </form>

    <div class="classes-table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Ad</th><th>Şube</th><th>İşlem</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td><td>{{ $item->name }}</td><td>{{ $item->section }}</td>
                    <td class="actions">
                        <a class="btn" href="{{ route('classes.show', $item) }}">Göster</a>
                        <a class="btn" href="{{ route('classes.edit', $item) }}">Düzenle</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{ $items->links() }}
</div>
<script>
(() => {
    const form = document.getElementById('classes-filter-form');
    const className = document.getElementById('classes-class-name');
    const section = document.getElementById('classes-section');
    if (form) {
        let timer = null;
        const submitLater = () => { if (timer) clearTimeout(timer); timer = setTimeout(() => form.submit(), 300); };
        className?.addEventListener('input', submitLater);
        section?.addEventListener('input', submitLater);
    }

})();
</script>
@endsection
