@extends('layout.app')
@section('title','Siniflar')
@section('content')
<div class="top"><h1>Sınıflar</h1></div>
<style>
    .panel-section{border:1px solid var(--line,#e2e8f0);border-radius:14px;padding:16px;margin-bottom:16px;background:var(--surface,#fff)}
    .panel-section:last-child{margin-bottom:0}
    .panel-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .panel-section-head h3{margin:0;font-family:var(--font-display);font-size:15px;color:var(--ink,#16182B)}
    .panel-section-head p{margin:2px 0 0;font-size:12.5px;color:var(--ink-soft,#585A72)}
    .panel-form-row{display:flex;gap:12px;align-items:end;flex-wrap:wrap}
    .panel-form-row .field-wrap{min-width:180px;display:flex;flex-direction:column;gap:6px}
    .panel-form-row .field-wrap label{font-size:12.5px;font-weight:600;color:var(--ink-soft,#585A72);margin:0}
    .panel-form-row .field-wrap input{margin:0}
    .classes-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
    @media (max-width: 768px){
        .panel-form-row{display:grid !important;grid-template-columns:1fr;gap:10px !important;align-items:stretch !important}
        .panel-form-row .field-wrap{min-width:0;width:100%}
        .panel-form-row input{width:100%}
        .panel-form-row .btn{justify-self:stretch;width:100%}
        .panel-section-head{flex-direction:column;align-items:stretch}
        .classes-table-wrap table{min-width:560px}
    }
</style>

<div class="panel-section">
    <div class="panel-section-head">
        <div>
            <h3>Yeni Sınıf Ekle</h3>
            <p>Sınıf adı ve şube bilgisiyle yeni bir sınıf oluşturun.</p>
        </div>
        @if(auth()->user()?->hasRole('admin'))
            <form id="classes-destroy-all-form" method="POST" action="{{ route('classes.destroy-all') }}" data-confirm="Tüm sınıflar sistemden kaldırılsın mı?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="padding:8px 14px;font-size:13px;">Tüm Sınıfları Sil</button>
            </form>
        @endif
    </div>
    <form method="POST" action="{{ route('classes.store') }}" class="panel-form-row">
        @csrf
        <div class="field-wrap"><label>Sınıf Adı</label><input name="name" value="{{ old('name') }}" placeholder="Örn: 5"></div>
        <div class="field-wrap"><label>Şube</label><input name="section" value="{{ old('section') }}" placeholder="Örn: A"></div>
        <button class="btn" type="submit">Sınıf Ekle</button>
    </form>
</div>

<div class="panel-section">
    <div class="panel-section-head">
        <div>
            <h3>Sınıfları Filtrele</h3>
            <p>Sınıf adı veya şubeye göre listeyi daraltın.</p>
        </div>
    </div>
    <form id="classes-filter-form" method="GET" class="panel-form-row">
        <div class="field-wrap" style="min-width:220px"><label>Sınıf</label><input id="classes-class-name" name="class_name" value="{{ $className ?? request('class_name') }}" placeholder="Sınıf adı..."></div>
        <div class="field-wrap"><label>Şube</label><input id="classes-section" name="section" value="{{ $section ?? request('section') }}" placeholder="Şube..."></div>
    </form>
</div>

<div class="panel-section">
    <div class="panel-section-head">
        <div>
            <h3>Sınıf Listesi</h3>
        </div>
    </div>
    <div class="classes-table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Ad</th><th>Şube</th><th>İşlem</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td><td>{{ $item->name }}</td><td>{{ $item->section }}</td>
                    <td class="actions">
                        <a class="btn" href="{{ route('classes.show', $item) }}" style="padding:7px 12px;font-size:13px;">Göster</a>
                        <a class="btn" href="{{ route('classes.edit', $item) }}" style="padding:7px 12px;font-size:13px;">Düzenle</a>
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
