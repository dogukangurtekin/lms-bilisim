{{-- Herkese açık ödev oluşturulunca açılan bilgilendirme penceresi + link kopyalama (tüm paneller layout.app üzerinden alır). --}}
@php($phl = session('public_homework_link'))
@if(is_array($phl) && !empty($phl['url']))
<div id="public-hw-popup" role="dialog" aria-modal="true" aria-labelledby="public-hw-title" style="position:fixed;inset:0;z-index:5000;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,.55)">
    <div style="width:min(520px,100%);background:#fff;border-radius:18px;box-shadow:0 24px 60px rgba(15,23,42,.35);overflow:hidden">
        <div style="padding:20px 22px;background:linear-gradient(135deg,#5B3DF5,#7c5cff);color:#fff">
            <div style="font-size:32px;line-height:1">🎉</div>
            <h3 id="public-hw-title" style="margin:8px 0 2px;font-size:20px;color:#fff">Ödev oluşturuldu!</h3>
            <div style="opacity:.9;font-size:14px">Herkese açık ödev linkin hazır.</div>
        </div>
        <div style="padding:20px 22px">
            <div style="font-weight:800;margin-bottom:2px;color:#16182B">{{ $phl['title'] }}</div>
            @if(!empty($phl['due']))<div style="color:#64748b;font-size:13.5px;margin-bottom:10px">Son teslim: {{ \Illuminate\Support\Carbon::parse($phl['due'])->format('d.m.Y') }}</div>@endif
            <p style="margin:8px 0 12px;color:#475569;font-size:14px;line-height:1.5">Bu linki WhatsApp, e-posta ya da başka bir yolla paylaş. Linke tıklayan öğrenci <b>giriş yapmadan</b> ödeve ulaşır.</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <input id="public-hw-url" type="text" readonly value="{{ $phl['url'] }}" onfocus="this.select()" style="flex:1;min-width:200px;margin:0;padding:11px 12px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:14px">
                <button type="button" class="btn" data-copy-link="{{ $phl['url'] }}" style="white-space:nowrap">📋 Linki Kopyala</button>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
                <a class="btn" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($phl['title'].' – '.$phl['url']) }}" style="background:#16a34a;border-color:#16a34a;color:#fff">WhatsApp'ta Paylaş</a>
                <a class="btn" target="_blank" rel="noopener" href="{{ $phl['url'] }}">Linki Aç</a>
                <button type="button" class="btn" id="public-hw-close" style="margin-left:auto;background:#e2e8f0;border-color:#e2e8f0;color:#0f172a">Kapat</button>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var m=document.getElementById('public-hw-popup');
    function close(){ if(m) m.remove(); }
    document.getElementById('public-hw-close').addEventListener('click', close);
    m.addEventListener('click', function(e){ if(e.target===m) close(); });
    document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });
})();
</script>
@endif
<script>
(function(){
    if (window.__publicHwCopy) return; window.__publicHwCopy = true;
    function fallback(text){
        var t=document.createElement('textarea'); t.value=text; t.setAttribute('readonly',''); t.style.position='fixed'; t.style.opacity='0';
        document.body.appendChild(t); t.select(); var ok=false; try{ ok=document.execCommand('copy'); }catch(e){} t.remove(); return ok;
    }
    document.addEventListener('click', function(e){
        var b=e.target.closest('[data-copy-link]'); if(!b) return;
        var text=b.getAttribute('data-copy-link'), label=b.getAttribute('data-label') || b.textContent;
        b.setAttribute('data-label', label);
        function done(ok){ b.textContent = ok ? '✅ Kopyalandı' : 'Kopyalanamadı'; setTimeout(function(){ b.textContent=label; }, 1800); }
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(function(){done(true)}, function(){done(fallback(text))});
        else done(fallback(text));
    });
})();
</script>
