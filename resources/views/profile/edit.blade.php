@extends('layout.app')
@section('title','Profilim')
@section('content')
@php
    $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')), 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
    $pwaSettings = (array) ($pwaSettings ?? []);
    $themes = (array) ($themes ?? []);
    $themeKey = (string) ($themeKey ?? 'default');
@endphp
<div class="top"><h1>Profilim</h1></div>

<div style="display:grid;gap:20px;max-width:1100px">
    <div class="card">
        <style>
            .theme-picker-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
            .theme-card{position:relative;display:block;border:1.5px solid var(--line,#dbe5f2);border-radius:18px;padding:14px;background:#fff;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s}
            .theme-card:hover{border-color:var(--violet,#5B3DF5)}
            .theme-card input[type="radio"]{position:absolute;top:12px;right:12px;width:18px;height:18px;margin:0;cursor:pointer;accent-color:var(--violet,#5B3DF5)}
            .theme-card:has(input:checked){border-color:var(--violet,#5B3DF5);background:var(--violet-tint,#EEEBFD);box-shadow:0 0 0 3px var(--violet-tint,#EEEBFD)}
            .theme-card-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding-right:26px}
            .theme-card-dots{display:inline-flex;gap:4px}
            .theme-card-dots span{width:16px;height:16px;border-radius:999px;display:inline-block}
            .theme-card p{margin:8px 0 0;color:var(--ink-soft,#64748b);font-size:13px;line-height:1.5}
            @media (max-width:640px){ .theme-picker-grid{grid-template-columns:1fr} }
        </style>
        <form method="POST" action="{{ route('profile.update') }}" style="display:grid;gap:14px" id="themeForm">
            @csrf
            @method('PUT')
            <div>
                <h3 style="margin:0 0 4px">Sistem Teması</h3>
                <p style="margin:0;color:#64748b">Profilinize uygun temayı seçin ve kaydedin. Seçim giriş yaptıktan sonra tüm arayüze uygulanır.</p>
            </div>
            <input type="hidden" name="first_name" value="{{ old('first_name', $firstName) }}">
            <input type="hidden" name="last_name" value="{{ old('last_name', $lastName) }}">
            <input type="hidden" name="username" value="{{ old('username', $username) }}">
            <div class="theme-picker-grid">
                @foreach($themes as $key => $theme)
                    <label class="theme-card">
                        <input type="radio" name="theme_key" value="{{ $key }}" {{ old('theme_key', $themeKey) === $key ? 'checked' : '' }}>
                        <div class="theme-card-head">
                            <strong>{{ $theme['label'] }}</strong>
                            <span class="theme-card-dots">
                                <span style="background:{{ $theme['preview'][0] ?? '#5B3DF5' }}"></span>
                                <span style="background:{{ $theme['preview'][1] ?? '#3E28B8' }}"></span>
                            </span>
                        </div>
                        <p>{{ $theme['description'] }}</p>
                    </label>
                @endforeach
            </div>
            <div>
                <button class="btn" type="submit">Temayı Kaydet</button>
            </div>
        </form>
    </div>

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
                    <input name="pwa_logo_url" value="{{ old('pwa_logo_url', $pwaSettings['logo_url'] ?? '') }}" placeholder="{{ \App\Support\Brand::logoUrl() }}">
                </div>
                <div style="grid-column:1 / -1">
                    <label>Okul Müdürü Ad Soyad</label>
                    <input name="principal_name" value="{{ old('principal_name', $pwaSettings['principal_name'] ?? '') }}" placeholder="Müdür adını yazın">
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <img src="{{ old('pwa_logo_url', $pwaSettings['logo_url'] ?? \App\Support\Brand::logoUrl()) }}" alt="Logo" style="width:64px;height:64px;object-fit:cover;border-radius:16px;border:1px solid #dbe5f2;background:#fff">
                <p style="margin:0;color:#64748b">Bu alan sadece logo ve açılış ekranı için kullanılır.</p>
            </div>
            <div>
                <button class="btn" type="submit">Logo Güncelle</button>
            </div>
        </form>

    @if($user->hasRole('admin'))
        <div class="card" style="border:1.5px solid #fecaca;background:#fef2f2;">
            <h3 style="margin:0 0 4px;color:#991b1b;">Tehlikeli Bölge</h3>
            <p style="margin:0 0 14px;color:#7f1d1d;">Bu işlem geri alınamaz. Öğretmenler, öğrenci hesapları ve sınıflar
               olduğu gibi kalır; ancak öğrencilere ait <strong>tüm ilerleme ve sonuç verisi</strong> (notlar, devamsızlık,
               ders/ödev/oyun/etkinlik/quiz ilerlemesi, XP, rozetler, satın alınan avatarlar) kalıcı olarak silinir —
               öğrenciler sisteme sanki yeni yüklenmiş gibi tertemiz bir durumda kalır.</p>
            <button type="button" class="btn btn-danger" id="open-reset-system-modal">Sistemi Sıfırla (Öğrenci Verileri)</button>
        </div>

        <div class="modal {{ $errors->has('confirm_phrase') ? 'open' : '' }}" id="reset-system-modal">
            <div class="modal-card">
                <div class="modal-head"><strong>Sistemi Sıfırla</strong></div>
                <p style="margin:0 0 10px;color:#475569;">Bu işlem <strong>geri alınamaz</strong>. Devam etmek için aşağıdaki
                   kutuya tam olarak <code>SİSTEMİ SIFIRLA</code> yazın.</p>
                <form method="POST" action="{{ route('system.reset-student-data') }}" id="reset-system-form">
                    @csrf
                    <input type="text" name="confirm_phrase" id="reset-system-confirm-input" autocomplete="off"
                        placeholder="SİSTEMİ SIFIRLA" style="width:100%;margin-bottom:12px;">
                    @error('confirm_phrase')
                        <div style="color:#dc2626;font-size:13px;margin-bottom:10px">{{ $message }}</div>
                    @enderror
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" class="btn" id="reset-system-cancel">Vazgeç</button>
                        <button type="submit" class="btn btn-danger" id="reset-system-submit" disabled>Evet, Kalıcı Olarak Sıfırla</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        (() => {
            const modal = document.getElementById('reset-system-modal');
            const openBtn = document.getElementById('open-reset-system-modal');
            const cancelBtn = document.getElementById('reset-system-cancel');
            const input = document.getElementById('reset-system-confirm-input');
            const submitBtn = document.getElementById('reset-system-submit');
            const REQUIRED_PHRASE = 'SİSTEMİ SIFIRLA';

            openBtn?.addEventListener('click', () => {
                if (input) input.value = '';
                if (submitBtn) submitBtn.disabled = true;
                modal?.classList.add('open');
            });
            cancelBtn?.addEventListener('click', () => modal?.classList.remove('open'));
            modal?.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
            input?.addEventListener('input', () => {
                if (submitBtn) submitBtn.disabled = input.value.trim() !== REQUIRED_PHRASE;
            });
        })();
        </script>
    @endif
</div>
@endsection