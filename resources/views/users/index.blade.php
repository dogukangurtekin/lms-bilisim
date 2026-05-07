@extends('layout.app')
@section('title','Kullanıcı Yönetimi')
@section('content')
<div class="top"><h1>Kullanıcı Yönetimi</h1></div>
<style>
    .users-form .field-wrap{min-width:180px}
    .users-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .bulk-tools{display:flex;flex-direction:column;gap:10px;align-items:stretch;margin-bottom:12px}
    .bulk-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .bulk-tools .template-btn{
        background:#166534 !important;
        border-color:#14532d !important;
        color:#fff !important;
        padding:8px 12px;
        font-size:13px;
        line-height:1.2;
        border-radius:8px;
    }
    .bulk-upload-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .bulk-upload-form input[type="file"]{
        max-width:240px;
        width:240px;
        font-size:12px;
        padding:4px;
    }
    .bulk-upload-form .btn{
        padding:8px 12px;
        font-size:13px;
        line-height:1.2;
        border-radius:8px;
    }
    .users-form .submit-wrap .btn{
        height:42px;
        padding:0 14px;
        display:inline-flex;
        align-items:center;
    }
    .users-form .submit-wrap{
        padding-top:28px;
    }
    @media (max-width:768px){
        .users-form{display:grid !important;grid-template-columns:1fr;gap:10px;align-items:stretch !important}
        .users-form .field-wrap{min-width:0;width:100%}
        .users-form input,.users-form select{width:100%}
        .users-form .btn{justify-self:start}
        .users-table-wrap table{min-width:680px}
        .bulk-upload-form input[type="file"]{width:100%;max-width:100%}
    }
</style>
<div class="card">
<div class="bulk-tools">
    <div class="bulk-row">
        <a class="btn template-btn" href="{{ route('users.bulk.students.template') }}">Ogrenci Sablonu (.xlsx)</a>
        <form method="POST" action="{{ route('users.bulk.students.store') }}" enctype="multipart/form-data" class="bulk-upload-form">@csrf
            <input type="file" name="file" accept=".xls,.xlsx,.csv,.txt" required>
            <button class="btn" type="submit">Toplu Ogrenci Yukle</button>
        </form>
    </div>
    <div class="bulk-row">
        <a class="btn template-btn" href="{{ route('users.bulk.teachers.template') }}">Ogretmen Sablonu (.xlsx)</a>
        <form method="POST" action="{{ route('users.bulk.teachers.store') }}" enctype="multipart/form-data" class="bulk-upload-form">@csrf
            <input type="file" name="file" accept=".xls,.xlsx,.csv,.txt" required>
            <button class="btn" type="submit">Toplu Ogretmen Yukle</button>
        </form>
    </div>
</div>
<form method="POST" action="{{ route('users.store') }}" class="actions users-form" style="margin-bottom:14px;align-items:end;flex-wrap:wrap">@csrf
<div class="field-wrap" style="min-width:220px"><label>Ad Soyad</label><input name="name" required></div>
<div class="field-wrap" style="min-width:220px"><label>E-posta</label><input type="email" name="email" required></div>
<div class="field-wrap"><label>Şifre</label><input type="password" name="password" required></div>
<div class="field-wrap"><label>Rol</label><select name="role" id="role-select" required><option value="teacher">Öğretmen</option><option value="student">Öğrenci</option><option value="admin">Admin</option></select></div>
<div class="field-wrap" style="min-width:220px" id="class-wrap"><label>Sınıf (Öğrenci)</label><select name="school_class_id"><option value="">Seçiniz</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }} / {{ $class->section }}</option>@endforeach</select></div>
<div class="submit-wrap"><button class="btn" type="submit">Kullanıcı Ekle</button></div>
</form>
<div class="table-responsive users-table-wrap"><table><thead><tr><th>ID</th><th>Ad</th><th>E-posta</th><th>Rol</th><th>İşlem</th></tr></thead><tbody>@foreach($users as $item)<tr><td>{{ $item->id }}</td><td>{{ $item->name }} @if($item->hasRole('teacher') && $item->teacher)<a class="btn" href="{{ route('users.teachers.classes.edit', $item->teacher) }}" style="margin-left:8px">Sınıf Ata</a>@endif</td><td>{{ $item->email }}</td><td>{{ $item->role?->slug ?? '-' }}</td><td class="actions">@if($item->hasRole('admin'))<button class="btn" type="button" disabled>Admin Silinemez</button>@else<form method="POST" action="{{ route('users.destroy', $item) }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Sil</button></form>@endif</td></tr>@endforeach</tbody></table></div>
{{ $users->links() }}
</div>
<script>(()=>{const role=document.getElementById('role-select');const wrap=document.getElementById('class-wrap');const set=()=>wrap.style.display=(role&&role.value==='student')?'block':'none';role?.addEventListener('change',set);set();})();</script>
@endsection
