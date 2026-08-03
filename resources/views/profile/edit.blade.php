@extends('layout.app')
@section('title','Profilim')
@section('content')
@php
    $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')), 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
    $pwaSettings = (array) ($pwaSettings ?? []);
@endphp
<div class="top"><h1>Profilim</h1></div>

<div style="display:grid;gap:20px;max-width:1100px">
    <div class="card">
        <form method="POST" action="{{ route('profile.update') }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;align-items:end">
            @csrf
            @method('PUT')
            <div style="grid-column:1 / -1">
                <h3 style="margin:0 0 4px">Kullanıcı Bilgileri</h3>
                <p style="margin:0;color:#64748b">Ad, kullanıcı adı ve şifre bilgilerini buradan güncelleyin.</p>
            </div>
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
                <button class="btn" type="submit">Kullanıcı Adı ve Şifreyi Güncelle</button>
            </div>
        </form>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('profile.branding.update') }}" style="display:grid;gap:12px">
            @csrf
            @method('PUT')
            <div>
                <h3 style="margin:0 0 4px">Logo ve Açılış Ekranı</h3>
                <p style="margin:0;color:#64748b">Yalnızca logo, başlık ve açılış ekranı ayarlarını burada yönetebilirsiniz.</p>
            </div>
            <label style="display:flex;align-items:center;gap:10px">
                <input type="checkbox" name="pwa_enabled" value="1" {{ old('pwa_enabled', $pwaSettings['enabled'] ?? false) ? 'checked' : '' }}>
                Açılış ekranını göster
            </label>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
                <div>
                    <label>Açılış Başlığı</label>
                    <input name="pwa_title" value="{{ old('pwa_title', $pwaSettings['title'] ?? '') }}" placeholder="Eğitim Portalı">
                </div>
                <div>
                    <label>Alt Metin</label>
                    <input name="pwa_subtitle" value="{{ old('pwa_subtitle', $pwaSettings['subtitle'] ?? '') }}" placeholder="Yükleniyor...">
                </div>
                <div style="grid-column:1 / -1">
                    <label>Logo URL</label>
                    <input name="pwa_logo_url" value="{{ old('pwa_logo_url', $pwaSettings['logo_url'] ?? '') }}" placeholder="{{ url('/public/logo.png') }}">
                </div>
                <div style="grid-column:1 / -1">
                    <label>Okul Müdürü Ad Soyad</label>
                    <input name="principal_name" value="{{ old('principal_name', $pwaSettings['principal_name'] ?? '') }}" placeholder="Müdür adını yazın">
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <img src="{{ old('pwa_logo_url', $pwaSettings['logo_url'] ?? url('/public/logo.png')) }}" alt="Logo" style="width:64px;height:64px;object-fit:cover;border-radius:16px;border:1px solid #dbe5f2;background:#fff">
                <p style="margin:0;color:#64748b">Bu alan sadece logo ve açılış ekranı için kullanılır.</p>
            </div>
            <div>
                <button class="btn" type="submit">Logo Güncelle</button>
            </div>
        </form>
    </div>
</div>
@endsection
