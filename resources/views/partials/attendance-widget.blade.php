{{-- Yönetici / öğretmen panelinin sağ panelinde sabit duran "Bugünkü Yoklama" kutusu; her dakika kendini yeniler. --}}
<section class="att-widget" id="attWidget" data-url="{{ route('attendance.widget') }}" aria-label="Bugünkü yoklama">
    <p class="aw-empty">Yoklama yükleniyor…</p>
</section>
<style>
    .att-widget{margin:0 0 14px;padding:14px;border-radius:20px;border:1px solid #dbe4ee;background:linear-gradient(160deg,#ffffff,#f1f6ff);box-shadow:0 12px 30px rgba(15,23,42,.06);max-height:min(58vh,520px);overflow:auto}
    .aw-head{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:10px}
    .aw-head strong{display:block;font-size:15px;color:#0f172a}
    .aw-head span{display:block;font-size:12px;color:#64748b;font-weight:600}
    .aw-link{font-size:12.5px;font-weight:800;color:#4338ca;text-decoration:none;white-space:nowrap;padding:4px 8px;border-radius:999px;background:#eef2ff}
    .aw-empty{margin:0;font-size:13px;line-height:1.5;color:#64748b}
    .aw-empty a{font-weight:800;color:#4338ca}
    .aw-chips{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:10px}
    .aw-chips span{display:flex;flex-direction:column;align-items:center;padding:7px 2px;border-radius:12px;background:#fff;border:1px solid #e2e8f0;font-size:10.5px;font-weight:700;color:#64748b}
    .aw-chips b{font-size:17px;color:#1e293b}.aw-chips .ok b{color:#15803d}.aw-chips .bad b{color:#b91c1c}.aw-chips .live b{color:#1d4ed8}
    .aw-list{list-style:none;margin:0;padding:0;display:grid;gap:7px}
    .aw-list a{display:block;padding:8px 10px;border-radius:12px;background:#fff;border:1px solid #e2e8f0;text-decoration:none;color:inherit}
    .aw-list a:hover{border-color:#a5b4fc}
    .aw-row{display:flex;align-items:center;gap:8px;font-size:13px}
    .aw-row small{color:#64748b;font-weight:700}
    .aw-st{margin-left:auto;font-style:normal;font-size:10.5px;font-weight:800;padding:2px 8px;border-radius:999px}
    .aw-st.upcoming{background:#f1f5f9;color:#475569}.aw-st.ongoing{background:#dbeafe;color:#1d4ed8}.aw-st.closed{background:#dcfce7;color:#166534}.aw-st.pending{background:#fef3c7;color:#92400e}
    .aw-bar{height:6px;border-radius:99px;background:#e2e8f0;margin:6px 0 4px;overflow:hidden}
    .aw-bar i{display:block;height:100%;background:linear-gradient(90deg,#22c55e,#16a34a)}
    .aw-meta{display:flex;gap:8px;font-size:11.5px;font-weight:700;color:#64748b}
    .aw-meta .ok{color:#15803d}.aw-meta .bad{color:#b91c1c}
    .aw-absent{margin-top:10px;padding:9px 10px;border-radius:12px;background:#fef2f2;border:1px solid #fecaca;font-size:12.5px;line-height:1.5;color:#7f1d1d}
    .aw-absent b{display:block;margin-bottom:2px}.aw-absent em{font-style:normal;font-weight:800}
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
