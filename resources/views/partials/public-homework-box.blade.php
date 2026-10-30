{{-- Ödev detay sayfasında herkese açık link kutusu. Kullanım: @include('partials.public-homework-box', ['item' => $homework]) --}}
@if(!empty($item->is_public) && !empty($item->public_token))
    @php($phUrl = \App\Support\PublicHomework::url($item->public_token))
    <div class="card" style="border:1.5px solid #a5b4fc;background:#eef2ff">
        <h3 style="margin:0 0 4px">🌐 Herkese açık link</h3>
        <p style="margin:0 0 10px;color:#4c4a8a">Bu linki alan herkes giriş yapmadan ödeve ulaşabilir.</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <input type="text" readonly value="{{ $phUrl }}" onfocus="this.select()" style="flex:1;min-width:220px;margin:0">
            <button type="button" class="btn" data-copy-link="{{ $phUrl }}">📋 Kopyala</button>
            <a class="btn" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode(($item->title ?? 'Ödev').' – '.$phUrl) }}">WhatsApp</a>
        </div>
    </div>
@endif
