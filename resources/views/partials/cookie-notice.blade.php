{{-- Yalnızca zorunlu çerez kullanıldığı için bu bir bilgilendirme bildirimidir; kapatıldığında tarayıcıda hatırlanır. --}}
<div id="cookie-notice" role="region" aria-label="Çerez bildirimi" hidden
     style="position:fixed;left:16px;right:16px;bottom:16px;max-width:560px;margin-inline:auto;background:#16182B;color:#fff;border-radius:14px;padding:16px 18px;box-shadow:0 10px 30px rgba(0,0,0,.25);z-index:200;font-size:14px;line-height:1.5">
    <p style="margin:0 0 12px">Bu site yalnızca oturum ve güvenlik için gerekli çerezleri kullanır; reklam veya izleme çerezi kullanmaz.
        <a href="{{ route('legal.cookies') }}" style="color:#C9BEFF">Ayrıntılar</a></p>
    <button type="button" id="cookie-notice-ok"
            style="background:#fff;color:#16182B;border:0;border-radius:9px;padding:9px 16px;font:inherit;font-weight:600;cursor:pointer">Anladım</button>
</div>
<script>
(function () {
    var box = document.getElementById('cookie-notice');
    if (!box) return;
    var seen = false;
    try { seen = localStorage.getItem('cookie_notice_ok') === '1'; } catch (e) {}
    if (!seen) box.hidden = false;
    document.getElementById('cookie-notice-ok').addEventListener('click', function () {
        box.hidden = true;
        try { localStorage.setItem('cookie_notice_ok', '1'); } catch (e) {}
    });
})();
</script>
