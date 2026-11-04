{{-- Yönetici / öğretmen panelinin sağ panelinde sabit duran "Bugünkü Yoklama" kutusu; her dakika kendini yeniler. --}}
<section class="att-widget" id="attWidget" data-url="{{ route('attendance.widget') }}" aria-label="Bugünkü yoklama">
    <p class="aw-empty">Yoklama yükleniyor…</p>
</section>
<style>
    .att-widget{margin:0 0 10px;padding:9px 10px;border-radius:14px;border:1px solid #dbe4ee;background:linear-gradient(160deg,#ffffff,#f1f6ff);box-shadow:0 6px 16px rgba(15,23,42,.05);max-height:min(36vh,300px);overflow:auto}
    .aw-head{display:flex;align-items:baseline;gap:6px;margin-bottom:6px}
    .aw-head strong{font-size:13px;color:#0f172a;white-space:nowrap}
    .aw-head span{font-size:11px;color:#64748b;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .aw-link{margin-left:auto;font-size:11px;font-weight:800;color:#4338ca;text-decoration:none;white-space:nowrap;padding:2px 7px;border-radius:999px;background:#eef2ff}
    .aw-empty{margin:0;font-size:12px;line-height:1.45;color:#64748b}
    .aw-empty a{font-weight:800;color:#4338ca}
    .aw-chips{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:6px}
    .aw-chips span{padding:2px 7px;border-radius:999px;background:#fff;border:1px solid #e2e8f0;font-size:10.5px;font-weight:700;color:#64748b;white-space:nowrap}
    .aw-chips b{font-size:12px;color:#1e293b}.aw-chips .ok b{color:#15803d}.aw-chips .bad b{color:#b91c1c}.aw-chips .live b{color:#1d4ed8}
    .aw-list{list-style:none;margin:0;padding:0;display:grid;gap:3px}
    .aw-item{display:grid;grid-template-columns:34px 34px minmax(30px,1fr) auto;align-items:center;gap:6px;padding:4px 7px;border-radius:9px;background:#fff;border:1px solid #e2e8f0;text-decoration:none;color:inherit;font-size:12px}
    .aw-item:hover{border-color:#a5b4fc}
    .aw-item b{font-size:12.5px;color:#0f172a}
    .aw-item small{font-size:10.5px;color:#64748b;font-weight:700}
    .aw-item.ongoing{border-color:#93c5fd;background:#f0f7ff}
    .aw-bar{height:5px;border-radius:99px;background:#e2e8f0;overflow:hidden}
    .aw-bar i{display:block;height:100%;background:linear-gradient(90deg,#22c55e,#16a34a)}
    .aw-n{font-style:normal;font-size:10.5px;font-weight:800;color:#64748b;white-space:nowrap}
    .aw-n.ok{color:#15803d}.aw-n.bad{color:#b91c1c}.aw-n.live{color:#1d4ed8}
    .aw-absent{margin-top:6px;padding:5px 7px;border-radius:9px;background:#fef2f2;border:1px solid #fecaca;font-size:11px;line-height:1.4;color:#7f1d1d}
    .aw-absent b{font-weight:800}.aw-absent em{font-style:normal;font-weight:800}
</style>
<script>
(function () {
    var el = document.getElementById('attWidget');
    if (!el) return;
    var busy = false;
    function load() {
        if (busy || document.hidden) return;
        busy = true;
        fetch(el.dataset.url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function (r) { return r.ok ? r.text() : null; })
            .then(function (html) { if (html) el.innerHTML = html; })
            .catch(function () {})
            .then(function () { busy = false; });
    }
    load();
    setInterval(load, 60000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) load(); });
})();
</script>
