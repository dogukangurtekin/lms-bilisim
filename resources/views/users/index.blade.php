@extends('layout.app')
@section('title','Kullanici Yonetimi')
@section('content')
<div class="top"><h1>Kullanici Yonetimi</h1></div>
<div class="card">
<form method="POST" action="{{ route('users.store') }}" class="actions" style="margin-bottom:14px;align-items:end;flex-wrap:wrap">@csrf
<div style="min-width:220px"><label>Ad Soyad</label><input name="name" required></div>
<div style="min-width:220px"><label>E-posta</label><input type="email" name="email" required></div>
<div style="min-width:180px"><label>Sifre</label><input type="password" name="password" required></div>
<div style="min-width:180px"><label>Rol</label><select name="role" id="role-select" required><option value="teacher">Ogretmen</option><option value="student">Ogrenci</option><option value="admin">Admin</option></select></div>
<div style="min-width:220px" id="class-wrap"><label>Sinif (Ogrenci)</label><select name="school_class_id"><option value="">Seciniz</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }} / {{ $class->section }}</option>@endforeach</select></div>
<div><button class="btn" type="submit">Kullanici Ekle</button></div>
</form>
<div class="table-responsive"><table><thead><tr><th>ID</th><th>Ad</th><th>E-posta</th><th>Rol</th><th>Islem</th></tr></thead><tbody>@foreach($users as $item)<tr><td>{{ $item->id }}</td><td>{{ $item->name }} @if($item->hasRole('teacher') && $item->teacher)<a class="btn" href="{{ route('users.teachers.classes.edit', $item->teacher) }}" style="margin-left:8px">Sinif Ata</a>@endif</td><td>{{ $item->email }}</td><td>{{ $item->role?->slug ?? '-' }}</td><td class="actions">@if($item->hasRole('admin'))<button class="btn" type="button" disabled>Admin Silinemez</button>@else<form method="POST" action="{{ route('users.destroy', $item) }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Sil</button></form>@endif</td></tr>@endforeach</tbody></table></div>
{{ $users->links() }}
</div>
<script>(()=>{const role=document.getElementById('role-select');const wrap=document.getElementById('class-wrap');const set=()=>wrap.style.display=(role&&role.value==='student')?'block':'none';role?.addEventListener('change',set);set();})();</script>
@endsection
