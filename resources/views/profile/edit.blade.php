@extends('layout.app')
@section('title','Profilim')
@section('content')
@php
    $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')), 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
@endphp
<div class="top"><h1>Profilim</h1></div>
<div class="card" style="max-width:760px">
    <form method="POST" action="{{ route('profile.update') }}" class="actions" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end">
        @csrf
        @method('PUT')
        <div>
            <label>Ad</label>
            <input name="first_name" value="{{ old('first_name', $firstName) }}" required>
        </div>
        <div>
            <label>Soyad</label>
            <input name="last_name" value="{{ old('last_name', $lastName) }}" required>
        </div>
        <div style="grid-column:1 / -1">
            <label>Kullanıcı Adı</label>
            <input name="username" value="{{ old('username', $username) }}" required>
        </div>
        <div>
            <label>Yeni Şifre</label>
            <input type="password" name="password" minlength="6" maxlength="72" placeholder="Değiştirmek istemiyorsanız boş bırakın">
        </div>
        <div>
            <label>Yeni Şifre (Tekrar)</label>
            <input type="password" name="password_confirmation" minlength="6" maxlength="72">
        </div>
        <div style="grid-column:1 / -1">
            <button class="btn" type="submit">Profili Güncelle</button>
        </div>
    </form>
</div>
@endsection

