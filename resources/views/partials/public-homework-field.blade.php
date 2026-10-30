{{-- Ödev formlarında "Herkese açık" seçeneği. Kullanım: @include('partials.public-homework-field', ['checked' => ...]) --}}
@if(\App\Support\PublicHomework::available())
    <div class="public-hw-field" style="margin:14px 0;padding:12px 14px;border:1.5px dashed #a5b4fc;border-radius:12px;background:#eef2ff">
        <input type="hidden" name="is_public" value="0">
        <label style="display:flex;align-items:flex-start;gap:10px;margin:0;cursor:pointer;font-weight:700;color:#312e81">
            <input type="checkbox" name="is_public" value="1" style="width:auto;margin:3px 0 0" @checked(old('is_public', $checked ?? false))>
            <span>🌐 Herkese açık ödev
                <small style="display:block;font-weight:500;color:#4c4a8a;margin-top:2px">Açıldığında ödev için bir link oluşur. Linki alan herkes (ör. WhatsApp'tan gelen öğrenci) giriş yapmadan ödeve ulaşır. İlerleme ve XP kaydedilmez.</small>
            </span>
        </label>
        @error('is_public')<div style="color:#b91c1c;margin-top:6px;font-size:13.5px">{{ $message }}</div>@enderror
    </div>
@endif
